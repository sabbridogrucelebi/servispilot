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
        Schema::create('pc_student_debts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('pc_student_id')->index();
            $table->string('month_name');
            $table->integer('month_number');
            $table->integer('year');
            $table->decimal('amount', 10, 2);
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
            
            $table->foreign('pc_student_id')->references('id')->on('pc_students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pc_student_debts');
    }
};
