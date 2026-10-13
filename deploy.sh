#!/bin/bash

set -Eeuo pipefail

echo "======================================"
echo " EAZYWEAR PRODUCTION DEPLOYMENT"
echo "======================================"

CURRENT_BRANCH="$(git branch --show-current)"
# Production may intentionally be pinned to a detached commit for rollback.
# Allow that safe state, but refuse deployments from any other named branch.
if [[ -n "$CURRENT_BRANCH" && "$CURRENT_BRANCH" != "main" ]]; then
    echo "ERROR: Deployment must run from 'main' or a detached production commit." >&2
    echo "Current branch: '$CURRENT_BRANCH'" >&2
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

# Validate the incoming build before changing the live Laravel source.
PREFLIGHT_DIR="$(mktemp -d)"
trap 'rm -rf "$PREFLIGHT_DIR"' EXIT
git archive origin/main public/build | tar -x -C "$PREFLIGHT_DIR"

echo
echo "[2/6] Validating incoming build assets..."
validate_manifest_assets "$PREFLIGHT_DIR/public/build"

PUBLIC_DIR="$(pwd)/public"
BUILD_DIR="$PUBLIC_DIR/build"
PUBLIC_HTML="$(cd ../../public_html && pwd)"
TARGET_BUILD_DIR="$PUBLIC_HTML/build"

# Copy hashed assets first. rsync without --delete keeps old hashed files available
# while the application still references the previous manifest.
echo
echo "[3/6] Syncing prevalidated build assets before updating application code..."
mkdir -p "$TARGET_BUILD_DIR"
rsync -a "$PREFLIGHT_DIR/public/build/" "$TARGET_BUILD_DIR/"
validate_manifest_assets "$TARGET_BUILD_DIR"
echo "Incoming build assets synced and verified."

echo
echo "[4/6] Updating source code (fast-forward only)..."
if [[ -z "$CURRENT_BRANCH" ]]; then
    git switch main
fi
git pull --ff-only origin main

echo
echo "[5/6] Installing Composer dependencies and syncing other public assets..."
composer install --no-dev --optimize-autoloader --no-scripts

if [[ -d "$PUBLIC_DIR/images" ]]; then
    mkdir -p "$PUBLIC_HTML/images"
    rsync -a "$PUBLIC_DIR/images/" "$PUBLIC_HTML/images/"
    echo "Images synced successfully."
fi

# Recheck the app build and public document root after the code update.
validate_manifest_assets "$BUILD_DIR"
rsync -a "$BUILD_DIR/" "$TARGET_BUILD_DIR/"
validate_manifest_assets "$TARGET_BUILD_DIR"
echo "Laravel and public_html build assets match and are verified."

echo
echo "[6/6] Clearing Laravel cache..."
php artisan optimize:clear

echo
echo "======================================"
echo " DEPLOYMENT SUCCESSFUL"
echo "Incoming assets were prevalidated and synced before application code update."
echo "======================================"
