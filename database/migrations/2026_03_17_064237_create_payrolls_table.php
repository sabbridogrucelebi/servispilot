<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->string('period_month', 7); // 2026-03
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('extra_payment', 12, 2)->default(0);
            $table->decimal('deduction', 12, 2)->default(0);
            $table->decimal('advance_payment', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['driver_id', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};