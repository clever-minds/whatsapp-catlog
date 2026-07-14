<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only seed if there are no orders
        if (Order::count() > 0) {
            return;
        }

        $products = Product::all();
        if ($products->count() === 0) {
            return;
        }

        $customers = [
            ['name' => 'Alex Miller', 'phone' => '+15550199', 'email' => 'alex@example.com'],
            ['name' => 'Sophia Chen', 'phone' => '+15550188', 'email' => 'sophia@example.com'],
            ['name' => 'Liam Robinson', 'phone' => '+15550177', 'email' => 'liam@example.com'],
            ['name' => 'Olivia Martinez', 'phone' => '+15550166', 'email' => 'olivia@example.com'],
            ['name' => 'Noah Patel', 'phone' => '+15550155', 'email' => 'noah@example.com'],
            ['name' => 'Emma Wright', 'phone' => '+15550144', 'email' => 'emma@example.com'],
            ['name' => 'Mason Davis', 'phone' => '+15550133', 'email' => 'mason@example.com'],
            ['name' => 'Isabella Lopez', 'phone' => '+15550122', 'email' => 'isabella@example.com'],
        ];

        $statuses = ['pending_approval', 'quoted', 'paid', 'delivered'];

        // Create 20 mock orders distributed over the last 30 days
        for ($i = 0; $i < 20; $i++) {
            $customer = $customers[array_rand($customers)];
            $status = $statuses[array_rand($statuses)];
            
            // Random date in the last 30 days
            $daysAgo = rand(0, 29);
            $createdAt = Carbon::now()->subDays($daysAgo)->subHours(rand(1, 23))->subMinutes(rand(1, 59));
            
            $order = Order::create([
                'customer_name' => $customer['name'],
                'customer_phone' => $customer['phone'],
                'customer_email' => $customer['email'],
                'status' => $status,
                'total_amount' => 0, // will calculate below
                'stripe_payment_link' => $status == 'quoted' || $status == 'paid' || $status == 'delivered' 
                    ? 'https://checkout.stripe.com/c/pay/mock_session_' . uniqid() 
                    : null,
                'stripe_session_id' => $status == 'quoted' || $status == 'paid' || $status == 'delivered'
                    ? 'cs_test_' . uniqid()
                    : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Add 1 to 4 random products as items
            $itemCount = rand(1, 4);
            $selectedProducts = $products->random(min($itemCount, $products->count()));
            
            $totalAmount = 0;

            foreach ($selectedProducts as $product) {
                $qty = rand(1, 5);
                $unitPrice = $product->price;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $totalAmount += $qty * $unitPrice;
            }

            // Update the total amount
            $order->update(['total_amount' => $totalAmount]);
        }
    }
}
