<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\PersonnelController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\TripController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::apiResource('/vehicles', VehicleController::class)->names('api.vehicles');
    Route::get('/vehicles/{vehicle}/documents', [VehicleController::class, 'documents']);
    Route::get('/vehicles/{vehicle}/fuels', [VehicleController::class, 'fuels']);
    Route::get('/vehicles/{vehicle}/maintenances', [VehicleController::class, 'maintenances']);
    Route::get('/vehicles/{vehicle}/maintenances/export-pdf', [VehicleController::class, 'exportMaintenancesPdf']);
    Route::get('/vehicles/{vehicle}/penalties', [VehicleController::class, 'penalties']);
    Route::get('/vehicles/{vehicle}/reports', [VehicleController::class, 'reports']);
    Route::get('/vehicles/{vehicle}/gallery', [VehicleController::class, 'gallery']);
    Route::post('/vehicles/{vehicle}/gallery', [VehicleController::class, 'uploadImage']);
    Route::post('/vehicles/{vehicle}/gallery/{image}/featured', [VehicleController::class, 'setFeaturedImage']);
    Route::delete('/vehicles/{vehicle}/gallery/{image}', [VehicleController::class, 'deleteImage']);
    Route::get('/personnel', [PersonnelController::class, 'index']);
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::get('/trips', [TripController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| V1 API ROUTES (New Standard)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Api\V1\DashboardApiController::class, 'index']);
    Route::get('/personnel', [\App\Http\Controllers\Api\V1\PersonnelApiController::class, 'index']);
    Route::get('/personnel/{id}', [\App\Http\Controllers\Api\V1\PersonnelApiController::class, 'show']);
    Route::get('/customers', [\App\Http\Controllers\Api\V1\CustomerApiController::class, 'index']);
    Route::get('/customers/{id}', [\App\Http\Controllers\Api\V1\CustomerApiController::class, 'show']);
    Route::get('/trips', [\App\Http\Controllers\Api\V1\TripApiController::class, 'index']);
    Route::get('/trips/{id}', [\App\Http\Controllers\Api\V1\TripApiController::class, 'show']);
    Route::get('/activity-logs', [\App\Http\Controllers\Api\V1\ActivityLogApiController::class, 'index']);
    Route::get('/finance/summary', [\App\Http\Controllers\Api\V1\FinanceApiController::class, 'summary']);
    Route::get('/payrolls', [\App\Http\Controllers\Api\V1\PayrollApiController::class, 'index']);
    Route::get('/payrolls/{id}', [\App\Http\Controllers\Api\V1\PayrollApiController::class, 'show']);
    
    // Vehicles (Read-Only)
    Route::get('/vehicles', [\App\Http\Controllers\Api\V1\VehicleApiController::class, 'index']);
    Route::get('/vehicles/{id}', [\App\Http\Controllers\Api\V1\VehicleApiController::class, 'show']);
    Route::get('/vehicles/{id}/documents', [\App\Http\Controllers\Api\V1\VehicleApiController::class, 'documents']);
    Route::get('/vehicles/{id}/fuels', [\App\Http\Controllers\Api\V1\VehicleApiController::class, 'fuels']);
    Route::get('/vehicles/{id}/maintenances', [\App\Http\Controllers\Api\V1\VehicleApiController::class, 'maintenances']);
    Route::get('/vehicles/{id}/penalties', [\App\Http\Controllers\Api\V1\VehicleApiController::class, 'penalties']);
    Route::get('/vehicles/{id}/gallery', [\App\Http\Controllers\Api\V1\VehicleApiController::class, 'gallery']);
    Route::get('/vehicles/{id}/reports', [\App\Http\Controllers\Api\V1\VehicleApiController::class, 'reports']);
});
