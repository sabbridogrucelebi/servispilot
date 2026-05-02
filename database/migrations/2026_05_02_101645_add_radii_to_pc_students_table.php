<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pc_students', function (Blueprint $table) {
            $table->integer('pickup_radius')->nullable()->default(1000)->after('pickup_location');
            $table->integer('dropoff_radius')->nullable()->default(1000)->after('dropoff_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pc_students', function (Blueprint $table) {
            $table->dropColumn(['pickup_radius', 'dropoff_radius']);
        });
    }
};
