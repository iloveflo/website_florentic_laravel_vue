<template>
  <div class="returns-page">
    <h1 class="title">Đổi Trả & Hoàn Tiền</h1>

    <section class="card">
      <h2>1. Điều kiện đổi trả</h2>
      <p>Sản phẩm phải còn nguyên tem mác, chưa qua sử dụng và còn đầy đủ phụ kiện đi kèm.</p>
      <p>Thời gian áp dụng đổi trả là trong vòng <strong>07 ngày</strong> kể từ ngày nhận hàng.</p>
    </section>

    <section class="card">
      <h2>2. Sản phẩm không được đổi trả</h2>
      <ul>
        <li>Sản phẩm đã qua sử dụng hoặc có dấu hiệu hư hỏng.</li>
        <li>Sản phẩm thuộc danh mục sale mạnh hoặc ưu đãi đặc biệt.</li>
        <li>Phụ kiện cá nhân như tất, mũ, nón....</li>
      </ul>
    </section>

    <section class="card">
      <h2>3. Các phương thức hoàn tiền</h2>
      <p>Hoàn tiền qua:</p>
      <ul>
        <li>Ví điện tử</li>
        <li>Tài khoản ngân hàng</li>
        <li>Mã giảm giá cho đơn kế tiếp</li>
      </ul>
    </section>

    <section class="card">
      <h2>4. Quy trình xử lý đổi trả</h2>
      <ol>
        <li>Liên hệ qua hotline hoặc chat để gửi yêu cầu đổi trả.</li>
        <li>Gửi lại sản phẩm cho FLORENTIC theo hướng dẫn.</li>
        <li>Kiểm tra chất lượng trong 24–48 giờ.</li>
        <li>Tiến hành đổi hoặc hoàn tiền theo yêu cầu.</li>
      </ol>
    </section>

    <section class="card">
      <h2>5. Liên hệ hỗ trợ</h2>
      <p>
        Email: support@florentic.vn<br />
        Hotline: 0900 000 000
      </p>
    </section>
  </div>
</template>

<script setup>
</script>

<style scoped>
/* ===== PAGE LAYOUT ===== */
.returns-page {
  max-width: 820px;
  margin: 60px auto;
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

/* ===== CARD SECTION ===== */
.card {
  background: #fff;
  border: 2px solid #000;
  border-radius: 0; /* VUÔNG VỨC HOÀN TOÀN */
  padding: 28px 30px;
  margin-bottom: 28px;
  transition: 0.3s ease;
}

.card:hover {
  background: #f7f7f7;
  transform: translateY(-2px);
}

/* ===== SECTION HEADINGS ===== */
.card h2 {
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 14px;
  text-transform: uppercase;
}

/* ===== TEXT ===== */
.card p,
.card li {
  font-size: 15px;
  line-height: 1.6;
  color: #222;
}

ul, ol {
  margin-left: 20px;
}

/* ===== LIST STYLE ===== */
ul li {
  list-style: square;
}

ol li {
  list-style: decimal;
}
</style>
