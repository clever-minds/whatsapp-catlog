<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        // Parse incoming message logic will go here
        // (e.g. creating orders from cart messages)

        return response('EVENT_RECEIVED', 200);
    }
}
