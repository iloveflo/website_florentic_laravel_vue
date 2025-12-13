<template>
  <footer class="site-footer">
    <div class="footer-container">
      
      <div class="footer-top">
        <div class="newsletter-wrapper">
          <h3 class="newsletter-title">ĐĂNG KÝ NHẬN TIN</h3>
          <p class="newsletter-desc">Nhận thông tin cập nhật mới nhất về bộ sưu tập và ưu đãi độc quyền.</p>
          <form class="newsletter-form" @submit.prevent>
            <input type="email" placeholder="EMAIL CỦA BẠN" class="newsletter-input" />
            <button class="newsletter-btn">ĐĂNG KÝ</button>
          </form>
        </div>
      </div>

      <div class="divider"></div>

      <div class="footer-middle">
        <div class="footer-col">
          <h4 class="footer-heading">FLORENTIC</h4>
          <p class="brand-desc">
            Phong cách tối giản, hiện đại và nghệ thuật. Chúng tôi định nghĩa lại vẻ đẹp qua những đường cắt vuông vức.
          </p>
          <div class="contact-info">
            <p>Hotline: 1900 0000</p>
            <p>Email: hello@florentic.com</p>
          </div>
        </div>

        <div class="footer-col">
          <h4 class="footer-heading">KHÁM PHÁ</h4>
          <ul class="footer-links">
            <li><router-link :to="{ name: 'product-category', params: { slug: 'new-arrivals' } }">New Arrivals</router-link></li>
            <li><router-link :to="{ name: 'product-category', params: { slug: 'ao' } }">Áo</router-link></li>
            <li><router-link :to="{ name: 'product-category', params: { slug: 'quan' } }">Quần</router-link></li>
            <li><router-link :to="{ name: 'product-category', params: { slug: 'sale' } }">Sale Off</router-link></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4 class="footer-heading">HỖ TRỢ</h4>
          <ul class="footer-links">
            <li><router-link to="/help/shipping">Chính sách vận chuyển</router-link></li>
            <li><router-link to="/help/returns">Đổi trả & Hoàn tiền</router-link></li>
            <li><router-link to="/help/size-guide">Hướng dẫn chọn size</router-link></li>
            <li><router-link to="/contact">Liên hệ</router-link></li>
          </ul>
        </div>

        <div class="footer-col">
        <h4 class="footer-heading">THEO DÕI</h4>
        <div class="social-links">
            
            <a href="#" class="social-icon">
            <svg class="icon-svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
            </svg>
            <span>FACEBOOK</span>
            </a>

            <a href="#" class="social-icon">
            <svg class="icon-svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
            </svg>
            <span>INSTAGRAM</span>
            </a>

            <a href="#" class="social-icon">
            <svg class="icon-svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5v4a9 9 0 0 1-9-9z"></path>
            </svg>
            <span>TIKTOK</span>
            </a>

            <a href="#" class="social-icon">
            <svg class="icon-svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
                <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02" fill="#000"></polygon> 
                </svg>
            <span>YOUTUBE</span>
            </a>

        </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p class="copyright">© 2025 FLORENTIC. ALL RIGHTS RESERVED.</p>
        <div class="payment-methods">
          <span>VISA</span>
          <span>MASTERCARD</span>
          <span>MOMO</span>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
// Không cần logic phức tạp
</script>

<style scoped>
/* Reset cơ bản */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

.site-footer {
  background-color: #000;
  color: #fff;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Helvetica, Arial, sans-serif;
  padding: 80px 0 30px 0;
  border-top: 1px solid #2a2a2a;
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.footer-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 24px;
}

/* --- NEWSLETTER SECTION --- */
.footer-top {
  max-width: 600px;
  margin: 0 auto 70px auto;
  text-align: center;
}

.newsletter-title {
  font-size: 20px;
  font-weight: 700;
  letter-spacing: 3px;
  margin-bottom: 12px;
  text-transform: uppercase;
  color: #fff;
}

.newsletter-desc {
  font-size: 14px;
  color: #b3b3b3;
  margin-bottom: 28px;
  letter-spacing: 0.3px;
  line-height: 1.5;
}

.newsletter-form {
  display: flex;
  justify-content: center;
  gap: 0;
  max-width: 500px;
  margin: 0 auto;
}

.newsletter-input {
  flex: 1;
  padding: 16px 20px;
  background-color: transparent;
  border: 2px solid #fff;
  border-right: none;
  color: #fff;
  font-size: 14px;
  outline: none;
  border-radius: 0;
  transition: border-color 0.3s ease;
  font-family: inherit;
}

.newsletter-input:focus {
  border-color: #e0e0e0;
}

.newsletter-input::placeholder {
  color: #666;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-size: 12px;
}

.newsletter-btn {
  padding: 16px 32px;
  background-color: #fff;
  color: #000;
  border: 2px solid #fff;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  transition: all 0.3s ease;
  border-radius: 0;
  white-space: nowrap;
}

.newsletter-btn:hover {
  background-color: #000;
  color: #fff;
  transform: translateX(2px);
}

.newsletter-btn:active {
  transform: scale(0.98);
}

/* Divider */
.divider {
  height: 1px;
  background: linear-gradient(to right, transparent, #333 20%, #333 80%, transparent);
  margin-bottom: 70px;
}

/* --- MAIN GRID --- */
.footer-middle {
  display: grid;
  grid-template-columns: 1.8fr 1fr 1fr 0.8fr;
  gap: 60px;
  margin-bottom: 70px;
}

.footer-heading {
  font-size: 15px;
  font-weight: 700;
  margin-bottom: 28px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #fff;
}

.brand-desc {
  font-size: 14px;
  color: #b3b3b3;
  line-height: 1.8;
  margin-bottom: 24px;
  max-width: 350px;
}

.contact-info p {
  font-size: 14px;
  color: #b3b3b3;
  margin: 8px 0;
  line-height: 1.6;
}

.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-links li {
  margin-bottom: 14px;
}

.footer-links a {
  color: #b3b3b3;
  text-decoration: none;
  font-size: 14px;
  transition: all 0.3s ease;
  display: inline-block;
  position: relative;
}

.footer-links a::after {
  content: '';
  position: absolute;
  width: 0;
  height: 1px;
  bottom: -2px;
  left: 0;
  background-color: #fff;
  transition: width 0.3s ease;
}

.footer-links a:hover {
  color: #fff;
  transform: translateX(4px);
}

.footer-links a:hover::after {
  width: 100%;
}

/* Social Icons */
.social-links {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.social-icon {
  color: #fff;
  text-decoration: none;
  font-size: 12px;
  font-weight: 700;
  border: 2px solid #333;
  padding: 14px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  width: fit-content;
  min-width: 160px;
  transition: all 0.3s ease;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  border-radius: 0;
}

.icon-svg {
  display: block;
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.social-icon:hover {
  background-color: #fff;
  color: #000;
  border-color: #fff;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 255, 255, 0.1);
}

.social-icon:hover .icon-svg path,
.social-icon:hover .icon-svg polygon {
  fill: #000;
}

/* --- BOTTOM SECTION --- */
.footer-bottom {
  border-top: 1px solid #2a2a2a;
  padding-top: 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
}

.copyright {
  font-size: 12px;
  color: #666;
  letter-spacing: 1px;
  text-transform: uppercase;
}

.payment-methods {
  display: flex;
  align-items: center;
  gap: 20px;
}

.payment-methods span {
  font-size: 12px;
  color: #666;
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
}

/* --- RESPONSIVE DESIGN --- */

/* Tablet (768px - 1024px) */
@media (max-width: 1024px) {
  .footer-middle {
    grid-template-columns: 1.5fr 1fr 1fr;
    gap: 50px;
  }
  
  .social-links {
    grid-column: span 3;
    flex-direction: row;
    justify-content: flex-start;
    flex-wrap: wrap;
  }
}

/* Tablet nhỏ (600px - 768px) */
@media (max-width: 768px) {
  .site-footer {
    padding: 60px 0 24px 0;
  }
  
  .footer-container {
    padding: 0 20px;
  }
  
  .footer-top {
    margin-bottom: 50px;
  }
  
  .newsletter-title {
    font-size: 18px;
  }
  
  .newsletter-desc {
    font-size: 13px;
  }
  
  .footer-middle {
    grid-template-columns: 1fr 1fr;
    gap: 40px;
  }
  
  .footer-col:first-child {
    grid-column: span 2;
  }
  
  .social-links {
    grid-column: span 2;
  }
  
  .footer-bottom {
    flex-direction: column;
    text-align: center;
    gap: 16px;
  }
}

/* Mobile (max 600px) */
@media (max-width: 600px) {
  .site-footer {
    padding: 50px 0 20px 0;
  }
  
  .footer-top {
    margin-bottom: 40px;
  }
  
  .newsletter-title {
    font-size: 16px;
    letter-spacing: 2px;
  }
  
  .newsletter-desc {
    font-size: 13px;
    margin-bottom: 24px;
  }
  
  .newsletter-form {
    flex-direction: column;
    gap: 12px;
  }
  
  .newsletter-input {
    width: 100%;
    border: 2px solid #fff;
    padding: 14px 18px;
  }
  
  .newsletter-btn {
    width: 100%;
    padding: 14px 24px;
  }
  
  .divider {
    margin-bottom: 40px;
  }
  
  .footer-middle {
    grid-template-columns: 1fr;
    gap: 40px;
    text-align: center;
  }
  
  .footer-heading {
    font-size: 14px;
    margin-bottom: 20px;
  }
  
  .brand-desc {
    margin: 0 auto 20px auto;
    max-width: 100%;
  }
  
  .contact-info p {
    font-size: 13px;
  }
  
  .footer-links a {
    font-size: 13px;
  }
  
  .footer-links a:hover {
    transform: translateX(0);
    transform: scale(1.02);
  }
  
  .social-links {
    flex-direction: column;
    align-items: center;
  }
  
  .social-icon {
    width: 100%;
    max-width: 280px;
    justify-content: center;
    min-width: auto;
  }
  
  .footer-bottom {
    padding-top: 20px;
    gap: 12px;
  }
  
  .copyright,
  .payment-methods span {
    font-size: 11px;
  }
  
  .payment-methods {
    flex-wrap: wrap;
    justify-content: center;
  }
}

/* Mobile nhỏ (max 400px) */
@media (max-width: 400px) {
  .footer-container {
    padding: 0 16px;
  }
  
  .newsletter-title {
    font-size: 15px;
  }
  
  .newsletter-input,
  .newsletter-btn {
    font-size: 13px;
  }
  
  .footer-heading {
    font-size: 13px;
  }
  
  .social-icon {
    padding: 12px 16px;
    font-size: 11px;
    gap: 12px;
  }
  
  .icon-svg {
    width: 16px;
    height: 16px;
  }
}
</style>