<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotConversation extends Model
{
    use HasFactory;

    protected $table = 'chatbot_conversations';

    /**
     * Các trường được phép fill.
     */
    protected $fillable = [
        'session_id',
        'user_id',
        'message',
        'response',
        'products_json',
    ];

    /**
     * Các trường datetime
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Quan hệ: ChatbotConversation thuộc User (có thể null)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
