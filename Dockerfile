# =============================================================
#  PhysioElectric - PHP 8.3 + Apache 2.4 (hardened)
# =============================================================
FROM php:8.3-apache

# ---- System packages -------------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev \
        zlib1g-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libwebp-dev \
        libonig-dev \
        libxml2-dev \
        libcurl4-openssl-dev \
        unzip \
        openssl \
        curl \
    && rm -rf /var/lib/apt/lists/*

# ---- PHP extensions --------------------------------------------------
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        gd \
        mbstring \
        intl \
        zip \
        curl \
        dom \
        xml \
        opcache

# ---- Apache: routing + headers, minus the info-leaking modules --------
RUN a2enmod rewrite headers expires \
    && ( a2dismod status autoindex info 2>/dev/null || true )

RUN { \
      echo "ServerTokens Prod"; \
      echo "ServerSignature Off"; \
      echo "TraceEnable Off"; \
      echo "FileETag None"; \
      echo "LimitRequestBody 8388608"; \
      echo "LimitRequestFields 60"; \
      echo "LimitRequestFieldSize 4096"; \
      echo "LimitRequestLine 4096"; \
      echo "<Directory /var/www/html>"; \
      echo "    AllowOverride All"; \
      echo "    Options -Indexes -Includes -ExecCGI"; \
      echo "    Require all granted"; \
      echo "</Directory>"; \
    } > /etc/apache2/conf-available/pe-hardening.conf \
    && a2enconf pe-hardening

# OPcache: production defaults
RUN { \
      echo "opcache.enable=1"; \
      echo "opcache.memory_consumption=128"; \
      echo "opcache.max_accelerated_files=20000"; \
      echo "opcache.validate_timestamps=0"; \
      echo "opcache.interned_strings_buffer=16"; \
      echo "opcache.jit_buffer_size=0"; \
    } > /usr/local/etc/php/conf.d/opcache-custom.ini

# PHP runtime hardening
RUN { \
      echo "expose_php=Off"; \
      echo "display_errors=Off"; \
      echo "display_startup_errors=Off"; \
      echo "log_errors=On"; \
      echo "error_reporting=E_ALL"; \
      echo "session.use_strict_mode=1"; \
      echo "session.use_only_cookies=1"; \
      echo "session.cookie_httponly=1"; \
      echo "session.cookie_samesite=Lax"; \
      echo "upload_max_filesize=4M"; \
      echo "post_max_size=8M"; \
      echo "max_file_uploads=5"; \
      echo "max_input_vars=500"; \
      echo "allow_url_fopen=Off"; \
      echo "allow_url_include=Off"; \
      echo "disable_functions=exec,passthru,shell_exec,system,proc_open,proc_close,proc_get_status,proc_terminate,popen,dl,show_source,highlight_file,phpinfo,pcntl_alarm,pcntl_exec,pcntl_fork,pcntl_signal,posix_kill,posix_getpwuid"; \
    } > /usr/local/etc/php/conf.d/pe-hardening.ini

# ---- Application ------------------------------------------------------
COPY app/ /var/www/html/

# uploads: writable by the web user, never executable.
RUN chown -R www-data:www-data /var/www/html/uploads \
    && chmod 0770 /var/www/html/uploads \
    && find /var/www/html/uploads -type f -exec chmod 0640 {} \; \
    && chmod 0755 /var/www/html/entrypoint.sh \
    && chown -R www-data:www-data /var/lock/apache2 /var/run/apache2 /var/log/apache2

RUN sed -ri 's/^[[:space:]]*Listen 80[[:space:]]*$/Listen 8080/' /etc/apache2/ports.conf \
    && sed -ri 's|<VirtualHost \*:80>|<VirtualHost *:8080>|' /etc/apache2/sites-available/000-default.conf \
    && grep -qE '^Listen 8080$' /etc/apache2/ports.conf \
    && grep -qF '<VirtualHost *:8080>' /etc/apache2/sites-available/000-default.conf \
    && apache2ctl configtest
ENV APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data \
    PHP_INI_SCAN_DIR=:/tmp/php-ini
USER www-data

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl -fsS http://127.0.0.1:8080/healthz >/dev/null 2>&1 || exit 1

ENTRYPOINT ["sh", "/var/www/html/entrypoint.sh"]
CMD ["apache2-foreground"]