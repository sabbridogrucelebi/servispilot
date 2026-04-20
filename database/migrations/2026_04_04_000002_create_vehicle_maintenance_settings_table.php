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
        Schema::create('vehicle_maintenance_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();

            $table->unsignedInteger('oil_change_interval_km')->nullable();
            $table->unsignedInteger('under_lubrication_interval_km')->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'vehicle_id'], 'vehicle_maintenance_settings_company_vehicle_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance_settings');
    }
};