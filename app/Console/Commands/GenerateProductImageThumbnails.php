<?php

namespace App\\Console\\Commands;

use App\\Models\\ProductImage;
use App\\Support\\ProductImageThumbnail;
use Illuminate\\Console\\Command;
use Illuminate\\Support\\Facades\\Storage;
use Throwable;

class GenerateProductImageThumbnails extends Command
{
    protected $signature = 'products:generate-thumbnails
                            {--dry-run : Hanya tampilkan file yang akan diproses}
                            {--limit=0 : Batasi jumlah foto yang diperiksa (0 = semua)}
                            {--overwrite : Buat ulang thumbnail yang sudah ada}';

    protected $description = 'Buat thumbnail WebP untuk foto produk khusus tampilan admin.';

    public function handle(): int
    {
        if (!function_exists('imagewebp')) {
            $this->error('GD dengan dukungan WebP tidak tersedia.');
            return self::FAILURE;
        }

        $query = ProductImage::query()->select(['id', 'image'])->orderBy('id');
        $limit = max(0, (int) $this->option('limit'));

        if ($limit > 0) {
            $query->limit($limit);
        }

        $images = $query->get();
        $created = 0;
        $skipped = 0;
        $failed = 0;
        $disk = Storage::disk('public');

        foreach ($images as $image) {
            $path = (string) $image->image;
            $thumbnailPath = ProductImageThumbnail::pathFor($path);

            if (!$thumbnailPath || !$disk->exists($path)) {
                $this->line("[SKIP] #{$image->id} {$path} — file lokal tidak tersedia atau bukan path products/.");
                $skipped++;
                continue;
            }

            if (!$this->option('overwrite') && $disk->exists($thumbnailPath)) {
                $this->line("[ADA]  #{$image->id} {$thumbnailPath}");
                $skipped++;
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("[SIMULASI] #{$image->id}: {$path} -> {$thumbnailPath}");
                $created++;
                continue;
            }

            try {
                ProductImageThumbnail::generate($path, (bool) $this->option('overwrite'));
                $this->info("[OK] #{$image->id}: {$thumbnailPath}");
                $created++;
            } catch (Throwable $exception) {
                report($exception);
                $this->error("[GAGAL] #{$image->id} {$path}: {$exception->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->table(
            ['Diproses / simulasi', 'Dilewati', 'Gagal'],
            [[$created, $skipped, $failed]]
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
