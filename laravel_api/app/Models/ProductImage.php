<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory; 

    protected $table = 'product_images';

    protected $fillable = [
        'product_id',
        'image_path',
        'is_primary',
        'sort_order',
    ];

    // Tự động thêm field 'url' vào JSON trả về
    protected $appends = ['url'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessor: Sửa lại để trả về đúng đường dẫn
    public function getUrlAttribute()
    {
        if (!$this->image_path) {
            return null;
        }
        return asset($this->image_path);
    }
}