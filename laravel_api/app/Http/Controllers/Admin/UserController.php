<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    // 1. Danh sách users với search, filter, pagination
    public function index(Request $request)
    {
        $query = User::query();

        // Exclude currently authenticated admin/user from the listing
        if (auth()->check()) {
            $query->where('id', '!=', auth()->id());
        }

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('full_name', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }

        // Filter by role
        if ($request->role) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Only select the fields we need for the admin listing
        // Pagination 10 per page
        $users = $query->select('id', 'avatar', 'username', 'email', 'full_name', 'role', 'created_at')
                   ->orderBy('id','desc')
                   ->paginate(10);

        // normalize avatar paths for returned JSON
        $users->getCollection()->transform(function($u) {
            $u->avatar = $this->formatAvatar($u->avatar);
            return $u;
        });

        return response()->json($users);
    }

    // 2. Xem chi tiết user
    public function show($id)
    {
        $user = User::with('orders', 'reviews')->findOrFail($id);

        // Decrypt phone and address if they are encrypted
        $decryptedPhone = $user->phone;
        $decryptedAddress = $user->address;
        try {
            if ($user->phone) {
                $decryptedPhone = Crypt::decryptString($user->phone);
            }
        } catch (\Exception $e) {
            // leave as-is if decryption fails
        }

        try {
            if ($user->address) {
                $decryptedAddress = Crypt::decryptString($user->address);
            }
        } catch (\Exception $e) {
            // leave as-is if decryption fails
        }

        $user->phone = $decryptedPhone;
        $user->address = $decryptedAddress;

        // normalize avatar path
        $user->avatar = $this->formatAvatar($user->avatar);

        $stats = [
            'total_orders' => $user->orders->count(),
            'total_order_value' => $user->orders->sum('total_amount'),
            'total_reviews' => $user->reviews->count(),
        ];

        return response()->json([
            'user' => $user,
            'stats' => $stats
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

    // 3. Cập nhật user
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Only super-admin can update other admin accounts
        $current = $request->user();
        if ($user->role === 'admin' && $current && $current->email !== 'admin@example.com') {
            return response()->json(['message' => 'Bạn không có quyền sửa tài khoản admin khác'], 403);
        }

        $request->validate([
            'email' => ['required','email', Rule::unique('users')->ignore($user->id)],
            'full_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:2048', // max 2MB
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $dest = public_path('uploads/avatar');
            if (! File::exists($dest)) {
                File::makeDirectory($dest, 0755, true);
            }

            // Compute hash of uploaded file and check existing files for duplicate by content
            $uploadedHash = sha1_file($file->getRealPath());
            $found = null;
            foreach (File::files($dest) as $existingFile) {
                try {
                    if (sha1_file($existingFile->getRealPath()) === $uploadedHash) {
                        $found = $existingFile->getFilename();
                        break;
                    }
                } catch (\Exception $e) {
                    // ignore unreadable files
                }
            }

            if ($found) {
                // If current avatar is different from the found duplicate, remove current
                if ($user->avatar && $user->avatar !== 'uploads/avatar/' . $found) {
                    $existing = public_path($user->avatar);
                    if (File::exists($existing)) {
                        File::delete($existing);
                    }
                }
                // Reuse existing file
                $user->avatar = 'uploads/avatar/' . $found;
            } else {
                // No duplicate found — remove current avatar and save new file
                if ($user->avatar) {
                    $existing = public_path($user->avatar);
                    if (File::exists($existing)) {
                        File::delete($existing);
                    }
                }
                $filename = time().'_'.preg_replace('/[^A-Za-z0-9_.-]/', '_', $file->getClientOriginalName());
                $file->move($dest, $filename);
                $user->avatar = 'uploads/avatar/' . $filename;
            }
        }

        $user->email = $request->email;
        $user->full_name = $request->full_name;
        $user->phone = $request->phone;
        $user->address = $request->address;

        $user->save();

        return response()->json(['message' => 'Cập nhật user thành công', 'user' => $user]);
    }

    // 3b. Tạo user (dùng để tạo admin từ trang quản lý)
    public function store(Request $request)
    {
        $request->validate([
            'username' => ['required','string','max:50', Rule::unique('users','username')],
            'email' => ['required','email', Rule::unique('users','email')],
            'password' => ['required','string','min:6'],
            'full_name' => 'required|string|max:100',
            'role' => 'nullable|in:admin,user',
            'phone' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:2048',
        ]);

         // Xác thực mật khẩu admin hiện tại
        if (! Hash::check($request->current_admin_password, $request->user()->password)) {
            return response()->json(['message' => 'Mật khẩu admin hiện tại không đúng'], 403);
        }

        $user = new User();
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->full_name = $request->full_name;
        // encrypt phone and address when provided
        $user->phone = $request->phone ? Crypt::encryptString($request->phone) : null;
        $user->address = $request->address ? Crypt::encryptString($request->address) : null;
        // default role is admin unless explicitly provided
        $user->role = $request->role ?? 'admin';

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $dest = public_path('uploads/avatar');
            if (! File::exists($dest)) {
                File::makeDirectory($dest, 0755, true);
            }

            // Avoid uploading duplicate images by comparing file content hash
            $uploadedHash = sha1_file($file->getRealPath());
            $found = null;
            foreach (File::files($dest) as $existingFile) {
                try {
                    if (sha1_file($existingFile->getRealPath()) === $uploadedHash) {
                        $found = $existingFile->getFilename();
                        break;
                    }
                } catch (\Exception $e) {
                    // ignore unreadable files
                }
            }

            if ($found) {
                // reuse existing file
                $user->avatar = 'uploads/avatar/' . $found;
            } else {
                $filename = time().'_'.preg_replace('/[^A-Za-z0-9_.-]/', '_', $file->getClientOriginalName());
                $file->move($dest, $filename);
                $user->avatar = 'uploads/avatar/' . $filename;
            }
        }

        $user->save();


        // Gửi email kèm mật khẩu tạm
        $msg = "Chào $user->full_name,\n\nTài khoản của bạn đã được tạo.\nUsername: $user->username\nPassword tạm: {$request->password}\n\nVui lòng đăng nhập và đổi mật khẩu ngay lần đầu tiên.";
        Mail::raw($msg, function($m) use ($user) {
            $m->to($user->email)
            ->subject('Tài khoản mới đã được tạo');
        });

        return response()->json(['message' => 'Tạo user thành công, email đã gửi và yêu cầu đổi mật khẩu lần đầu', 'user' => $user], 201);

    }

    // 4. Thay đổi trạng thái (Active/Inactive/Banned)
    public function changeStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'status' => 'required|in:active,inactive,banned'
        ]);

        $user->status = $request->status;
        $user->save();

        return response()->json(['message' => 'Cập nhật trạng thái thành công', 'status' => $user->status]);
    }

    // 5. Xem đơn hàng user
    public function orders($id)
    {
        $user = User::findOrFail($id);
        $orders = $user->orders()->orderBy('created_at','desc')->get();

        return response()->json($orders);
    }

    // 6. Xóa user
    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Protect the main super-admin account from deletion
        if ($user->email === 'admin@example.com') {
            return response()->json(['message' => 'Không thể xóa tài khoản super-admin'], 403);
        }

        // Prevent deleting currently authenticated user
        if (auth()->check() && $user->id === auth()->id()) {
            return response()->json(['message' => 'Không thể xóa chính bạn'], 403);
        }

        // Only super-admin can delete admin accounts
        $current = $request->user();
        if ($user->role === 'admin' && $current && $current->email !== 'admin@example.com') {
            return response()->json(['message' => 'Bạn không có quyền xóa tài khoản admin khác'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Xóa user thành công']);
    }

    // GET /admin/users/deleted
    public function deletedUsers()
    {
        $deleted = User::onlyTrashed()->get(); // chỉ lấy các tài khoản đã xóa
        return response()->json($deleted);
    }

    // PATCH /admin/users/{id}/restore
    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);

        $user->restore(); // khôi phục
        return response()->json(['message' => 'Khôi phục user thành công', 'user' => $user]);
    }
}
