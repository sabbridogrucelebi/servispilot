<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = \Illuminate\Support\Facades\Route::getRoutes();
$dead = [];
foreach ($routes as $route) {
    $action = $route->getActionName();
    if ($action !== 'Closure' && strpos($action, '@') !== false) {
        list($controller, $method) = explode('@', $action);
        if (class_exists($controller)) {
            if (!method_exists($controller, $method)) {
                $dead[] = "Dead Method: {$controller}@{$method} for route {$route->uri()}";
            }
        } else {
            $dead[] = "Dead Controller: {$controller} for route {$route->uri()}";
        }
    }
}
echo implode("\n", $dead);
if (empty($dead)) { echo "No dead routes found."; }
