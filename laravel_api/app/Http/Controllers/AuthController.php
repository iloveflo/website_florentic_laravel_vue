<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use Mews\Captcha\Facades\Captcha;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Login user
     */
    public function login(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $request->validate([
            'email'      => 'required|email',
            'password'   => 'required|string',
            'rememberMe' => 'sometimes|boolean',
            'key'        => 'required|string',
            'captcha'    => 'required|captcha_api:' . $request->key . ',default',
        ], [
            'captcha.required'    => 'Vui lòng nhập mã xác nhận.',
            'captcha.captcha_api' => 'Mã xác nhận không chính xác.',
        ]);

        // 2. Kiểm tra User tồn tại
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Sai thông tin đăng nhập'], 404);
        }

        // 3. Kiểm tra Mật khẩu
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Sai thông tin đăng nhập'], 401);
        }

        // --- XỬ LÝ THỜI GIAN HẾT HẠN TOKEN (LOGIC MỚI Ở ĐÂY) ---
        // Nếu là admin thì 5 tiếng (300 phút), còn user thường thì 1 tiếng (60 phút)
        if ($user->role === 'admin') {
            $expiresAt = now()->addHours(5);
            try {
                PersonalAccessToken::where('expires_at', '<', now())->delete();
            } catch (\Exception $e) {
                Log::error('Lỗi dọn dẹp Token (Sanctum Prune): ' . $e->getMessage());
            }
        } else {
            $expiresAt = now()->addHour(); // 1 tiếng
        }

        // Tạo token với thời gian hết hạn cụ thể (tham số thứ 3)
        $tokenName = 'auth-token';
        $token = $user->createToken($tokenName, ['*'], $expiresAt)->plainTextToken;

        // --- Xử lý Remember Me (Giữ nguyên logic của bạn) ---
        if ($request->rememberMe && $user->role !== 'admin') {
            $user->remember_token = bin2hex(random_bytes(60));
            $user->save();
        }

        // 4. Trả về kết quả
        return response()->json([
            'message' => 'Đăng nhập thành công',
            'user'    => $user,
            'token'   => $token,
            'expires_at' => $expiresAt->toIso8601String(),
        ], 200);
    }
    // Current authenticated user info
    public function me(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(null, 401);
        }

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'username' => $user->username,
            'full_name' => $user->full_name,
            'avatar' => $this->formatAvatar($user->avatar),
            'role' => $user->role,
        ]);
    }

    // helper to normalize avatar paths returned by API
    private function formatAvatar($avatar)
    {
        if (! $avatar) return null;
        $a = (string) $avatar;
        if (Str::startsWith($a, 'http')) return $a;
        if (Str::startsWith($a, '/')) return $a;
        if (Str::startsWith($a, 'public/uploads/')) {
            return '/' . preg_replace('/^public\//', '', $a);
        }
        if (Str::startsWith($a, 'uploads/')) {
            return '/' . $a;
        }
        if (Str::startsWith($a, 'storage/')) {
            return '/' . $a;
        }
        // fallback assume storage disk path
        return '/storage/' . ltrim($a, '/');
    }

    /**
     * Logout (nếu dùng token)
     */
    public function logout(Request $request)
    {
        // Lấy user hiện tại từ request
        $user = $request->user();

        // [QUAN TRỌNG] Kiểm tra xem user có tồn tại không (đề phòng trường hợp middleware lỗi)
        if ($user) {
            // 1. Xóa token hiện tại (Token mà user đang dùng để gửi request này)
            // Cần kiểm tra xem currentAccessToken có tồn tại không trước khi delete để tránh lỗi "Call on null"
            if ($user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }

            // 2. Xóa remember_token trong DB
            // Sử dụng forceFill để đảm bảo an toàn dữ liệu và save
            $user->forceFill([
                'remember_token' => null,
            ])->save();
        }

        return response()->json(['message' => 'Đăng xuất thành công'], 200);
    }

    public function register(Request $request)
    {
        // Validate dữ liệu
        $request->validate([
            'username'  => 'required|string|max:50|unique:users,username|regex:/^[a-zA-Z0-9_]+$/',
            'email'     => [
                'required',
                'string',
                'email',
                'max:100',
                'unique:users,email'
            ],
            'password'  => ['required', 'confirmed', Password::defaults()],
            'full_name' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[\p{L}\s]+$/u' // \p{L} là mọi ký tự chữ cái (Unicode), u là hỗ trợ UTF-8
            ],
            'phone'     => 'nullable|digits:10', // chỉ số, 10 chữ số
            'address'   => 'nullable|string|max:255',
            'avatar'    => 'nullable|image|max:2048', // tối đa 2MB
        ]);


        $avatarPath = null;

        if ($request->hasFile('avatar')) {
            $avatarFile = $request->file('avatar');
            $filename = $avatarFile->getClientOriginalName();
            $destination = public_path('uploads/avatar');

            // Tạo folder nếu chưa tồn tại
            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }

            // Kiểm tra xem đã có file cùng tên chưa
            if (!File::exists($destination . '/' . $filename)) {
                // Chưa có -> move file lên
                $avatarFile->move($destination, $filename);
            }
            // Dù file đã tồn tại hay mới upload, lưu đường dẫn relative vào DB
            $avatarPath = 'uploads/avatar/' . $filename;
        }

        // Tạo user mới
        $user = User::create([
            'username'  => $request->username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'full_name' => $request->full_name,
            'phone'     => $request->phone ? Crypt::encryptString($request->phone) : null,
            'address'   => $request->address ? Crypt::encryptString($request->address) : null,
            'avatar'    => $avatarPath,
            'role'      => 'user', // mặc định role là user
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'message' => 'Đăng ký thành công!',
            'user'    => $user,
        ], 201);
    }


    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email không tồn tại trong hệ thống'], 404);
        }

        // Tạo token reset mật khẩu
        $rawToken = Str::random(64);

        // Lưu token thật vào DB
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $rawToken,
                'created_at' => Carbon::now()
            ]
        );

        // Mã hóa email + token
        $data = json_encode([
            'email' => $request->email,
            'token' => $rawToken
        ]);
        $encrypted = Crypt::encryptString($data);

        // Lấy base URL hiện tại (scheme + host + port nếu có)
        $baseUrl = $request->getSchemeAndHttpHost(); // ví dụ: http://localhost:8000 hoặc https://example.com

        // Tạo link reset đầy đủ
        $resetLink = $baseUrl . '/reset-password?code=' . urlencode($encrypted);

        // Gửi email
        Mail::send('reset_password', ['link' => $resetLink], function ($message) use ($request) {
            $message->to($request->email)
                ->subject('Khôi phục mật khẩu - FLORENTIC');
        });

        return response()->json(['message' => 'Email khôi phục đã được gửi'], 200);
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'password' => 'required|min:8'
        ]);

        try {
            // Giải mã dữ liệu
            $decoded = Crypt::decryptString($request->code);
            $data = json_decode($decoded, true);

            $email = $data['email'];
            $token = $data['token'];
        } catch (\Exception $e) {
            return response()->json(['message' => 'Mã đặt lại mật khẩu không hợp lệ'], 400);
        }

        // Kiểm tra token trong DB
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Token không tồn tại hoặc đã hết hạn'], 400);
        }

        // Kiểm tra thời gian: 5 phút = 300 giây
        $created = Carbon::parse($record->created_at);
        if (Carbon::now()->diffInSeconds($created) > 300) {
            // Xóa token cũ
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return response()->json(['message' => 'Link đặt lại mật khẩu đã hết hạn (5 phút)'], 400);
        }

        // Cập nhật mật khẩu
        User::where('email', $email)->update([
            'password' => bcrypt($request->password),
            'remember_token' => null,
        ]);

        // Xóa token sau khi đổi mật khẩu
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return response()->json(['message' => 'Đặt mật khẩu thành công'], 200);
    }

    // --- Hàm xử lý chung để tạo Token giống hệt logic Login của bạn ---
    private function generateTokenAndUser($user, $rememberMe = false)
    {
        // 1. Tạo Token Sanctum
        $token = $user->createToken('auth-token')->plainTextToken;

        // 2. Logic Remember Me giống của bạn (dành cho User thường)
        if ($rememberMe && $user->role !== 'admin') {
            $user->forceFill([
                'remember_token' => bin2hex(random_bytes(60)),
            ])->save();
        }

        return [
            'message' => 'Đăng nhập thành công',
            'user' => $user,
            'token' => $token,
        ];
    }

    // Chuyển hướng người dùng sang Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Xử lý khi Google gọi lại (Callback)
    // --- CALLBACK GOOGLE ---
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            return $this->handleSocialCallback($googleUser, 'google');
        } catch (Exception $e) {
            dd($e->getMessage());
            // Redirect về trang login của Vue kèm thông báo lỗi trên URL
            return redirect('/login?error=' . urlencode('Đăng nhập Google thất bại'));
        }
    }

    // ==========================================
    // 2. FACEBOOK LOGIN
    // ==========================================

    // Chuyển hướng người dùng sang Facebook
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    // Xử lý khi Facebook gọi lại (Callback)
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->stateless()->user();
            return $this->handleSocialCallback($facebookUser, 'facebook');
        } catch (Exception $e) {
            dd($e->getMessage());
            return redirect('/login?error=' . urlencode('Đăng nhập Facebook thất bại'));
        }
    }

    // --- LOGIC XỬ LÝ CHUNG CHO SOCIAL ---
    private function handleSocialCallback($socialUser, $provider)
    {
        // 1. Tìm hoặc tạo user
        $user = $this->_registerOrLoginUser($socialUser, $provider);

        // 2. --- [LOGIC MỚI] Tính toán thời gian & Dọn rác ---
        if ($user->role === 'admin') {
            // Admin: 5 tiếng
            $expiresAt = now()->addHours(5);

            // Ké tính năng dọn dẹp rác (giống hàm Login)
            try {
                PersonalAccessToken::where('expires_at', '<', now())->delete();
            } catch (\Exception $e) { /* Ignore lỗi */
            }
        } else {
            // User thường: 1 tiếng
            $expiresAt = now()->addHour();
        }

        // 3. --- [LOGIC MỚI] Tạo Token trực tiếp tại đây ---
        // Bỏ qua hàm generateTokenAndUser để kiểm soát được expiresAt
        $token = $user->createToken('auth-token', ['*'], $expiresAt)->plainTextToken;

        // Cập nhật remember_token cho User thường (nếu cần)
        if ($user->role !== 'admin') {
            $user->forceFill([
                'remember_token' => bin2hex(random_bytes(60))
            ])->save();
        }

        // 4. Xây dựng URL để trả Token về cho Vue
        // Lưu ý: Thường các biến boolean gửi qua URL sẽ thành chuỗi "true"/"false"
        // hoặc "1"/"0". FE cần parse cẩn thận.

        // Nếu chạy local Vue dev:
        // $frontendUrl = 'http://localhost:5173/auth/callback'; 

        // Nếu chạy production (chung domain):
        $frontendUrl = '/login';

        $queryParams = http_build_query([
            'token'         => $token,
            'user_role'     => $user->role,
            'user_email'    => $user->email,
            'login_success' => 'true',
            'expires_at'    => $expiresAt->toIso8601String(),
        ]);

        return redirect($frontendUrl . '?' . $queryParams);
    }
    // ==========================================
    // 3. HÀM XỬ LÝ LOGIC CHUNG (PRIVATE)
    // ==========================================

    private function _registerOrLoginUser($socialUser, $provider)
    {
        // Xác định tên cột ID trong bảng users (google_id hoặc facebook_id)
        $providerIdField = $provider . '_id';

        // TRƯỜNG HỢP 1: User đã từng đăng nhập bằng Mạng xã hội này rồi
        $existingUser = User::where($providerIdField, $socialUser->getId())->first();

        if ($existingUser) {
            // Cập nhật lại avatar và tên mới nhất (nếu muốn)
            $existingUser->update([
                'avatar' => $socialUser->getAvatar(),
            ]);
            return $existingUser;
        }

        // TRƯỜNG HỢP 2: Chưa đăng nhập bằng MXH này, nhưng Email đã tồn tại trong hệ thống
        // (Ví dụ: Đã đăng ký bằng tay, giờ muốn login bằng Google cùng email đó)
        $existingUserByEmail = User::where('email', $socialUser->getEmail())->first();

        if ($existingUserByEmail) {
            // Cập nhật thêm ID của mạng xã hội vào user cũ
            $existingUserByEmail->update([
                $providerIdField => $socialUser->getId(),
                'email_verified_at' => now(), // Tin tưởng email từ Google/FB là đã xác thực
            ]);
            return $existingUserByEmail;
        }

        // TRƯỜNG HỢP 3: User hoàn toàn mới -> Tạo mới

        // Tạo username tự động (vì Model của bạn yêu cầu username)
        // Logic: Lấy phần trước @ của email + số ngẫu nhiên để tránh trùng
        $baseUsername = explode('@', $socialUser->getEmail())[0];
        $newUsername = $baseUsername . rand(1000, 9999);

        // Kiểm tra xem username đã tồn tại chưa, nếu có thì random lại (đơn giản hóa)
        while (User::where('username', $newUsername)->exists()) {
            $newUsername = $baseUsername . rand(1000, 9999);
        }

        // Tạo user mới
        $newUser = User::create([
            'email' => $socialUser->getEmail(),
            'full_name' => $socialUser->getName() ?? $newUsername, // Google/FB có thể trả về null name
            'username' => $newUsername,
            'password' => Hash::make(Str::random(16)), // Tạo mật khẩu ngẫu nhiên bảo mật
            $providerIdField => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'role' => 'user', // Mặc định role là khách hàng
            'email_verified_at' => now(),
            // Các trường khác như phone, address để null hoặc default
        ]);

        return $newUser;
    }

    public function getCaptcha()
    {
        // Tham số thứ 2 là true => chế độ API
        // 'default' là tên cấu hình trong file captcha.php (bạn có thể đổi thành 'flat', 'mini'...)
        $data = Captcha::create('default', true);

        return response()->json([
            'status' => true,
            'message' => 'Lấy captcha thành công',
            'data' => [
                'key' => $data['key'], // Key này cần gửi lại khi login
                'img' => $data['img']  // Ảnh này gắn vào src của thẻ img ở Vue
            ]
        ]);
    }
}
