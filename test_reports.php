<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/v1/vehicles/77/reports?reports_month=2026-04', 'GET');
$user = App\Models\User::first();
$request->setUserResolver(function () use ($user) {
    return $user;
});

$response = $kernel->handle($request);
echo $response->getContent();
