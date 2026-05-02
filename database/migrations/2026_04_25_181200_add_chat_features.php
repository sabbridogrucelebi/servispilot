<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Profile photo for users
        if (!Schema::hasColumn('users', 'profile_photo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('profile_photo')->nullable()->after('email');
            });
        }

        // Soft delete + deleted_for_everyone for messages
        if (!Schema::hasColumn('messages', 'deleted_at')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->softDeletes();
                $table->boolean('deleted_for_everyone')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_photo');
        });
        Schema::table('messages', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('deleted_for_everyone');
        });
    }
};
