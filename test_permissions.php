<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$permissions = \App\Models\Permission::pluck('key')->toArray();
$needed = [
    'customers.create', 'customers.edit', 'customers.delete',
    'trips.create', 'trips.edit', 'trips.delete',
    'payrolls.create', 'payrolls.edit', 'payrolls.delete',
    'drivers.create', 'drivers.edit', 'drivers.delete',
    'documents.create', 'documents.edit', 'documents.delete',
    'maintenances.create', 'maintenances.edit', 'maintenances.delete',
    'penalties.create', 'penalties.edit', 'penalties.delete',
    'fuels.create', 'fuels.edit', 'fuels.delete',
    'vehicles.create', 'vehicles.edit', 'vehicles.delete',
    'finance.view', 'finance.create', 'finance.edit', 'finance.delete',
    'reports.view', 'reports.export',
    'activity_logs.view',
    'chat.view', 'chat.create'
];
$missing = array_diff($needed, $permissions);
echo implode("\n", $missing) . "\n";
