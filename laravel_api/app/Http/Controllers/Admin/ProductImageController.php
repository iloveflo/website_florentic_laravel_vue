<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;


class ProductImageController extends Controller
{
    // GET /api/admin/products/{product}/images
    public function index($productId)
    {
        $product = Product::findOrFail($productId);

        $images = $product->images()
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->get();
            // Nếu Model đã có protected $appends = ['url'] thì không cần map thủ công ở đây nữa

        return response()->json($images);
    }

    // POST /api/admin/products/{product}/images
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $request->validate([
            'image'      => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            'is_primary' => 'nullable|boolean',
        ]);

        $file = $request->file('image');

        // 1. Lấy tên file gốc (Bỏ random)
        // Lưu ý: Có thể thêm xử lý xóa khoảng trắng hoặc ký tự đặc biệt trong tên file nếu cần
        $fileName = $file->getClientOriginalName(); 

        // 2. Định nghĩa đường dẫn
        $destinationPath = public_path('uploads/products');
        $absolutePath = $destinationPath . DIRECTORY_SEPARATOR . $fileName;
        
        // Đường dẫn lưu DB
        $dbPath = 'uploads/products/' . $fileName;

        // 3. Kiểm tra file đã tồn tại chưa
        if (!File::exists($absolutePath)) {
            // CHƯA CÓ => Upload file mới
            $file->move($destinationPath, $fileName);
        } 
        // ĐÃ CÓ => Không làm gì cả, code bên dưới sẽ dùng lại $dbPath để lưu vào DB

        // --- Logic lưu Database ---
        
        // Nếu ảnh này là ảnh chính => clear flag cũ
        $isPrimary = (bool) $request->boolean('is_primary');
        if ($isPrimary) {
            $product->images()->update(['is_primary' => false]);
        }

        // Tạo record trong DB (dù file mới hay cũ thì vẫn tạo record liên kết với Product này)
        $image = $product->images()->create([
            'image_path' => $dbPath,
            'is_primary' => $isPrimary,
            'sort_order' => $product->images()->max('sort_order') + 1,
        ]);

        return response()->json([
            'message' => 'Upload ảnh thành công', // Hoặc 'Đã liên kết ảnh có sẵn'
            'data'    => $image,
        ], 201);
    }

    // DELETE /api/admin/products/images/{image}
    public function destroy($imageId)
    {
        $image = ProductImage::findOrFail($imageId);
        $currentPath = $image->image_path;

        // Xóa record trong DB trước
        $image->delete();

        // --- KIỂM TRA AN TOÀN TRƯỚC KHI XÓA FILE ---
        // Đếm xem còn record nào khác trong DB đang dùng chung file ảnh này không
        $countUsage = ProductImage::where('image_path', $currentPath)->count();

        // Chỉ xóa file vật lý nếu KHÔNG còn ai dùng nữa (count == 0)
        if ($countUsage === 0) {
            $absolutePath = public_path($currentPath);
            if ($currentPath && File::exists($absolutePath)) {
                File::delete($absolutePath);
            }
        }

        return response()->json(['message' => 'Đã xóa ảnh']);
    }

    // PATCH /api/admin/products/images/{image}/primary
    public function setPrimary($imageId)
    {
        $image = ProductImage::findOrFail($imageId);
        $product = $image->product;

        $product->images()->update(['is_primary' => false]);

        $image->is_primary = true;
        $image->save();

        return response()->json([
            'message' => 'Đã đặt ảnh chính',
            'data'    => $image,
        ]);
    }
}