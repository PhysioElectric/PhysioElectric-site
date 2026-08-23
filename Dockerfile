# =============================================================
#  PhysioElectric - PHP 8.3 + Apache 2.4
# =============================================================
FROM php:8.3-apache

# ---- System packages -------------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev \
        zlib1g-dev \
        libzip-dev \
        unzip \
        openssl \
        curl \
    && rm -rf /var/lib/apt/lists/*

# ---- PHP extensions (pdo_mysql for DB, gd for image handling) --------
RUN docker-php-ext-configure pdo_mysql --with-pdo-mysql \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        gd \
        mbstring \
        intl \
        zip \
        dom \
        xml \
    && docker-php-ext-install opcache

# ---- Apache: mod_rewrite for SEO URLs + headers + static cache -------
RUN a2enmod rewrite headers expires

# OPcache tuning
RUN { \
      echo "opcache.enable=1"; \
      echo "opcache.memory_consumption=128"; \
      echo "opcache.max_accelerated_files=20000"; \
      echo "opcache.validate_timestamps=1"; \
      echo "opcache.revalidate_freq=60"; \
      echo "opcache.interned_strings_buffer=16"; \
    } > /usr/local/etc/php/conf.d/opcache-custom.ini

# PHP runtime hardening
RUN { \
      echo "expose_php=Off"; \
      echo "display_errors=Off"; \
      echo "log_errors=On"; \
      echo "session.cookie_httponly=1"; \
      echo "session.use_strict_mode=1"; \
      echo "upload_max_filesize=4M"; \
      echo "post_max_size=8M"; \
    } > /usr/local/etc/php/conf.d/pe-hardening.ini

# ---- Application ------------------------------------------------------
COPY app/ /var/www/html/

# uploads must be writable and non-executable by PHP
RUN chown -R www-data:www-data /var/www/html/uploads \
    && chmod 775 /var/www/html/uploads \
    && chmod +x /var/www/html/entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=15s \
    CMD curl -fsS http://127.0.0.1/ >/dev/null 2>&1 || exit 1

ENTRYPOINT ["/var/www/html/entrypoint.sh"]
CMD ["apache2-foreground"]
