#!/bin/sh
set -e

# Wait for the database to be ready
if [ "$DATABASE_URL" != "" ]; then
    echo "Waiting for database to be ready..."
    # Extract host and port from DATABASE_URL if possible, or just try to connect
    # For simplicity, we can use bin/console doctrine:query:sql "SELECT 1" to check connection
    ATTEMPTS_LEFT_RETRIES=20
    until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1 || [ $ATTEMPTS_LEFT_RETRIES -eq 0 ]; do
        echo "Waiting for database... $((ATTEMPTS_LEFT_RETRIES--)) attempts left"
        sleep 1
    done
fi

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
    echo "Running migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
fi

exec docker-php-entrypoint "$@"
