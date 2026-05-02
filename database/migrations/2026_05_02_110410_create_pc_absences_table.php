<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pc_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pc_student_id')->constrained('pc_students')->cascadeOnDelete();
            $table->date('absence_date');
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->unique(['pc_student_id', 'absence_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pc_absences');
    }
};
