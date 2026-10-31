FROM php:8.2-apache

# Install dependencies untuk ekstensi GD (JPG, PNG, WebP, Freetype)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Izinkan .htaccess di /var/www/html
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set direktori kerja & salin kode
WORKDIR /var/www/html
COPY . /var/www/html/

# Atur kepemilikan folder data dan uploads agar bisa ditulis oleh Apache
RUN chown -R www-data:www-data /var/www/html/data /var/www/html/uploads \
    && chmod -R 775 /var/www/html/data /var/www/html/uploads

# Port yang diexpose (Render otomatis mendeteksi port 80)
EXPOSE 80

CMD ["apache2-foreground"]
