<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

class StripeService
{
    protected string $secretKey;
    protected string $currency;
    protected bool   $isMock;

    public function __construct()
    {
        $this->secretKey = config('stripe.secret', '');
        $this->currency  = config('stripe.currency', 'usd');

        // Mock mode: key is empty OR contains the word "mock" (dev/test fallback)
        $this->isMock = empty($this->secretKey)
            || str_contains(strtolower($this->secretKey), 'mock');

        if (! $this->isMock) {
            Stripe::setApiKey($this->secretKey);

            $apiVersion = config('stripe.api_version');
            if ($apiVersion) {
                Stripe::setApiVersion($apiVersion);
            }
        }
    }

    /**
     * Create a Stripe Checkout Session for the given order.
     *
     * Returns an array with:
     *   - url            (string)  Redirect URL for the customer
     *   - session_id     (string)  Stripe Checkout Session ID (cs_...)
     *   - payment_intent (string|null) Payment Intent ID (pi_...) — null for mock
     *   - is_mock        (bool)    Whether this is a local simulation
     */
    public function createCheckoutSession(Order $order): array
    {
        if ($this->isMock) {
            return $this->buildMockSession($order, 'mock_sess_');
        }

        try {
            $lineItems = $this->buildLineItems($order);

            $params = [
                'mode'                => 'payment',
                'payment_method_types'=> ['card'],
                'line_items'          => $lineItems,
                'customer_email'      => $order->customer_email ?: null,
                'success_url'         => route('orders.stripe-success', ['order' => $order->id])
                                         . '?session_id={CHECKOUT_SESSION_ID}'
                                         . '&payment_intent={PAYMENT_INTENT}',
                'cancel_url'          => route('orders.show', ['order' => $order->id]),
                'metadata'            => [
                    'order_id'         => $order->id,
                    'customer_phone'   => $order->customer_phone,
                ],
            ];

            Log::info("StripeService: Creating Checkout Session for Order #{$order->id}", [
                'currency'   => $this->currency,
                'line_items' => count($lineItems),
            ]);

            $session = Session::create($params);

            Log::info("StripeService: Session created for Order #{$order->id}", [
                'session_id'     => $session->id,
                'payment_intent' => $session->payment_intent,
            ]);

            return [
                'url'            => $session->url,
                'session_id'     => $session->id,
                'payment_intent' => $session->payment_intent,
                'is_mock'        => false,
            ];

        } catch (ApiErrorException $e) {
            Log::error("StripeService: Stripe API error for Order #{$order->id}: " . $e->getMessage());
            // Graceful fallback to mock so dev is never blocked
            return $this->buildMockSession($order, 'fallback_sess_');

        } catch (\Exception $e) {
            Log::error("StripeService: Unexpected error for Order #{$order->id}: " . $e->getMessage());
            return $this->buildMockSession($order, 'fallback_sess_');
        }
    }

    /**
     * Retrieve a Checkout Session from Stripe by ID.
     * Used by webhook to expand the payment_intent.
     */
    public function retrieveSession(string $sessionId): ?array
    {
        if ($this->isMock || str_starts_with($sessionId, 'mock_') || str_starts_with($sessionId, 'fallback_')) {
            return null; // Nothing to retrieve in mock mode
        }

        try {
            $session = Session::retrieve([
                'id'     => $sessionId,
                'expand' => ['payment_intent'],
            ]);

            return [
                'session_id'     => $session->id,
                'payment_status' => $session->payment_status,
                'payment_intent' => $session->payment_intent?->id,
                'amount_total'   => $session->amount_total,
                'currency'       => $session->currency,
                'customer_email' => $session->customer_email,
            ];

        } catch (ApiErrorException $e) {
            Log::error("StripeService: Could not retrieve session {$sessionId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Build Stripe line_items array from order items.
     */
    protected function buildLineItems(Order $order): array
    {
        $lineItems = [];
        $subtotal = 0;

        foreach ($order->items as $item) {
            $productName  = $item->product ? $item->product->name : 'Custom Item';
            $unitPrice    = (float) ($item->unit_price ?? ($item->product?->price ?? 0.00));
            $amountCents  = max(1, (int) round($unitPrice * 100)); // min 1 cent
            
            $subtotal += $unitPrice * max(1, (int) $item->quantity);

            $lineItems[] = [
                'price_data' => [
                    'currency'     => $this->currency,
                    'unit_amount'  => $amountCents,
                    'product_data' => [
                        'name'        => $productName,
                        'description' => $item->product?->description ?: null,
                    ],
                ],
                'quantity' => max(1, (int) $item->quantity),
            ];
        }
        
        // Add tax or remaining balance as a separate line item if applicable
        $taxVal = 0;
        if (!empty($order->tax_amount) && $order->tax_amount > 0) {
            $taxVal = $order->tax_type == 'percent' ? ($subtotal * $order->tax_amount / 100) : (float) $order->tax_amount;
        } else {
            // Fallback: if tax_amount isn't saved but total_amount includes it
            $difference = (float) $order->total_amount - $subtotal;
            if ($difference > 0) {
                $taxVal = $difference;
            }
        }

        if ($taxVal > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => $this->currency,
                    'unit_amount'  => max(1, (int) round($taxVal * 100)),
                    'product_data' => [
                        'name'        => $order->tax_name ?: 'Tax',
                        'description' => 'Order Tax',
                    ],
                ],
                'quantity' => 1,
            ];
        }

        // Fallback: if no items and no tax, create a single line item for the total
        if (empty($lineItems)) {
            $total = (float) ($order->total_amount ?? 1.00);
            $lineItems[] = [
                'price_data' => [
                    'currency'     => $this->currency,
                    'unit_amount'  => max(1, (int) round($total * 100)),
                    'product_data' => ['name' => "Order #{$order->id}"],
                ],
                'quantity' => 1,
            ];
        }

        return $lineItems;
    }

    /**
     * Build a local mock/simulation checkout URL.
     */
    protected function buildMockSession(Order $order, string $prefix = 'mock_sess_'): array
    {
        $mockSessionId = $prefix . uniqid() . '_' . $order->id;
        $mockUrl = route('orders.mock-pay', [
            'order'      => $order->id,
            'session_id' => $mockSessionId,
        ]);

        Log::info("StripeService [MOCK]: Returning simulated checkout for Order #{$order->id}", [
            'session_id' => $mockSessionId,
        ]);

        return [
            'url'            => $mockUrl,
            'session_id'     => $mockSessionId,
            'payment_intent' => null,
            'is_mock'        => true,
        ];
    }

    /**
     * Determine whether the service is running in mock/simulation mode.
     */
    public function isMockMode(): bool
    {
        return $this->isMock;
    }

    /**
     * Return the configured currency code.
     */
    public function getCurrency(): string
    {
        return strtoupper($this->currency);
    }

    /**
     * Determine key mode: 'mock', 'test', or 'live'.
     */
    public function getKeyMode(): string
    {
        if ($this->isMock) {
            return 'mock';
        }

        $key = config('stripe.key', '');

        if (str_starts_with($key, 'pk_live_')) {
            return 'live';
        }

        return 'test';
    }
}
