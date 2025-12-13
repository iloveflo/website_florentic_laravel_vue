<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductDetailsController extends Controller
{
    /**
     * GET /api/products/{slug}
     * Lấy chi tiết sản phẩm cho trang người dùng
     */
    public function show($slug)
    {
        // 1. Lấy sản phẩm chính
        $product = Product::with([
            'category:id,name,slug',
            'images' => function ($query) {
                $query->orderByDesc('is_primary')
                    ->orderBy('sort_order');
            },
            'variants',
            'reviews' => function ($q) {
                $q->with(['order:id,full_name'])->latest();
            },
        ])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->first();

        // 2. Nếu không tìm thấy thì trả 404 luôn, KHÔNG dùng $product nữa
        if (!$product) {
            return response()->json([
                'message' => 'Sản phẩm không tồn tại'
            ], 404);
        }

        // 3. Lấy sản phẩm liên quan (cùng category, trừ chính nó)
        $relatedProducts = Product::with(['images'])
            ->where('status', 'active')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();

        // 4. Ảnh chính
        $primaryImage = $product->images
            ->where('is_primary', 1)
            ->first() ?? $product->images->first();

        // 5. Gom nhóm variant theo màu (nếu bạn dùng)
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

        return response()->json([
            'product'          => $product,
            'primary_image'    => $primaryImage?->image_path ?? null,
            'images'           => $product->images,
            'reviews'          => $product->reviews,
            'color_groups'     => $colorGroups,
            'related_products' => $relatedProducts,
        ]);
    }
}
