<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // email duy nhất
            [
                'username' => 'admin',
                'full_name' => 'Administrator',
                'phone' => Crypt::encryptString('0123456789'), // dữ liệu nhạy cảm
                'address' => Crypt::encryptString('123 Admin Street'),
                'password' => Hash::make('123'), // hash bcrypt
                'role' => 'admin',
                'google_id' => null,
                'facebook_id' => null,
                'email_verified_at' => now(),
            ]
        );
    }
}
