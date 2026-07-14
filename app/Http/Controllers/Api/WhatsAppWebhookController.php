<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class WhatsAppWebhookController extends Controller
{
    /**
     * Verify the webhook with Meta
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = env('WHATSAPP_VERIFY_TOKEN');

        if ($mode && $token) {
            if ($mode === 'subscribe' && $token === $verifyToken) {
                Log::info('WhatsApp Webhook Verified!');
                return response($challenge, 200);
            } else {
                return response('Forbidden', 403);
            }
        }
        return response('Bad Request', 400);
    }

    /**
     * Handle incoming WhatsApp messages
     */
    public function handle(Request $request)
    {
        Log::info('Incoming WhatsApp Webhook:', $request->all());

        try {
            $entry = $request->input('entry.0');
            $changes = data_get($entry, 'changes.0');
            $value = data_get($changes, 'value');
            $messages = data_get($value, 'messages.0');

            if ($messages) {
                $contact = data_get($value, 'contacts.0');
                $customerPhone = data_get($contact, 'wa_id');
                $customerName = data_get($contact, 'profile.name');

                if ($customerPhone && $customerName) {
                    Cache::put('whatsapp_name_' . $customerPhone, $customerName, now()->addDays(30));
                }
            }

            if ($messages && data_get($messages, 'type') === 'order') {
                $contact = data_get($value, 'contacts.0');
                $customerName = data_get($contact, 'profile.name', 'Unknown');
                $customerPhone = data_get($contact, 'wa_id');
                
                $orderData = data_get($messages, 'order');
                $productItems = data_get($orderData, 'product_items', []);

                if ($customerPhone && !empty($productItems)) {
                    DB::transaction(function () use ($customerName, $customerPhone, $productItems) {
                        $storeId = Cache::get('whatsapp_store_id_' . $customerPhone);
                        
                        // No fallback to last order. User must re-select store if session expired.
                        
                        $order = Order::create([
                            'customer_name' => $customerName,
                            'customer_phone' => $customerPhone,
                            'store_id' => $storeId,
                            'status' => 'pending_approval',
                            'total_amount' => 0, // Will calculate below
                        ]);

                        if ($storeId) {
                            $store = \App\Models\Store::find($storeId);
                            if ($store && $store->last_number !== $customerPhone) {
                                $store->update(['last_number' => $customerPhone]);
                            }
                        }

                        $totalAmount = 0;

                        foreach ($productItems as $item) {
                            $rawProductId = data_get($item, 'product_retailer_id');
                            $productId = str_replace('PROD_', '', $rawProductId);
                            
                            $quantity = data_get($item, 'quantity', 1);
                            $itemPrice = data_get($item, 'item_price');

                            $product = Product::find($productId);
                            $unitPrice = $product ? $product->price : $itemPrice;

                            if ($productId) {
                                OrderItem::create([
                                    'order_id' => $order->id,
                                    'product_id' => $productId,
                                    'quantity' => $quantity,
                                    'unit_price' => $unitPrice,
                                ]);

                                $totalAmount += ($quantity * $unitPrice);
                            }
                        }

                        $taxPercent = \App\Models\Setting::where('key', 'tax_percent')->value('value');
                        $taxName = \App\Models\Setting::where('key', 'tax_name')->value('value');
                        $taxAmount = 0;

                        if ($taxPercent > 0) {
                            $taxAmount = ($totalAmount * $taxPercent) / 100;
                        }

                        $order->update([
                            'total_amount' => $totalAmount + $taxAmount,
                            'tax_name' => $taxName,
                            'tax_type' => 'percent',
                            'tax_amount' => $taxPercent
                        ]);
                        Log::info('Order created successfully from WhatsApp.', ['order_id' => $order->id]);

                        $whatsAppService = new \App\Services\WhatsAppService();
                        
                        if (!$storeId) {
                            // Order placed via direct link without selecting a store
                            $stores = \App\Models\Store::all();
                            $whatsAppService->sendStoreWebViewMessage($customerPhone, "Thank you for your order! To complete it, please click below to select the nearest store for delivery:");
                        } else {
                            // Normal flow
                            $autoReplyMsg = "Your order #{$order->order_number} has been sent successfully. We will shortly *send you the updated price with a payment link to complete your order*.";
                            $whatsAppService->sendTextMessage($customerPhone, $autoReplyMsg);
                            // Cache::forget('whatsapp_store_id_' . $customerPhone); // Keep store ID for future orders
                        }
                        
                        Cache::forget('whatsapp_step_' . $customerPhone);
                    });
                }
            } elseif ($messages && data_get($messages, 'type') === 'text') {
                $contact = data_get($value, 'contacts.0');
                $customerPhone = data_get($contact, 'wa_id');
                $textBody = data_get($messages, 'text.body');
                
                if ($customerPhone && $textBody) {
                    $whatsAppService = new \App\Services\WhatsAppService();

                    $searchTerm = strtolower(trim($textBody));
                    $greetings = ['hi', 'hello', 'hey', 'start', 'menu'];
                    $trackingKeywords = ['track', 'status', 'order status', 'my order', 'my orders'];
                    $faqKeywords = ['faq', 'help', 'delivery time', 'support'];
                    
                    if (in_array($searchTerm, $greetings)) {
                        $whatsAppService->sendStoreWebViewMessage($customerPhone);
                    } elseif (in_array($searchTerm, $trackingKeywords)) {
                        $recentOrders = \App\Models\Order::where('customer_phone', $customerPhone)->latest()->take(3)->get();
                        if ($recentOrders->isNotEmpty()) {
                            $msg = "Here are your recent orders:\n\n";
                            foreach ($recentOrders as $order) {
                                $statusText = ucfirst(str_replace('_', ' ', $order->status));
                                $msg .= "*Order Number:* #{$order->order_number}\n";
                                $msg .= "*Status:* {$statusText}\n";
                                $msg .= "*Total:* " . env('CURRENCY_SYMBOL', '₹') . "{$order->total_amount}\n\n";
                            }
                            $msg .= "Type *Menu* to place a new order.";
                            $whatsAppService->sendTextMessage($customerPhone, $msg);
                        } else {
                            $whatsAppService->sendTextMessage($customerPhone, "We couldn't find any recent orders for your number. Type *Hi* to place a new order.");
                        }
                    } elseif (in_array($searchTerm, $faqKeywords)) {
                        $faqMsg1 = "*AFI WhatsApp Ordering Platform - FAQs (Part 1/2):*\n\n";
                        $faqMsg1 .= "*1. When can I place my AFI order?*\nYou can place your order once your delivery route has been scheduled. A WhatsApp notification will be sent to begin the ordering process.\n\n";
                        $faqMsg1 .= "*2. How do I know if my order has been received?*\nYou will receive an order confirmation message on WhatsApp immediately after your order has been submitted successfully.\n\n";
                        $faqMsg1 .= "*3. Can I place my order through email, phone call, or text message?*\nNo. All inventory orders must be placed through the WhatsApp Ordering Platform. Orders received through email, SMS, or phone calls will not be processed.\n\n";
                        $faqMsg1 .= "*4. What if I forget to order an item?*\nSimply submit a new WhatsApp order for the missing item. This ensures your request is recorded and processed correctly.\n\n";
                        $faqMsg1 .= "*5. How can I check the status of my order?*\nAll order updates, including invoice generation, payment confirmation, dispatch status, and ETA, will be shared through the same WhatsApp conversation.\n\n";
                        $faqMsg1 .= "*6. Is there a minimum order value?*\nYes. The minimum order value is $500. Orders below this amount cannot be processed.\n\n";
                        $faqMsg1 .= "*7. Where is my order?*\nOnce your order has been dispatched, you will receive dispatch updates along with the Estimated Time of Arrival (ETA) through WhatsApp.\n\n";
                        $faqMsg1 .= "*8. Can I submit my order without making the payment?*\nYes. However, your order will only be processed after payment has been successfully received.\n\n";
                        $faqMsg1 .= "*9. I haven't received the WhatsApp ordering message. What should I do?*\nEnsure the WhatsApp ordering number has been saved in your contacts. If you still do not receive the notification after your route has been scheduled, please contact the Helpdesk.";
                        
                        $faqMsg2 = "*FAQs (Part 2/2):*\n\n";
                        $faqMsg2 .= "*10. What if items are missing from my delivery?*\nPlease notify the Helpdesk immediately after receiving your order. Our team will investigate the issue and assist you with the resolution.\n\n";
                        $faqMsg2 .= "*11. Can I place multiple orders on the same day?*\nYes. Additional orders may be placed through the WhatsApp Ordering Platform. Each order is processed separately and must meet the applicable ordering requirements.\n\n";
                        $faqMsg2 .= "*12. What if I receive damaged or incorrect products?*\nPlease report any damaged or incorrect products to the Helpdesk as soon as possible after delivery. Supporting photos may be requested to assist with the investigation.\n\n";
                        $faqMsg2 .= "*13. Can I place an order for another restaurant location?*\nNo. Orders can only be placed for the store selected during the ordering process. Please ensure you select the correct location before submitting your order.\n\n";
                        $faqMsg2 .= "*14. Can I modify or cancel my order after it has been submitted?*\nOnce your order enters the review and allocation process, modifications or cancellations cannot be guaranteed. Please contact the Helpdesk immediately if changes are required.\n\n";
                        $faqMsg2 .= "*15. What should I do if I selected the wrong store?*\nDo not continue placing the order. Return to the store selection page and choose the correct restaurant location before submitting your order.\n\n";
                        $faqMsg2 .= "*16. What if I don't receive my invoice or payment link?*\nIf you have not received your invoice or payment link after your order has been reviewed, please contact the Helpdesk for assistance.\n\n";
                        $faqMsg2 .= "*17. Why hasn't my order been dispatched yet?*\nOrders are dispatched only after payment has been received and inventory allocation has been completed. If your order is delayed, please contact the Helpdesk for an update.\n\n";
                        $faqMsg2 .= "*18. Who should I contact if I have any questions or need assistance?*\nFor any questions regarding ordering, invoices, payments, dispatch, deliveries, missing items, or technical issues, please contact the Helpdesk at helpdesk@pizzatwist.com.\n\n";
                        $faqMsg2 .= "Type *Hi* or *Menu* to start ordering.";
                        
                        $whatsAppService->sendTextMessage($customerPhone, $faqMsg1);
                        $whatsAppService->sendTextMessage($customerPhone, $faqMsg2);
                    } else {
                        $potentialOrder = \App\Models\Order::where('customer_phone', $customerPhone)
                            ->where('id', ltrim($searchTerm, '0#')) 
                            ->first();

                        if ($potentialOrder) {
                            $statusText = ucfirst(str_replace('_', ' ', $potentialOrder->status));
                            $msg = "*Order Number:* #{$potentialOrder->order_number}\n";
                            $msg .= "*Status:* {$statusText}\n";
                            $msg .= "*Total:* " . env('CURRENCY_SYMBOL', '₹') . "{$potentialOrder->total_amount}";
                            $whatsAppService->sendTextMessage($customerPhone, $msg);
                        } else {
                            $whatsAppService->sendTextMessage($customerPhone, "I didn't quite catch that.\n\nHere are some things you can say:\n- *Hi* (To place an order)\n- *Track* (To check your order status)\n- *FAQ* (For help)");
                        }
                    }
                }
            } elseif ($messages && data_get($messages, 'type') === 'interactive') {
                $contact = data_get($value, 'contacts.0');
                $customerPhone = data_get($contact, 'wa_id');
                
                $interactiveType = data_get($messages, 'interactive.type');
                
                if ($interactiveType === 'list_reply' || $interactiveType === 'nfm_reply') {
                    
                    if ($interactiveType === 'list_reply') {
                        $listReplyId = data_get($messages, 'interactive.list_reply.id');
                        
                        $storeId = str_replace('store_', '', $listReplyId);
                    } else {
                        // For nfm_reply (Flow reply)
                        $responseJson = data_get($messages, 'interactive.nfm_reply.response_json');
                        $responseData = json_decode($responseJson, true);
                        $storeId = data_get($responseData, 'selected_store_id');
                    }
                    
                    if ($storeId) {
                        $store = \App\Models\Store::find($storeId);
                        
                        if ($store) {
                            Cache::put('whatsapp_store_id_' . $customerPhone, $storeId, now()->addMinutes(30));
                            $whatsAppService = new \App\Services\WhatsAppService();

                            $pendingOrder = Order::where('customer_phone', $customerPhone)
                                ->whereNull('store_id')
                                ->latest()
                                ->first();

                            if ($pendingOrder) {
                                $pendingOrder->update(['store_id' => $store->id]);
                                $whatsAppService->sendTextMessage($customerPhone, "Thank you! Your order #{$pendingOrder->order_number} is now assigned. We will shortly *send you the updated price with a payment link to complete your order*.");
                            } else {
                                $cartSession = \App\Models\CartSession::create([
                                    'customer_phone' => $customerPhone,
                                    'store_id' => $store->id,
                                ]);
                                $shopUrl = route('shop.index', $cartSession->uuid);
                                $whatsAppService->sendTextMessage($customerPhone, "You selected {$store->name}!\n\nPlease click the link below to select your products and place your order:\n{$shopUrl}\n\n*Note: Prices shown are estimates. We will send you the updated price with a payment link after you place your order.*");
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error processing WhatsApp Webhook: ' . $e->getMessage());
        }

        return response('EVENT_RECEIVED', 200);
    }
}
