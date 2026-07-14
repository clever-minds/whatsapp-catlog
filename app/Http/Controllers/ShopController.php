<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartSession;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShopController extends Controller
{
    public function index($uuid)
    {
        $session = CartSession::where('uuid', $uuid)->first();
        
        if (!$session || !$session->is_active) {
            return view('front.shop-expired');
        }

        // Check if 30 minutes have passed
        if ($session->created_at->addMinutes(30)->isPast()) {
            $session->update(['is_active' => false]);
            return view('front.shop-expired');
        }

        // Get categories that are parents (parent_id is null) or don't have parent logic set up yet
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function($q) {
                $q->where('is_active', true)->with('children');
            }])
            ->get();
            
        // Get all active products
        $products = Product::where('is_active', true)->get();

        return view('front.shop', compact('session', 'categories', 'products'));
    }

    public function search(Request $request, $uuid)
    {
        $session = CartSession::where('uuid', $uuid)->first();
        
        if (!$session || !$session->is_active || $session->created_at->addMinutes(30)->isPast()) {
            return response()->json(['error' => 'Session expired or invalid'], 400);
        }

        $query = $request->input('query');
        
        if (empty($query)) {
            return response()->json(['html' => '', 'empty' => true]);
        }

        $products = Product::where('is_active', true)
            ->where('name', 'like', '%' . $query . '%')
            ->get();

        $html = '';
        foreach ($products as $product) {
            $html .= view('front.partials.product-card', compact('product'))->render();
        }

        return response()->json(['html' => $html, 'empty' => false, 'count' => $products->count()]);
    }

    public function placeOrder(Request $request, $uuid)
    {
        $session = CartSession::where('uuid', $uuid)->first();
        
        if (!$session || !$session->is_active) {
            return response()->json(['error' => 'Session expired or invalid'], 400);
        }

        // Check if 30 minutes have passed
        if ($session->created_at->addMinutes(30)->isPast()) {
            $session->update(['is_active' => false]);
            return response()->json(['error' => 'Session expired (30 minute timeout limit). Please request a new link.'], 400);
        }

        $cartData = $request->input('cart', []);
        if (empty($cartData)) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        try {
            DB::beginTransaction();

            $previousName = Order::where('customer_phone', $session->customer_phone)
                ->whereNotNull('customer_name')
                ->where('customer_name', '!=', 'Customer')
                ->where('customer_name', '!=', 'Unknown')
                ->orderBy('id', 'desc')
                ->value('customer_name');

            $cachedName = \Illuminate\Support\Facades\Cache::get('whatsapp_name_' . $session->customer_phone);

            if (!empty($cachedName)) {
                $customerName = $cachedName;
            } else {
                $customerName = $previousName ?: 'Customer';
            }

            $order = Order::create([
                'customer_name' => $customerName,
                'customer_phone' => $session->customer_phone,
                'store_id' => $session->store_id,
                'status' => 'pending_approval',
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($cartData as $item) {
                $productId = $item['id'];
                $quantity = $item['quantity'];

                $product = Product::find($productId);
                if ($product) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                    ]);
                    $totalAmount += ($quantity * $product->price);
                }
            }

            $taxPercent = Setting::where('key', 'tax_percent')->value('value') ?? 0;
            $taxName = Setting::where('key', 'tax_name')->value('value') ?? 'Tax';
            $taxAmount = 0;

            if ($taxPercent > 0) {
                $taxAmount = ($totalAmount * $taxPercent) / 100;
            }

            $order->update([
                'total_amount' => $totalAmount + $taxAmount,
                'tax_name' => $taxName,
                'tax_type' => 'percent',
                'tax_amount' => $taxPercent
            ]);

            // Deactivate session
            $session->update(['is_active' => false]);

            DB::commit();

            // Send Email to Admin
            try {
                $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
                \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\NewOrderNotification($order));
            } catch (\Exception $mailEx) {
                Log::error('Failed to send order email: ' . $mailEx->getMessage());
            }

            // Send WhatsApp Message
            $whatsAppService = new \App\Services\WhatsAppService();
            $autoReplyMsg = "Your order #{$order->order_number} has been sent successfully. We will shortly *send you the updated price with a payment link to complete your order*.";
            $whatsAppService->sendTextMessage($session->customer_phone, $autoReplyMsg);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'redirect_url' => route('orders.thank-you', $order->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shop Order Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to place order'], 500);
        }
    }
}
