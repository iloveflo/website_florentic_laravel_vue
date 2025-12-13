<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartSession extends Model
{
    use HasFactory;

    protected $table = 'cart_sessions';

    /**
     * Các trường được phép fill.
     */
    protected $fillable = [
        'session_id',
        'user_id',
        'product_id',
        'quantity',
        'size',
        'color',
    ];

    /**
     * Các trường datetime
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Quan hệ: CartSession thuộc User (có thể null)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Quan hệ: CartSession thuộc Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
