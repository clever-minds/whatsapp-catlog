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

        // Facebook requires a valid public URL for image and product link
        $productLink = route('public.product.show', $product->id);
        $imageUrl = $product->image
            ? 'https://twistthetaste.com/storage/' . $product->image
            : 'https://dummyimage.com/600x600/000/fff.png?text=' . urlencode(str_replace(' ', '+', $product->name));

        // Meta Catalog API expects price as a string formatted like "250.00 INR" (Amount and Currency separated by space)

        $payload = [
            'item_type' => 'PRODUCT_ITEM',
            'allow_upsert' => true,
            'requests' => [
                [
                    'method' => 'CREATE',
                    'data' => [
                        'id' => 'PROD_' . $product->id, // Retailer ID
                        'title' => $product->name,
                        'description' => $product->description ?? $product->name,
                        'availability' => $product->is_active && $product->stock > 0 ? 'IN_STOCK' : 'OUT_OF_STOCK',
                        'condition' => 'NEW',
                        'price' => number_format($product->price ?? 0, 2, '.', ''),
                        'currency' => 'USD',
                        'image_link' => $imageUrl,
                        'link' => $productLink,
                        'brand' => 'MyStore',
                        'custom_label_0' => $product->category ? $product->category->name : 'Other Products'
                    ]
                ]
            ]
        ];

        Log::info("Sending Payload to Meta: " . json_encode($payload));

        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->baseUrl}/{$this->catalogId}/items_batch", $payload);

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

    /**
     * Sync multiple products in a single batch request to prevent timeouts
     */
    public function syncProductsBatch($products)
    {
        if (!$this->accessToken || !$this->catalogId) {
            Log::warning('WhatsAppCatalogService: Missing access token or catalog ID.');
            return false;
        }

        $requests = [];

        foreach ($products as $product) {
            $productLink = route('public.product.show', $product->id);
            $imageUrl = $product->image
                ? 'https://twistthetaste.com/storage/' . $product->image
                : 'https://dummyimage.com/600x600/000/fff.png?text=' . urlencode(str_replace(' ', '+', $product->name));

            $requests[] = [
                'method' => 'CREATE',
                'data' => [
                    'id' => 'PROD_' . $product->id,
                    'title' => $product->name,
                    'description' => $product->description ?? $product->name,
                    'availability' => $product->is_active && $product->stock > 0 ? 'IN_STOCK' : 'OUT_OF_STOCK',
                    'condition' => 'NEW',
                    'price' => number_format($product->price ?? 0, 2, '.', ''),
                    'currency' => 'USD',
                    'image_link' => $imageUrl,
                    'link' => $productLink,
                    'brand' => 'MyStore',
                    'custom_label_0' => $product->category ? $product->category->name : 'Other Products'
                ]
            ];
        }

        // Meta allows up to 1000 items per batch, we have 125 so this is well within limits.
        $payload = [
            'item_type' => 'PRODUCT_ITEM',
            'allow_upsert' => true,
            'requests' => $requests
        ];

        Log::info("Sending Batch Payload to Meta with " . count($requests) . " items.");

        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->baseUrl}/{$this->catalogId}/items_batch", $payload);

            if ($response->successful()) {
                Log::info("Batch sync of " . count($requests) . " products completed successfully.");
                return true;
            } else {
                Log::error("Failed to sync batch: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Exception in batch sync: " . $e->getMessage());
            return false;
        }
    }

    public function deleteProduct(Product $product)
    {
        if (!$this->accessToken || !$this->catalogId) {
            Log::warning('WhatsAppCatalogService: Missing access token or catalog ID.');
            return false;
        }

        $payload = [
            'item_type' => 'PRODUCT_ITEM',
            'allow_upsert' => true,
            'requests' => [
                [
                    'method' => 'DELETE',
                    'data' => [
                        'id' => 'PROD_' . $product->id
                    ]
                ]
            ]
        ];

        Log::info("Sending Delete Payload to Meta: " . json_encode($payload));

        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->baseUrl}/{$this->catalogId}/items_batch", $payload);

            if ($response->successful()) {
                Log::info("Deleted Product #{$product->id} from Meta Catalog successfully.");
                return true;
            } else {
                Log::error("Failed to delete Product #{$product->id}: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Exception in WhatsAppCatalogService: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Dynamically create or update Product Sets (Collections) in Meta Commerce Manager
     * so that WhatsApp Storefront displays products in categories/folders.
     */
    public function syncCategoriesAsCollections()
    {
        if (!$this->accessToken || !$this->catalogId) {
            Log::warning('WhatsAppCatalogService: Missing access token or catalog ID for syncCategoriesAsCollections.');
            return false;
        }

        try {
            // 1. Fetch existing product sets to avoid duplicates
            $existingSets = [];
            $response = Http::withToken($this->accessToken)
                ->get("{$this->baseUrl}/{$this->catalogId}/product_sets");
            
            if ($response->successful()) {
                $data = $response->json('data') ?? [];
                foreach ($data as $set) {
                    $existingSets[$set['name']] = $set['id'];
                }
            }

            // 2. Fetch all unique custom_label_0 used in our local products
            $products = \App\Models\Product::with('category')->get();
            $categoryNames = collect();
            
            foreach ($products as $product) {
                $catName = $product->category ? $product->category->name : 'Other';
                $categoryNames->push($catName);
            }
            
            $uniqueCategories = $categoryNames->unique()->filter();

            $successCount = 0;
            // 3. Create or update a Product Set for each category
            foreach ($uniqueCategories as $name) {
                $filter = json_encode([
                    'custom_label_0' => ['eq' => $name]
                ]);

                if (isset($existingSets[$name])) {
                    // Set exists, update it if needed (Meta API allows updating sets by POSTing to the set ID)
                    $setId = $existingSets[$name];
                    $res = Http::withToken($this->accessToken)
                        ->post("{$this->baseUrl}/{$setId}", [
                            'filter' => $filter
                        ]);
                    if ($res->successful()) $successCount++;
                } else {
                    // Create new set
                    $res = Http::withToken($this->accessToken)
                        ->post("{$this->baseUrl}/{$this->catalogId}/product_sets", [
                            'name' => $name,
                            'filter' => $filter
                        ]);
                    if ($res->successful()) $successCount++;
                }
            }

            Log::info("Successfully synced {$successCount} Category Collections to Meta Catalog.");
            return true;

        } catch (\Exception $e) {
            Log::error("Exception in WhatsAppCatalogService syncCategoriesAsCollections: " . $e->getMessage());
            return false;
        }
    }
}

