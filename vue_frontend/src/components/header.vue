<template>
  <div class="header-wrapper">
    <div class="top-bar">
      <span>MIỄN PHÍ VẬN CHUYỂN CHO ĐƠN HÀNG TỪ 500K</span>
    </div>

    <header class="site-header">
      <div class="header-container">
        
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" @click="toggleMobileMenu">
          <svg v-if="!mobileMenuOpen" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
          <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>

        <router-link to="/" class="logo">
          FLORENTIC
        </router-link>

        <nav class="nav" :class="{ 'mobile-open': mobileMenuOpen }">
          <router-link to="/" class="nav-link">TRANG CHỦ</router-link>
          
          <div class="nav-dropdown mega-parent">
            <router-link to="/products" class="nav-link">SẢN PHẨM</router-link>
            
            <div class="mega-menu">
              <div class="mega-content">
                
                <div class="mega-column">
                  <h4 class="column-title"><router-link :to="{ name: 'product-category', params: { slug: 'ao' } }">Áo</router-link></h4>
                  <ul class="column-list">
                    <li><router-link :to="{ name: 'product-category', params: { slug: 't-shirts' } }">Áo Thun (T-Shirts)</router-link></li>
                    <li><router-link :to="{ name: 'product-category', params: { slug: 'shirts' } }">Áo Sơ Mi (Shirts)</router-link></li>
                    <li><router-link :to="{ name: 'product-category', params: { slug: 'polo' } }">Áo Polo</router-link></li>
                    <li><router-link :to="{ name: 'product-category', params: { slug: 'jackets' } }">Áo Khoác / Blazer</router-link></li>
                    <li><router-link :to="{ name: 'product-category', params: { slug: 'hoodies' } }">Hoodie & Sweatshirt</router-link></li>
                  </ul>
                </div>

                <div class="mega-column">
                  <h4 class="column-title"><router-link :to="{ name: 'product-category', params: { slug: 'quan' } }">QUẦN</router-link></h4>
                  <ul class="column-list">
                    <li><router-link :to="{ name: 'product-category', params: { slug: 'trousers' } }">Quần Tây (Trousers)</router-link></li>
                    <li><router-link :to="{ name: 'product-category', params: { slug: 'jeans' } }">Denim / Jeans</router-link></li>
                    <li><router-link :to="{ name: 'product-category', params: { slug: 'shorts' } }">Quần Short</router-link></li>
                    <li><router-link :to="{ name: 'product-category', params: { slug: 'kaki' } }">Quần Kaki</router-link></li>
                    <li><router-link :to="{ name: 'product-category', params: { slug: 'joggers' } }">Quần Jogger</router-link></li>
                  </ul>
                </div>

                <div class="mega-column">
                  <h4 class="column-title"><router-link :to="{ name: 'product-category', params: { slug: 'phu-kien' } }">PHỤ KIỆN</router-link></h4>
                  <ul class="column-list">
                    <li><router-link :to="{ name: 'product-category', params: { slug: 'bags' } }">Túi Xách / Balo</router-link></li>
                    <li><router-link :to="{ name: 'product-category', params: { slug: 'hats' } }">Mũ / Nón</router-link></li>
                    <li><router-link :to="{ name: 'product-category', params: { slug: 'belts' } }">Thắt Lưng</router-link></li>
                    <li><router-link :to="{ name: 'product-category', params: { slug: 'wallets' } }">Ví Da</router-link></li>
                    <li><router-link :to="{ name: 'product-category', params: { slug: 'socks' } }">Tất / Vớ</router-link></li>
                  </ul>
                </div>

                <div class="mega-column highlight-column">
                  <h4 class="column-title"><router-link :to="{ name: 'product-category', params: { slug: 'bo-suu-tap' } }">BỘ SƯU TẬP</router-link></h4>
                  <ul class="column-list">
                    <li><router-link :to="{ name: 'product-category', params: { slug: 'new-arrivals' } }">New Arrivals 2025</router-link></li>
                    <li><router-link :to="{ name: 'product-category', params: { slug: 'essentials' } }">The Essentials</router-link></li>
                    <li><router-link :to="{ name: 'product-category', params: { slug: 'monochrome' } }">Monochrome Series</router-link></li>
                    <li class="mt-4"><router-link :to="{ name: 'product-category', params: { slug: 'sale' } }" class="text-sale">VIEW ALL SALE</router-link></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <router-link to="/about" class="nav-link">GIỚI THIỆU</router-link>
          <router-link to="/contact" class="nav-link">LIÊN HỆ</router-link>
        </nav>

        <div class="header-actions">
          <!-- Search Box -->
          <div class="search-wrapper">
            <button class="icon-btn search-toggle" @click="toggleSearch">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
              </svg>
            </button>
            
            <div class="search-dropdown" :class="{ active: searchOpen }">
              <input 
                type="text" 
                v-model="searchQuery"
                placeholder="Tìm kiếm sản phẩm..."
                class="search-input"
                @keyup.enter="performSearch"
              />
              <button class="search-btn" @click="performSearch">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="11" cy="11" r="8"></circle>
                  <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
              </button>
            </div>
          </div>

          <!-- User Account -->
          <div class="account-wrapper">
            <div class="icon-btn account-btn">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <!-- Tên user khi đã login -->
              <div v-if="user" class="username">{{ user.full_name }}</div>
            </div>
            <div class="account-dropdown">
              <!-- Khi chưa đăng nhập -->
              <template v-if="!user">
                <router-link to="/login" class="dropdown-item">Đăng nhập</router-link>
                <router-link to="/register" class="dropdown-item">Đăng ký</router-link>
              </template>
              <!-- Khi đã đăng nhập -->
              <template v-else>
                <router-link to="/user/profile" class="dropdown-item">Tài khoản</router-link>
                <router-link to="/user/orders" class="dropdown-item">Đơn hàng</router-link>
                <div class="divider"></div>
                <button @click="logout" class="dropdown-item logout-btn">Đăng xuất</button>
              </template>
            </div>
          </div>

          <!-- Shopping Cart -->
          <router-link to="/user/cart" class="icon-btn cart-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <span class="cart-count" v-if="cartCount > 0">{{ cartCount }}</span>
          </router-link>
        </div>
      </div>
    </header>
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
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const email = ref('')
const rememberMe = ref(false)
const searchOpen = ref(false)
const searchQuery = ref('')
const mobileMenuOpen = ref(false)
const showSessionAlert = ref(false)
let sessionCheckInterval = null

// 👉 STATE USER
const user = ref(null)

const cartCount = ref(0);


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

// Hàm gọi API lấy số lượng
const fetchCartCount = async () => {
  try {
    const token = localStorage.getItem('token');
    const sessionId = sessionStorage.getItem('cart_session_id');

    // Nếu không có gì thì reset về 0
    if (!token && !sessionId) {
      cartCount.value = 0;
      return;
    }

    const config = {
      params: { session_id: sessionId },
      headers: token ? { 'Authorization': `Bearer ${token}` } : {}
    };

    const response = await axios.get(`/cart`, config);
    
    if (response.data && response.data.summary) {
      cartCount.value = response.data.summary.total_items;
    }
  } catch (error) {
    console.error("Lỗi lấy giỏ hàng:", error);
  }
};

// Lifecycle
onMounted(() => {
  // 1. Gọi ngay khi Header hiện ra
  fetchCartCount();

  // 2. Đăng ký lắng nghe sự kiện 'cart-updated' từ bất kỳ đâu phát ra
  window.addEventListener('cart-updated', fetchCartCount);
  // [MỚI] Kích hoạt bộ đếm kiểm tra mỗi 1 phút (60000ms)
  checkSessionExpiration() // Kiểm tra ngay lập tức khi load trang
  sessionCheckInterval = setInterval(checkSessionExpiration, 60000)
});

onUnmounted(() => {
  if (sessionCheckInterval) clearInterval(sessionCheckInterval);
  // Dọn dẹp sự kiện khi Header bị hủy (tránh lỗi memory leak)
  window.removeEventListener('cart-updated', fetchCartCount);
});

// --- FETCH USER INFO /me ---
async function fetchUser() {
  const token = localStorage.getItem('token')
  if (!token) return

  try {
    const res = await axios.get('/me', {
      headers: { Authorization: `Bearer ${token}` }
    })
    const currentUser = res.data.user || res.data // tùy backend trả về

    // Nếu admin → redirect thẳng admin
    if (currentUser.role === 'admin') {
      window.location.href = '/admin'
      return
    }

    // User thường → gán vào reactive
    user.value = {
      id: currentUser.id,
      email: currentUser.email,
      username: currentUser.username,
      full_name: currentUser.full_name,
      avatar: currentUser.avatar,
      role: currentUser.role,
    }

  } catch (err) {
    console.log('Không thể lấy thông tin user:', err)
    localStorage.removeItem('token')
    user.value = null
  }
}

// --- ON MOUNTED ---
onMounted(() => {
  // Nếu rememberedEmail tồn tại và không phải admin, auto-fill
  const remembered = localStorage.getItem('rememberedEmail')
  if (remembered) {
    email.value = remembered
    rememberMe.value = true
  }

  fetchUser()
})

// --- LOGOUT ---
 async function logout() {
  try {
     await axios.post('/logout', {}, { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } })
  } catch (e) { /* ignore */ }

  // Clear localStorage và reactive user
  localStorage.removeItem('token')
  localStorage.removeItem('rememberedEmail')
  localStorage.removeItem('expires_at') // [MỚI] Xóa luôn cái này
  user.value = null

  router.push('/')
}

// ------ SEARCH FUNCTIONS ------
const toggleSearch = () => { searchOpen.value = !searchOpen.value }
const toggleMobileMenu = () => { mobileMenuOpen.value = !mobileMenuOpen.value }
const performSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/search', query: { keyword: searchQuery.value } })
    searchOpen.value = false
    searchQuery.value = ''
    mobileMenuOpen.value = false
  }
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
.header-wrapper {
  font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
}

/* ----- Top Bar ----- */
.top-bar {
  background-color: #000;
  color: #fff;
  font-size: 13px; /* Giảm nhẹ size trên mobile cho đỡ chật */
  text-transform: uppercase;
  letter-spacing: 1px;
  text-align: center;
  padding: 8px 10px;
  font-weight: 600;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1001;
  height: 32px; /* Set cứng chiều cao để tính toán header */
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ----- Header - FIXED ----- */
.site-header {
  background-color: #fff;
  border-bottom: 1px solid #000;
  position: fixed;
  top: 32px; /* Bằng chiều cao top-bar */
  left: 0;
  right: 0;
  z-index: 1000;
  height: 70px; /* Giảm nhẹ chiều cao header cho gọn */
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: top 0.3s;
}

.header-container {
  max-width: 1440px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 100%;
}

/* ----- Logo ----- */
.logo {
  font-weight: 800;
  font-size: 20px; /* Resize cho phù hợp */
  color: #000;
  text-decoration: none;
  letter-spacing: 2px;
  text-transform: uppercase;
  border: 2px solid #000;
  padding: 5px 10px;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.logo:hover {
  background-color: #000;
  color: #fff;
}

/* ----- Navigation Menu (Desktop Base) ----- */
.nav {
  display: flex;
  align-items: center;
  gap: 30px;
}

.nav-link {
  font-size: 13px;
  font-weight: 600;
  color: #000;
  text-decoration: none;
  text-transform: uppercase;
  letter-spacing: 1px;
  position: relative;
  padding: 24px 0; /* Padding khớp với height header mới */
  cursor: pointer;
  transition: color 0.3s ease;
  display: flex;
  align-items: center;
}

.nav-link::after {
  content: '';
  position: absolute;
  width: 0;
  height: 2px;
  bottom: 15px;
  left: 0;
  background-color: #000;
  transition: width 0.3s ease;
}

.nav-link:hover::after {
  width: 100%;
}

/* ----- Mega Menu ----- */
.mega-parent {
  position: static; /* Quan trọng để mega menu full width */
}

.mega-menu {
  opacity: 0;
  visibility: hidden;
  position: absolute;
  top: 100%; /* Ngay dưới header */
  left: 0;
  width: 100%;
  background-color: #fff;
  border-bottom: 1px solid #000;
  z-index: 99;
  transition: all 0.3s ease;
  transform: translateY(10px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.mega-parent:hover .mega-menu {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.mega-content {
  max-width: 1440px;
  margin: 0 auto;
  padding: 40px;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 30px;
}

.column-title {
  font-size: 14px;
  font-weight: 800;
  color: #000;
  margin-bottom: 20px;
  text-transform: uppercase;
  letter-spacing: 1px;
  border-left: 3px solid #000;
  padding-left: 10px;
}

.column-title a {
  text-decoration: none;
  color: inherit;
  display: block;
}

.column-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.column-list li {
  margin-bottom: 10px;
}

.column-list a {
  text-decoration: none;
  color: #666;
  font-size: 13px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: all 0.2s;
  display: block;
  padding: 2px 0;
}

.column-list a:hover {
  color: #000;
  padding-left: 5px;
}

.text-sale {
  color: #d00 !important;
  font-weight: 800 !important;
  border-bottom: 1px solid #d00;
  display: inline-block !important;
}

/* ----- Header Actions ----- */
.header-actions {
  display: flex;
  align-items: center;
  gap: 15px;
}

.icon-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 5px;
  color: #000;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s;
}

.icon-btn:hover {
  transform: scale(1.1);
}

.cart-btn {
  position: relative;
}

.cart-count {
  position: absolute;
  top: -2px;
  right: -2px;
  background-color: #d00;
  color: #fff;
  font-size: 9px;
  font-weight: 700;
  width: 16px;
  height: 16px;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 50%;
}

/* Account Dropdown & Search Dropdown giữ nguyên logic hiển thị */
.account-wrapper, .search-wrapper { position: relative; }

.account-dropdown, .search-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 10px;
  background: #fff;
  border: 1px solid #eee;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.3s ease;
  z-index: 1002;
}

.account-wrapper:hover .account-dropdown,
.search-dropdown.active {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.account-dropdown { min-width: 180px; }
.search-dropdown { display: flex; padding: 10px; min-width: 250px; }
.search-input { flex: 1; padding: 8px; background: #f5f5f5; border: none; outline: none; }
.search-btn { background: #000; color: #fff; border: none; padding: 8px 12px; cursor: pointer; }

.account-dropdown .dropdown-item, .account-dropdown .logout-btn {
  display: block;
  width: 100%;
  padding: 12px 15px;
  text-decoration: none;
  color: #333;
  font-size: 13px;
  text-align: left;
  border: none;
  background: none;
  cursor: pointer;
}
.account-dropdown .dropdown-item:hover, .account-dropdown .logout-btn:hover {
  background: #f5f5f5;
  color: #000;
}
.divider { height: 1px; background: #eee; margin: 0; }

/* =============================================
   RESPONSIVE MOBILE & TABLET (Max-width: 1024px)
   ============================================= */
@media (max-width: 1024px) {
  /* Layout Header: Chia 3 phần đều nhau để Logo luôn ở giữa */
  .mobile-menu-btn {
    display: flex; /* Hiện nút hamburger */
    flex: 1;       /* Chiếm 1 phần bên trái */
    justify-content: flex-start;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    color: #000;
  }

  .logo {
    flex: 0 0 auto; /* Logo tự co giãn theo nội dung */
    font-size: 18px;
    padding: 4px 8px;
    border-width: 2px;
  }

  .header-actions {
    flex: 1;       /* Chiếm 1 phần bên phải */
    justify-content: flex-end; /* Đẩy icon sang phải */
    gap: 12px;
  }

  /* Navigation Drawer (Menu trượt) */
  .nav {
    position: fixed;
    top: 102px; /* = Height TopBar (32px) + Header (70px) */
    left: 0;
    bottom: 0; /* Kéo dài xuống hết màn hình */
    width: 100%; /* Full width */
    background: #fff;
    flex-direction: column;
    align-items: flex-start;
    padding: 20px 20px 80px 20px; /* Padding bottom lớn để tránh bị che */
    gap: 0;
    
    /* Animation Trượt */
    transform: translateX(-100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    
    /* Scroll */
    overflow-y: auto;
    border-top: 1px solid #eee;
  }

  .nav.mobile-open {
    transform: translateX(0); /* Trượt ra */
  }

  /* Nav Links Mobile */
  .nav-link {
    width: 100%;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
    justify-content: space-between; /* Để nếu có icon mũi tên sẽ đẹp */
    height: auto;
  }
  
  .nav-link::after { display: none; }

  /* Mega Menu Mobile Layout */
  .nav-dropdown.mega-parent {
    width: 100%;
    display: flex;
    flex-direction: column;
  }

  .mega-menu {
    position: static; /* Không absolute nữa */
    opacity: 1;
    visibility: visible;
    transform: none;
    width: 100%;
    box-shadow: none;
    border: none;
    background: #fafafa; /* Nền xám nhẹ để phân biệt */
    padding: 0;
    
    /* Mặc định hiển thị luôn danh sách con để dễ bấm trên mobile 
       (Hoặc bạn có thể dùng JS toggle class để ẩn hiện) */
    display: block; 
  }

  .mega-content {
    display: flex;
    flex-direction: column; /* Xếp dọc các cột */
    padding: 15px;
    gap: 25px;
  }

  .mega-column {
    border-bottom: 1px dashed #ddd;
    padding-bottom: 15px;
  }
  .mega-column:last-child {
    border-bottom: none;
  }

  .column-title {
    margin-bottom: 10px;
    font-size: 13px;
    border-left: 3px solid #666;
  }

  .column-list a {
    padding: 6px 0;
    font-size: 13px;
    color: #555;
  }

  /* Search Dropdown Mobile */
  .search-dropdown {
    position: fixed;
    top: 102px;
    left: 0;
    right: 0;
    width: 100%;
    border: none;
    border-bottom: 1px solid #eee;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
  }
}

@media (min-width: 1025px) {
  .mobile-menu-btn {
    display: none;
  }
}
</style>