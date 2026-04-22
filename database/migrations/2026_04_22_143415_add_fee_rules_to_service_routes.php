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
            $table->foreignId('pickup_vehicle_id')->nullable()->after('driver_id')->constrained('vehicles')->nullOnDelete();
            $table->foreignId('dropoff_vehicle_id')->nullable()->after('pickup_vehicle_id')->constrained('vehicles')->nullOnDelete();
            $table->string('fee_type')->default('free')->after('dropoff_vehicle_id'); // free, paid
            $table->boolean('saturday_fee')->default(false)->after('fee_type');
            $table->boolean('sunday_fee')->default(false)->after('saturday_fee');
            $table->decimal('morning_fee', 10, 2)->default(0)->after('sunday_fee');
            $table->decimal('evening_fee', 10, 2)->default(0)->after('morning_fee');
            $table->decimal('undefined_morning_fee', 10, 2)->default(0)->after('evening_fee');
            $table->decimal('undefined_evening_fee', 10, 2)->default(0)->after('undefined_morning_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_routes', function (Blueprint $table) {
            $table->dropForeign(['pickup_vehicle_id']);
            $table->dropForeign(['dropoff_vehicle_id']);
            $table->dropColumn([
                'pickup_vehicle_id',
                'dropoff_vehicle_id',
                'fee_type',
                'saturday_fee',
                'sunday_fee',
                'morning_fee',
                'evening_fee',
                'undefined_morning_fee',
                'undefined_evening_fee'
            ]);
        });
    }
};
