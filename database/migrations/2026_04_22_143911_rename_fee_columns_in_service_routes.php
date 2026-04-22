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
        Schema::table('service_routes', function (Blueprint $table) {
            $table->renameColumn('pickup_vehicle_id', 'morning_vehicle_id');
            $table->renameColumn('dropoff_vehicle_id', 'evening_vehicle_id');
            $table->renameColumn('saturday_fee', 'saturday_pricing');
            $table->renameColumn('sunday_fee', 'sunday_pricing');
            $table->renameColumn('undefined_morning_fee', 'fallback_morning_fee');
            $table->renameColumn('undefined_evening_fee', 'fallback_evening_fee');
            
            $table->string('vehicle_type')->nullable()->after('route_name');
            $table->string('service_type')->default('both')->after('vehicle_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_routes', function (Blueprint $table) {
            $table->renameColumn('morning_vehicle_id', 'pickup_vehicle_id');
            $table->renameColumn('evening_vehicle_id', 'dropoff_vehicle_id');
            $table->renameColumn('saturday_pricing', 'saturday_fee');
            $table->renameColumn('sunday_pricing', 'sunday_fee');
            $table->renameColumn('fallback_morning_fee', 'undefined_morning_fee');
            $table->renameColumn('fallback_evening_fee', 'undefined_evening_fee');
            
            $table->dropColumn(['vehicle_type', 'service_type']);
        });
    }
};
