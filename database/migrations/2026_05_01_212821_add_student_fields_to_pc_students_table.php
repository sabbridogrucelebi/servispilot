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
        Schema::table('pc_students', function (Blueprint $table) {
            $table->string('grade')->nullable()->after('name');
            $table->string('parent1_name')->nullable()->after('phone');
            $table->string('parent1_phone')->nullable()->after('parent1_name');
            $table->string('parent2_name')->nullable()->after('parent1_phone');
            $table->string('parent2_phone')->nullable()->after('parent2_name');
            $table->decimal('monthly_fee', 10, 2)->nullable()->after('parent2_phone');
            $table->unsignedBigInteger('customer_id')->nullable()->after('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pc_students', function (Blueprint $table) {
            $table->dropColumn([
                'grade',
                'parent1_name',
                'parent1_phone',
                'parent2_name',
                'parent2_phone',
                'monthly_fee',
                'customer_id'
            ]);
        });
    }
};
