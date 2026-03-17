#!/bin/bash
set -e

# Deployment script for ePages Webhooks
# Run from the project directory: ./deploy/deploy.sh

APP_DIR="/var/www/epages-webhooks"
BRANCH="main"

echo "=== Deploying ePages Webhooks ==="

cd $APP_DIR

# Maintenance mode
echo "[1/8] Entering maintenance mode..."
php artisan down --retry=60

# Pull latest code
echo "[2/8] Pulling latest code..."
git fetch origin $BRANCH
git reset --hard origin/$BRANCH

# Install dependencies
echo "[3/8] Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Run migrations
echo "[4/8] Running migrations..."
php artisan migrate --force

# Clear and cache
echo "[5/8] Optimizing..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build assets
echo "[6/8] Building assets..."
npm ci
npm run build

# Set permissions
echo "[7/8] Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Restart services
echo "[8/8] Restarting services..."
sudo supervisorctl restart epages-webhooks-worker:*

# Exit maintenance mode
php artisan up

echo "=== Deployment complete! ==="
