<?php
$content = file_get_contents('routes/web.php');

$search = "Route::post('/maintenances/settings', [MaintenanceController::class, 'saveSettings'])
        ->middleware('permission:vehicles.view')
        ->name('maintenances.settings.store');";

$replace = $search . "

    Route::post('/maintenances/settings/mechanics', [MaintenanceController::class, 'storeMechanic'])
        ->middleware('permission:vehicles.view')
        ->name('maintenances.mechanics.store');

    Route::put('/maintenances/settings/mechanics/{mechanic}', [MaintenanceController::class, 'updateMechanic'])
        ->middleware('permission:vehicles.view')
        ->name('maintenances.mechanics.update');

    Route::patch('/maintenances/settings/mechanics/{mechanic}/toggle', [MaintenanceController::class, 'toggleMechanic'])
        ->middleware('permission:vehicles.view')
        ->name('maintenances.mechanics.toggle');

    Route::delete('/maintenances/settings/mechanics/{mechanic}', [MaintenanceController::class, 'destroyMechanic'])
        ->middleware('permission:vehicles.view')
        ->name('maintenances.mechanics.destroy');";

$content = str_replace(str_replace("\n", "\r\n", $search), str_replace("\n", "\r\n", $replace), $content);
file_put_contents('routes/web.php', $content);
echo "DONE\n";
