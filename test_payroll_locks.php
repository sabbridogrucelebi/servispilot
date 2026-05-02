<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\PayrollLock;
use Illuminate\Support\Facades\DB;

DB::table('companies')->insert([
    ['id' => 9991, 'name' => 'Company A'],
    ['id' => 9992, 'name' => 'Company B']
]);

DB::table('users')->insert([
    ['name' => 'Test User 1', 'email' => 't1@example.com', 'password' => bcrypt('password'), 'company_id' => 9991],
    ['name' => 'Test User 2', 'email' => 't2@example.com', 'password' => bcrypt('password'), 'company_id' => 9992]
]);

$u1 = User::where('email', 't1@example.com')->first();
$u2 = User::where('email', 't2@example.com')->first();

try {
    auth()->login($u1);
    PayrollLock::create(['period' => '2026-06', 'is_locked' => true]);
} catch (\Exception $e) { echo "Error 1: " . $e->getMessage() . "\n"; }

try {
    auth()->login($u2);
    PayrollLock::create(['period' => '2026-06', 'is_locked' => true]);
} catch (\Exception $e) { echo "Error 2: " . $e->getMessage() . "\n"; }

$count1 = PayrollLock::withoutGlobalScopes()->where('company_id', 9991)->count();
$count2 = PayrollLock::withoutGlobalScopes()->where('company_id', 9992)->count();

echo "Count 1: $count1\nCount 2: $count2\n";

DB::table('users')->whereIn('email', ['t1@example.com', 't2@example.com'])->delete();
DB::table('payroll_locks')->whereIn('company_id', [9991, 9992])->delete();
DB::table('companies')->whereIn('id', [9991, 9992])->delete();
