@extends('admin.layouts.app')

@section('content')
<h1 class="text-3xl font-bold mb-6">Dashboard</h1>
<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-gray-500 font-semibold">Total Orders</h2>
        <p class="text-3xl font-bold mt-2">{{ $totalOrders }}</p>
    </div>
    <div class="bg-white p-6 rounded shadow border-l-4 border-yellow-500">
        <h2 class="text-gray-500 font-semibold">Pending Approval</h2>
        <p class="text-3xl font-bold mt-2">{{ $pendingApproval }}</p>
    </div>
    <div class="bg-white p-6 rounded shadow border-l-4 border-green-500">
        <h2 class="text-gray-500 font-semibold">Delivered Orders</h2>
        <p class="text-3xl font-bold mt-2">{{ $deliveredOrders }}</p>
    </div>
    <div class="bg-white p-6 rounded shadow border-l-4 border-blue-500">
        <h2 class="text-gray-500 font-semibold">Revenue (Paid)</h2>
        <p class="text-3xl font-bold mt-2">${{ number_format($revenue, 2) }}</p>
    </div>
</div>
@endsection
