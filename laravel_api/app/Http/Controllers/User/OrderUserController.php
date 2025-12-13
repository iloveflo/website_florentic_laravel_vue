<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderUserController extends Controller
{
    public function index(Request $request)
    {
        // Nhận diện User qua Token (Sanctum)
        $user = $request->user('sanctum');

        // Khởi tạo query
        $query = Order::with('orderItems');

        if ($user) {
            // TRƯỜNG HỢP 1: Đã đăng nhập
            // -> CHỈ lấy theo user_id
            $query->where('user_id', $user->id);
        } else {
            // TRƯỜNG HỢP 2: Khách vãng lai
            $sessionId = $request->input('session_id');

            if ($sessionId) {
                // -> Lấy theo session_id VÀ user_id phải bằng NULL
                // (Đảm bảo đây là đơn của khách, không phải đơn của thành viên)
                $query->where('session_id', $sessionId)
                      ->whereNull('user_id'); 
            } else {
                // Không có thông tin định danh -> Trả về rỗng
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'current_page' => 1,
                        'data' => [],
                        'total' => 0
                    ]
                ]);
            }
        }

        // 3. Xử lý lọc theo trạng thái (status)
        if ($request->has('status') && $request->status != 'all') {
            $query->where('order_status', $request->status);
        }

        // 4. Sắp xếp đơn mới nhất lên đầu
        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        // 5. Trả về kết quả
        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    /**
     * Hàm lấy chi tiết một đơn hàng cụ thể (Optional - Thường sẽ cần dùng)
     */
    public function show(Request $request, $orderCode)
    {
        $user = $request->user('sanctum');

        $query = Order::with(['orderItems', 'couponUsages'])
            ->where('order_code', $orderCode);

        // Bảo mật: Chỉ cho phép xem nếu đúng chủ sở hữu
        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $sessionId = $request->input('session_id');
            if ($sessionId) {
                $query->where('session_id', $sessionId);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }
        }

        $order = $query->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $order
        ]);
    }
}