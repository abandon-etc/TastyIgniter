#!/usr/bin/env sh
set -eu

log() {
    printf '%s\n' "$*"
}

fail() {
    printf 'ERROR: %s\n' "$*" >&2
    exit 1
}

PORT="${PORT:-10000}"
export PORT

APP_ROOT="/var/www/html"
STORAGE_PATH="${APP_ROOT}/storage"

ensure_directory() {
    mkdir -p "$1"
}

ensure_safe_symlink() {
    link_path="$1"
    target_path="$2"

    ensure_directory "$target_path"

    if [ -L "$link_path" ]; then
        if [ "$(readlink -f "$link_path")" = "$(readlink -f "$target_path")" ]; then
            log "Symlink already configured: ${link_path}"
            return 0
        fi

        log "Replacing existing symlink: ${link_path}"
        rm "$link_path"
    elif [ -e "$link_path" ]; then
        if [ -d "$link_path" ] && [ -z "$(ls -A "$link_path")" ]; then
            log "Replacing empty directory with symlink: ${link_path}"
            rmdir "$link_path"
        else
            fail "${link_path} exists and is not an empty directory or expected symlink. Back it up and migrate it manually before deploying."
        fi
    fi

    ln -s "$target_path" "$link_path"
}

log "Preparing Render runtime directories"
ensure_directory "${STORAGE_PATH}/app"
ensure_directory "${STORAGE_PATH}/app/public"
ensure_directory "${STORAGE_PATH}/app/media"
ensure_directory "${STORAGE_PATH}/framework"
ensure_directory "${STORAGE_PATH}/framework/cache"
ensure_directory "${STORAGE_PATH}/framework/sessions"
ensure_directory "${STORAGE_PATH}/framework/views"
ensure_directory "${STORAGE_PATH}/igniter/combiner/data"
ensure_directory "${STORAGE_PATH}/logs"
ensure_directory "${APP_ROOT}/bootstrap/cache"

ensure_safe_symlink "${APP_ROOT}/public/storage" "${STORAGE_PATH}/app/public"
ensure_safe_symlink "${APP_ROOT}/public/media" "${STORAGE_PATH}/app/media"

chown -R www-data:www-data "${STORAGE_PATH}" "${APP_ROOT}/bootstrap/cache" || true
chmod -R ug+rw "${STORAGE_PATH}" "${APP_ROOT}/bootstrap/cache" || true

log "Rendering Nginx configuration for port ${PORT}"
envsubst '$PORT' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

if [ "${RUN_CONFIG_CACHE:-false}" = "true" ]; then
    log "Discovering Laravel packages"
    php artisan package:discover --ansi

    log "Caching Laravel configuration"
    php artisan config:cache
fi

if [ "${RUN_ROUTE_CACHE:-false}" = "true" ]; then
    log "Caching Laravel routes"
    php artisan route:cache
fi

if [ "${RUN_VIEW_CACHE:-false}" = "true" ]; then
    log "Caching Laravel views"
    php artisan view:cache
fi

log "Starting PHP-FPM"
php-fpm -D

log "Starting Nginx"
exec nginx -g "daemon off;"
