<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    /**
     * GET /api/admin/coupons
     * Lấy danh sách (có phân trang)
     */
    public function index(Request $request)
    {
        // 1. Khởi tạo Query Builder
        $query = Coupon::query();

        // 2. Lọc theo trạng thái (status: active/inactive)
        // Frontend gửi lên: ?status=active hoặc ?status=inactive
        $query->when($request->status, function ($q) use ($request) {
            if ($request->status !== 'all') {
                $q->where('status', $request->status);
            }
        });

        // 3. Lọc theo tình trạng hết hạn (expired)
        // Frontend gửi lên: ?filter_expiry=expired (đã hết hạn) hoặc ?filter_expiry=valid (còn hạn)
        $query->when($request->filter_expiry, function ($q) use ($request) {
            if ($request->filter_expiry === 'expired') {
                // Lấy các mã có ngày kết thúc nhỏ hơn thời điểm hiện tại
                $q->where('end_date', '<', now());
            } elseif ($request->filter_expiry === 'valid') {
                // Lấy các mã chưa hết hạn
                $q->where('end_date', '>=', now());
            }
        });

        // 4. (Bonus) Tìm kiếm theo mã Code
        // Frontend gửi lên: ?keyword=SALE
        $query->when($request->keyword, function ($q) use ($request) {
            $q->where('code', 'like', '%' . $request->keyword . '%');
        });

        // 5. Thực thi query và phân trang
        // withQueryString() giúp giữ lại các tham số lọc khi bấm sang trang 2, 3...
        $coupons = $query->latest()->paginate(10)->withQueryString();

        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách mã giảm giá thành công.',
            'data' => $coupons
        ], 200);
    }

    /**
     * POST /api/admin/coupons
     * Tạo mới
     */
    public function store(Request $request)
    {
        // 1. Validate
        $validator = Validator::make($request->all(), [
            'code'            => 'required|string|max:50|unique:coupons,code|regex:/^[A-Z0-9]+$/',
            'description'     => 'nullable|string',
            'discount_type'   => 'required|in:percent,fixed',
            'discount_value'  => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount'    => 'nullable|numeric|min:0',
            'usage_limit'     => 'nullable|integer|min:1',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after:start_date',
            'status'          => 'required|in:active,inactive',
        ], [
            'code.unique'      => 'Mã giảm giá này đã tồn tại.',
            'code.regex'       => 'Mã giảm giá chỉ được chứa chữ hoa và số, không dấu.',
            'end_date.after'   => 'Ngày kết thúc phải sau ngày bắt đầu.',
        ]);

        // Trả lỗi 422 nếu validate fail
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Format dữ liệu
        $data = $request->all();
        $data['code'] = strtoupper($request->code);
        $data['used_count'] = 0;

        // 3. Tạo mới
        try {
            $coupon = Coupon::create($data);

            return response()->json([
                'status' => true,
                'message' => 'Tạo mã giảm giá thành công.',
                'data' => $coupon
            ], 201); // 201 Created

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/admin/coupons/{id}
     * Xem chi tiết (để fill vào form Edit bên React)
     */
    public function show($id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy mã giảm giá.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $coupon
        ], 200);
    }

    /**
     * PUT/PATCH /api/admin/coupons/{id}
     * Cập nhật
     */
    public function update(Request $request, $id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy mã giảm giá.'], 404);
        }

        // 1. Validate
        $validator = Validator::make($request->all(), [
            'code'            => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9]+$/', Rule::unique('coupons')->ignore($coupon->id)],
            'description'     => 'nullable|string',
            'discount_type'   => 'required|in:percent,fixed',
            'discount_value'  => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount'    => 'nullable|numeric|min:0',
            'usage_limit'     => 'nullable|integer|min:1',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after:start_date',
            'status'          => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Update
        try {
            $data = $request->all();
            $data['code'] = strtoupper($request->code);

            $coupon->update($data);

            return response()->json([
                'status' => true,
                'message' => 'Cập nhật thành công.',
                'data' => $coupon
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/admin/coupons/{id}
     * Xóa
     */
    public function destroy($id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy mã giảm giá.'], 404);
        }

        // Logic bảo vệ: Đã dùng thì không xóa
        if ($coupon->used_count > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Mã này đã có người sử dụng, không thể xóa. Hãy chuyển trạng thái sang Inactive.'
            ], 400); // 400 Bad Request
        }

        try {
            $coupon->delete();
            return response()->json([
                'status' => true,
                'message' => 'Đã xóa mã giảm giá.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }
}
