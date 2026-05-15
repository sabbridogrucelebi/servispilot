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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('min_km_per_liter', 8, 2)->nullable()->comment('1 Litre ile gidilecek minimum KM');
            $table->decimal('max_km_per_liter', 8, 2)->nullable()->comment('1 Litre ile gidilecek maksimum KM');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['min_km_per_liter', 'max_km_per_liter']);
        });
    }
};
