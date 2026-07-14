<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return \Yajra\DataTables\Facades\DataTables::of(Order::with('store')->select('orders.*'))
                ->setRowClass(function ($row) {
                    return $row->status == 'delivered' ? '!bg-green-100 hover:!bg-green-200 transition-colors' : '';
                })
                ->addColumn('order_number', function ($row) {
                    return $row->order_number;
                })
                ->addColumn('store_name', function ($row) {
                    return $row->store ? $row->store->name : 'N/A';
                })
                ->addColumn('total', function ($row) {
                    return '$' . number_format($row->total_amount, 2);
                })
                ->addColumn('date', function ($row) {
                    return $row->created_at->format('M d, Y H:i');
                })
                ->editColumn('status', function ($row) {
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                        'quoted' => 'bg-blue-100 text-blue-800 border-blue-200',
                        'paid' => 'bg-green-100 text-green-800 border-green-200',
                        'cancelled' => 'bg-red-100 text-red-800 border-red-200',
                    ];
                    $color = $statusColors[$row->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                    return '<span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border ' . $color . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route('orders.show', $row->id);
                    $deleteUrl = route('orders.destroy', $row->id);
                    $deleteBtn = '';
                    if (!in_array($row->status, ['paid', 'shipped', 'delivered'])) {
                        $deleteBtn = '<button type="button" onclick="openDeleteModal(\'' . $deleteUrl . '\')" class="text-red-600 hover:text-red-900 font-medium">Delete</button>';
                    } else {
                        $deleteBtn = '<span class="text-gray-400 font-medium cursor-not-allowed" title="Paid/shipped/delivered orders cannot be deleted">Delete</span>';
                    }
                    return '
                        <a href="' . $showUrl . '" class="text-indigo-600 hover:text-indigo-900 mr-3 font-medium">Manage</a>
                        ' . $deleteBtn . '
                    ';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        return view('admin.orders.index');
    }

    public function show(Order $order)
    {
        $order->load('items.product');
        $stores = \App\Models\Store::all();
        $taxPercent = \App\Models\Setting::where('key', 'tax_percent')->value('value') ?? 0;
        $taxName = \App\Models\Setting::where('key', 'tax_name')->value('value') ?? '';
        return view('admin.orders.show', compact('order', 'stores', 'taxPercent', 'taxName'));
    }

    public function exportPdf(Order $order)
    {
        $order->load('items.product');
        $taxPercent = \App\Models\Setting::where('key', 'tax_percent')->value('value') ?? 0;
        $taxName = \App\Models\Setting::where('key', 'tax_name')->value('value') ?? '';
        
        $pdf = app('dompdf.wrapper')->loadView('admin.orders.pdf', compact('order', 'taxPercent', 'taxName'));
        
        return $pdf->download('order-' . $order->order_number . '.pdf');
    }

    public function edit(Order $order)
    {
        $order->load('items.product');
        $stores = \App\Models\Store::all();
        $taxPercent = \App\Models\Setting::where('key', 'tax_percent')->value('value') ?? 0;
        $taxName = \App\Models\Setting::where('key', 'tax_name')->value('value') ?? '';
        return view('admin.orders.show', compact('order', 'stores', 'taxPercent', 'taxName')); // Admin edits directly on the show page
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'store_id' => 'nullable|exists:stores,id',
            'customer_phone' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'status' => 'required|string',
            'total_amount' => 'nullable|numeric',
            'items' => 'nullable|array',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'tax_name' => 'nullable|string|max:255',
            'tax_type' => 'nullable|in:percent,fixed',
            'tax_amount' => 'nullable|numeric|min:0',
        ]);

        $calculatedTotal = 0;
        $itemsUpdated = false;

        if (isset($data['items'])) {
            foreach ($data['items'] as $itemId => $itemData) {
                $item = \App\Models\OrderItem::findOrFail($itemId);
                $item->update([
                    'unit_price' => $itemData['unit_price'],
                    'quantity' => $itemData['quantity'],
                ]);
                $calculatedTotal += $itemData['unit_price'] * $itemData['quantity'];
                $itemsUpdated = true;
            }
        }

        // Calculate and apply global tax
        $taxPercent = \App\Models\Setting::where('key', 'tax_percent')->value('value') ?? 0;
        $taxName = \App\Models\Setting::where('key', 'tax_name')->value('value') ?? '';
        $taxAmount = 0;

        if ($taxPercent > 0) {
            $taxAmount = ($calculatedTotal * $taxPercent) / 100;
        }

        // Set total amount to calculated sum plus tax
        if ($itemsUpdated) {
            $data['total_amount'] = $calculatedTotal + $taxAmount;
            $data['tax_name'] = $taxName;
            $data['tax_type'] = 'percent';
            $data['tax_amount'] = $taxPercent;
        } else {
            $data['total_amount'] = $order->total_amount;
            // Retain existing tax values if not updating items
            unset($data['tax_name'], $data['tax_type'], $data['tax_amount']);
        }

        $oldStatus = $order->status;
        unset($data['items']);
        $order->update($data);

        // Send WhatsApp notification if status changed to shipped or delivered
        if ($oldStatus != $order->status && in_array($order->status, ['shipped', 'delivered'])) {
            try {
                $whatsAppService = new \App\Services\WhatsAppService();
                $statusText = $order->status == 'shipped' ? 'shipped' : 'delivered';
                $msg = "📦 Update on Order #{$order->order_number}!\n\nYour order has been {$statusText}.\nThank you for shopping with us!";
                $whatsAppService->sendTextMessage($order->customer_phone, $msg);
            } catch (\Exception $e) {
                \Log::error('WhatsApp notification failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('orders.show', $order)->with('success', 'Order updated successfully.');
    }

    public function sendPaymentLink(Order $order)
    {
        $order->load('items.product');

        $stripeService = new \App\Services\StripeService();

        try {
            $sessionData = $stripeService->createCheckoutSession($order);

            $updateData = [
                'stripe_payment_link' => $sessionData['url'],
                'stripe_session_id' => $sessionData['session_id'],
                'status' => 'quoted',
            ];

            // Store payment_intent_id immediately if returned (real Stripe session)
            if (!empty($sessionData['payment_intent'])) {
                $updateData['stripe_payment_intent_id'] = $sessionData['payment_intent'];
            }

            $order->update($updateData);

            // Send WhatsApp payment link notification
            $whatsAppService = new \App\Services\WhatsAppService();
            $sent = $whatsAppService->sendPaymentLinkMessage($order, $sessionData['url']);

            $modeTag = $sessionData['is_mock'] ? ' [Simulated — add real Stripe keys to .env for live checkout]' : '';
            $successMsg = 'Stripe payment link generated!' . $modeTag . ' ';
            $successMsg .= $sent
                ? 'WhatsApp notification sent to customer.'
                : 'WhatsApp is in mock/log mode — check laravel.log.';

            return redirect()->route('orders.show', $order)->with('success', $successMsg);

        } catch (\Exception $e) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Error generating payment link: ' . $e->getMessage());
        }
    }

    /**
     * Render local Stripe payment simulation gateway page
     */
    public function mockPay(Request $request, Order $order)
    {
        if (in_array($order->status, ['paid', 'shipped', 'delivered'])) {
            return redirect()->route('orders.thank-you', $order);
        }
        
        $order->load('items.product');
        $sessionId = $request->query('session_id', 'mock_sess_' . uniqid());

        return view('admin.orders.mock_checkout', compact('order', 'sessionId'));
    }

    /**
     * Handle mock payment submission
     */
    public function mockPaySubmit(Request $request, Order $order)
    {
        $sessionId = $request->input('session_id');

        // Update Stripe details in database (status will be updated in stripeSuccess)
        $order->update([
            'stripe_session_id' => $sessionId
        ]);

        return redirect()->route('orders.stripe-success', [
            'order' => $order->id,
            'session_id' => $sessionId
        ]);
    }

    /**
     * Standard success callback URL for Stripe Redirect Flow.
     * Stripe appends ?session_id=cs_... and ?payment_intent=pi_... to the success URL.
     * This acts as an immediate UX fallback — webhooks are the authoritative payment confirmation.
     */
    public function stripeSuccess(Request $request, Order $order)
    {
        $sessionId = $request->query('session_id');
        $paymentIntent = $request->query('payment_intent'); // present on real Stripe redirects

        // Only update if not already marked paid by webhook
        if ($order->status !== 'paid') {
            $updateData = [
                'status' => 'paid',
                'paid_at' => now(),
            ];

            if ($sessionId) {
                $updateData['stripe_session_id'] = $sessionId;
            }

            if ($paymentIntent) {
                $updateData['stripe_payment_intent_id'] = $paymentIntent;
            }

            $order->update($updateData);

            $whatsAppService = new \App\Services\WhatsAppService();
            $msg = "🎉 Great news! Your payment for Order #{$order->order_number} has been received successfully.\n\nWe are now processing your order and will let you know once it's on its way. Thank you for shopping with us!";
            $whatsAppService->sendTextMessage($order->customer_phone, $msg);
        }

        return redirect()->route('orders.thank-you', $order);
    }

    public function destroyItem(Order $order, \App\Models\OrderItem $item)
    {
        if (in_array($order->status, ['paid', 'shipped', 'delivered'])) {
            return redirect()->route('orders.show', $order)->with('error', 'Items cannot be removed from paid, shipped or delivered orders.');
        }

        if ($item->order_id !== $order->id) {
            abort(403, 'Unauthorized action.');
        }

        $item->delete();

        // Recalculate and apply global tax
        $calculatedTotal = 0;
        foreach ($order->items()->get() as $orderItem) {
            $calculatedTotal += $orderItem->unit_price * $orderItem->quantity;
        }

        $taxPercent = \App\Models\Setting::where('key', 'tax_percent')->value('value') ?? 0;
        $taxAmount = 0;

        if ($taxPercent > 0) {
            $taxAmount = ($calculatedTotal * $taxPercent) / 100;
        }

        $order->update([
            'total_amount' => $calculatedTotal + $taxAmount
        ]);

        return redirect()->route('orders.show', $order)->with('success', 'Order item removed successfully.');
    }

    public function destroy(Order $order)
    {
        if (in_array($order->status, ['paid', 'shipped', 'delivered'])) {
            return redirect()->route('orders.index')->with('error', 'Paid, shipped or delivered orders cannot be deleted.');
        }
        
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Order deleted');
    }
}
