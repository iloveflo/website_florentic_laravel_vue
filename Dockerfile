# ============================================
# STAGE 1: Build VueJS (Chỉ dùng để build, không chạy)
# ============================================
FROM node:20-alpine as vue_builder

# Tạo thư mục làm việc tạm
WORKDIR /app/frontend

# 1. Copy file định nghĩa thư viện trước (để tận dụng Docker Cache)
COPY vue_frontend/package*.json ./

# 2. Cài thư viện Node
RUN npm install

# 3. Copy toàn bộ code Vue vào
COPY vue_frontend/ .

# 4. Build ra file tĩnh (kết quả nằm ở /app/frontend/dist)
RUN npm run build


# ============================================
# STAGE 2: Build Laravel (Container chính sẽ chạy)
# ============================================
FROM php:8.2-apache

WORKDIR /var/www/html

# 1. Cài đặt các thư viện hệ thống cần thiết cho Laravel & MySQL
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    # 1. Cài thư viện hệ thống bắt buộc cho việc vẽ chữ và xử lý ảnh
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    # 2. CẤU HÌNH GD ĐỂ KÍCH HOẠT FREETYPE (Đây là dòng quan trọng nhất bạn đang thiếu)
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    # 3. Cài đặt extension
    && docker-php-ext-install -j$(nproc) gd pdo_mysql mbstring exif pcntl bcmath

# 2. Bật Mod Rewrite của Apache để Laravel chạy URL đẹp
RUN a2enmod rewrite

# 3. Cấu hình Apache trỏ thẳng vào thư mục public của Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# 4. Copy Code Laravel từ máy thật vào Container
COPY laravel_api/ .

# 5. Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# 6. --- QUAN TRỌNG NHẤT ---
# Lấy kết quả build từ STAGE 1 (folder frontend) chép đè vào folder public của Laravel
# Copy tất cả file trong frontend (index.html, assets/...) vào public/
COPY --from=vue_builder /app/frontend/frontend ./public

# 7. Phân quyền cho Laravel ghi log và cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80