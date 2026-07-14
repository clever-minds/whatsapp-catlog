<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Services\WhatsAppCatalogService;

class SyncWhatsAppCatalog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:sync-catalog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all local products to the Meta WhatsApp Catalog';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $products = Product::all();
        $total = $products->count();

        if ($total === 0) {
            $this->info('No products found to sync.');
            return;
        }

        $service = new WhatsAppCatalogService();
        
        $this->info("Found $total products. Starting BATCH sync to Meta Catalog (this will be fast)...");
        $result = $service->syncProductsBatch($products);

        if ($result) {
            $this->info("Products batch sync completed! Success: $total");
        } else {
            $this->error("Products batch sync failed!");
        }
        
        $this->info("Syncing Categories as Meta Catalog Collections...");
        if ($service->syncCategoriesAsCollections()) {
            $this->info("✓ Collections synced successfully.");
        } else {
            $this->error("✗ Failed to sync Collections.");
        }
        
        $this->info("All sync operations completed!");
    }
}
