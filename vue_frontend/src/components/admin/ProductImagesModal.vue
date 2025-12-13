<template>
  <div class="overlay" v-if="show">
    <div class="modal">
      <div class="modal-header">
        <h2>Ảnh sản phẩm – {{ productName }}</h2>
        <button class="btn-close" @click="$emit('close')">×</button>
      </div>

      <div class="modal-body">
        <!-- Upload -->
        <form @submit.prevent="uploadImage" class="upload-row">
          <input type="file" @change="onFileChange" accept="image/*" />
          <label class="checkbox">
            <input type="checkbox" v-model="isPrimaryUpload" />
            Đặt làm ảnh chính
          </label>
          <button type="submit" class="btn primary" :disabled="!file || uploading">
            {{ uploading ? 'Đang tải...' : 'Upload ảnh' }}
          </button>
        </form>

        <!-- Danh sách ảnh -->
        <div v-if="loading" class="text-muted" style="margin-top: 10px">
          Đang tải danh sách ảnh...
        </div>

        <div v-else class="images-grid">
          <div
            v-for="img in images"
            :key="img.id"
            class="image-item"
            :class="{ primary: img.is_primary }"
          >
            <img :src="img.url" alt="" />

            <div class="image-actions">
              <span v-if="img.is_primary" class="badge-primary">Ảnh chính</span>
              <button
                v-else
                type="button"
                class="btn small"
                @click="setPrimary(img.id)"
              >
                Đặt ảnh chính
              </button>
              <button
                type="button"
                class="btn small danger"
                @click="remove(img.id)"
              >
                Xóa
              </button>
            </div>
          </div>

          <div v-if="!images.length && !loading" class="text-muted">
            Chưa có ảnh nào cho sản phẩm này.
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn ghost" @click="$emit('close')">Đóng</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: { type: Boolean, default: false },
  productId: { type: Number, required: true },
  productName: { type: String, default: '' },
})

const emit = defineEmits(['close', 'changed'])

const images = ref([])
const loading = ref(false)

const file = ref(null)
const isPrimaryUpload = ref(false)
const uploading = ref(false)

const fetchImages = async () => {
  if (!props.productId) return
  loading.value = true
  try {
    const res = await axios.get(`/admin/products/${props.productId}/images`)
    images.value = res.data
  } catch (e) {
    console.error('Lỗi load ảnh', e)
  } finally {
    loading.value = false
  }
}

const onFileChange = (e) => {
  file.value = e.target.files[0] || null
}

const uploadImage = async () => {
  if (!file.value) return

  const formData = new FormData()
  formData.append('image', file.value)
  formData.append('is_primary', isPrimaryUpload.value ? '1' : '0')

  uploading.value = true
  try {
    await axios.post(`/admin/products/${props.productId}/images`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    file.value = null
    isPrimaryUpload.value = false

    await fetchImages()
    emit('changed')
  } catch (e) {
    console.error('Upload ảnh thất bại', e.response?.data || e)

    // Lấy message dễ hiểu hơn từ Laravel
    const firstError =
      e.response?.data?.errors &&
      Object.values(e.response.data.errors)[0]?.[0]

    alert(firstError || e.response?.data?.message || 'Upload ảnh thất bại')
  } finally {
    uploading.value = false
  }
}

const remove = async (id) => {
  if (!confirm('Xóa ảnh này?')) return
  try {
    // Thay đổi từ axios.delete sang axios.post
    await axios.post(`/admin/products/images/${id}`, {
        _method: 'DELETE' // Báo cho server Laravel biết đây là lệnh DELETE
    });
    await fetchImages()
    emit('changed')
  } catch (e) {
    console.error('Xóa ảnh thất bại', e)
    alert('Xóa ảnh thất bại')
  }
}

const setPrimary = async (id) => {
  try {
    await axios.post(`/admin/products/images/${id}/primary`, {
      _method: 'PATCH'
    })
    await fetchImages()
    emit('changed')
  } catch (e) {
    console.error('Đặt ảnh chính thất bại', e)
    alert('Đặt ảnh chính thất bại')
  }
}

watch(
  () => props.show,
  (val) => {
    if (val) fetchImages()
  }
)

onMounted(() => {
  if (props.show) fetchImages()
})
</script>

<style scoped>
/* ============================================
   IMAGE MODAL - LIGHT MINIMAL STYLE
   ============================================ */

/* ============== OVERLAY ============== */
.overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

/* ============== MODAL ============== */
.modal {
  background: #ffffff;
  width: 100%;
  max-width: 1000px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  border: 1px solid #e5e5e5;
}

/* ============== MODAL HEADER ============== */
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 24px 32px;
  border-bottom: 2px solid #e5e5e5;
  background: #fafafa;
}

.modal-header h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  color: #000;
  letter-spacing: -0.5px;
}

.btn-close {
  background: transparent;
  border: 1px solid #d0d0d0;
  color: #666;
  width: 36px;
  height: 36px;
  font-size: 28px;
  line-height: 1;
  cursor: pointer;
  transition: all 0.2s ease;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-close:hover {
  background: #f5f5f5;
  border-color: #000;
  color: #000;
}

/* ============== MODAL BODY ============== */
.modal-body {
  padding: 32px;
  overflow-y: auto;
  flex: 1;
}

/* ============== UPLOAD ROW ============== */
.upload-row {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  background: #fafafa;
  border: 1px solid #e5e5e5;
  margin-bottom: 32px;
  flex-wrap: wrap;
}

.upload-row input[type="file"] {
  flex: 1;
  min-width: 200px;
  padding: 10px 16px;
  background: #ffffff;
  border: 1px solid #d0d0d0;
  color: #1a1a1a;
  font-size: 14px;
  cursor: pointer;
  font-family: inherit;
}

.upload-row input[type="file"]::-webkit-file-upload-button {
  background: #f5f5f5;
  border: 1px solid #d0d0d0;
  color: #1a1a1a;
  padding: 8px 16px;
  cursor: pointer;
  margin-right: 12px;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.upload-row input[type="file"]::-webkit-file-upload-button:hover {
  background: #e5e5e5;
  border-color: #a0a0a0;
}

/* ============== CHECKBOX ============== */
.checkbox {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #1a1a1a;
  cursor: pointer;
  user-select: none;
  white-space: nowrap;
}

.checkbox input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #000;
}

/* ============== IMAGES GRID ============== */
.images-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}

.image-item {
  background: #fafafa;
  border: 2px solid #e5e5e5;
  overflow: hidden;
  transition: all 0.2s ease;
}

.image-item:hover {
  border-color: #d0d0d0;
}

.image-item.primary {
  border-color: #000;
  border-width: 3px;
}

.image-item img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  display: block;
  background: #ffffff;
}

/* ============== IMAGE ACTIONS ============== */
.image-actions {
  padding: 12px;
  background: #ffffff;
  border-top: 1px solid #e5e5e5;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.badge-primary {
  display: inline-block;
  padding: 6px 12px;
  background: #000;
  color: #fff;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  text-align: center;
}

/* ============== MODAL FOOTER ============== */
.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  padding: 24px 32px;
  border-top: 2px solid #e5e5e5;
  background: #fafafa;
}

/* ============== BUTTONS ============== */
.btn {
  background: transparent;
  border: 1px solid #c0c0c0;
  color: #1a1a1a;
  padding: 12px 24px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
  white-space: nowrap;
  text-align: center;
}

.btn:hover {
  background: #f5f5f5;
  border-color: #a0a0a0;
}

.btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.btn.primary {
  background: #000;
  color: #fff;
  border-color: #000;
  font-weight: 600;
}

.btn.primary:hover {
  background: #1a1a1a;
  border-color: #1a1a1a;
}

.btn.primary:disabled {
  background: #666;
  border-color: #666;
}

.btn.ghost {
  border-color: #d0d0d0;
  color: #666;
}

.btn.ghost:hover {
  border-color: #a0a0a0;
  color: #1a1a1a;
}

.btn.small {
  padding: 8px 16px;
  font-size: 12px;
  width: 100%;
}

.btn.danger {
  border-color: #dc2626;
  color: #dc2626;
}

.btn.danger:hover {
  background: #dc2626;
  color: #fff;
}

/* ============== UTILITIES ============== */
.text-muted {
  color: #999;
  font-size: 14px;
  text-align: center;
  padding: 40px 20px;
}

/* ============== RESPONSIVE ============== */
@media (max-width: 768px) {
  .overlay {
    padding: 0;
  }

  .modal {
    max-width: 100%;
    max-height: 100vh;
    height: 100vh;
  }

  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 20px;
  }

  .modal-header h2 {
    font-size: 18px;
  }

  .upload-row {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }

  .upload-row input[type="file"] {
    width: 100%;
  }

  .images-grid {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
  }

  .image-item img {
    height: 150px;
  }
}

@media (max-width: 480px) {
  .images-grid {
    grid-template-columns: 1fr 1fr;
  }

  .image-item img {
    height: 120px;
  }

  .image-actions {
    padding: 8px;
    gap: 6px;
  }

  .btn.small {
    padding: 6px 12px;
    font-size: 11px;
  }

  .badge-primary {
    padding: 4px 8px;
    font-size: 10px;
  }
}
</style>
