<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

$companyA = Company::first();
$companyB = Company::where('id', '!=', $companyA->id)->first();
if (!$companyB) { $companyB = new Company(); $companyB->name = 'Test Company B'; $companyB->save(); }

$adminA = User::firstOrCreate(['email' => 'admin_a@test.com'], [
    'name' => 'Admin A', 'password' => Hash::make('password'), 'company_id' => $companyA->id, 'role' => 'company_admin'
]);
$viewerA = User::firstOrCreate(['email' => 'viewer_a@test.com'], [
    'name' => 'Viewer A', 'password' => Hash::make('password'), 'company_id' => $companyA->id, 'role' => 'viewer'
]);
$adminB = User::firstOrCreate(['email' => 'admin_b@test.com'], [
    'name' => 'Admin B', 'password' => Hash::make('password'), 'company_id' => $companyB->id, 'role' => 'company_admin'
]);

// Ensure viewer has no create/edit/delete
$viewerA->permissions()->sync(Permission::whereIn('key', ['customers.view'])->pluck('id')->toArray());
// Ensure admin has full access
$adminA->permissions()->sync(Permission::whereIn('key', ['customers.view', 'customers.create', 'customers.edit', 'customers.delete'])->pluck('id')->toArray());
$adminB->permissions()->sync(Permission::whereIn('key', ['customers.view', 'customers.create', 'customers.edit', 'customers.delete'])->pluck('id')->toArray());

function testEndpoint($user, $method, $url, $data = []) {
    \Laravel\Sanctum\Sanctum::actingAs($user, ['*']);
    $request = \Illuminate\Http\Request::create($url, $method, $data);
    $request->headers->set('Accept', 'application/json');
    $response = app()->handle($request);
    return ['status' => $response->getStatusCode(), 'content' => json_decode($response->getContent(), true)];
}

echo "Running tests...\n";

// B. API-seviye doğrulama
$r = testEndpoint($viewerA, 'POST', '/api/v1/customers', ['company_name' => 'X', 'customer_type' => 'company', 'vat_rate' => 20]);
echo "Viewer POST /api/v1/customers: " . ($r['status'] == 403 ? "PASS" : "FAIL ({$r['status']})") . "\n";

$r = testEndpoint($adminA, 'POST', '/api/v1/customers', []);
echo "Admin POST /api/v1/customers (BOŞ payload): " . ($r['status'] == 422 ? "PASS" : "FAIL ({$r['status']})") . "\n";

$r = testEndpoint($adminA, 'POST', '/api/v1/customers', [
    'company_name' => 'Test Customer A', 'customer_type' => 'company', 'vat_rate' => 20
]);
echo "Admin POST /api/v1/customers (geçerli): " . ($r['status'] == 201 ? "PASS" : "FAIL ({$r['status']})") . "\n";

$customerId = $r['content']['data']['id'] ?? null;
if (!$customerId) {
    echo "FAILED TO GET CUSTOMER ID!\n";
    exit;
}

$r = testEndpoint($viewerA, 'PUT', "/api/v1/customers/$customerId", ['company_name' => 'Updated']);
echo "Viewer PUT /api/v1/customers/{id}: " . ($r['status'] == 403 ? "PASS" : "FAIL ({$r['status']})") . "\n";

$r = testEndpoint($adminA, 'PUT', "/api/v1/customers/$customerId", [
    'company_name' => 'Updated Customer', 'customer_type' => 'company', 'vat_rate' => 20, 'is_active' => true
]);
echo "Admin PUT /api/v1/customers/{id} (geçerli): " . ($r['status'] == 200 ? "PASS" : "FAIL ({$r['status']})") . "\n";

$r = testEndpoint($viewerA, 'DELETE', "/api/v1/customers/$customerId");
echo "Viewer DELETE /api/v1/customers/{id}: " . ($r['status'] == 403 ? "PASS" : "FAIL ({$r['status']})") . "\n";

// D. Tenant izolasyon doğrulama
$r = testEndpoint($adminB, 'GET', "/api/v1/customers/$customerId");
echo "Company B Admin GET /api/v1/customers/{id}: " . ($r['status'] == 404 ? "PASS" : "FAIL ({$r['status']})") . "\n";

$r = testEndpoint($adminB, 'DELETE', "/api/v1/customers/$customerId");
echo "Company B Admin DELETE /api/v1/customers/{id}: " . (in_array($r['status'], [404, 403]) ? "PASS" : "FAIL ({$r['status']})") . "\n";

$r = testEndpoint($adminA, 'DELETE', "/api/v1/customers/$customerId");
echo "Admin A DELETE /api/v1/customers/{id}: " . ($r['status'] == 200 ? "PASS" : "FAIL ({$r['status']})") . "\n";

// Cleanup
$adminA->delete();
$viewerA->delete();
$adminB->delete();
Customer::where('company_name', 'like', '%Test Customer%')->delete();
Company::whereIn('id', [9991, 9992])->delete();
