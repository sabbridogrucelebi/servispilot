<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'issuer_name')) {
                $table->string('issuer_name')->nullable()->after('document_type');
            }

            if (!Schema::hasColumn('documents', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('end_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'issuer_name')) {
                $table->dropColumn('issuer_name');
            }

            if (Schema::hasColumn('documents', 'archived_at')) {
                $table->dropColumn('archived_at');
            }
        });
    }
};