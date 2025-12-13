<template>
  <div class="coupon-page-container">
    
    <div class="page-header">
      <h4 class="page-title">Quản Lý Mã Khuyến Mại</h4>
      <button class="btn-action primary" @click="openModal()">
        <i class="fas fa-plus"></i> Thêm Mã Mới
      </button>
    </div>

    <div class="filter-section">
      <div class="filter-group search-box">
        <input type="text" 
               v-model="filters.keyword" 
               @input="handleSearch" 
               placeholder="Nhập mã code để tìm...">
      </div>

      <div class="filter-group">
        <select v-model="filters.status" @change="fetchCoupons(1)">
          <option value="all">-- Tất cả trạng thái --</option>
          <option value="active">Đang hoạt động</option>
          <option value="inactive">Tạm khóa</option>
        </select>
      </div>

      <div class="filter-group">
        <select v-model="filters.filter_expiry" @change="fetchCoupons(1)">
          <option value="all">-- Tất cả thời hạn --</option>
          <option value="valid">Còn hạn sử dụng</option>
          <option value="expired">Đã hết hạn</option>
        </select>
      </div>

      <div class="filter-group">
        <button class="btn-action secondary full-width" @click="resetFilters">
          <i class="fas fa-sync-alt"></i> Đặt lại
        </button>
      </div>
    </div>

    <div class="main-content">
      <div v-if="alert.message" :class="['alert-box', alert.type]">
        {{ alert.message }}
      </div>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Đang tải dữ liệu...</p>
      </div>

      <div v-else class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Mã Code</th>
              <th>Giảm giá</th>
              <th>Điều kiện</th>
              <th>Lượt dùng</th>
              <th>Thời gian</th>
              <th>Trạng thái</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="coupon in coupons" :key="coupon.id">
              <td>
                <strong>{{ coupon.code }}</strong>
                <div class="description-text">{{ coupon.description }}</div>
              </td>
              <td>
                <span v-if="coupon.discount_type === 'percent'" class="tag info">
                  {{ coupon.discount_value }}%
                </span>
                <span v-else class="tag success">
                  -{{ formatCurrency(coupon.discount_value) }}
                </span>
                <div v-if="coupon.max_discount" class="helper-text danger">
                  Tối đa: {{ formatCurrency(coupon.max_discount) }}
                </div>
              </td>
              <td>
                <span class="label">Đơn tối thiểu:</span>
                <div>{{ formatCurrency(coupon.min_order_value || 0) }}</div>
              </td>
              <td>
                {{ coupon.used_count }} / {{ coupon.usage_limit || '∞' }}
              </td>
              <td>
                <div class="time-info">
                  Start: {{ formatDate(coupon.start_date) }} <br>
                  End: {{ formatDate(coupon.end_date) }}
                </div>
              </td>
              <td>
                <span :class="['status-badge', coupon.status]">
                  {{ coupon.status === 'active' ? 'Hoạt động' : 'Tạm khóa' }}
                </span>
              </td>
              <td class="action-buttons">
                <button class="btn-small edit" @click="editCoupon(coupon)">Sửa</button>
                <button class="btn-small delete" @click="deleteCoupon(coupon.id)">Xóa</button>
              </td>
            </tr>
            <tr v-if="coupons.length === 0">
              <td colspan="7" class="empty-state">
                  Không tìm thấy dữ liệu phù hợp.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <nav v-if="pagination.last_page > 1" class="pagination-wrapper">
        <ul class="pagination-list">
          <li :class="{ disabled: pagination.current_page === 1 }">
            <button @click="fetchCoupons(pagination.current_page - 1)">Trước</button>
          </li>
          
          <li v-for="page in pagination.last_page" :key="page" 
              :class="{ active: page === pagination.current_page }">
            <button @click="fetchCoupons(page)">{{ page }}</button>
          </li>

          <li :class="{ disabled: pagination.current_page === pagination.last_page }">
            <button @click="fetchCoupons(pagination.current_page + 1)">Sau</button>
          </li>
        </ul>
      </nav>
    </div>

    <div v-if="showModal" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h5>{{ isEditing ? 'Cập nhật Mã' : 'Thêm Mã Mới' }}</h5>
          <button type="button" class="close-btn" @click="closeModal()">×</button>
        </div>
        
        <div class="modal-body">
          <form @submit.prevent="saveCoupon">
             <div class="form-grid">
              
              <div class="form-column">
                <div class="form-group">
                  <label>Mã Code <span class="required">*</span></label>
                  <input type="text" v-model="form.code" :class="{ 'error': errors.code }" placeholder="VD: SALE2025">
                  <span class="error-msg">{{ errors.code?.[0] }}</span>
                </div>

                <div class="form-group">
                  <label>Loại giảm giá</label>
                  <select v-model="form.discount_type">
                    <option value="percent">Phần trăm (%)</option>
                    <option value="fixed">Số tiền cố định</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Giá trị giảm <span class="required">*</span></label>
                  <input type="number" v-model="form.discount_value" :class="{ 'error': errors.discount_value }">
                  <span class="error-msg">{{ errors.discount_value?.[0] }}</span>
                </div>

                <div class="form-group">
                    <label>Giảm tối đa (Nếu là %)</label>
                    <input type="number" v-model="form.max_discount">
                </div>
              </div>

              <div class="form-column">
                <div class="form-group">
                  <label>Đơn tối thiểu</label>
                  <input type="number" v-model="form.min_order_value">
                </div>

                <div class="form-group">
                  <label>Giới hạn số lần dùng</label>
                  <input type="number" v-model="form.usage_limit" placeholder="Để trống nếu không giới hạn">
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label>Ngày bắt đầu</label>
                        <input type="datetime-local" v-model="form.start_date" :class="{ 'error': errors.start_date }">
                        <span class="error-msg">{{ errors.start_date?.[0] }}</span>
                    </div>
                    <div class="form-group half">
                        <label>Ngày kết thúc</label>
                        <input type="datetime-local" v-model="form.end_date" :class="{ 'error': errors.end_date }">
                         <span class="error-msg">{{ errors.end_date?.[0] }}</span>
                    </div>
                </div>

                <div class="form-group">
                  <label>Trạng thái</label>
                  <select v-model="form.status">
                    <option value="active">Hoạt động</option>
                    <option value="inactive">Tạm khóa</option>
                  </select>
                </div>
              </div>
              
              <div class="form-column full">
                 <label>Mô tả</label>
                 <textarea v-model="form.description" rows="2"></textarea>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn-action secondary" @click="closeModal()">Hủy</button>
              <button type="submit" class="btn-action primary" :disabled="isSubmitting">
                {{ isSubmitting ? 'Đang lưu...' : 'Lưu lại' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

// --- CONFIG ---
// Đảm bảo đường dẫn này đúng với route trong routes/api.php
const API_URL = '/admin/coupons'; 

// --- STATE ---
const coupons = ref([]);
const loading = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const isSubmitting = ref(false);
const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0
});

// BIẾN LƯU TRẠNG THÁI BỘ LỌC (MỚI)
const filters = reactive({
    keyword: '',
    status: 'all',
    filter_expiry: 'all'
});
let searchTimeout = null; // Để debounce search

const alert = reactive({ message: '', type: '' });
const errors = ref({});

// Form Model
const form = reactive({
    id: null,
    code: '',
    description: '',
    discount_type: 'percent', 
    discount_value: 0,
    min_order_value: 0,
    max_discount: null,
    usage_limit: null,
    start_date: '',
    end_date: '',
    status: 'active'
});

// --- METHODS ---

// 1. Lấy danh sách Coupon (CẬP NHẬT)
const fetchCoupons = async (page = 1) => {
    loading.value = true;
    try {
        // Tạo params gửi lên
        const params = {
            page: page,
            // Nếu là 'all' thì không gửi lên, hoặc gửi rỗng tùy backend xử lý
            status: filters.status !== 'all' ? filters.status : null,
            filter_expiry: filters.filter_expiry !== 'all' ? filters.filter_expiry : null,
            keyword: filters.keyword
        };

        const response = await axios.get(API_URL, { params });
        
        const result = response.data.data; // Cấu trúc trả về từ Laravel Paginate
        coupons.value = result.data;
        pagination.value = {
            current_page: result.current_page,
            last_page: result.last_page,
            total: result.total
        };
    } catch (error) {
        console.error(error);
        showAlert('Không thể tải dữ liệu', 'error');
    } finally {
        loading.value = false;
    }
};

// Hàm xử lý tìm kiếm có delay (Debounce) để tránh gọi API liên tục khi gõ
const handleSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchCoupons(1); // Reset về trang 1 khi tìm kiếm
    }, 500); // Đợi 500ms sau khi ngừng gõ mới tìm
};

// Hàm đặt lại bộ lọc
const resetFilters = () => {
    filters.keyword = '';
    filters.status = 'all';
    filters.filter_expiry = 'all';
    fetchCoupons(1);
};

// 2. Mở Modal
const openModal = () => {
    resetForm();
    isEditing.value = false;
    showModal.value = true;
    errors.value = {};
};

const editCoupon = (coupon) => {
    Object.assign(form, coupon);
    if(form.start_date) form.start_date = form.start_date.slice(0, 16);
    if(form.end_date) form.end_date = form.end_date.slice(0, 16);
    isEditing.value = true;
    showModal.value = true;
    errors.value = {};
};

const closeModal = () => {
    showModal.value = false;
};

// 3. Lưu
const saveCoupon = async () => {
    isSubmitting.value = true;
    errors.value = {}; 

    try {
        let response;
        if (isEditing.value) {
            response = await axios.post(`${API_URL}/${form.id}`, {
        ...form,        // Lấy toàn bộ dữ liệu đang có trong form
        _method: 'PUT'  // Thêm dòng này để Laravel hiểu là update
    });
        } else {
            response = await axios.post(API_URL, form);
        }

        showAlert(response.data.message, 'success');
        closeModal();
        fetchCoupons(pagination.value.current_page); 

    } catch (error) {
        if (error.response && error.response.status === 422) {
            errors.value = error.response.data.errors;
        } else {
            showAlert('Có lỗi xảy ra, vui lòng thử lại.', 'error');
        }
    } finally {
        isSubmitting.value = false;
    }
};

// 4. Xóa
const deleteCoupon = async (id) => {
    if (!confirm('Bạn có chắc chắn muốn xóa mã giảm giá này?')) return;

    try {
        await axios.post(`${API_URL}/${id}`, {
    _method: 'DELETE'
});
        showAlert('Đã xóa mã giảm giá.', 'success');
        fetchCoupons(pagination.value.current_page);
    } catch (error) {
        const msg = error.response?.data?.message || 'Không thể xóa.';
        showAlert(msg, 'error');
    }
};

const resetForm = () => {
    Object.assign(form, {
        id: null, code: '', description: '', discount_type: 'percent',
        discount_value: 0, min_order_value: 0, max_discount: null,
        usage_limit: null, start_date: '', end_date: '', status: 'active'
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleString('vi-VN');
};

const showAlert = (msg, type) => {
    alert.message = msg;
    alert.type = type;
    setTimeout(() => { alert.message = ''; }, 3000);
};

onMounted(() => {
    fetchCoupons();
});
</script>

<style scoped>
/* ============================
   1. VARIABLES & RESET
   ============================ */
:root {
  --primary: #000000;
  --bg-page: #f8f9fa;
  --bg-card: #ffffff;
  --border-color: #e0e0e0;
  --border-strong: #000000;
  --text-main: #212529;
  --text-light: #6c757d;
  --danger: #dc3545;
  --success: #198754;
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

.coupon-page-container {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px 20px;
  color: var(--text-main);
  background-color: var(--bg-page);
  min-height: 100vh;
}

/* ============================
   2. HEADER
   ============================ */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: -0.5px;
  color: var(--primary);
}

/* ============================
   3. FILTERS
   ============================ */
.filter-section {
  display: grid;
  grid-template-columns: 2fr 1.5fr 1.5fr 1fr; /* Chia cột tỉ lệ */
  gap: 15px;
  margin-bottom: 25px;
  background: var(--bg-card);
  padding: 20px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.03);
}

.filter-group input,
.filter-group select {
  width: 100%;
  padding: 10px 12px;
  font-size: 0.9rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  outline: none;
  background-color: #fff;
  transition: all 0.2s ease;
  height: 42px; /* Chiều cao đồng nhất */
}

.filter-group input:focus,
.filter-group select:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 1px var(--primary);
}

/* ============================
   4. BUTTONS
   ============================ */
button {
  cursor: pointer;
  font-family: inherit;
}

.btn-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 20px;
  height: 42px;
  font-weight: 600;
  font-size: 0.9rem;
  border-radius: 6px;
  border: 1px solid var(--primary);
  transition: all 0.2s ease;
}

.btn-action.primary {
  background-color: black;
  color: #fbfbfb;
  border: 1px solid var(--primary);
}
.btn-action.primary:hover {
  background-color: #f80303;
  transform: translateY(-1px);
}

.btn-action.secondary {
  background-color: #fff;
  color: var(--primary);
}
.btn-action.secondary:hover {
  background-color: #f0f0f0;
}

.full-width {
  width: 100%;
}

/* Button nhỏ trong bảng */
.btn-small {
  padding: 5px 10px;
  font-size: 0.75rem;
  border-radius: 4px;
  border: none;
  font-weight: 600;
  margin-right: 5px;
}
.btn-small.edit { background: #fff; border: 1px solid #ffc107; color: #b48600; }
.btn-small.edit:hover { background: #ffc107; color: #000; }

.btn-small.delete { background: #fff; border: 1px solid #dc3545; color: #dc3545; }
.btn-small.delete:hover { background: #dc3545; color: #fff; }

/* ============================
   5. TABLE
   ============================ */
.table-wrapper {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  overflow: hidden; /* Bo góc cho bảng */
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.data-table th {
  background-color: var(--primary);
  color: #fff;
  padding: 15px;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.8rem;
  letter-spacing: 0.5px;
}

.data-table td {
  padding: 16px 15px;
  border-bottom: 1px solid #f2f2f2;
  font-size: 0.9rem;
  vertical-align: top;
}

.data-table tr:hover td {
  background-color: #fafafa;
}

.data-table tr:last-child td {
  border-bottom: none;
}

/* Các thành phần trong bảng */
.description-text { font-size: 0.8em; color: var(--text-light); margin-top: 4px; }
.label { font-size: 0.75rem; color: var(--text-light); text-transform: uppercase; }
.time-info { font-size: 0.8rem; line-height: 1.4; color: #555; }
.helper-text { font-size: 0.75rem; margin-top: 2px; }
.helper-text.danger { color: var(--danger); }

/* Badges & Tags */
.tag {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 4px;
  font-weight: 700;
  font-size: 0.75rem;
}
.tag.info { background: #e0f7fa; color: #006064; border: 1px solid #b2ebf2; }
.tag.success { background: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; }

.status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 600;
}
.status-badge.active { background: #d1e7dd; color: #0f5132; }
.status-badge.inactive { background: #e2e3e5; color: #41464b; }

.empty-state { text-align: center; padding: 40px !important; color: var(--text-light); font-style: italic; }

/* ============================
   6. PAGINATION
   ============================ */
.pagination-wrapper { margin-top: 20px; display: flex; justify-content: center; }
.pagination-list { list-style: none; display: flex; gap: 5px; }

.pagination-list button {
  padding: 8px 14px;
  border: 1px solid var(--border-color);
  background: #fefcfc;
  border-radius: 4px;
  font-size: 0.9rem;
  color: var(--text-main);
  transition: all 0.2s;
}

.pagination-list button:hover { background: #f0f0f0; }
.pagination-list li.active button { background: var(--primary); color: #1c1b1b; border-color: var(--primary); }
.pagination-list li.disabled button { opacity: 0.5; pointer-events: none; }

/* ============================
   7. MODAL
   ============================ */
.modal-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(5px); /* Hiệu ứng mờ nền */
  z-index: 1000;
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background: #fff;
  width: 95%;
  max-width: 800px;
  border: 2px solid var(--primary); /* Viền đen đậm */
  box-shadow: 12px 12px 0px rgba(0,0,0,0.15); /* Shadow cứng */
  animation: slideIn 0.3s ease-out;
  display: flex;
  flex-direction: column;
  max-height: 90vh;
}

.modal-header {
  padding: 20px;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fafafa;
}

.modal-header h5 { font-size: 1.1rem; font-weight: 700; text-transform: uppercase; }
.close-btn { background: none; border: none; font-size: 1.5rem; line-height: 1; padding: 0 10px; }

.modal-body { padding: 0; overflow-y: auto; }

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  padding: 30px;
}

.form-column { display: flex; flex-direction: column; gap: 15px; }
.form-column.full { grid-column: 1 / -1; }
.form-row { display: flex; gap: 15px; }
.form-group.half { flex: 1; }

.form-group label {
  display: block;
  font-size: 0.8rem;
  font-weight: 700;
  margin-bottom: 6px;
  text-transform: uppercase;
}
.required { color: var(--danger); }

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 0.9rem;
}
.form-group input:focus, .form-group textarea:focus { border-color: var(--primary); outline: none; }
.form-group textarea { resize: vertical; min-height: 80px; }

/* Validation styles */
.error { border-color: var(--danger) !important; background-color: #fff8f8; }
.error-msg { font-size: 0.75rem; color: var(--danger); margin-top: 4px; display: block; }

.modal-footer {
  padding: 20px 30px;
  border-top: 1px solid var(--border-color);
  background: #fafafa;
  text-align: right;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

@keyframes slideIn {
  from { transform: translateY(30px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

/* ============================
   8. UTILITIES (Alert & Loading)
   ============================ */
.alert-box { padding: 15px; margin-bottom: 20px; border-radius: 6px; font-weight: 500; font-size: 0.9rem; border: 1px solid transparent; }
.alert-box.success { background-color: #d1e7dd; color: #0f5132; border-color: #badbcc; }
.alert-box.error { background-color: #f8d7da; color: #842029; border-color: #f5c2c7; }

.loading-state { padding: 40px; text-align: center; color: var(--text-light); }
.spinner {
  width: 40px; height: 40px; margin: 0 auto 10px;
  border: 4px solid #f3f3f3; border-top: 4px solid var(--primary);
  border-radius: 50%; animation: spin 1s linear infinite;
}
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

/* ============================
   9. RESPONSIVE
   ============================ */
@media (max-width: 768px) {
  .filter-section { grid-template-columns: 1fr; }
  .form-grid { grid-template-columns: 1fr; padding: 20px; gap: 20px; }
  .table-wrapper { overflow-x: auto; }
  .data-table th, .data-table td { white-space: nowrap; }
}
</style>