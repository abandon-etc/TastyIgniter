#!/usr/bin/env sh
set -eu

log() {
    printf '%s\n' "$*"
}

fail() {
    printf 'ERROR: %s\n' "$*" >&2
    exit 1
}

PORT="${PORT:-8080}"
export PORT

if [ -z "${DB_SOCKET:-}" ] && [ -n "${DB_INSTANCE_CONNECTION_NAME:-}" ]; then
    log "Using Cloud SQL Unix socket from DB_INSTANCE_CONNECTION_NAME"
    DB_SOCKET="/cloudsql/${DB_INSTANCE_CONNECTION_NAME}"
    export DB_SOCKET
fi

if [ -z "${DB_HOST:-}" ]; then
    DB_HOST="127.0.0.1"
    export DB_HOST
fi

if [ -n "${CLOUD_RUN_SERVICE_URL:-}" ]; then
    case "${APP_URL:-}" in
        ""|"http://localhost"|"https://localhost"|"http://127.0.0.1"*|"https://127.0.0.1"*)
            log "Using CLOUD_RUN_SERVICE_URL for APP_URL"
            APP_URL="${CLOUD_RUN_SERVICE_URL}"
            export APP_URL
            ;;
    esac

    case "${ASSET_URL:-}" in
        ""|"http://localhost"|"https://localhost"|"http://127.0.0.1"*|"https://127.0.0.1"*)
            log "Using APP_URL for ASSET_URL"
            ASSET_URL="${APP_URL}"
            export ASSET_URL
            ;;
    esac
fi

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

log "Preparing Cloud Run runtime directories"
ensure_directory "${STORAGE_PATH}/app"
ensure_directory "${STORAGE_PATH}/app/public"
ensure_directory "${STORAGE_PATH}/app/media"
ensure_directory "${STORAGE_PATH}/app/media/media/uploads"
ensure_directory "${STORAGE_PATH}/app/media/media/attachments"
ensure_directory "${STORAGE_PATH}/framework"
ensure_directory "${STORAGE_PATH}/framework/cache"
ensure_directory "${STORAGE_PATH}/framework/cache/data"
ensure_directory "${STORAGE_PATH}/framework/sessions"
ensure_directory "${STORAGE_PATH}/framework/views"
ensure_directory "${STORAGE_PATH}/igniter/combiner/data"
ensure_directory "${STORAGE_PATH}/logs"
ensure_directory "${APP_ROOT}/bootstrap/cache"

ensure_safe_symlink "${STORAGE_PATH}/app/public/media" "${STORAGE_PATH}/app/media/media"
ensure_safe_symlink "${APP_ROOT}/public/storage" "${STORAGE_PATH}/app/public"
ensure_safe_symlink "${APP_ROOT}/public/media" "${STORAGE_PATH}/app/media"

# Do not recursively chown the Cloud Storage mount path. Cloud Run grants media
# access through IAM on the mounted bucket, and recursive metadata operations can
# be slow or unsupported on Cloud Storage FUSE.
chown -R www-data:www-data \
    "${STORAGE_PATH}/app/public" \
    "${STORAGE_PATH}/framework" \
    "${STORAGE_PATH}/igniter" \
    "${STORAGE_PATH}/logs" \
    "${APP_ROOT}/bootstrap/cache" || true
chmod -R ug+rw \
    "${STORAGE_PATH}/app/public" \
    "${STORAGE_PATH}/framework" \
    "${STORAGE_PATH}/igniter" \
    "${STORAGE_PATH}/logs" \
    "${APP_ROOT}/bootstrap/cache" || true

# TastyIgniter's Carte-key flow persists the key through
# SystemHelper::replaceInEnv(), which reads and rewrites "${APP_ROOT}/.env" and
# throws FileNotFoundException when that file does not exist. .env is excluded
# from the image by .dockerignore, correctly, because configuration arrives here
# as environment variables. Creating it empty makes that rewrite a harmless
# no-op - the regex matches no line and the empty content is written back - so
# the flow continues to its real persistence step, which stores the key in the
# shared settings table.
#
# Only this one file is handed to www-data. Overwriting an existing file needs
# write permission on the file, not on its directory, so the application root
# stays root-owned and composer.json, auth.json and lang/ remain unwritable at
# runtime. That is deliberate: language packs must be committed and baked into
# the image, never written at runtime, because the container filesystem is
# per-instance and does not survive the instance.
#
# The file is left empty on purpose. An IGNITER_CARTE_KEY= line here would put
# the key in a per-instance file that disappears with the instance, for no gain.
if [ ! -f "${APP_ROOT}/.env" ]; then
    log "Creating empty ${APP_ROOT}/.env so the Carte-key rewrite does not fail"
    : > "${APP_ROOT}/.env"
fi
chown www-data:www-data "${APP_ROOT}/.env" || true
chmod 600 "${APP_ROOT}/.env" || true

log "Rendering Nginx configuration for port ${PORT}"
envsubst '$PORT' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

RUN_CONFIG_CACHE="${RUN_CONFIG_CACHE:-true}"
RUN_ROUTE_CACHE="${RUN_ROUTE_CACHE:-false}"
RUN_VIEW_CACHE="${RUN_VIEW_CACHE:-false}"
export RUN_CONFIG_CACHE RUN_ROUTE_CACHE RUN_VIEW_CACHE

if [ "${RUN_CONFIG_CACHE}" = "true" ]; then
    log "Discovering Laravel packages"
    php artisan package:discover --ansi

    log "Caching Laravel configuration"
    php artisan config:cache
else
    log "Skipping Laravel configuration cache"
fi

if [ "${RUN_ROUTE_CACHE}" = "true" ]; then
    log "Caching Laravel routes"
    php artisan route:cache
fi

if [ "${RUN_VIEW_CACHE}" = "true" ]; then
    log "Caching Laravel views"
    php artisan view:cache
fi

log "Starting PHP-FPM"
php-fpm -D

log "Starting Nginx"
exec nginx -g "daemon off;"
