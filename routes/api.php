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
