<?php

namespace App\Services;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrisQrCodeService
{
    public function generateSvg(string $content, int $size = 320): string
    {
        if (trim($content) === '') {
            throw new \InvalidArgumentException('QRIS content tidak boleh kosong.');
        }

        $renderer = new ImageRenderer(
            new RendererStyle($size, 10),
            new SvgImageBackEnd()
        );

        return (new Writer($renderer))->writeString($content);
    }

    public function generatePng(string $content, int $size = 320): string
    {
        if (trim($content) === '') {
            throw new \InvalidArgumentException('QRIS content tidak boleh kosong.');
        }

        if (!extension_loaded('imagick')) {
            throw new \RuntimeException('Ekstensi Imagick tidak tersedia.');
        }

        $renderer = new ImageRenderer(
            new RendererStyle($size, 10),
            new ImagickImageBackEnd()
        );

        return (new Writer($renderer))->writeString($content);
    }
}
