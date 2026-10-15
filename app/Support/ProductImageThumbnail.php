<?php

namespace App\\Support;

use Illuminate\\Support\\Facades\\Log;
use Illuminate\\Support\\Facades\\Storage;
use Throwable;

class ProductImageThumbnail
{
    public const MAX_WIDTH = 600;
    public const MAX_HEIGHT = 600;
    public const QUALITY = 78;

    public static function pathFor(string $originalPath): ?string
    {
        if (
            !str_starts_with($originalPath, 'products/') ||
            str_starts_with($originalPath, 'http://') ||
            str_starts_with($originalPath, 'https://')
        ) {
            return null;
        }

        $relative = substr($originalPath, strlen('products/'));
        $withoutExtension = pathinfo($relative, PATHINFO_DIRNAME) === '.'
            ? pathinfo($relative, PATHINFO_FILENAME)
            : pathinfo($relative, PATHINFO_DIRNAME) . '/' . pathinfo($relative, PATHINFO_FILENAME);

        return 'products/thumbnails/' . $withoutExtension . '.webp';
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

        if (!function_exists('imagewebp') || !function_exists('imagecreatefromwebp')) {
            throw new RuntimeException('Ekstensi GD dengan dukungan WebP tidak tersedia.');
        }

        $sourcePath = $disk->path($originalPath);
        $imageInfo = @getimagesize($sourcePath);

        if (!$imageInfo || empty($imageInfo['mime'])) {
            throw new RuntimeException("File bukan gambar yang valid: {$originalPath}");
        }

        $source = match ($imageInfo['mime']) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            default => false,
        };

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

            if (!$target) {
                throw new RuntimeException('Gagal menyiapkan kanvas thumbnail.');
            }

            try {
                imagealphablending($target, false);
                imagesavealpha($target, true);
                $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
                imagefill($target, 0, 0, $transparent);

                if (!imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height)) {
                    throw new RuntimeException("Gagal mengubah ukuran gambar: {$originalPath}");
                }

                $absoluteThumbnailPath = $disk->path($thumbnailPath);
                $directory = dirname($absoluteThumbnailPath);

                if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                    throw new RuntimeException('Gagal membuat folder thumbnail.');
                }

                if (!imagewebp($target, $absoluteThumbnailPath, self::QUALITY)) {
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
