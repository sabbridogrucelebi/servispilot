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
        Schema::table('pc_routes', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->after('company_id');
            $table->string('service_no')->nullable()->after('name');
            $table->string('driver_name')->nullable()->after('driver_id');
            $table->string('driver_phone')->nullable()->after('driver_name');
            $table->string('hostess_name')->nullable()->after('driver_phone');
            $table->string('hostess_phone')->nullable()->after('hostess_name');

            // Optionally add foreign key constraint if you want
            // $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pc_routes', function (Blueprint $table) {
            $table->dropColumn([
                'customer_id',
                'service_no',
                'driver_name',
                'driver_phone',
                'hostess_name',
                'hostess_phone'
            ]);
        });
    }
};
