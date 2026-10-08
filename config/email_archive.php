<?php

return [
    'enabled' => env('EMAIL_ARCHIVE_ENABLED', false),

    'host' => env('EMAIL_ARCHIVE_HOST', 'imap.hostinger.com'),
    'port' => (int) env('EMAIL_ARCHIVE_PORT', 993),
    'encryption' => env('EMAIL_ARCHIVE_ENCRYPTION', 'ssl'),
    'username' => env('EMAIL_ARCHIVE_USERNAME', env('MAIL_USERNAME')),
    'password' => env('EMAIL_ARCHIVE_PASSWORD', env('MAIL_PASSWORD')),
    'folder' => env('EMAIL_ARCHIVE_FOLDER', 'INBOX.Sent'),
];
