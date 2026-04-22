<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Users tablosuna super admin alanı ──
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_super_admin')->default(false)->after('is_active');
        });

        // ── Companies tablosuna lisans alanları ──
        Schema::table('companies', function (Blueprint $table) {
            $table->string('license_type')->default('standard')->after('is_active');
            $table->dateTime('license_expires_at')->nullable()->after('license_type');
            $table->unsignedInteger('max_vehicles')->default(50)->after('license_expires_at');
            $table->unsignedInteger('max_users')->default(10)->after('max_vehicles');
            $table->string('logo_path')->nullable()->after('max_users');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_super_admin');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'license_type',
                'license_expires_at',
                'max_vehicles',
                'max_users',
                'logo_path',
            ]);
        });
    }
};
