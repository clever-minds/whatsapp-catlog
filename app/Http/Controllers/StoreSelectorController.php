<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StoreSelectorController extends Controller
{
    /**
     * Show the Web-View Store Selector UI
     */
    public function index(Request $request)
    {
        $phone = $request->query('phone');

        if (!$phone) {
            return response("Phone number missing. Please open this link from WhatsApp.", 400);
        }

        return view('store-selector', compact('phone'));
    }

    /**
     * AJAX route to search stores
     */
    public function search(Request $request)
    {
        $query = $request->query('q', '');
        
        $stores = Store::where('name', 'LIKE', '%' . $query . '%')
            ->limit(20)
            ->get();

        return response()->json($stores);
    }

    /**
     * AJAX route to save the selected store
     */
    public function save(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'store_id' => 'required|exists:stores,id'
        ]);

        $phone = $request->input('phone');
        $storeId = $request->input('store_id');

        // Clean phone
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        // Save selected store in cache for 2 hours using the correct key
        Cache::put('whatsapp_store_id_' . $cleanPhone, $storeId, now()->addHours(2));

        Log::info("WebView: Saved store {$storeId} for phone {$cleanPhone}");

        // Now immediately send the catalog!
        try {
            $store = Store::find($storeId);
            if ($store) {
                $whatsAppService = new \App\Services\WhatsAppService();
                
                $cartSession = \App\Models\CartSession::create([
                    'customer_phone' => $cleanPhone,
                    'store_id' => $store->id,
                ]);
                $shopUrl = route('shop.index', $cartSession->uuid);
                
                $whatsAppService->sendTextMessage($cleanPhone, "Great! You have selected *{$store->name}*.\n\nPlease click the link below to select your products and place your order:\n{$shopUrl}\n\n*Note: Prices shown are estimates. We will send you the updated price with a payment link after you place your order.*");
            }
        } catch (\Exception $e) {
            Log::error("WebView: Failed to send catalog after save: " . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }
}
