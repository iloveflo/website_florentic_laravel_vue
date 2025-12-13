<template>
  <div class="order-detail-page">
    <div class="container">
      
      <div v-if="loading" class="loading-box">
        <div class="spinner"></div>
        <p>Đang tải chi tiết đơn hàng...</p>
      </div>

      <div v-else-if="error" class="error-box">
        <div class="icon">⚠️</div>
        <h3>{{ error }}</h3>
        <button @click="$router.push('/user/orders')" class="btn-back">Quay lại danh sách</button>
      </div>

      <div v-else-if="order" class="content-wrapper">
        
        <div class="section-card header-card">
          <div class="header-left">
             <button @click="$router.go(-1)" class="back-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                TRỞ LẠI
             </button>
             <span class="order-code">MÃ ĐƠN: {{ order.order_code }}</span>
          </div>
          <div class="header-right">
             <span class="status-text">{{ getStatusLabel(order.order_status) }}</span>
          </div>
        </div>

        <div class="section-card status-stepper">
           <div :class="['step', isStepActive('pending') ? 'active' : '']">Đặt Hàng</div>
           <div class="line"></div>
           <div :class="['step', isStepActive('confirmed') ? 'active' : '']">Đã Xác Nhận</div>
           <div class="line"></div>
           <div :class="['step', isStepActive('shipping') ? 'active' : '']">Vận Chuyển</div>
           <div class="line"></div>
           <div :class="['step', isStepActive('completed') ? 'active' : '']">Hoàn Thành</div>
        </div>

        <div class="section-card address-card">
           <h3>Địa Chỉ Nhận Hàng</h3>
           <div class="address-info">
              <p class="name">{{ order.full_name }}</p>
              <p class="phone">{{ order.phone }}</p>
              <p class="address">{{ order.address }}</p>
              <p class="note" v-if="order.note">Ghi chú: {{ order.note }}</p>
           </div>
        </div>

        <div class="section-card products-card">
           <div class="product-list">
              <div v-for="item in order.order_items" :key="item.id" class="product-item">
                 <div class="img-wrapper">
                    <img :src="getImageUrl(item.product_image)" alt="sp">
                 </div>
                 <div class="info-wrapper">
                    <div class="name">{{ item.product_name }}</div>
                    <div class="variant">Phân loại: {{ item.color }}, {{ item.size }}</div>
                    <div class="qty">x{{ item.quantity }}</div>
                 </div>
                 <div class="price-wrapper">
                    {{ formatCurrency(item.price) }}
                 </div>
              </div>
           </div>
        </div>

        <div class="section-card summary-card">
           <div class="summary-row">
              <span>Tổng tiền hàng</span>
              <span>{{ formatCurrency(order.subtotal) }}</span>
           </div>
           <div class="summary-row">
              <span>Phí vận chuyển</span>
              <span>{{ formatCurrency(order.shipping_fee) }}</span>
           </div>
           <div class="summary-row" v-if="order.discount_amount > 0">
              <span>Giảm giá Voucher</span>
              <span class="discount">-{{ formatCurrency(order.discount_amount) }}</span>
           </div>
           <div class="summary-row total">
              <span>Thành tiền</span>
              <span class="total-price">{{ formatCurrency(order.total_amount) }}</span>
           </div>
           
           <div class="payment-method-info">
              Phương thức thanh toán: <strong>{{ order.payment_method === 'cod' ? 'Thanh toán khi nhận hàng' : order.payment_method }}</strong>
           </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      order: null,
      loading: true,
      error: null
    };
  },
  mounted() {
    this.fetchOrderDetail();
  },
  methods: {
    async fetchOrderDetail() {
      this.loading = true;
      this.error = null;
      try {
        // 1. Lấy mã đơn hàng từ URL
        const token = localStorage.getItem('token'); // Lấy Token nếu có
        const orderCode = this.$route.params.order_code;
        const urlSessionId = this.$route.query.session_id; 
        const localSessionId = localStorage.getItem('cart_session_id');
        const sessionId = urlSessionId || localSessionId; // Ưu tiên URL
        // 3. Chuẩn bị Config
        const params = {};
        
        // LOGIC KHỚP VỚI CONTROLLER:
        // Nếu không có Token (Khách vãng lai), Gửi session_id vào params
        if (!token && sessionId) {
            params.session_id = sessionId;
        }

        const config = {
            params: params,
            headers: {}
        };

        // Nếu có Token (User), Gửi vào Header
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }

        // 4. Gọi API
        // Lưu ý: Route phải khớp với api.php (VD: /api/orders/{code})
        const response = await axios.get(`/orders/${orderCode}`, config);

        this.order = response.data.data;

      } catch (err) {
        console.error(err);
        if (err.response && err.response.status === 401) {
             this.error = "Bạn không có quyền xem đơn hàng này.";
        } else if (err.response && err.response.status === 404) {
             this.error = "Không tìm thấy đơn hàng.";
        } else {
             this.error = "Có lỗi xảy ra khi tải dữ liệu.";
        }
      } finally {
        this.loading = false;
      }
    },

    // --- CÁC HÀM HELPER ---
    formatCurrency(val) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
    },
    
    getImageUrl(path) {
      if (!path) return 'https://via.placeholder.com/80';
      if (path.startsWith('http')) return path;
      // Dùng Proxy /uploads
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

    // Helper đơn giản để highlight timeline
    isStepActive(stepName) {
        if (!this.order) return false;
        const status = this.order.order_status;
        const steps = ['pending', 'confirmed', 'shipping', 'completed'];
        // Nếu đã hủy thì không active gì cả hoặc logic riêng
        if (status === 'cancelled') return false;
        
        return steps.indexOf(status) >= steps.indexOf(stepName);
    }
  }
};
</script>

<style scoped>
.order-detail-page {
    background-color: #f5f5f5;
    min-height: 100vh;
    padding: 120px 0;
    font-family: 'Helvetica Neue', Arial, sans-serif;
    color: #333;
}
.container {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 15px;
}

/* CARDS */
.section-card {
    background: #fff;
    border-radius: 3px;
    box-shadow: 0 1px 1px 0 rgba(0,0,0,0.05);
    padding: 24px;
    margin-bottom: 15px;
}

/* HEADER */
.header-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.back-link {
    background: none;
    border: none;
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    color: #666;
    font-size: 13px;
    font-weight: 500;
}
.order-code {
    margin-left: 15px;
    font-weight: 600;
    font-size: 14px;
}
.status-text {
    color: #ee4d2d;
    font-weight: 600;
    text-transform: uppercase;
}

/* STEPPER */
.status-stepper {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.step {
    font-size: 14px;
    color: #bbb;
    font-weight: 500;
}
.step.active {
    color: #26aa99; /* Màu xanh Shopee mall hoặc #ee4d2d */
}
.line {
    flex: 1;
    height: 2px;
    background: #e0e0e0;
    margin: 0 10px;
}

/* ADDRESS */
.address-card h3 {
    margin: 0 0 15px 0;
    font-size: 16px;
    font-weight: 600;
}
.address-info p {
    margin: 5px 0;
    font-size: 14px;
    color: #555;
}
.address-info .name {
    font-weight: 600;
    color: #000;
}

/* PRODUCTS */
.product-item {
    display: flex;
    gap: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f1f1f1;
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
.info-wrapper {
    flex: 1;
}
.info-wrapper .name {
    font-size: 15px;
    color: #333;
    margin-bottom: 5px;
}
.info-wrapper .variant {
    font-size: 13px;
    color: #888;
}
.info-wrapper .qty {
    margin-top: 5px;
    font-size: 14px;
}
.price-wrapper {
    font-weight: 500;
    color: #ee4d2d;
}

/* SUMMARY */
.summary-card {
    background: #fffbf8; /* Màu nền nhẹ nhàng cho phần tiền */
    border-top: 1px dotted #e8e8e8;
}
.summary-row {
    display: flex;
    justify-content: flex-end;
    gap: 20px;
    margin-bottom: 10px;
    font-size: 14px;
    color: #777;
}
.summary-row span:last-child {
    width: 150px;
    text-align: right;
    color: #333;
}
.summary-row.total {
    margin-top: 20px;
    font-size: 18px;
    align-items: center;
}
.total-price {
    color: #ee4d2d !important;
    font-size: 24px;
    font-weight: 600;
}
.payment-method-info {
    text-align: right;
    margin-top: 20px;
    font-size: 13px;
    color: #888;
    padding-top: 10px;
    border-top: 1px solid #eee;
}

/* LOADING & ERROR */
.loading-box, .error-box {
    text-align: center;
    padding: 50px;
    background: #fff;
}
.spinner {
    width: 30px;
    height: 30px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #ee4d2d;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

.btn-back {
    margin-top: 15px;
    padding: 10px 20px;
    background: #ee4d2d;
    color: #fff;
    border: none;
    cursor: pointer;
}
</style>