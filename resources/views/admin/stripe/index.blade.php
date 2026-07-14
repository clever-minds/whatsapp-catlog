@extends('admin.layouts.app')

@section('page-title', 'Stripe Integration')

@section('content')

@php
$modeConfig = [
    'live' => [
        'label'   => 'Live Mode',
        'dot'     => 'bg-emerald-500',
        'badge'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'icon'    => '🟢',
        'desc'    => 'Connected to the Stripe Production environment. Real payments are processed.',
    ],
    'test' => [
        'label'   => 'Test Mode',
        'dot'     => 'bg-blue-500',
        'badge'   => 'bg-blue-50 text-blue-700 border-blue-200',
        'icon'    => '🔵',
        'desc'    => 'Connected to Stripe Test environment. Use test cards like 4242 4242 4242 4242.',
    ],
    'mock' => [
        'label'   => 'Mock / Simulation',
        'dot'     => 'bg-amber-500 animate-pulse',
        'badge'   => 'bg-amber-50 text-amber-700 border-amber-200',
        'icon'    => '🟡',
        'desc'    => 'No real Stripe keys found. A local checkout simulator is used instead.',
    ],
];
$mode = $modeConfig[$keyMode] ?? $modeConfig['mock'];
@endphp

{{-- ── Page heading ─────────────────────────────────────────── --}}
<div class="mb-8 fade-up">
    <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center"
             style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Stripe Integration</h2>
            <p class="text-sm text-gray-500 mt-0.5">Payment gateway status, configuration health & recent transactions.</p>
        </div>
    </div>
</div>

{{-- ── Status cards ─────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

    {{-- Paid Orders --}}
    <div class="fade-up fade-up-d1 bg-white rounded-2xl p-6 shadow-sm border border-gray-100
                hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center"
                 style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <span class="text-xs font-medium text-green-600 bg-green-50 border border-green-100 rounded-full px-2 py-0.5">Paid</span>
        </div>
        <p class="text-3xl font-extrabold text-gray-800 mb-1">{{ $paidOrders }}</p>
        <p class="text-sm font-medium text-gray-500">Paid Orders</p>
    </div>

    {{-- Total Revenue --}}
    <div class="fade-up fade-up-d2 rounded-2xl p-6 shadow-sm border hover:shadow-md hover:-translate-y-0.5 transition-all duration-300"
         style="background:linear-gradient(135deg,#14532d,#15803d);border-color:transparent;">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center"
                 style="background:rgba(255,255,255,.15);">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-xs font-medium rounded-full px-2 py-0.5"
                  style="background:rgba(255,255,255,.2);color:rgba(255,255,255,.9);">{{ $currency }}</span>
        </div>
        <p class="text-3xl font-extrabold text-white mb-1">${{ number_format($totalRevenue, 2) }}</p>
        <p class="text-sm font-medium" style="color:rgba(255,255,255,.7);">Total Revenue</p>
    </div>

    {{-- Pending Checkout --}}
    <div class="fade-up fade-up-d3 bg-white rounded-2xl p-6 shadow-sm border border-gray-100
                hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center"
                 style="background:linear-gradient(135deg,#fffbeb,#fef3c7);">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-xs font-medium text-amber-600 bg-amber-50 border border-amber-100 rounded-full px-2 py-0.5">Awaiting</span>
        </div>
        <p class="text-3xl font-extrabold text-gray-800 mb-1">{{ $pendingPayments }}</p>
        <p class="text-sm font-medium text-gray-500">Awaiting Payment</p>
    </div>

    {{-- Failed Payments --}}
    <div class="fade-up fade-up-d4 bg-white rounded-2xl p-6 shadow-sm border border-gray-100
                hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center"
                 style="background:linear-gradient(135deg,#fff5f5,#fee2e2);">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <span class="text-xs font-medium text-red-600 bg-red-50 border border-red-100 rounded-full px-2 py-0.5">Failed</span>
        </div>
        <p class="text-3xl font-extrabold text-gray-800 mb-1">{{ $failedPayments }}</p>
        <p class="text-sm font-medium text-gray-500">Failed Payments</p>
    </div>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Left: Config Status ──────────────────────────────── --}}
    <div class="lg:col-span-1 space-y-5">

        {{-- Key Mode Card --}}
        <div class="fade-up bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto overflow-y-hidden">
            <div class="px-6 py-4 border-b border-gray-50">
                <h3 class="font-semibold text-gray-800 text-sm">API Key Status</h3>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-3 h-3 rounded-full {{ $mode['dot'] }} flex-shrink-0"></span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold border rounded-full px-3 py-1 {{ $mode['badge'] }}">
                        {{ $mode['icon'] }} {{ $mode['label'] }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 leading-relaxed">{{ $mode['desc'] }}</p>

                @if($keyMode === 'mock')
                <div class="mt-4 p-3 rounded-xl text-xs text-amber-800 bg-amber-50 border border-amber-100 leading-relaxed">
                    <strong>To enable real Stripe:</strong> Add your <code>sk_test_...</code> and <code>pk_test_...</code>
                    keys to <code>.env</code>, then run <code>php artisan config:clear</code>.
                </div>
                @endif
            </div>
        </div>

        {{-- Configuration Details --}}
        <div class="fade-up bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto overflow-y-hidden">
            <div class="px-6 py-4 border-b border-gray-50">
                <h3 class="font-semibold text-gray-800 text-sm">Configuration Details</h3>
            </div>
            <div class="p-6 space-y-3 text-sm">

                {{-- Publishable Key --}}
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-500 font-medium">Publishable Key</span>
                    @php $pubKey = config('stripe.key', ''); @endphp
                    @if($pubKey && !str_contains($pubKey, 'mock'))
                        <span class="text-gray-700 font-mono text-xs">{{ substr($pubKey,0,12) }}...{{ substr($pubKey,-4) }}</span>
                    @else
                        <span class="text-amber-600 text-xs font-semibold">Not set / Mock</span>
                    @endif
                </div>

                {{-- Secret Key --}}
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-500 font-medium">Secret Key</span>
                    @if(!$isMock)
                        <span class="text-green-600 text-xs font-semibold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Configured
                        </span>
                    @else
                        <span class="text-amber-600 text-xs font-semibold">Mock / Not set</span>
                    @endif
                </div>

                {{-- Webhook Secret --}}
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-500 font-medium">Webhook Secret</span>
                    @if($webhookSecretSet)
                        <span class="text-green-600 text-xs font-semibold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Verified
                        </span>
                    @else
                        <span class="text-red-500 text-xs font-semibold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Not configured
                        </span>
                    @endif
                </div>

                {{-- Currency --}}
                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                    <span class="text-gray-500 font-medium">Currency</span>
                    <span class="text-gray-700 font-semibold">{{ $currency }}</span>
                </div>

                {{-- Webhook URL --}}
                <div class="pt-2">
                    <span class="text-gray-500 font-medium text-xs uppercase tracking-wider block mb-1.5">Webhook Endpoint</span>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 bg-gray-50 border border-gray-200 text-xs py-2 px-3 rounded-lg font-mono text-gray-600 overflow-auto">
                            {{ url('/api/webhooks/stripe') }}
                        </code>
                        <button onclick="copyWebhookUrl()" id="webhookCopyBtn"
                                class="flex-shrink-0 bg-gray-100 hover:bg-gray-200 text-gray-600 p-2 rounded-lg transition-colors" title="Copy URL">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Setup Instructions --}}
        @if($keyMode === 'mock')
        <div class="fade-up bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto overflow-y-hidden">
            <div class="px-6 py-4 border-b border-gray-50">
                <h3 class="font-semibold text-gray-800 text-sm">Quick Setup Guide</h3>
            </div>
            <div class="p-6 space-y-4 text-sm text-gray-600">
                <div class="flex gap-3">
                    <span class="w-6 h-6 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">1</span>
                    <p>Create a free account at <a href="https://dashboard.stripe.com" target="_blank" class="text-green-600 underline font-medium">dashboard.stripe.com</a></p>
                </div>
                <div class="flex gap-3">
                    <span class="w-6 h-6 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">2</span>
                    <p>Go to <strong>Developers → API Keys</strong> and copy your <code class="bg-gray-100 px-1 rounded">sk_test_...</code> and <code class="bg-gray-100 px-1 rounded">pk_test_...</code> keys.</p>
                </div>
                <div class="flex gap-3">
                    <span class="w-6 h-6 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">3</span>
                    <p>Paste them into your <code class="bg-gray-100 px-1 rounded">.env</code> file as <code class="bg-gray-100 px-1 rounded">STRIPE_KEY</code> and <code class="bg-gray-100 px-1 rounded">STRIPE_SECRET</code>.</p>
                </div>
                <div class="flex gap-3">
                    <span class="w-6 h-6 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">4</span>
                    <p>Install the Stripe CLI and run:<br><code class="bg-gray-100 px-1 py-0.5 rounded text-xs mt-1 block">stripe listen --forward-to localhost:8000/api/webhooks/stripe</code></p>
                </div>
                <div class="flex gap-3">
                    <span class="w-6 h-6 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">5</span>
                    <p>Copy the printed <code class="bg-gray-100 px-1 rounded">whsec_...</code> value into <code class="bg-gray-100 px-1 rounded">STRIPE_WEBHOOK_SECRET</code>, then run <code class="bg-gray-100 px-1 rounded">php artisan config:clear</code>.</p>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- ── Right: Recent Paid Orders ──────────────────────── --}}
    <div class="lg:col-span-2">
        <div class="fade-up bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto overflow-y-hidden">
            <div class="px-6 py-4 border-b border-gray-50 flex justify-between items-center">
                <h3 class="font-semibold text-gray-800 text-sm">Recent Paid Orders</h3>
                <a href="{{ route('orders.index') }}"
                   class="text-xs font-semibold text-green-600 hover:text-green-800 transition-colors">
                    View All →
                </a>
            </div>

            @if($recentPaidOrders->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 rounded-full mx-auto mb-4 flex items-center justify-center"
                     style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                    <svg class="w-7 h-7 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <p class="text-gray-500 text-sm font-medium">No paid orders yet.</p>
                <p class="text-gray-400 text-xs mt-1">Approved orders will appear here once payment is confirmed.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-bold uppercase tracking-wider text-gray-500">
                            <th class="py-3 px-6">Order</th>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Session ID</th>
                            <th class="py-3 px-4">Paid At</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($recentPaidOrders as $order)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-6">
                                <span class="font-bold text-gray-800">#{{ $order->id }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-medium text-gray-700 text-sm">{{ $order->customer_name ?? '—' }}</div>
                                <div class="text-xs text-gray-400">{{ $order->customer_phone }}</div>
                            </td>
                            <td class="py-4 px-4 font-bold text-green-700">
                                ${{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-mono text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">
                                    {{ $order->stripe_session_id ? substr($order->stripe_session_id, 0, 16) . '...' : '—' }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-xs text-gray-500">
                                {{ $order->paid_at ? $order->paid_at->format('M d, H:i') : $order->updated_at->format('M d, H:i') }}
                            </td>
                            <td class="py-4 px-4 text-right">
                                <a href="{{ route('orders.show', $order) }}"
                                   class="text-xs font-semibold text-green-600 hover:text-green-800 transition-colors">
                                    View →
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Webhook events info --}}
        <div class="fade-up mt-5 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto overflow-y-hidden">
            <div class="px-6 py-4 border-b border-gray-50">
                <h3 class="font-semibold text-gray-800 text-sm">Monitored Webhook Events</h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @php
                $events = [
                    [
                        'event'  => 'checkout.session.completed',
                        'action' => 'Marks order as Paid, saves payment_intent_id',
                        'color'  => 'green',
                    ],
                    [
                        'event'  => 'payment_intent.payment_failed',
                        'action' => 'Marks order as Payment Failed',
                        'color'  => 'red',
                    ],
                ];
                @endphp
                @foreach($events as $ev)
                <div class="flex items-start gap-3 p-4 rounded-xl border"
                     style="border-color: {{ $ev['color'] === 'green' ? '#bbf7d0' : '#fecaca' }};
                            background: {{ $ev['color'] === 'green' ? '#f0fdf4' : '#fff5f5' }};">
                    <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0"
                         style="background:{{ $ev['color'] === 'green' ? '#16a34a' : '#ef4444' }};"></div>
                    <div>
                        <code class="text-xs font-bold block mb-1"
                              style="color:{{ $ev['color'] === 'green' ? '#15803d' : '#b91c1c' }};">
                            {{ $ev['event'] }}
                        </code>
                        <p class="text-xs text-gray-500">{{ $ev['action'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
function copyWebhookUrl() {
    const url = '{{ url('/api/webhooks/stripe') }}';
    navigator.clipboard.writeText(url).then(() => {
        const btn = document.getElementById('webhookCopyBtn');
        btn.innerHTML = `<svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`;
        setTimeout(() => {
            btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>`;
        }, 2000);
    });
}
</script>

@endsection
