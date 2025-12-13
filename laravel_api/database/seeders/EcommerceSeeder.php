<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EcommerceSeeder extends Seeder
{
    public function run()
    {
        // -------------------------
        // 1. Insert Categories
        // -------------------------
        DB::table('categories')->insert([
            [
                'name' => 'Áo Thun',
                'slug' => 'tshirts',
                'description' => 'Các mẫu áo thun thời trang',
                'parent_id' => null,
                'image' => 'categories/tshirts.jpg',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Áo Polo',
                'slug' => 'polo',
                'description' => 'BST áo Polo cao cấp',
                'parent_id' => null,
                'image' => 'categories/polo.jpg',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Quần Jean',
                'slug' => 'jeans',
                'description' => 'Quần jean phong cách',
                'parent_id' => null,
                'image' => 'categories/jeans.jpg',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // -------------------------
        // 2. Insert Products
        // -------------------------
        for ($i = 1; $i <= 10; $i++) {

            $categoryId = rand(1, 3);
            $name = "Sản phẩm demo $i";

            $productId = DB::table('products')->insertGetId([
                'category_id' => $categoryId,
                'name'        => $name,
                'slug'        => Str::slug($name) . "-$i",
                'description' => 'Mô tả sản phẩm demo ' . $i,
                'price'       => rand(150000, 450000),
                'cost_price'  => rand(100000, 200000),
                'sale_price'  => rand(120000, 300000),
                'quantity'    => rand(10, 100),
                'sku'         => 'SKU-' . strtoupper(Str::random(6)),
                'status'      => 'active',
                'featured'    => rand(0, 1),
                'view_count'  => rand(0, 200),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // -------------------------
            // 3. Insert Product Images
            // ------------------------
            DB::table('product_images')->insert([
                [
                    'product_id' => $productId,
                    'image_path' => "products/product{$i}_1.jpg",
                    'is_primary' => 1,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'product_id' => $productId,
                    'image_path' => "products/product{$i}_2.jpg",
                    'is_primary' => 0,
                    'sort_order' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // -------------------------
            // 4. Insert Product Colors
            // -------------------------
            DB::table('product_colors')->insert([
                [
                    'product_id' => $productId,
                    'color_name' => 'Đen',
                    'color_code' => '#000000',
                    'quantity'   => rand(5, 20),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'product_id' => $productId,
                    'color_name' => 'Trắng',
                    'color_code' => '#FFFFFF',
                    'quantity'   => rand(5, 20),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);

            // -------------------------
            // 5. Insert Product Sizes
            // -------------------------
            $sizes = ['S','M','L','XL','XXL'];

            foreach ($sizes as $size) {
                DB::table('product_sizes')->insert([
                    'product_id' => $productId,
                    'size'       => $size,
                    'quantity'   => rand(5, 20),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
