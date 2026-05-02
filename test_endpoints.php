<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

$company = Company::firstOrCreate(['id' => 9993], ['name' => 'API Test Co', 'owner_id' => 1]);

$admin = User::firstOrCreate(['email' => 'admin_api@test.com'], [
    'name' => 'Admin User', 'password' => bcrypt('password'), 'company_id' => $company->id, 'role' => 'company_admin'
]);

$viewer = User::firstOrCreate(['email' => 'viewer_api@test.com'], [
    'name' => 'Viewer User', 'password' => bcrypt('password'), 'company_id' => $company->id, 'role' => 'viewer'
]);
$viewer->permissions_updated_at = now();
$viewer->save();
$viewer->update(['permissions_updated_at' => now()]);

$adminToken = $admin->createToken('admin')->plainTextToken;
$viewerToken = $viewer->createToken('viewer')->plainTextToken;

$vehicle = \App\Models\Fleet\Vehicle::firstOrCreate(
    ['plate' => '34TEST34'],
    ['company_id' => $company->id, 'type' => 'minibus', 'brand' => 'Test', 'model' => 'Test']
);

function sendReq($method, $url, $token) {
    $ch = curl_init('http://127.0.0.1:8081' . $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Accept: application/json']);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    curl_close($ch);
    return ['status' => $status, 'body' => $body, 'header' => $header];
}

$r1 = sendReq('DELETE', '/api/vehicles/' . $vehicle->id, $viewerToken);
echo "Viewer DELETE /api/vehicles/{$vehicle->id} Status: {$r1['status']}\n";

$r2 = sendReq('DELETE', '/api/vehicles/' . $vehicle->id, $adminToken);
echo "Admin DELETE /api/vehicles/{$vehicle->id} Status: {$r2['status']}\n";

$r3 = sendReq('GET', '/api/me', $viewerToken);
echo "Viewer GET /api/me Status: {$r3['status']}\n";
echo "Viewer GET /api/me Body: {$r3['body']}\n";
if (preg_match('/X-Permissions-Updated-At: (.+)/i', $r3['header'], $matches)) {
    echo "Permissions header: " . trim($matches[1]) . "\n";
} else {
    echo "Permissions header: Not Found\n";
}

DB::table('users')->whereIn('email', ['admin_api@test.com', 'viewer_api@test.com'])->delete();
$company->delete();
