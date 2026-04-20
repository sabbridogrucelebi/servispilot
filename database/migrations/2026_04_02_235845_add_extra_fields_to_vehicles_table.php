<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->date('registration_date')->nullable()->after('model_year');
            $table->string('gear_type')->nullable()->after('seat_count');
            $table->string('vehicle_package')->nullable()->after('vehicle_type');

            $table->string('license_serial_no')->nullable()->after('chassis_no');
            $table->string('license_owner')->nullable()->after('license_serial_no');
            $table->string('owner_tax_or_tc_no')->nullable()->after('license_owner');

            $table->date('exhaust_date')->nullable()->after('inspection_date');
            $table->string('other_color')->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'registration_date',
                'gear_type',
                'vehicle_package',
                'license_serial_no',
                'license_owner',
                'owner_tax_or_tc_no',
                'exhaust_date',
                'other_color',
            ]);
        });
    }
};