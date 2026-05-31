<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Product;

class PizzaRawMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Categories
        $catDairy = Category::firstOrCreate(['name' => 'Dairy']);
        $catVeg = Category::firstOrCreate(['name' => 'Vegetables']);
        $catSauce = Category::firstOrCreate(['name' => 'Condiments & Sauces']);
        $catBase = Category::firstOrCreate(['name' => 'Bakery & Base']);
        $catMeat = Category::firstOrCreate(['name' => 'Meat']);

        // Create Units
        $unitKg = Unit::firstOrCreate(['name' => 'Kilograms', 'short_name' => 'kg']);
        $unitG = Unit::firstOrCreate(['name' => 'Grams', 'short_name' => 'g']);
        $unitL = Unit::firstOrCreate(['name' => 'Liters', 'short_name' => 'L']);
        $unitTin = Unit::firstOrCreate(['name' => 'Tin / Can', 'short_name' => 'tin']);
        $unitPkt = Unit::firstOrCreate(['name' => 'Packet', 'short_name' => 'pkt']);

        // Products Data
        $products = [
            [
                'name' => 'All-Purpose Flour (Maida)',
                'description' => 'High quality refined flour for perfect pizza dough base.',
                'category_id' => $catBase->id,
                'unit_id' => $unitKg->id,
                'stock' => 50,
                'price' => 2.50,
                'is_active' => true,
            ],
            [
                'name' => 'Classic Pizza Sauce',
                'description' => 'Rich tomato-based sauce with Italian herbs and garlic.',
                'category_id' => $catSauce->id,
                'unit_id' => $unitKg->id,
                'stock' => 30,
                'price' => 4.00,
                'is_active' => true,
            ],
            [
                'name' => 'Mozzarella Cheese (Diced)',
                'description' => 'Premium 100% mozzarella cheese, pre-diced for melting.',
                'category_id' => $catDairy->id,
                'unit_id' => $unitKg->id,
                'stock' => 25,
                'price' => 8.50,
                'is_active' => true,
            ],
            [
                'name' => 'Extra Virgin Olive Oil',
                'description' => 'Cold-pressed extra virgin olive oil for authentic taste.',
                'category_id' => $catSauce->id,
                'unit_id' => $unitL->id,
                'stock' => 15,
                'price' => 12.00,
                'is_active' => true,
            ],
            [
                'name' => 'Sliced Pepperoni',
                'description' => 'Spicy cured meat slices, perfect for pepperoni pizza.',
                'category_id' => $catMeat->id,
                'unit_id' => $unitPkt->id,
                'stock' => 40,
                'price' => 5.50,
                'is_active' => true,
            ],
            [
                'name' => 'Fresh Red Onions',
                'description' => 'Farm fresh red onions for topping.',
                'category_id' => $catVeg->id,
                'unit_id' => $unitKg->id,
                'stock' => 20,
                'price' => 1.20,
                'is_active' => true,
            ],
            [
                'name' => 'Green Capsicum (Bell Peppers)',
                'description' => 'Crisp green bell peppers for pizza toppings.',
                'category_id' => $catVeg->id,
                'unit_id' => $unitKg->id,
                'stock' => 15,
                'price' => 2.00,
                'is_active' => true,
            ],
            [
                'name' => 'Black Olives (Pitted)',
                'description' => 'Pitted Spanish black olives in brine.',
                'category_id' => $catSauce->id,
                'unit_id' => $unitTin->id,
                'stock' => 35,
                'price' => 3.50,
                'is_active' => true,
            ],
            [
                'name' => 'Sliced Jalapenos',
                'description' => 'Spicy pickled jalapeno slices.',
                'category_id' => $catSauce->id,
                'unit_id' => $unitTin->id,
                'stock' => 30,
                'price' => 3.00,
                'is_active' => true,
            ],
            [
                'name' => 'Dried Oregano Seasoning',
                'description' => 'Aromatic dried oregano leaves for finishing touch.',
                'category_id' => $catSauce->id,
                'unit_id' => $unitG->id,
                'stock' => 100,
                'price' => 1.50,
                'is_active' => true,
            ]
        ];

        foreach ($products as $productData) {
            Product::firstOrCreate(
                ['name' => $productData['name']],
                $productData
            );
        }
    }
}
