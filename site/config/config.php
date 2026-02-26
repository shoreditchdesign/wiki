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

$panelPath = static function (): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $queryPos = strpos($uri, '?');

    if ($queryPos !== false) {
        $uri = substr($uri, 0, $queryPos);
    }

    return $uri;
};

return [
    'debug' => $debug,
    'yaml.handler' => 'symfony',
    'url' => $url ?? null,
    'panel.menu' => [
        'globals' => [
            'label' => 'Globals',
            'icon' => 'layers',
            'link' => 'pages/global',
            'current' => static function () use ($panelPath): bool {
                return str_contains($panelPath(), '/panel/pages/global');
            },
        ],
        'handbooks' => [
            'label' => 'Handbooks',
            'icon' => 'book',
            'link' => 'pages/sites',
            'current' => static function () use ($panelPath): bool {
                return str_contains($panelPath(), '/panel/pages/sites');
            },
        ],
        'users',
        'system',
    ],
    'thumbs' => [
        'driver' => $thumbsDriver,
    ],
    'home' => 'home',
    'routes' => [
        [
            'pattern' => 'site',
            'action' => function () {
                return page('sites') ?? site()->errorPage();
            },
        ],
        [
            'pattern' => 'site/(:all)',
            'action' => function (string $path) {
                return page('sites/' . $path) ?? site()->errorPage();
            },
        ],
    ],
];
