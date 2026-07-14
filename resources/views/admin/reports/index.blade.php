@extends('admin.layouts.app')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Sales & Performance Reports</h1>
        <p class="text-sm text-gray-500 mt-1">Real-time store metrics, popular products, and financial settlements.</p>
    </div>
    
    <!-- Primary Export Action -->
    <a href="{{ route('admin.reports.export', request()->query()) }}" class="inline-flex items-center justify-center bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl shadow transition-all duration-150 hover:-translate-y-0.5 active:translate-y-0">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        Export CSV Data Report
    </a>
</div>

<!-- Filters Panel Card -->
<div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6 mb-8">
    <form action="{{ route('admin.reports') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Start Date</label>
            <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" class="w-full border border-gray-200 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
        
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">End Date</label>
            <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" class="w-full border border-gray-200 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
        
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Payment Status Filter</label>
            <select name="status" class="w-full border border-gray-200 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none appearance-none bg-no-repeat bg-right pr-8" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%236b7280%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22%3E%3Cpath d=%22m6 9 6 6 6-6%22/%3E%3C/svg%3E'); background-size: 1.25rem;">
                <option value="">All Statuses</option>
                <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                <option value="quoted" {{ request('status') == 'quoted' ? 'selected' : '' }}>Quoted</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
            </select>
        </div>
        
        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-gray-800 hover:bg-gray-900 text-white font-bold py-2.5 rounded-lg text-sm transition-colors text-center">
                🔍 Filter Results
            </button>
            <a href="{{ route('admin.reports') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 px-4 rounded-lg text-sm font-semibold transition-colors text-center">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- High-Level KPI Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- Card 1: Revenue -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Paid Revenue</h3>
            <p class="text-3xl font-extrabold text-blue-900 mt-2">${{ number_format($totalRevenue, 2) }}</p>
            <span class="text-xs text-emerald-600 mt-1 font-semibold block">✓ Settlement Verified</span>
        </div>
        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>
    
    <!-- Card 2: Average Order Value -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Average Order</h3>
            <p class="text-3xl font-extrabold text-indigo-950 mt-2">${{ number_format($averageOrderValue, 2) }}</p>
            <span class="text-xs text-gray-400 mt-1 block">AOV for Paid Orders</span>
        </div>
        <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
        </div>
    </div>

    <!-- Card 3: Pending Approvals -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 border-l-4 border-amber-500">
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Pending Approvals</h3>
            <p class="text-3xl font-extrabold text-amber-900 mt-2">{{ $pendingApprovalsCount }}</p>
            <span class="text-xs text-amber-600 mt-1 font-semibold block">⚠️ Action Required</span>
        </div>
        <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
    </div>

    <!-- Card 4: Orders volume -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Orders</h3>
            <p class="text-3xl font-extrabold text-slate-800 mt-2">{{ $totalOrdersCount }}</p>
            <span class="text-xs text-gray-400 mt-1 block">In Selected Range</span>
        </div>
        <div class="p-3 bg-slate-50 text-slate-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
        </div>
    </div>
</div>

<!-- Main Section: Daily Trend Graph -->
<div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-6 mb-8">
    <h2 class="font-extrabold text-lg text-gray-900 mb-4 tracking-tight">Daily Sales Trend (Last 30 Days)</h2>
    <div class="relative w-full" style="height: 350px;">
        <canvas id="salesTrendChart"></canvas>
    </div>
</div>

<!-- Bottom Section: Hot Selling Items Breakdowns -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    
    <!-- Top Performing Products -->
    <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-x-auto overflow-y-hidden">
        <div class="bg-gray-50 border-b border-gray-100 px-6 py-4 flex justify-between items-center">
            <h3 class="font-extrabold text-gray-900 tracking-tight">Top 5 Best-Selling Products</h3>
            <span class="text-xs bg-blue-100 text-blue-800 font-bold px-2 py-0.5 rounded">Sales Vol.</span>
        </div>
        <div class="divide-y divide-gray-100">
            @if($topProducts->count() > 0)
                @foreach($topProducts as $product)
                    <div class="px-6 py-4 flex justify-between items-center hover:bg-slate-50 transition-colors">
                        <div>
                            <span class="font-semibold text-gray-800 text-sm">{{ $product->name }}</span>
                            <span class="text-xs text-gray-400 block mt-0.5">Quantity Ordered: {{ $product->total_qty }}</span>
                        </div>
                        <span class="font-extrabold text-blue-900">${{ number_format($product->total_sales, 2) }}</span>
                    </div>
                @endforeach
            @else
                <div class="p-8 text-center text-gray-400 text-sm">
                    No sales recorded for products in this date range.
                </div>
            @endif
        </div>
    </div>

    <!-- Top Performing Categories -->
    <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-x-auto overflow-y-hidden">
        <div class="bg-gray-50 border-b border-gray-100 px-6 py-4 flex justify-between items-center">
            <h3 class="font-extrabold text-gray-900 tracking-tight">Top Categories by Sales Volume</h3>
            <span class="text-xs bg-indigo-100 text-indigo-800 font-bold px-2 py-0.5 rounded">Revenue</span>
        </div>
        <div class="divide-y divide-gray-100">
            @if($topCategories->count() > 0)
                @foreach($topCategories as $category)
                    <div class="px-6 py-4 flex justify-between items-center hover:bg-slate-50 transition-colors">
                        <div>
                            <span class="font-semibold text-gray-800 text-sm">{{ $category->name }}</span>
                            <span class="text-xs text-gray-400 block mt-0.5">Quantity Sold: {{ $category->total_qty }}</span>
                        </div>
                        <span class="font-extrabold text-indigo-900">${{ number_format($category->total_sales, 2) }}</span>
                    </div>
                @endforeach
            @else
                <div class="p-8 text-center text-gray-400 text-sm">
                    No sales recorded for categories in this date range.
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Loading Chart.js via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const trendData = {!! json_encode($salesTrend) !!};
        
        const labels = trendData.map(item => item.date);
        const data = trendData.map(item => item.revenue);

        const ctx = document.getElementById('salesTrendChart').getContext('2d');
        
        // Build subtle background gradient under line
        const blueGradient = ctx.createLinearGradient(0, 0, 0, 300);
        blueGradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
        blueGradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Daily Revenue ($)',
                    data: data,
                    borderColor: '#2563eb', // blue-600
                    backgroundColor: blueGradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3, // smooth rounded curves
                    pointBackgroundColor: '#1e3a8a',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        padding: 12,
                        backgroundColor: '#0f172a', // slate-900
                        titleColor: '#cbd5e1',
                        titleFont: {
                            family: 'Plus Jakarta Sans',
                            weight: 'bold'
                        },
                        bodyColor: '#ffffff',
                        bodyFont: {
                            family: 'Plus Jakarta Sans',
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return ' Revenue: $' + context.raw.toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10,
                                family: 'Plus Jakarta Sans'
                            },
                            color: '#94a3b8'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        },
                        ticks: {
                            font: {
                                size: 10,
                                family: 'Plus Jakarta Sans'
                            },
                            color: '#94a3b8',
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
