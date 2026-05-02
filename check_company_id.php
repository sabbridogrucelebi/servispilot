<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = DB::select('SHOW TABLES');
$missing = [];
foreach($tables as $t) {
    $table = array_values((array)$t)[0];
    $columns = Schema::getColumnListing($table);
    if(!in_array('company_id', $columns) && !in_array($table, ['migrations', 'password_reset_tokens', 'failed_jobs', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches'])) {
        $missing[] = $table;
    }
}
echo implode("\n", $missing);
