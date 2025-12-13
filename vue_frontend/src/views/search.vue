<template>
  <div class="products-page">
    <div class="container">
      <main class="main-content full-width">
        <div class="header-search-result">
          <h1>
            <span v-if="currentKeyword">Kết quả tìm kiếm cho: "{{ currentKeyword }}"</span>
            <span v-else>Tất cả sản phẩm</span>
          </h1>
          <p class="result-count">Tìm thấy {{ totalProducts }} sản phẩm</p>
        </div>

        <div class="products-grid">
          <div v-for="product in products" :key="product.id" class="product-card">
            <div class="product-image">
              <img :src="getProductImage(product)" :alt="product.name"
                @error="(e) => e.target.src = 'https://via.placeholder.com/300x300?text=No+Image'" />
              <span v-if="product.featured" class="badge">Nổi bật</span>
            </div>
            <div class="product-info">
              <h3 class="product-name">{{ product.name }}</h3>

              <div class="product-price">
                <div v-if="product.sale_price && parseFloat(product.sale_price) < parseFloat(product.price)"
                  class="price-sale">
                  <span class="sale-price">{{ formatPrice(product.sale_price) }}</span>
                  <span class="original-price">{{ formatPrice(product.price) }}</span>
                </div>
                <span v-else class="price">{{ formatPrice(product.price) }}</span>
              </div>
              <div class="product-actions">
                <button class="quick-view-btn" @click="openQuickView(product)">
                  Xem nhanh
                </button>
                <button class="detail-btn" @click="gotoDetail(product)">
                  Xem chi tiết
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-if="loading" class="loading">
          <div class="spinner"></div>
          <p>Đang tìm kiếm...</p>
        </div>

        <div v-if="!loading && products.length === 0" class="no-products">
          <svg class="icon-empty" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="64" height="64">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <p>Không tìm thấy sản phẩm nào phù hợp với từ khóa "{{ currentKeyword }}"</p>
          <button @click="clearSearch" class="back-btn">Xem tất cả sản phẩm</button>
        </div>

        <div v-if="totalPages > 1 && !loading" class="pagination-container">
          <button class="page-btn prev" :disabled="currentPage === 1" @click="changePage(currentPage - 1)">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>

          <div class="page-numbers">
            <button v-for="page in visiblePages" :key="page" class="page-btn"
              :class="{ active: currentPage === page, 'dots': page === '...' }"
              @click="page !== '...' ? changePage(page) : null" :disabled="page === '...'">
              {{ page }}
            </button>
          </div>

          <button class="page-btn next" :disabled="currentPage === totalPages" @click="changePage(currentPage + 1)">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <div v-if="showQuickView && quickViewProduct" class="modal-overlay" @click.self="closeQuickView">
          <div class="modal">
            <button class="modal-close" @click="closeQuickView">×</button>

            <div class="modal-body">
              <div class="modal-image">
                <img :src="quickMainImage" :alt="quickViewProduct.name" class="modal-main-image"
                  @error="onQuickImageError" />

                <div class="thumb-list" v-if="quickViewProduct.images?.length">
                  <img v-for="img in quickViewProduct.images" :key="img.id" :src="img.image_path" class="thumb"
                    :class="{ active: img.image_path === quickMainImage }" @click="quickMainImage = img.image_path"
                    @error="onQuickImageError" />
                </div>
              </div>

              <div class="modal-info">
                <h2 class="modal-title">{{ quickViewProduct.name }}</h2>

                <div class="modal-price">
                  <span
                    v-if="quickViewProduct.sale_price && parseFloat(quickViewProduct.sale_price) < parseFloat(quickViewProduct.price)"
                    class="sale-price">
                    {{ formatPrice(quickViewProduct.sale_price) }}
                  </span>
                  <span :class="{
                    'original-price':
                      quickViewProduct.sale_price &&
                      parseFloat(quickViewProduct.sale_price) < parseFloat(quickViewProduct.price),
                  }">
                    {{ formatPrice(quickViewProduct.price) }}
                  </span>
                </div>

                <p class="modal-desc-toggle" @click="showFullDesc = !showFullDesc">
                  Mô tả sản phẩm {{ showFullDesc ? '⯅' : '⯆' }}
                </p>
                <p v-if="showFullDesc" class="modal-desc">
                  {{ quickViewProduct.description || 'Chưa có mô tả.' }}
                </p>

                <div v-if="quickViewProduct.colors?.length" class="modal-block">
                  <p><strong>Chọn màu:</strong></p>
                  <div class="color-options">
                    <button v-for="c in quickViewProduct.colors" :key="c.id" class="color-option"
                      :style="{ backgroundColor: c.color_code }"
                      :class="{ active: selectedColor === c.color_name }" @click="selectedColor = c.color_name"
                      :title="c.color_name"></button>
                  </div>
                </div>

                <div v-if="quickViewProduct.sizes?.length" class="modal-block">
                  <p class="label">
                    <strong>Chọn size:</strong>
                    <a href="/help/size-guide" target="_blank" class="size-guide-link">
                      Hướng dẫn chọn size
                    </a>
                  </p>
                  <div class="size-options">
                    <button v-for="s in quickViewProduct.sizes" :key="s.id" class="modal-size-btn"
                      :class="{ active: selectedSize === s.size }" @click="selectedSize = s.size">
                      {{ s.size }}
                    </button>
                  </div>
                </div>

                <div class="modal-block">
                  <p><strong>Số lượng:</strong></p>
                  <div class="quantity-container">
                    <button class="qty-btn" @click="decreaseQty">–</button>
                    <input type="number" class="qty-input" v-model.number="quantity" min="1" />
                    <button class="qty-btn" @click="increaseQty">+</button>
                  </div>
                </div>

                <button class="add-cart-btn" @click="addQuickToCart" :disabled="addingQuick">
                  <span v-if="addingQuick">Đang xử lý...</span>
                  <span v-else>Thêm vào giỏ hàng</span>
                </button>

                <div class="detail-link-wrap">
                  <a href="javascript:void(0)" @click="gotoDetailFromQuick">
                    Xem chi tiết sản phẩm
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
    <transition name="toast-fade">
      <div v-if="isToastVisible" class="center-toast">
        {{ toastMessage }}
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onBeforeUnmount, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'; // Thêm useRoute
import axios from 'axios';

const router = useRouter();
const route = useRoute(); // Hook để lấy query params từ URL

const products = ref([]);
const loading = ref(false);

// Phân trang
const currentPage = ref(1);
const totalPages = ref(0);
const totalProducts = ref(0); // Hiển thị số lượng tìm thấy
const itemsPerPage = 20;

// Keyword hiện tại
const currentKeyword = ref('');

// --- LOGIC PHÂN TRANG MỚI ---
const visiblePages = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  const delta = 2;
  const range = [];
  const rangeWithDots = [];
  let l;

  for (let i = 1; i <= total; i++) {
    if (i === 1 || i === total || (i >= current - delta && i <= current + delta)) {
      range.push(i);
    }
  }

  for (let i of range) {
    if (l) {
      if (i - l === 2) {
        rangeWithDots.push(l + 1);
      } else if (i - l !== 1) {
        rangeWithDots.push('...');
      }
    }
    rangeWithDots.push(i);
    l = i;
  }
  return rangeWithDots;
});

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
    fetchProducts(page);
  }
};

// --- FETCH PRODUCTS (CHỈ SEARCH THEO TÊN) ---
const fetchProducts = async (page = 1) => {
  loading.value = true;
  try {
    // Lấy keyword từ URL query string (vd: ?keyword=iphone)
    const keyword = route.query.keyword || '';
    currentKeyword.value = keyword;

    const params = new URLSearchParams({
      page: page,
      per_page: itemsPerPage,
      keyword: keyword // Gửi keyword lên API
    });

    const response = await fetch(`/api/search?${params.toString()}`);
    const data = await response.json();

    products.value = data.data;
    currentPage.value = data.current_page;
    totalPages.value = data.last_page;
    totalProducts.value = data.total; // Laravel paginate trả về field total

    window.scrollTo({ top: 0, behavior: 'smooth' });

  } catch (error) {
    console.error('Error fetching products:', error);
  } finally {
    loading.value = false;
  }
};

// Theo dõi URL: Nếu người dùng search từ header khi đang ở trang này, url thay đổi -> gọi lại API
watch(
  () => route.query.keyword,
  (newKeyword) => {
    fetchProducts(1); // Reset về trang 1 khi search từ khóa mới
  }
);

// Nút "Xem tất cả" khi không tìm thấy kết quả
const clearSearch = () => {
  router.push({ path: '/search' }); // Xóa query param
  // watch sẽ tự động trigger fetchProducts
};

const gotoDetail = (product) => {
  router.push({
    name: 'product-details',
    params: { slug: product.slug },
  })
}

// Format price
const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price);
};

// Ảnh chính của sản phẩm
const getProductImage = (product) => {
  if (product.primary_image_url) return product.primary_image_url;
  if (product.images && product.images.length) {
    const main = product.images.find(img => img.is_primary) || product.images[0]
    if (main.url) return main.url
    if (main.image_path) return `/${main.image_path}`
  }
  return 'https://via.placeholder.com/300x300?text=No+Image'
}

onMounted(() => {
  fetchProducts(1);
});

/* ======================================================
   QUICK VIEW MODAL (GIỮ NGUYÊN)
====================================================== */
const showQuickView = ref(false)
const quickViewProduct = ref(null)
const quickMainImage = ref('')
const showFullDesc = ref(false)

const selectedSize = ref('')
const selectedColor = ref('')
const quantity = ref(1)
const addingQuick = ref(false)

const openQuickView = async (product) => {
  try {
    showQuickView.value = true
    quickViewProduct.value = product
    quickMainImage.value = getProductImage(product)
    selectedSize.value = ''
    selectedColor.value = ''
    quantity.value = 1
    showFullDesc.value = false

    const res = await axios.get(`/products/${product.slug}`)
    let detail = res.data.product || res.data

    if (detail && detail.variants && detail.variants.length) {
      const sizeMap = new Map()
      const colorMap = new Map()

      detail.variants.forEach((v, index) => {
        if (v.size && !sizeMap.has(v.size)) {
          sizeMap.set(v.size, {
            id: 'size_' + (index + 1),
            size: v.size,
            quantity: v.quantity ?? 0,
          })
        }
        const colorKey = v.color_name || v.color_code
        if (colorKey && !colorMap.has(colorKey)) {
          colorMap.set(colorKey, {
            id: 'color_' + (index + 1),
            color_name: v.color_name || '',
            color_code: v.color_code || '#000000',
          })
        }
      })
      detail = {
        ...detail,
        sizes: Array.from(sizeMap.values()),
        colors: Array.from(colorMap.values()),
      }
    }

    if (detail) {
      quickViewProduct.value = detail
      const mainImg = detail.images?.find((img) => img.is_primary) || detail.images?.[0]
      quickMainImage.value = mainImg?.url || mainImg?.image_path || getProductImage(detail)
    }
  } catch (err) {
    console.error('Lỗi openQuickView:', err)
    alert('Không tải được thông tin sản phẩm')
  }
}

const closeQuickView = () => {
  showQuickView.value = false
}

const onQuickImageError = (e) => {
  e.target.src = 'https://via.placeholder.com/600x600?text=No+Image'
}

const increaseQty = () => {
  quantity.value++
}
const decreaseQty = () => {
  if (quantity.value > 1) quantity.value--
}

const getSessionId = () => {
  let sessionId = localStorage.getItem('cart_session_id')
  if (!sessionId) {
    sessionId = 'sess_' + Math.random().toString(36).substr(2, 9) + Date.now()
    localStorage.setItem('cart_session_id', sessionId)
  }
  return sessionId
}

const getAvailableStock = (p, size, color) => {
  if (!p) return null
  if (p.variants && p.variants.length) {
    const variant = p.variants.find(v => {
      const sameSize = !size || v.size === size
      const sameColor = !color || v.color_name === color || v.color_code === color
      return sameSize && sameColor
    })
    if (!variant) return 0
    return Number(variant.quantity ?? 0)
  }
  if (typeof p.stock_quantity !== 'undefined') {
    return Number(p.stock_quantity)
  }
  return null
}

const addQuickToCart = async () => {
  if (!quickViewProduct.value) return
  const p = quickViewProduct.value
  const hasSizeOptions = (p.sizes && p.sizes.length) || (p.variants && p.variants.some((v) => v.size))
  if (hasSizeOptions && !selectedSize.value) {
    showToast('Vui lòng chọn kích thước!')
    return
  }
  const hasColorOptions = (p.colors && p.colors.length) || (p.variants && p.variants.some((v) => v.color_name || v.color_code))
  if (hasColorOptions && !selectedColor.value) {
    showToast('Vui lòng chọn màu sắc!')
    return
  }
  const stock = getAvailableStock(p, selectedSize.value || null, selectedColor.value || null)
  if (stock !== null) {
    if (stock <= 0) {
      showToast('Sản phẩm hiện đã hết hàng!')
      return
    }
    if (quantity.value > stock) {
      showToast(`Kho chỉ còn ${stock} sản phẩm!`)
      return
    }
  }

  addingQuick.value = true
  try {
    const token = localStorage.getItem('token')
    const sessionId = getSessionId()
    const payload = {
      product_id: p.id,
      quantity: quantity.value,
      size: selectedSize.value || null,
      color: selectedColor.value || null,
      session_id: sessionId
    }
    const config = { headers: {} }
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`
    }
    const response = await axios.post(`/cart/add`, payload, config)
    if (response.status === 200 || response.status === 201) {
      showToast('Đã thêm sản phẩm vào giỏ hàng!')
      if (response.data.data?.session_id) {
        localStorage.setItem('cart_session_id', response.data.data.session_id)
      }
      window.dispatchEvent(new Event('cart-updated'))
      showQuickView.value = false
    }
  } catch (error) {
    console.error('Lỗi thêm giỏ hàng:', error)
    showToast('Có lỗi khi thêm vào giỏ hàng!')
  } finally {
    addingQuick.value = false
  }
}

const gotoDetailFromQuick = () => {
  if (!quickViewProduct.value) return
  router.push({
    name: 'product-details',
    params: { slug: quickViewProduct.value.slug }
  })
  showQuickView.value = false
}

// ========== TOAST ========== 
const isToastVisible = ref(false)
const toastMessage = ref('')
let toastTimerId = null

const showToast = (msg) => {
  toastMessage.value = msg
  isToastVisible.value = true
  if (toastTimerId) clearTimeout(toastTimerId)
  toastTimerId = setTimeout(() => {
    isToastVisible.value = false
  }, 2200)
}

onBeforeUnmount(() => {
  if (toastTimerId) clearTimeout(toastTimerId)
})
</script>

<style scoped>
* {
  box-sizing: border-box;
}
.header-search-result {
  margin-bottom: 20px;
  border-bottom: 1px solid #eee;
  padding-bottom: 15px;
}

.header-search-result h1 {
  font-size: 24px;
  margin-bottom: 5px;
}

.result-count {
  color: #666;
  font-size: 14px;
}

.no-products {
  text-align: center;
  padding: 50px 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
}
.icon-empty {
  color: #ddd;
}
.back-btn {
  padding: 10px 20px;
  background: #000;
  color: #fff;
  border: none;
  cursor: pointer;
}

.products-page {
  min-height: 100vh;
  background-color: #ffffff;
  padding-top: 50px;
}

.container {
  max-width: 100%;
  margin: 0;
  padding: 0;
}

.layout {
  display: flex;
  gap: 0;
  align-items: stretch;
  /* Thêm dòng này: Bắt buộc 2 cột cao bằng nhau */
}

/* Custom Scrollbar */
.filter-panel::-webkit-scrollbar {
  width: 6px;
}

.filter-panel::-webkit-scrollbar-thumb {
  background: #555;
  border-radius: 3px;
}

.filter-panel::-webkit-scrollbar-track {
  background: #000;
}

/* Header Filter */
.filter-header h2 {
  font-size: 14px;
  font-weight: 400;
  margin: 0 0 20px 0;
  display: flex;
  align-items: center;
  gap: 10px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #ffffff;
}

.icon {
  width: 18px;
  height: 18px;
}

/* Apply Button */
.apply-btn {
  width: 100%;
  padding: 14px;
  background: #ffffff;
  color: #000000;
  border: none;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  margin-bottom: 30px;
  transition: all 0.3s ease;
  border-radius: 4px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.apply-btn:hover {
  background: #000000;
  color: #ffffff;
  border: 1px solid #ffffff;
  box-shadow: 0 2px 8px rgba(255, 255, 255, 0.2);
}

/* Filter sections */
.filter-section {
  margin-bottom: 40px;
  padding-bottom: 40px;
  border-bottom: 1px solid #333333;
}

.filter-section:last-of-type {
  border-bottom: none;
}

.filter-section h3 {
  font-size: 12px;
  font-weight: 400;
  margin: 0 0 20px 0;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: #bbbbbb;
}

/* Price Inputs */
.price-inputs {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.input {
  width: 100%;
  padding: 12px 10px;
  border: none;
  border-bottom: 1px solid #333333;
  background: transparent;
  font-size: 14px;
  color: #ffffff;
  transition: border-color 0.3s, background 0.3s;
}

.input::placeholder {
  color: #777777;
}

.input:focus {
  outline: none;
  border-bottom-color: #ffffff;
  background: rgba(255, 255, 255, 0.05);
}

/* Size Buttons */
.size-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.size-btn {
  padding: 8px 16px;
  border: 1px solid #333333;
  background: transparent;
  color: #ffffff;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 12px;
  letter-spacing: 1px;
  border-radius: 4px;
}

.size-btn:hover {
  border-color: #ffffff;
  background: #ffffff;
  color: #000000;
}

.size-btn.active {
  background: #ffffff;
  color: #000000;
  border-color: #ffffff;
}

/* Color Filter */
.color-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.color-item {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.color-item:hover .color-box {
  transform: scale(1.1);
}

.color-item input {
  width: 16px;
  height: 16px;
  cursor: pointer;
  accent-color: #ffffff;
}

.color-box {
  width: 20px;
  height: 20px;
  border: 1px solid #333333;
  border-radius: 3px;
  transition: transform 0.2s ease;
}

.color-item span {
  font-size: 12px;
  letter-spacing: 0.5px;
  color: #ffffff;
}

/* --- MAIN CONTENT --- */
.main-content {
  flex: 1;
  background: #ffffff;
  padding: 100px 60px;
  display: flex;
  flex-direction: column;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 60px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e0e0e0;
}

.header h1 {
  font-size: 32px;
  font-weight: 300;
  margin: 0;
  letter-spacing: 4px;
  text-transform: uppercase;
  color: #000000;
}

.toggle-filter-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #000000;
  color: #ffffff;
  border: none;
  cursor: pointer;
  font-size: 11px;
  letter-spacing: 1px;
  text-transform: uppercase;
}

/* Products Grid */
.products-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 24px;                
  background: transparent;  
  border: none;
  margin-bottom: 40px;
}

/* Tablet / laptop nhỏ: 3 cột */
@media (max-width: 1400px) {
  .products-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

/* Tablet dọc / mobile ngang: 2 cột */
@media (max-width: 1023px) {
  .products-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

/* Mobile rất nhỏ: 1 cột */
@media (max-width: 375px) {
  .products-grid {
    grid-template-columns: 1fr;
  }
}
.product-card {
  background: #ffffff;
  border-radius: 10px;
  border: 1px solid #e5e5e5;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  position: relative;
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.product-card:hover {
  transform: translateY(-4px);
  border-color: #d0d0d0;
  box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
}

.product-card:hover .product-image img {
  transform: scale(1.05);
}

.product-image {
  position: relative;
  background: #f5f5f5;
  aspect-ratio: 3 / 4;   
  overflow: hidden;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* Badge "NỔI BẬT" giữ như cũ */
.badge {
  position: absolute;
  top: 10px;
  right: 10px;
  background: #000000;
  color: #ffffff;
  font-size: 10px;
  padding: 6px 10px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
}

/* Nội dung card */
.product-info {
  padding: 16px 16px 14px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

/* Tên sản phẩm: 2 dòng, đều nhau */
.product-name {
  font-size: 16px;
  font-weight: 500;
  color: #000;
  margin: 0 0 10px 0;
  line-height: 1.5;
  min-height: 48px;                  /* giữ chiều cao ổn định cho 2 dòng */
  display: -webkit-box;
  -webkit-line-clamp: 2;             /* tối đa 2 dòng */
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Giá giữ style hiện tại, chỉ chỉnh chút margin */
.product-price {
  margin-bottom: 10px;
}

.price-sale {
  display: flex;
  align-items: baseline;
  gap: 8px;
}

.sale-price {
  font-size: 18px;
  font-weight: 500;
  color: #ff0000;
}

.original-price {
  font-size: 16px;
  color: #999999;
  text-decoration: line-through;
}

.price {
  font-size: 18px;
  font-weight: 500;
  color: #ff0000;
}

.size-tag {
  font-size: 10px;
  background: #f5f5f5;
  padding: 6px 10px;
  letter-spacing: 1px;
  border: 1px solid #e0e0e0;
}

.product-colors {
  display: flex;
  gap: 6px;
  margin-bottom: 20px;
}

.color-circle {
  width: 20px;
  height: 20px;
  border: 1px solid #e0e0e0;
}
/* ===== NHÓM NÚT HÀNH ĐỘNG ===== */
.product-actions {
  display: flex;
  gap: 8px;
  margin-top: 14px;
}

/* Base button cho 2 nút */
.product-actions .quick-view-btn,
.product-actions .detail-btn {
  flex: 1;
  padding: 10px 0;
  font-size: 11px;
  letter-spacing: 1.8px;
  text-transform: uppercase;
  border: 1px solid #000;
  background: #ffffff;
  color: #000000;
  cursor: pointer;
  transition: all 0.2s ease;
}

/* Nút Xem nhanh: nền trắng, hover đen */
.product-actions .quick-view-btn:hover {
  background: #000000;
  color: #ffffff;
}

/* Nút Xem chi tiết: mặc định nền đen */
.product-actions .detail-btn {
  background: #000000;
  color: #ffffff;
}
.product-actions .detail-btn:hover {
  background: #ffffff;
  color: #000000;
}

/* Mobile: cho 2 nút xếp dọc để dễ bấm */
@media (max-width: 768px) {
  .product-actions {
    flex-direction: column;
  }
}

/* Loading & Empty State */
.loading {
  text-align: center;
  padding: 60px 0;
}

.spinner {
  display: inline-block;
  width: 40px;
  height: 40px;
  border: 2px solid #e0e0e0;
  border-top: 2px solid #000000;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

.loading p,
.no-products p {
  margin-top: 16px;
  color: #999999;
  font-size: 11px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
}

.no-products {
  text-align: center;
  padding: 100px 0;
}

/* --- PAGINATION (NEW) --- */
.pagination-container {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 40px;
  margin-bottom: 20px;
  gap: 12px;
}

.page-numbers {
  display: flex;
  gap: 8px;
}

.page-btn {
  height: 40px;
  min-width: 40px;
  padding: 0 12px;
  border: 1px solid #e0e0e0;
  background-color: #ffffff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 400;
  font-size: 14px;
  color: #000000;
  transition: all 0.3s ease;
}

/* Hover effect cho nút phân trang */
.page-btn:hover:not(:disabled) {
  border-color: #000000;
  background-color: #f9f9f9;
}

/* Trang đang active */
.page-btn.active {
  background-color: #000000;
  color: #ffffff;
  border-color: #000000;
}

/* Nút bị disable (khi ở trang đầu/cuối) */
.page-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
  border-color: #f0f0f0;
}

/* Dấu ba chấm */
.page-btn.dots {
  border: none;
  cursor: default;
  background: none;
  font-weight: bold;
  letter-spacing: 2px;
}

.page-btn svg {
  width: 16px;
  height: 16px;
}

/* --- RESPONSIVE --- */
/* ... Giữ nguyên các phần CSS chung ở trên ... */

/* --- RESPONSIVE OPTIMIZATION --- */

/* 1. Tablet & Màn hình laptop nhỏ (1024px - 1400px) */
@media (max-width: 1400px) {
  .products-grid {
    grid-template-columns: repeat(3, 1fr);
    /* 3 cột */
  }
}

/* 2. Tablet dọc & Mobile ngang (768px - 1023px) */
@media (max-width: 1023px) {
  .layout {
    position: relative;
  }
  .filter-header {
    display: flex;
    justify-content: space-between;
    /* Đẩy chữ Bộ lọc và nút X ra 2 bên */
    align-items: center;
  }
  .filter-panel {
    top: 0;
    height: 100%;
    padding-top: 60px;
    /* Chừa chỗ cho nút đóng nếu có */
  }

  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    /* 2 cột */
  }

  .main-content {
    padding: 30px;
  }
}

/* 3. Mobile (Dưới 768px) */
@media (max-width: 768px) {
  .products-page {
    padding-top: 100px;
    /* Giảm khoảng cách top */
  }

  /* Header trang (Tiêu đề + Nút lọc) */
  .header {
    flex-direction: row;
    /* Giữ ngang hàng */
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 15px;
    gap: 10px;
  }

  .header h1 {
    font-size: 18px;
    /* Giảm size tiêu đề */
    letter-spacing: 1px;
    font-weight: 600;
  }

  .toggle-filter-btn {
    padding: 8px 12px;
    font-size: 10px;
    white-space: nowrap;
  }

  /* Main Content */
  .main-content {
    padding: 15px 10px;
    /* Giảm padding lề */
  }

  /* Grid Sản phẩm - Quan trọng: Giữ 2 cột trên mobile */
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1px;
    /* Giữ gap nhỏ tạo hiệu ứng border */
    border-top: 1px solid #e0e0e0;
    border-left: 1px solid #e0e0e0;
  }

  /* Tinh chỉnh thẻ sản phẩm trên mobile */
  .product-info {
    padding: 12px 10px;
    /* Giảm padding trong thẻ */
  }

  .product-name {
    font-size: 13px;
    /* Chữ tên nhỏ lại */
    min-height: 40px;
    /* Giảm chiều cao tối thiểu */
    margin-bottom: 8px;
    font-weight: 500;
    -webkit-line-clamp: 2;
  }

  .price-sale,
  .product-price {
    margin-bottom: 8px;
    flex-wrap: wrap;
    /* Cho phép giá xuống dòng nếu quá dài */
    gap: 6px;
  }

  .sale-price,
  .original-price,
  .price {
    font-size: 14px;
    /* Giá nhỏ lại */
  }

  /* Ẩn bớt size/color trên mobile nếu cần cho gọn, hoặc thu nhỏ */
  .size-tag {
    font-size: 9px;
    padding: 4px 6px;
  }

  .color-circle {
    width: 16px;
    height: 16px;
  }

  .badge {
    font-size: 8px;
    padding: 4px 8px;
  }

  /* Nút xem chi tiết */
  .detail-btn {
    padding: 8px;
    font-size: 9px;
  }

  /* Pagination trên mobile */
  .pagination-container {
    gap: 8px;
    margin-top: 30px;
  }

  .page-btn {
    height: 32px;
    min-width: 32px;
    font-size: 12px;
    padding: 0 6px;
  }
}

/* 4. Màn hình rất nhỏ (iPhone 5/SE - Dưới 375px) */
@media (max-width: 375px) {
  .products-grid {
    grid-template-columns: 1fr;
    /* Về 1 cột nếu màn hình quá bé */
  }

  .header h1 {
    font-size: 16px;
  }
}

/* ===============================
   QUICK VIEW MODAL (XEM NHANH)
   =============================== */

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal {
  background: #ffffff;
  max-width: 980px;
  width: calc(100% - 40px);
  border-radius: 10px;
  box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
  position: relative;
  padding: 28px 32px 24px;
  animation: modal-fade-in 0.2s ease-out;
}

/* nút đóng */
.modal-close {
  position: absolute;
  top: 14px;
  right: 14px;
  width: 32px;
  height: 32px;
  border-radius: 999px;
  border: none;
  background: #f4f4f4;
  color: #000;
  font-size: 20px;
  line-height: 1;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}
.modal-close:hover {
  background: #000;
  color: #fff;
}

/* layout 2 cột */
.modal-body {
  display: grid;
  grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
  gap: 32px;
  align-items: flex-start;
}

/* cột ảnh */
.modal-image {
  width: 100%;
}

.modal-main-image {
  width: 100%;
  max-height: 420px;
  object-fit: cover;
  border-radius: 8px;
  background: #f5f5f5;
}

/* list ảnh nhỏ */
.thumb-list {
  display: flex;
  gap: 8px;
  margin-top: 12px;
  overflow-x: auto;
}
.thumb {
  flex: 0 0 64px;
  width: 64px;
  height: 64px;
  border-radius: 6px;
  border: 1px solid transparent;
  background: #f5f5f5;
  object-fit: cover;
  cursor: pointer;
  transition: all 0.2s ease;
}
.thumb:hover {
  transform: translateY(-2px);
}
.thumb.active {
  border-color: #000;
}

/* cột thông tin */
.modal-info {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.modal-title {
  font-size: 20px;
  font-weight: 500;
  margin: 0;
  color: #000;
}

/* giá */
.modal-price {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 4px;
}
.modal-price .sale-price {
  font-size: 20px;
  font-weight: 500;
  color: #ff0000;
}
.modal-price span {
  font-size: 18px;
}
.modal-price .original-price {
  color: #999;
  text-decoration: line-through;
}

/* mô tả */
.modal-desc-toggle {
  margin-top: 4px;
  font-size: 13px;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  color: #111;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.modal-desc-toggle:hover {
  text-decoration: underline;
}

.modal-desc {
  margin: 4px 0 0;
  font-size: 14px;
  line-height: 1.6;
  color: #555;
}

/* block nhỏ (màu, size, số lượng) */
.modal-block {
  margin-top: 10px;
  font-size: 14px;
}

.modal-block p {
  margin: 0 0 8px 0;
}

/* màu */
.color-options {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.color-option {
  width: 26px;
  height: 26px;
  border-radius: 999px;
  border: 1px solid #ddd;
  cursor: pointer;
  transition: all 0.2s ease;
}
.color-option:hover {
  transform: translateY(-1px);
  border-color: #000;
}
.color-option.active {
  border-color: #000;
  box-shadow: 0 0 0 1px #000;
}

/* size */
.size-options {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

/* nút size trong modal (dùng class modal-size-btn như ta đã đổi) */
.modal-size-btn {
  min-width: 48px;
  padding: 8px 14px;
  border-radius: 4px;
  border: 1px solid #ddd;
  background: #fff;
  color: #000;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
}
.modal-size-btn:hover {
  border-color: #000;
}
.modal-size-btn.active {
  background: #000;
  color: #fff;
  border-color: #000;
}

/* link hướng dẫn size */
.size-guide-link {
  margin-left: 8px;
  font-size: 12px;
  color: #1e88e5;
  text-decoration: underline;
}

/* số lượng */
.quantity-container {
  display: inline-flex;
  align-items: center;
  border: 1px solid #ddd;
  border-radius: 4px;
  overflow: hidden;
}
.qty-btn {
  width: 32px;
  height: 40px;
  border: none;
  background: #f6f6f6;
  cursor: pointer;
  font-size: 18px;
}
.qty-btn:hover {
  background: #e9e9e9;
}
.qty-input {
  width: 54px;
  height: 40px;
  border: none;
  border-left: 1px solid #ddd;
  border-right: 1px solid #ddd;
  text-align: center;
  font-size: 14px;
}

/* nút thêm giỏ */
.add-cart-btn {
  margin-top: 18px;
  width: 100%;
  padding: 13px 20px;
  background: #000;
  color: #fff;
  border: none;
  text-transform: uppercase;
  letter-spacing: 2px;
  font-size: 11px;
  cursor: pointer;
  transition: all 0.2s ease;
}
.add-cart-btn:hover:not(.disabled-btn) {
  background: #111;
}
.add-cart-btn.disabled-btn {
  opacity: 0.5;
  cursor: default;
}

/* link xem chi tiết */
.detail-link-wrap {
  margin-top: 12px;
  text-align: center;
}
.detail-link-wrap a {
  font-size: 13px;
  color: #1e88e5;
  text-decoration: underline;
}

/* animation nhỏ cho modal */
@keyframes modal-fade-in {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* RESPONSIVE MODAL */
@media (max-width: 900px) {
  .modal {
    padding: 22px 18px 18px;
  }
  .modal-body {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  .modal-main-image {
    max-height: 340px;
  }
}

@media (max-width: 600px) {
  .modal {
    width: 100%;
    height: 100%;
    max-width: none;
    border-radius: 0;
    padding: 18px 14px 16px;
  }
  .modal-body {
    gap: 16px;
  }
  .modal-title {
    font-size: 18px;
  }
  .modal-price .sale-price,
  .modal-price span {
    font-size: 16px;
  }
}
/* ========== TOAST GIỮA MÀN HÌNH ========== */

.center-toast {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: #111;
  color: #fff;
  padding: 14px 28px;
  border-radius: 4px;
  font-size: 15px;
  text-align: center;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
  z-index: 10000; /* cao hơn modal overlay */
}

/* hiệu ứng fade nhỏ */
.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.toast-fade-enter-from,
.toast-fade-leave-to {
  opacity: 0;
  transform: translate(-50%, -46%);
}

</style>