<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate')->unique();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->integer('model_year')->nullable();
            $table->integer('seat_count')->nullable();
            $table->string('fuel_type')->nullable();
            $table->string('color')->nullable();
            $table->string('engine_no')->nullable();
            $table->string('chassis_no')->nullable();
            $table->date('inspection_date')->nullable();
            $table->date('insurance_end_date')->nullable();
            $table->date('kasko_end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};