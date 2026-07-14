<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add Stripe Payment Intent ID to orders.
     * This allows admins to reconcile payments and issue refunds
     * via the Stripe API using the payment_intent ID.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Stripe Payment Intent ID (pi_...) — returned inside the checkout session
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_session_id');

            // Track when payment was confirmed (either by webhook or redirect fallback)
            $table->timestamp('paid_at')->nullable()->after('stripe_payment_intent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['stripe_payment_intent_id', 'paid_at']);
        });
    }
};
