<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('full_name');
            $table->string('tc_no', 20)->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('license_class')->nullable();
            $table->string('src_type')->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->date('src_expiry_date')->nullable();
            $table->date('psychotechnic_expiry_date')->nullable();
            $table->date('birth_date')->nullable();
            $table->date('start_date')->nullable();
            $table->decimal('base_salary', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};