<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_service_routes', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_service_routes', 'service_type')) {
                $table->string('service_type', 30)->default('both')->after('route_name');
            }

            if (!Schema::hasColumn('customer_service_routes', 'morning_vehicle_id')) {
                $table->unsignedBigInteger('morning_vehicle_id')->nullable()->after('vehicle_type');
            }

            if (!Schema::hasColumn('customer_service_routes', 'evening_vehicle_id')) {
                $table->unsignedBigInteger('evening_vehicle_id')->nullable()->after('morning_vehicle_id');
            }

            if (!Schema::hasColumn('customer_service_routes', 'fallback_morning_fee')) {
                $table->decimal('fallback_morning_fee', 12, 2)->nullable()->after('evening_fee');
            }

            if (!Schema::hasColumn('customer_service_routes', 'fallback_evening_fee')) {
                $table->decimal('fallback_evening_fee', 12, 2)->nullable()->after('fallback_morning_fee');
            }

            if (!Schema::hasColumn('customer_service_routes', 'saturday_pricing')) {
                $table->boolean('saturday_pricing')->default(true)->after('fallback_evening_fee');
            }

            if (!Schema::hasColumn('customer_service_routes', 'sunday_pricing')) {
                $table->boolean('sunday_pricing')->default(true)->after('saturday_pricing');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_service_routes', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('customer_service_routes', 'service_type')) {
                $columns[] = 'service_type';
            }

            if (Schema::hasColumn('customer_service_routes', 'morning_vehicle_id')) {
                $columns[] = 'morning_vehicle_id';
            }

            if (Schema::hasColumn('customer_service_routes', 'evening_vehicle_id')) {
                $columns[] = 'evening_vehicle_id';
            }

            if (Schema::hasColumn('customer_service_routes', 'fallback_morning_fee')) {
                $columns[] = 'fallback_morning_fee';
            }

            if (Schema::hasColumn('customer_service_routes', 'fallback_evening_fee')) {
                $columns[] = 'fallback_evening_fee';
            }

            if (Schema::hasColumn('customer_service_routes', 'saturday_pricing')) {
                $columns[] = 'saturday_pricing';
            }

            if (Schema::hasColumn('customer_service_routes', 'sunday_pricing')) {
                $columns[] = 'sunday_pricing';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};