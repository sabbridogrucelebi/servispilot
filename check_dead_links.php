<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$namedRoutes = array_keys(\Illuminate\Support\Facades\Route::getRoutes()->getRoutesByName());

function searchDir($dir, &$results = []) {
    $files = scandir($dir);
    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            if (pathinfo($path, PATHINFO_EXTENSION) == 'php') {
                $results[] = $path;
            }
        } else if ($value != "." && $value != "..") {
            searchDir($path, $results);
        }
    }
    return $results;
}

$views = searchDir(__DIR__ . '/resources/views');
$deadLinks = [];

foreach ($views as $view) {
    $content = file_get_contents($view);
    preg_match_all("/route\(['\"]([^'\"]+)['\"]/", $content, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $routeCall) {
            if (!in_array($routeCall, $namedRoutes)) {
                $relative = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $view);
                $deadLinks[] = "Dead Route Call: {$routeCall} in {$relative}";
            }
        }
    }
}
echo implode("\n", array_unique($deadLinks));
if (empty($deadLinks)) { echo "No dead route calls found."; }
