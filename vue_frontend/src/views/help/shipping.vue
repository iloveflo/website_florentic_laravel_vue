<template>
  <div class="shipping-policy">
    <div class="container">
      <!-- Page Header -->
      <div class="page-header">
        <div class="page-header-content">
          <div>
            <h2 class="title">CHÍNH SÁCH VẬN CHUYỂN</h2>
            <p class="subtitle">DELIVERING EXCELLENCE WITH PRECISION</p>
          </div>
          <svg class="icon-truck" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
            <path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
          </svg>
        </div>
      </div>
      <!-- Shipping Methods -->
      <div class="section">
        <h3 class="section-title">PHƯƠNG THỨC VẬN CHUYỂN</h3>
        
        <div class="method-grid">
          <div 
            v-for="method in shippingMethods" 
            :key="method.id"
            @click="selectedMethod = method.id"
            :class="['method-card', { active: selectedMethod === method.id }]"
          >
            <div class="method-icon">
              <svg class="icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path :d="method.icon"/>
              </svg>
            </div>
            <div class="method-content">
              <h4 class="method-name">{{ method.name }}</h4>
              <p class="method-time">{{ method.time }}</p>
              <p class="method-fee">{{ method.fee }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Shipping Zones -->
      <div class="section">
        <h3 class="section-title">KHU VỰC VẬN CHUYỂN</h3>
        
        <div class="zone-wrapper">
          <div 
            v-for="zone in shippingZones" 
            :key="zone.id"
            class="zone-item"
          >
            <div class="zone-header">
              <div class="zone-dot"></div>
              <h4 class="zone-name">{{ zone.name }}</h4>
            </div>
            <div class="zone-details">
              <div class="zone-detail">
                <span class="detail-label">Thời gian:</span>
                <span class="detail-value">{{ zone.time }}</span>
              </div>
              <div class="zone-detail">
                <span class="detail-label">Phí vận chuyển:</span>
                <span class="detail-value">{{ zone.fee }}</span>
              </div>
              <p class="zone-note">{{ zone.note }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Shipping Policy Details -->
      <div class="section">
        <h3 class="section-title">QUY ĐỊNH VẬN CHUYỂN</h3>
        
        <div class="policy-grid">
          <div class="policy-column">
            <div 
              v-for="policy in policyLeft" 
              :key="policy.id"
              class="policy-item"
            >
              <div class="policy-number">{{ policy.number }}</div>
              <div class="policy-content">
                <h4 class="policy-heading">{{ policy.title }}</h4>
                <p class="policy-text">{{ policy.content }}</p>
              </div>
            </div>
          </div>
          
          <div class="policy-column">
            <div 
              v-for="policy in policyRight" 
              :key="policy.id"
              class="policy-item"
            >
              <div class="policy-number">{{ policy.number }}</div>
              <div class="policy-content">
                <h4 class="policy-heading">{{ policy.title }}</h4>
                <p class="policy-text">{{ policy.content }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Free Shipping Condition -->
      <div class="promo-box">
        <div class="promo-icon">
          <svg class="icon-gift" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
          </svg>
        </div>
        <div class="promo-content">
          <h4 class="promo-title">MIỄN PHÍ VẬN CHUYỂN</h4>
          <p class="promo-text">Áp dụng cho đơn hàng từ 500.000₫ trên toàn quốc</p>
        </div>
      </div>

      <!-- Contact Info -->
      <div class="contact-box">
        <h4 class="contact-title">HỖ TRỢ VẬN CHUYỂN</h4>
        <div class="contact-grid">
          <div class="contact-item">
            <span class="contact-label">Hotline:</span>
            <span class="contact-value">1900 98765</span>
          </div>
          <div class="contact-item">
            <span class="contact-label">Email:</span>
            <span class="contact-value">shipping@florentic.com</span>
          </div>
          <div class="contact-item">
            <span class="contact-label">Thời gian:</span>
            <span class="contact-value">8:00 - 22:00 (Hàng ngày)</span>
          </div>
        </div>
      </div>

      <!-- Important Notes -->
      <div class="note-box">
        <h4 class="note-title">LƯU Ý QUAN TRỌNG</h4>
        <ul class="note-list">
          <li v-for="note in importantNotes" :key="note">{{ note }}</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const selectedMethod = ref('standard');

const shippingMethods = [
  {
    id: 'standard',
    name: 'TIÊU CHUẨN',
    time: '3-5 ngày làm việc',
    fee: '30.000₫',
    icon: 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'
  },
  {
    id: 'express',
    name: 'NHANH',
    time: '1-2 ngày làm việc',
    fee: '50.000₫',
    icon: 'M13 10V3L4 14h7v7l9-11h-7z'
  },
  {
    id: 'super',
    name: 'SIÊU TỐC',
    time: 'Trong ngày',
    fee: '80.000₫',
    icon: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'
  }
];

const shippingZones = [
  {
    id: 'inner',
    name: 'NỘI THÀNH HÀ NỘI & TP.HCM',
    time: '1-2 ngày',
    fee: 'Từ 25.000₫',
    note: 'Giao hàng trong ngày với đơn đặt trước 14h00'
  },
  {
    id: 'suburban',
    name: 'NGOẠI THÀNH & CÁC TỈNH LÂN CẬN',
    time: '2-3 ngày',
    fee: 'Từ 30.000₫',
    note: 'Bao gồm các tỉnh thuộc miền Bắc, miền Nam'
  },
  {
    id: 'province',
    name: 'CÁC TỈNH THÀNH KHÁC',
    time: '3-5 ngày',
    fee: 'Từ 35.000₫',
    note: 'Các tỉnh miền Trung, Tây Nguyên và vùng sâu vùng xa'
  }
];

const policyLeft = [
  {
    id: 1,
    number: '01',
    title: 'KIỂM TRA HÀNG HÓA',
    content: 'Khách hàng được kiểm tra hàng trước khi thanh toán. Vui lòng kiểm tra kỹ sản phẩm về mẫu mã, màu sắc, size trước khi nhận hàng.'
  },
  {
    id: 2,
    number: '02',
    title: 'ĐÓNG GÓI CHUẨN MỰC',
    content: 'Sản phẩm được đóng gói cẩn thận trong hộp FLORENTIC cao cấp với túi chống sốc, đảm bảo nguyên vẹn khi đến tay khách hàng.'
  },
  {
    id: 3,
    number: '03',
    title: 'BẢO HIỂM VẬN CHUYỂN',
    content: 'Tất cả đơn hàng đều được bảo hiểm 100% giá trị. FLORENTIC chịu trách nhiệm với mọi rủi ro trong quá trình vận chuyển.'
  }
];

const policyRight = [
  {
    id: 4,
    number: '04',
    title: 'THEO DÕI ĐƠN HÀNG',
    content: 'Mã vận đơn sẽ được gửi qua SMS/Email sau khi đơn hàng được giao cho đơn vị vận chuyển. Khách hàng có thể theo dõi 24/7.'
  },
  {
    id: 5,
    number: '05',
    title: 'GIAO HÀNG THẤT BẠI',
    content: 'Trong trường hợp giao hàng 2 lần không thành công, đơn hàng sẽ được hoàn về kho. Vui lòng liên hệ để sắp xếp lại lịch giao.'
  },
  {
    id: 6,
    number: '06',
    title: 'PHÍ HOÀN TRẢ',
    content: 'Khách hàng chịu phí vận chuyển hoàn trả nếu từ chối nhận hàng không có lý do chính đáng hoặc cung cấp sai thông tin.'
  }
];

const importantNotes = [
  'Thời gian giao hàng có thể kéo dài hơn trong các dịp lễ, Tết hoặc do ảnh hưởng thời tiết, thiên tai.',
  'FLORENTIC không chịu trách nhiệm với việc giao hàng trễ do khách hàng cung cấp sai thông tin địa chỉ hoặc không bắt máy.',
  'Vui lòng giữ lại hóa đơn và bao bì sản phẩm để được hỗ trợ đổi trả theo chính sách của FLORENTIC.',
  'Phí vận chuyển có thể thay đổi tùy theo trọng lượng và kích thước đơn hàng thực tế.',
  'Đơn hàng được xử lý từ Thứ 2 đến Thứ 7 (8:00 - 17:00). Đơn đặt sau 17:00 sẽ được xử lý vào ngày làm việc tiếp theo.'
];
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.shipping-policy {
  background-color: #ffffff;
  color: #000000;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
  padding: 48px 0;
}

.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 24px;
}

/* Page Header */
.page-header {
  padding: 70px 0;
  border-bottom: 2px solid #000000;
  margin-bottom: 64px;
}

.page-header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.title {
  font-size: 48px;
  font-weight: 300;
  letter-spacing: 0.1em;
  margin: 0 0 8px 0;
}

.subtitle {
  font-size: 14px;
  letter-spacing: 0.2em;
  opacity: 0.6;
  margin: 0;
}

.icon-truck {
  width: 48px;
  height: 48px;
}

/* Section */
.section {
  margin: 64px 0;
}

.section-title {
  font-size: 24px;
  font-weight: 300;
  letter-spacing: 0.2em;
  margin: 0 0 32px 0;
  padding-bottom: 16px;
  border-bottom: 2px solid #000000;
}

/* Shipping Methods */
.method-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
}

.method-card {
  border: 2px solid #000000;
  padding: 32px;
  cursor: pointer;
  transition: all 0.3s ease;
  background-color: #ffffff;
}

.method-card:hover {
  background-color: #f9fafb;
}

.method-card.active {
  background-color: #000000;
  color: #ffffff;
}

.method-icon {
  margin-bottom: 24px;
}

.icon {
  width: 40px;
  height: 40px;
}

.method-name {
  font-size: 20px;
  font-weight: 500;
  letter-spacing: 0.1em;
  margin: 0 0 12px 0;
}

.method-time {
  font-size: 14px;
  opacity: 0.7;
  margin: 0 0 8px 0;
}

.method-fee {
  font-size: 18px;
  font-weight: 500;
  margin: 0;
}

/* Shipping Zones */
.zone-wrapper {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.zone-item {
  border: 2px solid #000000;
  padding: 32px;
}

.zone-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
}

.zone-dot {
  width: 12px;
  height: 12px;
  background-color: #000000;
  flex-shrink: 0;
}

.zone-name {
  font-size: 18px;
  font-weight: 500;
  letter-spacing: 0.1em;
  margin: 0;
}

.zone-details {
  padding-left: 28px;
}

.zone-detail {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e5e7eb;
}

.detail-label {
  font-size: 14px;
  opacity: 0.7;
}

.detail-value {
  font-size: 14px;
  font-weight: 500;
}

.zone-note {
  font-size: 13px;
  opacity: 0.6;
  margin: 16px 0 0 0;
  line-height: 1.6;
}

/* Policy Details */
.policy-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 32px;
}

@media (min-width: 768px) {
  .policy-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.policy-column {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.policy-item {
  display: flex;
  gap: 24px;
}

.policy-number {
  font-size: 32px;
  font-weight: 300;
  letter-spacing: 0.1em;
  flex-shrink: 0;
  opacity: 0.3;
}

.policy-content {
  flex: 1;
}

.policy-heading {
  font-size: 16px;
  font-weight: 500;
  letter-spacing: 0.05em;
  margin: 0 0 12px 0;
}

.policy-text {
  font-size: 14px;
  opacity: 0.7;
  line-height: 1.6;
  margin: 0;
}

/* Promo Box */
.promo-box {
  margin: 48px 0;
  border: 2px solid #000000;
  background-color: #000000;
  color: #ffffff;
  padding: 40px;
  display: flex;
  align-items: center;
  gap: 32px;
}

.promo-icon {
  flex-shrink: 0;
}

.icon-gift {
  width: 56px;
  height: 56px;
}

.promo-title {
  font-size: 24px;
  font-weight: 500;
  letter-spacing: 0.1em;
  margin: 0 0 8px 0;
}

.promo-text {
  font-size: 16px;
  opacity: 0.8;
  margin: 0;
}

/* Contact Box */
.contact-box {
  border: 2px solid #000000;
  padding: 32px;
  margin: 48px 0;
}

.contact-title {
  font-size: 18px;
  font-weight: 500;
  letter-spacing: 0.1em;
  margin: 0 0 24px 0;
}

.contact-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 24px;
}

.contact-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.contact-label {
  font-size: 12px;
  letter-spacing: 0.1em;
  opacity: 0.6;
  text-transform: uppercase;
}

.contact-value {
  font-size: 16px;
  font-weight: 500;
}

/* Note Box */
.note-box {
  border: 2px solid #000000;
  padding: 32px;
  background-color: #f9fafb;
  margin: 48px 0;
}

.note-title {
  font-size: 16px;
  font-weight: 500;
  letter-spacing: 0.1em;
  margin: 0 0 20px 0;
}

.note-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.note-list li {
  font-size: 13px;
  opacity: 0.7;
  line-height: 1.6;
  margin-bottom: 12px;
  padding-left: 24px;
  position: relative;
}

.note-list li:before {
  content: '';
  position: absolute;
  left: 0;
  top: 10px;
  width: 8px;
  height: 1px;
  background-color: #000000;
}

.note-list li:last-child {
  margin-bottom: 0;
}

/* Responsive */
@media (max-width: 768px) {
  .title {
    font-size: 32px;
  }
  
  .page-header {
    padding: 32px 0;
  }
  
  .page-header-content {
    padding: 0;
  }
  
  .method-grid {
    grid-template-columns: 1fr;
  }
  
  .promo-box {
    flex-direction: column;
    text-align: center;
    padding: 32px 24px;
  }
  
  .contact-grid {
    grid-template-columns: 1fr;
  }
  
  .policy-item {
    flex-direction: column;
    gap: 16px;
  }
}
</style>