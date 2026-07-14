<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Store;

class WhatsAppFlowController extends Controller
{
    /**
     * Handle incoming data exchange requests from Meta WhatsApp Flows
     */
    public function handleEndpoint(Request $request)
    {
        try {
            $privateKeyPath = storage_path('app/keys/private.pem');
            if (!file_exists($privateKeyPath)) {
                Log::error('WhatsAppFlow: Private key not found.');
                return response('Private key missing', 500);
            }
            $privateKey = file_get_contents($privateKeyPath);

            $encryptedAesKey = $request->input('encrypted_aes_key');
            $encryptedFlowData = $request->input('encrypted_flow_data');
            $initialVector = $request->input('initial_vector');

            if (!$encryptedAesKey || !$encryptedFlowData || !$initialVector) {
                Log::warning('WhatsAppFlow: Missing encrypted payload fields.');
                return response('Invalid payload', 400);
            }

            // 1. Decrypt the AES key using our RSA private key
            $aesKey = '';
            if (!openssl_private_decrypt(base64_decode($encryptedAesKey), $aesKey, $privateKey, OPENSSL_PKCS1_OAEP_PADDING)) {
                Log::error('WhatsAppFlow: Failed to decrypt AES key.');
                return response('Decryption failed', 400);
            }

            // 2. Decrypt Flow Data
            $iv = base64_decode($initialVector);
            $flowDataBinary = base64_decode($encryptedFlowData);
            
            // Meta appends a 16-byte GCM authentication tag to the end of the encrypted data
            $tagLength = 16;
            $tag = substr($flowDataBinary, -$tagLength);
            $ciphertext = substr($flowDataBinary, 0, -$tagLength);

            $decryptedJson = openssl_decrypt($ciphertext, 'aes-256-gcm', $aesKey, OPENSSL_RAW_DATA, $iv, $tag);
            
            if ($decryptedJson === false) {
                Log::error('WhatsAppFlow: Failed to decrypt flow data.');
                return response('Decryption failed', 400);
            }

            $flowData = json_decode($decryptedJson, true);
            $action = $flowData['action'] ?? '';
            $data = $flowData['data'] ?? [];

            Log::info("WhatsAppFlow: Received action '{$action}'");

            $responsePayload = [];

            if ($action === 'ping') {
                $responsePayload = [
                    'data' => [
                        'status' => 'active'
                    ]
                ];
            } elseif ($action === 'data_exchange') {
                // Handle live search
                $searchTerm = $data['store_name'] ?? '';
                Log::info("WhatsAppFlow: Searching stores for '{$searchTerm}'");
                
                // Fetch top 20 stores matching the search
                $stores = Store::where('name', 'LIKE', '%' . $searchTerm . '%')
                    ->limit(20)
                    ->get();

                $dropdownItems = [];
                foreach ($stores as $store) {
                    $dropdownItems[] = [
                        'id' => (string) $store->id,
                        'title' => substr($store->name, 0, 30) // Max 30 chars
                    ];
                }

                if (empty($dropdownItems)) {
                    $dropdownItems[] = [
                        'id' => '0',
                        'title' => 'No stores found. Try again.'
                    ];
                }

                $responsePayload = [
                    'screen' => 'STORE_SELECTION',
                    'data' => [
                        'stores_list' => $dropdownItems
                    ]
                ];
            } else {
                Log::warning("WhatsAppFlow: Unknown action {$action}");
                return response('Unknown action', 400);
            }

            // 3. Encrypt the Response
            // Meta expects response encrypted with the SAME AES key, but with a flipped IV
            $flippedIv = '';
            for ($i = 0; $i < strlen($iv); $i++) {
                $flippedIv .= chr(~ord($iv[$i]));
            }

            $responseJsonStr = json_encode($responsePayload);
            $responseTag = '';
            
            $encryptedResponse = openssl_encrypt($responseJsonStr, 'aes-256-gcm', $aesKey, OPENSSL_RAW_DATA, $flippedIv, $responseTag);

            if ($encryptedResponse === false) {
                 Log::error('WhatsAppFlow: Failed to encrypt response.');
                 return response('Encryption failed', 500);
            }

            // Meta expects the Base64 of (EncryptedResponse + Tag)
            $finalPayload = base64_encode($encryptedResponse . $responseTag);

            return response($finalPayload, 200, ['Content-Type' => 'text/plain']);

        } catch (\Exception $e) {
            Log::error('WhatsAppFlow: Exception during flow endpoint: ' . $e->getMessage());
            return response('Server Error', 500);
        }
    }
}
