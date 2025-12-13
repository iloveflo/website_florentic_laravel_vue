<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('coupons')->insert([
            'code' => 'SALE10',
            'description' => 'Giảm 10%',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'min_order_value' => 200000,
            'usage_limit' => 100,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
        ]);
    }
}
