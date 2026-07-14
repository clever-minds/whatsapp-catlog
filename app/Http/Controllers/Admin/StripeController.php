<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use App\Models\Order;

class StripeController extends Controller
{
    public function index()
    {
        $stripeService = new StripeService();

        $keyMode         = $stripeService->getKeyMode();           // 'mock' | 'test' | 'live'
        $isMock          = $stripeService->isMockMode();
        $currency        = $stripeService->getCurrency();
        $webhookSecretSet= ! (
            empty(config('stripe.webhook_secret')) ||
            str_contains(strtolower(config('stripe.webhook_secret', '')), 'mock')
        );

        // Revenue stats
        $paidOrders        = Order::where('status', 'paid')->count();
        $totalRevenue      = Order::where('status', 'paid')->sum('total_amount');
        $failedPayments    = Order::where('status', 'payment_failed')->count();
        $pendingPayments   = Order::where('status', 'quoted')->count();
        $recentPaidOrders  = Order::where('status', 'paid')
                                  ->whereNotNull('stripe_session_id')
                                  ->latest('paid_at')
                                  ->take(5)
                                  ->get();

        return view('admin.stripe.index', compact(
            'keyMode',
            'isMock',
            'currency',
            'webhookSecretSet',
            'paidOrders',
            'totalRevenue',
            'failedPayments',
            'pendingPayments',
            'recentPaidOrders'
        ));
    }
}
