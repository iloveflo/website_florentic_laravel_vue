<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
   public function getByCategory(Request $request, $slug)
    {
       $category = Category::with('children')
            ->where('slug', $slug)
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        // Lấy chính nó + các con (nếu có)
        $targetIds = $category->children->pluck('id')->toArray();
        $targetIds[] = $category->id;

        $query = Product::with(['images', 'variants'])
            ->whereIn('category_id', $targetIds)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc');

        // --- Lọc giá ---
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        // --- Lọc theo size (qua bảng product_variants) ---
        if ($request->filled('sizes')) {
            $sizes = explode(',', $request->sizes);
            $query->whereHas('variants', function ($q) use ($sizes) {
                $q->whereIn('size', $sizes);
            });
        }

        // --- Lọc theo màu (color_name hoặc color_code) ---
        if ($request->filled('colors')) {
            $colors = explode(',', $request->colors);
            $query->whereHas('variants', function ($q) use ($colors) {
                $q->whereIn('color_name', $colors)
                  ->orWhereIn('color_code', $colors);
            });
        }

        // 4. Phân trang
        $perPage = $request->input('per_page', 12); // Mặc định 12
        if ($perPage > 12) $perPage = 12; // Nếu xin > 12 thì ép về 12
        $products = $query->paginate($perPage);

        // 5. Trả về response
        return response()->json([
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug
            ],
            'products' => $products
        ]);
    }



    public function getAll(Request $request) 
    {
        $query = Product::with(['images', 'variants'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc');

        // Lọc theo khoảng giá
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        // Lọc theo size
        if ($request->filled('sizes')) {
            $sizes = explode(',', $request->sizes);
            $query->whereHas('variants', function ($q) use ($sizes) {
                $q->whereIn('size', $sizes);
            });
        }

        // Lọc theo màu
        if ($request->filled('colors')) {
            $colors = explode(',', $request->colors);
            $query->whereHas('variants', function ($q) use ($colors) {
                $q->whereIn('color_name', $colors)
                  ->orWhereIn('color_code', $colors);
            });
        }

        // Phân trang
        $products = $query->paginate(12);

        return response()->json($products);
    }
}
