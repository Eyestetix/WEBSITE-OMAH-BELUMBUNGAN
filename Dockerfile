FROM php:8.2-apache

# 1. Konfigurasi port default
ENV PORT=3000

# 2. Instal dependensi untuk ekstensi PHP GD (JPEG, PNG, WebP, FreeType)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# 3. Aktifkan AllowOverride All agar file .htaccess berfungsi
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# 4. Konfigurasi build-time Apache untuk port non-privileged 3000 pada interface 0.0.0.0
# Dibangkitkan secara deterministik pada build time sesuai kontrak Deplexo (deplexo.yaml run.port: 3000)
# Tidak memerlukan penulisan file saat runtime
RUN echo "Listen 0.0.0.0:3000" > /etc/apache2/ports.conf \
    && sed -i -E 's/<VirtualHost [^>]+>/<VirtualHost *:3000>/' /etc/apache2/sites-available/000-default.conf

# 5. Adaptasi runtime Apache untuk mendukung Read-Only Root Filesystem:
# - Arahkan PID file, lock dir, dan run dir ke /tmp (yang dijamin writable tmpfs oleh Deplexo)
# - Arahkan ErrorLog dan CustomLog langsung ke file descriptor stdout/stderr (/proc/self/fd/1 & 2)
RUN sed -i 's|export APACHE_PID_FILE=.*|export APACHE_PID_FILE=/tmp/apache2.pid|' /etc/apache2/envvars \
    && sed -i 's|export APACHE_RUN_DIR=.*|export APACHE_RUN_DIR=/tmp|' /etc/apache2/envvars \
    && sed -i 's|export APACHE_LOCK_DIR=.*|export APACHE_LOCK_DIR=/tmp|' /etc/apache2/envvars \
    && sed -i 's|export APACHE_LOG_DIR=.*|export APACHE_LOG_DIR=/tmp|' /etc/apache2/envvars \
    && sed -i 's|ErrorLog ${APACHE_LOG_DIR}/error.log|ErrorLog /proc/self/fd/2|' /etc/apache2/apache2.conf \
    && sed -i 's|ErrorLog ${APACHE_LOG_DIR}/error.log|ErrorLog /proc/self/fd/2|' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|CustomLog ${APACHE_LOG_DIR}/access.log combined|CustomLog /proc/self/fd/1 combined|' /etc/apache2/sites-available/000-default.conf

# 6. Set direktori kerja & salin kode aplikasi
WORKDIR /var/www/html
COPY . /var/www/html/

# 7. Salin entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# 8. Berikan hak akses tulis minimum untuk folder data dan uploads
RUN chown -R www-data:www-data /var/www/html/data /var/www/html/uploads \
    && chmod -R 775 /var/www/html/data /var/www/html/uploads

# 9. Expose port kontrak Deplexo
EXPOSE 3000

# 10. Jalankan entrypoint tanpa mutasi disk runtime
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
