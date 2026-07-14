@extends('admin.layouts.app')

@section('page-title', 'Order #' . $order->order_number)

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Order #{{ $order->order_number }}</h1>
        <p class="text-sm text-gray-500 mt-1">Received on {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('orders.export-pdf', $order) }}" class="inline-flex items-center text-sm font-semibold text-red-600 hover:text-red-800 transition-colors bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg border border-red-200 shadow-sm">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Export to PDF
        </a>
        <a href="{{ route('orders.index') }}" class="inline-flex items-center text-sm font-semibold text-green-600 hover:text-green-800 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Orders List
        </a>
    </div>
</div>

<!-- Alert notifications for specific actions -->
@if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm mb-6 flex items-start">
        <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div>
            <p class="font-bold">Error</p>
            <p class="text-sm">{{ session('error') }}</p>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left 2 Columns: Single comprehensive editing form -->
    <div class="lg:col-span-2 space-y-8">
        <form id="orderForm" action="{{ route('orders.update', $order) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="bg-white shadow-md rounded-xl border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-lg">
                <div class="px-6 py-4 flex justify-between items-center text-white"
                     style="background:linear-gradient(135deg,#14532d,#15803d);">
                    <h2 class="font-bold text-lg tracking-wide">Customer &amp; Status Profile</h2>
                    
                    @php
                        $statusColors = [
                            'shipped' => 'bg-blue-500 text-white',
                            'delivered' => 'bg-slate-500 text-white'
                        ];
                        $statusLabels = [
                            'shipped' => 'Shipped',
                            'delivered' => 'Delivered'
                        ];
                    @endphp
                    <span class="px-3 py-1 text-xs font-extrabold uppercase rounded-full shadow-inner tracking-wider {{ $statusColors[$order->status] ?? 'bg-gray-500 text-white' }}">
                        {{ $statusLabels[$order->status] ?? $order->status }}
                    </span>
                </div>
                
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Customer Name</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </span>
                            <input type="text" name="customer_name" value="{{ $order->customer_name }}" class="w-full border border-gray-200 pl-10 pr-4 py-2.5 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="John Doe">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Store</label>
                        <select name="store_id" class="w-full border border-gray-200 px-4 py-2.5 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all appearance-none bg-no-repeat bg-right pr-10" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%236b7280%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22%3E%3Cpath d=%22m6 9 6 6 6-6%22/%3E%3C/svg%3E'); background-size: 1.25rem;">
                            <option value="">-- Select a Store --</option>
                            @foreach($stores as $s)
                                <option value="{{ $s->id }}" {{ $order->store_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">WhatsApp Phone Number *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 001.333 4.982L2 22l5.183-1.359a9.936 9.936 0 004.825 1.239h.004c5.507 0 9.99-4.478 9.99-9.985a9.982 9.982 0 00-9.99-9.895zm5.782 14.154c-.247.697-1.21 1.272-1.666 1.34-.457.067-.932.137-2.955-.667-2.581-1.026-4.22-3.649-4.35-3.821-.13-.172-1.054-1.399-1.054-2.673 0-1.274.666-1.902.903-2.15.237-.248.522-.31.696-.31h.5c.174 0 .408.067.625.592.223.538.761 1.849.827 1.982.066.134.11.29.02.464-.09.174-.136.27-.27.424-.136.155-.283.344-.405.464-.135.133-.277.279-.12.548.156.269.697 1.144 1.493 1.854.767.684 1.413.896 1.616.996.204.1.326.084.448-.052.122-.136.522-.604.662-.808.14-.204.28-.17.472-.1.192.07 1.22.576 1.43.684.21.107.35.16.402.247.052.09.052.518-.195 1.215z"/></svg>
                            </span>
                            <input type="text" name="customer_phone" value="{{ $order->customer_phone }}" class="w-full border border-gray-200 pl-10 pr-4 py-2.5 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" required placeholder="e.g. 919876543210">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Customer Email</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </span>
                            <input type="email" name="customer_email" value="{{ $order->customer_email }}" class="w-full border border-gray-200 pl-10 pr-4 py-2.5 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="name@example.com">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Workflow Status</label>
                        @if(in_array($order->status, ['paid', 'shipped']))
                            <select name="status" class="w-full border border-gray-200 px-4 py-2.5 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all appearance-none bg-no-repeat bg-right pr-10" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%236b7280%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22%3E%3Cpath d=%22m6 9 6 6 6-6%22/%3E%3C/svg%3E'); background-size: 1.25rem;">
                                @if($order->status == 'paid')
                                    <option value="paid" selected>Paid & Verified (Current)</option>
                                @endif
                                <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            </select>
                        @else
                            <div class="w-full border border-gray-100 bg-gray-50 px-4 py-2.5 rounded-lg text-gray-600 font-medium">
                                {{ $order->status == 'delivered' ? 'Delivered' : ($statusLabels[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status))) }}
                            </div>
                            <input type="hidden" name="status" value="{{ $order->status }}">
                        @endif
                    </div>
                </div>
            </div>

            <!-- Interactive Order Items Table Card -->
            <div class="bg-white shadow-md rounded-xl border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-lg mt-8">
                <div class="bg-gradient-to-r from-gray-800 to-slate-900 px-6 py-4 flex justify-between items-center text-white">
                    <h2 class="font-bold text-lg tracking-wide">Editable Order Items</h2>
                    <span class="bg-slate-700 text-xs px-2.5 py-1 rounded font-bold">{{ $order->items->count() }} Products</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs font-bold uppercase tracking-wider text-gray-500">
                                <th class="py-3 px-6">Product Item</th>
                                <th class="py-3 px-4 text-center w-36">Quantity</th>
                                <th class="py-3 px-4 text-right w-44">Unit Price ($)</th>
                                <th class="py-3 px-6 text-right w-40">Subtotal ($)</th>
                                <th class="py-3 px-4 text-center w-16">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @if($order->items->count() > 0)
                                @foreach($order->items as $item)
                                    <tr class="item-row hover:bg-slate-50 transition-colors" data-item-id="{{ $item->id }}">
                                        <td class="py-4 px-6">
                                            <div class="font-semibold text-gray-800">{{ $item->product ? $item->product->name : 'Unknown Product' }}</div>
                                            @if($item->product && $item->product->description)
                                                <div class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ $item->product->description }}</div>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4">
                                            <input type="number" 
                                                   name="items[{{ $item->id }}][quantity]" 
                                                   value="{{ $item->quantity }}" 
                                                   min="1" 
                                                   class="item-qty w-full border border-gray-200 py-1.5 px-3 rounded-lg text-center font-semibold focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                                   {{ in_array($order->status, ['paid', 'shipped', 'delivered']) ? 'disabled' : '' }}>
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                                                <input type="number" 
                                                       step="0.01" 
                                                       name="items[{{ $item->id }}][unit_price]" 
                                                       value="{{ $item->unit_price ?? ($item->product ? $item->product->price : 0.00) }}" 
                                                       min="0" 
                                                       class="item-price w-full border border-gray-200 py-1.5 pl-7 pr-3 rounded-lg text-right font-semibold focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                                       {{ in_array($order->status, ['paid', 'shipped', 'delivered']) ? 'disabled' : '' }}>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 text-right font-bold text-gray-800 item-subtotal">
                                            ${{ number_format(($item->unit_price ?? ($item->product ? $item->product->price : 0.00)) * $item->quantity, 2) }}
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            @if($order->status != 'paid' && $order->status != 'delivered')
                                                <button type="button" 
                                                        onclick="openDeleteModal('{{ route('orders.items.destroy', [$order, $item]) }}')" 
                                                        class="text-red-500 hover:text-red-700 transition-colors">
                                                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            @else
                                                <span class="text-gray-400">
                                                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="py-8 px-6 text-center text-gray-400">
                                        No items registered for this order.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="bg-gray-50 p-6 border-t border-gray-100 flex flex-col gap-2">
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Tax Information</div>
                    <div class="text-sm text-gray-700">
                        @if($taxPercent > 0)
                            A global <strong>{{ $taxName }}</strong> of <strong>{{ $taxPercent }}%</strong> is configured and will be applied to the subtotal.
                        @else
                            No global tax is currently configured.
                        @endif
                    </div>
                </div>

                <div class="bg-gray-50 p-6 flex flex-col md:flex-row justify-between items-center border-t border-gray-100 gap-4">
                    <div class="text-sm text-gray-500 text-center md:text-left">
                        💡 Admin edits automatically recalculate sub-totals and grand-totals dynamically.
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-bold uppercase tracking-wider text-gray-500">Order Grand Total:</span>
                        <div class="relative w-44">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-600 font-extrabold text-xl">$</span>
                            <input type="number" 
                                   step="0.01" 
                                   id="total_amount" 
                                   name="total_amount" 
                                   value="{{ $order->total_amount ?? 0.00 }}" 
                                   class="w-full border-none bg-transparent py-1.5 pl-8 pr-1 text-right font-extrabold text-2xl text-blue-900 focus:ring-0 outline-none select-none" 
                                   readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit action controls -->
            @if($order->status != 'delivered')
                @if(!in_array($order->status, ['paid', 'shipped']) && $order->items->count() == 0)
                    <div class="bg-amber-50 text-amber-600 p-4 rounded-xl text-center font-semibold mt-6 border border-amber-200">
                        ⚠️ Order details cannot be updated because all items have been removed.
                    </div>
                @else
                    <div class="flex gap-4 mt-6">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-md transition-all duration-150 hover:-translate-y-0.5 active:translate-y-0 text-center">
                            @if(in_array($order->status, ['paid', 'shipped']))
                                💾 Update Workflow Status
                            @else
                                💾 Update Order Details & Recalculate Total
                            @endif
                        </button>
                    </div>
                @endif
            @endif
            
            @if(in_array($order->status, ['paid', 'shipped']))
                <div class="bg-slate-100 text-slate-600 p-4 rounded-xl text-center font-semibold mt-4">
                    🔒 Items and details are locked because the order has already been finalized/paid. You can only update the Workflow Status.
                </div>
            @elseif($order->status == 'delivered')
                <div class="bg-slate-100 text-slate-600 p-4 rounded-xl text-center font-semibold mt-4">
                    🔒 This order is Delivered and completely locked. No further changes can be made.
                </div>
            @endif
        </form>
    </div>

    <!-- Right 1 Column: Billing, Payment Actions & Status Badges -->
    <div class="space-y-8">
        <!-- Stripe Payments Operations Card -->
        @if($order->items->count() > 0)
        <div class="bg-white shadow-md rounded-xl border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-lg">
            <div class="bg-gradient-to-r from-indigo-800 to-blue-800 px-6 py-4 text-white">
                <h3 class="font-bold text-lg tracking-wide flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Stripe Checkout Gateway
                </h3>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Current Checkout Status -->
                <div class="border-b border-gray-100 pb-4">
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Payment Status</div>
                    <div class="mt-2 flex items-center">
                        @if(in_array($order->status, ['paid', 'shipped', 'delivered']))
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                <span class="w-2 h-2 mr-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Fully Paid &amp; Settlement Verified
                            </span>
                        @elseif($order->status == 'quoted')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">
                                <span class="w-2 h-2 mr-1.5 rounded-full bg-indigo-500"></span>
                                Quoted &amp; Sent to Customer
                            </span>
                        @elseif($order->status == 'payment_failed')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                <span class="w-2 h-2 mr-1.5 rounded-full bg-red-500"></span>
                                Payment Failed
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                <span class="w-2 h-2 mr-1.5 rounded-full bg-amber-500"></span>
                                Waiting Approval
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Payment Session Details -->
                @if($order->stripe_payment_link)
                    <div class="space-y-4">
                        @if(!in_array($order->status, ['paid', 'shipped', 'delivered']))
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Generated Payment Link</div>
                            <div class="mt-2 flex items-center gap-2">
                                <input type="text" id="stripeLinkInput" readonly value="{{ $order->stripe_payment_link }}" class="flex-1 bg-gray-50 border border-gray-200 text-xs py-2 px-3 rounded-lg outline-none font-mono">
                                <button onclick="copyPaymentLink()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 p-2 rounded-lg transition-colors" title="Copy to clipboard">
                                    <svg id="copyIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                    <svg id="checkIcon" class="w-4 h-4 text-green-600 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            </div>
                        </div>
                        @endif

                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Stripe Session ID</div>
                            <div class="text-xs font-mono text-gray-500 mt-1 break-all bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                {{ $order->stripe_session_id }}
                            </div>
                        </div>

                        @if($order->stripe_payment_intent_id)
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Payment Intent ID</div>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="text-xs font-mono text-gray-500 break-all bg-gray-50 p-2.5 rounded-lg border border-gray-100 flex-1">
                                    {{ $order->stripe_payment_intent_id }}
                                </div>
                                @if(!str_contains($order->stripe_payment_intent_id ?? '', 'mock') && !str_contains($order->stripe_payment_intent_id ?? '', 'fallback'))
                                <a href="https://dashboard.stripe.com/payments/{{ $order->stripe_payment_intent_id }}"
                                   target="_blank"
                                   class="flex-shrink-0 bg-gray-100 hover:bg-gray-200 text-gray-600 p-2 rounded-lg transition-colors"
                                   title="View in Stripe Dashboard">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($order->paid_at)
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Confirmed At</div>
                            <div class="text-sm text-gray-600 mt-1 font-medium">
                                {{ $order->paid_at->format('M d, Y \a\t h:i A') }}
                            </div>
                        </div>
                        @endif

                        @if(!in_array($order->status, ['paid', 'shipped', 'delivered']))
                        <div class="flex flex-col gap-2 pt-2">
                            <a href="{{ $order->stripe_payment_link }}" target="_blank"
                               class="w-full text-white font-bold py-2.5 px-4 rounded-xl text-center shadow transition-all flex justify-center items-center"
                               style="background:linear-gradient(135deg,#14532d,#15803d);">
                                🌐 Launch Stripe Checkout
                                <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        </div>
                        @endif
                    </div>
                @else
                    <div class="text-sm text-gray-500 text-center py-4 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                        Payment link not generated yet. Approve the order to build session.
                    </div>
                @endif

                <!-- Payment Generation Actions -->
                @if($order->status == 'pending_approval')
                    <div class="border-t border-gray-100 pt-6">
                        <button type="button" onclick="document.getElementById('checkoutConfirmModal').classList.remove('hidden')" class="w-full text-white font-extrabold py-3.5 px-6 rounded-xl shadow-md transition-all flex justify-center items-center hover:-translate-y-0.5 active:translate-y-0"
                                style="background:linear-gradient(135deg,#14532d,#22c55e);">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Approve &amp; Dispatch Billing Link
                        </button>
                        <p class="text-xs text-gray-400 mt-2.5 text-center">
                            Creates a Stripe session for all items and notifies the customer via WhatsApp.
                        </p>
                    </div>
                @endif
            </div>
        </div>
        @else
        <div class="bg-white shadow-md rounded-xl border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-lg p-6 text-center text-gray-500">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            <p class="text-lg font-semibold text-gray-600">No Items in Order</p>
            <p class="text-sm mt-1">Stripe Checkout is disabled because all items have been removed from this order.</p>
        </div>
        @endif

        <!-- WhatsApp Messaging Profile Summary -->
        <div class="bg-white shadow-md rounded-xl border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-lg">
            <div class="bg-gradient-to-r from-emerald-800 to-teal-800 px-6 py-4 text-white">
                <h3 class="font-bold text-lg tracking-wide flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    WhatsApp Alert Dispatcher
                </h3>
            </div>
            
            <div class="p-6 space-y-4">
                <div class="text-sm text-gray-600">
                    Active Meta Access Tokens and verified Catalog parameters are automatically scanned to trigger real-time WhatsApp updates.
                </div>
                
                <div class="bg-slate-50 p-4 rounded-xl border border-gray-100 text-xs space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-400 font-semibold">Phone ID:</span>
                        <span class="text-gray-700 font-mono">{{ env('WHATSAPP_PHONE_NUMBER_ID', 'Not Found') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400 font-semibold">Verify Token:</span>
                        <span class="text-gray-700 font-mono">{{ env('WHATSAPP_VERIFY_TOKEN', 'Not Found') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400 font-semibold">Catalog ID:</span>
                        <span class="text-gray-700 font-mono">{{ env('META_CATALOG_ID', 'Not Found') }}</span>
                    </div>
                </div>

                @if($order->status == 'quoted' && $order->stripe_payment_link)
                    <button type="button" onclick="document.getElementById('redispatchConfirmModal').classList.remove('hidden')" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold py-2 rounded-lg text-xs text-center border border-slate-200 transition-colors flex justify-center items-center">
                        🔄 Re-dispatch WhatsApp Notification
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    // Live JavaScript pricing dynamic totalizer logic
    document.addEventListener('DOMContentLoaded', function() {
        const priceInputs = document.querySelectorAll('.item-price');
        const qtyInputs = document.querySelectorAll('.item-qty');
        const totalAmountInput = document.getElementById('total_amount');
        
        const globalTaxPercent = {{ $taxPercent > 0 ? $taxPercent : 0 }};

        function recalculateTotal() {
            let subtotal = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                const qty = parseInt(row.querySelector('.item-qty').value) || 0;
                const rowSubtotal = price * qty;
                
                row.querySelector('.item-subtotal').textContent = '$' + rowSubtotal.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                subtotal += rowSubtotal;
            });
            
            let taxAmount = 0;
            if (globalTaxPercent > 0) {
                taxAmount = (subtotal * globalTaxPercent) / 100;
            }

            const total = subtotal + taxAmount;
            
            if (totalAmountInput) {
                totalAmountInput.value = total.toFixed(2);
            }
        }

        priceInputs.forEach(input => {
            input.addEventListener('input', recalculateTotal);
            input.addEventListener('change', recalculateTotal);
        });
        
        qtyInputs.forEach(input => {
            input.addEventListener('input', recalculateTotal);
            input.addEventListener('change', recalculateTotal);
        });

        // Initial check to make sure calculations are clean
        recalculateTotal();
    });

    // Copy to clipboard utility function
    function copyPaymentLink() {
        const copyText = document.getElementById("stripeLinkInput");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices
        
        navigator.clipboard.writeText(copyText.value).then(() => {
            const copyIcon = document.getElementById("copyIcon");
            const checkIcon = document.getElementById("checkIcon");
            
            copyIcon.classList.add("hidden");
            checkIcon.classList.remove("hidden");
            
            setTimeout(() => {
                copyIcon.classList.remove("hidden");
                checkIcon.classList.add("hidden");
            }, 2000);
        });
    }

    function openDeleteModal(url) {
        document.getElementById('deleteForm').action = url;
        document.getElementById('deleteConfirmModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteConfirmModal').classList.add('hidden');
    }
</script>

<!-- Generate Link Modal -->
<div id="checkoutConfirmModal" class="hidden fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('checkoutConfirmModal').classList.add('hidden')"></div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        <div class="sm:flex sm:items-start">
          <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              Generate Checkout Link
            </h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                Are you sure you want to generate a checkout link and send a WhatsApp alert to the customer?
              </p>
            </div>
          </div>
        </div>
      </div>
      <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
        <form action="{{ route('orders.send-payment-link', $order) }}" method="POST">
            @csrf
            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                Confirm & Send
            </button>
        </form>
        <button type="button" onclick="document.getElementById('checkoutConfirmModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
          Cancel
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Re-Dispatch Modal -->
<div id="redispatchConfirmModal" class="hidden fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('redispatchConfirmModal').classList.add('hidden')"></div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        <div class="sm:flex sm:items-start">
          <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </div>
          <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              Re-dispatch Notification
            </h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                Are you sure you want to resend the payment link WhatsApp notification to this customer?
              </p>
            </div>
          </div>
        </div>
      </div>
      <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
        <form action="{{ route('orders.send-payment-link', $order) }}" method="POST">
            @csrf
            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                Yes, Resend Alert
            </button>
        </form>
        <button type="button" onclick="document.getElementById('redispatchConfirmModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
          Cancel
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Delete Item Modal -->
<div id="deleteConfirmModal" class="hidden fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeDeleteModal()"></div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        <div class="sm:flex sm:items-start">
          <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </div>
          <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              Remove Order Item
            </h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                Are you sure you want to remove this item from the order? This action cannot be undone.
              </p>
            </div>
          </div>
        </div>
      </div>
      <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                Yes, Remove Item
            </button>
        </form>
        <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
          Cancel
        </button>
      </div>
    </div>
  </div>
</div>

@endsection
