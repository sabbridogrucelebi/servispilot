<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;
use App\Models\Company;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

$companyA = Company::first();
$companyB = Company::where('id', '!=', $companyA->id)->first();

$adminA = User::firstOrCreate(['email' => 'admin_a_batch1@test.com'], [
    'name' => 'Admin A', 'password' => Hash::make('password'), 'company_id' => $companyA->id, 'role' => 'company_admin'
]);
$viewerA = User::firstOrCreate(['email' => 'viewer_a_batch1@test.com'], [
    'name' => 'Viewer A', 'password' => Hash::make('password'), 'company_id' => $companyA->id, 'role' => 'viewer'
]);
$adminB = User::firstOrCreate(['email' => 'admin_b_batch1@test.com'], [
    'name' => 'Admin B', 'password' => Hash::make('password'), 'company_id' => $companyB->id, 'role' => 'company_admin'
]);

$viewerA->permissions()->sync(Permission::whereIn('key', ['trips.view', 'drivers.view', 'fuels.view', 'maintenances.view'])->pluck('id')->toArray());
$adminA->permissions()->sync(Permission::whereIn('key', [
    'trips.view', 'trips.create', 'trips.edit', 'trips.delete',
    'drivers.view', 'drivers.create', 'drivers.edit', 'drivers.delete',
    'fuels.view', 'fuels.create', 'fuels.edit', 'fuels.delete',
    'maintenances.view', 'maintenances.create', 'maintenances.edit', 'maintenances.delete'
])->pluck('id')->toArray());
$adminB->permissions()->sync(Permission::whereIn('key', [
    'trips.view', 'trips.create', 'trips.edit', 'trips.delete',
    'drivers.view', 'drivers.create', 'drivers.edit', 'drivers.delete',
    'fuels.view', 'fuels.create', 'fuels.edit', 'fuels.delete',
    'maintenances.view', 'maintenances.create', 'maintenances.edit', 'maintenances.delete'
])->pluck('id')->toArray());

function testEndpoint($user, $method, $url, $data = []) {
    \Laravel\Sanctum\Sanctum::actingAs($user, ['*']);
    $request = \Illuminate\Http\Request::create($url, $method, $data);
    $request->headers->set('Accept', 'application/json');
    $response = app()->handle($request);
    return ['status' => $response->getStatusCode(), 'content' => json_decode($response->getContent(), true)];
}

echo "Running tests...\n";

// Pre-reqs
$route = \App\Models\CustomerServiceRoute::where('company_id', $companyA->id)->first();
if (!$route) {
    $cust = \App\Models\Customer::where('company_id', $companyA->id)->first();
    if (!$cust) {
        $cust = \App\Models\Customer::create(['company_id' => $companyA->id, 'company_name' => 'Test Company Name', 'customer_type' => 'corporate']);
    }
    $route = \App\Models\CustomerServiceRoute::create(['company_id' => $companyA->id, 'customer_id' => $cust->id ?? 1, 'route_name' => 'Test Route', 'vehicle_type' => 'minibus', 'is_active' => true]);
}
$vehicle = \App\Models\Fleet\Vehicle::where('company_id', $companyA->id)->first();
if (!$vehicle) {
    $vehicle = \App\Models\Fleet\Vehicle::create(['company_id' => $companyA->id, 'plate' => '34 TEST 123', 'is_active' => true, 'ownership_status' => 'owned', 'vehicle_type' => 'minibus']);
}

$modules = [
    'Trips' => [
        'base_url' => '/api/v1/trips',
        'valid_payload' => [
            'service_route_id' => $route->id,
            'trip_date' => '2026-05-01',
            'trip_status' => 'planned'
        ]
    ],
    'Personnel' => [
        'base_url' => '/api/v1/personnel',
        'valid_payload' => [
            'full_name' => 'Test Driver',
            'tc_no' => '12345678901'
        ]
    ],
    'Fuels' => [
        'base_url' => '/api/v1/fuels',
        'valid_payload' => [
            'vehicle_id' => $vehicle->id,
            'date' => '2026-05-01',
            'fuel_type' => 'Dizel',
            'liters' => 50,
            'price_per_liter' => 45
        ]
    ],
    'Maintenances' => [
        'base_url' => '/api/v1/maintenances',
        'valid_payload' => [
            'vehicle_id' => $vehicle->id,
            'service_date' => '2026-05-01',
            'maintenance_type' => 'Periyodik',
            'title' => 'Test Maintenance'
        ]
    ]
];

$allPassed = true;

foreach ($modules as $modName => $config) {
    echo "\n=== Testing $modName ===\n";
    $url = $config['base_url'];
    
    // Viewer POST -> 403
    $res = testEndpoint($viewerA, 'POST', $url, $config['valid_payload']);
    if ($res['status'] !== 403) { echo "Viewer POST $modName: FAIL ({$res['status']})\n"; $allPassed = false; }
    else echo "Viewer POST $modName: PASS\n";
    
    // Admin POST empty -> 422
    $res = testEndpoint($adminA, 'POST', $url, []);
    if ($res['status'] !== 422) { echo "Admin POST empty $modName: FAIL ({$res['status']})\n"; $allPassed = false; }
    else echo "Admin POST empty $modName: PASS\n";
    
    // Admin POST valid -> 201
    $res = testEndpoint($adminA, 'POST', $url, $config['valid_payload']);
    if ($res['status'] !== 201 && $res['status'] !== 200) { 
        echo "Admin POST valid $modName: FAIL ({$res['status']})\n"; 
        if (isset($res['content']['message'])) {
            echo "Error: " . $res['content']['message'] . "\n";
        }
        $allPassed = false; 
        continue; 
    }
    else echo "Admin POST valid $modName: PASS\n";
    
    $id = $res['content']['data']['id'];
    
    // Viewer PUT -> 403
    $res = testEndpoint($viewerA, 'PUT', "$url/$id", $config['valid_payload']);
    if ($res['status'] !== 403) { echo "Viewer PUT $modName: FAIL ({$res['status']})\n"; $allPassed = false; }
    else echo "Viewer PUT $modName: PASS\n";
    
    // Admin PUT valid -> 200
    $res = testEndpoint($adminA, 'PUT', "$url/$id", $config['valid_payload']);
    if ($res['status'] !== 200) { echo "Admin PUT valid $modName: FAIL ({$res['status']})\n"; $allPassed = false; }
    else echo "Admin PUT valid $modName: PASS\n";
    
    // Viewer DELETE -> 403
    $res = testEndpoint($viewerA, 'DELETE', "$url/$id");
    if ($res['status'] !== 403) { echo "Viewer DELETE $modName: FAIL ({$res['status']})\n"; $allPassed = false; }
    else echo "Viewer DELETE $modName: PASS\n";
    
    // Tenant B GET -> 404
    $res = testEndpoint($adminB, 'GET', "$url/$id");
    if ($res['status'] !== 404 && $res['status'] !== 403) { echo "Tenant Isolation GET $modName: FAIL ({$res['status']})\n"; $allPassed = false; }
    else echo "Tenant Isolation GET $modName: PASS\n";
    
    // Admin A DELETE -> 200
    $res = testEndpoint($adminA, 'DELETE', "$url/$id");
    if ($res['status'] !== 200 && $res['status'] !== 204) { echo "Admin DELETE $modName: FAIL ({$res['status']})\n"; $allPassed = false; }
    else echo "Admin DELETE $modName: PASS\n";
}

if (!$allPassed) {
    exit(1);
}
