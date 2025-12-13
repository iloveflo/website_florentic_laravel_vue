<template>
  <div class="overlay">
    <div class="modal">
      <div class="modal-header">
        <h2>{{ mode === 'create' ? 'Thêm sản phẩm' : 'Chỉnh sửa sản phẩm' }}</h2>
        <button class="btn-close" @click="$emit('close')">×</button>
      </div>

      <form @submit.prevent="handleSubmit" class="modal-body">
        <!-- Thông tin chung -->
        <div class="form-group">
          <label>Tên sản phẩm <span>*</span></label>
          <input v-model="form.name" type="text" required />
        </div>

        <!-- Nhóm danh mục + danh mục con -->
        <div class="form-row">
          <div class="form-group">
            <label>Nhóm sản phẩm <span>*</span></label>
            <select v-model="selectedParentId" @change="onParentChange">
              <option value="">-- Chọn nhóm (Áo / Quần / ...) --</option>
              <option
                v-for="pc in parentCategories"
                :key="pc.id"
                :value="pc.id"
              >
                {{ pc.name }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Danh mục <span>*</span></label>
            <select v-model="form.category_id" required>
              <option value="">-- Chọn danh mục trong nhóm --</option>
              <option
                v-for="cate in childCategories"
                :key="cate.id"
                :value="cate.id"
              >
                {{ cate.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Giá gốc (VND) <span>*</span></label>
            <input
              v-model.number="form.price"
              type="number"
              min="0"
              required
            />
          </div>

          <div class="form-group">
            <label>Trạng thái</label>
            <select v-model="form.status">
              <option value="active">Đang bán</option>
              <option value="inactive">Ngừng bán</option>
              <option value="out_of_stock">Hết hàng</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label>Mô tả</label>
          <textarea v-model="form.description" rows="3" />
        </div>

        <!-- Biến thể -->
        <div class="variants-block">
          <div class="variants-header">
            <h3>Biến thể (màu / size / số lượng)</h3>
            <button type="button" class="btn small" @click="addVariantRow">
              + Thêm biến thể
            </button>
          </div>

          <table class="variant-table">
            <thead>
              <tr>
                <th>Màu</th>
                <th>Mã màu (#hex)</th>
                <th>Size</th>
                <th>Số lượng</th>
                <th>Phụ phí (+VND)</th>
                <th>SKU riêng</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(v, index) in variants" :key="index">
                <td>
                  <input v-model="v.color_name" type="text" placeholder="Trắng" />
                </td>
                <td>
                  <input
                    v-model="v.color_code"
                    type="text"
                    placeholder="#FFFFFF"
                  />
                </td>
                <td>
                  <select v-model="v.size">
                    <option value="S">S</option>
                    <option value="M">M</option>
                    <option value="L">L</option>
                    <option value="XL">XL</option>
                    <option value="XXL">XXL</option>
                  </select>
                </td>
                <td>
                  <input
                    v-model.number="v.quantity"
                    type="number"
                    min="0"
                    style="width: 80px"
                  />
                </td>
                <td>
                  <input
                    v-model.number="v.additional_price"
                    type="number"
                    min="0"
                    style="width: 100px"
                  />
                </td>
                <td>
                  <input v-model="v.sku" type="text" placeholder="Tự sinh nếu bỏ trống" />
                </td>
                <td class="text-right">
                  <button
                    type="button"
                    class="btn small danger"
                    v-if="variants.length > 1"
                    @click="removeVariantRow(index)"
                  >
                    Xóa
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <p class="variant-hint">
            * Quantity tổng của sản phẩm sẽ tự động = tổng quantity của tất cả
            biến thể.
          </p>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn ghost" @click="$emit('close')">
            Hủy
          </button>
          <button type="submit" class="btn primary" :disabled="submitting">
            {{ submitting ? 'Đang lưu...' : 'Lưu lại' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  mode: {
    type: String,
    default: 'create' // 'create' | 'edit'
  },
  product: {
    type: Object,
    default: null
  },
  categories: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'saved'])

const selectedParentId = ref('')

const form = ref({
  name: '',
  category_id: '',
  price: 0,
  description: '',
  status: 'active'
})

const variants = ref([])
const submitting = ref(false)

const isEdit = computed(() => props.mode === 'edit')

// ---- helpers ----
const defaultVariant = () => ({
  color_name: '',
  color_code: '',
  size: 'M',
  quantity: 0,
  additional_price: 0,
  sku: ''
})

const resetForm = () => {
  selectedParentId.value = ''
  form.value = {
    name: '',
    category_id: '',
    price: 0,
    description: '',
    status: 'active'
  }
  variants.value = [defaultVariant()]
}

// Nhóm cha / con cho danh mục
const parentCategories = computed(() =>
  props.categories.filter(c => !c.parent_id)
)

const childCategories = computed(() => {
  if (selectedParentId.value) {
    return props.categories.filter(
      c => c.parent_id === Number(selectedParentId.value)
    )
  }
  return props.categories.filter(c => c.parent_id !== null)
})

const onParentChange = () => {
  form.value.category_id = ''
}

// Map dữ liệu khi edit
watch(
  () => props.product,
  (val) => {
    if (isEdit.value && val) {
      form.value = {
        name: val.name || '',
        category_id: val.category_id || '',
        price: val.price || 0,
        description: val.description || '',
        status: val.status || 'active'
      }

      const cate = props.categories.find(c => c.id === val.category_id)
      if (cate) {
        selectedParentId.value = cate.parent_id || cate.id
      } else {
        selectedParentId.value = ''
      }

      if (val.variants && val.variants.length) {
        variants.value = val.variants.map(v => ({
          color_name: v.color_name || '',
          color_code: v.color_code || '',
          size: v.size || 'M',
          quantity: v.quantity ?? 0,
          additional_price: v.additional_price ?? 0,
          sku: v.sku || ''
        }))
      } else {
        variants.value = [defaultVariant()]
      }
    } else {
      resetForm()
    }
  },
  { immediate: true }
)

// Thao tác với biến thể
const addVariantRow = () => {
  variants.value.push(defaultVariant())
}

const removeVariantRow = (index) => {
  if (variants.value.length === 1) return
  variants.value.splice(index, 1)
}

const handleSubmit = async () => {
  if (!form.value.name || !form.value.category_id) return

  // chỉ gửi những biến thể có size và (quantity >0 hoặc có thông tin màu/sku)
  const cleanVariants = variants.value.filter(
    v =>
      v.size &&
      (v.quantity > 0 || v.color_name || v.color_code || v.sku)
  )

  const payload = {
    ...form.value,
    variants: cleanVariants
  }

  submitting.value = true
  try {
    if (isEdit.value && props.product) {
      await axios.post(`/admin/products/${props.product.id}`, {
    ...payload,      // Copy toàn bộ dữ liệu form cũ
    _method: 'PUT'   // Thêm dòng này để báo cho Laravel biết đây là PUT
});
    } else {
      await axios.post('/admin/products', payload)
    }
    emit('saved')
  } catch (e) {
    console.error(e)
    alert('Lưu sản phẩm thất bại')
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
/* ============================================
   MODAL FORM - LIGHT MINIMAL STYLE
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
  max-width: 900px;
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

/* ============== FORM GROUPS ============== */
.form-group {
  margin-bottom: 24px;
}

.form-group label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-group label span {
  color: #dc2626;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group select,
.form-group textarea {
  width: 100%;
  background: #fafafa;
  border: 1px solid #d0d0d0;
  color: #1a1a1a;
  padding: 12px 16px;
  font-size: 14px;
  outline: none;
  transition: all 0.2s ease;
  font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #000;
  background: #ffffff;
}

.form-group textarea {
  resize: vertical;
}

.form-group select {
  cursor: pointer;
}

/* ============== FORM ROW ============== */
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 24px;
}

/* ============== VARIANTS BLOCK ============== */
.variants-block {
  margin-top: 32px;
  padding-top: 32px;
  border-top: 2px solid #e5e5e5;
}

.variants-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.variants-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #000;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* ============== VARIANT TABLE ============== */
.variant-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
  margin-bottom: 12px;
  background: #fafafa;
  border: 1px solid #e5e5e5;
}

.variant-table thead {
  background: #f5f5f5;
  border-bottom: 2px solid #e5e5e5;
}

.variant-table th {
  padding: 12px 8px;
  text-align: left;
  font-weight: 600;
  color: #666;
  text-transform: uppercase;
  font-size: 10px;
  letter-spacing: 0.5px;
}

.variant-table td {
  padding: 8px;
  border-bottom: 1px solid #e5e5e5;
}

.variant-table tbody tr:last-child td {
  border-bottom: none;
}

.variant-table input[type="text"],
.variant-table input[type="number"],
.variant-table select {
  width: 100%;
  background: #ffffff;
  border: 1px solid #d0d0d0;
  color: #1a1a1a;
  padding: 8px 10px;
  font-size: 13px;
  outline: none;
  transition: all 0.2s ease;
  font-family: inherit;
}

.variant-table input:focus,
.variant-table select:focus {
  border-color: #000;
}

.variant-table input::placeholder {
  color: #999;
  font-size: 12px;
}

.variant-table select {
  cursor: pointer;
}

.variant-hint {
  font-size: 12px;
  color: #999;
  margin: 8px 0 0 0;
  font-style: italic;
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
.text-right {
  text-align: right;
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
    font-size: 20px;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .variant-table {
    font-size: 11px;
  }

  .variant-table th,
  .variant-table td {
    padding: 6px 4px;
  }

  .variant-table input,
  .variant-table select {
    padding: 6px 8px;
    font-size: 12px;
  }

  .variants-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .btn.small {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .variant-table th:nth-child(5),
  .variant-table td:nth-child(5),
  .variant-table th:nth-child(6),
  .variant-table td:nth-child(6) {
    display: none;
  }
}
</style>
