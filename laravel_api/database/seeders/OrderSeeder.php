<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id');
        $products = DB::table('products')->get();

        for ($i = 1; $i <= 3; $i++) {

            $user_id = $userIds->random();

            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $user_id,
                'order_code' => 'ORD' . time() . $i,
                'full_name' => "User $i",
                'email' => "user$i@example.com",
                'phone' => "09000$i$i$i",
                'address' => "Street $i",
                'subtotal' => 500000,
                'discount_amount' => 0,
                'shipping_fee' => 30000,
                'total_amount' => 530000,
                'order_status' => 'completed',
            ]);

            // Order Items
            $picked = $products->random(2);

            foreach ($picked as $p) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $p->id,
                    'product_name' => $p->name,
                    'product_image' => "uploads/products/sample_1.jpg",
                    'price' => $p->price,
                    'quantity' => 1,
                    'subtotal' => $p->price,
                ]);
            }
        }
    }
}
