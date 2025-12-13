<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChatbotConversation;

class ChatbotSeeder extends Seeder
{
    public function run(): void
    {
        ChatbotConversation::create([
            'session_id' => 'test-session',
            'user_id' => 2,
            'message' => 'Hello',
            'response' => 'Hi, how can I help you today?'
        ]);
    }
}
