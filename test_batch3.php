<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;
use App\Models\Company;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\Driver;
use App\Models\Customer;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

echo "=================================================\n";
echo "BATCH 3 SMOKE TEST (ServiceRoutes, RouteStops, FuelStations)\n";
echo "=================================================\n\n";

$companyA = Company::first();
if (!$companyA) {
    $companyA = Company::create(['name' => 'Test Company A']);
}
$companyId = $companyA->id;

$adminA = User::firstOrCreate(['email' => 'admin_a_b3@test.com'], [
    'name' => 'Admin A B3', 'password' => Hash::make('password'), 'company_id' => $companyId, 'role' => 'company_admin'
]);
$viewerA = User::firstOrCreate(['email' => 'viewer_a_b3@test.com'], [
    'name' => 'Viewer A B3', 'password' => Hash::make('password'), 'company_id' => $companyId, 'role' => 'viewer'
]);

$adminA->permissions()->sync(Permission::whereIn('key', [
    'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
    'finance.view', 'finance.create', 'finance.edit', 'finance.delete',
])->pluck('id')->toArray());

$viewerA->permissions()->sync(Permission::whereIn('key', [
    'customers.view', 'finance.view'
])->pluck('id')->toArray());

$vehicle = Vehicle::where('plate', '34TSTB3')->first();
if (!$vehicle) {
    $vehicle = Vehicle::create(['company_id' => $companyId, 'plate' => '34TSTB3', 'brand' => 'TestBrand', 'model' => 'TestModel', 'vehicle_type' => 'minibus', 'capacity' => 16, 'status' => 'active', 'ownership_status' => 'owned']);
}

$driver = Driver::where('tc_no', '11111111113')->first();
if (!$driver) {
    $driver = Driver::create(['company_id' => $companyId, 'tc_no' => '11111111113', 'full_name' => 'Test Driver B3', 'phone' => '5554443333', 'is_active' => true]);
}

$customer = Customer::where('email', 'customer_b3@test.com')->first();
if (!$customer) {
    $customer = Customer::create(['company_id' => $companyId, 'email' => 'customer_b3@test.com', 'company_name' => 'Test Customer B3', 'company_title' => 'Test Customer B3', 'customer_type' => 'company']);
}

function testEndpoint($user, $method, $url, $data = []) {
    \Laravel\Sanctum\Sanctum::actingAs($user, ['*']);
    $request = \Illuminate\Http\Request::create($url, $method, $data);
    $request->headers->set('Accept', 'application/json');
    $response = app()->handle($request);
    return ['status' => $response->getStatusCode(), 'content' => json_decode($response->getContent(), true)];
}

// Ensure a route exists for RouteStops test
$route = \App\Models\ServiceRoute::firstOrCreate([
    'company_id' => $companyId,
    'customer_id' => $customer->id,
    'route_name' => 'Test Route Base'
], [
    'vehicle_type' => 'minibus'
]);

$modules = [
    'ServiceRoutes' => [
        'base_url' => '/api/v1/service-routes',
        'valid_payload' => [
            'customer_id' => $customer->id,
            'route_name' => 'Test Route 1',
            'start_location' => 'A',
            'end_location' => 'B',
            'price' => 500
        ]
    ],
    'RouteStops' => [
        'base_url' => '/api/v1/route-stops',
        'valid_payload' => [
            'service_route_id' => $route->id,
            'stop_name' => 'Stop 1',
            'stop_order' => 1,
            'stop_time' => '08:00',
            'is_active' => true
        ]
    ],
    'FuelStations' => [
        'base_url' => '/api/v1/fuel-stations',
        'valid_payload' => [
            'name' => 'Test Station',
            'discount_type' => 'percentage',
            'discount_value' => 5
        ]
    ]
];

$results = [];

foreach ($modules as $modName => $modData) {
    echo "\n--- $modName ---\n";
    $baseUrl = $modData['base_url'];
    $payload = $modData['valid_payload'];

    // Viewer
    $res = testEndpoint($viewerA, 'POST', $baseUrl, $payload);
    $status = $res['status'];
    echo $status == 403 ? "[PASS] POST $baseUrl (Viewer) -> 403\n" : "[FAIL] POST $baseUrl (Viewer) -> $status\n";

    // Admin Empty
    $res = testEndpoint($adminA, 'POST', $baseUrl, []);
    $status = $res['status'];
    echo $status == 422 ? "[PASS] POST $baseUrl (Admin, Empty) -> 422\n" : "[FAIL] POST $baseUrl (Admin, Empty) -> $status\n";

    // Admin Create
    $res = testEndpoint($adminA, 'POST', $baseUrl, $payload);
    $status = $res['status'];
    $id = $res['content']['data']['id'] ?? null;
    if ($status == 201) {
        echo "[PASS] POST $baseUrl (Admin, Valid) -> 201\n";
    } else {
        echo "[FAIL] POST $baseUrl (Admin, Valid) -> $status\n";
        if ($status == 422) echo "       Errors: " . json_encode($res['content']['errors'] ?? []) . "\n";
        if ($status == 500) echo "       Error: " . substr(json_encode($res['content']), 0, 200) . "\n";
        if ($status == 404) echo "       Error: " . json_encode($res['content']) . "\n";
    }

    if ($id) {
        // Viewer Update
        $res = testEndpoint($viewerA, 'PUT', "$baseUrl/$id", $payload);
        $status = $res['status'];
        if ($status == 405) {
            $res = testEndpoint($viewerA, 'POST', "$baseUrl/$id", array_merge($payload, ['_method' => 'PUT']));
            $status = $res['status'];
        }
        echo $status == 403 ? "[PASS] PUT $baseUrl/$id (Viewer) -> 403\n" : "[FAIL] PUT $baseUrl/$id (Viewer) -> $status\n";

        // Admin Update
        $res = testEndpoint($adminA, 'PUT', "$baseUrl/$id", $payload);
        $status = $res['status'];
        if ($status == 405) {
            $res = testEndpoint($adminA, 'POST', "$baseUrl/$id", array_merge($payload, ['_method' => 'PUT']));
            $status = $res['status'];
        }
        echo $status == 200 ? "[PASS] PUT $baseUrl/$id (Admin, Valid) -> 200\n" : "[FAIL] PUT $baseUrl/$id (Admin, Valid) -> $status\n";

        // Viewer Delete
        $res = testEndpoint($viewerA, 'DELETE', "$baseUrl/$id");
        $status = $res['status'];
        echo $status == 403 ? "[PASS] DELETE $baseUrl/$id (Viewer) -> 403\n" : "[FAIL] DELETE $baseUrl/$id (Viewer) -> $status\n";

        // Admin Delete
        $res = testEndpoint($adminA, 'DELETE', "$baseUrl/$id");
        $status = $res['status'];
        echo $status == 200 ? "[PASS] DELETE $baseUrl/$id (Admin) -> 200\n" : "[FAIL] DELETE $baseUrl/$id (Admin) -> $status\n";
    }
}

echo "\n=================================================\n";
echo "BATCH 3 TESTS COMPLETED.\n";
echo "=================================================\n";
