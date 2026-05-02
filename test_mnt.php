<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'aa@gmail.com')->first();
$token = $user->createToken('test')->plainTextToken;

$client = new \GuzzleHttp\Client();
try {
    $res = $client->get('http://127.0.0.1:8000/api/v1/maintenances', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ]
    ]);
    echo $res->getBody();
} catch (\Exception $e) {
    if ($e->hasResponse()) {
        echo $e->getResponse()->getBody();
    } else {
        echo $e->getMessage();
    }
}
