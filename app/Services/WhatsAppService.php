<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $accessToken;
    protected $phoneNumberId;
    protected $baseUrl;
    protected $isMock;

    public function __construct()
    {
        $this->accessToken = env('WHATSAPP_ACCESS_TOKEN');
        $this->phoneNumberId = env('WHATSAPP_PHONE_NUMBER_ID');
        $this->baseUrl = "https://graph.facebook.com/v18.0";

        // Determine if we should mock/simulate sending messages
        $this->isMock = empty($this->accessToken) || empty($this->phoneNumberId);
    }

    /**
     * Send order approval and Stripe payment link message to customer
     */
    public function sendPaymentLinkMessage(Order $order, string $paymentLink): bool
    {
        $customerName = $order->customer_name ?? 'Valued Customer';
        $customerPhone = $order->customer_phone;
        $orderId = $order->order_number;

        // Build the text message body
        $body = "👋 Hello *{$customerName}*!\n\n";
        $body .= "Your Order *#{$orderId}* has been approved and quoted! 🎉\n\n";
        $body .= "*Order Summary:*\n";

        foreach ($order->items as $item) {
            $productName = $item->product ? $item->product->name : 'Custom Item';
            $qty = $item->quantity;
            $price = number_format($item->unit_price ?? ($item->product ? $item->product->price : 0.00), 2);
            $body .= "• {$productName} x {$qty} (\${$price})\n";
        }

        $totalAmount = number_format($order->total_amount, 2);
        $body .= "\n*Total Amount:* \${$totalAmount}\n\n";
        $body .= "Please complete your payment securely using this Stripe payment link:\n";
        $body .= "🔗 *{$paymentLink}*\n\n";
        $body .= "Thank you for shopping with us! If you have any questions, feel free to message us here.";

        if ($this->isMock) {
            Log::info("WhatsAppService (MOCK SEND) to [{$customerPhone}]:\n" . $body);
            // Append mock log in the database or session to display feedback if needed, but logging to laravel.log is standard
            return true;
        }

        try {
            // Clean phone number (WhatsApp API requires E.164 without '+' or leading '00')
            $cleanPhone = preg_replace('/[^0-9]/', '', $customerPhone);

            // If the phone number is too short or doesn't have country code, let's keep it as is but warn
            if (strlen($cleanPhone) < 10) {
                Log::warning("WhatsAppService: Customer phone '{$customerPhone}' might be invalid for WhatsApp.");
            }

            $endpoint = "{$this->baseUrl}/{$this->phoneNumberId}/messages";

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $cleanPhone,
                'type' => 'text',
                'text' => [
                    'preview_url' => true,
                    'body' => $body
                ]
            ];

            Log::info("WhatsAppService: Dispatching payment link message to {$cleanPhone} via Cloud API...");

            $response = Http::withToken($this->accessToken)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                Log::info("WhatsAppService: Payment link message sent to {$cleanPhone} successfully.");
                return true;
            } else {
                Log::error("WhatsAppService: Meta API error sending message: " . $response->body());
                // Don't block order flow if WhatsApp API fails - we fall back to logging so the link is still generated
                return false;
            }

        } catch (\Exception $e) {
            Log::error("WhatsAppService: Exception during message send: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a generic text message
     */
    public function sendTextMessage(string $customerPhone, string $message): bool
    {
        if ($this->isMock) {
            Log::info("WhatsAppService (MOCK SEND) to [{$customerPhone}]:\n" . $message);
            return true;
        }

        try {
            $cleanPhone = preg_replace('/[^0-9]/', '', $customerPhone);

            if (strlen($cleanPhone) < 10) {
                Log::warning("WhatsAppService: Customer phone '{$customerPhone}' might be invalid for WhatsApp.");
            }

            $endpoint = "{$this->baseUrl}/{$this->phoneNumberId}/messages";

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $cleanPhone,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $message
                ]
            ];

            Log::info("WhatsAppService: Dispatching text message to {$cleanPhone} via Cloud API...");

            $response = Http::withToken($this->accessToken)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                Log::info("WhatsAppService: Text message sent to {$cleanPhone} successfully.");
                return true;
            } else {
                Log::error("WhatsAppService: Meta API error sending text message: " . $response->body());
                return false;
            }

        } catch (\Exception $e) {
            Log::error("WhatsAppService: Exception during text message send: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send an interactive list message for store selection
     */
    public function sendStoreListMessage(string $customerPhone, $stores, string $messageText = 'Please select a store to begin placing your order.', int $page = 1): bool
    {
        if ($stores->isEmpty()) {
            return $this->sendTextMessage($customerPhone, "Sorry, there are no stores available at the moment.");
        }

        if ($this->isMock) {
            Log::info("WhatsAppService (MOCK SEND LIST) to [{$customerPhone}]: Displaying store list page {$page}");
            return true;
        }

        try {
            $cleanPhone = preg_replace('/[^0-9]/', '', $customerPhone);

            $endpoint = "{$this->baseUrl}/{$this->phoneNumberId}/messages";

            $rows = [];

            $perPage = 10; // Show up to 10 search results

            $totalPages = ceil($stores->count() / $perPage);
            if ($page < 1)
                $page = 1;
            if ($page > $totalPages)
                $page = $totalPages;

            $offset = ($page - 1) * $perPage;
            $storesOnPage = $stores->slice($offset, $perPage);

            foreach ($storesOnPage as $store) {
                $rows[] = [
                    'id' => 'store_' . $store->id,
                    'title' => substr($store->name, 0, 24), // Max 24 chars for title
                    'description' => substr($store->address ?? 'Select this store', 0, 72) // Max 72 chars
                ];
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $cleanPhone,
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'list',
                    'header' => [
                        'type' => 'text',
                        'text' => 'Welcome!'
                    ],
                    'body' => [
                        'text' => $messageText
                    ],
                    'footer' => [
                        'text' => $stores->count() > 10 ? 'Or type your location' : 'Choose from the list below'
                    ],
                    'action' => [
                        'button' => 'Select Store',
                        'sections' => [
                            [
                                'title' => 'Available Stores',
                                'rows' => $rows
                            ]
                        ]
                    ]
                ]
            ];

            Log::info("WhatsAppService: Dispatching store list message to {$cleanPhone}...");

            $response = Http::withToken($this->accessToken)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                Log::info("WhatsAppService: Store list message sent to {$cleanPhone} successfully.");
                return true;
            } else {
                Log::error("WhatsAppService: Meta API error sending list message: " . $response->body());
                return false;
            }

        } catch (\Exception $e) {
            Log::error("WhatsAppService: Exception during list message send: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a Web-View CTA link message for store selection
     */
    public function sendStoreWebViewMessage(string $customerPhone, string $messageText = "Welcome! Please click the button below to search and select your nearest store."): bool
    {
        try {
            $cleanPhone = preg_replace('/[^0-9]/', '', $customerPhone);
            $endpoint = "{$this->baseUrl}/{$this->phoneNumberId}/messages";

            // Build the URL using Laravel's robust route helper, adding a timestamp to bypass mobile browser cache
            $storeSelectorUrl = route('store.selector', ['phone' => $cleanPhone, 't' => time()]);

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $cleanPhone,
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'cta_url',
                    'header' => [
                        'type' => 'text',
                        'text' => 'Nearest Store'
                    ],
                    'body' => [
                        'text' => $messageText
                    ],
                    'action' => [
                        'name' => 'cta_url',
                        'parameters' => [
                            'display_text' => 'Select Store',
                            'url' => $storeSelectorUrl
                        ]
                    ]
                ]
            ];

            Log::info("WhatsAppService: Dispatching store Web-View message to {$cleanPhone}...");

            $response = Http::withToken($this->accessToken)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                Log::info("WhatsAppService: Store Web-View message sent to {$cleanPhone} successfully.");
                return true;
            } else {
                Log::error("WhatsAppService: Meta API error sending web-view message: " . $response->body());
                return $this->sendTextMessage($customerPhone, "Please reply with your store name to proceed.");
            }

        } catch (\Exception $e) {
            Log::error("WhatsAppService: Exception during web-view message send: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send an interactive catalog message
     */
    public function sendCatalogMessage(string $customerPhone, string $messageText): bool
    {
        if ($this->isMock) {
            Log::info("WhatsAppService (MOCK SEND CATALOG) to [{$customerPhone}]: " . $messageText);
            return true;
        }

        try {
            $cleanPhone = preg_replace('/[^0-9]/', '', $customerPhone);
            $endpoint = "{$this->baseUrl}/{$this->phoneNumberId}/messages";

            // Try to get a product to use as the thumbnail
            $firstProduct = \App\Models\Product::where('is_active', true)->first();

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $cleanPhone,
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'catalog_message',
                    'body' => [
                        'text' => $messageText
                    ],
                    'action' => [
                        'name' => 'catalog_message'
                    ]
                ]
            ];

            // Removed thumbnail to prevent 'Products not found in FB Catalog' error

            Log::info("WhatsAppService: Dispatching catalog message to {$cleanPhone}...");

            $response = Http::withToken($this->accessToken)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                Log::info("WhatsAppService: Catalog message sent to {$cleanPhone} successfully.");
                return true;
            } else {
                Log::error("WhatsAppService: Meta API error sending catalog message: " . $response->body());
                // Fallback to text message if catalog message fails (e.g., catalog not fully approved/linked)
                return $this->sendTextMessage($customerPhone, $messageText . " (Tap the storefront icon at the top of the chat to view our catalog!)");
            }

        } catch (\Exception $e) {
            Log::error("WhatsAppService: Exception during catalog message send: " . $e->getMessage());
            return false;
        }
    }
}
