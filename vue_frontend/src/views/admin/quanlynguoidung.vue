<template>
  <div>
    <h2>Quản lý người dùng</h2>

    <!-- Modal Edit User -->
    <div v-if="selectedUser">
      <h3>Edit User</h3>
      <form @submit.prevent="saveUser">
        <div>
          <label>Avatar</label>
          <img
            v-if="previewAvatar"
            :src="previewAvatar"
            style="width: 70px; height: 40px; border-radius: 6px; object-fit: cover;"
            ><br>
          <input type="file" @change="onFileChange"/>
        </div>
        <div>
          <label>Username</label>
          <input type="text" v-model="selectedUser.username" disabled/>
        </div>
        <div>
          <label>Email</label>
          <input type="email" v-model="selectedUser.email" disabled/>
        </div>
        <div>
          <label>Full Name</label>
          <input type="text" v-model="selectedUser.full_name"/>
        </div>
        <div>
          <label>Phone</label>
          <input type="text" v-model="selectedUser.phone"/>
        </div>
        <div>
          <label>Address</label>
          <input type="text" v-model="selectedUser.address"/>
        </div>
        <template v-if="selectedUser.role === 'user'">
        <div>
          <p>Tổng đơn hàng: {{ stats.total_orders }}</p>
          <p>Tổng giá trị đơn hàng: {{ Math.round(stats.total_order_value) }} VNĐ</p>
          <p>Số đánh giá: {{ stats.total_reviews }}</p>
        </div>
        </template>
        <div class="flex gap-2">
          <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded">Lưu thay đổi</button>
          <button type="button" @click="cancelEdit" class="bg-gray-400 text-white px-3 py-1 rounded">Hủy</button>
        </div>
      </form>
    </div>


    <!-- Create Admin Modal -->
    <div v-if="createModal">
      <h3>Tạo Admin mới</h3>
      <form @submit.prevent="createAdmin">
        <div>
          <label>Avatar</label>
          <img
            v-if="newPreviewAvatar"
            :src="newPreviewAvatar"
            style="width: 70px; height: 40px; border-radius: 6px; object-fit: cover;"
            ><br>
          <input type="file" @change="onNewFileChange"/>
        </div>
        <div>
          <label>Username</label>
          <input type="text" v-model="newAdmin.username" required/>
        </div>
        <div>
          <label>Email</label>
          <input type="email" v-model="newAdmin.email" required/>
        </div>
        <div>
          <label>Full Name</label>
          <input type="text" v-model="newAdmin.full_name" required/>
        </div>
        <div>
          <label>Phone</label>
          <input type="text" v-model="newAdmin.phone" placeholder="Số điện thoại"/>
        </div>
        <div>
          <label>Address</label>
          <input type="text" v-model="newAdmin.address" placeholder="Địa chỉ"/>
        </div>
        <div>
          <label>Password</label>
          <input type="password" v-model="newAdmin.password" required/>
        </div>
        <div>
          <label>Re-Password</label>
          <input type="password" v-model="newAdmin.password_confirmation" required/>
        </div>

        <div>
            <label>Password admin đang login</label>
            <input type="password" v-model="newAdmin.current_admin_password" />
        </div>

        <button type="submit">Tạo</button>
        <button type="button" @click="closeCreateModal">Hủy</button>
      </form>
    </div>
  </div>


    <!-- Filter & Search -->
    <div class="flex gap-2 mb-4 items-center">
      <button v-if="isSuperAdmin" @click="toggleShowDeleted"
        :class="showDeleted ? 'bg-gray-400' : 'bg-blue-600'"
        class="text-white px-3 py-1 rounded">
        {{ showDeleted ? 'Ẩn tài khoản đã xóa' : 'Xem tài khoản đã xóa' }}
      </button>
      <button v-if="isSuperAdmin" @click="openCreateModal" class="bg-blue-600 text-white px-3 py-1 rounded">Tạo Admin mới</button>
      <input v-model="search" placeholder="Tìm kiếm theo username..." @keyup.enter="performSearch" class="px-2 py-1 border rounded"/>
      <select v-model="filterRole" class="px-2 py-1 border rounded">
        <option value="">Tất cả vai trò</option>
        <option value="admin">Admin</option>
        <option value="user">User</option>
      </select>
      <button @click="performSearch" class="bg-green-600 text-white px-3 py-1 rounded">Tìm kiếm</button>
    </div>

    <!-- Table -->
    <table class="table-auto w-full border">
      <thead>
        <tr>
              <th>ID</th>
              <th style="width:48px;min-width:48px">Avatar</th>
              <th>Username</th>
              <th>Email</th>
              <th>Họ tên</th>
              <th>Role</th>
              <th>Ngày tạo</th>
              <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in showDeleted ? deletedUsers : users.data" :key="user.id">
          <td>{{ user.id }}</td>
              <td class="px-2" style="width:48px;min-width:48px">
                <img v-if="user.avatar" :src="avatarPath(user.avatar)" style="width:32px;height:32px;border-radius:9999px;object-fit:cover;display:block"/>
              </td>
          <td>{{ user.username }}</td>
          <td>{{ user.email }}</td>
          <td>{{ user.full_name }}</td>
          <td>{{ user.role }}</td>
          <td>{{ user.created_at }}</td>
          <td>
            <template v-if="showDeleted">
              <button @click="restoreUser(user.id)" class="bg-green-600 text-white px-2 py-1 rounded">Khôi phục</button>
            </template>
            <template v-else-if="user.email !== 'admin@example.com'">
              <!-- show edit for normal users; for admin rows only show if current is super-admin -->
              <button v-if="user.role !== 'admin' || isSuperAdmin" @click="viewUser(user.id)">Xem chi tiết</button>
              <button v-if="user.role === 'admin' && isSuperAdmin" @click="deleteUser(user.id)" class="text-red-600">Xóa</button>
            </template>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination (numeric) -->
    <div class="mt-4 flex gap-2 items-center">
      <button @click="prevPage" :disabled="currentPage === 1"><</button>
      <template v-for="p in pages()" :key="p">
        <button @click="goToPage(p)" :class="{ 'font-bold underline': currentPage === p }">{{ p }}</button>
      </template>
      <button @click="nextPage" :disabled="currentPage === (users.last_page || 1)">></button>
    </div>
</template>

<script>
import axios from 'axios'
import { ref, onMounted, onBeforeUnmount } from 'vue'

export default {
  setup() {
    const users = ref({data: []})
    const search = ref('')
    const filterRole = ref('')
    const filterStatus = ref('')
    const selectedUser = ref(null)
    const stats = ref({})
    const previewAvatar = ref(null)
    let avatarFile = null
    let newAvatarFile = null
    let currentPage = ref(1)
    const createModal = ref(false)
    const newAdmin = ref({ username: '', email: '', password: '', password_confirmation: '', full_name: '', phone: '', address: '', role: 'admin' })
    const newPreviewAvatar = ref(null)
    const isSuperAdmin = ref(false)

    const deletedUsers = ref([])   // danh sách user đã xóa
    const showDeleted = ref(false) // toggle giữa active / deleted
    const fetchUsers = async () => {
      const res = await axios.get('/admin/users', {
        params: { 
          search: search.value,
          role: filterRole.value,
          status: filterStatus.value,
          page: currentPage.value
        },
        headers: {
          Authorization: token ? `Bearer ${token}` : undefined
        }
      })
      users.value = res.data
    }
    const performSearch = () => {
      // when user triggers a search, reset to first page
      currentPage.value = 1
      fetchUsers()
    }

    const fetchCurrentUser = async () => {
      try {
        let res = null
        try {
          res = await axios.get('/me', { withCredentials: true })
        } catch (err) {
          const token = localStorage.getItem('token')
          if (token) {
            res = await axios.get('/me', { headers: { Authorization: `Bearer ${token}` } })
          } else {
            throw err
          }
        }
        isSuperAdmin.value = res.data?.email === 'admin@example.com'
      } catch (err) {
        isSuperAdmin.value = false
      }
    }

    const backendOrigin = import.meta.env.VITE_BACKEND_URL || window.location.origin;
    const avatarPath = (path) => {
      if (!path) return null
      const p = String(path)
      // already an absolute URL
      if (p.startsWith('http')) return p

      // normalized absolute path starting with /
      if (p.startsWith('/')) {
        // if it's a public uploads or storage path, load from backend origin
        if (p.startsWith('/uploads/') || p.startsWith('/storage/')) {
          return backendOrigin + p
        }
        return p
      }

      // handle values like 'public/uploads/..', 'uploads/..', or storage disk paths
      if (p.startsWith('public/uploads/')) {
        return backendOrigin + '/' + p.replace(/^public\//, '')
      }
      if (p.startsWith('uploads/')) return backendOrigin + '/' + p
      if (p.startsWith('storage/')) return backendOrigin + '/' + p

      // fallback assume storage disk path
      return backendOrigin + '/storage/' + p
    }
    
    const token = localStorage.getItem('token')

    const viewUser = async (id) => {
      const res = await axios.get(`/admin/users/${id}`,{
        headers: {
          Authorization: token ? `Bearer ${token}` : undefined
        }})
      selectedUser.value = res.data.user
      stats.value = res.data.stats
      previewAvatar.value = avatarPath(selectedUser.value.avatar)
    }

    const onFileChange = (e) => {
      avatarFile = e.target.files[0]
      previewAvatar.value = URL.createObjectURL(avatarFile)
    }

    const onNewFileChange = (e) => {
      newAvatarFile = e.target.files[0]
      newPreviewAvatar.value = URL.createObjectURL(newAvatarFile)
    }

    const saveUser = async () => {
      // ---- VALIDATE ----
      if (!selectedUser.value.full_name?.trim()) {
        return alert("Full Name không được để trống");
      }
      if (!selectedUser.value.phone?.trim()) {
        return alert("Phone không được để trống");
      }
      if (!/^[0-9]{10}$/.test(selectedUser.value.phone)) {
        return alert("Phone phải gồm đúng 10 chữ số");
      }
      if (!selectedUser.value.address?.trim()) {
        return alert("Address không được để trống");
      }
      const formData = new FormData()
      // Do not append the avatar URL string (Laravel expects a file for 'avatar' when validating 'image').
      for (const key in selectedUser.value) {
        if (key === 'avatar') continue
        formData.append(key, selectedUser.value[key])
      }
      if (avatarFile) {
        formData.append('avatar', avatarFile)
      }
      try {
        await axios.post(`/admin/users/${selectedUser.value.id}?_method=PUT`, formData)
        fetchUsers()
        alert('Cập nhật thành công!')
        // reload page after user dismisses the alert so UI reflects changes
        window.location.reload()
      } catch (err) {
        console.error(err)
        const data = err.response?.data
        if (data?.errors) {
          const msgs = Object.values(data.errors).flat().join('\n')
          alert(msgs)
        } else {
          alert(data?.message || 'Lỗi khi cập nhật user')
        }
      }
    }

      fetchUsers()

      const changeStatus = async (id) => {
        const newStatus = prompt('Nhập trạng thái mới: active, inactive, banned')
        if (!newStatus) return
        await axios.post(`/admin/users/${id}/status`, { status: newStatus, _method: 'PATCH' })
        fetchUsers()
      }

    const viewOrders = async (id) => {
      const res = await axios.get(`/admin/users/${id}/orders`)
      console.log('Orders', res.data)
      alert(`User có ${res.data.length} đơn hàng. Xem console để chi tiết.`)
    }

    const openCreateModal = () => {
      createModal.value = true
    }

    const closeCreateModal = () => {
      createModal.value = false
      newAdmin.value = { username: '', email: '', password: '', password_confirmation: '', full_name: '', phone: '', address: '', role: 'admin' }
      newPreviewAvatar.value = null
      newAvatarFile = null
    }

    const createAdmin = async () => {
          
      // ================= VALIDATION =================
      if (!newAdmin.value.username?.trim()) {
        return alert("Username không được để trống");
      }

      if (!newAdmin.value.email?.trim()) {
        return alert("Email không được để trống");
      }
      const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
      if (!emailRegex.test(newAdmin.value.email)) {
        return alert("Email không hợp lệ!");
      }

      if (!newAdmin.value.full_name?.trim()) {
        return alert("Full name không được để trống");
      }

      if (!newAdmin.value.phone?.trim()) {
        return alert("Số điện thoại không được để trống");
      }

      if (!/^[0-9]{10}$/.test(newAdmin.value.phone)) {
        return alert("Số điện thoại phải gồm đúng 10 chữ số");
      }

      if (!newAdmin.value.address?.trim()) {
        return alert("Địa chỉ không được để trống");
      }

      if (!newAdmin.value.password?.trim()) {
        return alert("Password không được để trống");
      }

      if (newAdmin.value.password.length < 8) {
        return alert("Mật khẩu phải có ít nhất 8 ký tự");
      }

      if (!newAdmin.value.password_confirmation?.trim()) {
        return alert("Vui lòng nhập lại mật khẩu");
      }

      if (newAdmin.value.password !== newAdmin.value.password_confirmation) {
        return alert("Mật khẩu và nhập lại mật khẩu không khớp");
      }

      if (!newAdmin.value.current_admin_password?.trim()) {
        return alert("Bạn phải nhập password admin đang login để xác nhận");
      }

      const formData = new FormData()
      for (const key in newAdmin.value) {
        formData.append(key, newAdmin.value[key])
      }
      if (newAvatarFile) {
        formData.append('avatar', newAvatarFile)
      }
      try {
        await axios.post('/admin/users', formData)
        fetchUsers()
        closeCreateModal()
        alert('Admin mới đã được tạo và email đã gửi!');
      } catch (err) {
        console.error(err)
        const data = err.response?.data
        if (data?.errors) {
          const msgs = Object.values(data.errors).flat().join('\n')
          alert(msgs)
        } else {
          alert(data?.message || 'Lỗi khi tạo admin')
        }
      }
    }


    const fetchDeletedUsers = async () => {
      const res = await axios.get('/admin/users/deleted', { withCredentials: true })
      deletedUsers.value = res.data
    }

    const toggleShowDeleted = () => {
      showDeleted.value = !showDeleted.value
      if (showDeleted.value) fetchDeletedUsers()
      else fetchUsers()
    }

    const restoreUser = async (id) => {
      if (!confirm('Bạn có chắc muốn khôi phục user này?')) return
      try {
        await axios.post(`/admin/users/${id}/restore`, {_method: 'PATCH'});
        alert('Đã khôi phục user')
        fetchDeletedUsers()
        fetchUsers()
      } catch (err) {
        console.error(err)
        alert(err.response?.data?.message || 'Lỗi khi khôi phục user')
      }
    }


    const deleteUser = async (id) => {
      if (!confirm('Bạn có chắc muốn xóa user này?')) return
      try {
        await axios.post(`/admin/users/${id}`, {_method: 'DELETE'});
        fetchUsers()
        alert('Đã xóa user')
      } catch (err) {
        console.error(err)
        alert(err.response?.data?.message || 'Lỗi khi xóa user')
      }
    }

    const cancelEdit = () => {
      selectedUser.value = null
      previewAvatar.value = null
      avatarFile = null
      stats.value = {}
    }

    const prevPage = () => {
      if (currentPage.value > 1) {
        currentPage.value--
        fetchUsers()
      }
    }

    const nextPage = () => {
      const last = users.value.last_page || 1
      if (currentPage.value < last) {
        currentPage.value++
        fetchUsers()
      }
    }

    const pages = () => {
      const last = users.value.last_page || 1
      return Array.from({ length: last }, (_, i) => i + 1)
    }

    const goToPage = (p) => {
      if (p === currentPage.value) return
      currentPage.value = p
      fetchUsers()
    }

    fetchUsers()
    fetchCurrentUser()

    // listen for auth changes (login/logout) to refresh isSuperAdmin
    const onAuthChanged = () => fetchCurrentUser()
    window.addEventListener('auth-changed', onAuthChanged)
    window.__onAuthChangedUsers = onAuthChanged

    onBeforeUnmount(() => {
      const handler = window.__onAuthChangedUsers
      if (handler) window.removeEventListener('auth-changed', handler)
    })

    return { users, search, filterRole, filterStatus, selectedUser, stats, previewAvatar, onFileChange, onNewFileChange, saveUser, viewUser, changeStatus, viewOrders, prevPage, nextPage, createModal, newAdmin, newPreviewAvatar, openCreateModal, closeCreateModal, createAdmin, deleteUser, cancelEdit, pages, goToPage, currentPage, isSuperAdmin, performSearch, avatarPath,deletedUsers, showDeleted, toggleShowDeleted, restoreUser }
  }
}
</script>

<style scoped>
/* =========================================
   GLOBAL & RESET (Modern Monochrome Theme)
   ========================================= */
:root {
  --mono-bg: #ffffff;
  --mono-text: #111111;
  --mono-gray-light: #f4f4f5;
  --mono-border: #3f3f42;
  --mono-accent: #000000;
}

/* Áp dụng font hiện đại cho toàn bộ container */
div {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  color: var(--mono-text);
  box-sizing: border-box;
}

/* Tiêu đề chính */
h2 {
  font-size: 2rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  margin-bottom: 2rem;
  border-bottom: 2px solid var(--mono-text);
  padding-bottom: 1rem;
  display: inline-block;
}

/* =========================================
   MODAL STYLING (Biến div thường thành Modal xịn)
   ========================================= */

/* Chiến thuật: Tìm các thẻ div chứa thẻ form.
   Đây là các Modal Edit/Create. Chúng ta sẽ đưa nó lên lớp phủ (Overlay).
*/
div:has(> form) {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(255, 255, 255, 0.85); /* Nền trắng mờ */
  backdrop-filter: blur(8px); /* Hiệu ứng kính mờ hiện đại */
  z-index: 9999;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  animation: fadeIn 0.2s ease-out;
}

/* Tiêu đề trong Modal */
div:has(> form) h3 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  background: var(--mono-text);
  color: white;
  padding: 0.5rem 1.5rem;
  transform: skew(-10deg); /* Tạo điểm nhấn hình học */
}

/* Form Container (Giả lập thẻ Card) */
div:has(> form) form {
  background: white;
  padding: 2.5rem;
  border: 1px solid var(--mono-text);
  box-shadow: 10px 10px 0px rgba(0,0,0,0.1); /* Bóng cứng phong cách Brutalism */
  width: 100%;
  max-width: 500px;
  max-height: 85vh;
  overflow-y: auto;
}

/* Input file trong form */
input[type="file"] {
  font-size: 0.875rem;
  padding: 0.5rem 0;
}

/* Các div bao quanh input */
form > div {
  margin-bottom: 1.25rem;
}

/* Label */
form label {
  display: block;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.5rem;
  color: #555;
}

/* Input fields */
form input[type="text"],
form input[type="email"],
form input[type="password"],
input.border /* Toolbar search input */,
select.border {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ccc;
  border-radius: 0; /* Vuông vức hiện đại */
  background: var(--mono-gray-light);
  transition: all 0.2s;
  outline: none;
}

form input:focus,
input.border:focus,
select.border:focus {
  background: white;
  border-color: var(--mono-text);
  box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
}

/* =========================================
   BUTTON OVERRIDES (Ghi đè class Tailwind)
   ========================================= */

/* Button chung */
button {
  font-weight: 600;
  font-size: 0.8rem;
  padding: 0.6rem 1.2rem !important; /* Ghi đè padding Tailwind */
  border-radius: 4px !important;
  cursor: pointer;
  transition: transform 0.1s, box-shadow 0.1s;
  border: 1px solid transparent;
}

button:active {
  transform: translateY(1px);
}

/* Nút Primary (Lưu, Tạo, Tìm kiếm) - Biến màu xanh/lá thành Đen */
button[class*="bg-blue-600"],
button[class*="bg-green-600"] {
  background-color: black !important;
  color: rgb(255, 254, 254) !important;
  border: 1px solid var(--mono-text) !important;
}

button[class*="bg-blue-600"]:hover,
button[class*="bg-green-600"]:hover {
  background-color: #ee1919 !important;
}

/* Nút Secondary (Hủy)*/
button[class*="bg-gray-400"] {
  background-color: white !important;
  color: var(--mono-text) !important;
  border: 1px solid #ccc !important;
}

button[class*="bg-gray-400"]:hover {
  background-color:  #ee1919  !important;
}

/* Nút Delete (Đỏ) */
button.text-red-600 {
  color: #000 !important; /* Chuyển text đỏ thành đen */
  text-decoration: line-through; /* Gạch ngang để biểu thị xóa */
  opacity: 0.6;
}
button.text-red-600:hover {
  opacity: 1;
  text-decoration: none;
  background-color: #ffecec;
}

/* =========================================
   TOOLBAR & TABLE
   ========================================= */

/* Vùng filter/search */
.flex.gap-2.mb-4 {
  background: var(--mono-gray-light);
  padding: 1rem;
  border: 1px solid var(--mono-border);
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

/* Table Styling */
table.table-auto {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
  border: 1px solid #e5e5e5 !important;
}

table thead tr {
  border-bottom: 2px solid var(--mono-text);
}

table th {
  border: 1px solid #e5e5e5;
  text-align: left;
  padding: 1rem;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 700;
}

table tbody tr {
  border-bottom: 1px solid var(--mono-border);
  transition: background 0.2s;
}

table tbody tr:hover {
  background-color: var(--mono-gray-light);
}

table td {
  border: 1px solid #e5e5e5;
  padding: 1rem;
  font-size: 0.9rem;
  vertical-align: middle;
}

/* Ảnh đại diện tròn trong bảng */
table td img {
  filter: grayscale(0%); /* Chuyển ảnh thành đen trắng */
  transition: filter 0.3s;
}

/* =========================================
   PAGINATION
   ========================================= */

/* Container của phân trang */
.mt-4.flex.gap-2 {
  /* Cấu hình Flexbox để sửa lỗi dọc và căn giữa */
  display: flex !important;           /* Bắt buộc dùng Flexbox */
  flex-direction: row !important;     /* Bắt buộc xếp ngang */
  justify-content: center !important; /* Căn chính giữa */
  align-items: center !important;     /* Căn giữa theo chiều dọc */
  flex-wrap: wrap !important;         /* Cho phép xuống dòng nếu màn hình quá nhỏ */
  
  /* Khoảng cách và viền */
  gap: 0.5rem !important;             /* Khoảng cách giữa các nút */
  padding-top: 1.5rem;
  margin-top: 1.5rem;
  border-top: 1px solid var(--mono-border);
  width: 100%;                        /* Chiếm hết chiều rộng để căn giữa chính xác */
}

/* Style chung cho các nút phân trang */
.mt-4.flex.gap-2 button {
  background: white;
  border: 1px solid #ddd;
  color: var(--mono-text);
  min-width: 36px;
  height: 36px;
  
  /* Flexbox nội bộ để số trang nằm giữa nút */
  display: flex !important;
  align-items: center;
  justify-content: center;
  
  padding: 0 !important;
  border-radius: 4px; /* Bo góc nhẹ cho hiện đại */
  transition: all 0.2s;
}

/* Hiệu ứng Hover cho nút thường */
.mt-4.flex.gap-2 button:hover:not(:disabled) {
  border-color: var(--mono-text);
  background-color: #f9f9f9;
}

/* Trang đang active (Nền đen - Chữ trắng) */
.mt-4.flex.gap-2 button.font-bold.underline {
  background: var(--mono-text) !important;
  color: rgb(61, 56, 56) !important;
  border-color: var(--mono-text) !important;
  text-decoration: none !important; /* Bỏ gạch chân */
  font-weight: bold;
  cursor: default;
}

/* Nút Prev/Next khi bị Disable */
button:disabled {
  opacity: 0.3;
  cursor: not-allowed;
  background: #f5f5f5 !important;
  border-color: #eee !important;
}

</style>