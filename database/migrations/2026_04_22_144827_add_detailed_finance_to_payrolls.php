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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('traffic_penalty', 10, 2)->default(0)->after('bank_payment');
            $table->decimal('extra_bonus', 10, 2)->default(0)->after('traffic_penalty');
            $table->text('extra_notes')->nullable()->after('extra_bonus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['traffic_penalty', 'extra_bonus', 'extra_notes']);
        });
    }
};
