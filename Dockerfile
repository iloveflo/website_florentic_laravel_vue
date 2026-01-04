# ============================================
# STAGE 1: Build VueJS (Giữ nguyên không đổi)
# ============================================
FROM node:20-alpine AS vue_builder
WORKDIR /app/frontend
COPY vue_frontend/package*.json ./
RUN npm install
COPY vue_frontend/ .
RUN npm run build

# ============================================
# STAGE 2: Build Laravel với Nginx + PHP-FPM
# ============================================
# 1. ĐỔI IMAGE GỐC: Dùng FPM thay vì Apache
FROM php:8.2-fpm

WORKDIR /var/www/html

# 2. Cài đặt thư viện hệ thống + Nginx + Supervisor
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    # --- CÀI THÊM NGINX VÀ SUPERVISOR ---
    nginx \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql mbstring exif pcntl bcmath \
    # Dọn dẹp cache apt để giảm dung lượng image
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Cấu hình PHP-FPM & Nginx
# Copy file cấu hình Nginx từ máy bạn vào vị trí mặc định của Nginx
COPY docker-conf/nginx.conf /etc/nginx/sites-available/default
# Copy file cấu hình Supervisor
COPY docker-conf/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 4. Copy Code Laravel
COPY laravel_api/ .

# 5. Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# 6. Copy Code Vue đã build từ Stage 1
COPY --from=vue_builder /app/frontend/frontend ./public

# 7. Phân quyền (Quan trọng: Nginx thường chạy user www-data giống Apache)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/uploads

# 8. Mở port 80
EXPOSE 80

# 9. LỆNH KHỞI CHẠY MỚI
# Thay vì apache2-foreground, ta chạy supervisor để nó quản lý cả Nginx và PHP
CMD ["/usr/bin/supervisord"]