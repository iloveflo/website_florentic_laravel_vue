<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';

    /**
     * Các trường được phép fill.
     */
    protected $fillable = [
        'user_id',
        'order_code',
        'full_name',
        'email',
        'phone',
        'address',
        'subtotal',
        'discount_amount',
        'shipping_fee',
        'total_amount',
        'coupon_code',
        'payment_method',
        'payment_status',
        'transaction_id',
        'order_status',
        'note',
        'session_id',
        'tracking_token',
    ];

    /**
     * Các trường datetime
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Quan hệ: Order thuộc User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Quan hệ: Order có nhiều order_items
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /**
     * Quan hệ: Order có nhiều coupon usages
     */
    public function couponUsages()
    {
        return $this->hasMany(CouponUsage::class, 'order_id');
    }

    /**
     * Quan hệ: Order có nhiều reviews (qua order_items / product)
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'order_id');
    }
}

