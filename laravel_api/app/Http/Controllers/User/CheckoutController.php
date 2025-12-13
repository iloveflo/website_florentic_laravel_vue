<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt; // Thêm thư viện Crypt
use Illuminate\Contracts\Encryption\DecryptException; // Để bắt lỗi giải mã
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Models\CartSession;
use App\Models\ProductVariant;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    /**
     * Lấy thông tin hiển thị lên form
     * Cần GIẢI MÃ (Decrypt) dữ liệu từ DB để Frontend hiển thị được
     */
    public function getCheckoutInfo(Request $request)
    {
        $user = $request->user('sanctum');

        if ($user) {
            // Xử lý giải mã an toàn (tránh lỗi nếu dữ liệu cũ chưa mã hóa hoặc bị lỗi)
            $phone = '';
            $address = '';

            try {
                // Kiểm tra nếu có dữ liệu thì mới giải mã
                $phone = $user->phone ? Crypt::decryptString($user->phone) : '';
            } catch (DecryptException $e) {
                // Nếu giải mã lỗi (do data cũ là plain text), ta lấy nguyên gốc
                $phone = $user->phone;
            }

            try {
                $address = $user->address ? Crypt::decryptString($user->address) : '';
            } catch (DecryptException $e) {
                $address = $user->address;
            }

            return response()->json([
                'is_logged_in' => true,
                'customer_info' => [
                    'id'        => $user->id,
                    'full_name' => $user->full_name,
                    'email'     => $user->email,
                    'phone'     => $phone,   // Đã giải mã
                    'address'   => $address, // Đã giải mã
                ]
            ]);
        }

        // Khách vãng lai
        return response()->json([
            'is_logged_in' => false,
            'customer_info' => [
                'full_name' => '',
                'email'     => '',
                'phone'     => '',
                'address'   => '',
            ]
        ]);
    }

    /**
     * Xử lý đặt hàng
     */
    public function processCheckout(Request $request)
    {
        // 1. Validate (Giữ nguyên)
        $validatedData = $request->validate([
            'full_name'      => 'required|string|max:255',
            'email'          => 'required|email|max:255',
            'phone'          => 'required|string|max:20',
            'address'        => 'required|string|max:500',
            'payment_method' => 'required|in:cod,vnpay',
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.size'     => 'nullable|string',
            'items.*.color'    => 'nullable|string',
            'note'           => 'nullable|string',
            'session_id'     => 'required|string',
            'coupon_code'    => 'nullable|string|exists:coupons,code'
        ]);

        DB::beginTransaction();
        try {
            $user = $request->user('sanctum');

            // 2. Update User (Giữ nguyên)
            if ($user) {
                $user->full_name = $validatedData['full_name'];
                $user->phone = $validatedData['phone'] ? Crypt::encryptString($validatedData['phone']) : null;
                $user->address = $validatedData['address'] ? Crypt::encryptString($validatedData['address']) : null;
                $user->save();
            }

            // 3. Xử lý Logic Lọc Dần & Trừ Kho
            $subtotal = 0;
            $orderItemsData = [];

            foreach ($validatedData['items'] as $item) {
                $product = Product::find($item['id']);

                // --- BƯỚC 1: KHỞI TẠO QUERY (Lớp lọc 1: Product ID) ---
                // Bắt đầu tìm kiếm trong bảng variants dựa trên product_id
                $query = ProductVariant::where('product_id', $product->id);

                // --- BƯỚC 2: LỌC THEO SIZE (Lớp lọc 2) ---
                if (!empty($item['size'])) {
                    // Nếu khách chọn size cụ thể -> Tìm đúng size đó
                    $query->where('size', $item['size']);
                } else {
                    // Nếu khách KHÔNG chọn size -> Tìm variant nào có size là NULL hoặc rỗng
                    // (Dành cho sản phẩm không có thuộc tính size)
                    $query->where(function ($q) {
                        $q->whereNull('size')->orWhere('size', '');
                    });
                }

                // --- BƯỚC 3: LỌC THEO MÀU (Lớp lọc 3) ---
                if (!empty($item['color'])) {
                    // Nếu khách chọn màu cụ thể -> Tìm đúng màu đó
                    $query->where('color_name', $item['color']);
                } else {
                    // Nếu khách KHÔNG chọn màu -> Tìm variant có màu là NULL hoặc rỗng
                    $query->where(function ($q) {
                        $q->whereNull('color_name')->orWhere('color_name', '');
                    });
                }

                // --- BƯỚC 4: KHÓA DỮ LIỆU & LẤY KẾT QUẢ ---
                // Dùng lockForUpdate() ở cuối cùng để khóa dòng dữ liệu tìm được
                $variant = $query->lockForUpdate()->first();

                // --- BƯỚC 5: KIỂM TRA & TRỪ KHO ---

                // Case A: Không tìm thấy biến thể phù hợp
                if (!$variant) {
                    // Tạo thông báo lỗi chi tiết
                    $errorMsg = "Sản phẩm {$product->name}";
                    if (!empty($item['size'])) $errorMsg .= " - Size: {$item['size']}";
                    if (!empty($item['color'])) $errorMsg .= " - Màu: {$item['color']}";
                    $errorMsg .= " không tồn tại trong kho.";

                    throw new \Exception($errorMsg);
                }

                // Case B: Hết hàng hoặc không đủ số lượng
                if ($variant->quantity < $item['quantity']) {
                    throw new \Exception("Sản phẩm {$product->name} (Kho còn: {$variant->quantity}) không đủ đáp ứng số lượng {$item['quantity']}.");
                }

                // Thực hiện trừ kho
                $variant->decrement('quantity', $item['quantity']);

                // --- BƯỚC 6: TÍNH TIỀN ---
                // Giá = Giá SP + Giá thêm của Variant (nếu có)
                $basePrice = $product->sale_price > 0 ? $product->sale_price : $product->price;
                $finalPrice = $basePrice + ($variant->additional_price ?? 0);

                $lineTotal = $finalPrice * $item['quantity'];
                $subtotal += $lineTotal;

                $orderItemsData[] = [
                    'product_id'    => $product->id,
                    'product_name'  => $product->name,
                    'product_image' => $product->main_image_path,
                    'size'          => $item['size'] ?? null,
                    'color'         => $item['color'] ?? null,
                    'price'         => $finalPrice,
                    'quantity'      => $item['quantity'],
                    'subtotal'      => $lineTotal,
                ];
            }


            // --- BẮT ĐẦU LOGIC KHUYẾN MẠI ---
            $discountAmount = 0;
            $couponIdApplied = null; // Để lưu lại ID coupon đã dùng

            if (!empty($request->coupon_code)) {
                // Lock coupon để tránh race condition (nhiều người dùng mã cuối cùng cùng lúc)
                $coupon = Coupon::where('code', $request->coupon_code)
                    ->lockForUpdate() 
                    ->first();

                if (!$coupon) {
                    throw new \Exception("Mã giảm giá không tồn tại.");
                }

                // A. Check trạng thái và thời gian
                $now = Carbon::now();
                if ($coupon->status !== 'active') {
                    throw new \Exception("Mã giảm giá đang bị khóa.");
                }
                if ($coupon->start_date && $now->lt($coupon->start_date)) {
                    throw new \Exception("Mã giảm giá chưa đến đợt áp dụng.");
                }
                if ($coupon->end_date && $now->gt($coupon->end_date)) {
                    throw new \Exception("Mã giảm giá đã hết hạn.");
                }

                // B. Check giới hạn số lượng toàn hệ thống
                if ($coupon->usage_limit > 0 && $coupon->used_count >= $coupon->usage_limit) {
                    throw new \Exception("Mã giảm giá đã hết lượt sử dụng.");
                }

                // C. Check giá trị đơn hàng tối thiểu
                if ($coupon->min_order_value > 0 && $subtotal < $coupon->min_order_value) {
                    throw new \Exception("Đơn hàng chưa đạt giá trị tối thiểu để dùng mã này.");
                }

                // D. Check lịch sử sử dụng của User (Nếu đã login)
                if ($user) {
                    $hasUsed = CouponUsage::where('coupon_id', $coupon->id)
                        ->where('user_id', $user->id)
                        ->exists();
                    if ($hasUsed) {
                        throw new \Exception("Bạn đã sử dụng mã giảm giá này rồi.");
                    }
                }

                // E. Tính toán số tiền giảm
                if ($coupon->discount_type === 'fixed') {
                    $discountAmount = $coupon->discount_value;
                } elseif ($coupon->discount_type === 'percent') {
                    $discountAmount = $subtotal * ($coupon->discount_value / 100);
                    // Áp dụng mức giảm tối đa (nếu có)
                    if ($coupon->max_discount > 0) {
                        $discountAmount = min($discountAmount, $coupon->max_discount);
                    }
                }

                // Đảm bảo không giảm quá giá trị đơn hàng
                $discountAmount = min($discountAmount, $subtotal);
                
                // Đánh dấu để cập nhật sau khi tạo Order
                $couponIdApplied = $coupon->id;
                
                // Tăng biến đếm sử dụng
                $coupon->increment('used_count');
            }


            // 4. Tạo Order & Order Items
            $shippingFee = 0;
            // Tính tổng cuối cùng: Subtotal + Ship - Discount
            $totalAmount = $subtotal + $shippingFee - $discountAmount;
            // Đảm bảo không âm
            $totalAmount = max($totalAmount, 0);

            $orderCode = 'ORD' . time() . strtoupper(Str::random(4));

            $order = new Order();
            $order->user_id         = $user ? $user->id : null;
            $order->order_code      = $orderCode;
            $order->full_name       = $validatedData['full_name'];
            $order->email           = $validatedData['email'];
            $order->phone           = $validatedData['phone'];
            $order->address         = $validatedData['address'];
            $order->subtotal        = $subtotal;
            $order->shipping_fee    = $shippingFee;
            $order->discount_amount = $discountAmount;
            $order->total_amount    = $totalAmount;
            $order->payment_method  = $validatedData['payment_method'];
            $order->note            = $request->note;
            $order->order_status    = 'pending';
            $order->payment_status  = 'pending';
            $order->session_id      = $request->session_id;
            $order->save();

            foreach ($orderItemsData as $itemData) {
                $orderItem = new OrderItem($itemData);
                $orderItem->order_id = $order->id;
                $orderItem->save();
            }


            // --- LƯU LỊCH SỬ DÙNG COUPON  ---
            if ($couponIdApplied) {
                CouponUsage::create([
                    'coupon_id' => $couponIdApplied,
                    'user_id'   => $user ? $user->id : null,
                    'order_id'  => $order->id,
                ]);
            }
            // 5. Xóa giỏ hàng & Commit
            CartSession::where('session_id', $request->session_id)->delete();
            DB::commit();

            try {
                // 1. Tạo link dựa trên URL của Backend hiện tại
                // Hàm url() sẽ tự lấy domain trong file .env (APP_URL) hoặc domain request hiện tại
                // Ví dụ kết quả: http://localhost:8000/user/orders
                $trackingLink = url('/user/orders');

                // 2. Logic phân loại khách hàng
                if (!$user) {
                    // Nếu là khách vãng lai -> Thêm session_id
                    // Kết quả: http://localhost:8000/user/orders?session_id=...
                    $trackingLink .= '?session_id=' . $request->session_id;
                } 
                
                // 3. Gửi Mail
                \Illuminate\Support\Facades\Mail::send('order_confirm', [
                    'order'        => $order,
                    'items'        => $orderItemsData,
                    'trackingLink' => $trackingLink
                ], function ($message) use ($order) {
                    $message->to($order->email, $order->full_name)
                            ->subject('Xác nhận đơn hàng #' . $order->order_code);
                });

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gửi mail thất bại: " . $e->getMessage());
            }

            // 6. Response 
            if ($validatedData['payment_method'] === 'cod') {
                return response()->json([
                    'success' => true,
                    'type' => 'cod',
                    'message' => 'Đặt hàng thành công!',
                    'order_code' => $orderCode
                ]);
            }

            // --- XỬ LÝ THANH TOÁN VNPAY ---
            if ($validatedData['payment_method'] === 'vnpay') {

                // 1. Cấu hình
                $vnp_TmnCode = config('vnpay.tmn_code');
                $vnp_HashSecret = config('vnpay.hash_secret');
                $vnp_Url = config('vnpay.url');
                $vnp_Returnurl = config('vnpay.return_url');

                // 2. DỮ LIỆU GỬI ĐI
                $vnp_TxnRef = $orderCode;
                $vnp_Amount = $totalAmount * 100;
                $vnp_IpAddr = request()->ip();
                $vnp_Locale = "vn";
                $vnp_OrderInfo = "Thanh toan don hang " . $vnp_TxnRef; // Ghi rõ mã đơn
                $vnp_OrderType = "billpayment";

                // 3. TẠO MẢNG DỮ LIỆU
                $inputData = array(
                    "vnp_Version" => "2.1.0",
                    "vnp_TmnCode" => $vnp_TmnCode,
                    "vnp_Amount" => $vnp_Amount,
                    "vnp_Command" => "pay",
                    "vnp_CreateDate" => date('YmdHis'),
                    "vnp_CurrCode" => "VND",
                    "vnp_IpAddr" => $vnp_IpAddr,
                    "vnp_Locale" => $vnp_Locale,
                    "vnp_OrderInfo" => $vnp_OrderInfo,
                    "vnp_OrderType" => $vnp_OrderType,
                    "vnp_ReturnUrl" => $vnp_Returnurl,
                    "vnp_TxnRef" => $vnp_TxnRef, // Mã tham chiếu khớp với DB
                );

                // 4. SẮP XẾP DATA THEO KEY (Bắt buộc để tạo chữ ký đúng)
                ksort($inputData);

                // 5. TẠO URL & CHỮ KÝ (HASH)
                $query = "";
                $i = 0;
                $hashdata = "";
                foreach ($inputData as $key => $value) {
                    if ($i == 1) {
                        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                    } else {
                        $hashdata .= urlencode($key) . "=" . urlencode($value);
                        $i = 1;
                    }
                    $query .= urlencode($key) . "=" . urlencode($value) . '&';
                }

                $vnp_Url = $vnp_Url . "?" . $query;
                if (isset($vnp_HashSecret)) {
                    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
                }

                // 6. TRẢ VỀ JSON CHO FRONTEND (VUEJS)
                return response()->json([
                    'success' => true,
                    'message' => 'Tạo link thanh toán thành công',
                    'payment_method' => 'vnpay',
                    'payment_url' => $vnp_Url
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }


    public function vnpayCallback(Request $request)
    {
        $vnp_HashSecret = config('vnpay.hash_secret');
        $inputData = array();

        // 1. Lấy tham số
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        // 2. Tách hash để kiểm tra
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);

        // 3. Sắp xếp và tạo chuỗi hash
        ksort($inputData);
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

        // 4. Các biến trạng thái
        $isValidSignature = $secureHash == $vnp_SecureHash;
        $isSuccess = $inputData['vnp_ResponseCode'] == '00';

        // 5. Xử lý Đơn hàng
        if ($isValidSignature) {
            // Lấy đơn hàng
            $orderCode = $inputData['vnp_TxnRef'];
            $order = Order::where('order_code', $orderCode)->first();

            if ($order) {
                if ($isSuccess) {
                    // TRƯỜNG HỢP THÀNH CÔNG
                    if ($order->payment_status != 'paid') {
                        $order->payment_status = 'paid';
                        $order->transaction_id = $inputData['vnp_TransactionNo'] ?? null;
                        $order->save();
                    }
                } else {
                    $order->payment_status = 'failed';
                    $order->save();
                }
            }
        }

        // 6. TRẢ VỀ JSON CHO VUE (QUAN TRỌNG)
        // Phải trả về đúng cấu trúc mà file Vue "PaymentResult.vue" đang chờ
        return response()->json([
            'status' => ($isValidSignature && $isSuccess) ? 'success' : 'failed',
            'signature_valid' => $isValidSignature,
            'data' => [
                // Trả lại nguyên các tham số VNPAY để Vue hiển thị lên bảng
                'vnp_TxnRef' => $inputData['vnp_TxnRef'] ?? null,
                'vnp_Amount' => $inputData['vnp_Amount'] ?? 0, // Trả về số nguyên gốc (nhân 100), Vue sẽ tự chia
                'vnp_OrderInfo' => $inputData['vnp_OrderInfo'] ?? null,
                'vnp_ResponseCode' => $inputData['vnp_ResponseCode'] ?? null,
                'vnp_TransactionNo' => $inputData['vnp_TransactionNo'] ?? null,
                'vnp_BankCode' => $inputData['vnp_BankCode'] ?? null,
                'vnp_PayDate' => $inputData['vnp_PayDate'] ?? null,
            ]
        ]);
    }
}
