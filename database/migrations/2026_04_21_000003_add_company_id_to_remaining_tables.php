<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_contracts', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            }
        });

        Schema::table('customer_service_routes', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_service_routes', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            }
        });

        // Mevcut kayıtları customer üzerinden company_id ile dolduralım
        $contracts = \Illuminate\Support\Facades\DB::table('customer_contracts')
            ->whereNull('company_id')
            ->get();

        foreach ($contracts as $contract) {
            $customer = \Illuminate\Support\Facades\DB::table('customers')
                ->where('id', $contract->customer_id)
                ->first();

            if ($customer && $customer->company_id) {
                \Illuminate\Support\Facades\DB::table('customer_contracts')
                    ->where('id', $contract->id)
                    ->update(['company_id' => $customer->company_id]);
            }
        }

        $routes = \Illuminate\Support\Facades\DB::table('customer_service_routes')
            ->whereNull('company_id')
            ->get();

        foreach ($routes as $route) {
            $customer = \Illuminate\Support\Facades\DB::table('customers')
                ->where('id', $route->customer_id)
                ->first();

            if ($customer && $customer->company_id) {
                \Illuminate\Support\Facades\DB::table('customer_service_routes')
                    ->where('id', $route->id)
                    ->update(['company_id' => $customer->company_id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('customer_service_routes', function (Blueprint $table) {
            if (Schema::hasColumn('customer_service_routes', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
        });

        Schema::table('customer_contracts', function (Blueprint $table) {
            if (Schema::hasColumn('customer_contracts', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
        });
    }
};
