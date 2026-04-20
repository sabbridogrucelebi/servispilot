<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            }

            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }

            if (!Schema::hasColumn('users', 'user_type')) {
                $table->string('user_type')->default('staff')->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'customer_id')) {
                $table->dropConstrainedForeignId('customer_id');
            }

            $columns = [];

            if (Schema::hasColumn('users', 'username')) {
                $columns[] = 'username';
            }

            if (Schema::hasColumn('users', 'user_type')) {
                $columns[] = 'user_type';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};