<template>
  <div class="vnpay-response-container">
    
    <div class="header clearfix">
      <h3 class="text-muted">VNPAY RESPONSE</h3>
    </div>

    <div v-if="loading" class="text-center">
      <p>Đang xác thực giao dịch...</p>
    </div>

    <div v-else class="table-responsive">
      
      <div class="form-group">
        <label>Mã đơn hàng:</label>
        <label class="value">{{ resultData.vnp_TxnRef }}</label>
      </div>

      <div class="form-group">
        <label>Số tiền:</label>
        <label class="value">{{ formatCurrency(resultData.vnp_Amount / 100) }}</label>
      </div>

      <div class="form-group">
        <label>Nội dung thanh toán:</label>
        <label class="value">{{ resultData.vnp_OrderInfo }}</label>
      </div>

      <div class="form-group">
        <label>Mã phản hồi:</label>
        <label class="value">{{ resultData.vnp_ResponseCode }}</label>
      </div>

      <div class="form-group">
        <label>Mã GD Tại VNPAY:</label>
        <label class="value">{{ resultData.vnp_TransactionNo }}</label>
      </div>

      <div class="form-group">
        <label>Mã Ngân hàng:</label>
        <label class="value">{{ resultData.vnp_BankCode }}</label>
      </div>

      <div class="form-group">
        <label>Thời gian thanh toán:</label>
        <label class="value">{{ formatDate(resultData.vnp_PayDate) }}</label>
      </div>

      <div class="form-group">
        <label>Kết quả:</label>
        <label class="value">
          <span v-if="isValidSignature && isSuccess" style="color:blue">Giao dịch thành công</span>
          <span v-else-if="isValidSignature && !isSuccess" style="color:red">Giao dịch không thành công</span>
          <span v-else style="color:red">Chữ ký không hợp lệ</span>
        </label>
      </div>

      <div style="margin-top: 20px;">
        <button @click="goHome" class="btn-return">Quay về trang chủ</button>
      </div>
    </div>

    <footer class="footer">
      <p>&copy; VNPAY {{ new Date().getFullYear() }}</p>
    </footer>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const resultData = ref({});
const isSuccess = ref(false);
const isValidSignature = ref(false);

const formatCurrency = (value) => {
  if (!value) return '0 VND';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  // Định dạng chuỗi YYYYMMDDHHmmss thành DD/MM/YYYY HH:mm:ss
  return dateString.replace(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/, "$3/$2/$1 $4:$5:$6");
};

const goHome = () => {
  router.push('/');
};

onMounted(async () => {
  try {
    // Gọi về Laravel để check hash (thay URL API của bạn vào đây)
    const response = await axios.get('/payment/vnpay-callback', {
      params: route.query
    });

    const apiRes = response.data;
    
    // Gán dữ liệu vào biến để hiển thị
    resultData.value = apiRes.data;
    isSuccess.value = (apiRes.status === 'success');
    isValidSignature.value = apiRes.signature_valid;

  } catch (error) {
    console.error("Lỗi xác thực:", error);
    isValidSignature.value = false; // Coi như sai chữ ký nếu lỗi mạng
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
/* 1. Cấu hình Font chữ & Reset cơ bản */
.vnpay-response-container {
  font-family: 'Courier New', Courier, monospace; /* Font đơn gõ tạo cảm giác hoá đơn/kỹ thuật */
  background-color: #ffffff;
  color: #000000;
  max-width: 600px;
  margin: 150px auto;
  border: 3px solid #000000; /* Viền dày tạo khối */
  box-shadow: 10px 10px 0px #000000; /* Đổ bóng cứng (Hard shadow) đặc trưng */
  padding: 0; /* Reset padding để content tràn viền */
}

/* 2. Phần Header */
.header {
  background-color: #000000;
  color: #ffffff;
  padding: 20px;
  text-align: center;
  border-bottom: 3px solid #000000;
}

.header h3 {
  margin: 0;
  text-transform: uppercase;
  font-weight: 900;
  letter-spacing: 2px;
  font-size: 24px;
}

/* 3. Phần Nội dung chính */
.table-responsive {
  padding: 30px;
}

/* Biến mỗi dòng dữ liệu thành 1 hàng kẻ ngang */
.form-group {
  display: flex;
  justify-content: space-between; /* Đẩy nhãn và giá trị sang 2 bên */
  align-items: center;
  padding: 15px 0;
  border-bottom: 1px dashed #000000; /* Đường kẻ đứt nét phong cách receipt */
}

.form-group:last-child {
  border-bottom: none; /* Bỏ kẻ dòng cuối */
}

/* Nhãn bên trái (Key) */
.form-group label {
  font-weight: 700;
  text-transform: uppercase;
  font-size: 14px;
  flex-shrink: 0; /* Không bị co lại */
  margin-right: 20px;
}

/* Giá trị bên phải (Value) */
.form-group label.value {
  font-weight: 400;
  text-align: right;
  word-break: break-all; /* Xuống dòng nếu mã quá dài */
}

/* 4. Xử lý phần Kết quả (Ghi đè màu inline của HTML cũ) */
.form-group label.value span {
  font-weight: 900 !important;
  text-transform: uppercase;
  padding: 4px 8px;
  border: 1px solid #000;
  color: #000 !important; /* Force về màu đen */
}

/* Style riêng cho Success (Nền đen chữ trắng) */
.form-group label.value span[style*="blue"] {
  background-color: #000;
  color: #fff !important;
}

/* Style riêng cho Error (Nền trắng chữ đen gạch chéo hoặc viền đậm) */
.form-group label.value span[style*="red"] {
  background-color: #fff;
  color: #000 !important;
  text-decoration: line-through; /* Gạch ngang để thể hiện lỗi */
}

/* 5. Nút bấm (Button) */
.btn-return {
  display: block;
  width: 100%;
  padding: 15px;
  background-color: #fff;
  color: #000;
  border: 2px solid #000;
  font-weight: 700;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 30px;
  font-family: inherit; /* Kế thừa font monospace */
  font-size: 16px;
}

.btn-return:hover {
  background-color: #000;
  color: #fff;
  transform: translate(-4px, -4px); /* Hiệu ứng nhấc nút lên */
  box-shadow: 4px 4px 0px #000; /* Tạo bóng giả khi hover */
}

/* 6. Footer */
.footer {
  text-align: center;
  padding: 15px;
  font-size: 12px;
  border-top: 3px solid #000;
  background-color: #f0f0f0;
  font-weight: bold;
}

/* Loading state */
.text-center {
  padding: 40px;
  text-align: center;
  font-style: italic;
}
</style>