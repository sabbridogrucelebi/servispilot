<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Auth::loginUsingId(2);

$view = view('dashboard', [
    'vehicleCount' => 0, 'driverCount' => 0, 'customerCount' => 0, 
    'todayTrips' => 0, 'monthlyIncome' => 0, 'monthlyFuel' => 0, 
    'monthlyPenalty' => 0, 'activeMaintenances' => 0, 'waitingMaintenances' => 0, 
    'expiredDocumentsCount' => 0, 'documentsExpiringIn7DaysCount' => 0, 
    'documentsExpiringIn30DaysCount' => 0, 'upcomingDocuments' => collect([]), 
    'totalFuel' => 0, 'totalSalary' => 0, 'netProfit' => 0, 
    'recentTrips' => collect([]), 'recentActivity' => collect([])
])->render();

file_put_contents('rendered_dashboard.html', $view);
echo "DONE";
