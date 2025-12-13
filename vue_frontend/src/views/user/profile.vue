<template>
  <div class="profile-container">
    <div class="card">
      <div class="card-header">
        <h3>Hồ Sơ Của Tôi</h3>
        <p>Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
      </div>
      
      <div class="card-body">
        <form @submit.prevent="saveProfile">
          
          <div class="avatar-section">
            <div class="avatar-wrapper">
                <img :src="getAvatarSrc()" alt="Avatar" class="avatar-img">
                
                <div class="avatar-action">
                    <button type="button" class="btn-select-img" @click="$refs.fileInput.click()">
                        Chọn Ảnh
                    </button>
                    
                    <input 
                        type="file" 
                        ref="fileInput" 
                        style="display: none" 
                        accept="image/jpeg, image/png, image/jpg" 
                        @change="handleFileUpload"
                    >
                    <p class="file-hint">Dụng lượng file tối đa 2 MB<br>Định dạng: .JPEG, .PNG</p>
                </div>
            </div>
          </div>

          <hr class="divider">

          <div class="form-group">
            <label>Tên Đăng Nhập</label>
            <div class="input-wrapper">
              <input 
                type="text" 
                v-model="user.username" 
                disabled 
                class="form-control read-only"
              >
            </div>
          </div>

          <div class="form-group">
            <label>Họ Tên</label>
            <div class="input-wrapper">
              <input 
                type="text" 
                v-model="user.full_name" 
                class="form-control"
              >
            </div>
          </div>

          <div class="form-group">
            <label>Email</label>
            <div class="input-wrapper">
              <input 
                type="email" 
                v-model="user.email" 
                disabled 
                class="form-control read-only"
              >
              </div>
          </div>

          <div class="form-group">
            <label>Số Điện Thoại</label>
            <div class="input-wrapper with-action">
              <input 
                type="tel" 
                v-model="user.phone" 
                :disabled="!isPhoneEditable"
                class="form-control"
                maxlength="10"
                placeholder="Nhập 10 chữ số"
                @input="user.phone = user.phone.replace(/[^0-9]/g, '')"
              >
              <a 
                href="#" 
                class="action-link" 
                @click.prevent="isPhoneEditable = true"
                v-if="!isPhoneEditable"
              >
                Thay đổi
              </a>
            </div>
          </div>

          <div class="form-group">
            <label>Địa Chỉ</label>
            <div class="input-wrapper with-action">
              <input 
                type="text" 
                v-model="user.address" 
                :disabled="!isAddressEditable"
                class="form-control"
                placeholder="Nhập địa chỉ nhận hàng"
              >
              <a 
                href="#" 
                class="action-link" 
                @click.prevent="isAddressEditable = true"
                v-if="!isAddressEditable"
              >
                Thay đổi
              </a>
            </div>
          </div>

          <div class="form-footer">
            <button type="submit" class="btn-save">Lưu</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      user: {
        username: '',
        full_name: '',
        email: '',
        phone: '',
        address: '', 
        avatar: null
      },
      // State quản lý upload ảnh
      selectedFile: null,
      previewAvatar: null,
      
      // State quản lý edit
      isPhoneEditable: false,
      isAddressEditable: false,
      
      // BỎ imageBaseUrl vì ta sẽ dùng Proxy cho /uploads
    };
  },
  mounted() {
    this.fetchUserProfile();
  },
  methods: {
    // 1. Lấy thông tin User
    async fetchUserProfile() {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('/profile', {
            headers: { Authorization: `Bearer ${token}` }
        });
        
        // Backend đã giải mã phone/address ở hàm show(), ta chỉ việc hiển thị
        const userData = response.data.data;
        this.user = {
            ...userData,
            address: userData.address || ''
        };
      } catch (error) {
        console.error('Lỗi tải thông tin:', error);
      }
    },

    // 2. Helper hiển thị ảnh (Logic khớp với Controller lưu ở public/uploads)
    getAvatarSrc() {
        // Ưu tiên 1: Ảnh preview khi vừa chọn file
        if (this.previewAvatar) {
            return this.previewAvatar;
        }
        
        // Ưu tiên 2: Ảnh từ Database
        if (this.user.avatar) {
            // Trường hợp ảnh Social Login (http...)
            if (this.user.avatar.startsWith('http')) {
                return this.user.avatar;
            }

            // Trường hợp ảnh lưu local: "uploads/avatar/abc.jpg"
            // Ta thêm dấu "/" vào đầu -> "/uploads/avatar/abc.jpg"
            // Vite Proxy đã cấu hình /uploads -> http://localhost:8000/uploads
            // Nên nó sẽ tự load được ảnh.
            return this.user.avatar.startsWith('/') ? this.user.avatar : '/' + this.user.avatar;
        }
        
        // Ưu tiên 3: Ảnh mặc định
        return 'https://via.placeholder.com/150?text=Avatar'; 
    },

    // 3. Xử lý khi chọn file từ máy tính
    handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validate client size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert("File quá lớn! Vui lòng chọn ảnh dưới 2MB.");
            return;
        }

        this.selectedFile = file;
        // Tạo URL ảo để hiển thị ngay lập tức (Preview)
        this.previewAvatar = URL.createObjectURL(file);
    },

    // 4. Lưu thông tin
    async saveProfile() {
      // Validate Phone Client-side
      const phoneRegex = /^\d{10}$/;
      if (!this.user.phone || !phoneRegex.test(this.user.phone)) {
        alert('Số điện thoại không hợp lệ! Vui lòng nhập đúng 10 chữ số.');
        return;
      }

      // Tạo FormData
      let formData = new FormData();
      formData.append('full_name', this.user.full_name);
      formData.append('phone', this.user.phone);
      formData.append('address', this.user.address || '');

      // Chỉ gửi avatar nếu có chọn file mới
      if (this.selectedFile) {
          formData.append('avatar', this.selectedFile);
      }
      
      try {
        const token = localStorage.getItem('token');
        
        // Gửi POST request tới /update
        const response = await axios.post('/update', formData, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'multipart/form-data'
            }
        });

        alert(response.data.message);

        // Reset trạng thái edit
        this.isPhoneEditable = false;
        this.isAddressEditable = false;

        // Cập nhật lại UI bằng dữ liệu Backend trả về
        // Controller trả về biến $responseData (đã chứa path ảnh mới và thông tin chưa mã hóa)
        if (response.data.data) {
            this.user = response.data.data;
            
            // Reset phần upload
            this.selectedFile = null;
            this.previewAvatar = null;
        }

      } catch (error) {
        console.error(error);
        if (error.response && error.response.data.errors) {
          // Lấy lỗi đầu tiên để hiển thị cho gọn
          const firstErrorKey = Object.keys(error.response.data.errors)[0];
          alert(error.response.data.errors[firstErrorKey][0]);
        } else {
          alert('Có lỗi xảy ra, vui lòng thử lại.');
        }
      }
    }
  }
};
</script>

<style scoped>
/* IMPORT FONT (Tuỳ chọn: Nếu muốn font đẹp hơn, có thể dùng Inter hoặc Roboto) */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap');

/* --- LAYOUT CHUNG --- */
.profile-container {
    max-width: 900px;
    margin: 120px auto 50px; /* margin-top 120px để né header, tạo khoảng trắng nghệ thuật */
    font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif;
    color: #000;
}

/* --- CARD STYLE (KHUNG BAO) --- */
.card {
    background: #fff;
    border: 1px solid #000; /* Viền đen mảnh, sắc nét */
    border-radius: 0; /* Vuông vức, không bo góc */
    box-shadow: none; /* Bỏ đổ bóng để phẳng hoàn toàn */
}

.card-header {
    padding: 40px 40px 20px;
    border-bottom: 1px solid #000;
}

.card-header h3 {
    font-size: 24px;
    font-weight: 600;
    text-transform: uppercase; /* Chữ in hoa hiện đại */
    letter-spacing: 1px;
    margin: 0 0 5px 0;
}

.card-header p {
    font-size: 13px;
    color: #666;
    margin: 0;
    font-weight: 300;
}

.card-body {
    padding: 40px;
}

/* --- AVATAR SECTION --- */
.avatar-section {
    display: flex;
    justify-content: flex-start; /* Căn trái thay vì giữa để trông art hơn */
    margin-bottom: 40px;
}

.avatar-wrapper {
    display: flex;
    align-items: center;
    gap: 30px;
}

.avatar-img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border: 1px solid #000; /* Viền ảnh đen */
    border-radius: 0; /* Ảnh vuông */
    padding: 5px; /* Khoảng cách giữa ảnh và viền */
    background: #fff;
}

.avatar-action {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.btn-select-img {
    background: #fff;
    color: #000;
    border: 1px solid #000;
    padding: 8px 20px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all 0.3s ease;
    border-radius: 0;
}

.btn-select-img:hover {
    background: #000;
    color: #fff;
}

.file-hint {
    margin-top: 10px;
    font-size: 11px;
    color: #888;
    line-height: 1.4;
}

/* --- DIVIDER --- */
.divider {
    border: 0;
    border-top: 1px solid #eee;
    margin: 30px 0;
}

/* --- FORM ELEMENTS --- */
.form-group {
    display: grid;
    grid-template-columns: 200px 1fr; /* Chia cột: Label bên trái, Input bên phải */
    align-items: center;
    margin-bottom: 25px;
}

.form-group label {
    font-size: 13px;
    font-weight: 600;
    color: #000;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.input-wrapper {
    position: relative;
    width: 100%;
    display: flex;
    align-items: center;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 0; /* Input vuông */
    background: #fff;
    color: #000;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #000; /* Focus màu đen */
}

.form-control.read-only {
    background-color: #f8f8f8; /* Xám rất nhạt */
    color: #555;
    border-color: #eee;
    cursor: default;
}

/* --- LINKS & BUTTONS --- */
.with-action {
    gap: 15px;
}

.action-link {
    font-size: 12px;
    color: #000;
    text-decoration: underline;
    text-underline-offset: 3px;
    white-space: nowrap;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.action-link:hover {
    background: #000;
    color: #fff;
    text-decoration: none;
    padding: 2px 5px;
}

.form-footer {
    margin-top: 50px;
    display: flex;
    justify-content: flex-end;
    border-top: 1px solid #000; /* Đường gạch ngang trên nút lưu */
    padding-top: 20px;
}

.btn-save {
    background: #000;
    color: #fff;
    border: 1px solid #000;
    padding: 12px 40px;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    border-radius: 0;
    transition: all 0.3s ease;
}

.btn-save:hover {
    background: #fff;
    color: #000;
}

.btn-save:disabled {
    background: #ccc;
    border-color: #ccc;
    cursor: not-allowed;
}

/* Responsive cho màn hình nhỏ */
@media (max-width: 768px) {
    .profile-container {
        margin-top: 80px;
        padding: 0 15px;
    }
    
    .form-group {
        grid-template-columns: 1fr; /* Label lên trên */
        gap: 8px;
    }

    .avatar-wrapper {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>