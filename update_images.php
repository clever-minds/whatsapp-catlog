<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

$artifactDir = 'C:\Users\ujjaw\.gemini\antigravity\brain\9f9771db-7b2f-4f21-9548-c912a9a98d05';
$productsPath = storage_path('app/public/products');

if (!file_exists($productsPath)) {
    mkdir($productsPath, 0755, true);
}

$images = [
    'All-Purpose Flour (Maida)' => 'flour_1780126651376.png',
    'Classic Pizza Sauce' => 'pizza_sauce_1780126669977.png',
    'Mozzarella Cheese (Diced)' => 'mozzarella_cheese_1780126689988.png',
    'Extra Virgin Olive Oil' => 'olive_oil_1780126705489.png',
    'Sliced Pepperoni' => 'pepperoni_1780126720695.png',
    'Fresh Red Onions' => 'red_onions_1780126739575.png',
    'Green Capsicum (Bell Peppers)' => 'green_capsicum_1780126758013.png',
    'Black Olives (Pitted)' => 'black_olives_1780126778180.png',
    'Sliced Jalapenos' => 'jalapenos_1780126793717.png',
    'Dried Oregano Seasoning' => 'oregano_1780126808545.png'
];

foreach ($images as $productName => $filename) {
    $source = $artifactDir . '\\' . $filename;
    $destination = $productsPath . '/' . $filename;
    
    if (file_exists($source)) {
        copy($source, $destination);
        
        $product = Product::where('name', $productName)->first();
        if ($product) {
            $product->update([
                'image' => 'products/' . $filename
            ]);
            echo "Updated image for: {$productName}\n";
        }
    } else {
        echo "Source file not found: {$source}\n";
    }
}
