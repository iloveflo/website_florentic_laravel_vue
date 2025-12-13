<template>
  <div class="page-container">
    
    <div v-if="isReviewed" class="modal-overlay">
        <div class="modal-content">
            <div class="success-icon">✅</div>
            <h3>ĐƠN HÀNG ĐÃ ĐƯỢC ĐÁNH GIÁ</h3>
            <p>Bạn đã gửi đánh giá cho đơn hàng <b>#{{ order?.order_code }}</b> rồi.</p>
            <p>Cảm ơn bạn đã chia sẻ trải nghiệm mua sắm!</p>
            
            <div class="modal-actions">
                <button @click="router.push('/')" class="btn-home">Về Trang Chủ</button>
            </div>
        </div>
    </div>

    <div v-else-if="order" class="content-wrapper">
      
      <div class="header">
        <h2 class="title">ĐÁNH GIÁ ĐƠN HÀNG <span class="order-id">#{{ order.order_code }}</span></h2>
        <div class="divider"></div>
      </div>
      
      <div v-for="(item, index) in reviews" :key="item.product_id" class="review-card">
        <div class="product-header">
          <div class="image-box">
            <img :src="item.image" alt="Product Image">
          </div>
          <div class="product-info">
            <h3 class="product-name">{{ item.product_name }}</h3>
            <p class="product-meta">Size: {{ item.size }} | Màu: {{ item.color }}</p>
          </div>
        </div>
        <div class="rating-box">
            <label class="label-text">CHẤT LƯỢNG SẢN PHẨM</label>
            
            <div class="star-wrapper">
                <button 
                    v-for="n in 5" 
                    :key="n" 
                    type="button"
                    @click="item.rating = n" 
                    class="star-btn" 
                    :class="n <= item.rating ? 'is-active' : ''"
                >
                    ★
                </button>
                <span class="rating-status">{{ getRatingText(item.rating) }}</span>
            </div>
            <textarea 
                v-model="item.comment" 
                class="comment-input" 
                rows="4"
                placeholder="Sản phẩm thế nào? Hãy viết vài dòng đánh giá..."
            ></textarea>
        </div>
      </div>

      <button 
        @click="submitReviews" 
        :disabled="isSubmitting"
        class="submit-btn"
      >
        {{ isSubmitting ? 'ĐANG GỬI...' : 'GỬI ĐÁNH GIÁ NGAY' }}
      </button>
    </div>

    <div v-else class="loading-state">
        <span class="loader"></span> ĐANG TẢI DỮ LIỆU...
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const order = ref(null);
const reviews = ref([]);
const isSubmitting = ref(false);
const isReviewed = ref(false); // Biến kiểm tra trạng thái

const getRatingText = (star) => {
    const texts = { 1: 'TỆ', 2: 'KHÔNG TỐT', 3: 'BÌNH THƯỜNG', 4: 'HÀI LÒNG', 5: 'TUYỆT VỜI' };
    return texts[star] || '';
};

onMounted(async () => {
  try {
    const res = await axios.get(`/orders/${route.params.order_code}/review-info`);
    order.value = res.data;

    // 1. KIỂM TRA LUÔN KHI VỪA TẢI XONG
    if (res.data.is_reviewed === true) {
        isReviewed.value = true;
        return; // Dừng, không cần map dữ liệu form làm gì
    }

    // Nếu chưa đánh giá thì mới map dữ liệu ra form
    reviews.value = res.data.order_items.map(item => ({
      product_id: item.product_id,
      product_name: item.product_name,
      image: item.product_image_url || 'https://via.placeholder.com/150',
      size: item.size, 
      color: item.color,
      rating: 5,
      comment: ''
    }));
  } catch (e) { 
    console.error(e);
    // Nếu API trả lỗi 404 hoặc lỗi khác thì đẩy về trang chủ hoặc báo lỗi
    alert("Không tìm thấy đơn hàng hoặc đơn hàng không tồn tại.");
    router.push('/');
  }
});

const submitReviews = async () => {
  if(!confirm('Gửi đánh giá này?')) return;
  isSubmitting.value = true;
  try {
    await axios.post('/reviews', {
      order_code: order.value.order_code,
      reviews: reviews.value.map(r => ({
        product_id: r.product_id,
        rating: r.rating,
        comment: r.comment
      }))
    });
    
    // Đánh giá xong thì chuyển sang màn hình thông báo luôn
    isReviewed.value = true; 
    
  } catch (e) { 
    alert('Lỗi khi gửi đánh giá, vui lòng thử lại.');
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
/* CSS CHO MODAL GIỮA MÀN HÌNH */
.modal-overlay {
    position: fixed;
    top: 0; 
    left: 0;
    width: 100%;
    height: 100vh;
    background: rgba(0, 0, 0, 0.6); /* Mờ nền đen */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.modal-content {
    background: white;
    padding: 40px;
    border-radius: 12px;
    text-align: center;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    animation: popIn 0.3s ease-out;
}

.success-icon {
    font-size: 50px;
    margin-bottom: 20px;
}

.modal-content h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-weight: bold;
}

.modal-content p {
    color: #666;
    margin-bottom: 25px;
}

.modal-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-home, .btn-history {
    padding: 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-home {
    background: #000;
    color: #fff;
}
.btn-home:hover { background: #333; }

@keyframes popIn {
    0% { transform: scale(0.8); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}


/* --- CẤU TRÚC TRANG --- */
.page-container {
    min-height: 100vh;
    background-color: #f8f8f8; /* Màu nền xám cực nhạt để làm nổi bật khung trắng */
    font-family: 'Arial', sans-serif; /* Font đơn giản, dễ đọc */
    padding: 120px 1rem 2rem 1rem;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.content-wrapper {
    width: 100%;
    max-width: 700px; /* Giới hạn chiều rộng để không bị loãng trên màn hình to */
    background: #ffffff;
    padding: 2rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border: 1px solid #000; /* Viền đen bao quanh khung chính */
}

/* --- HEADER --- */
.header {
    margin-bottom: 2rem;
    text-align: center;
}
.title {
    font-size: 1.5rem;
    font-weight: 800;
    text-transform: uppercase;
    color: #000;
    margin-bottom: 0.5rem;
    letter-spacing: 1px;
}
.order-id {
    color: #555;
    font-weight: 400;
}
.divider {
    height: 4px;
    background: #000;
    width: 60px;
    margin: 0 auto;
}

/* --- THẺ REVIEW --- */
.review-card {
    margin-bottom: 3rem; /* Khoảng cách giữa các sản phẩm lớn hơn */
    border-bottom: 1px dashed #ccc; /* Đường kẻ phân cách */
    padding-bottom: 2rem;
}
.review-card:last-child {
    border-bottom: none;
}

/* --- PHẦN 1: ẢNH & THÔNG TIN --- */
.product-header {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
    align-items: center;
}

.image-box {
    width: 80px;  /* CỐ ĐỊNH CHIỀU RỘNG */
    height: 80px; /* CỐ ĐỊNH CHIỀU CAO */
    flex-shrink: 0; /* Không cho co lại */
    border: 1px solid #000;
    padding: 2px;
}

.image-box img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Cắt ảnh vừa khung, không bị méo */
    display: block;
}

.product-info {
    flex: 1;
}

.product-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: #000;
    text-transform: uppercase;
    line-height: 1.3;
}

.product-meta {
    font-size: 0.9rem;
    color: #666;
    margin-top: 0.25rem;
}

/* --- PHẦN 2: FORM ĐÁNH GIÁ --- */
.rating-box {
    background: #fff;
}

.label-text {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #000;
    display: block;
    margin-bottom: 0.5rem;
    letter-spacing: 0.5px;
}

/* SAO - Đã chỉnh to lên */
.star-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.star-btn {
    background: none;
    border: none;
    font-size: 3rem; /* SAO TO */
    line-height: 1;
    cursor: pointer;
    color: #e0e0e0; /* Màu xám khi chưa chọn */
    transition: transform 0.1s, color 0.2s;
    padding: 0;
}

.star-btn:hover {
    transform: scale(1.1);
}

.star-btn.is-active {
    color:yellow; /* Màu vàng sáng khi được chọn */
    /* Hoặc dùng màu vàng nếu muốn: color: #FFD700; */
}

.rating-status {
    margin-left: 1rem;
    font-weight: bold;
    font-size: 0.9rem;
    background: #000;
    color: #fff;
    padding: 2px 8px;
    text-transform: uppercase;
}

/* TEXTAREA */
.comment-input {
    width: 100%;
    padding: 1rem;
    font-size: 1rem;
    border: 1px solid #000; /* Viền đen sắc nét */
    border-radius: 0; /* Vuông vức */
    outline: none;
    background: #fdfdfd;
    transition: background 0.2s;
}

.comment-input:focus {
    background: #fff;
    box-shadow: 4px 4px 0 0 rgba(0,0,0,0.1); /* Hiệu ứng nổi nhẹ khi gõ */
}

/* --- NÚT GỬI --- */
.submit-btn {
    width: 100%;
    background-color: #000000; /* Nền ĐEN tuyệt đối */
    color: #ffffff;            /* Chữ TRẮNG tuyệt đối */
    font-size: 1.1rem;
    font-weight: 700;
    padding: 1.2rem;
    border: none;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: opacity 0.2s, transform 0.1s;
    margin-top: 1rem;
}

.submit-btn:hover {
    opacity: 0.85; /* Hơi mờ nhẹ khi di chuột để biết có tác động */
}

.submit-btn:active {
    transform: scale(0.99);
}

.submit-btn:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

.loading-state {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
    font-weight: bold;
    letter-spacing: 2px;
}
</style>