<template>
  <div class="reset-password-page">
    <div class="reset-container">
      <h2>Đặt lại mật khẩu</h2>
      <p>Vui lòng nhập mật khẩu mới của bạn.</p>

      <form @submit.prevent="submitReset">
        <div class="form-group">
          <label for="password">Mật khẩu mới</label>
          <input
            id="password"
            type="password"
            v-model="password"
            placeholder="Ít nhất 8 ký tự"
            required
          />
        </div>

        <div class="form-group">
          <label for="password_confirmation">Nhập lại mật khẩu</label>
          <input
            id="password_confirmation"
            type="password"
            v-model="password_confirmation"
            placeholder="Nhập lại mật khẩu"
            required
          />
        </div>

        <button type="submit" :disabled="loading">
          {{ loading ? 'Đang xử lý...' : 'Đặt lại mật khẩu' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useRoute, useRouter } from "vue-router"
import axios from "axios"

const route = useRoute()
const router = useRouter()

const password = ref("")
const password_confirmation = ref("")
const code = ref("")
const loading = ref(false)

onMounted(() => {
  // Lấy code từ query param
  code.value = route.query.code || ""
  if (!code.value) {
    alert("Liên kết đặt lại mật khẩu không hợp lệ!")
    router.push("/")
  }
})

const submitReset = async () => {
  // Client-side validation
  if (password.value.length < 8) {
    alert("Mật khẩu phải có ít nhất 8 ký tự!")
    return
  }
  if (password.value !== password_confirmation.value) {
    alert("Mật khẩu và xác nhận mật khẩu không khớp!")
    return
  }

  try {
    loading.value = true
    const response = await axios.post("/reset-password", {
      code: code.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
    })
    alert(response.data.message || "Mật khẩu đã được đặt lại thành công!")
    router.push("/login")
  } catch (err) {
    console.error(err)
    const msg = err.response?.data?.message || "Có lỗi xảy ra, vui lòng thử lại"
    alert(msg)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.reset-password-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: #fff;
  color: #000;
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
}

.reset-container {
  max-width: 400px;
  width: 100%;
  padding: 2rem;
  border: 2px solid #000;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.reset-container h2 {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.reset-container p {
  font-size: 0.9rem;
  color: #333;
  margin-bottom: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

input {
  padding: 0.5rem 0.7rem;
  border: 1px solid #000;
  border-radius: 6px;
  font-size: 0.9rem;
}

input:focus {
  outline: none;
  border-color: #000;
  box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
}

button {
  padding: 0.6rem 1rem;
  background: #000;
  color: #fff;
  text-transform: uppercase;
  font-weight: bold;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
}

button:hover {
  background: #333;
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
