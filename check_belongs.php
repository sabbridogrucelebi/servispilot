<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function getModels($dir) {
    $models = [];
    foreach(glob($dir . '/*.php') as $f) {
        $models[] = $f;
    }
    foreach(glob($dir . '/*/*.php') as $f) {
        $models[] = $f;
    }
    return $models;
}

$missing = [];
foreach(getModels(app_path('Models')) as $f) {
    $content = file_get_contents($f);
    if(strpos($content, 'class ') !== false && (strpos($content, 'extends Model') !== false || strpos($content, 'extends Authenticatable') !== false)) {
        if(strpos($content, 'BelongsToCompany') === false) {
            $missing[] = basename($f);
        }
    }
}
echo implode("\n", $missing);
