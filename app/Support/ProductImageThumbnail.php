<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProductImageThumbnail
{
    public const MAX_WIDTH = 600;
    public const MAX_HEIGHT = 600;
    public const QUALITY = 78;

    public static function pathFor(string $originalPath): ?string
    {
        $originalPath = str_replace('\\', '/', ltrim($originalPath, '/'));

        if (
            !str_starts_with($originalPath, 'products/') ||
            str_starts_with($originalPath, 'http://') ||
            str_starts_with($originalPath, 'https://') ||
            str_contains($originalPath, '../')
        ) {
            return null;
        }

        $relative = substr($originalPath, strlen('products/'));
        $directory = pathinfo($relative, PATHINFO_DIRNAME);
        $name = pathinfo($relative, PATHINFO_FILENAME);
        $relativeThumbnail = ($directory === '.' ? '' : $directory . '/') . $name . '.webp';

        return 'products/thumbnails/' . $relativeThumbnail;
    }

    public static function adminUrl(?string $originalPath): ?string
    {
        if (!$originalPath) {
            return null;
        }

        $thumbnailPath = self::pathFor($originalPath);
        if ($thumbnailPath && Storage::disk('public')->exists($thumbnailPath)) {
            return Storage::disk('public')->url($thumbnailPath);
        }

        if (str_starts_with($originalPath, 'http://') || str_starts_with($originalPath, 'https://')) {
            return $originalPath;
        }

        return (str_starts_with($originalPath, 'images/') || str_starts_with($originalPath, 'storage/'))
            ? asset($originalPath)
            : asset('storage/' . $originalPath);
    }

    public static function generate(string $originalPath, bool $overwrite = false): bool
    {
        $disk = Storage::disk('public');
        $thumbnailPath = self::pathFor($originalPath);

        if (!$thumbnailPath || !$disk->exists($originalPath)) {
            return false;
        }

        if (!$overwrite && $disk->exists($thumbnailPath)) {
            return true;
        }

        if (!function_exists('imagewebp') || !function_exists('imagecreatefromstring')) {
            throw new RuntimeException('GD WebP tidak tersedia di PHP yang menjalankan perintah ini.');
        }

        $bytes = $disk->get($originalPath);
        $source = @imagecreatefromstring($bytes);
        unset($bytes);

        if (!$source) {
            throw new RuntimeException("Gambar tidak dapat dibaca: {$originalPath}");
        }

        try {
            $width = imagesx($source);
            $height = imagesy($source);
            if ($width < 1 || $height < 1) {
                throw new RuntimeException("Dimensi gambar tidak valid: {$originalPath}");
            }

            $scale = min(self::MAX_WIDTH / $width, self::MAX_HEIGHT / $height, 1);
            $targetWidth = max(1, (int) round($width * $scale));
            $targetHeight = max(1, (int) round($height * $scale));
            $target = imagecreatetruecolor($targetWidth, $targetHeight);

            try {
                imagealphablending($target, false);
                imagesavealpha($target, true);
                $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
                imagefill($target, 0, 0, $transparent);

                if (!imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height)) {
                    throw new RuntimeException("Gagal mengubah ukuran gambar: {$originalPath}");
                }

                $absolutePath = $disk->path($thumbnailPath);
                $directory = dirname($absolutePath);
                if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                    throw new RuntimeException('Gagal membuat direktori thumbnail.');
                }

                if (!imagewebp($target, $absolutePath, self::QUALITY)) {
                    throw new RuntimeException("Gagal menyimpan thumbnail: {$originalPath}");
                }
            } finally {
                imagedestroy($target);
            }
        } finally {
            imagedestroy($source);
        }

        return true;
    }
}
