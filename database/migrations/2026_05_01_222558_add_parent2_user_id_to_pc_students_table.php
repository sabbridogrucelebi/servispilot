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
            $table->unsignedBigInteger('parent2_user_id')->nullable()->after('parent_user_id');
            $table->foreign('parent2_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pc_students', function (Blueprint $table) {
            $table->dropForeign(['parent2_user_id']);
            $table->dropColumn('parent2_user_id');
        });
    }
};
