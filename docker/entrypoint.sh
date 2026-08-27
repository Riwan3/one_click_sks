#!/bin/sh

# Production Entrypoint Script for OneClick Laravel Container

set -e

echo "🚀 Starting OneClick Laravel Container..."

# Wait for PostgreSQL to be ready
echo "⏳ Waiting for PostgreSQL..."
until PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -c "\q"; do
  sleep 1
done
echo "✅ PostgreSQL is ready"

# Wait for Redis to be ready
echo "⏳ Waiting for Redis..."
until redis-cli -h "$REDIS_HOST" -p "$REDIS_PORT" ping > /dev/null 2>&1; do
  sleep 1
done
echo "✅ Redis is ready"

# Run migrations (only on app container, not queue/scheduler)
if [ "$APP_ENV" = "production" ]; then
  if ! [ -f /app/storage/.migrated ]; then
    echo "🔄 Running database migrations..."
    php /app/artisan migrate --force
    touch /app/storage/.migrated
    echo "✅ Migrations completed"
  fi

  # Clear and cache config/routes/views for performance
  echo "⚡ Optimizing for production..."
  php /app/artisan config:cache
  php /app/artisan route:cache
  php /app/artisan view:cache
  php /app/artisan cache:clear
  echo "✅ Production optimization complete"
fi

echo "🎯 Container ready to serve requests"

# Execute the command passed to the container
exec "$@"
