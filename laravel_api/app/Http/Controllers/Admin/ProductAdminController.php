<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductAdminController extends Controller
{
    // GET /api/admin/products
    public function index(Request $request)
    {
        $query = Product::with([
            'category',
            'images',
            'mainImage',
            'variants',
        ])->orderBy('id', 'asc');

        // Tìm kiếm theo tên hoặc SKU
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Lọc theo category con
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Lọc theo nhóm cha (Áo / Quần / Phụ kiện / Bộ sưu tập)
        if ($parentId = $request->input('category_parent_id')) {
            $query->whereHas('category', function ($q) use ($parentId) {
                $q->where('parent_id', $parentId)
                    ->orWhere('id', $parentId);
            });
        }

        $perPage = (int) $request->input('per_page', 10);

        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    // POST /api/admin/products
    public function store(ProductRequest $request)
    {
        $data = $request->validated();

        // Tách variants ra khỏi data chính
        $variants = $data['variants'] ?? [];
        unset($data['variants']);

        // Tạo slug & SKU nếu chưa có
        $data['slug'] = Str::slug($data['name']);
        $data['sku']  = $data['sku'] ?? strtoupper(Str::random(8));

        // Nếu không có cost_price thì cho = price
        if (!isset($data['cost_price']) || $data['cost_price'] === null) {
            $data['cost_price'] = $data['price'];
        }

        // ❌ KHÔNG còn $data['quantity'] vì bảng products không có cột này nữa

        // Tạo sản phẩm
        $product = Product::create($data);

        // Tạo biến thể
        foreach ($variants as $variant) {
            $product->variants()->create([
                'color_name'       => $variant['color_name'] ?? '',
                'color_code'       => $variant['color_code'] ?? null,
                'size'             => $variant['size'],
                'sku'              => $variant['sku'] ?? null,
                'quantity'         => $variant['quantity'] ?? 0,
                'additional_price' => $variant['additional_price'] ?? 0,
            ]);
        }

        // ❌ Không cập nhật quantity trên bảng products nữa

        return response()->json([
            'message' => 'Thêm sản phẩm thành công!',
            'data'    => $product->load('category', 'variants'),
        ], 201);
    }

    // GET /api/admin/products/{id}
    public function show($id)
    {
        $product = Product::with([
            'category',
            'images',
            'mainImage',
            'variants',
        ])->findOrFail($id);

        return response()->json($product);
    }

    // PUT /api/admin/products/{id}
    public function update(ProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);

        $data     = $request->validated();
        $variants = $data['variants'] ?? [];
        unset($data['variants']);

        $data['slug'] = Str::slug($data['name']);

        // Giữ SKU gốc, không cho sửa trong form này
        unset($data['sku']);

        // Nếu không gửi cost_price thì giữ cost_price cũ (hoặc = price mới)
        if (!isset($data['cost_price']) || $data['cost_price'] === null) {
            $data['cost_price'] = $product->cost_price ?? $data['price'] ?? $product->price;
        }

        // Cập nhật sản phẩm
        $product->update($data);

        // Clear variants cũ và tạo lại
        $product->variants()->delete();

        foreach ($variants as $variant) {
            $product->variants()->create([
                'color_name'       => $variant['color_name'] ?? '',
                'color_code'       => $variant['color_code'] ?? null,
                'size'             => $variant['size'],
                'sku'              => $variant['sku'] ?? null,
                'quantity'         => $variant['quantity'] ?? 0,
                'additional_price' => $variant['additional_price'] ?? 0,
            ]);
        }

        // ❌ Không cập nhật quantity trên bảng products nữa

        return response()->json([
            'message' => 'Cập nhật sản phẩm thành công',
            'data'    => $product->load('category', 'variants'),
        ]);
    }

    // DELETE /api/admin/products/{id}
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Đã xóa sản phẩm']);
    }
}
