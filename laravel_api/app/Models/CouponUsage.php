<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    use HasFactory;

    protected $table = 'coupon_usage';

    /**
     * Các trường được phép fill.
     */
    protected $fillable = [
        'coupon_id',
        'user_id',
        'order_id',
    ];

    /**
     * Các trường datetime
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Quan hệ: CouponUsage thuộc Coupon
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    /**
     * Quan hệ: CouponUsage thuộc User (có thể null)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Quan hệ: CouponUsage thuộc Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
