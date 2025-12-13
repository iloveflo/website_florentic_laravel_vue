<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\CartSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Review;
use App\Models\OrderItem;

class OrderController extends Controller
{
    // 1. Lấy danh sách đơn hàng (kèm bộ lọc)
    public function index(Request $request)
    {
        // Eager load dùng quan hệ đúng: orderItems
        $query = Order::query()->with(['orderItems', 'user']);

        // Tìm kiếm chung (Mã, Tên, Phone, Email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        // Lọc theo phương thức thanh toán
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Lọc theo ngày tạo
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
        }

        // Sắp xếp mặc định mới nhất
        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($orders);
    }

    // 2. Xem chi tiết đơn hàng
    public function show($order_code)
    {
        $order = Order::with([
            'orderItems',  
            'user',
            'couponUsages.coupon'
        ])
        ->where('order_code', $order_code)
        ->firstOrFail();

        return response()->json($order);
    }


    // 3. Cập nhật trạng thái đơn hàng
    public function updateStatus(Request $request, $order_code)
    {
        $request->validate([
            'order_status' => 'required|in:pending,confirmed,shipping,completed,cancelled'
        ]);

        // Eager load orderItems để duyệt qua các sản phẩm trong đơn
        $order = Order::with('orderItems')
            ->where('order_code', $order_code)
            ->firstOrFail();

        $oldStatus = $order->order_status;
        $newStatus = $request->order_status;

        // Nếu trạng thái không đổi thì return luôn
        if ($oldStatus === $newStatus) {
            return response()->json(['message' => 'Trạng thái không thay đổi']);
        }

        // Cập nhật trạng thái mới
        $order->order_status = $newStatus;

        // CASE A: Hoàn thành -> Đã thanh toán
        if ($newStatus == 'completed') {
            $order->payment_status = 'paid';
            // ---> THÊM MỚI: Gửi mail khi hoàn thành <---
            if ($order->email) {
                // Gửi trực tiếp dùng Mail::send cho nhanh, khỏi tạo class Mail
                Mail::send('review_request', ['order' => $order], function ($message) use ($order) {
                    $message->to($order->email);
                    $message->subject('Mời đánh giá đơn hàng #' . $order->order_code);
                });
            }
        }

        // CASE B: Hủy đơn (Cancelled) -> Hoàn lại kho (Restock)
        // Chỉ thực hiện khi đơn cũ KHÔNG PHẢI là cancelled (tránh cộng dồn nhiều lần)
        if ($newStatus == 'cancelled' && $oldStatus != 'cancelled') {
            foreach ($order->orderItems as $item) {
                $this->updateStock($item, 'increment');
            }
        }

        // CASE C: Khôi phục đơn hủy (Undo Cancel) -> Trừ lại kho
        // Chỉ thực hiện khi đơn cũ LÀ cancelled và chuyển sang trạng thái khác
        if ($oldStatus == 'cancelled' && $newStatus != 'cancelled') {
            foreach ($order->orderItems as $item) {
                $this->updateStock($item, 'decrement');
            }
        }

        $order->save();

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công',
            'order_status' => $order->order_status
        ]);
    }


    private function updateStock($item, $action)
    {
        // Chỉ xử lý nếu có product_id (tránh trường hợp sản phẩm đã bị xóa cứng khỏi DB)
        if ($item->product_id) {
            // Tìm biến thể dựa trên: Product ID + Size + Color
            // Lưu ý: OrderItem lưu 'color', ProductVariant lưu 'color_name'.
            // Cần đảm bảo dữ liệu 2 bên khớp nhau.
            $variant = \App\Models\ProductVariant::where('product_id', $item->product_id)
                ->where('size', $item->size)
                ->where('color_name', $item->color) // Hoặc color_code tùy logic lưu của bạn
                ->first();

            if ($variant) {
                if ($action === 'increment') {
                    $variant->increment('quantity', $item->quantity);
                } else {
                    $variant->decrement('quantity', $item->quantity);
                }
            } else {
                // FALLBACK: Trường hợp sản phẩm không có biến thể (Simple Product)
                // Nếu logic của bạn cho phép sản phẩm không có variant, thì mới dùng đoạn này.
                // Nếu 100% sản phẩm đều có variant thì có thể bỏ qua.
                /*
                $product = \App\Models\Product::find($item->product_id);
                if ($product && in_array('quantity', $product->getFillable())) {
                     if ($action === 'increment') $product->increment('quantity', $item->quantity);
                     else $product->decrement('quantity', $item->quantity);
                }
                */
            }
        }
    }


    // 2. THÊM MỚI: Lấy thông tin để hiện lên trang Review
    public function getReviewInfo($order_code)
    {
        // Lấy đơn hàng kèm theo sản phẩm (orderItems)
        $order = Order::with('orderItems')
            ->where('order_code', $order_code)
            ->firstOrFail();

        // 1. Chỉ cho phép review đơn đã hoàn thành
        if ($order->order_status !== 'completed') {
             return response()->json(['message' => 'Đơn chưa hoàn thành, chưa thể đánh giá.'], 400);
        }

        // 2. LOGIC KIỂM TRA ĐÃ REVIEW CHƯA
        // Kiểm tra trong bảng reviews xem có dòng nào chứa order_id này không
        // Hàm exists() trả về true/false rất nhanh
        $isReviewed = Review::where('order_id', $order->id)->exists();

        // 3. Gắn thêm cờ 'is_reviewed' vào kết quả trả về
        // Laravel cho phép gán thuộc tính động vào Model instance trước khi trả về JSON
        $order->is_reviewed = $isReviewed;

        return response()->json($order);
    }

    // 3. THÊM MỚI: Lưu đánh giá (Thay thế ReviewController)
    public function storeReviews(Request $request)
    {
        $request->validate([
            'order_code' => 'required|exists:orders,order_code',
            'reviews' => 'required|array',
            'reviews.*.rating' => 'required|integer|min:1|max:5',
        ]);

        $order = Order::where('order_code', $request->order_code)->firstOrFail();

        // Check xem đã đánh giá chưa (tránh spam)
        if (Review::where('order_id', $order->id)->exists()) {
            return response()->json(['message' => 'Đơn này đã đánh giá rồi'], 403);
        }

        foreach ($request->reviews as $item) {
            // Check sản phẩm có trong đơn không
            $valid = OrderItem::where('order_id', $order->id)
                              ->where('product_id', $item['product_id'])->exists();
            
            if ($valid) {
                Review::create([
                    'product_id' => $item['product_id'],
                    'user_id' => $order->user_id, // Lấy từ order
                    'order_id' => $order->id,
                    'rating' => $item['rating'],
                    'comment' => $item['comment'] ?? '',
                    'status' => 'approved'
                ]);
            }
        }

        return response()->json(['message' => 'Đánh giá thành công']);
    }

    // 4. Lấy danh sách giỏ hàng bị bỏ quên
    public function abandonedCarts()
    {
        $carts = CartSession::with(['product', 'user'])
            ->select(
                'session_id',
                'user_id',
                DB::raw('SUM(quantity * (SELECT price FROM products WHERE products.id = cart_sessions.product_id)) as total_value'),
                DB::raw('COUNT(*) as item_count'),
                DB::raw('MAX(updated_at) as last_activity')
            )
            ->groupBy('session_id', 'user_id')
            ->orderBy('last_activity', 'desc')
            ->paginate(10);

        return response()->json($carts);
    }

    // 5. Lấy chi tiết một giỏ hàng bị bỏ quên
    public function abandonedCartDetail($sessionId)
    {
        $items = CartSession::with('product')->where('session_id', $sessionId)->get();
        return response()->json($items);
    }
}
