<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens,HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';
    protected $dates = ['deleted_at'];
    /**
     * Các trường được phép fill.
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'full_name',
        'phone',
        'address',
        'avatar',
        'role',
        'google_id',
        'facebook_id',
        'email_verified_at',
        'remember_token',
    ];

    /**
     * Các trường không được trả về JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Chuyển đổi kiểu dữ liệu.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
        'deleted_at'        => 'datetime',
    ];

    /**
     * Quan hệ: User có nhiều Orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Quan hệ: User có nhiều Reviews
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Quan hệ: User có nhiều coupon usage
     */
    public function couponUsage()
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Quan hệ: User có nhiều cart sessions
     */
    public function cartSessions()
    {
        return $this->hasMany(CartSession::class);
    }

    /**
     * Quan hệ: User có nhiều chatbot conversations
     */
    public function chatbotConversations()
    {
        return $this->hasMany(ChatbotConversation::class);
    }
}
