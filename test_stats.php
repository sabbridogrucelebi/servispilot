<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$fuel = \App\Models\Fuel::find(10);
if ($fuel) {
    $fuel->company_id = 2; // Fixed!
    $fuel->save();
    echo "Fuel ID 10 company_id fixed to 2!\n";
} else {
    echo "Fuel ID 10 not found.\n";
}
