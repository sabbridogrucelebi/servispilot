<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traffic_penalties', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();

            $table->string('penalty_no')->unique();
            $table->date('penalty_date');
            $table->string('penalty_time', 10)->nullable();
            $table->string('penalty_article');
            $table->string('penalty_location');

            $table->decimal('penalty_amount', 12, 2)->default(0);
            $table->decimal('discounted_amount', 12, 2)->default(0);

            $table->string('driver_name');
            $table->date('payment_date')->nullable();
            $table->decimal('paid_amount', 12, 2)->nullable();

            $table->string('payment_status')->default('unpaid');
            $table->string('traffic_penalty_document')->nullable();
            $table->string('payment_receipt')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'penalty_date']);
            $table->index(['company_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traffic_penalties');
    }
};