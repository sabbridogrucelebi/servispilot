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
        Schema::table('trips', function (Blueprint $table) {
            $table->foreignId('morning_driver_id')->nullable()->after('driver_id')->constrained('drivers')->nullOnDelete();
            $table->foreignId('evening_driver_id')->nullable()->after('morning_driver_id')->constrained('drivers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign(['morning_driver_id']);
            $table->dropForeign(['evening_driver_id']);
            $table->dropColumn(['morning_driver_id', 'evening_driver_id']);
        });
    }
};
