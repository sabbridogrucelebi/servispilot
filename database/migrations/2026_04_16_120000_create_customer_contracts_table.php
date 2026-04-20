<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->year('year');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_contracts');
    }
};