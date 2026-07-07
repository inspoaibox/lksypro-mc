#!/usr/bin/env sh
set -eu

cd /var/www/html

mkdir -p \
    storage/app/uploads \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/runtime \
    bootstrap/cache

if [ ! -e storage/runtime/.env ]; then
    cp .env.example storage/runtime/.env
fi

if [ ! -L .env ]; then
    rm -f .env
    ln -s storage/runtime/.env .env
fi

if [ ! -L installed.lock ]; then
    rm -f installed.lock
    ln -s storage/runtime/installed.lock installed.lock
fi

set_env_file_value() {
    key="$1"
    value="${2:-}"
    escaped_value="$(printf '%s' "$value" | sed 's/[\/&]/\\&/g')"

    if grep -q "^${key}=" storage/runtime/.env; then
        sed -i "s/^${key}=.*/${key}=${escaped_value}/" storage/runtime/.env
    else
        printf '\n%s=%s\n' "$key" "$value" >> storage/runtime/.env
    fi
}

set_env_if_present() {
    key="$1"
    eval "value=\${$key:-}"

    if [ -n "$value" ]; then
        set_env_file_value "$key" "$value"
    fi
}

for key in APP_ENV APP_DEBUG APP_URL DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD; do
    set_env_if_present "$key"
done

chown -R www-data:www-data storage bootstrap/cache

if ! grep -Eq '^APP_KEY=.+$' storage/runtime/.env; then
    php artisan key:generate --force --ansi >/dev/null 2>&1 || true
fi

php artisan package:discover --ansi >/dev/null 2>&1 || true

exec "$@"
