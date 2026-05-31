<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleWhatsApp(Request $request)
    {
        // Example logic to parse WhatsApp message/catalog order
        // Webhook from Meta/Twilio
        Log::info('WhatsApp Webhook received', $request->all());
        $data = $request->all();
        
        // Order::create([...]); // Map data to order
        
        return response()->json(['status' => 'success']);
    }

    public function handleStripe(Request $request)
    {
        // Example logic for Stripe Webhook
        Log::info('Stripe Webhook received', $request->all());
        $payload = $request->all();
        
        if (isset($payload['type']) && $payload['type'] == 'checkout.session.completed') {
            $sessionId = $payload['data']['object']['id'] ?? null;
            $order = Order::where('stripe_session_id', $sessionId)->first();
            
            if ($order) {
                $order->update(['status' => 'paid']);
                // \Mail::to('operator@example.com')->send(new \App\Mail\OrderPaid($order));
            }
        }
        
        return response()->json(['status' => 'success']);
    }
}
