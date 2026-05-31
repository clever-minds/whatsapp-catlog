@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Manage Order #{{ $order->id }}</h1>
    <a href="{{ route('orders.index') }}" class="text-gray-600 hover:underline">Back to Orders</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Order Details & Update -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">Order Details</h2>
        <form action="{{ route('orders.update', $order) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Customer Name</label>
                <input type="text" name="customer_name" value="{{ $order->customer_name }}" class="w-full border p-2 rounded">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Customer Phone *</label>
                <input type="text" name="customer_phone" value="{{ $order->customer_phone }}" class="w-full border p-2 rounded" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Customer Email</label>
                <input type="email" name="customer_email" value="{{ $order->customer_email }}" class="w-full border p-2 rounded">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Status</label>
                <select name="status" class="w-full border p-2 rounded">
                    <option value="pending_approval" {{ $order->status == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="quoted" {{ $order->status == 'quoted' ? 'selected' : '' }}>Quoted</option>
                    <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Total Amount ($)</label>
                <input type="number" step="0.01" name="total_amount" value="{{ $order->total_amount }}" class="w-full border p-2 rounded font-bold text-lg">
            </div>
            
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full mb-2">Update Order</button>
        </form>

        @if($order->status == 'pending_approval')
        <form action="{{ route('orders.send-payment-link', $order) }}" method="POST" onsubmit="return confirm('Generate and send Stripe Link via WhatsApp?');">
            @csrf
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 w-full flex justify-center items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Approve & Send Stripe Link
            </button>
        </form>
        @endif
    </div>

    <!-- Order Items -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">Order Items</h2>
        @if($order->items->count() > 0)
        <ul>
            @foreach($order->items as $item)
            <li class="border-b py-3 flex justify-between">
                <div>
                    <span class="font-bold">{{ $item->product ? $item->product->name : 'Unknown Product' }}</span>
                    <span class="text-gray-600 text-sm block">Qty: {{ $item->quantity }}</span>
                </div>
                <div class="font-semibold text-gray-700">
                    ${{ number_format($item->unit_price, 2) }}
                </div>
            </li>
            @endforeach
        </ul>
        @else
        <p class="text-gray-500">No items found for this order.</p>
        @endif
    </div>
</div>
@endsection
