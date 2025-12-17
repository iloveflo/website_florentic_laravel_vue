<template>
  <div class="product-details-page" v-if="product">
    <div class="top-section">
      <div class="image-section">
        <div class="main-image-container" @mousemove="onMouseMoveZoom" @mouseenter="startZoom" @mouseleave="stopZoom">
          <div class="main-image-wrapper">
            <transition :name="slideDirection">
              <img :key="mainImage" :src="mainImage" alt="" class="main-image" :class="{ 'is-zooming': isZooming }"
                :style="zoomStyle" />
            </transition>
          </div>
        </div>

        <div class="thumb-list">
          <img v-for="(img, index) in validImages" :key="img.id || index" :src="getImageSrc(img)" class="thumb"
            :class="{ active: index === currentImageIndex }" @click="changeImage(index)" />
        </div>
      </div>

      <div class="info-section">
        <h2 class="title">{{ product.name }}</h2>

        <div class="price-wrapper">
          <span v-if="isSale" class="sale-price">
            {{ formatPrice(product.sale_price) }}
          </span>
          <span :class="{ 'original-price': isSale }">
            {{ formatPrice(product.price) }}
          </span>
        </div>

        <div class="section-block" v-if="sizeOptions.length">
          <h4>Kích thước</h4>
          <div class="size-list">
            <button v-for="s in sizeOptions" :key="s" class="size-btn" :class="{ active: selectedSize === s }"
              @click="selectedSize = s">
              {{ s }}
            </button>
          </div>
        </div>

        <div class="section-block" v-if="colorOptions.length">
          <h4>Màu sắc</h4>
          <div class="color-list">
            <div v-for="c in colorOptions" :key="c.name || c.code" class="color-circle"
              :style="{ backgroundColor: c.code }" :class="{ active: selectedColor === (c.name || c.code) }"
              @click="selectedColor = c.name || c.code" :title="c.name"></div>
          </div>
        </div>

        <div class="section-block">
          <h4>Số lượng</h4>
          <div class="qty-box">
            <button @click="decreaseQty">-</button>
            <input type="number" v-model="quantity" />
            <button @click="increaseQty">+</button>
          </div>
        </div>

        <button class="add-cart-btn" @click="addToCart" :disabled="isAdding" :class="{ 'disabled-btn': isAdding }">
          <span v-if="isAdding">Đang xử lý...</span>
          <span v-else>Thêm vào giỏ hàng</span>
        </button>
        <div class="product-accordion">
          <div class="accordion-section">
            <button class="accordion-header" @click="openDesc = !openDesc">
              <span>Mô tả</span>
              <span class="toggle-icon">{{ openDesc ? '−' : '+' }}</span>
            </button>
            <div class="accordion-content" v-show="openDesc">
              <p>
                {{ product.description || 'Chưa có mô tả cho sản phẩm này.' }}
              </p>
            </div>
          </div>

          <div class="accordion-section">
            <button class="accordion-header" @click="openMaterial = !openMaterial">
              <span>Chất liệu</span>
              <span class="toggle-icon">{{ openMaterial ? '−' : '+' }}</span>
            </button>
            <div class="accordion-content" v-show="openMaterial">
              <p>65% cotton, 35% polyester. Chất vải mềm, ít nhăn, giữ form tốt khi sử dụng hàng ngày.</p>
            </div>
          </div>

          <div class="accordion-section">
            <button class="accordion-header" @click="openUsage = !openUsage">
              <span>Hướng dẫn sử dụng</span>
              <span class="toggle-icon">{{ openUsage ? '−' : '+' }}</span>
            </button>
            <div class="accordion-content" v-show="openUsage">
              <ul>
                <li>Giặt máy ở chế độ nhẹ, nhiệt độ thường.</li>
                <li>Không sử dụng hóa chất tẩy có chứa Clo.</li>
                <li>Phơi trong bóng mát, tránh ánh nắng gắt.</li>
                <li>Không sử dụng máy sấy.</li>
                <li>Là ở nhiệt độ thấp (tối đa 110°C).</li>
                <li>Giặt riêng quần áo tối màu. Không ngâm lâu.</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="purchase-policy">
          <div class="policy-item">
            <div class="policy-icon-circle">💳</div>
            <div>
              <strong>Thanh toán khi nhận hàng (COD)</strong><br />
              Giao hàng toàn quốc.
            </div>
          </div>

          <div class="policy-item">
            <div class="policy-icon-circle">🚚</div>
            <div>
              <strong>Miễn phí giao hàng</strong><br />
              Với đơn hàng trên 599.000 ₫.
            </div>
          </div>

          <div class="policy-item">
            <div class="policy-icon-circle">🔁</div>
            <div>
              <strong>Đổi hàng miễn phí</strong><br />
              Trong 30 ngày kể từ ngày mua.
            </div>
          </div>
        </div>

      </div>
    </div>

    <div class="reviews-section">
      <h3>Đánh giá sản phẩm</h3>

      <div v-if="isLoadingReviews">Đang tải đánh giá...</div>

      <div v-else-if="reviewsData && reviewsData.data && reviewsData.data.length > 0">
        <div v-for="rv in reviewsData.data" :key="rv.id" class="review-card">
          <div class="star-rating">
            <span v-for="star in 5" :key="star" class="star" :class="{ filled: star <= Math.round(rv.rating || 5) }">
              ★
            </span>
          </div>

          <strong>{{ rv.order?.full_name || rv.customer_name || 'Khách hàng ẩn danh' }}</strong>

          <div class="review-date">{{ formatDate(rv.created_at) }}</div>
          <p>{{ rv.comment }}</p>
        </div>

        <div class="pagination-controls" v-if="reviewsData.last_page > 1">
          <button @click="changePage(reviewsData.current_page - 1)" :disabled="reviewsData.current_page === 1"
            class="btn-page">
            &laquo;< </button>

              <span class="page-info">
                Trang {{ reviewsData.current_page }} / {{ reviewsData.last_page }}
              </span>

              <button @click="changePage(reviewsData.current_page + 1)"
                :disabled="reviewsData.current_page === reviewsData.last_page" class="btn-page">
                >&raquo;
              </button>
        </div>
      </div>

      <div v-else>
        <p>Chưa có đánh giá nào cho sản phẩm này.</p>
      </div>
    </div>
    <div v-if="relatedProducts.length" class="related-section">
      <div class="related-header">
        <h3>Sản phẩm cùng phong cách</h3>
        <div class="related-controls">
          <button class="related-arrow" @click="prevRelated">‹</button>
          <button class="related-arrow" @click="nextRelated">›</button>
        </div>
      </div>

      <div class="related-slider" @mouseenter="stopRelatedAutoSlide" @mouseleave="startRelatedAutoSlide">
        <div class="related-track" :style="{ transform: `translateX(-${relatedSlide * 100}%)` }">

          <div class="related-page" v-for="pageIndex in relatedTotalPages" :key="pageIndex">
            <div class="products-grid related-grid">
              <div v-for="card in relatedProducts.slice(
                (pageIndex - 1) * relatedPerPage,
                pageIndex * relatedPerPage
              )" :key="card.id" class="product-card">
                <div class="product-image">
                  <img :src="getImageSrc(
                    (card.images && card.images.find(i => i.is_primary)) ||
                    (card.images && card.images[0])
                  )" :alt="card.name" @error="replaceImage" />
                  <span v-if="card.featured" class="badge">Nổi bật</span>
                </div>

                <div class="product-info">
                  <h3 class="product-name">{{ card.name }}</h3>

                  <div class="product-price">
                    <div v-if="card.sale_price && Number(card.sale_price) < Number(card.price)" class="price-sale">
                      <span class="sale-price">{{ formatPrice(card.sale_price) }}</span>
                      <span class="original-price">{{ formatPrice(card.price) }}</span>
                    </div>
                    <span v-else class="price">
                      {{ formatPrice(card.price) }}
                    </span>
                  </div>

                  <div class="product-actions">
                    <button class="quick-view-btn" @click.stop="openQuickView(card)">
                      Xem nhanh
                    </button>
                    <button class="detail-btn" @click.stop="gotoRelatedDetail(card)">
                      Xem chi tiết
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showQuickView && quickViewProduct" class="modal-overlay" @click.self="closeQuickView">
      <div class="modal">
        <button class="modal-close" @click="closeQuickView">×</button>

        <div class="modal-body">
          <div class="modal-image">
            <img :src="quickMainImage" :alt="quickViewProduct.name" class="modal-main-image" @error="replaceImage" />

            <div class="thumb-list" v-if="quickViewProduct.images?.length">
              <img v-for="img in quickViewProduct.images" :key="img.id" :src="getImageSrc(img)" class="thumb" :class="{
                active: getImageSrc(img) === quickMainImage
              }" @click="quickMainImage = getImageSrc(img)" @error="replaceImage" />
            </div>
          </div>

          <div class="modal-info">
            <h2 class="modal-title">{{ quickViewProduct.name }}</h2>

            <div class="modal-price">
              <span v-if="quickViewProduct.sale_price &&
                Number(quickViewProduct.sale_price) < Number(quickViewProduct.price)" class="sale-price">
                {{ formatPrice(quickViewProduct.sale_price) }}
              </span>
              <span :class="{
                'original-price':
                  quickViewProduct.sale_price &&
                  Number(quickViewProduct.sale_price) < Number(quickViewProduct.price),
              }">
                {{ formatPrice(quickViewProduct.price) }}
              </span>
            </div>

            <p class="modal-desc-toggle" @click="showFullDescQuick = !showFullDescQuick">
              Mô tả sản phẩm {{ showFullDescQuick ? '⯅' : '⯆' }}
            </p>
            <p v-if="showFullDescQuick" class="modal-desc">
              {{ quickViewProduct.description || 'Chưa có mô tả.' }}
            </p>

            <div v-if="quickColorOptions.length" class="modal-block">
              <p><strong>Chọn màu:</strong></p>
              <div class="color-options">
                <button v-for="c in quickColorOptions" :key="c.name || c.code" class="color-option"
                  :style="{ backgroundColor: c.code }" :class="{ active: quickSelectedColor === (c.name || c.code) }"
                  @click="quickSelectedColor = c.name || c.code" :title="c.name"></button>
              </div>
            </div>

            <div v-if="quickSizeOptions.length" class="modal-block">
              <p class="label">
                <strong>Chọn size:</strong>
                <a href="/help/size-guide" target="_blank" class="size-guide-link">
                  Hướng dẫn chọn size
                </a>
              </p>
              <div class="size-options">
                <button v-for="s in quickSizeOptions" :key="s" class="modal-size-btn"
                  :class="{ active: quickSelectedSize === s }" @click="quickSelectedSize = s">
                  {{ s }}
                </button>
              </div>
            </div>

            <div class="modal-block">
              <p><strong>Số lượng:</strong></p>
              <div class="quantity-container">
                <button class="qty-btn" @click="decreaseQuickQty">–</button>
                <input type="number" class="qty-input" v-model.number="quickQuantity" min="1" />
                <button class="qty-btn" @click="increaseQuickQty">+</button>
              </div>
            </div>

            <button class="add-cart-btn" @click="addQuickToCart" :disabled="addingQuick">
              <span v-if="addingQuick">Đang xử lý...</span>
              <span v-else>Thêm vào giỏ hàng</span>
            </button>

            <div class="detail-link-wrap">
              <a href="" @click.prevent="gotoDetailFromQuick">
                Xem chi tiết sản phẩm
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <transition name="toast-fade">
      <div v-if="isToastVisible" class="center-toast">
        {{ toastMessage }}
      </div>
    </transition>
  </div>

  <div v-else class="loading">Đang tải sản phẩm...</div>
</template>

<script setup>
import { ref, onMounted, computed, onBeforeUnmount, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";

const route = useRoute();
const router = useRouter();

// ================= STATE =================
const product = ref(null);
const reviewsData = ref(null); // [NEW] Chứa data review phân trang
const isLoadingReviews = ref(false); // [NEW] Loading cho phần review

const mainImage = ref("");
const selectedSize = ref("");
const selectedColor = ref("");
const quantity = ref(1);
const isAdding = ref(false);

// Chính sách, hướng dẫn
const openDesc = ref(true);
const openMaterial = ref(false);
const openUsage = ref(false);

// Zoom ảnh
const isZooming = ref(false)
const zoomOrigin = ref('50% 50%')

// ================= API CALLS =================

/**
 * [EDIT] Hàm lấy chi tiết sản phẩm + reviews
 * Thêm tham số page, mặc định = 1
 */
const fetchProduct = async (slugValue, page = 1) => {
  isLoadingReviews.value = true;
  try {
    // Gọi API. Lưu ý endpoint nên là /api/products/ nếu bạn cấu hình route api
    const res = await axios.get(`/products/${slugValue}?page=${page}`);
    const data = res.data;

    // [EDIT] Tách dữ liệu theo cấu trúc mới của Controller
    product.value = data.product;
    reviewsData.value = data.reviews; // Gán object reviews riêng

    // Chỉ reset ảnh chính khi load trang đầu tiên (đổi sản phẩm)
    // Nếu chỉ chuyển trang review thì không cần reset ảnh
    if (page === 1) {
      if (product.value && product.value.images && product.value.images.length) {
        const main = product.value.images.find((img) => img.is_primary) || product.value.images[0];
        mainImage.value = getImageSrc(main);
        currentImageIndex.value = product.value.images.findIndex((i) => i.id === main.id);
      } else if (data.primary_image) {
        mainImage.value = getImageSrc({ image_path: data.primary_image });
      } else {
        mainImage.value = "/placeholder.jpg";
      }

      // Reset các lựa chọn
      selectedSize.value = "";
      selectedColor.value = "";
      quantity.value = 1;
      isZooming.value = false;

      // Related Products
      relatedProducts.value = data.related_products || [];
      relatedSlide.value = 0;
      startRelatedAutoSlide();
    }

  } catch (err) {
    console.error("Lỗi tải sản phẩm:", err);
  } finally {
    isLoadingReviews.value = false;
  }
};

/**
 * [NEW] Hàm chuyển trang Review
 */
const changePage = (page) => {
  if (!reviewsData.value || page < 1 || page > reviewsData.value.last_page) return;

  // Gọi lại API fetchProduct với page mới
  // Slug lấy từ route params hoặc từ product hiện tại
  const currentSlug = route.params.slug;
  fetchProduct(currentSlug, page);

  // Cuộn nhẹ xuống phần review
  const reviewSection = document.querySelector('.reviews-section');
  if (reviewSection) {
    reviewSection.scrollIntoView({ behavior: 'smooth' });
  }
};

// ================= LIFECYCLE =================
onMounted(() => {
  if (route.params.slug) {
    fetchProduct(route.params.slug);
  }
});

watch(
  () => route.params.slug,
  (newSlug, oldSlug) => {
    if (newSlug && newSlug !== oldSlug) {
      // Khi đổi sản phẩm, load lại từ trang 1
      fetchProduct(newSlug, 1);
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  }
);

// ================= HELPERS =================

// [NEW] Hàm format ngày (Sửa lỗi _ctx.formatDate)
const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('vi-VN');
};

const formatPrice = (price) =>
  Number(price).toLocaleString("vi-VN") + "₫";

const getImageSrc = (img) => {
  if (!img) return "/placeholder.jpg";
  if (img.url) return img.url;
  if (img.image_path) {
    if (img.image_path.startsWith("http")) return img.image_path;
    if (img.image_path.startsWith("/")) return img.image_path;
    return `/storage/${img.image_path}`;
  }
  return "/placeholder.jpg";
};

const validImages = computed(() =>
  (product.value?.images || []).filter(
    (img) => img && (img.image_path || img.url)
  )
);

// ... Các hàm logic khác giữ nguyên ...

const zoomStyle = computed(() => {
  if (!isZooming.value) return {}
  return {
    transform: 'scale(1.8)',
    transformOrigin: zoomOrigin.value,
  }
})

const startZoom = () => { isZooming.value = true }
const stopZoom = () => { isZooming.value = false }

const onMouseMoveZoom = (e) => {
  if (!isZooming.value) return
  const rect = e.currentTarget.getBoundingClientRect()
  const x = ((e.clientX - rect.left) / rect.width) * 100
  const y = ((e.clientY - rect.top) / rect.height) * 100
  zoomOrigin.value = `${x}% ${y}%`
}

const currentImageIndex = ref(0)
const slideDirection = ref('slide-left')

const changeImage = (newIndex) => {
  const imgs = validImages.value;
  if (!imgs.length) return;
  if (newIndex === currentImageIndex.value) return;
  slideDirection.value = newIndex > currentImageIndex.value ? "slide-left" : "slide-right";
  currentImageIndex.value = newIndex;
  mainImage.value = getImageSrc(imgs[newIndex]);
};

const isSale = computed(() => {
  if (!product.value) return false;
  if (!product.value.sale_price) return false;
  return Number(product.value.sale_price) < Number(product.value.price);
});

const getSessionId = () => {
  let sessionId = sessionStorage.getItem("cart_session_id");
  if (!sessionId) {
    sessionId = "sess_" + Math.random().toString(36).substr(2, 9) + Date.now();
    sessionStorage.setItem("cart_session_id", sessionId);
  }
  return sessionId;
};

// SIZE & COLOR OPTIONS
const sizeOptions = computed(() => {
  if (!product.value) return [];
  if (product.value.variants && product.value.variants.length) {
    const s = new Set();
    product.value.variants.forEach((v) => v.size && s.add(v.size));
    return Array.from(s);
  }
  if (product.value.sizes && product.value.sizes.length) {
    const s = new Set();
    product.value.sizes.forEach((v) => v.size && s.add(v.size));
    return Array.from(s);
  }
  return [];
});

const colorOptions = computed(() => {
  if (!product.value) return [];
  if (product.value.variants && product.value.variants.length) {
    const map = new Map();
    product.value.variants.forEach((v) => {
      const key = v.color_name || v.color_code;
      if (!key) return;
      if (!map.has(key)) map.set(key, { name: v.color_name, code: v.color_code });
    });
    return Array.from(map.values());
  }
  if (product.value.colors && product.value.colors.length) {
    const map = new Map();
    product.value.colors.forEach((c) => {
      const key = c.color_name || c.color_code;
      if (!key) return;
      if (!map.has(key)) map.set(key, { name: c.color_name, code: c.color_code });
    });
    return Array.from(map.values());
  }
  return [];
});

const replaceImage = (e) => { e.target.src = '/placeholder.jpg' }
const increaseQty = () => { quantity.value++; };
const decreaseQty = () => { if (quantity.value > 1) quantity.value--; };

// CART LOGIC
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

const addToCart = async () => {
  if (!product.value) return

  if (sizeOptions.value.length > 0 && !selectedSize.value) {
    showToast("Vui lòng chọn kích thước!")
    return
  }
  if (colorOptions.value.length > 0 && !selectedColor.value) {
    showToast("Vui lòng chọn màu sắc!")
    return
  }

  const stock = getAvailableStock(product.value, selectedSize.value, selectedColor.value)
  if (stock !== null) {
    if (stock <= 0) { showToast("Sản phẩm hiện đã hết hàng!"); return }
    if (quantity.value > stock) { showToast(`Kho chỉ còn ${stock} sản phẩm!`); return }
  }

  isAdding.value = true
  try {
    const token = localStorage.getItem("token")
    const sessionId = getSessionId()
    const payload = {
      product_id: product.value.id,
      quantity: quantity.value,
      size: selectedSize.value || null,
      color: selectedColor.value || null,
      session_id: sessionId,
    }
    const config = { headers: {} }
    if (token) config.headers["Authorization"] = `Bearer ${token}`

    const response = await axios.post(`/cart/add`, payload, config) // [EDIT] /api/

    if (response.status === 200 || response.status === 201) {
      showToast("Đã thêm sản phẩm vào giỏ hàng!")
      if (response.data.data?.session_id) {
        sessionStorage.setItem("cart_session_id", response.data.data.session_id)
      }
      window.dispatchEvent(new Event("cart-updated"))
    }
  } catch (error) {
    console.error("Lỗi thêm giỏ hàng:", error)
    showToast("Có lỗi khi thêm vào giỏ hàng!")
  } finally {
    isAdding.value = false
  }
}

// RELATED PRODUCTS SLIDER
const relatedProducts = ref([]);
const relatedPerPage = 4;
const relatedSlide = ref(0);
const relatedIntervalId = ref(null);

const relatedTotalPages = computed(() => {
  if (!relatedProducts.value.length) return 0;
  return Math.ceil(relatedProducts.value.length / relatedPerPage);
});

const startRelatedAutoSlide = () => {
  stopRelatedAutoSlide();
  if (relatedTotalPages.value <= 1) return;
  relatedIntervalId.value = setInterval(() => {
    relatedSlide.value = (relatedSlide.value + 1) % relatedTotalPages.value;
  }, 4000);
};

const stopRelatedAutoSlide = () => {
  if (relatedIntervalId.value) {
    clearInterval(relatedIntervalId.value);
    relatedIntervalId.value = null;
  }
};

const nextRelated = () => {
  if (relatedTotalPages.value <= 1) return;
  relatedSlide.value = (relatedSlide.value + 1) % relatedTotalPages.value;
  startRelatedAutoSlide();
};

const prevRelated = () => {
  if (relatedTotalPages.value <= 1) return;
  relatedSlide.value = (relatedSlide.value - 1 + relatedTotalPages.value) % relatedTotalPages.value;
  startRelatedAutoSlide();
};

onBeforeUnmount(() => {
  stopRelatedAutoSlide()
  if (toastTimerId) clearTimeout(toastTimerId)
})

const gotoRelatedDetail = (p) => {
  if (!p || !p.slug) return;
  router.push({ name: "product-details", params: { slug: p.slug } });
};

// QUICK VIEW
const showQuickView = ref(false);
const quickViewProduct = ref(null);
const quickMainImage = ref("");
const quickSelectedSize = ref("");
const quickSelectedColor = ref("");
const quickQuantity = ref(1);
const addingQuick = ref(false);
const showFullDescQuick = ref(false);

const openQuickView = async (card) => {
  try {
    const res = await axios.get(`/products/${card.slug}`); // [EDIT] /api/
    const data = res.data.product || res.data;
    quickViewProduct.value = data;

    if (data.images?.length) {
      const main = data.images.find((i) => i.is_primary) || data.images[0];
      quickMainImage.value = getImageSrc(main);
    } else if (res.data.primary_image) {
      quickMainImage.value = getImageSrc({ image_path: res.data.primary_image });
    } else {
      quickMainImage.value = "/placeholder.jpg";
    }

    quickSelectedSize.value = "";
    quickSelectedColor.value = "";
    quickQuantity.value = 1;
    showFullDescQuick.value = false;
    showQuickView.value = true;
  } catch (e) {
    console.error("Lỗi load xem nhanh:", e);
  }
};

const closeQuickView = () => { showQuickView.value = false; };

const quickSizeOptions = computed(() => {
  const p = quickViewProduct.value;
  if (!p) return [];
  if (p.variants && p.variants.length) {
    const s = new Set();
    p.variants.forEach((v) => v.size && s.add(v.size));
    return Array.from(s);
  }
  return [];
});

const quickColorOptions = computed(() => {
  const p = quickViewProduct.value;
  if (!p) return [];
  if (p.variants && p.variants.length) {
    const map = new Map();
    p.variants.forEach((v) => {
      const key = v.color_name || v.color_code;
      if (!key) return;
      if (!map.has(key)) map.set(key, { name: v.color_name, code: v.color_code });
    });
    return Array.from(map.values());
  }
  return [];
});

const increaseQuickQty = () => { quickQuantity.value++; };
const decreaseQuickQty = () => { if (quickQuantity.value > 1) quickQuantity.value--; };

const addQuickToCart = async () => {
  if (!quickViewProduct.value) return
  if (quickSizeOptions.value.length && !quickSelectedSize.value) {
    showToast("Vui lòng chọn kích thước!"); return
  }
  if (quickColorOptions.value.length && !quickSelectedColor.value) {
    showToast("Vui lòng chọn màu sắc!"); return
  }
  const stock = getAvailableStock(quickViewProduct.value, quickSelectedSize.value, quickSelectedColor.value)
  if (stock !== null) {
    if (stock <= 0) { showToast("Hết hàng!"); return }
    if (quickQuantity.value > stock) { showToast(`Còn ${stock} sp!`); return }
  }

  addingQuick.value = true
  try {
    const token = localStorage.getItem("token")
    const sessionId = getSessionId()
    const payload = {
      product_id: quickViewProduct.value.id,
      quantity: quickQuantity.value,
      size: quickSelectedSize.value || null,
      color: quickSelectedColor.value || null,
      session_id: sessionId,
    }
    const config = { headers: {} }
    if (token) config.headers["Authorization"] = `Bearer ${token}`

    const response = await axios.post(`/cart/add`, payload, config)
    if (response.status === 200 || response.status === 201) {
      showToast("Đã thêm vào giỏ hàng!")
      if (response.data.data?.session_id) sessionStorage.setItem("cart_session_id", response.data.data.session_id)
      window.dispatchEvent(new Event("cart-updated"))
      showQuickView.value = false
    }
  } catch (error) {
    showToast("Lỗi thêm giỏ hàng!")
  } finally {
    addingQuick.value = false
  }
};

const gotoDetailFromQuick = () => {
  if (!quickViewProduct.value?.slug) return;
  router.push({ name: "product-details", params: { slug: quickViewProduct.value.slug } });
  showQuickView.value = false;
};

// TOAST
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

</script>
<style scoped>
/* ================================
   MINIMALIST BLACK & WHITE THEME
   ================================ */

.product-details-page {
  max-width: 1160px;
  /* THU NHỎ KHUNG NỘI DUNG */
  margin: 0 auto;
  padding: 120px 32px 60px;
  /* giảm padding + trái/phải nhỏ lại */
  background: #ffffff;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  color: #000000;
}

/* ================================
   TOP SECTION - GRID LAYOUT
   ================================ */

.top-section {
  display: grid;
  grid-template-columns: 1.1fr 1fr;
  /* cột ảnh hơi rộng hơn cột info */
  gap: 56px;
  /* bớt gap */
  margin-bottom: 60px;
  border-bottom: 1px solid #e5e5e5;
  padding-bottom: 56px;
}

/* ================================
   IMAGE SECTION
   ================================ */

.image-section {
  max-width: 480px;
}

.main-image-wrapper {
  position: relative;
  width: 100%;
  aspect-ratio: 3 / 4;
  /* Tỉ lệ 3:4 cố định giống Canifa */
  border: 2px solid #000000;
  background: #f7f7f7;
  overflow: hidden;
  cursor: zoom-in;
  display: flex;
  align-items: center;
  justify-content: center;
}

.main-image-container {
  position: relative;
  width: 100%;
  height: 520px;
  aspect-ratio: 3 / 4;
  border: 2px solid #000000;
  background: #f7f7f7;
  overflow: hidden;
  cursor: zoom-in;
  display: flex;
  align-items: center;
  justify-content: center;
}

.main-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transform: scale(1);
  transform-origin: center center;
  transition: transform 0.15s ease-out;
}

/* Khi zoom */
.main-image.is-zooming {
  transform: scale(1.8);
  /* tăng/giảm tùy thích */
}


.thumb-list {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  margin-top: 18px;
}

.thumb {
  width: 100%;
  aspect-ratio: 1;
  object-fit: cover;
  border: 1px solid #000000;
  background: #fff;
  cursor: pointer;
  opacity: 0.5;
  transition: all 0.2s ease;
}

.thumb:hover {
  opacity: 0.8;
}

.thumb.active {
  opacity: 1;
  border-width: 2px;
}

/* ================================
   INFO SECTION
   ================================ */

.info-section {
  display: flex;
  flex-direction: column;
  gap: 24px;
  max-width: 520px;
}

.title {
  font-size: 30px;
  /* nhỏ lại cho cân */
  font-weight: 700;
  letter-spacing: 1px;
  line-height: 1.25;
  margin: 0 0 4px;
  text-transform: uppercase;
  border-left: 4px solid #000000;
  padding-left: 14px;
}

/* ================================
   PRICE
   ================================ */

.price-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 22px;
  font-weight: 600;
  padding: 14px 0;
  border-top: 1px solid #000000;
  border-bottom: 1px solid #000000;
}

.sale-price {
  color: #000000;
}

.original-price {
  font-size: 16px;
  text-decoration: line-through;
  opacity: 0.5;
  font-weight: 400;
}

/* ================================
   DESCRIPTION
   ================================ */

.description {
  font-size: 14px;
  line-height: 1.7;
  margin: 0;
  color: #444444;
}

/* ================================
   SECTION BLOCKS
   ================================ */

.section-block {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.section-block h4 {
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1.6px;
  margin: 0;
}

/* ================================
   SIZE SELECTOR
   ================================ */

.size-list {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.size-btn {
  min-width: 44px;
  height: 36px;
  border: 1px solid #000000;
  background: #ffffff;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  text-transform: uppercase;
}

.size-btn:hover {
  background: #000000;
  color: #ffffff;
  transform: translateY(-1px);
}

.size-btn.active {
  background: #000000;
  color: #ffffff;
  box-shadow: 2px 2px 0 rgba(0, 0, 0, 0.2);
}

/* ================================
   COLOR SELECTOR
   ================================ */

.color-list {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.color-circle {
  width: 26px;
  height: 26px;
  border: 1px solid #000000;
  cursor: pointer;
  transition: all 0.2s ease;
}

.color-circle:hover {
  transform: scale(1.05);
}

.color-circle.active {
  box-shadow: 0 0 0 3px #ffffff, 0 0 0 4px #000000;
  transform: scale(1.03);
}

/* ================================
   QUANTITY BOX
   ================================ */

.qty-box {
  display: inline-flex;
  border: 1px solid #000000;
  width: fit-content;
}

.qty-box button {
  width: 32px;
  height: 32px;
  border: none;
  background: #ffffff;
  font-size: 18px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  padding: 0;
  flex-shrink: 0;
}

.qty-box button:first-child {
  border-right: 1px solid #000000;
}

.qty-box button:last-child {
  border-left: 1px solid #000000;
}

.qty-box button:hover {
  background: #000000;
  color: #ffffff;
}

.qty-box input {
  width: 48px;
  height: 32px;
  border: none;
  text-align: center;
  font-size: 14px;
  font-weight: 500;
  outline: none;
  padding: 0;
  margin: 0;
  box-sizing: border-box;
  -moz-appearance: textfield;
}

.qty-box input::-webkit-outer-spin-button,
.qty-box input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.qty-box input:focus {
  background: #f5f5f5;
}

/* ================================
   ADD TO CART BUTTON
   ================================ */

.add-cart-btn {
  width: 100%;
  height: 56px;
  background: #000000;
  color: #ffffff;
  border: none;
  font-size: 14px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 2px;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  margin-top: 16px;
}

.add-cart-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 0 rgba(0, 0, 0, 0.3);
}

.add-cart-btn:active {
  transform: translateY(-1px);
  box-shadow: 0 2px 0 rgba(0, 0, 0, 0.3);
}

/* ================================
   REVIEWS SECTION
   ================================ */

.reviews-section {
  padding: 40px 0 50px;
}

.reviews-section h3 {
  font-size: 22px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin: 0 0 24px 0;
  border-left: 4px solid #000000;
  padding-left: 14px;
}

.review-card {
  border: 1px solid #000000;
  padding: 16px;
  margin-bottom: 12px;
  transition: all 0.2s ease;
  background: #ffffff;
}

.star-rating {
  display: inline-block;
  margin-right: 10px;
}

.star {
  font-size: 18px;
  color: #d1d5db;
  /* Màu xám cho sao rỗng (chưa được chọn) */
  margin-right: 2px;
}

/* Class này được Vue thêm vào nếu star <= rating */
.star.filled {
  color: #f59e0b;
  /* Màu vàng cam cho sao đã chọn */
}

.review-card:hover {
  transform: translateX(4px);
  box-shadow: -4px 4px 0 rgba(0, 0, 0, 0.08);
}

.review-card strong {
  display: block;
  font-size: 14px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
}

.review-card p {
  margin: 0 0 8px 0;
  line-height: 1.6;
  color: #333333;
}

.rating {
  font-size: 12px;
  font-weight: 600;
  display: inline-block;
  padding: 3px 10px;
  background: #f5eeee;
  color: #121212;
}


.star-rating .star {
  color: #ddd;
  font-size: 18px;
}

.star-rating .star.filled {
  color: #ffc107;
  /* Màu vàng */
}

.review-date {
  font-size: 12px;
  color: #999;
  margin-bottom: 5px;
}

/* PHẦN NÚT PHÂN TRANG */
.pagination-controls {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 15px;
  margin-top: 20px;
}

.btn-page {
  padding: 8px 16px;
  background-color: #fff;
  border: 1px solid #ddd;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s;
}

.btn-page:hover:not(:disabled) {
  background-color: #f0f0f0;
  border-color: #999;
}

.btn-page:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background-color: #eee;
}

.page-info {
  font-weight: 600;
  font-size: 14px;
  color: #333;
}

/* ================================
   LOADING STATE
   ================================ */

.loading {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 50vh;
  font-size: 18px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1.5px;
}

/* ================================
   ACCORDION THÔNG TIN
   ================================ */

.product-accordion {
  margin-top: 24px;
  border-top: 1px solid #e5e5e5;
}

.accordion-section {
  border-bottom: 1px solid #e5e5e5;
}

.accordion-header {
  width: 100%;
  padding: 10px 0;
  background: transparent;
  border: none;
  display: flex;
  justify-content: space-between;
  align-items: center;
  text-align: left;
  font-size: 12px;
  letter-spacing: 1.4px;
  text-transform: uppercase;
  font-weight: 500;
  cursor: pointer;
}

.accordion-header span:first-child {
  color: #000;
}

.toggle-icon {
  font-size: 16px;
  line-height: 1;
}

.accordion-content {
  padding: 0 0 12px 0;
  font-size: 13px;
  line-height: 1.7;
  color: #444;
}

.accordion-content ul {
  margin: 0;
  padding-left: 18px;
}

.accordion-content li {
  margin-bottom: 4px;
}

/* ================================
   CHÍNH SÁCH MUA HÀNG
   ================================ */

.purchase-policy {
  margin-top: 20px;
  padding-top: 14px;
  border-top: 1px solid #e5e5e5;
}

.policy-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 8px;
  font-size: 12px;
  color: #333;
}

.policy-item strong {
  font-weight: 600;
}

/* Icon dạng vòng tròn đơn giản, giữ đúng style đen–trắng */
.policy-icon-circle {
  width: 26px;
  height: 26px;
  border-radius: 999px;
  border: 1px solid #000;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
}

/* ================================
   RESPONSIVE
   ================================ */

@media (max-width: 1024px) {
  .product-details-page {
    padding: 100px 24px 50px;
  }

  .top-section {
    grid-template-columns: 1fr;
    gap: 40px;
  }

  .image-section {
    max-width: 100%;
    margin: 0 auto;
  }
}

@media (max-width: 768px) {
  .product-details-page {
    padding: 80px 16px 40px;
  }

  .title {
    font-size: 24px;
  }

  .price-wrapper {
    font-size: 18px;
  }

  .thumb-list {
    grid-template-columns: repeat(3, 1fr);
  }

  .add-cart-btn {
    height: 52px;
    font-size: 13px;
  }

  .accordion-header {
    font-size: 11px;
  }

  .accordion-content {
    font-size: 12px;
  }

  .policy-item {
    font-size: 11px;
  }
}

/* ========== SLIDE EFFECT ========== */
.slide-left-enter-active,
.slide-left-leave-active,
.slide-right-enter-active,
.slide-right-leave-active {
  transition: all 0.35s ease;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

/* Trượt sang trái (ảnh mới đi từ phải sang, ảnh cũ trượt sang trái) */
.slide-left-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.slide-left-enter-to {
  transform: translateX(0);
  opacity: 1;
}

.slide-left-leave-from {
  transform: translateX(0);
  opacity: 1;
}

.slide-left-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}

/* Trượt sang phải (ngược lại) */
.slide-right-enter-from {
  transform: translateX(-100%);
  opacity: 0;
}

.slide-right-enter-to {
  transform: translateX(0);
  opacity: 1;
}

.slide-right-leave-from {
  transform: translateX(0);
  opacity: 1;
}

.slide-right-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

/* ================== RELATED (SLIDER KHUNG) ================== */

.related-section {
  margin-top: 40px;
  padding-top: 40px;
  border-top: 1px solid #e5e5e5;
}

.related-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.related-header h3 {
  font-size: 22px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin: 0;
  border-left: 4px solid #000;
  padding-left: 14px;
}


.related-controls {
  display: flex;
  gap: 10px;
}

.related-arrow {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 1px solid #000;
  background: #fff;
  cursor: pointer;
  font-size: 16px;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.related-arrow:hover {
  background: #000;
  color: #fff;
}

.related-slider {
  overflow: hidden;
}

.related-track {
  display: flex;
  transition: transform 0.4s ease;
}

.related-page {
  min-width: 100%;
}

/* dùng lại lưới 4 cột giống ngoài */
.related-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 24px;
}

/* Tablet */
@media (max-width: 1024px) {
  .related-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

/* Mobile */
@media (max-width: 768px) {
  .related-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

/* ================== CARD SẢN PHẨM GIỐNG  ================== */

.products-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 24px;
  background: transparent;
  border: none;
  margin-bottom: 40px;
}

@media (max-width: 1400px) {
  .products-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 1023px) {
  .products-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

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

/* Badge "Nổi bật" */
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

.product-name {
  font-size: 16px;
  font-weight: 500;
  color: #000;
  margin: 0 0 10px 0;
  line-height: 1.5;
  min-height: 48px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Giá */
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

/* Nút hành động */
.product-actions {
  display: flex;
  gap: 8px;
  margin-top: 14px;
}

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

.product-actions .quick-view-btn:hover {
  background: #000000;
  color: #ffffff;
}

.product-actions .detail-btn {
  background: #000000;
  color: #ffffff;
}

.product-actions .detail-btn:hover {
  background: #ffffff;
  color: #000000;
}

@media (max-width: 768px) {
  .product-actions {
    flex-direction: column;
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

/* list ảnh nhỏ - CHỈ áp dụng trong modal để không phá thumb bên ngoài */
.modal-image .thumb-list {
  display: flex;
  gap: 8px;
  margin-top: 12px;
  overflow-x: auto;
}

.modal-image .thumb {
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

.modal-image .thumb:hover {
  transform: translateY(-2px);
}

.modal-image .thumb.active {
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

/* nút size trong modal */
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

.quantity-container .qty-btn {
  width: 32px;
  height: 40px;
  border: none;
  background: #f6f6f6;
  cursor: pointer;
  font-size: 18px;
}

.quantity-container .qty-btn:hover {
  background: #e9e9e9;
}

.quantity-container .qty-input {
  width: 54px;
  height: 40px;
  border: none;
  border-left: 1px solid #ddd;
  border-right: 1px solid #ddd;
  text-align: center;
  font-size: 14px;
}

/* nút thêm giỏ: CHỈ cho nút trong modal, không đè nút trên trang */
.modal .add-cart-btn {
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

.modal .add-cart-btn:hover:not(.disabled-btn) {
  background: #111;
}

.modal .add-cart-btn.disabled-btn {
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
  z-index: 10000;
  /* cao hơn modal overlay */
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
