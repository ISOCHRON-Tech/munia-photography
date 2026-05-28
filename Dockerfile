FROM php:8.3-apache

# ── System deps ────────────────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        git curl zip unzip libpng-dev libonig-dev libxml2-dev \
        libzip-dev libexif-dev libjpeg-dev libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install \
        pdo_mysql mbstring bcmath zip gd exif opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ── Composer ───────────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── Apache: point document root at /var/www/html/public ───────────────────────
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
        /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' \
        /etc/apache2/apache2.conf \
    && a2enmod rewrite

# ── OPcache tuning ─────────────────────────────────────────────────────────────
RUN echo "opcache.enable=1\nopcache.memory_consumption=256\nopcache.max_accelerated_files=20000\nopcache.validate_timestamps=0" \
    > /usr/local/etc/php/conf.d/opcache.ini

# ── App files ─────────────────────────────────────────────────────────────────
WORKDIR /var/www/html
COPY . .

# ── PHP deps (prod only) ───────────────────────────────────────────────────────
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ── Permissions ────────────────────────────────────────────────────────────────
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache

# ── Entrypoint ─────────────────────────────────────────────────────────────────
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
