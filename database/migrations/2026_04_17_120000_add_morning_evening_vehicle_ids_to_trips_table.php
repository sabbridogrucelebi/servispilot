<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'morning_vehicle_id')) {
                $table->foreignId('morning_vehicle_id')
                    ->nullable()
                    ->after('vehicle_id')
                    ->constrained('vehicles')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('trips', 'evening_vehicle_id')) {
                $table->foreignId('evening_vehicle_id')
                    ->nullable()
                    ->after('morning_vehicle_id')
                    ->constrained('vehicles')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            if (Schema::hasColumn('trips', 'morning_vehicle_id')) {
                $table->dropConstrainedForeignId('morning_vehicle_id');
            }

            if (Schema::hasColumn('trips', 'evening_vehicle_id')) {
                $table->dropConstrainedForeignId('evening_vehicle_id');
            }
        });
    }
};