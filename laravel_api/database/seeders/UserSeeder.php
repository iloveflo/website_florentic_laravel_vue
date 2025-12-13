<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin gốc
        DB::table('users')->insert([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('123'),
            'full_name' => 'Administrator',
            'phone' => Crypt::encryptString('0123456789'),
            'address' => Crypt::encryptString('123 Admin Street'),
            'role' => 'admin',
            'avatar' => null,
        ]);

        // 5 user mẫu
        for ($i = 1; $i <= 5; $i++) {
            DB::table('users')->insert([
                'username' => "user$i",
                'email' => "user$i@example.com",
                'password' => Hash::make('123456'),
                'full_name' => "User $i",
                'phone' => Crypt::encryptString("0900$i$i$i$i$i$i"),
                'address' => Crypt::encryptString("Street $i, City ABC"),
                'role' => 'user',
            ]);
        }
    }
}
