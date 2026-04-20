<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuel_station_payments', function (Blueprint $table) {
            $table->string('payment_method')->default('nakit')->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('fuel_station_payments', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};