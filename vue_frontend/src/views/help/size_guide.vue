<template>
  <div class="size-guide">
    <div class="container">
      <!-- Page Header -->
      <div class="page-header">
        <div class="page-header-content">
          <div>
            <h2 class="title">HƯỚNG DẪN CHỌN SIZE</h2>
            <p class="subtitle">PRECISION IN EVERY CUT</p>
          </div>
          <svg class="icon-ruler" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
      </div>
      <!-- Category Selection -->
      <div class="category-grid">
        <button
          @click="activeCategory = 'shirts'"
          :class="['category-btn', { active: activeCategory === 'shirts' }]"
        >
          ÁO
        </button>
        <button
          @click="activeCategory = 'pants'"
          :class="['category-btn', 'category-btn-right', { active: activeCategory === 'pants' }]"
        >
          QUẦN
        </button>
      </div>

      <!-- Gender Selection -->
      <div class="gender-grid">
        <button
          @click="activeGender = 'male'"
          :class="['gender-btn', { active: activeGender === 'male' }]"
        >
          <svg class="icon-user" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          NAM
        </button>
        <button
          @click="activeGender = 'female'"
          :class="['gender-btn', 'gender-btn-right', { active: activeGender === 'female' }]"
        >
          <svg class="icon-user" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          NỮ
        </button>
      </div>

      <!-- Size Table -->
      <div class="table-wrapper">
        <div class="table-header">
          <h3 class="table-title">ĐƠN VỊ: CENTIMETERS (CM)</h3>
        </div>

        <div class="table-container">
          <table class="size-table">
            <thead>
              <tr>
                <th class="th-size">SIZE</th>
                <th
                  v-for="(measurement, idx) in currentMeasurements"
                  :key="idx"
                  :class="['th-measurement', { 'th-border-right': idx < currentMeasurements.length - 1 }]"
                >
                  {{ measurement }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(item, idx) in currentData"
                :key="idx"
                @click="selectedSize = item.size"
                :class="['table-row', { selected: selectedSize === item.size }]"
              >
                <td class="td-size">{{ item.size }}</td>
                <td
                  v-for="(value, key, i) in item"
                  :key="key"
                  v-show="key !== 'size'"
                  :class="['td-value', { 'td-border-right': i < Object.keys(item).length - 1 }]"
                >
                  {{ value }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Measurement Guide -->
      <div class="guide-section">
        <h3 class="guide-title">HƯỚNG DẪN ĐO</h3>
        <div class="guide-grid">
          <div class="guide-column">
            <div class="guide-item">
              <div class="guide-dot"></div>
              <div>
                <h4 class="guide-heading">VÒNG NGỰC</h4>
                <p class="guide-text">
                  Đo vòng quanh phần rộng nhất của ngực, giữ thước nằm ngang song song với mặt đất
                </p>
              </div>
            </div>
            <div class="guide-item">
              <div class="guide-dot"></div>
              <div>
                <h4 class="guide-heading">VÒNG EO</h4>
                <p class="guide-text">
                  Đo vòng quanh phần nhỏ nhất của eo, thường là phần trên rốn
                </p>
              </div>
            </div>
          </div>
          <div class="guide-column">
            <div class="guide-item">
              <div class="guide-dot"></div>
              <div>
                <h4 class="guide-heading">CHIỀU RỘNG VAI</h4>
                <p class="guide-text">
                  Đo khoảng cách giữa hai điểm khớp vai, ngang qua lưng
                </p>
              </div>
            </div>
            <div class="guide-item">
              <div class="guide-dot"></div>
              <div>
                <h4 class="guide-heading">VÒNG MÔNG</h4>
                <p class="guide-text">
                  Đo vòng quanh phần đầy đặn nhất của mông, giữ thước song song với mặt đất
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Note -->
      <div class="note-box">
        <p class="note-text">
          * Số đo có thể chênh lệch ±2cm tùy theo chất liệu và kiểu dáng sản phẩm. 
          Để được tư vấn size chính xác, vui lòng liên hệ với FLORENTIC qua hotline hoặc chat trực tuyến.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const activeCategory = ref('shirts');
const activeGender = ref('male');
const selectedSize = ref(null);

const sizeData = {
  shirts: {
    male: [
      { size: 'S', chest: '88-92', waist: '74-78', shoulder: '44', length: '68' },
      { size: 'M', chest: '92-96', waist: '78-82', shoulder: '46', length: '70' },
      { size: 'L', chest: '96-100', waist: '82-86', shoulder: '48', length: '72' },
      { size: 'XL', chest: '100-104', waist: '86-90', shoulder: '50', length: '74' },
      { size: 'XXL', chest: '104-108', waist: '90-94', shoulder: '52', length: '76' }
    ],
    female: [
      { size: 'S', chest: '80-84', waist: '62-66', shoulder: '36', length: '60' },
      { size: 'M', chest: '84-88', waist: '66-70', shoulder: '38', length: '62' },
      { size: 'L', chest: '88-92', waist: '70-74', shoulder: '40', length: '64' },
      { size: 'XL', chest: '92-96', waist: '74-78', shoulder: '42', length: '66' }
    ]
  },
  pants: {
    male: [
      { size: 'S', waist: '74-78', hips: '88-92', length: '100', thigh: '54' },
      { size: 'M', waist: '78-82', hips: '92-96', length: '102', thigh: '56' },
      { size: 'L', waist: '82-86', hips: '96-100', length: '104', thigh: '58' },
      { size: 'XL', waist: '86-90', hips: '100-104', length: '106', thigh: '60' },
      { size: 'XXL', waist: '90-94', hips: '104-108', length: '108', thigh: '62' }
    ],
    female: [
      { size: 'S', waist: '62-66', hips: '86-90', length: '96', thigh: '50' },
      { size: 'M', waist: '66-70', hips: '90-94', length: '98', thigh: '52' },
      { size: 'L', waist: '70-74', hips: '94-98', length: '100', thigh: '54' },
      { size: 'XL', waist: '74-78', hips: '98-102', length: '102', thigh: '56' }
    ]
  }
};

const measurements = {
  shirts: ['NGỰC', 'EO', 'VAI', 'DÀI ÁO'],
  pants: ['EO', 'MÔNG', 'DÀI QUẦN', 'ĐÙI']
};

const currentData = computed(() => sizeData[activeCategory.value][activeGender.value]);
const currentMeasurements = computed(() => measurements[activeCategory.value]);
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.size-guide {
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
  margin-bottom: 48px;
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

.icon-ruler {
  width: 48px;
  height: 48px;
}

/* Category Selection */
.category-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0;
  margin-bottom: 48px;
  border: 2px solid #000000;
}

.category-btn {
  padding: 24px 0;
  font-size: 18px;
  letter-spacing: 0.2em;
  background-color: #ffffff;
  color: #000000;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  font-family: inherit;
}

.category-btn-right {
  border-left: 2px solid #000000;
}

.category-btn:hover {
  background-color: #f3f4f6;
}

.category-btn.active {
  background-color: #000000;
  color: #ffffff;
}

/* Gender Selection */
.gender-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0;
  margin-bottom: 64px;
  border: 2px solid #000000;
}

.gender-btn {
  padding: 20px 0;
  font-size: 16px;
  letter-spacing: 0.2em;
  background-color: #ffffff;
  color: #000000;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  font-family: inherit;
}

.gender-btn-right {
  border-left: 2px solid #000000;
}

.gender-btn:hover {
  background-color: #f3f4f6;
}

.gender-btn.active {
  background-color: #000000;
  color: #ffffff;
}

.icon-user {
  width: 20px;
  height: 20px;
}

/* Table */
.table-wrapper {
  border: 2px solid #000000;
  overflow: hidden;
  margin-bottom: 64px;
}

.table-header {
  background-color: #000000;
  color: #ffffff;
  padding: 16px 24px;
}

.table-title {
  font-size: 14px;
  letter-spacing: 0.2em;
  font-weight: 300;
  margin: 0;
}

.table-container {
  overflow-x: auto;
}

.size-table {
  width: 100%;
  border-collapse: collapse;
}

.size-table thead tr {
  border-bottom: 2px solid #000000;
}

.th-size {
  padding: 16px 24px;
  text-align: left;
  font-size: 14px;
  letter-spacing: 0.2em;
  font-weight: 400;
  border-right: 2px solid #000000;
  background-color: #f9fafb;
}

.th-measurement {
  padding: 16px 24px;
  text-align: center;
  font-size: 12px;
  letter-spacing: 0.2em;
  font-weight: 400;
  background-color: #f9fafb;
}

.th-border-right {
  border-right: 2px solid #000000;
}

.table-row {
  border-bottom: 2px solid #000000;
  cursor: pointer;
  transition: all 0.2s ease;
}

.table-row:hover {
  background-color: #f9fafb;
}

.table-row.selected {
  background-color: #000000;
  color: #ffffff;
}

.td-size {
  padding: 20px 24px;
  font-weight: 500;
  font-size: 18px;
  letter-spacing: 0.05em;
  border-right: 2px solid #000000;
}

.td-value {
  padding: 20px 24px;
  text-align: center;
  font-weight: 300;
}

.td-border-right {
  border-right: 2px solid #000000;
}

/* Measurement Guide */
.guide-section {
  margin-top: 64px;
  border: 2px solid #000000;
  padding: 32px;
}

.guide-title {
  font-size: 24px;
  letter-spacing: 0.2em;
  margin: 0 0 32px 0;
  font-weight: 300;
}

.guide-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 32px;
}

@media (min-width: 768px) {
  .guide-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.guide-column {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.guide-item {
  display: flex;
  align-items: flex-start;
  gap: 16px;
}

.guide-dot {
  width: 8px;
  height: 8px;
  background-color: #000000;
  margin-top: 8px;
  flex-shrink: 0;
}

.guide-heading {
  font-weight: 500;
  letter-spacing: 0.05em;
  margin: 0 0 4px 0;
  font-size: 16px;
}

.guide-text {
  font-size: 14px;
  opacity: 0.7;
  line-height: 1.6;
  margin: 0;
}

/* Note */
.note-box {
  margin-top: 32px;
  padding: 24px;
  border: 2px solid #000000;
  background-color: #f9fafb;
}

.note-text {
  font-size: 12px;
  letter-spacing: 0.05em;
  opacity: 0.7;
  line-height: 1.6;
  margin: 0;
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
  
  .category-btn {
    padding: 20px 0;
    font-size: 16px;
  }
  
  .guide-section {
    padding: 24px;
  }
}
</style>