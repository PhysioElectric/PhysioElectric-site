#!/bin/sh
# =============================================================
#  Entrypoint: wait for MySQL, run migrations, ensure the admin
#  user exists, tighten permissions, then start Apache.
# =============================================================
set -e

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
APP_ENV="${APP_ENV:-production}"
SECRETS_DIR="${SECRETS_DIR:-/run/secrets}"

# ---------------------------------------------------------------
# Zero-config secrets. Any value set through the environment (i.e. via
# .env / docker-compose) wins; otherwise fall back to the file the `init`
# service generated into the shared `secrets` volume.
# ---------------------------------------------------------------
read_secret() {
    # $1 = secret file name; prints the value with any trailing newline stripped
    if [ -s "${SECRETS_DIR}/$1" ]; then
        if [ -r "${SECRETS_DIR}/$1" ]; then
            tr -d '\n\r' < "${SECRETS_DIR}/$1"
        else
            echo "[entrypoint] WARNING: ${SECRETS_DIR}/$1 exists but is not readable by $(id -un) (uid $(id -u))." >&2
        fi
    fi
}

GENERATED_ADMIN=0
if [ -z "${DB_PASS:-}" ]; then
    DB_PASS="$(read_secret db_pass)"
    export DB_PASS
fi
if [ -z "${ADMIN_PASSWORD:-}" ]; then
    ADMIN_PASSWORD="$(read_secret admin_pass)"
    export ADMIN_PASSWORD
    [ -n "$ADMIN_PASSWORD" ] && GENERATED_ADMIN=1
fi

if [ -z "${DB_PASS:-}" ]; then
    echo "[entrypoint] ERROR: no DB_PASS and no ${SECRETS_DIR}/db_pass."
    exit 1
fi
if [ -z "${ADMIN_PASSWORD:-}" ]; then
    echo "[entrypoint] ERROR: no ADMIN_PASSWORD and no ${SECRETS_DIR}/admin_pass."
    exit 1
fi

# create_admin.php validates every ADMIN_PASSWORD against the shared
# PasswordPolicy (app/core/PasswordPolicy.php): minimum length — 16 in
# production, 12 in development — plus the offline common/leaked-password
# list, the character-class rule for passwords under 20 characters, and the
# email/name-substring guard. Only the cheapest check (length) is mirrored
# here so a misconfigured .env fails fast with a pointer at the cause; the
# PHP policy remains the single source of truth and refuses anything else
# with the exact reason.
ADMIN_PASSWORD_MIN=16
if [ "$APP_ENV" = "development" ]; then
    ADMIN_PASSWORD_MIN=12
fi
if [ "${#ADMIN_PASSWORD}" -lt "$ADMIN_PASSWORD_MIN" ]; then
    echo "[entrypoint] ERROR: ADMIN_PASSWORD is ${#ADMIN_PASSWORD} character(s); at least $ADMIN_PASSWORD_MIN are required in APP_ENV=${APP_ENV}."
    echo "[entrypoint]        A non-empty value this short can only come from the environment"
    echo "[entrypoint]        (.env or docker-compose), not from the generated secret."
    echo "[entrypoint]        Fix it in .env (ADMIN_PASSWORD=...), or delete that line entirely"
    echo "[entrypoint]        so the generated 32-character secret is used instead."
    echo "[entrypoint]        Note: the password must also not be a known leaked/common"
    echo "[entrypoint]        password and must not embed the admin e-mail or name."
    exit 1
fi

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

# init.sql (schema+seed) runs only on the very first boot of a fresh MySQL
# volume. migrate.php brings older volumes up to date on every boot and is
# idempotent; a failed migration must not be silently ignored.
if ! php /var/www/html/setup/migrate.php; then
    echo "[entrypoint] ERROR: schema migration failed."
    exit 1
fi

# The admin user is (re)checked on every boot. In production a missing or
# default ADMIN_PASSWORD is fatal, so the failure propagates.
if ! php /var/www/html/setup/create_admin.php; then
    echo "[entrypoint] ERROR: create_admin.php failed."
    exit 1
fi

# Uploads ownership/permissions are prepared by the `init` service in
# docker-compose.yml. This container runs as www-data with all capabilities
# dropped, so chown/chmod here would silently fail - do not "fix" it here.
if [ -d /var/www/html/uploads ] && [ ! -w /var/www/html/uploads ]; then
    echo "[entrypoint] WARNING: /var/www/html/uploads is not writable by $(id -un)."
    echo "[entrypoint]          uploads will fail until the init service has run."
fi

# Runtime PHP overrides that must differ per environment. Written to a
# tmpfs path picked up through PHP_INI_SCAN_DIR (the image itself is
# read-only and runs unprivileged).
mkdir -p /tmp/php-ini 2>/dev/null || true
if [ "$APP_ENV" = "development" ]; then
    {
        echo "display_errors=On"
        echo "display_startup_errors=On"
        echo "opcache.validate_timestamps=1"
        echo "opcache.revalidate_freq=2"
    } > /tmp/php-ini/zz-env.ini 2>/dev/null || true
    echo "[entrypoint] development mode: errors displayed, opcache revalidates."
else
    {
        echo "display_errors=Off"
        echo "opcache.validate_timestamps=0"
    } > /tmp/php-ini/zz-env.ini 2>/dev/null || true
fi

if [ "$GENERATED_ADMIN" -eq 1 ]; then
    echo ""
    echo "[entrypoint] ============================================"
    echo "[entrypoint]  Admin panel : ${SITE_BASE_URL:-http://localhost:8080}/admin"
    echo "[entrypoint]  Email       : ${ADMIN_EMAIL:-admin@physioelectric.com}"
    echo "[entrypoint]  Password    : ${ADMIN_PASSWORD}"
    echo "[entrypoint] ============================================"
    echo "[entrypoint] This password was generated once and lives in the"
    echo "[entrypoint] 'secrets' volume. Set ADMIN_PASSWORD in .env to use"
    echo "[entrypoint] your own, or 'docker compose down -v' to regenerate."
    echo "[entrypoint] The admin must rotate this password at first login"
    echo "[entrypoint] (the panel forces it before anything else opens)."
    echo ""
fi

echo "[entrypoint] env=${APP_ENV} starting Apache..."
exec "$@"
