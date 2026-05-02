<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Route;

$admin = User::where('role', 'company_admin')->whereNotNull('company_id')->first();
auth()->login($admin);

$request = \Illuminate\Http\Request::create('/company-users', 'POST', [
    'name' => 'Test Observer',
    'email' => 'test_obs_' . rand() . '@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'role' => 'viewer',
    'permissions' => []
]);
$request->setLaravelSession(app('session')->driver());

// dispatch without middlewares to bypass CSRF, but we DO want to test the middleware...
// Let's just instantiate the controller and call the method directly.
$controller = app(\App\Http\Controllers\CompanyUserController::class);
try {
    $response = $controller->store($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    if ($response->isRedirect()) {
        $session = $response->getSession();
        if ($session && $session->has('errors')) {
            echo "Errors: ";
            print_r($session->get('errors')->all());
        }
        if ($session && $session->has('error')) {
            echo "Session Error: " . $session->get('error') . "\n";
        }
    }
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "Validation Exception!\n";
    print_r($e->errors());
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
