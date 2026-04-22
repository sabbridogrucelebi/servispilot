<?php

use App\Models\Trip;
use App\Models\Fuel;
use App\Models\Document;
use App\Models\Payroll;
use App\Models\Fleet\Driver;
use App\Models\Fleet\Vehicle;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TripController;
use App\Http\Controllers\FuelController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RouteStopController;
use App\Http\Controllers\FuelStationController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\CompanyUserController;
use App\Http\Controllers\ServiceRouteController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\TrafficPenaltyController;
use App\Http\Controllers\CustomerContractController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\CustomerPortalUserController;
use App\Http\Controllers\CustomerServiceRouteController;
use App\Http\Controllers\VehicleTrackingController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\CompanyController as SuperAdminCompanyController;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| PUBLIC VEHICLE IMAGE UPLOAD
|--------------------------------------------------------------------------
*/
Route::get('/vehicle-photo-upload/{vehicle}/{token}', [VehicleController::class, 'publicImageUploadForm'])
    ->name('vehicles.public-images.form');

Route::post('/vehicle-photo-upload/{vehicle}/{token}', [VehicleController::class, 'publicImageUploadStore'])
    ->name('vehicles.public-images.store');

Route::get('/dashboard', function () {

    $vehicleCount = Vehicle::count();
    $driverCount = Driver::count();

    $todayTrips = Trip::whereDate('trip_date', now()->toDateString())->count();

    $monthlyIncome = Trip::whereMonth('trip_date', now()->month)
        ->whereYear('trip_date', now()->year)
        ->sum('trip_price');

    $today = now()->startOfDay();
    $sevenDaysLater = now()->copy()->addDays(7)->startOfDay();
    $thirtyDaysLater = now()->copy()->addDays(30)->startOfDay();

    $expiredDocumentsCount = Document::whereNotNull('end_date')
        ->whereDate('end_date', '<', $today)
        ->count();

    $documentsExpiringIn7DaysCount = Document::whereNotNull('end_date')
        ->whereDate('end_date', '>=', $today)
        ->whereDate('end_date', '<=', $sevenDaysLater)
        ->count();

    $documentsExpiringIn30DaysCount = Document::whereNotNull('end_date')
        ->whereDate('end_date', '>', $sevenDaysLater)
        ->whereDate('end_date', '<=', $thirtyDaysLater)
        ->count();

    $upcomingDocuments = Document::whereNotNull('end_date')
        ->whereDate('end_date', '>=', $today)
        ->whereDate('end_date', '<=', $thirtyDaysLater)
        ->orderBy('end_date')
        ->take(10)
        ->get();

    $totalFuel = Fuel::sum('total_cost');
    $totalSalary = Payroll::sum('net_salary');
    $netProfit = $monthlyIncome - ($totalFuel + $totalSalary);

    return view('dashboard', compact(
        'vehicleCount',
        'driverCount',
        'todayTrips',
        'monthlyIncome',
        'expiredDocumentsCount',
        'documentsExpiringIn7DaysCount',
        'documentsExpiringIn30DaysCount',
        'upcomingDocuments',
        'totalFuel',
        'totalSalary',
        'netProfit'
    ));

})->middleware(['auth', 'verified', 'permission:dashboard.view'])->name('dashboard');

    Route::get('/vehicle-tracking', [VehicleTrackingController::class, 'index'])
        ->middleware(['auth', 'permission:vehicles.view'])
        ->name('vehicle-tracking.index');

    Route::post('/vehicle-tracking', [VehicleTrackingController::class, 'store'])
        ->middleware(['auth', 'permission:vehicles.view'])
        ->name('vehicle-tracking.store');

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER PORTAL
    |--------------------------------------------------------------------------
    */
    Route::get('/customer-portal', [CustomerPortalController::class, 'dashboard'])
        ->name('customer.portal.dashboard');

    Route::get('/customer-portal/trips', [CustomerPortalController::class, 'trips'])
        ->name('customer.portal.trips');

    /*
    |--------------------------------------------------------------------------
    | VEHICLE EXTRA
    |--------------------------------------------------------------------------
    */
    Route::get('/vehicles/export/excel', [VehicleController::class, 'exportExcel'])
        ->middleware('permission:vehicles.view')
        ->name('vehicles.export.excel');

    Route::post('/vehicles/{vehicle}/images', [VehicleController::class, 'uploadImage'])
        ->middleware('permission:vehicles.view')
        ->name('vehicles.images.store');

    Route::patch('/vehicles/{vehicle}/images/{image}/featured', [VehicleController::class, 'setFeaturedImage'])
        ->middleware('permission:vehicles.view')
        ->name('vehicles.images.featured');

    Route::delete('/vehicles/{vehicle}/images/{image}', [VehicleController::class, 'deleteImage'])
        ->middleware('permission:vehicles.view')
        ->name('vehicles.images.destroy');

    Route::post('/vehicles/{vehicle}/documents', [VehicleController::class, 'uploadDocument'])
        ->middleware('permission:vehicles.view')
        ->name('vehicles.documents.store');

    Route::delete('/vehicles/{vehicle}/documents/{document}', [VehicleController::class, 'deleteDocument'])
        ->middleware('permission:vehicles.view')
        ->name('vehicles.documents.destroy');

    Route::get('/vehicles/{vehicle}/documents/download-zip', [VehicleController::class, 'downloadDocumentsZip'])
        ->middleware('permission:vehicles.view')
        ->name('vehicles.documents.zip');

    /*
    |--------------------------------------------------------------------------
    | DRIVER EXTRA
    |--------------------------------------------------------------------------
    */
    Route::post('/drivers/{driver}/documents', [DriverController::class, 'uploadDocument'])
        ->middleware('permission:drivers.view')
        ->name('drivers.documents.store');

    Route::delete('/drivers/{driver}/documents/{document}', [DriverController::class, 'deleteDocument'])
        ->middleware('permission:drivers.view')
        ->name('drivers.documents.destroy');

    /*
    |--------------------------------------------------------------------------
    | MAINTENANCES
    |--------------------------------------------------------------------------
    */
    Route::get('/maintenances/export/excel', [MaintenanceController::class, 'exportExcel'])
        ->middleware('permission:vehicles.view')
        ->name('maintenances.export.excel');

    Route::get('/maintenances/export/pdf', [MaintenanceController::class, 'exportPdf'])
        ->middleware('permission:vehicles.view')
        ->name('maintenances.export.pdf');

    Route::get('/maintenances/settings', [MaintenanceController::class, 'settings'])
        ->middleware('permission:vehicles.view')
        ->name('maintenances.settings');

    Route::post('/maintenances/settings', [MaintenanceController::class, 'saveSettings'])
        ->middleware('permission:vehicles.view')
        ->name('maintenances.settings.store');

    Route::resource('maintenances', MaintenanceController::class)
        ->parameters(['maintenances' => 'maintenance'])
        ->middleware('permission:vehicles.view');

    /*
    |--------------------------------------------------------------------------
    | FUEL STATIONS
    |--------------------------------------------------------------------------
    */
    Route::get('/fuel-stations', [FuelStationController::class, 'index'])
        ->middleware('permission:fuels.view')
        ->name('fuel-stations.index');

    Route::post('/fuel-stations', [FuelStationController::class, 'store'])
        ->middleware('permission:fuels.view')
        ->name('fuel-stations.store');

    /*
    |--------------------------------------------------------------------------
    | PAYMENTS
    |--------------------------------------------------------------------------
    */
    Route::post('/fuel-stations/payments', [FuelStationController::class, 'storePayment'])
        ->middleware('permission:fuels.view')
        ->name('fuel-stations.payments.store');

    Route::get('/fuel-stations/payments/{payment}', [FuelStationController::class, 'showPayment'])
        ->middleware('permission:fuels.view')
        ->name('fuel-stations.payments.show');

    Route::put('/fuel-stations/payments/{payment}', [FuelStationController::class, 'updatePayment'])
        ->middleware('permission:fuels.view')
        ->name('fuel-stations.payments.update');

    Route::delete('/fuel-stations/payments/{payment}', [FuelStationController::class, 'destroyPayment'])
        ->middleware('permission:fuels.view')
        ->name('fuel-stations.payments.destroy');

    /*
    |--------------------------------------------------------------------------
    | EXTRA
    |--------------------------------------------------------------------------
    */
    Route::post('/fuels/import', [FuelController::class, 'import'])
        ->middleware('permission:fuels.view')
        ->name('fuels.import');

    Route::post('/fuel-stations/payments/bulk', [FuelStationController::class, 'storeBulkPayment'])
        ->middleware('permission:fuels.view')
        ->name('fuel-stations.payments.bulk');

    Route::get('/fuel-stations/{station}/statement', [FuelStationController::class, 'statement'])
        ->middleware('permission:fuels.view')
        ->name('fuel-stations.statement');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])
        ->middleware('permission:reports.view')
        ->name('activity-logs.index');

    /*
    |--------------------------------------------------------------------------
    | TRAFFIC PENALTIES
    |--------------------------------------------------------------------------
    */
    Route::get('/traffic-penalties/export/excel', [TrafficPenaltyController::class, 'exportExcel'])
        ->middleware('permission:vehicles.view')
        ->name('traffic-penalties.export.excel');

    Route::get('/traffic-penalties/export/pdf', [TrafficPenaltyController::class, 'exportPdf'])
        ->middleware('permission:vehicles.view')
        ->name('traffic-penalties.export.pdf');

    Route::post('/traffic-penalties/{trafficPenalty}/quick-pay', [TrafficPenaltyController::class, 'quickPay'])
        ->middleware('permission:vehicles.view')
        ->name('traffic-penalties.quick-pay');

    Route::resource('traffic-penalties', TrafficPenaltyController::class)
        ->except(['show'])
        ->middleware('permission:vehicles.view');

    /*
    |--------------------------------------------------------------------------
    | RESOURCES
    |--------------------------------------------------------------------------
    */
    Route::resource('vehicles', VehicleController::class)
        ->middleware('permission:vehicles.view');

    Route::resource('drivers', DriverController::class)
        ->middleware('permission:drivers.view');
    Route::post('drivers/{driver}/leave-work', [DriverController::class, 'leaveWork'])->name('drivers.leave-work');
    Route::post('drivers/{driver}/change-vehicle', [DriverController::class, 'changeVehicle'])->name('drivers.change-vehicle');

    Route::resource('customers', CustomerController::class)
        ->middleware('permission:customers.view');

    Route::post('/customers/{customer}/contracts', [CustomerContractController::class, 'store'])
        ->middleware('permission:customers.view')
        ->name('customers.contracts.store');

    Route::delete('/customers/{customer}/contracts/{contract}', [CustomerContractController::class, 'destroy'])
        ->middleware('permission:customers.view')
        ->name('customers.contracts.destroy');

    Route::post('/customers/{customer}/portal-users', [CustomerPortalUserController::class, 'store'])
        ->middleware('permission:customers.view')
        ->name('customers.portal-users.store');

    Route::put('/customers/{customer}/portal-users/{portalUser}', [CustomerPortalUserController::class, 'update'])
        ->middleware('permission:customers.view')
        ->name('customers.portal-users.update');

    Route::patch('/customers/{customer}/portal-users/{portalUser}/toggle-status', [CustomerPortalUserController::class, 'toggleStatus'])
        ->middleware('permission:customers.view')
        ->name('customers.portal-users.toggle-status');

    Route::delete('/customers/{customer}/portal-users/{portalUser}', [CustomerPortalUserController::class, 'destroy'])
        ->middleware('permission:customers.view')
        ->name('customers.portal-users.destroy');

    Route::post('/customers/{customer}/service-routes', [CustomerServiceRouteController::class, 'store'])
        ->middleware('permission:customers.view')
        ->name('customers.service-routes.store');

    Route::put('/customers/{customer}/service-routes/{serviceRoute}', [CustomerServiceRouteController::class, 'update'])
        ->middleware('permission:customers.view')
        ->name('customers.service-routes.update');

    Route::patch('/customers/{customer}/service-routes/{serviceRoute}/toggle-status', [CustomerServiceRouteController::class, 'toggleStatus'])
        ->middleware('permission:customers.view')
        ->name('customers.service-routes.toggle-status');

    Route::delete('/customers/{customer}/service-routes/{serviceRoute}', [CustomerServiceRouteController::class, 'destroy'])
        ->middleware('permission:customers.view')
        ->name('customers.service-routes.destroy');

    Route::resource('service-routes', ServiceRouteController::class)
        ->middleware('permission:service_routes.view');

    Route::resource('route-stops', RouteStopController::class)
        ->middleware('permission:route_stops.view');

    /*
    |--------------------------------------------------------------------------
    | TRIPS
    |--------------------------------------------------------------------------
    */
    Route::post('/trips/upsert-cell', [TripController::class, 'upsertCell'])
        ->middleware('permission:trips.view')
        ->name('trips.upsert-cell');

    Route::resource('trips', TripController::class)
        ->middleware('permission:trips.view');

    Route::resource('payrolls', PayrollController::class)
        ->middleware('permission:payrolls.view');
    Route::post('/payrolls/bulk-store', [PayrollController::class, 'bulkStore'])->name('payrolls.bulk-store');
    Route::post('/payrolls/update-single', [PayrollController::class, 'updateSingle'])->name('payrolls.update-single');
    Route::get('/payrolls/bulk-report', [PayrollController::class, 'bulkReport'])->name('payrolls.bulk-report');
    Route::get('/payrolls/report/{driver}/{period}', [PayrollController::class, 'showReport'])->name('payrolls.report');

    Route::resource('documents', DocumentController::class)
        ->middleware('permission:documents.view');

    Route::resource('fuels', FuelController::class)
        ->middleware('permission:fuels.view');

    Route::resource('company-users', CompanyUserController::class)
        ->parameters(['company-users' => 'companyUser'])
        ->middleware('permission:company_users.view');

    /*
    |--------------------------------------------------------------------------
    | REPORTS
    |--------------------------------------------------------------------------
    */
    Route::get('/reports', [ReportController::class, 'index'])
        ->middleware('permission:reports.view')
        ->name('reports.index');

    Route::get('/reports/trips-csv', [ReportController::class, 'exportTripsCsv'])
        ->middleware('permission:reports.view')
        ->name('reports.trips.csv');

    Route::get('/reports/payrolls-csv', [ReportController::class, 'exportPayrollsCsv'])
        ->middleware('permission:reports.view')
        ->name('reports.payrolls.csv');

    Route::get('/reports/fuels-csv', [ReportController::class, 'exportFuelsCsv'])
        ->middleware('permission:reports.view')
        ->name('reports.fuels.csv');

    Route::get('/reports/documents-csv', [ReportController::class, 'exportDocumentsCsv'])
        ->middleware('permission:reports.view')
        ->name('reports.documents.csv');

    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */
    Route::get('/company-settings', [CompanySettingsController::class, 'edit'])
        ->name('company-settings.edit');

    Route::put('/company-settings', [CompanySettingsController::class, 'update'])
        ->name('company-settings.update');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| LİSANS SÜRESİ DOLDU SAYFASI
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->get('/license-expired', function () {
    return view('license-expired');
})->name('license.expired');

/*
|--------------------------------------------------------------------------
| SUPER ADMIN PANELİ
|--------------------------------------------------------------------------
*/
Route::prefix('super-admin')
    ->middleware(['auth', 'super_admin'])
    ->name('super-admin.')
    ->group(function () {

        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('companies', SuperAdminCompanyController::class);

        Route::post('/companies/{company}/users', [SuperAdminCompanyController::class, 'storeUser'])
            ->name('companies.users.store');

        Route::put('/companies/{company}/modules', [SuperAdminCompanyController::class, 'updateModules'])
            ->name('companies.modules.update');
    });