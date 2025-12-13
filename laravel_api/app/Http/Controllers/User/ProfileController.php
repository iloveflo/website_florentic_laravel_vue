<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException; 

class ProfileController extends Controller
{
    // Lấy thông tin user đang đăng nhập
    public function show(Request $request)
    {
        // 1. Lấy user hiện tại
        $user = $request->user();

        // 2. Xử lý giải mã SỐ ĐIỆN THOẠI
        if (!empty($user->phone)) {
            try {
                $user->phone = Crypt::decryptString($user->phone);
            } catch (DecryptException $e) {
                // Nếu giải mã lỗi (do data cũ không được mã hóa), giữ nguyên giá trị gốc
                // hoặc gán bằng null tùy logic của bạn.
                // $user->phone = null; 
            }
        }

        // 3. Xử lý giải mã ĐỊA CHỈ
        if (!empty($user->address)) {
            try {
                $user->address = Crypt::decryptString($user->address);
            } catch (DecryptException $e) {
                // Tương tự như trên
            }
        }

        // 4. Trả về json
        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        // 1. Validate dữ liệu
        $request->validate([
            'full_name' => 'required|string|max:100',
            'phone'     => 'required|digits:10',
            'address'   => 'nullable|string|max:255',
            'avatar'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'full_name.required' => 'Vui lòng nhập họ tên.',
            'phone.required'     => 'Vui lòng nhập số điện thoại.',
            'phone.digits'       => 'Số điện thoại phải bao gồm đúng 10 chữ số.',
            'avatar.image'       => 'File tải lên phải là hình ảnh.',
            'avatar.max'         => 'Ảnh không được vượt quá 2MB.',
        ]);

        // 2. Xử lý Avatar
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            // Lấy tên file gốc (ví dụ: anh-dai-dien.jpg)
            $fileName = $file->getClientOriginalName();

            // Đường dẫn thư mục đích: laravel_api/public/uploads/avatar
            $destinationPath = public_path('uploads/avatar');

            // Tạo thư mục nếu chưa có
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            // Kiểm tra xem file đã tồn tại trong thư mục chưa
            if (!File::exists($destinationPath . '/' . $fileName)) {
                // Chưa có thì mới move vào (Upload)
                $file->move($destinationPath, $fileName);
            }
            // Nếu có rồi thì bỏ qua bước upload, chỉ lấy tên để lưu vào DB

            // Cập nhật đường dẫn vào database
            $user->avatar = 'uploads/avatar/' . $fileName;
        }

        // 3. Cập nhật thông tin text (MÃ HÓA TRƯỚC KHI LƯU)
        $user->full_name = $request->full_name;

        // Mã hóa số điện thoại
        $user->phone = Crypt::encryptString($request->phone);

        // Mã hóa địa chỉ (nếu có nhập)
        if ($request->filled('address')) {
            $user->address = Crypt::encryptString($request->address);
        } else {
            $user->address = null;
        }

        $user->save();

        // 4. Trả về dữ liệu (Lưu ý: Trả về data chưa giải mã hoặc giải mã lại nếu muốn hiển thị ngay)
        // Để tiện cho Frontend cập nhật UI ngay lập tức, ta nên trả về dữ liệu DẠNG THÔ (đã giải mã)
        // vì biến $user lúc này đang giữ giá trị mã hóa.

        $responseData = $user->toArray();
        $responseData['phone'] = $request->phone;     // Trả lại số user vừa nhập
        $responseData['address'] = $request->address; // Trả lại địa chỉ user vừa nhập

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật hồ sơ thành công!',
            'data' => $responseData
        ]);
    }
}
