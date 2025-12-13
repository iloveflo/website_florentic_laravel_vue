<template>
  <div class="products-page">
    <div class="container">
      <div class="layout">
        <div class="products-page">
          <div class="sidebar-overlay" :class="{ active: showFilters }" @click="showFilters = false"></div>

          <aside class="sidebar" :class="{ hidden: !showFilters }" @click.stop>
            <div class="filter-panel">
              <div class="filter-header">
                <h2>
                  <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                  </svg>
                  Bộ lọc
                </h2>
                <button class="close-sidebar-btn" @click="showFilters = false">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
                </button>
              </div>

              <button @click="applyFilters" class="apply-btn" style="margin: 10px 0;">
                Áp dụng
              </button>

              <div class="filter-section">
                <h3>Khoảng giá - VND</h3>
                <div class="price-inputs">
                  <input v-model="filters.minPrice" type="number" placeholder="Từ" class="input" />
                  <input v-model="filters.maxPrice" type="number" placeholder="Đến" class="input" />
                </div>
              </div>

              <div class="filter-section">
                <h3>Kích cỡ</h3>
                <div class="size-buttons">
                  <button v-for="size in availableSizes" :key="size" @click="toggleFilter('sizes', size)"
                    :class="{ active: filters.sizes.includes(size) }" class="size-btn">
                    {{ size }}
                  </button>
                </div>
              </div>

              <div class="filter-section">
                <h3>Màu sắc</h3>
                <div class="color-list">
                  <label v-for="(color, idx) in availableColors" :key="idx" class="color-item">
                    <input type="checkbox" :checked="filters.colors.includes(color.name)"
                      @change="toggleFilter('colors', color.name)" />
                    <div class="color-box" :style="{ backgroundColor: color.code }" />
                    <span>{{ color.name }}</span>
                  </label>
                </div>
              </div>
            </div>
          </aside>
        </div>


        <!-- Products Grid -->
        <main class="main-content">
          <div class="header">
            <h1>{{ categoryName || 'Sản phẩm' }}</h1>
            <button @click="showFilters = !showFilters" class="toggle-filter-btn">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
              </svg>
              {{ showFilters ? 'Ẩn bộ lọc' : 'Hiện bộ lọc' }}
            </button>
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
            <p>Đang tải...</p>
          </div>

          <div v-if="!loading && products.length === 0" class="no-products">
            <p>Không tìm thấy sản phẩm nào</p>
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
          <!-- QUICK VIEW MODAL -->
          <div v-if="showQuickView && quickViewProduct" class="modal-overlay" @click.self="closeQuickView">
            <div class="modal">
              <button class="modal-close" @click="closeQuickView">×</button>

              <div class="modal-body">
                <!-- Cột ảnh -->
                <div class="modal-image">
                  <img :src="quickMainImage" :alt="quickViewProduct.name" class="modal-main-image"
                    @error="onQuickImageError" />

                  <div class="thumb-list" v-if="quickViewProduct.images?.length">
                    <img v-for="img in quickViewProduct.images" :key="img.id" :src="img.image_path" class="thumb"
                      :class="{ active: img.image_path === quickMainImage }" @click="quickMainImage = img.image_path"
                      @error="onQuickImageError" />
                  </div>
                </div>

                <!-- Cột thông tin -->
                <div class="modal-info">
                  <h2 class="modal-title">{{ quickViewProduct.name }}</h2>

                  <!-- Giá -->
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

                  <!-- Mô tả -->
                  <p class="modal-desc-toggle" @click="showFullDesc = !showFullDesc">
                    Mô tả sản phẩm {{ showFullDesc ? '⯅' : '⯆' }}
                  </p>
                  <p v-if="showFullDesc" class="modal-desc">
                    {{ quickViewProduct.description || 'Chưa có mô tả.' }}
                  </p>

                  <!-- Màu sắc -->
                  <div v-if="quickViewProduct.colors?.length" class="modal-block">
                    <p><strong>Chọn màu:</strong></p>
                    <div class="color-options">
                      <button v-for="c in quickViewProduct.colors" :key="c.id" class="color-option"
                        :style="{ backgroundColor: c.color_code }" :class="{ active: selectedColor === c.color_name }"
                        @click="selectedColor = c.color_name" :title="c.color_name"></button>
                    </div>
                  </div>

                  <!-- Size -->
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

                  <!-- Số lượng -->
                  <div class="modal-block">
                    <p><strong>Số lượng:</strong></p>
                    <div class="quantity-container">
                      <button class="qty-btn" @click="decreaseQty">–</button>
                      <input type="number" class="qty-input" v-model.number="quantity" min="1" />
                      <button class="qty-btn" @click="increaseQty">+</button>
                    </div>
                  </div>

                  <!-- Thêm giỏ -->
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
    </div>
    <!-- TOAST THÔNG BÁO GIỮA MÀN HÌNH -->
    <transition name="toast-fade">
      <div v-if="isToastVisible" class="center-toast">
        {{ toastMessage }}
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch, onBeforeUnmount } from 'vue'; // 1. Thêm watch
import { useRouter, useRoute } from 'vue-router'; // 2. Thêm useRoute
import axios from 'axios';

const router = useRouter();
const route = useRoute(); // 3. Khởi tạo route để lấy params

const products = ref([]);
const loading = ref(false);

// Phân trang
const currentPage = ref(1);
const totalPages = ref(0);
const itemsPerPage = 20;

const showFilters = ref(true);

// Biến để hiển thị tên danh mục trên tiêu đề (tùy chọn)
const categoryName = ref('');

const filters = reactive({
  minPrice: '',
  maxPrice: '',
  sizes: [],
  colors: []
});

const availableSizes = ref(['S', 'M', 'L', 'XL', 'XXL']);
const availableColors = ref([]);

// --- LOGIC PHÂN TRANG MỚI (Giữ nguyên) ---
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

// --- FETCH PRODUCTS (ĐÃ SỬA) ---
const fetchProducts = async (page = 1) => {
  if (loading.value) return;
  loading.value = true;

  try {
    // 1. Chuẩn bị tham số
    const params = new URLSearchParams({
      page: page,
      per_page: itemsPerPage
    });

    // Thêm filter
    if (filters.minPrice) params.append('min_price', filters.minPrice);
    if (filters.maxPrice) params.append('max_price', filters.maxPrice);
    if (filters.sizes.length > 0) params.append('sizes', filters.sizes.join(','));
    if (filters.colors.length > 0) params.append('colors', filters.colors.join(','));

    // 2. Lấy slug
    const slug = route.params.slug;

    // Kiểm tra nhanh: Nếu slug bị undefined thì không gọi API để tránh lỗi
    if (!slug) {
      console.error("Lỗi: Không tìm thấy slug trên URL");
      loading.value = false;
      return;
    }

    // 3. Log đường dẫn ra để kiểm tra (Xem trong Console F12)
    const apiUrl = `/api/products/category/${slug}?${params.toString()}`;

    const response = await fetch(apiUrl);

    // 4. Kiểm tra xem Server có trả về lỗi HTML không (Tránh lỗi Unexpected token <)
    const contentType = response.headers.get("content-type");
    if (contentType && contentType.indexOf("application/json") === -1) {
      // Nếu server trả về HTML, log nội dung ra để biết lỗi gì
      const text = await response.text();
      throw new Error("Sai đường dẫn API hoặc lỗi Server 404/500");
    }

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    // 5. Gán dữ liệu (Dựa theo JSON bạn vừa gửi)
    // JSON của bạn: { products: { data: [...], current_page: 1, ... } }

    if (data.products && data.products.data) {
      products.value = data.products.data;
      currentPage.value = data.products.current_page;
      totalPages.value = data.products.last_page;

      // Cập nhật tên danh mục nếu muốn
      if (data.category) {
        categoryName.value = data.category.name;
      }

      // Xử lý màu sắc
      // Xử lý màu sắc từ variants
      const colors = new Set();
      data.products.data.forEach(product => {
        product.variants?.forEach(variant => {
          if (variant.color_name || variant.color_code) {
            colors.add(JSON.stringify({
              name: variant.color_name,
              code: variant.color_code
            }));
          }
        });
      });

      if (availableColors.value.length === 0) {
        availableColors.value = Array.from(colors).map(c => JSON.parse(c));
      }
    } else {
      products.value = []; // Trường hợp không có dữ liệu
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });

  } catch (error) {
    console.error('Chi tiết lỗi fetch:', error);
  } finally {
    loading.value = false;
  }
};

const gotoDetail = (product) => {
  router.push({
    name: 'product-details',
    params: { slug: product.slug },
  })
}

// Hàm kiểm tra giá
const isPriceValid = () => {
  const min = Number(filters.minPrice);
  const max = Number(filters.maxPrice);

  if (filters.minPrice && min < 0) {
    alert('Giá tối thiểu không được âm.');
    return false;
  }

  if (filters.minPrice && filters.maxPrice && min > max) {
    alert('Khoảng giá không hợp lệ: giá từ phải nhỏ hơn hoặc bằng giá đến.');
    return false;
  }

  return true;
};

const applyFilters = () => {
  if (!isPriceValid()) return;
  currentPage.value = 1;
  fetchProducts(1);
};

const toggleFilter = (type, value) => {
  if (filters[type].includes(value)) {
    filters[type] = filters[type].filter(item => item !== value);
  } else {
    filters[type].push(value);
  }
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price);
};

// 5. Watcher: Quan trọng để phát hiện thay đổi Route
// Khi user click từ "Áo thun" sang "Áo sơ mi", component không reload lại
// mà chỉ update params, nên cần watch để fetch lại dữ liệu.
watch(
  () => route.params.slug,
  (newSlug, oldSlug) => {
    // Chỉ fetch lại nếu slug thực sự thay đổi và có giá trị
    if (newSlug !== oldSlug) {
      // Có thể reset filter nếu muốn khi chuyển danh mục
      // filters.minPrice = ''; filters.maxPrice = ''; ...

      currentPage.value = 1; // Reset về trang 1
      fetchProducts(1);
    }
  }
);

// Ảnh chính của sản phẩm
const getProductImage = (product) => {
  // Nếu sau này API có primary_image_url thì ưu tiên dùng
  if (product.primary_image_url) return product.primary_image_url

  if (product.images && product.images.length) {
    const main = product.images.find(img => img.is_primary) || product.images[0]

    // Model ProductImage đã có accessor "url"
    if (main.url) return main.url

    // fallback: ghép path nếu chỉ có image_path
    if (main.image_path) return `/${main.image_path}`
  }

  // fallback cuối cùng
  return 'https://via.placeholder.com/300x300?text=No+Image'
}

// Lấy list size từ variants (unique)
const getProductSizes = (product) => {
  if (!product.variants || !product.variants.length) return []

  const set = new Set()
  product.variants.forEach(v => {
    if (v.size) set.add(v.size)
  })

  return Array.from(set) // ['S','M','L',...]
}

// Lấy list màu (unique theo color_name / color_code)
const getProductColors = (product) => {
  if (!product.variants || !product.variants.length) return []

  const map = new Map()
  product.variants.forEach(v => {
    const key = v.color_name || v.color_code
    if (!key) return
    if (!map.has(key)) {
      map.set(key, {
        name: v.color_name,
        code: v.color_code
      })
    }
  })

  return Array.from(map.values()) // [{name, code}, ...]
}

onMounted(() => {
  fetchProducts(1);
});

/* ======================================================
   QUICK VIEW MODAL
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

    // map variants -> sizes, colors
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
      const mainImg =
        detail.images?.find((img) => img.is_primary) ||
        detail.images?.[0]

      quickMainImage.value =
        mainImg?.url ||
        mainImg?.image_path ||
        getProductImage(detail)
    }
  } catch (err) {
    console.error('Lỗi openQuickView:', err)
    showToast('Không tải được thông tin sản phẩm')
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

// session_id
const getSessionId = () => {
  let sessionId = localStorage.getItem('cart_session_id')
  if (!sessionId) {
    sessionId = 'sess_' + Math.random().toString(36).substr(2, 9) + Date.now()
    localStorage.setItem('cart_session_id', sessionId)
  }
  return sessionId
}

// tồn kho
const getAvailableStock = (p, size, color) => {
  if (!p) return null

  if (p.variants && p.variants.length) {
    const variant = p.variants.find(v => {
      const sameSize  = !size  || v.size === size
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

  const hasSizeOptions =
    (p.sizes && p.sizes.length) ||
    (p.variants && p.variants.some((v) => v.size))

  if (hasSizeOptions && !selectedSize.value) {
    showToast('Vui lòng chọn kích thước!')
    return
  }

  const hasColorOptions =
    (p.colors && p.colors.length) ||
    (p.variants && p.variants.some((v) => v.color_name || v.color_code))

  if (hasColorOptions && !selectedColor.value) {
    showToast('Vui lòng chọn màu sắc!')
    return
  }

  const stock = getAvailableStock(
    p,
    selectedSize.value || null,
    selectedColor.value || null
  )

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
    console.error('Lỗi thêm giỏ hàng (xem nhanh - category):', error)
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

/* TOAST */
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

/* --- SIDEBAR --- */
.sidebar {
  width: 280px;
  background: #000000;
  transition: all 0.4s ease;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
}

.sidebar.hidden {
  width: 0;
}

/* Filter panel scrollable */
.filter-panel {
  background: #000000;
  padding: 40px 30px;
  position: sticky;
  top: 80px;
  color: #ffffff;
  height: calc(100vh - 80px);
  overflow-y: auto;
  scroll-behavior: smooth;
}

@media (min-width: 1024px) {
  .sidebar {
    position: sticky;
    top: 80px;
    height: calc(100vh - 80px);
    overflow-y: auto;
  }
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

.close-sidebar-btn {
  display: none;
  /* Ẩn trên desktop */
  background: none;
  border: none;
  cursor: pointer;
  color: #fff;
}

.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1999;
  /* Nằm dưới sidebar */
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s;
}

.sidebar-overlay.active {
  display: none;
  /* Desktop không cần overlay */
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
  min-height: 48px;
  /* giữ chiều cao ổn định cho 2 dòng */
  display: -webkit-box;
  -webkit-line-clamp: 2;
  /* tối đa 2 dòng */
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

.product-sizes {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  margin-bottom: 16px;
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

  /* Chuyển Sidebar thành dạng Drawer (Trượt) */
  /* Hiển thị nút đóng và overlay */
  .close-sidebar-btn {
    display: block;
  }

  .sidebar-overlay.active {
    display: block;
    opacity: 1;
    visibility: visible;
  }

  .filter-header {
    display: flex;
    justify-content: space-between;
    /* Đẩy chữ Bộ lọc và nút X ra 2 bên */
    align-items: center;
  }

  /* Cấu hình Sidebar dạng Drawer (Ngăn kéo) */
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 280px;
    background: #000;
    /* Màu nền sidebar */
    z-index: 2000;
    box-shadow: 4px 0 15px rgba(0, 0, 0, 0.3);

    /* Trạng thái HIỆN (showFilters = true) */
    transform: translateX(0);
    transition: transform 0.3s ease-in-out;
  }

  /* Trạng thái ẨN (showFilters = false -> có class .hidden) */
  .sidebar.hidden {
    transform: translateX(-100%);
    /* Trượt hẳn ra khỏi màn hình bên trái */
    width: 280px;
    /* Giữ nguyên width để animation mượt */
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