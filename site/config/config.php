<?php

declare(strict_types=1);

use Kirby\Cms\App;

$url = null;
if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'https';
    $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    $url = $proto . '://' . $host;
}

$debugEnv = strtolower((string) getenv('KIRBY_DEBUG'));
$debug = in_array($debugEnv, ['1', 'true', 'yes', 'on'], true);

$thumbsDriver = extension_loaded('imagick') ? 'imagick' : 'gd';

return [
    'debug' => $debug,
    'yaml.handler' => 'symfony',
    'url' => $url ?? null,
    'panel.menu' => [
        'site',
        'users',
        'system',
    ],
    'thumbs' => [
        'driver' => $thumbsDriver,
    ],
    'home' => 'home',
];
