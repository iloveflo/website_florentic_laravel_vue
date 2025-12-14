<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductDetailsController extends Controller
{
    public function show($slug)
    {
        // 1. Lấy sản phẩm chính
        // LƯU Ý: Đã XÓA 'reviews' khỏi hàm with() ở đây
        $product = Product::with([
            'category:id,name,slug',
            'images' => function ($query) {
                $query->orderByDesc('is_primary')
                    ->orderBy('sort_order');
            },
            'variants',
        ])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->first();

        // 2. Check 404
        if (!$product) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        // ======================================================
        // THAY ĐỔI CHÍNH: Truy vấn và Phân trang Review riêng
        // ======================================================
        $reviews = $product->reviews() // Gọi dạng function () để lấy Query Builder
            ->with(['order:id,full_name']) // Eager load quan hệ bên trong review
            ->latest()
            ->paginate(3); // Giới hạn 10 item 1 trang. Tự động bắt tham số ?page= trên URL


        // 3. Lấy sản phẩm liên quan
        $relatedProducts = Product::with(['images'])
            ->where('status', 'active')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();

        // 4. Xử lý ảnh chính (Giữ nguyên code cũ của bạn)
        $primaryImage = $product->images
            ->where('is_primary', 1)
            ->first() ?? $product->images->first();

        // 5. Gom nhóm variants (Giữ nguyên code cũ của bạn)
        $colorGroups = $product->variants
            ->groupBy(function ($v) {
                return $v->color_name ?: $v->color_code ?: 'default';
            })
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'color_name' => $first->color_name,
                    'color_code' => $first->color_code,
                    'variants'   => $group->map(function ($v) {
                        return [
                            'id'               => $v->id,
                            'size'             => $v->size,
                            'quantity'         => $v->quantity,
                            'sku'              => $v->sku,
                            'additional_price' => $v->additional_price,
                        ];
                    })->values(),
                ];
            })
            ->values();

        // 6. Trả về Response
        return response()->json([
            'product'          => $product,
            'primary_image'    => $primaryImage?->image_path ?? null,
            'images'        => $product->images, // Dư thừa vì trong product đã có images do with() ở trên, nhưng giữ lại nếu front-end cần
            'reviews'          => $reviews, // <-- Bây giờ biến này chứa object phân trang
            'color_groups'     => $colorGroups,
            'related_products' => $relatedProducts,
        ]);
    }
}