<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    /**
     * Các trường được phép fill.
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_image',
        'size',
        'color',
        'price',
        'quantity',
        'subtotal',
    ];

    /**
     * Các trường datetime
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Quan hệ: OrderItem thuộc Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Quan hệ: OrderItem thuộc Product (có thể null nếu sản phẩm bị xóa)
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Quan hệ: OrderItem có thể có Review
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'order_id');
    }

    // 1. Tự động thêm field 'product_image_url' vào JSON
    protected $appends = ['product_image_url'];

    // 2. Định nghĩa logic tạo URL đầy đủ
    public function getProductImageUrlAttribute()
    {
        // Nếu trong DB chưa có ảnh, trả về ảnh lỗi
        if (!$this->product_image) {
            return asset('images/placeholder.png');
        }

        // Nếu dữ liệu cũ lỡ lưu cả http:// rồi thì trả về luôn
        if (filter_var($this->product_image, FILTER_VALIDATE_URL)) {
            return $this->product_image;
        }

        // Nối domain vào đường dẫn tương đối
        return asset($this->product_image);
    }
}

