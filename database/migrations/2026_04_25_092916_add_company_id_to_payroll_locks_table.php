<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add company_id nullable first
        Schema::table('payroll_locks', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
        });

        // Backfill company_id
        $defaultCompany = DB::table('companies')->first();
        if ($defaultCompany) {
            DB::table('payroll_locks')->update(['company_id' => $defaultCompany->id]);
        }

        // Drop old unique constraint, make company_id non-nullable, add new unique constraint
        Schema::table('payroll_locks', function (Blueprint $table) {
            $table->dropUnique('payroll_locks_period_unique');
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->unique(['company_id', 'period'], 'payroll_locks_company_period_unique');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_locks', function (Blueprint $table) {
            $table->dropUnique('payroll_locks_company_period_unique');
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
            $table->unique('period', 'payroll_locks_period_unique');
        });
    }
};
