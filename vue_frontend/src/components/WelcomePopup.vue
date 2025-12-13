<template>
  <transition name="bounce">
    <div v-if="isVisible && coupon" class="popup-overlay">
      <div class="popup-content">
        
        <button class="btn-close-popup" @click="closePopup">✕</button>

        <div class="popup-body text-center">
          <div class="sale-badge">FLASH SALE</div>
          
          <h2 class="popup-title">NHẬN ƯU ĐÃI NGAY</h2>
          
          <p class="popup-desc">
            {{ coupon.description || 'Chương trình khuyến mại đặc biệt dành riêng cho bạn.' }}
          </p>

          <div class="voucher-container">
            <div class="voucher-left">
              <span class="discount-amount">
                {{ coupon.discount_type === 'percent' ? `-${coupon.discount_value}%` : formatCurrency(coupon.discount_value) }}
              </span>
              <span class="discount-label">GIẢM GIÁ</span>
            </div>
            
            <div class="voucher-dash"></div>

            <div class="voucher-right" @click="copyCode">
              <span class="code-label">MÃ CODE:</span>
              <span class="code-text">{{ coupon.code }}</span>
              <span class="tap-copy" v-if="!copied">(Chạm để sao chép)</span>
              <span class="tap-copy text-success" v-else>ĐÃ SAO CHÉP!</span>
            </div>
          </div>

          <div class="expiry-info" v-if="coupon.end_date">
             Hết hạn: {{ formatDate(coupon.end_date) }}
          </div>

          <button class="btn-action" @click="closePopup">
            MUA SẮM NGAY
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// --- STATE ---
const isVisible = ref(false);
const coupon = ref(null); // Lưu dữ liệu coupon lấy từ API
const copied = ref(false);

// --- CONFIG ---
// Bạn nên tạo một route API public để lấy mã ngon nhất (VD: mã mới nhất active)
const API_URL = '/api/coupons/latest-active'; 

onMounted(async () => {
  // 1. Check Session (để không hiện lại nếu khách đã tắt)
  const hasSeen = sessionStorage.getItem('promo_popup_seen');
  if (hasSeen) return;

  // 2. Gọi API lấy mã khuyến mại
  try {
    // Giả lập gọi API (Bạn thay bằng axios.get thật nhé)
    // const res = await axios.get(API_URL);
    // coupon.value = res.data.data;

    // --- DỮ LIỆU MẪU (Xóa đoạn này khi có API thật) ---
    coupon.value = {
        code: 'BLACKFRIDAY2025',
        description: 'Black Friday! Giảm giá 50% áp dụng cho mọi đơn hàng.',
        discount_type: 'percent',
        discount_value: 50,
        end_date: '2025-12-31 23:59:00'
    };
    // --------------------------------------------------

    // Nếu có coupon thì mới hiện Popup
    if (coupon.value) {
        setTimeout(() => { isVisible.value = true; }, 1000);
    }

  } catch (error) {
    console.error("Lỗi lấy khuyến mại:", error);
  }
});

// --- METHODS ---
const closePopup = () => {
  isVisible.value = false;
  sessionStorage.setItem('promo_popup_seen', 'true');
};

const copyCode = () => {
  if(!coupon.value) return;
  navigator.clipboard.writeText(coupon.value.code);
  copied.value = true;
  setTimeout(() => { copied.value = false; }, 2000);
};

// Helper Format
const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};
const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('vi-VN');
};
</script>

<style scoped>
/* FONT */
@import url('https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Work+Sans:wght@700;900&display=swap');

/* OVERLAY */
.popup-overlay {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.6); /* Nền tối để tập trung vào Voucher */
  backdrop-filter: blur(8px);
  z-index: 9999;
  display: flex; justify-content: center; align-items: center;
}

/* CONTENT BOX */
.popup-content {
  position: relative;
  background: #fff;
  width: 95%; max-width: 450px;
  border: 4px solid #000;
  box-shadow: 12px 12px 0px #000; /* Brutalist Shadow */
  padding: 0; /* Padding xử lý bên trong */
  overflow: hidden;
}

/* HEADER STYLE */
.sale-badge {
  background: #000; color: #fff;
  display: inline-block;
  padding: 5px 15px;
  font-family: 'Space Mono', monospace;
  font-weight: bold;
  margin-top: 2rem;
  transform: rotate(-3deg);
}

.popup-title {
  font-family: 'Work Sans', sans-serif;
  font-size: 2.2rem;
  font-weight: 900;
  text-transform: uppercase;
  margin: 10px 0;
  line-height: 1;
}

.popup-desc {
  font-family: 'Space Mono', monospace;
  font-size: 0.9rem;
  padding: 0 20px;
  color: #555;
  margin-bottom: 1.5rem;
}

/* VOUCHER TICKET DESIGN */
.voucher-container {
  display: flex;
  margin: 0 1.5rem;
  border: 2px solid #000;
  background: #f4f4f4;
  position: relative;
  cursor: pointer;
  transition: transform 0.1s;
}
.voucher-container:active {
  transform: scale(0.98);
}

.voucher-left {
  background: #000;
  color: #fff;
  padding: 15px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  min-width: 100px;
}
.discount-amount {
  font-family: 'Work Sans', sans-serif;
  font-size: 1.8rem;
  font-weight: 900;
}
.discount-label { font-size: 0.7rem; letter-spacing: 1px; }

/* Đường cắt voucher (Dashed line) */
.voucher-dash {
  width: 0;
  border-left: 2px dashed #000;
  position: relative;
}
/* Tạo hình bán nguyệt ở đường cắt (Optional - nâng cao) */
.voucher-dash::before, .voucher-dash::after {
  content: ''; position: absolute; left: -6px; width: 12px; height: 12px;
  background: #fff; border-radius: 50%; border: 2px solid #000;
}
.voucher-dash::before { top: -8px; }
.voucher-dash::after { bottom: -8px; }

.voucher-right {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 10px;
}
.code-label { font-size: 0.7rem; font-weight: bold; color: #777; }
.code-text {
  font-family: 'Space Mono', monospace;
  font-size: 1.5rem;
  font-weight: 700;
  color: #000;
  letter-spacing: 1px;
}
.tap-copy { font-size: 0.65rem; color: #888; margin-top: 4px; }
.text-success { color: #000 !important; font-weight: bold; background: #fff; padding: 2px; }

/* FOOTER */
.expiry-info {
  margin-top: 10px;
  font-size: 0.75rem;
  color: #666;
  font-style: italic;
}

.btn-action {
  width: 100%;
  background: #000;
  color: #fff;
  border: none;
  border-top: 4px solid #000;
  padding: 1.2rem;
  font-family: 'Work Sans', sans-serif;
  font-weight: 900;
  font-size: 1.2rem;
  text-transform: uppercase;
  margin-top: 1.5rem;
  cursor: pointer;
}
.btn-action:hover {
  background: #ffff00; /* Vàng neon khi hover */
  color: #000;
}

.btn-close-popup {
  position: absolute; top: 10px; right: 10px;
  background: transparent; border: none; font-size: 1.5rem; cursor: pointer; z-index: 10;
  font-weight: bold;
}

/* ANIMATION */
.bounce-enter-active { animation: bounce-in 0.5s; }
.bounce-leave-active { animation: bounce-in 0.5s reverse; }
@keyframes bounce-in {
  0% { transform: scale(0); opacity: 0; }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); opacity: 1; }
}
</style>