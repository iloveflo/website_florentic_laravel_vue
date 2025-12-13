<template>
  <div class="shopee-container">
    <div class="main-content">
      
      <div class="tabs-header">
        <div 
          v-for="tab in tabs" 
          :key="tab.value"
          @click="changeTab(tab.value)"
          :class="['tab-item', currentStatus === tab.value ? 'active' : '']"
        >
          {{ tab.label }}
        </div>
      </div>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div> Đang tải đơn hàng...
      </div>

      <div v-else class="order-list">
        
        <div v-if="orders.length === 0" class="empty-state">
          <div v-if="isLoggedIn || hasSessionParam">
             <img src="https://deo.shopeemobile.com/shopee/shopee-pcmall-live-sg/orderlist/5fafbb923393b712b96488590b8f781f.png" alt="Empty">
             <p>Chưa có đơn hàng nào</p>
             <button class="btn-solid" @click="$router.push('/')">Mua sắm ngay</button>
          </div>

          <div v-else>
             <img src="https://cdni.iconscout.com/illustration/premium/thumb/login-3305943-2757111.png" alt="Login" style="width: 150px">
             <p>Vui lòng đăng nhập để xem lịch sử đơn hàng của bạn</p>
             <button class="btn-solid" @click="$router.push('/login')">Đăng Nhập Ngay</button>
          </div>
        </div>

        <div v-for="order in orders" :key="order.id" class="order-card">
          
          <div class="card-header">
            <div class="shop-info">
              <span class="favorite-badge">Yêu thích</span>
              <span class="shop-name">Mã đơn: {{ order.order_code }}</span>
              <button class="btn-view-shop">Xem Shop</button>
            </div>
            <div class="order-status">
              {{ getStatusLabel(order.order_status) }}
            </div>
          </div>

          <div class="card-body">
            <div v-for="item in order.order_items" :key="item.id" class="product-item">
              <div class="img-wrapper">
                <img :src="getImageUrl(item.product_image)" alt="Product Image">
              </div>
              <div class="product-info">
                <h3 class="product-name">{{ item.product_name }}</h3>
                <div class="product-variant">Phân loại: {{ item.color }}, {{ item.size }}</div>
                <div class="product-qty">x{{ item.quantity }}</div>
              </div>
              <div class="product-price">
                <span class="old-price">{{ formatCurrency(item.price * 1.2) }}</span>
                <span class="current-price">{{ formatCurrency(item.price) }}</span>
              </div>
            </div>
          </div>

          <div class="card-footer">
            <div class="total-section">
              <span class="total-label">Thành tiền:</span>
              <span class="total-price">{{ formatCurrency(order.total_amount) }}</span>
            </div>

            <div class="action-buttons">
              <div class="btn-group">
                <span class="note-text" v-if="order.order_status === 'cancelled'">Đã hủy</span>
                
                <button v-if="order.order_status === 'completed'"class="btn-solid"@click="handleReview(order.order_code)">Đánh Giá</button>
                <button v-if="order.order_status === 'completed' || order.order_status === 'cancelled'"class="btn-solid"@click="openBuyAgainModal(order)">Mua Lại</button>
                <button class="btn-outline" @click="viewDetail(order.order_code)">Xem Chi Tiết</button>
              </div>
            </div>
          </div>

        </div>
      </div>
      
      </div>
  </div>

  <div v-if="showConfirmModal" class="modal-overlay">
      <div class="modal-content">
        <h3 class="modal-title">Xác Nhận Mua Lại</h3>
        <p class="modal-desc">
          Bạn có chắc muốn thêm tất cả sản phẩm từ đơn <b>{{ selectedOrder?.order_code }}</b> vào giỏ hàng không?
        </p>
        <div class="modal-actions">
          <button @click="closeModals" class="btn-cancel">Hủy Bỏ</button>
          <button @click="executeBuyAgain" class="btn-confirm">Đồng Ý</button>
        </div>
      </div>
    </div>

    <div v-if="showResultModal" class="modal-overlay">
      <div class="modal-content">
        <h3 class="modal-title">{{ resultTitle }}</h3>
        <p class="modal-desc">{{ resultMessage }}</p>
        <div class="modal-actions">
          <button @click="closeResultModal" class="btn-confirm full-width">Đã Hiểu</button>
        </div>
      </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      orders: [],
      loading: false,
      currentStatus: 'all',
      tabs: [
        { label: 'Tất cả', value: 'all' },
        { label: 'Chờ xác nhận', value: 'pending' },
        { label: 'Đang vận chuyển', value: 'shipping' },
        { label: 'Hoàn thành', value: 'completed' },
        { label: 'Đã hủy', value: 'cancelled' },
        
      ],
      showConfirmModal: false,
      showResultModal: false,
      selectedOrder: null,   // Lưu đơn hàng đang chọn mua lại
      resultTitle: '',       // Tiêu đề thông báo (Thành công/Thất bại)
      resultMessage: '',     // Nội dung thông báo
      isSuccess: false,      // Để biết nếu thành công thì redirect

      isLoggedIn: false,     // Biến theo dõi trạng thái đăng nhập
      hasSessionParam: false // Biến theo dõi xem có đang xem theo Session không
    };
  },
  mounted() {
    // Kiểm tra trạng thái ngay khi load trang
    this.checkAuthStatus();
    this.fetchOrders();
  },
  methods: {
    checkAuthStatus() {
        // Kiểm tra Token
        this.isLoggedIn = !!localStorage.getItem('token');
        
        // Kiểm tra Session Params (URL hoặc Local)
        const urlSession = this.$route.query.session_id;
        const localSession = localStorage.getItem('cart_session_id');
        this.hasSessionParam = !!(urlSession || localSession);
    },
    async fetchOrders() {
      this.loading = true;
      try {
        // 1. Lấy Token từ LocalStorage
        // ⚠️ QUAN TRỌNG: Kiểm tra xem lúc Login bạn lưu key tên là 'token' hay 'access_token'?
        // Mở F12 -> Application -> Local Storage để xem chính xác.
        const token = localStorage.getItem('token'); 
        // Cập nhật lại trạng thái (đề phòng thay đổi)
        this.isLoggedIn = !!token;
        
        // 2. Lấy session_id (Ưu tiên URL, sau đó đến LocalStorage)
        const urlSessionId = this.$route.query.session_id;
        const localSessionId = localStorage.getItem('cart_session_id');
        this.hasSessionParam = !!(urlSessionId || localSessionId);
        // 3. Chuẩn bị params
        const params = { 
          status: this.currentStatus,
          page: 1
        };

        // --- LOGIC XỬ LÝ ID ---
        if (urlSessionId) {
            // Trường hợp 1: Có session trên URL (Link tracking) -> Ưu tiên số 1
            params.session_id = urlSessionId;
        } 
        else if (!token && localSessionId) {
            // Trường hợp 2: Không đăng nhập + Có session local -> Gửi session local
            params.session_id = localSessionId;
        }
        // Trường hợp 3: Đã đăng nhập (Có token) -> Không gửi session_id -> Backend tự lấy User ID

        // 4. Cấu hình Headers
        const config = {
          params: params,
          headers: {}
        };
        
        if (token) {
          config.headers['Authorization'] = `Bearer ${token}`;
        }

        // 5. GỌI API (SỬA LẠI ĐƯỜNG DẪN THEO BACKEND CỦA BẠN)
        const response = await axios.get('/orders', config);
        
        // Gán dữ liệu
        this.orders = response.data.data.data;

      } catch (error) {
        console.error("Lỗi tải đơn hàng:", error);
      } finally {
        this.loading = false;
      }
    },

    changeTab(val) {
      this.currentStatus = val;
      this.fetchOrders();
    },

    formatCurrency(val) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
    },

    getImageUrl(path) {
      if (!path) return 'https://via.placeholder.com/80';
      if (path.startsWith('http')) return path;
      return path.startsWith('/') ? path : '/' + path;
    },

    getStatusLabel(status) {
      const map = {
        'pending': 'CHỜ XÁC NHẬN',
        'confirmed': 'ĐÃ XÁC NHẬN',
        'shipping': 'ĐANG VẬN CHUYỂN',
        'completed': 'HOÀN THÀNH',
        'cancelled': 'ĐÃ HỦY'
      };
      return map[status] || status;
    },

    viewDetail(orderCode) {
       // 1. Kiểm tra xem hiện tại có đang xem bằng session_id không
       const currentSession = this.$route.query.session_id;
       
       // 2. Chuyển hướng kèm theo query params
       this.$router.push({
           path: `/user/order/${orderCode}`,
           query: currentSession ? { session_id: currentSession } : {}
       });
    },

    handleReview(orderCode) {
       this.$router.push({path: `/reviews/${orderCode}`,})
    },
    
    openBuyAgainModal(order) {
      this.selectedOrder = order;
      this.showConfirmModal = true;
    },

    // 2. HÀM CHẠY KHI BẤM "ĐỒNG Ý" TRONG MODAL
    async executeBuyAgain() {
      // Đóng modal xác nhận & hiện loading (nếu muốn)
      this.showConfirmModal = false;
      this.loading = true; // Tận dụng loading của trang hoặc tạo loading riêng

      try {
        const token = localStorage.getItem('token');
        const sessionId = localStorage.getItem('cart_session_id');

        const payload = {
          order_id: this.selectedOrder.id,
          session_id: sessionId
        };

        const config = { headers: {} };
        if (token) config.headers['Authorization'] = `Bearer ${token}`;

        // Gọi API
        const response = await axios.post('/cart/buy-again', payload, config);

        // THÀNH CÔNG -> Hiện Modal Kết quả
        this.showResultModal = true;
        this.resultTitle = 'Thành Công';
        this.resultMessage = response.data.message;
        this.isSuccess = true;

      } catch (error) {
        // THẤT BẠI -> Hiện Modal Lỗi
        console.error(error);
        this.showResultModal = true;
        this.resultTitle = 'Thất Bại';
        this.resultMessage = error.response?.data?.message || 'Có lỗi xảy ra.';
        this.isSuccess = false;
      } finally {
        this.loading = false;
      }
    },

    // 3. CÁC HÀM ĐÓNG MODAL
    closeModals() {
      this.showConfirmModal = false;
      this.selectedOrder = null;
    },

    closeResultModal() {
      this.showResultModal = false;
      // Nếu mua lại thành công thì mới chuyển trang
      if (this.isSuccess) {
        this.$router.push('/user/cart');
      }
    },
  }
};
</script>

<style scoped>
/* Thêm CSS Spinner cho đẹp */
.loading-state {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    padding: 20px;
    color: #555;
}
.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid #ddd;
    border-top-color: #ee4d2d;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Các CSS cũ của Shopee style giữ nguyên */
.shopee-container {
    background-color: #f5f5f5;
    min-height: 100vh;
    padding-top: 120px;
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
}
.main-content {
    max-width: 980px;
    margin: 0 auto;
}
.tabs-header {
    background: #fff;
    display: flex;
    margin-bottom: 20px;
    border-radius: 2px;
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.05);
}
.tab-item {
    flex: 1;
    text-align: center;
    padding: 16px 0;
    cursor: pointer;
    font-size: 16px;
    color: #333;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
}
.tab-item:hover {
    color: #ee4d2d;
}
.tab-item.active {
    color: #ee4d2d;
    border-bottom: 2px solid #ee4d2d;
}
.empty-state {
    background: #fff;
    text-align: center;
    padding: 100px 0;
    width: 100%;
}
.empty-state img {
    width: 100px;
    margin-bottom: 20px;
}
.order-card {
    background: #fff;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.05);
    border-radius: 2px;
}
.card-header {
    padding: 20px;
    border-bottom: 1px solid #f1f1f1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.shop-info {
    display: flex;
    align-items: center;
    gap: 10px;
}
.favorite-badge {
    background: #ee4d2d;
    color: #fff;
    font-size: 12px;
    padding: 2px 4px;
    border-radius: 2px;
}
.shop-name {
    font-weight: 500;
}
.btn-chat, .btn-view-shop {
    background: #ee4d2d;
    color: #fff;
    border: none;
    padding: 4px 10px;
    font-size: 12px;
    cursor: pointer;
    border-radius: 2px;
}
.btn-view-shop {
    background: #fff;
    border: 1px solid #ccc;
    color: #555;
}
.order-status {
    color: #ee4d2d;
    text-transform: uppercase;
    font-size: 14px;
}
.card-body {
    padding: 20px;
}
.product-item {
    display: flex;
    gap: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #fafafa;
    margin-bottom: 15px;
}
.product-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}
.img-wrapper img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border: 1px solid #e1e1e1;
}
.product-info {
    flex: 1;
}
.product-name {
    font-size: 16px;
    margin: 0 0 5px;
    font-weight: 400;
    line-height: 20px;
    color: rgba(0,0,0,.87);
}
.product-variant {
    color: rgba(0,0,0,.54);
    font-size: 14px;
    margin-bottom: 5px;
}
.product-qty {
    font-size: 14px;
}
.product-price {
    text-align: right;
}
.old-price {
    text-decoration: line-through;
    color: #999;
    font-size: 14px;
    margin-right: 10px;
}
.current-price {
    color: #ee4d2d;
    font-size: 16px;
}
.card-footer {
    background: #fffbf8;
    padding: 24px;
    border-top: 1px dotted #e8e8e8;
}
.total-section {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}
.total-label {
    font-size: 14px;
    color: #000;
}
.total-price {
    font-size: 24px;
    color: #ee4d2d;
    font-weight: 500;
}
.action-buttons {
    display: flex;
    justify-content: flex-end;
}
.btn-group {
    display: flex;
    gap: 10px;
    align-items: center;
}
.btn-solid {
    background: #ee4d2d;
    color: #fff;
    border: 1px solid #ee4d2d;
    padding: 10px 40px;
    border-radius: 2px;
    cursor: pointer;
    font-size: 14px;
}
.btn-solid:hover {
    background: #d7321e;
}
.btn-outline {
    background: #fff;
    color: #555;
    border: 1px solid #dbdbdb;
    padding: 10px 20px;
    border-radius: 2px;
    cursor: pointer;
    font-size: 14px;
}
.btn-outline:hover {
    background: #fdfdfd;
    border-color: #ccc;
}
.note-text {
    color: #888;
    font-size: 14px;
    margin-right: 10px;
}

/* Lớp phủ màn hình (Backdrop) */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6); /* Nền đen mờ */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  animation: fadeIn 0.2s ease-out;
}

/* Hộp Modal */
.modal-content {
  background: #fff;
  width: 90%;
  max-width: 400px;
  padding: 30px;
  border-radius: 0; /* Vuông vức theo style của bạn */
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
  text-align: center;
  border: 1px solid #000; /* Viền đen mỏng hiện đại */
}

.modal-title {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 15px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.modal-desc {
  font-size: 14px;
  color: #555;
  margin-bottom: 30px;
  line-height: 1.5;
}

/* Khu vực nút bấm */
.modal-actions {
  display: flex;
  gap: 15px;
  justify-content: center;
}

.btn-cancel, .btn-confirm {
  padding: 10px 25px;
  font-size: 14px;
  cursor: pointer;
  border: 1px solid #000;
  font-weight: 500;
  transition: all 0.2s;
  min-width: 100px;
}

.btn-cancel {
  background: #fff;
  color: #000;
}
.btn-cancel:hover {
  background: #f0f0f0;
}

.btn-confirm {
  background: #000;
  color: #fff;
}
.btn-confirm:hover {
  background: #333;
}

.full-width {
  width: 100%;
}

/* Hiệu ứng hiện dần */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>