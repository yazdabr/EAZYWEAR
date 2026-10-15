<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use App\Support\ProductImageThumbnail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateProductThumbnails extends Command
{
    protected $signature = 'products:generate-thumbnails
                            {--dry-run : Simulasikan tanpa membuat file}
                            {--limit=0 : Batasi jumlah gambar yang diperiksa (0 = semua)}
                            {--overwrite : Buat ulang thumbnail yang sudah ada}';

    protected $description = 'Buat thumbnail WebP khusus admin tanpa mengubah foto asli.';

    public function handle(): int
    {
        if (!function_exists('imagewebp') || !function_exists('imagecreatefromstring')) {
            $this->error('GD dengan dukungan WebP tidak tersedia pada PHP CLI ini.');
            return self::FAILURE;
        }

        $limit = max(0, (int) $this->option('limit'));
        $query = ProductImage::query()->select(['id', 'image'])->orderBy('id');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $disk = Storage::disk('public');
        $created = 0;
        $existing = 0;
        $missing = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($query->cursor() as $image) {
            $path = (string) $image->image;
            $thumb = ProductImageThumbnail::pathFor($path);

            if (!$thumb) {
                $skipped++;
                $this->line("SKIP: {$path}");
                continue;
            }

            if (!$disk->exists($path)) {
                $missing++;
                $this->warn("MISSING ORIGINAL: {$path}");
                continue;
            }

            if (!$this->option('overwrite') && $disk->exists($thumb)) {
                $existing++;
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("WOULD CREATE: {$path} -> {$thumb}");
                continue;
            }

            try {
                if (ProductImageThumbnail::generate($path, (bool) $this->option('overwrite'))) {
                    $created++;
                    $this->line("CREATED: {$thumb}");
                } else {
                    $failed++;
                    $this->warn("FAILED: {$path}");
                }
            } catch (Throwable $exception) {
                $failed++;
                $this->warn("FAILED: {$path} - {$exception->getMessage()}");
            }
        }

        $this->newLine();
        $this->table(['Status', 'Jumlah'], [
            ['Thumbnail dibuat', $created],
            ['Sudah ada', $existing],
            ['Foto asli tidak ditemukan', $missing],
            ['Gagal', $failed],
            ['Dilewati', $skipped],
            ['Mode simulasi', $this->option('dry-run') ? 'ya' : 'tidak'],
        ]);

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
