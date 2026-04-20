<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
        });

        Schema::table('service_routes', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
        });

        Schema::table('route_stops', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
        });

        Schema::table('fuels', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
        });

        $companyId = DB::table('companies')->insertGetId([
            'name' => 'Ana Firma',
            'slug' => 'ana-firma',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->whereNull('company_id')->update(['company_id' => $companyId]);
        DB::table('vehicles')->whereNull('company_id')->update(['company_id' => $companyId]);
        DB::table('drivers')->whereNull('company_id')->update(['company_id' => $companyId]);
        DB::table('customers')->whereNull('company_id')->update(['company_id' => $companyId]);
        DB::table('service_routes')->whereNull('company_id')->update(['company_id' => $companyId]);
        DB::table('route_stops')->whereNull('company_id')->update(['company_id' => $companyId]);
        DB::table('trips')->whereNull('company_id')->update(['company_id' => $companyId]);
        DB::table('payrolls')->whereNull('company_id')->update(['company_id' => $companyId]);
        DB::table('documents')->whereNull('company_id')->update(['company_id' => $companyId]);
        DB::table('fuels')->whereNull('company_id')->update(['company_id' => $companyId]);
    }

    public function down(): void
    {
        Schema::table('fuels', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('route_stops', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('service_routes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
    }
};