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
    public function index()
    {
        $orders = Order::latest()->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items.product');
        return view('admin.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        return view('admin.orders.edit', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'status' => 'required|string',
            'total_amount' => 'nullable|numeric',
        ]);

        $order->update($data);
        return redirect()->route('orders.show', $order)->with('success', 'Order updated successfully');
    }

    public function sendPaymentLink(Order $order)
    {
        // Integrate Stripe API to generate payment link here
        $order->update(['status' => 'quoted']);
        return redirect()->route('orders.show', $order)->with('success', 'Payment link sent successfully');
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Order deleted');
    }
}
