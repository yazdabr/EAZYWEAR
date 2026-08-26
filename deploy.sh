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

if [ -d "$PUBLIC_DIR/images" ]; then
    mkdir -p "$PUBLIC_HTML/images"
    rsync -a "$PUBLIC_DIR/images/" "$PUBLIC_HTML/images/"
    echo "Images synced successfully."
fi

if [ -d "$PUBLIC_DIR/build" ]; then
    mkdir -p "$PUBLIC_HTML/build"
    rsync -a "$PUBLIC_DIR/build/" "$PUBLIC_HTML/build/"
    echo "Build assets synced successfully."
fi

echo ""
echo "[4/4] Clearing Laravel cache..."
php artisan optimize:clear

echo ""
echo "======================================"
echo " DEPLOYMENT SUCCESSFUL"
echo "======================================"