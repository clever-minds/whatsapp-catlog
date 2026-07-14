@extends('admin.layouts.app')

@section('page-title', 'Dashboard Overview')

@section('content')

{{-- ── Page heading ─────────────────────────────────────────── --}}
<div class="mb-8 fade-up">
    <h2 class="text-2xl font-bold text-gray-800">Welcome back 👋</h2>
    <p class="text-sm text-gray-500 mt-1">Here's what's happening with your store today.</p>
</div>

{{-- ── Stat cards ───────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

    {{-- Total Orders --}}
    <div class="fade-up fade-up-d1 group bg-white rounded-2xl p-6 shadow-sm border border-gray-100
                hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center"
                 style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <span class="text-xs font-medium text-green-600 bg-green-50 border border-green-100 rounded-full px-2 py-0.5">All time</span>
        </div>
        <p class="text-3xl font-extrabold text-gray-800 mb-1">{{ $totalOrders }}</p>
        <p class="text-sm font-medium text-gray-500">Total Orders</p>
    </div>

    {{-- Pending Approval --}}
    <div class="fade-up fade-up-d2 group bg-white rounded-2xl p-6 shadow-sm border border-gray-100
                hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center"
                 style="background:linear-gradient(135deg,#fffbeb,#fef3c7);">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-xs font-medium text-amber-600 bg-amber-50 border border-amber-100 rounded-full px-2 py-0.5">Pending</span>
        </div>
        <p class="text-3xl font-extrabold text-gray-800 mb-1">{{ $pendingApproval }}</p>
        <p class="text-sm font-medium text-gray-500">Pending Approval</p>
    </div>

    {{-- Delivered Orders --}}
    <div class="fade-up fade-up-d3 group bg-white rounded-2xl p-6 shadow-sm border border-gray-100
                hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center"
                 style="background:linear-gradient(135deg,#f0fdf4,#bbf7d0);">
                <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <span class="text-xs font-medium text-green-700 bg-green-50 border border-green-200 rounded-full px-2 py-0.5">Done</span>
        </div>
        <p class="text-3xl font-extrabold text-gray-800 mb-1">{{ $deliveredOrders }}</p>
        <p class="text-sm font-medium text-gray-500">Delivered Orders</p>
    </div>

    {{-- Revenue --}}
    <div class="fade-up fade-up-d4 group rounded-2xl p-6 shadow-sm border
                hover:shadow-md hover:-translate-y-0.5 transition-all duration-300"
         style="background:linear-gradient(135deg,#14532d 0%,#15803d 100%); border-color:transparent;">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center"
                 style="background:rgba(255,255,255,.15);">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-xs font-medium rounded-full px-2 py-0.5"
                  style="background:rgba(255,255,255,.18);color:rgba(255,255,255,.9);">Revenue</span>
        </div>
        <p class="text-3xl font-extrabold text-white mb-1">${{ number_format($revenue, 2) }}</p>
        <p class="text-sm font-medium" style="color:rgba(255,255,255,.7);">Paid Revenue</p>
    </div>

</div>

{{-- ── Quick links ──────────────────────────────────────────── --}}
<div class="fade-up" style="animation-delay:.25s;">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Quick Actions</h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        @php
        $links = [
            ['label'=>'Orders',     'route'=>'orders.index',     'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
            ['label'=>'Products',   'route'=>'products.index',   'icon'=>'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ['label'=>'Categories', 'route'=>'categories.index', 'icon'=>'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
            ['label'=>'Units',      'route'=>'units.index',      'icon'=>'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3'],
            ['label'=>'Reports',    'route'=>'admin.reports',    'icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ];
        @endphp

        @foreach($links as $link)
        <a href="{{ route($link['route']) }}"
           class="group bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-col items-center gap-2
                  hover:border-green-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 text-center">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                 style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                <svg class="w-5 h-5 text-green-600 group-hover:text-green-700 transition-colors"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-gray-600 group-hover:text-green-700 transition-colors">
                {{ $link['label'] }}
            </span>
        </a>
        @endforeach
    </div>
</div>

@endsection
