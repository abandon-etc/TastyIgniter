#!/usr/bin/env sh
set -eu

if [ ! -f .env ] && [ -f .env.docker.example ]; then
    cp .env.docker.example .env
fi

mkdir -p \
    bootstrap/cache \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    storage/temp

chmod -R ug+rw bootstrap/cache storage || true

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

if [ -f .env ] && ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

if [ ! -d node_modules ]; then
    npm install
fi

if [ ! -f public/js/app.js ] || [ ! -f public/css/app.css ]; then
    npm run dev
fi

exec "$@"
