<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    // Danh sách cây danh mục (cha + con)
    public function tree()
    {
        $categories = Category::with(['children' => function ($q) {
                $q->where('status', 'active')->orderBy('name');
            }])
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    // Danh sách phẳng (dùng cho select trong admin nếu muốn)
    public function index()
    {
        $categories = Category::where('status', 'active')
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }
}
