<template>
    <main class="main-content">
      <!-- Hero Section -->
      <section class="hero-section">
        <!-- Animated Background -->
        <div class="hero-bg">
          <div class="geo-shape shape-1"></div>
          <div class="geo-shape shape-2"></div>
          <div class="geo-shape shape-3"></div>
          <div class="grain-overlay"></div>
        </div>

        <!-- Main Banner Content -->
        <div class="hero-container">
          <!-- Left - Image -->
          <div class="hero-image-wrapper">
            <div class="image-frame frame-1"></div>
            <div class="image-frame frame-2"></div>
            <div class="hero-image-box">
              <img 
                src="/images/photo-1515886657613-9f3515b0c78f.jpg" 
                alt="Fashion Model"
                class="hero-image"
              />
              <div class="image-overlay"></div>
            </div>
            <div class="floating-tag">SS 2025</div>
          </div>

          <!-- Right - Text -->
          <div class="hero-text-content">
            <p class="hero-tagline">— THỜI TRANG TỐI GIẢN —</p>
            <h1 class="hero-title">
              <span class="title-line">FLOR</span>
              <span class="title-line title-gradient">ENTIC</span>
            </h1>
            <div class="hero-divider"></div>
            <p class="hero-subtitle">
              Định nghĩa lại sự tối giản.<br/>
              <span class="subtitle-bold">Nghệ thuật trên từng đường cắt.</span>
            </p>
            <div class="hero-buttons">
              <router-link to="/products" class="btn-primary">
                <span class="btn-text">KHÁM PHÁ NGAY</span>
                <span class="btn-hover-text">KHÁM PHÁ NGAY</span>
              </router-link>
            </div>
          </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="scroll-indicator">
          <span>CUỘN XUỐNG</span>
          <div class="scroll-arrow">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 5v14M19 12l-7 7-7-7"/>
            </svg>
          </div>
        </div>

        <!-- Side Decoration -->
        <div class="side-deco">
          <div class="deco-line"></div>
          <span class="deco-text">SINCE 2024</span>
          <div class="deco-line"></div>
        </div>
      </section>

      <!-- Marquee Strip -->
      <div class="marquee-strip">
        <div class="marquee-track">
          <span v-for="n in 10" :key="n" class="marquee-item">
            FREE SHIPPING • NEW COLLECTION • PREMIUM QUALITY • SUSTAINABLE FASHION •
          </span>
        </div>
      </div>

      <!-- Content Blocks -->
      <div class="scroll-sections-container">
        <section 
          v-for="(block, index) in contentBlocks" 
          :key="index"
          class="scroll-block"
          :class="{ 'slide-from-right': index % 2 !== 0, 'slide-from-left': index % 2 === 0 }"
        >
          <div class="block-inner">
            <div class="block-content">
              <h2 class="block-title">{{ block.title }}</h2>
              <div class="divider-short"></div>
              <p class="block-desc">{{ block.description }}</p>
              <router-link :to="block.linkUrl" class="block-link">{{ block.linkText }}</router-link>
            </div>
            <div class="block-image-container">
              <img :src="block.imageSrc" :alt="block.title" class="block-image" />
            </div>
          </div>
        </section>
      </div>
    </main>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const contentBlocks = ref([
  {
    title: "BỘ SƯU TẬP: MONOCHROME",
    description: "Sự giao thoa giữa ánh sáng và bóng tối. Những thiết kế đen trắng chủ đạo, tôn vinh hình khối cơ bản và chất liệu cao cấp.",
    linkText: "XEM BỘ SƯU TẬP",
    linkUrl: "/products/category/monochrome",
    imageSrc: "/images/photo-1558618666-fcd25c85cd64.jpg"
  },
  {
    title: "CÂU CHUYỆN VỀ FLORENTIC",
    description: "Chúng tôi không chỉ bán quần áo. Chúng tôi bán một tư duy thẩm mỹ. Tìm hiểu về triết lý thiết kế 'Brutalist Minimal' của chúng tôi.",
    linkText: "ĐỌC THÊM",
    linkUrl: "/about",
    imageSrc: "/images/photo-1490481651871-ab68de25d43d.jpg"
  },
  {
    title: "NEW ARRIVALS: MÙA HÈ 2025",
    description: "Thoáng mát, phóng khoáng nhưng vẫn giữ sự chỉn chu. Khám phá những items mới nhất vừa cập bến.",
    linkText: "MUA NGAY",
    linkUrl: "/products/category/new-arrivals",
    imageSrc: "/images/photo-1509631179647-0177331693ae.jpg"
  },
  {
    title: "ESSENTIAL BASICS",
    description: "Nền tảng của mọi tủ đồ hoàn hảo. Những item cơ bản được nâng tầm với chất liệu premium và đường cắt tinh tế đến từng chi tiết.",
    linkText: "KHÁM PHÁ BASICS",
    linkUrl: "/products/category/essentials",
    imageSrc: "/images/photo-1489987707025-afc232f7ea0f.jpg"
  },
  {
    title: "OUTERWEAR COLLECTION",
    description: "Lớp áo khoác định hình phong cách. Từ blazer thanh lịch đến overcoat cổ điển, mỗi thiết kế là một statement về sự tinh tế.",
    linkText: "XEM OUTERWEAR",
    linkUrl: "/products/category/jackets",
    imageSrc: "/images/photo-1591047139829-d91aecb6caea.jpg"
  },
  {
    title: "THE TAILORED EDIT",
    description: "Nghệ thuật may đo trong từng đường kim mũi chỉ. Những bộ suit và quần âu được chế tác với sự chính xác tuyệt đối.",
    linkText: "XEM TAILORED",
    linkUrl: "/products/category/trousers",
    imageSrc: "/images/photo-1507003211169-0a1dd7228f2d.jpg"
  },
  {
    title: "SUSTAINABLE FASHION",
    description: "Thời trang có trách nhiệm. Chúng tôi cam kết sử dụng chất liệu bền vững và quy trình sản xuất thân thiện với môi trường.",
    linkText: "TÌM HIỂU THÊM",
    linkUrl: "/products",
    imageSrc: "/images/photo-1532453288672-3a27e9be9efd.jpg"
  },
  {
    title: "ACCESSORIES",
    description: "Điểm nhấn hoàn thiện phong cách. Từ túi xách tối giản đến phụ kiện tinh tế, mỗi item đều mang DNA của FLORENTIC.",
    linkText: "XEM THÊM",
    linkUrl: "/products/category/bags",
    imageSrc: "/images/photo-1553062407-98eeb64c6a62.jpg"
  },
  {
    title: "LIMITED EDITION",
    description: "Những thiết kế giới hạn, được đánh số riêng biệt. Sở hữu một phần nghệ thuật độc bản từ các nghệ nhân hàng đầu.",
    linkText: "XEM LIMITED",
    linkUrl: "/products",
    imageSrc: "/images/photo-1441986300917-64674bd600d8.jpg"
  },
  {
    title: "FLORENTIC JOURNAL",
    description: "Góc nhìn về thời trang, nghệ thuật và lối sống. Đọc những bài viết sâu sắc từ đội ngũ sáng tạo và các cộng tác viên của chúng tôi.",
    linkText: "ĐỌC JOURNAL",
    linkUrl: "/products",
    imageSrc: "/images/photo-1524758631624-e2822e304c36.jpg"
  }
]);

let observer = null;

onMounted(() => {
  const options = {
    root: null,
    rootMargin: '0px',
    threshold: 0.2
  };

  const handleIntersect = (entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
      }
    });
  };

  observer = new IntersectionObserver(handleIntersect, options);
  const targets = document.querySelectorAll('.scroll-block');
  targets.forEach(target => observer.observe(target));
});

onUnmounted(() => {
  if (observer) observer.disconnect();
});
</script>

<style scoped>
.home-page {
  font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
  background-color: #fff;
  color: #000;
  overflow-x: hidden;
}

.main-content {
  min-height: 100vh;
}

/* ========== HERO SECTION ========== */
.hero-section {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  padding: 100px 40px 60px;
}

/* Background Effects */
.hero-bg {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, #f5f5f5 0%, #fff 50%, #ebebeb 100%);
}

.geo-shape {
  position: absolute;
  border: 2px solid rgba(0,0,0,0.08);
  pointer-events: none;
}

.shape-1 {
  width: 300px;
  height: 300px;
  top: 10%;
  left: 5%;
  transform: rotate(12deg);
  animation: float1 20s ease-in-out infinite;
}

.shape-2 {
  width: 400px;
  height: 400px;
  bottom: 10%;
  right: 5%;
  transform: rotate(-8deg);
  animation: float2 25s ease-in-out infinite;
}

.shape-3 {
  width: 150px;
  height: 150px;
  top: 30%;
  right: 25%;
  background: rgba(0,0,0,0.03);
  animation: float3 15s ease-in-out infinite;
}

.grain-overlay {
  position: absolute;
  inset: 0;
  opacity: 0.4;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
}

@keyframes float1 {
  0%, 100% { transform: rotate(12deg) translate(0, 0); }
  50% { transform: rotate(15deg) translate(20px, -20px); }
}

@keyframes float2 {
  0%, 100% { transform: rotate(-8deg) translate(0, 0); }
  50% { transform: rotate(-5deg) translate(-15px, 15px); }
}

@keyframes float3 {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-30px); }
}

/* Hero Container */
.hero-container {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 80px;
  max-width: 1200px;
  margin: 0 auto;
}

/* Hero Image */
.hero-image-wrapper {
  position: relative;
  flex-shrink: 0;
}

.image-frame {
  position: absolute;
  inset: -16px;
  border: 2px solid #000;
  pointer-events: none;
  transition: transform 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.frame-1 { transform: rotate(-3deg); }
.frame-2 { transform: rotate(3deg); }

.hero-image-wrapper:hover .frame-1,
.hero-image-wrapper:hover .frame-2 {
  transform: rotate(0deg);
}

.hero-image-box {
  position: relative;
  width: 320px;
  height: 420px;
  overflow: hidden;
}

.hero-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  filter: grayscale(100%);
  transition: all 1s ease;
}

.hero-image-wrapper:hover .hero-image {
  filter: grayscale(0%);
  transform: scale(1.05);
}

.image-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.3), transparent);
}

.floating-tag {
  position: absolute;
  bottom: -20px;
  right: -20px;
  background: #000;
  color: #fff;
  padding: 10px 20px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 3px;
}

/* Hero Text */
.hero-text-content {
  max-width: 500px;
}

.hero-tagline {
  font-size: 12px;
  letter-spacing: 0.3em;
  color: #888;
  margin-bottom: 20px;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.hero-title {
  font-size: clamp(3rem, 8vw, 6rem);
  font-weight: 900;
  line-height: 0.95;
  margin: 0 0 30px;
}

.title-line {
  display: block;
}

.title-gradient {
  background: linear-gradient(90deg, #000 0%, #555 50%, #000 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-divider {
  width: 80px;
  height: 4px;
  background: #000;
  margin-bottom: 30px;
}

.hero-subtitle {
  font-size: 1.2rem;
  line-height: 1.8;
  color: #555;
  margin-bottom: 40px;
}

.subtitle-bold {
  font-weight: 700;
  color: #000;
}

/* Hero Buttons */
.hero-buttons {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.btn-primary {
  position: relative;
  display: inline-block;
  padding: 18px 40px;
  background: #000;
  color: #fff;
  text-decoration: none;
  font-weight: 700;
  font-size: 13px;
  letter-spacing: 2px;
  overflow: hidden;
}

.btn-text {
  position: relative;
  z-index: 1;
  transition: opacity 0.5s;
}

.btn-hover-text {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #000;
  opacity: 0;
  transition: opacity 0.5s;
}

.btn-primary::before {
  content: '';
  position: absolute;
  inset: 0;
  background: #fff;
  transform: translateX(-100%);
  transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.btn-primary:hover::before {
  transform: translateX(0);
}

.btn-primary:hover .btn-text {
  opacity: 0;
}

.btn-primary:hover .btn-hover-text {
  opacity: 1;
}

.btn-secondary {
  display: inline-block;
  padding: 18px 40px;
  background: transparent;
  color: #000;
  text-decoration: none;
  font-weight: 700;
  font-size: 13px;
  letter-spacing: 2px;
  border: 2px solid #000;
  transition: all 0.3s;
}

.btn-secondary:hover {
  background: #000;
  color: #fff;
}

/* Scroll Indicator */
.scroll-indicator {
  position: absolute;
  bottom: 40px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  animation: bounce 2s infinite;
}

.scroll-indicator span {
  font-size: 11px;
  letter-spacing: 3px;
  font-weight: 600;
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
  40% { transform: translateX(-50%) translateY(-10px); }
  60% { transform: translateX(-50%) translateY(-5px); }
}

/* Side Decoration */
.side-deco {
  position: absolute;
  left: 40px;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
}

.deco-line {
  width: 1px;
  height: 60px;
  background: #000;
}

.deco-text {
  font-size: 11px;
  letter-spacing: 2px;
  font-weight: 600;
  writing-mode: vertical-rl;
  text-orientation: mixed;
  transform: rotate(180deg);
}

/* ========== MARQUEE STRIP ========== */
.marquee-strip {
  background: #000;
  color: #fff;
  padding: 15px 0;
  overflow: hidden;
}

.marquee-track {
  display: flex;
  animation: marquee 30s linear infinite;
  white-space: nowrap;
}

.marquee-item {
  flex-shrink: 0;
  padding: 0 20px;
  font-size: 13px;
  letter-spacing: 3px;
  font-weight: 500;
}

@keyframes marquee {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}

/* ========== SCROLL BLOCKS ========== */
.scroll-sections-container {
  padding: 120px 40px;
  background: #fff;
}

.scroll-block {
  max-width: 1100px;
  margin: 0 auto 120px;
  border: 2px solid #000;
  background: #fff;
  opacity: 0;
  transition: all 1s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.slide-from-left { transform: translateX(-100px); }
.slide-from-right { transform: translateX(100px); }

.scroll-block.is-visible {
  opacity: 1;
  transform: translateX(0);
}

.block-inner {
  display: flex;
  min-height: 450px;
}

.scroll-block:nth-child(odd) .block-inner {
  flex-direction: row-reverse;
}

.block-content {
  flex: 1;
  padding: 60px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  border-right: 2px solid #000;
}

.scroll-block:nth-child(odd) .block-content {
  border-right: none;
  border-left: 2px solid #000;
}

.block-title {
  font-size: 1.8rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 2px;
  margin-bottom: 20px;
}

.divider-short {
  width: 60px;
  height: 3px;
  background: #000;
  margin-bottom: 25px;
}

.block-desc {
  font-size: 1.05rem;
  line-height: 1.7;
  color: #444;
  margin-bottom: 35px;
}

.block-link {
  display: inline-block;
  width: fit-content;
  text-decoration: none;
  color: #000;
  font-weight: 700;
  font-size: 13px;
  letter-spacing: 1px;
  padding-bottom: 8px;
  border-bottom: 2px solid #000;
  transition: all 0.3s;
}

.block-link:hover {
  background: #000;
  color: #fff;
  padding: 10px 20px;
}

.block-image-container {
  flex: 1;
  overflow: hidden;
  background: #eee;
}

.block-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 1.2s ease;
}

.scroll-block.is-visible .block-image {
  transform: scale(1.05);
}

/* ========== RESPONSIVE ========== */
@media (max-width: 992px) {
  .hero-container {
    flex-direction: column;
    gap: 50px;
    text-align: center;
  }
  
  .hero-text-content {
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  
  .hero-buttons {
    justify-content: center;
  }
  
  .side-deco {
    display: none;
  }
}

@media (max-width: 768px) {
  .hero-section {
    padding: 120px 20px 80px;
  }
  
  .hero-image-box {
    width: 260px;
    height: 340px;
  }
  
  .hero-title {
    font-size: 3rem;
  }
  
  .hero-buttons {
    flex-direction: column;
    width: 100%;
  }
  
  .btn-primary, .btn-secondary {
    width: 100%;
    text-align: center;
  }
  
  .scroll-sections-container {
    padding: 80px 20px;
  }
  
  .scroll-block {
    margin-bottom: 80px;
    border-width: 1px;
  }
  
  .block-inner {
    flex-direction: column-reverse !important;
    min-height: auto;
  }
  
  .block-content {
    border: none !important;
    border-top: 2px solid #000 !important;
    padding: 40px 25px;
  }
  
  .block-image-container {
    height: 280px;
  }
  
  .slide-from-left,
  .slide-from-right {
    transform: translateY(50px);
  }
}
</style>