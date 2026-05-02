<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('user_type', 'customer_portal')->first();
if(!$user) {
    echo "No customer portal user found.\n";
    exit;
}

$student = \App\Models\PilotCell\PcStudent::with('debts')->where('parent_user_id', $user->id)->orWhere('parent2_user_id', $user->id)->first();
print_r($student->toArray());

$json = json_encode($student->toArray());
if(json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Encode Error: " . json_last_error_msg() . "\n";
}
