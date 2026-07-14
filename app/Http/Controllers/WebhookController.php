<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class WebhookController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // WhatsApp / Meta Webhook
    // ─────────────────────────────────────────────────────────

    public function handleWhatsApp(Request $request)
    {
        Log::info('WhatsApp Webhook received', $request->all());
        // Future: map WhatsApp catalog message to Order::create([...])
        return response()->json(['status' => 'success']);
    }

    // ─────────────────────────────────────────────────────────
    // Stripe Webhook
    // ─────────────────────────────────────────────────────────

    public function handleStripe(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('stripe.webhook_secret', '');

        $isMockSecret = empty($secret) || str_contains(strtolower($secret), 'mock');

        // ── Signature Verification ────────────────────────────
        if (!$isMockSecret) {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $secret);
            } catch (SignatureVerificationException $e) {
                Log::warning('Stripe Webhook: Invalid signature — ' . $e->getMessage());
                return response('Invalid webhook signature.', 400);
            } catch (\UnexpectedValueException $e) {
                Log::warning('Stripe Webhook: Invalid payload — ' . $e->getMessage());
                return response('Invalid payload.', 400);
            }
        } else {
            // Mock / dev mode: skip signature check, parse JSON directly
            Log::info('Stripe Webhook [MOCK]: Signature verification skipped (mock secret).');
            $data = json_decode($payload, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($data['type'])) {
                return response('Invalid JSON payload.', 400);
            }

            // Build a simple event-like object from raw JSON
            $event = (object) [
                'type' => $data['type'],
                'data' => (object) [
                    'object' => (object) ($data['data']['object'] ?? []),
                ],
            ];
        }

        Log::info("Stripe Webhook received: {$event->type}");

        // ── Event Handlers ────────────────────────────────────
        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event->data->object),
            default => Log::info("Stripe Webhook: Unhandled event type [{$event->type}]"),
        };

        return response('Webhook handled.', 200);
    }

    // ─────────────────────────────────────────────────────────
    // Private Handlers
    // ─────────────────────────────────────────────────────────

    /**
     * Handle checkout.session.completed — mark order as paid.
     */
    private function handleCheckoutCompleted(object $session): void
    {
        $sessionId = $session->id ?? null;
        $paymentIntent = $session->payment_intent ?? null;
        $customerEmail = $session->customer_email ?? null;

        if (!$sessionId) {
            Log::warning('Stripe Webhook: checkout.session.completed missing session ID.');
            return;
        }

        $order = Order::where('stripe_session_id', $sessionId)->first();

        if (!$order) {
            Log::warning("Stripe Webhook: No order found for session [{$sessionId}].");
            return;
        }

        if ($order->status !== 'paid') {
            $updateData = [
                'status' => 'paid',
                'paid_at' => now(),
            ];

            if ($paymentIntent) {
                $updateData['stripe_payment_intent_id'] = $paymentIntent;
            }

            $order->update($updateData);

            $whatsAppService = new \App\Services\WhatsAppService();
            $msg = "🎉 Great news! Your payment for Order #{$order->order_number} has been received successfully.\n\nWe are now processing your order and will let you know once it's on its way. Thank you for shopping with us!";
            $whatsAppService->sendTextMessage($order->customer_phone, $msg);

            Log::info("Stripe Webhook: Order #{$order->id} marked as PAID.", [
                'session_id' => $sessionId,
                'payment_intent' => $paymentIntent,
                'customer_email' => $customerEmail,
            ]);
        } else {
            Log::info("Stripe Webhook: Order #{$order->id} is already PAID.");
        }
    }

    /**
     * Handle payment_intent.payment_failed — mark order as payment_failed.
     */
    private function handlePaymentFailed(object $paymentIntent): void
    {
        $intentId = $paymentIntent->id ?? null;

        if (!$intentId) {
            return;
        }

        $order = Order::where('stripe_payment_intent_id', $intentId)->first();

        if (!$order) {
            // Also try matching by session metadata (less reliable, best-effort)
            Log::warning("Stripe Webhook: payment_intent.payment_failed — no order found for intent [{$intentId}].");
            return;
        }

        // Only move to failed if not already paid
        if ($order->status !== 'paid') {
            $order->update(['status' => 'payment_failed']);

            Log::warning("Stripe Webhook: Order #{$order->id} payment FAILED.", [
                'payment_intent' => $intentId,
                'failure_reason' => $paymentIntent->last_payment_error?->message ?? 'Unknown',
            ]);
        }
    }
}
