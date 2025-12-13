<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Coupon extends Model
{
    use HasFactory;

    protected $table = 'coupons';

    /**
     * Các trường được phép fill.
     */
    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_order_value',
        'max_discount',
        'usage_limit',
        'used_count',
        'start_date',
        'end_date',
        'status',
    ];

    /**
     * Các trường datetime
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Quan hệ: Coupon có nhiều coupon usages
     */
    public function usages()
    {
        return $this->hasMany(CouponUsage::class, 'coupon_id');
    }
}
