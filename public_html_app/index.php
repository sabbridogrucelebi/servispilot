<?php

// /app alt dizininden çalıştığımız için asset URL'lerini ayarla
putenv('ASSET_URL=/app');
$_ENV['ASSET_URL'] = '/app';
$_SERVER['ASSET_URL'] = '/app';

$laravelBase   = __DIR__ . '/../../servispilot';
$laravelPublic = $laravelBase . '/public';
$requestUri    = $_SERVER['REQUEST_URI'];

// /app prefix'ini kaldırarak gerçek dosya yolunu bul
$path = preg_replace('#^/app#', '', parse_url($requestUri, PHP_URL_PATH));

// Eğer istenen dosya Laravel public klasöründe varsa, doğrudan sun
$filePath = $laravelPublic . $path;
if ($path !== '/' && $path !== '' && file_exists($filePath) && !is_dir($filePath)) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'webp'  => 'image/webp',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'eot'   => 'application/vnd.ms-fontobject',
        'json'  => 'application/json',
        'map'   => 'application/json',
    ];
    $mime = $mimeTypes[$ext] ?? (function_exists('mime_content_type') ? mime_content_type($filePath) : 'application/octet-stream');
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: public, max-age=31536000');
    readfile($filePath);
    exit;
}

// Statik dosya değilse Laravel'i başlat
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = $laravelBase . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $laravelBase . '/vendor/autoload.php';

/** @var Application $app */
$app = require_once $laravelBase . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
