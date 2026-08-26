#!/bin/bash

set -e

echo "======================================"
echo " EAZYWEAR PRODUCTION DEPLOYMENT"
echo "======================================"

echo ""
echo "[1/4] Updating source code..."
git pull origin main

echo ""
echo "[2/4] Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-scripts

echo ""
echo "[3/4] Syncing public assets..."

PUBLIC_DIR="$(pwd)/public"
PUBLIC_HTML="$(cd ../../public_html && pwd)"

rsync -a "$PUBLIC_DIR/images/" "$PUBLIC_HTML/images/"
rsync -a "$PUBLIC_DIR/icons/" "$PUBLIC_HTML/icons/"
rsync -a "$PUBLIC_DIR/fonts/" "$PUBLIC_HTML/fonts/"
rsync -a "$PUBLIC_DIR/build/" "$PUBLIC_HTML/build/"

echo "Public assets synced successfully."

echo ""
echo "[4/4] Clearing Laravel cache..."
php artisan optimize:clear

echo ""
echo "======================================"
echo " DEPLOYMENT SUCCESSFUL"
echo "======================================"