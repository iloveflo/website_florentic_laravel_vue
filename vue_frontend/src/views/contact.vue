<template>
  <div class="contact-page">

    <h1 class="title">Liên hệ</h1>

    <section class="card">
      <h2>Thông tin liên hệ</h2>
      <p>FLORENTIC luôn sẵn sàng hỗ trợ bạn trong mọi thắc mắc về sản phẩm, đổi trả hoặc đơn hàng.</p>

      <div class="info-block">
        <div class="info-item">
          <span class="label">Email</span>
          <span class="value">support@florentic.vn</span>
        </div>
        <div class="info-item">
          <span class="label">Hotline</span>
          <span class="value">0900 000 000</span>
        </div>
        <div class="info-item">
          <span class="label">Giờ làm việc</span>
          <span class="value">08:00 – 21:00, Thứ 2 – Chủ Nhật</span>
        </div>
      </div>
    </section>

    <section class="card">
      <h2>Gửi tin nhắn</h2>

      <form class="contact-form">
        <div class="form-row">
          <label>Họ tên</label>
          <input type="text" placeholder="Tên của bạn">
        </div>

        <div class="form-row">
          <label>Email</label>
          <input type="email" placeholder="Email của bạn">
        </div>

        <div class="form-row">
          <label>Nội dung</label>
          <textarea placeholder="Bạn muốn gửi điều gì?"></textarea>
        </div>

        <button type="submit" class="submit-btn">Gửi Tin Nhắn</button>
      </form>
    </section>

  </div>
</template>

<script setup>
</script>

<style scoped>
/* ===== PAGE LAYOUT ===== */
.contact-page {
  max-width: 820px;
  margin: 120px auto;
  padding: 0 20px;
  color: #111;
  font-family: "Inter", sans-serif;
}

/* ===== TITLE ===== */
.title {
  font-size: 34px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  text-align: left;
  margin-bottom: 40px;
  border-left: 6px solid #000;
  padding-left: 16px;
}

/* ===== CARD BLOCK ===== */
.card {
  background: #fff;
  border: 2px solid #000;
  border-radius: 0;
  padding: 28px 30px;
  margin-bottom: 28px;
  transition: 0.3s ease;
}

.card:hover {
  background: #f7f7f7;
  transform: translateY(-2px);
}

/* ===== SUBTITLE ===== */
.card h2 {
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 14px;
  text-transform: uppercase;
}

/* ===== TEXT ===== */
.card p {
  font-size: 15px;
  line-height: 1.6;
  margin-bottom: 18px;
  color: #222;
}

/* ===== INFO BLOCK ===== */
.info-block {
  margin-top: 10px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid #cfcfcf;
}

.info-item:last-child {
  border-bottom: none;
}

.label {
  font-size: 14px;
  text-transform: uppercase;
  font-weight: 600;
}

.value {
  font-size: 14px;
  color: #333;
}

/* ===== FORM ===== */
.contact-form .form-row {
  margin-bottom: 16px;
}

label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 6px;
}

input,
textarea {
  width: 100%;
  border: 2px solid #000;
  border-radius: 0;
  padding: 10px 12px;
  font-size: 14px;
  background: #fff;
  transition: 0.2s ease;
}

input:focus,
textarea:focus {
  outline: none;
  background: #fafafa;
}

textarea {
  min-height: 120px;
  resize: vertical;
}

/* ===== SUBMIT BUTTON ===== */
.submit-btn {
  background: #000;
  color: #fff;
  border: 2px solid #000;
  padding: 12px 18px;
  font-size: 14px;
  font-weight: 700;
  border-radius: 0;
  cursor: pointer;
  transition: 0.25s ease;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.submit-btn:hover {
  background: #fff;
  color: #000;
}
</style>
