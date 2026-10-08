<?php

namespace App\Services;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailArchiveService
{
    public function send(string $recipient, Mailable $mailable): void
    {
        $sentMessage = Mail::to($recipient)->send($mailable);

        if ($sentMessage instanceof SentMessage) {
            try {
                $this->archive($sentMessage);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    public function archive(SentMessage $sentMessage): bool
    {
        if (!config('email_archive.enabled')) {
            return false;
        }

        $username = config('email_archive.username');
        $password = config('email_archive.password');

        if (!$username || !$password) {
            Log::warning('Email archive skipped: IMAP credentials are not configured.');

            return false;
        }

        $mailbox = sprintf(
            '{%s:%d/imap/%s}%s',
            config('email_archive.host'),
            config('email_archive.port'),
            config('email_archive.encryption'),
            config('email_archive.folder'),
        );

        $imap = @imap_open(
            $mailbox,
            $username,
            $password
        );

        if (!$imap) {
            Log::error('Email archive failed: IMAP connection failed.', [
                'host' => config('email_archive.host'),
                'folder' => config('email_archive.folder'),
                'error' => imap_last_error(),
            ]);

            return false;
        }

        try {
            $rawMessage = $sentMessage
                ->getSymfonySentMessage()
                ->getOriginalMessage()
                ->toString();

            $result = imap_append(
                $imap,
                $mailbox,
                $rawMessage
            );

            if (!$result) {
                Log::error('Email archive failed: IMAP append failed.', [
                    'folder' => config('email_archive.folder'),
                    'error' => imap_last_error(),
                ]);

                return false;
            }

            return true;
        } finally {
            imap_close($imap);
        }
    }
}
