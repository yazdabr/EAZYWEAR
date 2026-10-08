<?php

namespace Tests\Unit\Services;

use App\Services\EmailArchiveService;
use Illuminate\Mail\SentMessage;
use Tests\TestCase;

class EmailArchiveServiceTest extends TestCase
{
    public function test_archive_is_skipped_when_disabled(): void
    {
        config([
            'email_archive.enabled' => false,
        ]);

        $service = app(EmailArchiveService::class);

        $result = $service->archive(
            $this->createMock(SentMessage::class)
        );

        $this->assertFalse($result);
    }
}
