<?php
$content = file_get_contents('routes/api.php');

$search = "    Route::delete('/maintenances/{id}', [\App\Http\Controllers\Api\V1\MaintenanceApiController::class, 'destroy']);";

$replace = "    Route::delete('/maintenances/{id}', [\App\Http\Controllers\Api\V1\MaintenanceApiController::class, 'destroy']);

    Route::get('/maintenances-settings', [\App\Http\Controllers\Api\V1\MaintenanceApiController::class, 'settings']);
    Route::post('/maintenances-settings', [\App\Http\Controllers\Api\V1\MaintenanceApiController::class, 'saveSettings']);
    
    Route::post('/maintenances-mechanics', [\App\Http\Controllers\Api\V1\MaintenanceApiController::class, 'storeMechanic']);
    Route::put('/maintenances-mechanics/{id}', [\App\Http\Controllers\Api\V1\MaintenanceApiController::class, 'updateMechanic']);
    Route::patch('/maintenances-mechanics/{id}/toggle', [\App\Http\Controllers\Api\V1\MaintenanceApiController::class, 'toggleMechanic']);
    Route::delete('/maintenances-mechanics/{id}', [\App\Http\Controllers\Api\V1\MaintenanceApiController::class, 'destroyMechanic']);";

$content = str_replace(str_replace("\n", "\r\n", $search), str_replace("\n", "\r\n", $replace), $content);
file_put_contents('routes/api.php', $content);
echo "DONE\n";
