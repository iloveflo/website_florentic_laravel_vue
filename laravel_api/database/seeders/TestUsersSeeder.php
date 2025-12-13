<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use Faker\Factory as Faker;

class TestUsersSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('vi_VN'); // dùng Faker tiếng Việt

        for ($i = 1; $i <= 30; $i++) {
            User::updateOrCreate(
                ['email' => "user{$i}@example.com"], // email duy nhất
                [
                    'username' => "user{$i}",
                    'full_name' => $faker->name,
                    'phone' => Crypt::encryptString($faker->numerify('0#########')), // mã hóa
                    'address' => Crypt::encryptString($faker->address), // mã hóa
                    'password' => Hash::make('123456'), // mật khẩu mặc định
                    'avatar' =>"uploads/avatar/1763785841____nh_ch___p_m__n_h__nh_2025-10-23_204213.png",
                    'role' => $faker->randomElement(['user', 'admin']),
                    'google_id' => null,
                    'facebook_id' => null,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
