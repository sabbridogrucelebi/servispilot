<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_service_routes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            $table->string('route_name');
            $table->string('service_type', 30)->default('both'); // both | morning | evening
            $table->string('vehicle_type', 100);

            $table->unsignedBigInteger('morning_vehicle_id')->nullable();
            $table->unsignedBigInteger('evening_vehicle_id')->nullable();

            $table->string('fee_type', 20)->default('free'); // free | paid
            $table->decimal('morning_fee', 12, 2)->nullable();
            $table->decimal('evening_fee', 12, 2)->nullable();

            $table->decimal('fallback_morning_fee', 12, 2)->nullable();
            $table->decimal('fallback_evening_fee', 12, 2)->nullable();

            $table->boolean('saturday_pricing')->default(true);
            $table->boolean('sunday_pricing')->default(true);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['customer_id', 'is_active']);
            $table->index(['morning_vehicle_id']);
            $table->index(['evening_vehicle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_service_routes');
    }
};