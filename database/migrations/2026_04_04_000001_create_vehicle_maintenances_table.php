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
        Schema::create('vehicle_maintenances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('maintenance_type')->nullable();
            $table->string('title');
            $table->date('service_date')->nullable();
            $table->string('service_name')->nullable();
            $table->text('description')->nullable();

            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedInteger('km')->nullable();

            $table->date('next_service_date')->nullable();
            $table->unsignedInteger('next_service_km')->nullable();

            $table->string('status')->default('completed');

            $table->timestamps();

            $table->index(['company_id', 'vehicle_id']);
            $table->index(['company_id', 'service_date']);
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenances');
    }
};