<template>
<div class="admin-container">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div v-if="currentUser" class="mt-6 user-info">
      <img v-if="currentUser.avatar" :src="avatarPath(currentUser.avatar)" class="avatar"/>
      <div class="font-semibold">{{ currentUser.full_name || currentUser.username }}</div>
      <div class="text-sm text-gray-700">{{ currentUser.email }}</div>
    </div>

    <router-link to="/admin/quan-ly-nguoi-dung">Quản lý người dùng</router-link>
    <router-link to="/admin/quan-ly-san-pham">Quản lý sản phẩm</router-link>
    <router-link to="/admin/quan-ly-don-hang">Quản lý đơn hàng</router-link>
    <router-link to="/admin/quan-ly-khuyen-mai">Quản lý khuyến mãi</router-link>
    <router-link to="/admin/thong-ke-bao-cao">Thống kê báo cáo</router-link>

    <div class="logout-wrap">
      <button @click="logout" class="bg-red-600 text-white">Đăng xuất</button>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <router-view v-slot="{ Component }">
      <component :is="Component" v-if="Component"/>
      
      <!-- WELCOME / INFO TEXT -->
      <div v-else class="dashboard-welcome">
        <h1>Chào mừng đến với FLORENTIC Admin</h1>
        <p>Đây là trung tâm quản lý toàn bộ hệ thống bán hàng FLORENTIC. Trước khi bắt đầu, vui lòng lưu ý các thông tin sau:</p>
        
        <ul>
          <li>Chọn một mục bên trái để quản lý dữ liệu tương ứng.</li>
          <li class="warning">⚠ Không xóa dữ liệu quan trọng nếu không chắc chắn.</li>
          <li class="warning">⚠ Chỉ tạo admin mới nếu bạn có quyền Super Admin.</li>
          <li>Kiểm tra kỹ thông tin người dùng, sản phẩm và đơn hàng trước khi cập nhật.</li>
          <li>Đăng xuất sau khi hoàn thành công việc để bảo mật.</li>
        </ul>

        <p>Mọi thao tác đều được ghi lại và có thể xem lại trong báo cáo hoạt động.</p>
      </div>
    </router-view>
  </main>

</div>

<div class="header-wrapper">
    <div v-if="showSessionAlert" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-icon">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
        </div>
        <h3>Phiên đăng nhập hết hạn</h3>
        <p>Để bảo mật tài khoản, phiên làm việc của bạn đã kết thúc. Vui lòng đăng nhập lại.</p>
        <button @click="handleSessionExpiredConfirm" class="modal-btn">
          Đăng nhập lại ngay
        </button>
      </div>
    </div>
    </div>
</template>


<script setup>
import { ref, onMounted,onUnmounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const currentUser = ref(null)
const router = useRouter()
const showSessionAlert = ref(false)
let sessionCheckInterval = null



// [MỚI] Hàm kiểm tra thời gian hết hạn
const checkSessionExpiration = () => {
  const token = localStorage.getItem('token')
  const expiresAtString = localStorage.getItem('expires_at') // Backend phải trả về cái này lúc login

  // Nếu không có token hoặc chưa lưu thời gian hết hạn thì thôi
  if (!token || !expiresAtString) return

  const now = new Date()
  const expirationTime = new Date(expiresAtString)

  // So sánh: Nếu giờ hiện tại >= giờ hết hạn
  if (now >= expirationTime) {
    showSessionAlert.value = true
    // Dừng kiểm tra để đỡ tốn tài nguyên
    if (sessionCheckInterval) clearInterval(sessionCheckInterval)
  }
}

// [MỚI] Xử lý khi bấm nút "Đăng nhập lại"
const handleSessionExpiredConfirm = async () => {
  showSessionAlert.value = false
  await logout() // Tận dụng hàm logout có sẵn bên dưới
  router.push('/login')
}


const fetchCurrentUser = async () => {
	try {
		// First try using cookie (Sanctum) if available
		let res = null
		try {
			res = await axios.get('/me', { withCredentials: true })
		} catch (err) {
			// fallback to token from localStorage if provided
			const token = localStorage.getItem('token')
			if (token) {
				res = await axios.get('/me', { headers: { Authorization: `Bearer ${token}` } })
			} else {
				throw err
			}
		}
		currentUser.value = res.data
	} catch (err) {
		currentUser.value = null
	}
}

onMounted(() => {
	fetchCurrentUser()
  // [MỚI] Kích hoạt bộ đếm kiểm tra mỗi 1 phút (60000ms)
  checkSessionExpiration() // Kiểm tra ngay lập tức khi load trang
  sessionCheckInterval = setInterval(checkSessionExpiration, 60000)
	// listen for auth changes (login/logout) from other components
	const onAuthChanged = () => fetchCurrentUser()
	window.addEventListener('auth-changed', onAuthChanged)
	// store listener so we can remove it later
	window.__onAuthChanged = onAuthChanged
})
onBeforeUnmount(() => {
	const handler = window.__onAuthChanged
	if (handler) window.removeEventListener('auth-changed', handler)
})

onUnmounted(() => {
  if (sessionCheckInterval) clearInterval(sessionCheckInterval);
});

const logout = async () => {
  try {
    const token = localStorage.getItem('token')
    if (token) {
      // Gửi token qua header Bearer
      await axios.post(
        '/logout',
        {},
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      )
    }
  } catch (e) {
    // Ignore lỗi nếu logout trên server thất bại
    console.error('Logout error:', e)
  }

  // Xóa token local
  localStorage.removeItem('token')
  // Xóa remembered email nếu muốn (user bình thường)
  localStorage.removeItem('rememberedEmail')
  localStorage.removeItem('expires_at')

  // Xóa header Authorization mặc định
  if (axios.defaults.headers.common['Authorization']) {
    delete axios.defaults.headers.common['Authorization']
  }

  // Thông báo các component khác user đã logout
  window.dispatchEvent(new Event('auth-changed'))

  // Chuyển về trang chủ
  router.push('/')
}


const backendOrigin = import.meta.env.VITE_BACKEND_URL || window.location.origin;

const avatarPath = (path) => {
	if (!path) return null
	const p = String(path)
	if (p.startsWith('http')) return p
	if (p.startsWith('/')) return backendOrigin + p
	if (p.startsWith('public/uploads/')) return backendOrigin + '/' + p.replace(/^public\//, '')
	if (p.startsWith('uploads/')) return backendOrigin + '/' + p
	if (p.startsWith('storage/')) return backendOrigin + '/' + p
	return backendOrigin + '/storage/' + p
}
</script>


<style scoped>
/* [MỚI] CSS CHO MODAL SESSION */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6); /* Nền tối mờ */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999; /* Đảm bảo nằm trên cùng */
  animation: fadeIn 0.3s ease;
}

.modal-content {
  background: white;
  padding: 30px;
  border-radius: 12px;
  text-align: center;
  max-width: 400px;
  width: 90%;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
  animation: slideUp 0.3s ease;
}

.modal-icon {
  margin-bottom: 15px;
}

.modal-content h3 {
  margin: 0 0 10px;
  color: #1f2937;
  font-size: 1.25rem;
  font-weight: 600;
}

.modal-content p {
  margin-bottom: 25px;
  color: #6b7280;
  font-size: 0.95rem;
  line-height: 1.5;
}

.modal-btn {
  background-color: #000; /* Màu đen chủ đạo của web bạn */
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  width: 100%;
}

.modal-btn:hover {
  background-color: #333;
  transform: translateY(-1px);
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.admin-container {
    display: flex;
    min-height: 100vh;
    background: #f8f9fa; /* trắng xám nhẹ */
    color: #111; /* đen hơi nhạt */
    font-family: "Inter", sans-serif;
}

/* ===== SIDEBAR ===== */
.sidebar {
    width: 260px;
    background: #fff;
    border-right: 1px solid #e5e7eb;
    padding: 25px 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    position: sticky;
    top: 0;
    height: 100vh;
}

.user-info {
    text-align: center;
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.user-info img {
    margin: 0 auto;
    border: 2px solid #ddd;
}

.user-info .font-semibold {
    margin-top: 8px;
    font-size: 16px;
    font-weight: 600;
}

.user-info .text-sm {
    color: #6b7280;
    margin-top: 2px;
}

/* ===== LINKS ===== */
.sidebar a {
    display: block;
    padding: 12px 16px;
    border-radius: 8px;
    color: #111;
    font-size: 15px;
    font-weight: 500;
    text-decoration: none;
    transition: 0.25s;
}

.sidebar a:hover {
    background: #f3f4f6; /* xám nhạt hover */
}

.sidebar a.router-link-active {
    background: #111;
    color: #fff;
}

/* ===== LOGOUT BUTTON ===== */
.logout-wrap {
    margin-top: auto;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.logout-wrap button {
    width: 100%;
    border-radius: 8px;
    padding: 10px;
    font-size: 15px;
    transition: 0.25s;
}

.logout-wrap button:hover {
    opacity: 0.8;
}

/* ===== MAIN CONTENT ===== */
.main-content {
    flex: 1;
    padding: 28px 34px;
}

</style>