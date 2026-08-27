#!/bin/bash

################################################################################
# OneClick Laravel 13 - Production Deployment Script
#
# Usage: ./deploy.sh [environment]
# Example: ./deploy.sh production
#
# Features:
# - Pulls latest code from git
# - Installs/updates dependencies
# - Runs database migrations
# - Clears and optimizes caches
# - Restarts queue workers
# - Verifies health check
################################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
ENVIRONMENT=${1:-production}
APP_PATH="/var/www/oneclick"
LOG_FILE="/var/log/oneclick/deploy.log"
BACKUP_DIR="/var/backups/oneclick"
DOCKER_COMPOSE="docker-compose -f docker-compose.yml"

# Logging function
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

success() {
    echo -e "${GREEN}[✓]${NC} $1" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   error "This script must be run as root"
fi

# Create log directory if it doesn't exist
mkdir -p "$(dirname "$LOG_FILE")"
mkdir -p "$BACKUP_DIR"

log "=========================================="
log "🚀 Starting OneClick Laravel Deployment"
log "Environment: $ENVIRONMENT"
log "=========================================="

# Step 1: Backup current state
log "\n📦 Creating backup..."
if [ -d "$APP_PATH" ]; then
    BACKUP_TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    BACKUP_FILE="$BACKUP_DIR/backup_$BACKUP_TIMESTAMP.tar.gz"
    tar -czf "$BACKUP_FILE" -C "$(dirname $APP_PATH)" "$(basename $APP_PATH)" 2>/dev/null || true
    success "Backup created: $BACKUP_FILE"
else
    warning "App directory not found, skipping backup"
fi

# Step 2: Change to app directory
log "\n📂 Changing to app directory..."
cd "$APP_PATH" || error "Failed to change to $APP_PATH"
success "Changed to $APP_PATH"

# Step 3: Git pull latest code
log "\n📥 Pulling latest code from git..."
git pull origin main || error "Failed to pull from git"
success "Latest code pulled"

# Step 4: Update environment file
log "\n⚙️  Updating environment configuration..."
if [ ! -f ".env.production" ]; then
    warning ".env.production not found, using .env"
    ENV_FILE=".env"
else
    ENV_FILE=".env.production"
fi
success "Using $ENV_FILE"

# Step 5: Install PHP dependencies
log "\n📦 Installing PHP dependencies..."
composer install --no-interaction --no-dev --optimize-autoloader || error "Composer install failed"
success "PHP dependencies installed"

# Step 6: Install Node dependencies and build assets
log "\n📦 Installing and building Node.js assets..."
npm install --ignore-scripts 2>/dev/null || true
npm run build || error "npm build failed"
success "Assets built successfully"

# Step 7: Database backup
log "\n💾 Backing up database..."
if command -v pg_dump &> /dev/null; then
    DB_BACKUP="$BACKUP_DIR/db_backup_$BACKUP_TIMESTAMP.sql"
    pg_dump "$DB_DATABASE" -U "$DB_USERNAME" -h "$DB_HOST" > "$DB_BACKUP" 2>/dev/null || warning "Database backup failed"
    success "Database backed up to $DB_BACKUP"
fi

# Step 8: Run database migrations
log "\n🔄 Running database migrations..."
$DOCKER_COMPOSE exec -T app php artisan migrate --force || error "Database migration failed"
success "Migrations completed"

# Step 9: Clear and optimize caches
log "\n⚡ Clearing and optimizing caches..."
$DOCKER_COMPOSE exec -T app php artisan cache:clear || true
$DOCKER_COMPOSE exec -T app php artisan config:cache || true
$DOCKER_COMPOSE exec -T app php artisan route:cache || true
$DOCKER_COMPOSE exec -T app php artisan view:cache || true
success "Caches optimized"

# Step 10: Restart services
log "\n🔄 Restarting services..."
$DOCKER_COMPOSE restart app || warning "Failed to restart app service"
$DOCKER_COMPOSE restart queue || warning "Failed to restart queue service"
$DOCKER_COMPOSE restart scheduler || warning "Failed to restart scheduler service"
success "Services restarted"

# Step 11: Wait for services to be ready
log "\n⏳ Waiting for services to be ready..."
sleep 5

# Step 12: Verify health check
log "\n🏥 Running health check..."
HEALTH_CHECK_URL="http://localhost/health"
MAX_ATTEMPTS=10
ATTEMPT=0

while [ $ATTEMPT -lt $MAX_ATTEMPTS ]; do
    HEALTH_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$HEALTH_CHECK_URL" 2>/dev/null || echo "000")

    if [ "$HEALTH_STATUS" = "200" ]; then
        success "Health check passed (HTTP $HEALTH_STATUS)"
        break
    fi

    warning "Health check attempt $((ATTEMPT + 1))/$MAX_ATTEMPTS - HTTP $HEALTH_STATUS"
    ATTEMPT=$((ATTEMPT + 1))
    sleep 2
done

if [ $ATTEMPT -eq $MAX_ATTEMPTS ]; then
    error "Health check failed after $MAX_ATTEMPTS attempts"
fi

# Step 13: Verify critical paths
log "\n✅ Verifying critical user paths..."

# Test login page
LOGIN_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/login" 2>/dev/null || echo "000")
[ "$LOGIN_STATUS" = "200" ] && success "Login page accessible" || warning "Login page returned HTTP $LOGIN_STATUS"

# Test dashboard
DASHBOARD_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/dashboard" 2>/dev/null || echo "000")
[ "$DASHBOARD_STATUS" = "200" ] || [ "$DASHBOARD_STATUS" = "302" ] && success "Dashboard accessible" || warning "Dashboard returned HTTP $DASHBOARD_STATUS"

# Step 14: Log deployment summary
log "\n=========================================="
log "✨ Deployment Complete!"
log "=========================================="
log "Environment: $ENVIRONMENT"
log "Timestamp: $(date)"
log "Backup: $BACKUP_FILE"
log "Log: $LOG_FILE"
log "Health Check: PASSED"
log "=========================================="

# Optional: Send notification
if command -v curl &> /dev/null && [ -n "$SLACK_WEBHOOK" ]; then
    log "\n📢 Sending Slack notification..."
    curl -X POST "$SLACK_WEBHOOK" \
        -H 'Content-Type: application/json' \
        -d "{
            \"text\": \"✅ OneClick Laravel Deployment Successful\",
            \"blocks\": [
                {
                    \"type\": \"section\",
                    \"text\": {
                        \"type\": \"mrkdwn\",
                        \"text\": \"*OneClick Laravel - Deployment Complete*\n*Environment:* $ENVIRONMENT\n*Status:* ✅ Success\n*Time:* $(date)\"
                    }
                }
            ]
        }" 2>/dev/null || warning "Failed to send Slack notification"
fi

log "\n✅ Deployment script finished successfully!"

exit 0
