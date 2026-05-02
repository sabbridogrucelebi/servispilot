<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/login', 'POST', [
    'email' => 'admin@admin.com', 
    'password' => 'password',
    'device_name' => 'mobile'
]);
$request->headers->set('Accept', 'application/json');

$response = $kernel->handle($request);
echo "STATUS: " . $response->getStatusCode() . "\n";
echo "BODY: " . $response->getContent() . "\n";
