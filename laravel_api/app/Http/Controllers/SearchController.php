<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function getAll(Request $request)
    {
        // 1. Khởi tạo Query Builder
        $query = Product::with(['images', 'variants'])
            ->where('status', 'active');

        // 2. Xử lý tìm kiếm
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);

            // Tìm kiếm gộp: Tên HOẶC SKU HOẶC Mô tả
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('sku', 'like', '%' . $keyword . '%')
                    // Nếu muốn tìm cả mô tả (lưu ý: tìm mô tả dài có thể hơi chậm)
                    ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }

        // 3. Sắp xếp
        // Mẹo: Nếu đang tìm kiếm, ta bỏ qua sắp xếp ngày tháng để ưu tiên độ chính xác,
        // hoặc giữ nguyên tùy bạn. Ở đây mình giữ nguyên logic cũ.
        $query->orderBy('created_at', 'desc');

        // 4. Phân trang
        $products = $query->paginate(12);

        // Quan trọng: Giữ tham số trên URL cho các trang sau
        $products->appends($request->query());

        return response()->json($products);
    }
}
