<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Product;

class WhatsAppCatalogService
{
    protected $accessToken;
    protected $catalogId;
    protected $baseUrl;

    public function __construct()
    {
        $this->accessToken = env('WHATSAPP_ACCESS_TOKEN');
        $this->catalogId = env('META_CATALOG_ID');
        $this->baseUrl = "https://graph.facebook.com/v18.0";
    }

    /**
     * Sync a product to the Meta Catalog
     */
    public function syncProduct(Product $product)
    {
        if (!$this->accessToken || !$this->catalogId) {
            Log::warning('WhatsAppCatalogService: Missing access token or catalog ID.');
            return false;
        }

        $endpoint = "{$this->baseUrl}/{$this->catalogId}/products";

        // Generate full image URL
        $imageUrl = $product->image ? asset('storage/' . $product->image) : asset('placeholder.png');

        // Note: Prices should be in cents/paisa for some regions, but Meta generally expects integer in 100ths, 
        // e.g. 299 for $2.99 or 1000 for $10.00. Check Meta docs for your currency.
        $price = $product->price ? $product->price * 100 : 0; 
        
        $payload = [
            'requests' => [
                [
                    'method' => 'CREATE',
                    'data' => [
                        'id' => 'PROD_' . $product->id, // Retailer ID
                        'title' => $product->name,
                        'description' => $product->description ?? $product->name,
                        'image_url' => $imageUrl,
                        'availability' => $product->is_active && $product->stock > 0 ? 'in stock' : 'out of stock',
                        'condition' => 'new',
                        'price' => $price,
                        'currency' => 'USD', // Change as per your requirement (e.g. INR)
                        'brand' => 'MyStore'
                    ]
                ]
            ]
        ];

        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->baseUrl}/{$this->catalogId}/batch", $payload);

            if ($response->successful()) {
                Log::info("Synced Product #{$product->id} to Meta Catalog successfully.");
                return true;
            } else {
                Log::error("Failed to sync Product #{$product->id}: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Exception in WhatsAppCatalogService: " . $e->getMessage());
            return false;
        }
    }
}
