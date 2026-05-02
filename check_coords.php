<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$students = App\Models\PilotCell\PcStudent::select('name', \Illuminate\Support\Facades\DB::raw('ST_Y(pickup_location) as morning_lat, ST_X(pickup_location) as morning_lng'), \Illuminate\Support\Facades\DB::raw('ST_Y(dropoff_location) as evening_lat, ST_X(dropoff_location) as evening_lng'))->get();
foreach($students as $s) {
    echo $s->name . ' - Morning: ' . $s->morning_lat . ',' . $s->morning_lng . ' - Evening: ' . $s->evening_lat . ',' . $s->evening_lng . PHP_EOL;
}
