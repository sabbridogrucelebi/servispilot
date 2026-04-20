<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuels', function (Blueprint $table) {
            $table->foreignId('fuel_station_id')->nullable()->after('vehicle_id')->constrained('fuel_stations')->nullOnDelete();
            $table->string('station_name')->nullable()->after('fuel_station_id');
            $table->string('fuel_type')->default('Dizel')->after('station_name');
            $table->decimal('gross_total_cost', 12, 2)->default(0)->after('price_per_liter');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('gross_total_cost');
        });
    }

    public function down(): void
    {
        Schema::table('fuels', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fuel_station_id');
            $table->dropColumn([
                'station_name',
                'fuel_type',
                'gross_total_cost',
                'discount_amount',
            ]);
        });
    }
};