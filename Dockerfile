FROM php:8.2-apache

# 1. Instal dependensi untuk ekstensi PHP GD (JPEG, PNG, WebP, FreeType)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# 2. Aktifkan AllowOverride All agar konfigurasi .htaccess berfungsi
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# 3. Konfigurasi baseline port non-privileged (3000) dan interface 0.0.0.0
# Ini mencegah Apache mencoba membuka privileged port 80 pada lingkungan non-root
RUN echo "Listen 0.0.0.0:3000" > /etc/apache2/ports.conf \
    && sed -i -E 's/<VirtualHost [^>]+>/<VirtualHost *:3000>/' /etc/apache2/sites-available/000-default.conf

# 4. Siapkan direktori runtime, lock, dan log dengan hak akses yang dapat ditulis
RUN mkdir -p /var/run/apache2 /var/lock/apache2 /var/log/apache2 \
    && chown -R www-data:www-data /var/run/apache2 /var/lock/apache2 /var/log/apache2 \
    && chmod -R 777 /var/run/apache2 /var/lock/apache2 /var/log/apache2

# 5. Set direktori kerja & salin kode aplikasi
WORKDIR /var/www/html
COPY . /var/www/html/

# 6. Salin script entrypoint untuk menangani environment variable $PORT secara dinamis
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# 7. Berikan hak akses tulis minimum yang diperlukan untuk folder data dan uploads
RUN chown -R www-data:www-data /var/www/html/data /var/www/html/uploads \
    && chmod -R 775 /var/www/html/data /var/www/html/uploads

# 8. Expose port standar non-privileged web Deplexo
EXPOSE 3000

# 9. Jalankan melalui entrypoint dinamis dengan foreground Apache
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
