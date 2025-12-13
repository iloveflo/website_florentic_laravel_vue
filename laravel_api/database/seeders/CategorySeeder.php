<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'name' => 'T-Shirts',
                'slug' => 't-shirts',
                'description' => 'Basic and premium T-Shirts',
                'image' => null,
            ],
            [
                'name' => 'Hoodies',
                'slug' => 'hoodies',
                'description' => 'Winter hoodies',
                'image' => null,
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'description' => 'Fashion accessories',
                'image' => null,
            ],
        ]);
    }
}
