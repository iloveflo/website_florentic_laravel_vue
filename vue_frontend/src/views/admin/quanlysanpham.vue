<template>
  <div class="admin-content">
    <h1 class="page-title">Quản lý sản phẩm</h1>

    <!-- Thanh tìm kiếm / lọc -->
    <div class="toolbar">
      <input v-model="filters.search" type="text" class="input" placeholder="Tìm theo tên, SKU..."
        @keyup.enter="fetchProducts" />

      <!-- Lọc theo nhóm cha: Áo / Quần / Phụ kiện / Bộ sưu tập -->
      <select v-model="selectedParentId" class="input select" @change="handleParentChange">
        <option value="">Tất cả nhóm sản phẩm</option>
        <option v-for="parent in parentCategories" :key="parent.id" :value="parent.id">
          {{ parent.name }}
        </option>
      </select>

      <!-- Lọc theo danh mục con trong nhóm đã chọn -->
      <select v-model="filters.category_id" class="input select" @change="fetchProducts"
        :disabled="childCategories.length === 0">
        <option value="">Tất cả loại trong nhóm</option>
        <option v-for="cate in childCategories" :key="cate.id" :value="cate.id">
          {{ cate.name }}
        </option>
      </select>

      <button class="btn primary" @click="openCreateForm">
        + Thêm sản phẩm
      </button>
    </div>

    <!-- Bảng dữ liệu -->
    <div class="table-wrapper" v-if="!loading">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Ảnh</th>
            <th>Tên</th>
            <th>Danh mục</th>
            <th>Màu sắc</th>
            <th>Size</th>
            <th>Số lượng</th>
            <th>Giá</th>
            <th>Trạng thái</th>
            <th style="width: 150px">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products.data" :key="product.id">
            <td>#{{ product.id }}</td>

            <!-- Ảnh -->
            <td>
              <div v-if="getMainImageSrc(product)" class="thumb-wrapper">
                <img :src="getMainImageSrc(product)" alt="" class="thumb" />
              </div>
              <div v-else class="thumb-placeholder">Không ảnh</div>
            </td>

            <!-- Tên + SKU -->
            <td class="name-cell">
              <strong>{{ product.name }}</strong>
              <div class="sub">
                SKU: {{ product.sku }} • Lượt xem: {{ product.view_count }}
              </div>
            </td>

            <!-- Danh mục -->
            <td>{{ product.category?.name || '-' }}</td>

            <!-- MÀU SẮC: mỗi biến thể 1 dòng -->
            <td>
              <div v-if="product.variants && product.variants.length">
                <div v-for="v in product.variants" :key="v.id" class="variant-line">
                  {{ v.color_name || v.color_code || '-' }}
                </div>
              </div>
              <span v-else>-</span>
            </td>

            <!-- SIZE: mỗi biến thể 1 dòng, cùng thứ tự với màu -->
            <td>
              <div v-if="product.variants && product.variants.length">
                <div v-for="v in product.variants" :key="v.id" class="variant-line">
                  {{ v.size || '-' }}
                </div>
              </div>
              <span v-else>-</span>
            </td>

            <!-- SỐ LƯỢNG: mỗi biến thể 1 dòng, cùng thứ tự luôn -->
            <td>
              <div v-if="product.variants && product.variants.length">
                <div v-for="v in product.variants" :key="v.id" class="variant-line">
                  {{ v.quantity ?? 0 }}
                </div>
              </div>
              <span v-else>{{ product.quantity }}</span>
            </td>

            <!-- Giá / Trạng thái / Thao tác giữ nguyên -->
            <td>{{ formatPrice(product.price) }}</td>
            <td>
              <span class="badge" :class="product.status === 'active' ? 'badge-success' : 'badge-muted'">
                {{ product.status === 'active' ? 'Đang bán' : 'Ngừng bán' }}
              </span>
            </td>
            <td>
              <button class="btn small" @click="openImageModal(product)">
                Ảnh
              </button>
              <button class="btn small" @click="openEditForm(product)">
                Sửa
              </button>
              <button class="btn small danger" @click="deleteProduct(product.id)">
                Xóa
              </button>
            </td>
          </tr>

        </tbody>
      </table>
    </div>

    <div v-else class="text-center text-muted" style="margin-top: 20px">
      Đang tải dữ liệu...
    </div>

    <!-- Phân trang -->
    <div v-if="products.last_page > 1" class="pagination">
      <button class="btn small" :disabled="products.current_page === 1" @click="changePage(products.current_page - 1)">
        « Trước
      </button>

      <span>
        Trang {{ products.current_page }} / {{ products.last_page }}
      </span>

      <button class="btn small" :disabled="products.current_page === products.last_page"
        @click="changePage(products.current_page + 1)">
        Sau »
      </button>
    </div>


    <!-- Modal form thêm / sửa -->
    <ProductFormModal v-if="showForm" :mode="formMode" :product="editingProduct" :categories="categories"
      @close="closeForm" @saved="onSaved" />
    <ProductImagesModal v-if="showImages && currentProductForImages" :show="showImages"
      :product-id="currentProductForImages.id" :product-name="currentProductForImages.name" @close="showImages = false"
      @changed="fetchProducts(products.current_page || 1)" />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import ProductFormModal from '../../components/admin/ProductFormModal.vue'
import ProductImagesModal from '../../components/admin/ProductImagesModal.vue'

const products = ref({
  data: [],
  current_page: 1,
  last_page: 1,
  total: 0,
})
const categories = ref([])
const loading = ref(false)

const selectedParentId = ref('')   // nhóm Áo / Quần / ...
const filters = ref({
  search: '',
  category_id: ''                  // id category con
})

const showForm = ref(false)
const formMode = ref('create') // 'create' | 'edit'
const editingProduct = ref(null)

const showImages = ref(false)
const currentProductForImages = ref(null)

const openImageModal = (product) => {
  currentProductForImages.value = product
  showImages.value = true
}

// 4 mục cha
const parentCategories = computed(() =>
  categories.value.filter(c => !c.parent_id)
)

// danh sách con theo nhóm cha
const childCategories = computed(() => {
  if (selectedParentId.value) {
    return categories.value.filter(
      c => c.parent_id === Number(selectedParentId.value)
    )
  }
  return categories.value.filter(c => c.parent_id !== null)
})

const fetchCategories = async () => {
  try {
    const res = await axios.get('/admin/products/categories')
    categories.value = res.data
  } catch (e) {
    console.error('Lỗi load categories', e)
  }
}

const fetchProducts = async (page = 1) => {
  loading.value = true
  try {
    const res = await axios.get('/admin/products', {
      params: {
        page,
        search: filters.value.search || undefined,
        category_id: filters.value.category_id || undefined,
        category_parent_id: selectedParentId.value || undefined
      }
    })
    products.value = res.data
  } catch (e) {
    console.error('Lỗi load products', e)
  } finally {
    loading.value = false
  }
}

const handleParentChange = () => {
  // reset loại con khi đổi nhóm cha
  filters.value.category_id = ''
  fetchProducts()
}

const changePage = (page) => {
  fetchProducts(page)
}

const openCreateForm = () => {
  formMode.value = 'create'
  editingProduct.value = null
  showForm.value = true
}

const openEditForm = async (product) => {
  try {
    formMode.value = 'edit'
    // gọi API chi tiết để lấy cả variants + images
    const res = await axios.get(`/admin/products/${product.id}`)
    editingProduct.value = res.data
    showForm.value = true
  } catch (e) {
    console.error('Lỗi tải chi tiết sản phẩm', e)
    alert('Không tải được chi tiết sản phẩm')
  }
}

const closeForm = () => {
  showForm.value = false
}

const onSaved = () => {
  showForm.value = false
  fetchProducts(products.value.current_page || 1)
}

const deleteProduct = async (id) => {
  if (!confirm('Bạn chắc chắn muốn xóa sản phẩm này?')) return
  try {
    await axios.post(`/admin/products/${id}`, {
    _method: 'DELETE'
});
    fetchProducts(products.value.current_page || 1)
  } catch (e) {
    console.error(e)
    alert('Xóa sản phẩm thất bại')
  }
}

const formatPrice = (value) =>
  new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(value || 0)

onMounted(async () => {
  await fetchCategories()
  await fetchProducts()
})

// Lấy URL ảnh chính
const getMainImageSrc = (product) => {
  // ưu tiên main_image_url từ API
  if (product.main_image_url) return product.main_image_url

  // fallback: lấy từ mảng images
  if (product.images && product.images.length) {
    const main = product.images.find(i => i.is_primary) || product.images[0]
    return main.url || `/storage/${main.image_path}`
  }

  return null
}

// Lấy danh sách màu (color_name) từ variants
const getColorNames = (product) => {
  if (!product.variants || !product.variants.length) return '-'

  const names = product.variants
    .map((v) => v.color_name || '')
    .filter((name) => name.trim() !== '')

  const unique = [...new Set(names)]

  // Nếu không có color_name, có thể fallback sang color_code
  if (!unique.length) {
    const codes = product.variants
      .map((v) => v.color_code || '')
      .filter((c) => c.trim() !== '')
    return codes.length ? [...new Set(codes)].join(', ') : '-'
  }

  return unique.join(', ')
}

// Lấy danh sách size từ variants
const getSizeList = (product) => {
  if (!product.variants || !product.variants.length) return '-'

  const sizes = product.variants
    .map((v) => v.size || '')
    .filter((s) => s.trim() !== '')

  const unique = [...new Set(sizes)]

  return unique.length ? unique.join(', ') : '-'
}

</script>

<style scoped>
/* ============================================
   QUẢN LÝ SẢN PHẨM - LIGHT MINIMAL STYLE
   ============================================ */

.admin-content {
  background: #ffffff;
  min-height: 100vh;
  padding: 40px;
  color: #1a1a1a;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* ============== PAGE TITLE ============== */
.page-title {
  font-size: 32px;
  font-weight: 700;
  color: #000000;
  margin: 0 0 40px 0;
  padding-bottom: 16px;
  border-bottom: 2px solid #e5e5e5;
  letter-spacing: -0.5px;
}

/* ============== TOOLBAR ============== */
.toolbar {
  display: flex;
  gap: 12px;
  margin-bottom: 32px;
  flex-wrap: wrap;
}

.input {
  background: #fafafa;
  border: 1px solid #d0d0d0;
  color: #1a1a1a;
  padding: 12px 16px;
  font-size: 14px;
  outline: none;
  transition: all 0.2s ease;
  font-family: inherit;
}

.input::placeholder {
  color: #999;
}

.input:focus {
  border-color: #000;
  background: #ffffff;
}

.input[type="text"] {
  flex: 1;
  min-width: 250px;
}

.select {
  min-width: 200px;
  cursor: pointer;
}

.select:disabled {
  opacity: 0.4;
  cursor: not-allowed;
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

.btn.small {
  padding: 6px 12px;
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

/* ============== TABLE ============== */
.table-wrapper {
  background: #fafafa;
  border: 1px solid #e5e5e5;
  overflow-x: auto;
}

.table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.table thead {
  background: #f5f5f5;
  border-bottom: 2px solid #e5e5e5;
}

.table th {
  padding: 16px 12px;
  text-align: left;
  font-weight: 600;
  color: #666;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 1px;
}

.table td {
  padding: 16px 12px;
  border-bottom: 1px solid #e5e5e5;
  color: #4a4a4a;
}

.table tbody tr {
  transition: background 0.15s ease;
}

.table tbody tr:hover {
  background: #f5f5f5;
}

/* ============== TABLE CELLS ============== */
.thumb-wrapper {
  width: 50px;
  height: 50px;
  overflow: hidden;
  background: #fff;
  border: 1px solid #d0d0d0;
}

.thumb {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.thumb-placeholder {
  width: 50px;
  height: 50px;
  background: #f5f5f5;
  border: 1px solid #d0d0d0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  color: #999;
}

.name-cell strong {
  color: #000;
  display: block;
  margin-bottom: 4px;
  font-weight: 600;
}

.sub {
  font-size: 12px;
  color: #999;
  margin-top: 4px;
}

.variant-line {
  padding: 4px 0;
  border-bottom: 1px solid #e5e5e5;
}

.variant-line:last-child {
  border-bottom: none;
}

/* ============== BADGES ============== */
.badge {
  display: inline-block;
  padding: 4px 10px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border: 1px solid;
}

.badge-success {
  background: #f0fdf4;
  color: #16a34a;
  border-color: #bbf7d0;
}

.badge-muted {
  background: #f5f5f5;
  color: #999;
  border-color: #d0d0d0;
}

/* ============== PAGINATION ============== */
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid #e5e5e5;
}

.pagination span {
  color: #666;
  font-size: 14px;
  font-weight: 500;
}

/* ============== UTILITIES ============== */
.text-center {
  text-align: center;
}

.text-muted {
  color: #999;
  font-size: 14px;
}

/* ============== RESPONSIVE ============== */
@media (max-width: 768px) {
  .admin-content {
    padding: 20px;
  }

  .page-title {
    font-size: 24px;
    margin-bottom: 24px;
  }

  .toolbar {
    flex-direction: column;
  }

  .input[type="text"],
  .select {
    width: 100%;
  }

  .table {
    font-size: 12px;
  }

  .table th,
  .table td {
    padding: 10px 8px;
  }

  .btn.small {
    font-size: 11px;
    padding: 5px 10px;
  }
}
</style>