#!/usr/bin/env sh
set -u

APP_DIR="/var/www/html"
cd "$APP_DIR"

echo "[entrypoint] Boot started."

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY is not set. Configure APP_KEY in Railway variables."
    exit 1
fi

STORAGE_PATH="${LARAVEL_STORAGE_PATH:-$APP_DIR/storage}"
RUN_MIGRATIONS_ON_BOOT="${RUN_MIGRATIONS_ON_BOOT:-false}"

mkdir -p "$STORAGE_PATH/framework/cache"
mkdir -p "$STORAGE_PATH/framework/sessions"
mkdir -p "$STORAGE_PATH/framework/views"
mkdir -p "$STORAGE_PATH/logs"
mkdir -p "$STORAGE_PATH/app/public"
mkdir -p "$APP_DIR/bootstrap/cache"

chown -R www-data:www-data "$STORAGE_PATH" "$APP_DIR/bootstrap/cache" || true
chmod -R ug+rwx "$STORAGE_PATH" "$APP_DIR/bootstrap/cache" || true

php artisan optimize:clear || true
php artisan storage:link || true

if [ "$RUN_MIGRATIONS_ON_BOOT" = "true" ]; then
    if [ "${DB_CONNECTION:-}" = "mysql" ]; then
        missing_mysql_var=false
        unresolved_ref_var=false
        for var_name in DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD; do
            var_value="$(printenv "$var_name" || true)"

            if [ -z "$var_value" ]; then
                echo "$var_name is not set for DB_CONNECTION=mysql"
                missing_mysql_var=true
            fi

            case "$var_value" in
                *'${{'*)
                    echo "$var_name still contains an unresolved Railway reference: $var_value"
                    unresolved_ref_var=true
                    ;;
            esac
        done

        if [ "$missing_mysql_var" = "true" ]; then
            echo "MySQL variables are incomplete. Set DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD and redeploy."
            exit 1
        fi

        if [ "$unresolved_ref_var" = "true" ]; then
            echo "Database references were not resolved. Re-add DB vars using Railway Variable References UI."
            exit 1
        fi
    fi

    attempt=1
    max_attempts="${MIGRATE_MAX_ATTEMPTS:-60}"
    retry_sleep="${MIGRATE_RETRY_SLEEP_SECONDS:-3}"

    while true; do
        if php artisan migrate --force; then
            break
        fi

        if [ "$attempt" -ge "$max_attempts" ]; then
            echo "Database migration failed after ${attempt} attempts."
            exit 1
        fi

        echo "Migration attempt ${attempt} failed. Retrying in ${retry_sleep}s..."
        attempt=$((attempt + 1))
        sleep "$retry_sleep"
    done
else
    echo "RUN_MIGRATIONS_ON_BOOT=false, skipping migrations (recommended for Railway startup stability)."
fi

if [ "${RUN_DB_SEED:-false}" = "true" ]; then
    php artisan db:seed --force || echo "[entrypoint] db:seed failed, continuing startup."
fi

php artisan config:cache || echo "[entrypoint] config:cache failed, continuing startup."
if [ "${ENABLE_ROUTE_CACHE:-false}" = "true" ]; then
    if ! php artisan route:cache; then
        echo "route:cache failed. Likely because of closure routes (e.g. /up). Falling back to route:clear."
        php artisan route:clear || true
    fi
else
    php artisan route:clear || true
fi
php artisan view:cache || echo "[entrypoint] view:cache failed, continuing startup."

echo "[entrypoint] Starting web server on port ${PORT:-8080}."
exec php -d variables_order=EGPCS artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
