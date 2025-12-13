<template>
  <div class="forgot-wrapper">
    <div class="forgot-box">
      <h2 class="title">Quên mật khẩu</h2>
      <p class="subtitle">
        Nhập email của bạn để nhận liên kết đặt lại mật khẩu.
      </p>

      <form @submit.prevent="submitEmail" class="form">
        <div class="form-group">
          <label>Email</label>
          <input 
            type="email" 
            v-model="email" 
            class="input"
            placeholder="example@gmail.com"
          />
        </div>

        <button type="submit" class="btn-submit">Gửi yêu cầu</button>

        <router-link to="/login" class="back-link">
          ← Quay lại đăng nhập
        </router-link>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue"
import axios from "axios"

const email = ref("")
const loading = ref(false)

async function submitEmail() {
  if (!email.value.trim()) {
    alert("Vui lòng nhập email!")
    return
  }

  const emailRegex =/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!emailRegex.test(email.value)) {
    alert("Email không hợp lệ!")
    return
  }

  try {
    loading.value = true
    const response = await axios.post("/forgot-password", {
      email: email.value
    })
    alert(response.data.message || "Yêu cầu đặt lại mật khẩu đã được gửi!")
    email.value = ""
  } catch (error) {
    console.error(error)
    const msg = error.response?.data?.message || "Có lỗi xảy ra, vui lòng thử lại"
    alert(msg)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.forgot-wrapper {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #ffffff;
  padding: 40px;
}

.forgot-box {
  width: 380px;
  padding: 40px;
  background: #fff;
  border: 2px solid #000;
  box-shadow: 8px 8px 0 #000;
  text-align: center;
}

.title {
  font-size: 26px;
  font-weight: 700;
  text-transform: uppercase;
  margin-bottom: 10px;
  color: #000;
  letter-spacing: 1px;
}

.subtitle {
  color: #333;
  margin-bottom: 25px;
  font-size: 14px;
}

.form-group {
  text-align: left;
  margin-bottom: 20px;
}

label {
  font-size: 14px;
  font-weight: 600;
  color: #000;
}

.input {
  width: 100%;
  margin-top: 4px;
  padding: 12px;
  border: 2px solid #000;
  background: #fff;
  color: #000;
  font-size: 14px;
  outline: none;
  transition: 0.2s;
}

.input:focus {
  border-color: #000;
  background: #f5f5f5;
}

.btn-submit {
  width: 100%;
  padding: 12px;
  background: #000;
  color: #fff;
  font-size: 14px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  text-transform: uppercase;
  margin-top: 10px;
  transition: 0.2s;
}

.btn-submit:hover {
  background: #222;
}

.back-link {
  display: inline-block;
  margin-top: 18px;
  font-size: 13px;
  color: #000;
  text-decoration: none;
  border-bottom: 1px solid transparent;
  transition: 0.2s;
}

.back-link:hover {
  border-bottom: 1px solid #000;
}
</style>
