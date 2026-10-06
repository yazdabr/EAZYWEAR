<?php

namespace Tests\Unit;

use App\Services\QrisQrCodeService;
use Tests\TestCase;

class QrisQrCodeServiceTest extends TestCase
{
    public function test_it_generates_svg_from_qris_content(): void
    {
        $service = app(QrisQrCodeService::class);

        $svg = $service->generateSvg(
            '00020101021226670016COM.DOKU.WWW01189360091500000000000215QRIS2026100614300303UMI5204599953033605802ID5908Eazywear6007Banjarmasin6105701116304ABCD'
        );

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
        $this->assertStringContainsString('xmlns="http://www.w3.org/2000/svg"', $svg);
    }

    public function test_it_rejects_empty_qris_content(): void
    {
        $service = app(QrisQrCodeService::class);

        $this->expectException(\InvalidArgumentException::class);

        $service->generateSvg('');
    }
}