<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = \App\Models\User::whereNotNull('company_id')->first();
auth()->login($u);

echo "User Company ID: " . $u->company_id . "\n";

$lock = \App\Models\PayrollLock::firstOrNew(['period' => '2026-05']);
$lock->is_locked = 1;
$lock->save();

print_r(\App\Models\PayrollLock::all()->toArray());
