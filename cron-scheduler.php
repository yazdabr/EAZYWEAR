<?php

chdir(__DIR__);

$_SERVER['argv'] = [
    'artisan',
    'schedule:run',
];

require __DIR__ . '/artisan';