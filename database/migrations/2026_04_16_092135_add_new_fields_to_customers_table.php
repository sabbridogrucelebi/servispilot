<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'company_title')) {
                $table->string('company_title')->nullable()->after('company_name');
            }

            if (!Schema::hasColumn('customers', 'authorized_phone')) {
                $table->string('authorized_phone')->nullable()->after('authorized_person');
            }

            if (!Schema::hasColumn('customers', 'vat_rate')) {
                $table->decimal('vat_rate', 5, 2)->default(20)->after('monthly_price');
            }

            if (!Schema::hasColumn('customers', 'withholding_rate')) {
                $table->string('withholding_rate')->nullable()->after('vat_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('customers', 'company_title')) {
                $columns[] = 'company_title';
            }

            if (Schema::hasColumn('customers', 'authorized_phone')) {
                $columns[] = 'authorized_phone';
            }

            if (Schema::hasColumn('customers', 'vat_rate')) {
                $columns[] = 'vat_rate';
            }

            if (Schema::hasColumn('customers', 'withholding_rate')) {
                $columns[] = 'withholding_rate';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};