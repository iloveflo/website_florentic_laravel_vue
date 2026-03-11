# 🌸 Florentic – E-Commerce Website

<p align="center">
  <strong>Website thương mại điện tử full-stack được xây dựng với Laravel 12 & Vue 3</strong>
  <br>
  Dự án này chỉ mang tính chất học tập và tham khảo, không được sử dụng cho mục đích thương mại.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12"/>
  <img src="https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue 3"/>
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2"/>
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL 8.0"/>
  <img src="https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker"/>
  <img src="https://img.shields.io/badge/Vite-7-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite 7"/>
</p>

---

## 📋 Mục lục

- [Giới thiệu](#-giới-thiệu)
- [Tính năng](#-tính-năng)
- [Công nghệ sử dụng](#-công-nghệ-sử-dụng)
- [Kiến trúc hệ thống](#-kiến-trúc-hệ-thống)
- [Cấu trúc thư mục](#-cấu-trúc-thư-mục)
- [Yêu cầu hệ thống](#-yêu-cầu-hệ-thống)
- [Cài đặt & Chạy](#-cài-đặt--chạy)
- [Biến môi trường](#-biến-môi-trường)
- [API Endpoints](#-api-endpoints)
- [Frontend Routes](#-frontend-routes)
- [CI/CD & Deployment](#-cicd--deployment)
- [Bảo mật](#-bảo-mật)
- [Đóng góp](#-đóng-góp)
- [License](#-license)

---

## 📖 Giới thiệu

**Florentic** là một dự án website thương mại điện tử hoàn chỉnh, được thiết kế theo kiến trúc **API-driven SPA** (Single Page Application). Backend sử dụng **Laravel 12** cung cấp RESTful API, frontend sử dụng **Vue 3** với **Vite** để xây dựng giao diện người dùng hiện đại, nhanh và mượt mà.

Dự án hỗ trợ đầy đủ các chức năng cần thiết cho một sàn thương mại điện tử: từ duyệt sản phẩm, giỏ hàng, thanh toán trực tuyến, đến trang quản trị với thống kê và biểu đồ chi tiết.

---

## ✨ Tính năng

### 🛒 Dành cho khách hàng (Customer)

| Chức năng | Mô tả |
|-----------|-------|
| **Đăng ký / Đăng nhập** | Xác thực bằng email hoặc qua **Google / Facebook** (OAuth 2.0) |
| **Captcha** | Bảo mật form đăng nhập bằng captcha hình ảnh |
| **Quên / Đặt lại mật khẩu** | Gửi email đặt lại mật khẩu có rate-limiting |
| **Hồ sơ cá nhân** | Xem và cập nhật thông tin cá nhân, avatar |
| **Trang chủ** | Banner, sản phẩm nổi bật, danh mục |
| **Duyệt sản phẩm** | Xem sản phẩm theo danh mục (slug-based routing) |
| **Chi tiết sản phẩm** | Xem hình ảnh, biến thể (size, màu), đánh giá từ người mua |
| **Tìm kiếm** | Tìm kiếm sản phẩm toàn site |
| **Giỏ hàng** | Thêm, sửa số lượng, xóa sản phẩm, mua lại đơn cũ |
| **Mã giảm giá** | Áp dụng coupon khi thanh toán |
| **Thanh toán VNPay** | Tích hợp cổng thanh toán VNPay với callback xác nhận |
| **Theo dõi đơn hàng** | Xem danh sách đơn, chi tiết đơn hàng, trạng thái |
| **Đánh giá sản phẩm** | Đánh giá sao và nhận xét sau khi mua hàng |
| **Chatbot AI** | Trợ lý ảo hỗ trợ tương tác trực tiếp trên website |
| **Trang giới thiệu** | Giới thiệu về thương hiệu Florentic |
| **Trang liên hệ** | Form liên hệ cho khách hàng |
| **Trung tâm trợ giúp** | Chính sách vận chuyển, đổi trả & hoàn tiền, hướng dẫn chọn size |

### 🛡️ Dành cho quản trị viên (Admin)

| Chức năng | Mô tả |
|-----------|-------|
| **Dashboard** | Tổng quan KPI: doanh thu, đơn hàng, người dùng |
| **Biểu đồ doanh thu** | Biểu đồ doanh thu theo thời gian (Chart.js) |
| **Biểu đồ danh mục** | Phân tích sản phẩm bán chạy theo danh mục |
| **Biểu đồ đơn hàng** | Biểu đồ tròn phân bố trạng thái đơn hàng |
| **Top sản phẩm** | Danh sách sản phẩm bán chạy nhất |
| **Hoạt động gần đây** | Đơn hàng mới, sản phẩm sắp hết hàng |
| **Xuất báo cáo** | Export báo cáo ra file PDF (DomPDF) |
| **Quản lý sản phẩm** | CRUD sản phẩm, upload/quản lý nhiều hình ảnh, đặt ảnh chính |
| **Quản lý danh mục** | Danh mục dạng cây (tree structure) |
| **Quản lý đơn hàng** | Xem chi tiết, cập nhật trạng thái đơn hàng |
| **Quản lý người dùng** | Tạo, sửa, khóa, xóa mềm, khôi phục tài khoản |
| **Quản lý khuyến mãi** | Tạo và quản lý mã coupon (CRUD đầy đủ) |

---

## 🛠 Công nghệ sử dụng

### Backend

| Công nghệ | Phiên bản | Mục đích |
|-----------|-----------|----------|
| PHP | 8.2 | Ngôn ngữ chính |
| Laravel | 12 | Framework API |
| Laravel Sanctum | 4.x | Xác thực API bằng token |
| Laravel Socialite | 5.x | Đăng nhập qua Google / Facebook |
| DomPDF | 3.x | Xuất báo cáo PDF |
| Mews Captcha | 3.x | Captcha bảo mật |

### Frontend

| Công nghệ | Phiên bản | Mục đích |
|-----------|-----------|----------|
| Vue.js | 3.5 | Framework SPA |
| Vue Router | 4.x | Client-side routing (History mode) |
| Vite | 7.x | Build tool & dev server |
| Axios | 1.x | HTTP client cho API |
| Chart.js + vue-chartjs | 4.x / 5.x | Biểu đồ thống kê admin |
| vue-virtual-scroller | 2.x | Virtual scrolling cho danh sách lớn |

### Infrastructure

| Công nghệ | Mục đích |
|-----------|----------|
| Docker | Đóng gói ứng dụng |
| Docker Compose | Orchestration multi-container |
| Nginx | Reverse proxy & serve static files |
| PHP-FPM | PHP process manager |
| Supervisor | Quản lý process (Nginx + PHP-FPM) |
| MySQL 8.0 | Cơ sở dữ liệu |
| GitHub Actions | CI/CD pipeline |
| Docker Hub | Container registry |
| Render | Cloud hosting platform |

---

## 🏗 Kiến trúc hệ thống

```
                        ┌─────────────────┐
                        │   Client Browser │
                        └────────┬────────┘
                                 │ HTTP :8000
                                 ▼
┌──────────────────────────────────────────────────────────────┐
│                    Docker Container: app                      │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐     │
│  │              Supervisor (PID 1)                      │     │
│  │  ┌─────────────────┐    ┌────────────────────────┐  │     │
│  │  │     Nginx       │    │      PHP-FPM           │  │     │
│  │  │   (port 80)     │───▶│   (port 9000)          │  │     │
│  │  │                 │    │                        │  │     │
│  │  │ ┌─────────────┐ │    │ ┌────────────────────┐ │  │     │
│  │  │ │ Static Files│ │    │ │   Laravel 12 API   │ │  │     │
│  │  │ │  (Vue SPA)  │ │    │ │                    │ │  │     │
│  │  │ │  /public/*  │ │    │ │ • Auth (Sanctum)   │ │  │     │
│  │  │ └─────────────┘ │    │ │ • Products CRUD    │ │  │     │
│  │  └─────────────────┘    │ │ • Cart & Checkout  │ │  │     │
│  │                         │ │ • Orders           │ │  │     │
│  │                         │ │ • Statistics       │ │  │     │
│  │                         │ │ • Chat AI          │ │  │     │
│  │                         │ └────────────────────┘ │  │     │
│  │                         └────────────────────────┘  │     │
│  └─────────────────────────────────────────────────────┘     │
└───────────────────────────────┬──────────────────────────────┘
                                │ TCP :3306
                                ▼
                ┌──────────────────────────────┐
                │   Docker Container: db       │
                │                              │
                │       MySQL 8.0              │
                │   ┌──────────────────────┐   │
                │   │  Database: laravel_app│   │
                │   │  Volume: db_data     │   │
                │   │  Init: init.sql      │   │
                │   └──────────────────────┘   │
                └──────────────────────────────┘
```

### Luồng hoạt động

1. **Client** gửi request đến `http://localhost:8000`
2. **Docker** forward port `8000` → `80` trong container `app`
3. **Nginx** nhận request:
   - Nếu là static file (JS, CSS, hình ảnh) → phục vụ trực tiếp từ `/public`
   - Nếu là API request (`/api/*`) → forward sang **PHP-FPM** (:9000)
   - Nếu không match → trả về `index.php` (SPA fallback)
4. **PHP-FPM** xử lý request thông qua **Laravel** framework
5. **Laravel** tương tác với **MySQL** để đọc/ghi dữ liệu
6. **Supervisor** giám sát và tự động restart Nginx/PHP-FPM nếu crash

### Multi-stage Docker Build

```
Stage 1: vue_builder (node:20-alpine)
  └── npm install → npm run build → output: /app/frontend/frontend/

Stage 2: Final Image (php:8.2-fpm)
  └── Install: Nginx, Supervisor, PHP extensions
  └── Copy: Laravel code + Composer install
  └── Copy: Vue build từ Stage 1 → /public/
  └── CMD: supervisord
```

---

## 📁 Cấu trúc thư mục

```
website_florentic_laravel_vue/
│
├── 📂 laravel_api/                     # ── Backend Laravel 12 ──
│   ├── 📂 app/
│   │   ├── 📂 Http/
│   │   │   ├── 📂 Controllers/
│   │   │   │   ├── AuthController.php         # Đăng ký, đăng nhập, OAuth, quên MK
│   │   │   │   ├── ChatController.php         # Chatbot AI
│   │   │   │   ├── SearchController.php       # Tìm kiếm sản phẩm
│   │   │   │   ├── 📂 Admin/
│   │   │   │   │   ├── CategoryController.php     # Quản lý danh mục
│   │   │   │   │   ├── CouponController.php       # Quản lý khuyến mãi
│   │   │   │   │   ├── OrderController.php        # Quản lý đơn hàng + đánh giá
│   │   │   │   │   ├── ProductAdminController.php # CRUD sản phẩm
│   │   │   │   │   ├── ProductImageController.php # Quản lý hình ảnh SP
│   │   │   │   │   ├── StatisticsController.php   # Thống kê & biểu đồ
│   │   │   │   │   └── UserController.php         # Quản lý người dùng
│   │   │   │   ├── 📂 Product/
│   │   │   │   │   ├── ProductController.php      # Hiển thị SP theo danh mục
│   │   │   │   │   └── ProductDetailsController.php # Chi tiết SP
│   │   │   │   └── 📂 User/
│   │   │   │       ├── CartController.php         # Giỏ hàng
│   │   │   │       ├── CheckoutController.php     # Thanh toán + VNPay
│   │   │   │       ├── OrderUserController.php    # Đơn hàng khách
│   │   │   │       └── ProfileController.php      # Hồ sơ cá nhân
│   │   │   ├── 📂 Middleware/
│   │   │   │   └── AdminMiddleware.php        # Kiểm tra quyền admin
│   │   │   └── 📂 Requests/                   # Form Request validation
│   │   ├── 📂 Models/
│   │   │   ├── User.php                  # Người dùng
│   │   │   ├── Product.php               # Sản phẩm
│   │   │   ├── ProductVariant.php        # Biến thể SP (size, màu)
│   │   │   ├── ProductImage.php          # Hình ảnh SP
│   │   │   ├── Category.php              # Danh mục (cây)
│   │   │   ├── Order.php                 # Đơn hàng
│   │   │   ├── OrderItem.php             # Chi tiết đơn hàng
│   │   │   ├── CartSession.php           # Phiên giỏ hàng
│   │   │   ├── Coupon.php                # Mã giảm giá
│   │   │   ├── CouponUsage.php           # Lịch sử dùng coupon
│   │   │   ├── Review.php                # Đánh giá SP
│   │   │   └── ChatbotConversation.php   # Lịch sử chat AI
│   │   ├── 📂 Services/
│   │   │   └── ReportService.php         # Logic xuất báo cáo PDF
│   │   └── 📂 Providers/
│   ├── 📂 config/
│   │   ├── sanctum.php                   # Cấu hình Sanctum token
│   │   ├── services.php                  # Cấu hình Google, Facebook OAuth
│   │   ├── vnpay.php                     # Cấu hình cổng thanh toán VNPay
│   │   └── captcha.php                   # Cấu hình captcha
│   ├── 📂 database/
│   │   ├── 📂 migrations/                # Schema migrations
│   │   ├── 📂 factories/                 # Model factories
│   │   └── 📂 seeders/                   # Database seeders
│   ├── 📂 routes/
│   │   ├── api.php                       # RESTful API routes (120+ lines)
│   │   └── web.php                       # OAuth callback + SPA fallback
│   ├── composer.json
│   └── .env.example
│
├── 📂 vue_frontend/                     # ── Frontend Vue 3 SPA ──
│   ├── 📂 src/
│   │   ├── main.js                       # Entry point + Axios interceptors
│   │   ├── App.vue                       # Root component
│   │   ├── style.css                     # Global styles
│   │   ├── 📂 routes/
│   │   │   └── index.js                  # Vue Router (25+ routes)
│   │   ├── 📂 views/
│   │   │   ├── home.vue                  # Trang chủ
│   │   │   ├── login.vue                 # Đăng nhập + OAuth
│   │   │   ├── register.vue              # Đăng ký
│   │   │   ├── forgotpassword.vue        # Quên mật khẩu
│   │   │   ├── resetpassword.vue         # Đặt lại mật khẩu
│   │   │   ├── search.vue                # Tìm kiếm
│   │   │   ├── about.vue                 # Giới thiệu
│   │   │   ├── contact.vue               # Liên hệ
│   │   │   ├── PaymentResult.vue         # Kết quả thanh toán VNPay
│   │   │   ├── admin.vue                 # Layout admin dashboard
│   │   │   ├── 📂 products/
│   │   │   │   ├── index.vue             # Danh sách sản phẩm
│   │   │   │   ├── productcategory.vue   # SP theo danh mục
│   │   │   │   └── productdetails.vue    # Chi tiết SP
│   │   │   ├── 📂 user/
│   │   │   │   ├── cart.vue              # Giỏ hàng
│   │   │   │   ├── profile.vue           # Hồ sơ cá nhân
│   │   │   │   ├── orders.vue            # Danh sách đơn hàng
│   │   │   │   ├── orderdetail.vue       # Chi tiết đơn hàng
│   │   │   │   └── review.vue            # Đánh giá sản phẩm
│   │   │   ├── 📂 admin/
│   │   │   │   ├── quanlynguoidung.vue   # Quản lý người dùng
│   │   │   │   ├── quanlysanpham.vue     # Quản lý sản phẩm
│   │   │   │   ├── quanlydonhang.vue     # Quản lý đơn hàng
│   │   │   │   ├── quanlykhuyenmai.vue   # Quản lý khuyến mãi
│   │   │   │   └── thongkebaocao.vue     # Thống kê & báo cáo
│   │   │   └── 📂 help/
│   │   │       ├── shipping.vue          # Chính sách vận chuyển
│   │   │       ├── returns.vue           # Đổi trả & hoàn tiền
│   │   │       └── size_guide.vue        # Hướng dẫn chọn size
│   │   └── 📂 components/
│   │       ├── header.vue                # Header & navigation
│   │       ├── footer.vue                # Footer
│   │       ├── ChatWidget.vue            # Widget chatbot AI
│   │       ├── WelcomePopup.vue          # Popup chào mừng
│   │       └── 📂 admin/                 # Components admin riêng
│   ├── index.html
│   ├── vite.config.js                    # Vite config + proxy API
│   └── package.json
│
├── 📂 docker-conf/                      # ── Cấu hình Docker ──
│   ├── nginx.conf                        # Nginx reverse proxy config
│   └── supervisord.conf                  # Supervisor process manager
│
├── 📂 .github/workflows/               # ── CI/CD ──
│   └── deploy.yml                        # GitHub Actions pipeline
│
├── Dockerfile                            # Multi-stage build
├── docker-compose.yml                    # Docker Compose orchestration
├── init.sql                              # Database dump (khởi tạo dữ liệu)
└── README.md                             # 📖 Bạn đang đọc file này
```

---

## 📦 Yêu cầu hệ thống

### Chạy với Docker (Khuyến nghị)

| Yêu cầu | Phiên bản |
|----------|-----------|
| Docker | >= 20.x |
| Docker Compose | >= 2.x |
| RAM | >= 2 GB |
| Disk | >= 2 GB |

### Chạy local (Development)

| Yêu cầu | Phiên bản |
|----------|-----------|
| PHP | >= 8.2 |
| Composer | >= 2.x |
| Node.js | >= 20.x |
| npm | >= 9.x |
| MySQL | >= 8.0 |

---

## 🚀 Cài đặt & Chạy

### 🐳 Cách 1: Sử dụng Docker (Khuyến nghị)

```bash
# 1. Clone repository
git clone https://github.com/iloveflo/website_florentic_laravel_vue.git
cd website_florentic_laravel_vue

# 2. Khởi chạy với Docker Compose
docker compose up -d --build

# 3. Truy cập website
#    🌐 http://localhost:8000
```

> **📌 Lưu ý:** File `init.sql` sẽ tự động import dữ liệu mẫu vào MySQL khi container database khởi tạo lần đầu. Nếu bạn muốn reset dữ liệu, xóa volume và build lại:
> ```bash
> docker compose down -v
> docker compose up -d --build
> ```

### 💻 Cách 2: Chạy local (Development)

#### Bước 1 – Cài đặt Backend

```bash
cd laravel_api

# Copy file cấu hình
cp .env.example .env

# Cài dependencies
composer install

# Sinh khóa ứng dụng
php artisan key:generate

# Cấu hình database trong file .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel_app
# DB_USERNAME=root
# DB_PASSWORD=your_password

# Chạy migration
php artisan migrate

# (Tùy chọn) Import dữ liệu mẫu
mysql -u root -p laravel_app < ../init.sql

# Khởi chạy server backend
php artisan serve
# → Backend chạy tại http://localhost:8000
```

#### Bước 2 – Cài đặt Frontend

```bash
cd vue_frontend

# Cài dependencies
npm install

# Khởi chạy dev server
npm run dev
# → Frontend chạy tại http://localhost:5173
# → API requests tự động proxy sang http://localhost:8000
```

#### Bước 3 – Build Frontend cho Production

```bash
cd vue_frontend
npm run build
# → Output được tạo tại vue_frontend/frontend/
```

---

## 🔧 Biến môi trường

### Laravel Backend (`laravel_api/.env`)

| Biến | Mô tả | Ví dụ |
|------|--------|-------|
| `APP_NAME` | Tên ứng dụng | `Florentic` |
| `APP_ENV` | Môi trường | `local` / `production` |
| `APP_KEY` | Khóa mã hóa (auto-generate) | `base64:...` |
| `APP_DEBUG` | Chế độ debug | `true` / `false` |
| `APP_URL` | URL ứng dụng | `http://localhost` |
| **Database** | | |
| `DB_CONNECTION` | Driver database | `mysql` |
| `DB_HOST` | Host database | `127.0.0.1` / `db` (Docker) |
| `DB_PORT` | Port database | `3306` |
| `DB_DATABASE` | Tên database | `laravel_app` |
| `DB_USERNAME` | Username database | `root` |
| `DB_PASSWORD` | Password database | `rootpassword` |
| **OAuth – Google** | | |
| `GOOGLE_CLIENT_ID` | Google OAuth Client ID | |
| `GOOGLE_CLIENT_SECRET` | Google OAuth Client Secret | |
| `GOOGLE_REDIRECT_URI` | Google callback URL |
| **OAuth – Facebook** | | |
| `FACEBOOK_CLIENT_ID` | Facebook App ID | |
| `FACEBOOK_CLIENT_SECRET` | Facebook App Secret | |
| `FACEBOOK_REDIRECT_URI` | Facebook callback URL |
| **VNPay** | | |
| `VNP_TMN_CODE` | Mã website trên VNPay | |
| `VNP_HASH_SECRET` | Chuỗi bí mật VNPay | |
| `VNP_URL` | URL thanh toán VNPay | `https://sandbox.vnpayment.vn/paymentv2/vpcpay.html` |
| `VNP_RETURN_URL` | URL callback sau thanh toán |

---

## 📡 API Endpoints

### Xác thực (Authentication)

| Method | Endpoint | Mô tả | Auth |
|--------|----------|--------|------|
| `GET` | `/api/captcha` | Lấy captcha hình ảnh | ❌ |
| `POST` | `/api/login` | Đăng nhập (rate limit: 5/phút) | ❌ |
| `POST` | `/api/register` | Đăng ký tài khoản mới | ❌ |
| `POST` | `/api/logout` | Đăng xuất | ✅ |
| `GET` | `/api/me` | Lấy thông tin user hiện tại | ✅ |
| `POST` | `/api/forgot-password` | Gửi email reset mật khẩu | ❌ |
| `POST` | `/api/reset-password` | Đặt lại mật khẩu | ❌ |

### OAuth (Web Routes)

| Method | Endpoint | Mô tả |
|--------|----------|--------|
| `GET` | `/auth/google` | Redirect đến Google OAuth |
| `GET` | `/auth/google/callback` | Google callback |
| `GET` | `/auth/facebook` | Redirect đến Facebook OAuth |
| `GET` | `/auth/facebook/callback` | Facebook callback |

### Sản phẩm (Products)

| Method | Endpoint | Mô tả | Auth |
|--------|----------|--------|------|
| `GET` | `/api/products` | Danh sách tất cả sản phẩm | ❌ |
| `GET` | `/api/products/{slug}` | Chi tiết sản phẩm theo slug | ❌ |
| `GET` | `/api/products/category/{slug}` | Sản phẩm theo danh mục | ❌ |
| `GET` | `/api/search` | Tìm kiếm sản phẩm | ❌ |

### Giỏ hàng (Cart)

| Method | Endpoint | Mô tả | Auth |
|--------|----------|--------|------|
| `GET` | `/api/cart` | Xem giỏ hàng | ❌ |
| `POST` | `/api/cart/add` | Thêm sản phẩm vào giỏ | ❌ |
| `PUT` | `/api/cart/update` | Cập nhật số lượng | ❌ |
| `DELETE` | `/api/cart/remove/{id}` | Xóa sản phẩm khỏi giỏ | ❌ |
| `POST` | `/api/cart/buy-again` | Mua lại đơn hàng cũ | ❌ |

### Thanh toán (Checkout)

| Method | Endpoint | Mô tả | Auth |
|--------|----------|--------|------|
| `GET` | `/api/checkout/info` | Lấy thông tin thanh toán | ❌ |
| `POST` | `/api/checkout/process` | Xử lý đặt hàng | ❌ |
| `GET` | `/api/payment/vnpay-callback` | VNPay callback xác nhận | ❌ |

### Đơn hàng (Orders)

| Method | Endpoint | Mô tả | Auth |
|--------|----------|--------|------|
| `GET` | `/api/orders` | Danh sách đơn hàng | ❌ |
| `GET` | `/api/orders/{code}` | Chi tiết đơn hàng | ❌ |

### Đánh giá (Reviews)

| Method | Endpoint | Mô tả | Auth |
|--------|----------|--------|------|
| `GET` | `/api/orders/{order_code}/review-info` | Thông tin đánh giá đơn hàng | ❌ |
| `POST` | `/api/reviews` | Gửi đánh giá sản phẩm | ❌ |

### Chatbot AI

| Method | Endpoint | Mô tả | Auth |
|--------|----------|--------|------|
| `POST` | `/api/chat` | Gửi tin nhắn cho chatbot | ❌ |
| `GET` | `/api/chat/history` | Lấy lịch sử chat | ❌ |

### Hồ sơ (Profile)

| Method | Endpoint | Mô tả | Auth |
|--------|----------|--------|------|
| `GET` | `/api/profile` | Xem hồ sơ cá nhân | ✅ Sanctum |
| `POST` | `/api/update` | Cập nhật hồ sơ | ✅ Sanctum |

### Admin – Quản lý đơn hàng

| Method | Endpoint | Mô tả |
|--------|----------|--------|
| `GET` | `/api/admin/orders` | Danh sách tất cả đơn hàng |
| `GET` | `/api/admin/orders/{order_code}` | Chi tiết đơn hàng |
| `PUT` | `/api/admin/{order_code}/status` | Cập nhật trạng thái đơn |

### Admin – Quản lý người dùng

| Method | Endpoint | Mô tả |
|--------|----------|--------|
| `GET` | `/api/admin/users` | Danh sách người dùng |
| `POST` | `/api/admin/users` | Tạo user/admin mới |
| `GET` | `/api/admin/users/{id}` | Chi tiết người dùng |
| `PUT` | `/api/admin/users/{id}` | Cập nhật thông tin |
| `DELETE` | `/api/admin/users/{id}` | Xóa mềm người dùng |
| `PATCH` | `/api/admin/users/{id}/status` | Khóa/mở khóa tài khoản |
| `PATCH` | `/api/admin/users/{id}/restore` | Khôi phục user đã xóa |
| `GET` | `/api/admin/users/deleted` | DS user đã xóa |
| `GET` | `/api/admin/users/{id}/orders` | Lịch sử đơn hàng của user |

### Admin – Quản lý sản phẩm

| Method | Endpoint | Mô tả |
|--------|----------|--------|
| `GET` | `/api/admin/products` | Danh sách sản phẩm |
| `POST` | `/api/admin/products` | Tạo sản phẩm mới |
| `GET` | `/api/admin/products/{id}` | Chi tiết sản phẩm |
| `PUT` | `/api/admin/products/{id}` | Cập nhật sản phẩm |
| `DELETE` | `/api/admin/products/{id}` | Xóa sản phẩm |
| `GET` | `/api/admin/products/{id}/images` | DS hình ảnh SP |
| `POST` | `/api/admin/products/{id}/images` | Upload hình ảnh |
| `DELETE` | `/api/admin/products/images/{id}` | Xóa hình ảnh |
| `PATCH` | `/api/admin/products/images/{id}/primary` | Đặt ảnh chính |
| `GET` | `/api/admin/products/categories` | DS danh mục |
| `GET` | `/api/admin/products/categories/tree` | Cây danh mục |

### Admin – Khuyến mãi

| Method | Endpoint | Mô tả |
|--------|----------|--------|
| `GET` | `/api/admin/coupons` | Danh sách coupon |
| `POST` | `/api/admin/coupons` | Tạo coupon mới |
| `GET` | `/api/admin/coupons/{id}` | Chi tiết coupon |
| `PUT` | `/api/admin/coupons/{id}` | Cập nhật coupon |
| `DELETE` | `/api/admin/coupons/{id}` | Xóa coupon |

### Admin – Thống kê

| Method | Endpoint | Mô tả |
|--------|----------|--------|
| `GET` | `/api/admin/statistics/overview` | KPI tổng quan |
| `GET` | `/api/admin/statistics/revenue-over-time` | Biểu đồ doanh thu |
| `GET` | `/api/admin/statistics/sales-by-category` | Doanh số theo danh mục |
| `GET` | `/api/admin/statistics/order-status-distribution` | Phân bố trạng thái đơn |
| `GET` | `/api/admin/statistics/top-selling-products` | Top sản phẩm bán chạy |
| `GET` | `/api/admin/statistics/recent-activities` | Hoạt động gần đây |
| `GET` | `/api/admin/statistics/export` | Xuất báo cáo PDF |

> **📌 Tất cả Admin routes đều yêu cầu:** `auth:sanctum` + `admin` middleware

---

## 🗺 Frontend Routes

### Trang công khai

| Route | Trang | Mô tả |
|-------|-------|-------|
| `/` | Home | Trang chủ |
| `/login` | Login | Đăng nhập |
| `/register` | Register | Đăng ký |
| `/forgot-password` | Forgot Password | Quên mật khẩu |
| `/reset-password` | Reset Password | Đặt lại mật khẩu |
| `/about` | About | Giới thiệu |
| `/contact` | Contact | Liên hệ |
| `/search` | Search | Tìm kiếm |
| `/products` | Products | Tất cả sản phẩm |
| `/products/category/:slug` | Category | SP theo danh mục |
| `/product/:slug` | Product Details | Chi tiết sản phẩm |

### Trang người dùng

| Route | Trang | Mô tả |
|-------|-------|-------|
| `/cart` | Cart | Giỏ hàng |
| `/user/profile` | Profile | Hồ sơ cá nhân |
| `/user/orders` | Orders | Danh sách đơn hàng |
| `/user/order/:order_code` | Order Detail | Chi tiết đơn |
| `/reviews/:order_code` | Review | Đánh giá sản phẩm |
| `/payment/result` | Payment Result | Kết quả thanh toán |

### Trang trợ giúp

| Route | Trang | Mô tả |
|-------|-------|-------|
| `/help/shipping` | Shipping | Chính sách vận chuyển |
| `/help/returns` | Returns | Đổi trả & hoàn tiền |
| `/help/size-guide` | Size Guide | Hướng dẫn chọn size |

### Trang Admin (layout riêng)

| Route | Trang | Mô tả |
|-------|-------|-------|
| `/admin` | Dashboard | Trang quản trị chính |
| `/admin/quan-ly-nguoi-dung` | Users | Quản lý người dùng |
| `/admin/quan-ly-san-pham` | Products | Quản lý sản phẩm |
| `/admin/quan-ly-don-hang` | Orders | Quản lý đơn hàng |
| `/admin/quan-ly-khuyen-mai` | Coupons | Quản lý khuyến mãi |
| `/admin/thong-ke-bao-cao` | Statistics | Thống kê & báo cáo |

---

## 🔄 CI/CD & Deployment

### GitHub Actions Pipeline

Pipeline tự động chạy khi push vào branch `main`:

```yaml
Workflow: Build & Push Docker Image
│
├── Step 1: Checkout code
├── Step 2: Login to Docker Hub  (secrets: DOCKER_USERNAME, DOCKER_PASSWORD)
├── Step 3: Build & Push multi-stage Docker image
└── Step 4: Trigger Render deploy  (secrets: RENDER_DEPLOY_HOOK)
```

### GitHub Secrets cần cấu hình

| Secret | Mô tả |
|--------|--------|
| `DOCKER_USERNAME` | Docker Hub username |
| `DOCKER_PASSWORD` | Docker Hub password / access token |
| `RENDER_DEPLOY_HOOK` | Render deploy webhook URL |

### Hosting trên Render

Website được deploy trên [Render](https://render.com/) với domain:
```
https://my-laravel-app-lb2l.onrender.com
```

---

## 🔒 Bảo mật

### Xác thực & Phân quyền
- **Laravel Sanctum** – Token-based API authentication
- **Admin Middleware** – Kiểm tra role admin cho tất cả route quản trị
- **Rate Limiting** – Giới hạn 5 lần/phút cho login và forgot-password
- **Captcha** – Bảo vệ form đăng nhập khỏi bot
- **Axios Interceptors** – Tự động gắn token và xử lý lỗi 401

### Bảo mật Nginx
- `X-Frame-Options: SAMEORIGIN` – Chống clickjacking
- `X-XSS-Protection: 1; mode=block` – Chống XSS
- `X-Content-Type-Options: nosniff` – Chống MIME sniffing
- `server_tokens off` – Ẩn phiên bản Nginx
- `fastcgi_hide_header X-Powered-By` – Ẩn phiên bản PHP
- Chặn truy cập file `.env`, `composer.json`, `package.json`
- Chặn truy cập trực tiếp vào thư mục hệ thống (`/app`, `/config`, `/vendor`...)

### Bảo mật Frontend
- Token lưu trong `localStorage`, tự động xóa khi nhận response 401
- API requests luôn gửi kèm credentials (`withCredentials: true`)

---

## 🗄️ Database Schema

### Các bảng chính

| Model | Bảng | Mô tả |
|-------|------|--------|
| `User` | `users` | Thông tin người dùng, role, OAuth |
| `Product` | `products` | Sản phẩm (slug, giá, mô tả, SEO) |
| `ProductVariant` | `product_variants` | Biến thể SP (size, màu, tồn kho) |
| `ProductImage` | `product_images` | Hình ảnh SP (nhiều ảnh, ảnh chính) |
| `Category` | `categories` | Danh mục (hỗ trợ cấu trúc cây) |
| `Order` | `orders` | Đơn hàng (mã đơn, trạng thái, tổng tiền) |
| `OrderItem` | `order_items` | Chi tiết đơn hàng |
| `CartSession` | `cart_sessions` | Phiên giỏ hàng |
| `Coupon` | `coupons` | Mã giảm giá |
| `CouponUsage` | `coupon_usages` | Theo dõi sử dụng coupon |
| `Review` | `reviews` | Đánh giá & xếp hạng sản phẩm |
| `ChatbotConversation` | `chatbot_conversations` | Lịch sử hội thoại AI |

---

## 🤝 Đóng góp

Mọi đóng góp đều được hoan nghênh! Hãy làm theo các bước sau:

1. **Fork** repository này
2. Tạo branch mới: `git checkout -b feature/ten-tinh-nang`
3. Commit thay đổi: `git commit -m "Thêm tính năng mới"`
4. Push lên branch: `git push origin feature/ten-tinh-nang`
5. Tạo **Pull Request**

---

## 📄 License

Dự án được phân phối theo giấy phép [MIT](https://opensource.org/licenses/MIT).

---

<p align="center">
  Made with ❤️ by <strong>Florentic Team</strong>
</p>
