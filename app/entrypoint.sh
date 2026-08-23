#!/bin/sh
# =============================================================
#  Entrypoint: wait for MySQL, ensure the admin user exists,
#  fix permissions, then start Apache.
# =============================================================
set -e

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"

echo "[entrypoint] Waiting for database at ${DB_HOST}:${DB_PORT} ..."
i=0
until php /var/www/html/setup/db_ready.php; do
    i=$((i+1))
    if [ "$i" -ge 60 ]; then
        echo "[entrypoint] ERROR: database did not become reachable in time."
        exit 1
    fi
    echo "[entrypoint] db not ready yet, retrying ($i/60)..."
    sleep 2
done
echo "[entrypoint] Database is reachable."

# The init.sql (schema+seed) runs only on the very first boot of a
# fresh MySQL volume; the admin user is (re)checked on every boot.
php /var/www/html/setup/create_admin.php || echo "[entrypoint] WARNING: create_admin.php failed (DB still initializing?)"

# Make sure the upload dir is writable and non-executable for PHP.
chown -R www-data:www-data /var/www/html/uploads 2>/dev/null || true
chmod 775 /var/www/html/uploads 2>/dev/null || true

echo "[entrypoint] Starting Apache..."
exec "$@"
