<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        Contact::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '0123456789',
            'subject' => 'Support',
            'message' => 'Need help regarding order'
        ]);
    }
}
