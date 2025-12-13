<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Basic T-Shirt', 'category_id' => 1, 'price' => 150000, 'cost_price' => 90000],
            ['name' => 'Premium Cotton Tee', 'category_id' => 1, 'price' => 220000, 'cost_price' => 150000],
            ['name' => 'Oversize T-Shirt', 'category_id' => 1, 'price' => 180000, 'cost_price' => 110000],
            ['name' => 'Black Hoodie', 'category_id' => 2, 'price' => 350000, 'cost_price' => 250000],
            ['name' => 'White Hoodie', 'category_id' => 2, 'price' => 360000, 'cost_price' => 260000],
            ['name' => 'Cap Classic', 'category_id' => 3, 'price' => 120000, 'cost_price' => 70000],
            ['name' => 'Leather Belt', 'category_id' => 3, 'price' => 250000, 'cost_price' => 150000],
        ];

        foreach ($products as $p) {
            $id = DB::table('products')->insertGetId([
                'category_id' => $p['category_id'],
                'name' => $p['name'],
                'slug' => Str::slug($p['name']) . '-' . uniqid(),
                'description' => $p['name'] . ' high quality fashion product.',
                'price' => $p['price'],
                'cost_price' => $p['cost_price'],
                'sale_price' => null,
                'quantity' => 100,
                'sku' => strtoupper(Str::random(8)),
                'featured' => rand(0,1),
            ]);

            // Size
            foreach (['S','M','L','XL'] as $s) {
                DB::table('product_sizes')->insert([
                    'product_id' => $id,
                    'size' => $s,
                    'quantity' => rand(10,50),
                ]);
            }

            // Color
            $colors = [
                ['Red', '#FF0000'], ['Black', '#000000'], ['White', '#FFFFFF']
            ];

            foreach ($colors as $c) {
                DB::table('product_colors')->insert([
                    'product_id' => $id,
                    'color_name' => $c[0],
                    'color_code' => $c[1],
                    'quantity' => rand(10, 40),
                ]);
            }

            // Images (3 ảnh mỗi sản phẩm)
            for ($i = 1; $i <= 3; $i++) {
                DB::table('product_images')->insert([
                    'product_id' => $id,
                    'image_path' => "uploads/products/sample_$i.jpg",
                    'is_primary' => $i === 1 ? 1 : 0,
                    'sort_order' => $i,
                ]);
            }
        }
    }
}
