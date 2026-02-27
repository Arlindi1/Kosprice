#!/usr/bin/env sh
set -eu

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY is required."
    exit 1
fi

PORT="${PORT:-10000}"
sed -i "s/__PORT__/${PORT}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

php artisan migrate --force

if [ "${APP_DEMO_SEED:-true}" = "true" ]; then
    php artisan db:seed --force
fi

php artisan optimize

exec apache2-foreground
