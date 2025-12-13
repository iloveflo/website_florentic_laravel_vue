<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    /**
     * Các trường được phép fill.
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'comment',
        'status',
    ];

    /**
     * Các trường datetime
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Quan hệ: Review thuộc sản phẩm
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Quan hệ: Review thuộc User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Quan hệ: Review thuộc Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // Thêm dòng này để JSON trả về có sẵn field 'customer_name'
    protected $appends = ['customer_name']; 

    // Accessor: Tự động lấy full_name từ Order
    public function getCustomerNameAttribute()
    {
        // Kiểm tra xem có order không (đề phòng order bị xóa cứng)
        return $this->order ? $this->order->full_name : 'Khách hàng';
    }
}
