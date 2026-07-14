<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Checkout Sandbox (Simulated)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-tr from-slate-900 via-indigo-950 to-slate-900">
    
    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2">
        
        <!-- Left Column: Order Summary (Branded Indigo Sidebar) -->
        <div class="bg-gradient-to-b from-indigo-900 to-slate-950 p-8 text-white flex flex-col justify-between">
            <div>
                <!-- Brand logo placeholder -->
                <div class="flex items-center gap-2 mb-8">
                    <span class="p-1.5 bg-indigo-500 rounded-lg text-white font-extrabold text-sm tracking-widest uppercase">MyStore</span>
                    <span class="text-xs tracking-widest text-indigo-300 font-bold uppercase">Checkout</span>
                </div>

                <div class="space-y-1">
                    <span class="text-xs uppercase tracking-wider text-indigo-300 font-semibold">Payment Request</span>
                    <h1 class="text-3xl font-extrabold tracking-tight">${{ number_format($order->total_amount, 2) }}</h1>
                    <p class="text-xs text-indigo-200 mt-1">Order #{{ $order->id }} • {{ $order->customer_name ?? 'Valued Customer' }}</p>
                </div>

                <!-- Product line items list -->
                <div class="mt-8 space-y-4 border-t border-indigo-950 pt-6">
                    <div class="text-xs uppercase tracking-wider text-indigo-300 font-bold mb-2">Order Summary</div>
                    <div class="max-h-60 overflow-y-auto space-y-3 pr-2 scrollbar-thin scrollbar-thumb-indigo-800">
                        @foreach($order->items as $item)
                            <div class="flex justify-between items-center text-sm">
                                <div>
                                    <span class="font-semibold">{{ $item->product ? $item->product->name : 'Custom Item' }}</span>
                                    <span class="text-xs text-indigo-300 block">Qty: {{ $item->quantity }}</span>
                                </div>
                                <span class="font-bold text-indigo-100">${{ number_format(($item->unit_price ?? ($item->product ? $item->product->price : 0.00)) * $item->quantity, 2) }}</span>
                            </div>
                        @endforeach
                        @if($order->tax_amount > 0)
                            @php
                                $subtotal = 0;
                                foreach($order->items as $item) {
                                    $subtotal += ($item->unit_price ?? ($item->product ? $item->product->price : 0.00)) * $item->quantity;
                                }
                                $taxVal = $order->tax_type == 'percent' ? ($subtotal * $order->tax_amount / 100) : $order->tax_amount;
                            @endphp
                            <div class="flex justify-between items-center text-sm mt-3 pt-3 border-t border-indigo-900">
                                <div>
                                    <span class="font-semibold">{{ $order->tax_name ?: 'Tax' }}</span>
                                    <span class="text-xs text-indigo-300 block">@if($order->tax_type == 'percent') {{ floatval($order->tax_amount) }}% @else Fixed @endif</span>
                                </div>
                                <span class="font-bold text-indigo-100">${{ number_format($taxVal, 2) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer Details -->
            <div class="mt-8 border-t border-indigo-950 pt-4 flex justify-between items-center text-xs text-indigo-400">
                <span class="flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.9L10 9.554L17.834 4.9A2 2 0 0016 4H4a2 2 0 00-1.834.9zM2 6.641V14a2 2 0 002 2h12a2 2 0 002-2V6.641l-8 4.792l-8-4.792z" clip-rule="evenodd"></path></svg>
                    {{ $order->customer_email ?? 'billing@mystore.local' }}
                </span>
                <span class="flex items-center">
                    🔒 Sandbox Encrypted
                </span>
            </div>
        </div>

        <!-- Right Column: Payments form -->
        <div class="p-8 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 tracking-tight">Credit Card Payment</h2>
                    <span class="text-xs uppercase bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded">Simulated Mode</span>
                </div>

                <!-- Alert message explaining simulation -->
                <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-xl text-xs text-indigo-800 space-y-1 mb-6 leading-relaxed">
                    <div class="font-bold flex items-center">
                        <svg class="w-4 h-4 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Stripe Sandbox Environment
                    </div>
                    <p>This page simulates a live Stripe Checkout session. Clicking "Complete Payment" will securely update the order database and trigger successful payment redirects.</p>
                </div>

                <!-- Simulation checkout form -->
                <form action="{{ route('orders.mock-pay.submit', $order) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $sessionId }}">

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Email Address</label>
                        <input type="email" readonly value="{{ $order->customer_email ?? 'customer@example.com' }}" class="w-full bg-gray-50 border border-gray-200 py-2 px-3.5 rounded-lg text-sm text-gray-600 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Card details</label>
                        <div class="border border-gray-200 rounded-lg overflow-hidden divide-y divide-gray-100 bg-white">
                            <div class="relative">
                                <input type="text" value="4242 •••• •••• 4242" readonly class="w-full py-2.5 pl-4 pr-10 text-sm outline-none bg-white font-mono text-gray-700">
                                <span class="absolute inset-y-0 right-3 flex items-center">
                                    <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
                                </span>
                            </div>
                            <div class="grid grid-cols-2 divide-x divide-gray-100">
                                <input type="text" value="12 / 29" readonly class="w-full py-2.5 px-4 text-sm outline-none bg-white font-mono text-gray-700 text-center">
                                <input type="text" value="CVC" readonly class="w-full py-2.5 px-4 text-sm outline-none bg-white font-mono text-gray-700 text-center">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Cardholder Name</label>
                        <input type="text" value="{{ $order->customer_name ?? 'John Doe' }}" readonly class="w-full bg-gray-50 border border-gray-200 py-2 px-3.5 rounded-lg text-sm text-gray-600 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Billing Address</label>
                        <input type="text" value="United States" readonly class="w-full bg-gray-50 border border-gray-200 py-2 px-3.5 rounded-lg text-sm text-gray-600 outline-none">
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-md text-center transition-all duration-150 hover:-translate-y-0.5 active:translate-y-0 mt-6">
                        💳 Complete Simulated Payment (${{ number_format($order->total_amount, 2) }})
                    </button>
                </form>
            </div>

            <!-- Back out cancel option -->
            <div class="mt-6 text-center">
                <a href="{{ route('orders.show', $order) }}" class="text-xs font-semibold text-gray-400 hover:text-gray-600 transition-colors">
                    Cancel and return to store
                </a>
            </div>

        </div>

    </div>

</body>
</html>
