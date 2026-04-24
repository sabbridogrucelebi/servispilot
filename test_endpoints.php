<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$adminUser = App\Models\User::find(2); // company_admin
$viewerUser = App\Models\User::find(4); // viewer, no vehicles.view permission

function testEndpoint($user, $uri) {
    Laravel\Sanctum\Sanctum::actingAs($user, ['*']);
    $request = Illuminate\Http\Request::create($uri, 'GET');
    $request->headers->set('Accept', 'application/json');
    $response = app()->handle($request);
    return [
        'status' => $response->status(),
        'content' => json_decode($response->getContent(), true)
    ];
}

$endpoints = [
    '/api/v1/dashboard',
    '/api/v1/vehicles',
    '/api/v1/vehicles/15', 
    '/api/v1/personnel',
    '/api/v1/customers',
    '/api/v1/trips',
    '/api/v1/activity-logs',
    '/api/v1/finance/summary',
    '/api/v1/payrolls',
];

$results = [];
foreach ($endpoints as $ep) {
    $res = testEndpoint($adminUser, $ep);
    $results['admin'][$ep] = [
        'status' => $res['status'],
        'success' => $res['content']['success'] ?? false,
        'has_data' => !empty($res['content']['data'])
    ];
}

// Test Unauthorized
$results['unauthorized']['/api/v1/vehicles'] = testEndpoint($viewerUser, '/api/v1/vehicles')['status'];

// Example Response for /api/v1/vehicles
$vehicleResp = testEndpoint($adminUser, '/api/v1/vehicles');
$results['example_vehicle'] = $vehicleResp['content']['data']['vehicles'][0] ?? null;
$results['kpi'] = $vehicleResp['content']['data']['kpi'] ?? null;

echo json_encode($results, JSON_PRETTY_PRINT);
