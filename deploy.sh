#!/bin/bash

set -Eeuo pipefail

echo "======================================"
echo " EAZYWEAR PRODUCTION DEPLOYMENT"
echo "======================================"

CURRENT_BRANCH="$(git branch --show-current)"
if [[ "$CURRENT_BRANCH" != "main" ]]; then
    echo "ERROR: Deployment must run from branch 'main'. Current branch: '${CURRENT_BRANCH:-detached HEAD}'." >&2
    exit 1
fi

if [[ -n "$(git status --porcelain)" ]]; then
    echo "ERROR: Working tree is not clean. Review changes before deploying." >&2
    git status --short
    exit 1
fi

validate_manifest_assets() {
    local build_dir="$1"
    local manifest="$build_dir/manifest.json"

    if [[ ! -f "$manifest" ]]; then
        echo "ERROR: Missing Vite manifest: $manifest" >&2
        return 1
    fi

    php -r '
        $manifestPath = $argv[1];
        $baseDir = dirname($manifestPath);
        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (!is_array($manifest)) {
            fwrite(STDERR, "ERROR: Invalid JSON in Vite manifest: {$manifestPath}\n");
            exit(1);
        }
        $missing = [];
        foreach ($manifest as $entry) {
            $files = [];
            if (isset($entry["file"]) && is_string($entry["file"])) {
                $files[] = $entry["file"];
            }
            foreach (($entry["css"] ?? []) as $file) {
                if (is_string($file)) $files[] = $file;
            }
            foreach (($entry["assets"] ?? []) as $file) {
                if (is_string($file)) $files[] = $file;
            }
            foreach ($files as $file) {
                if ($file === "" || str_starts_with($file, "/") || str_contains($file, "..")) {
                    $missing[] = "unsafe manifest path: {$file}";
                    continue;
                }
                if (!is_file($baseDir . DIRECTORY_SEPARATOR . $file)) {
                    $missing[] = $file;
                }
            }
        }
        if ($missing) {
            fwrite(STDERR, "ERROR: Manifest references missing/unsafe assets in {$baseDir}:\n");
            foreach (array_unique($missing) as $file) fwrite(STDERR, " - {$file}\n");
            exit(1);
        }
    ' "$manifest"
}

echo
echo "[1/6] Fetching origin/main without changing the working tree..."
git fetch origin main

# Preflight the incoming build before updating the live Laravel source.
# If assets are incomplete, stop while the currently deployed code remains untouched.
PREFLIGHT_DIR="$(mktemp -d)"
trap 'rm -rf "$PREFLIGHT_DIR"' EXIT
mkdir -p "$PREFLIGHT_DIR"
git archive origin/main public/build | tar -x -C "$PREFLIGHT_DIR"

echo
echo "[2/6] Validating incoming build assets..."
validate_manifest_assets "$PREFLIGHT_DIR/public/build"

echo
echo "[3/6] Updating source code (fast-forward only)..."
git pull --ff-only origin main

echo
echo "[4/6] Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-scripts

PUBLIC_DIR="$(pwd)/public"
BUILD_DIR="$PUBLIC_DIR/build"
PUBLIC_HTML="$(cd ../../public_html && pwd)"
TARGET_BUILD_DIR="$PUBLIC_HTML/build"

echo
echo "[5/6] Syncing and verifying public assets..."
if [[ -d "$PUBLIC_DIR/images" ]]; then
    mkdir -p "$PUBLIC_HTML/images"
    rsync -a "$PUBLIC_DIR/images/" "$PUBLIC_HTML/images/"
    echo "Images synced successfully."
fi

if [[ ! -d "$BUILD_DIR" ]]; then
    echo "ERROR: Build directory not found: $BUILD_DIR" >&2
    exit 1
fi

validate_manifest_assets "$BUILD_DIR"
mkdir -p "$TARGET_BUILD_DIR"
rsync -a "$BUILD_DIR/" "$TARGET_BUILD_DIR/"
validate_manifest_assets "$TARGET_BUILD_DIR"
echo "Build assets synced and verified successfully."

echo
echo "[6/6] Clearing Laravel cache..."
php artisan optimize:clear

echo
echo "======================================"
echo " DEPLOYMENT SUCCESSFUL"
echo "Incoming manifest prechecked; live and public_html build assets verified."
echo "======================================"
