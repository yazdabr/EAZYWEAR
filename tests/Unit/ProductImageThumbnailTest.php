<?php

namespace Tests\Unit;

use App\Support\ProductImageThumbnail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageThumbnailTest extends TestCase
{
    private function pngBytes(int $width = 1200, int $height = 800): string
    {
        $image = imagecreatetruecolor($width, $height);

        ob_start();
        imagepng($image);
        $bytes = ob_get_clean();

        imagedestroy($image);

        return $bytes;
    }

    public function test_it_generates_webp_without_changing_original(): void
    {
        if (!function_exists('imagewebp') || !function_exists('imagecreatefromstring')) {
            $this->markTestSkipped('PHP GD WebP tidak tersedia.');
        }

        Storage::fake('public');

        $originalPath = 'products/thumbnail-test.png';
        $originalBytes = $this->pngBytes();

        Storage::disk('public')->put($originalPath, $originalBytes);

        $result = ProductImageThumbnail::generate($originalPath);

        $thumbnailPath = 'products/thumbnails/thumbnail-test.webp';

        $this->assertTrue($result);
        Storage::disk('public')->assertExists($originalPath);
        Storage::disk('public')->assertExists($thumbnailPath);
        $this->assertSame(
            $originalBytes,
            Storage::disk('public')->get($originalPath),
            'Foto asli tidak boleh berubah.'
        );

        $thumbnail = imagecreatefromwebp(
            Storage::disk('public')->path($thumbnailPath)
        );

        $this->assertNotFalse($thumbnail);
        $this->assertLessThanOrEqual(600, imagesx($thumbnail));
        $this->assertLessThanOrEqual(600, imagesy($thumbnail));

        imagedestroy($thumbnail);
    }

    public function test_admin_url_uses_thumbnail_when_available(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put(
            'products/thumbnails/sample.webp',
            'fake-webp'
        );

        $url = ProductImageThumbnail::adminUrl('products/sample.png');

        $this->assertStringContainsString(
            'products/thumbnails/sample.webp',
            $url
        );
    }

    public function test_admin_url_falls_back_to_original_when_thumbnail_is_missing(): void
    {
        Storage::fake('public');

        $url = ProductImageThumbnail::adminUrl('products/sample.png');

        $this->assertSame(asset('storage/products/sample.png'), $url);
    }

    public function test_external_image_url_is_preserved(): void
    {
        $url = 'https://example.com/products/sample.png';

        $this->assertSame($url, ProductImageThumbnail::adminUrl($url));
    }

    public function test_non_product_path_is_not_converted_to_thumbnail(): void
    {
        $this->assertNull(ProductImageThumbnail::pathFor('images/sample.png'));
        $this->assertNull(ProductImageThumbnail::pathFor('../private/sample.png'));
    }
}