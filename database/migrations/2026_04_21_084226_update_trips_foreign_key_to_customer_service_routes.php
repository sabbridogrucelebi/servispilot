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
        try {
            Schema::table('trips', function (Blueprint $table) {
                $table->dropForeign(['service_route_id']);
            });
        } catch (\Exception $e) {
            // Ignore if foreign key doesn't exist
        }

        Schema::table('trips', function (Blueprint $table) {
            $table->foreign('service_route_id')->references('id')->on('customer_service_routes')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_service_routes', function (Blueprint $table) {
            //
        });
    }
};
