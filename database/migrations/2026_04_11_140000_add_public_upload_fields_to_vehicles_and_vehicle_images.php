<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'public_image_upload_token')) {
                $table->string('public_image_upload_token', 100)->nullable()->unique()->after('notes');
            }
        });

        Schema::table('vehicle_images', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicle_images', 'image_type')) {
                $table->string('image_type', 50)->nullable()->after('title');
            }

            if (!Schema::hasColumn('vehicle_images', 'upload_source')) {
                $table->string('upload_source', 50)->nullable()->after('image_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_images', function (Blueprint $table) {
            if (Schema::hasColumn('vehicle_images', 'upload_source')) {
                $table->dropColumn('upload_source');
            }

            if (Schema::hasColumn('vehicle_images', 'image_type')) {
                $table->dropColumn('image_type');
            }
        });

        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'public_image_upload_token')) {
                $table->dropUnique(['public_image_upload_token']);
                $table->dropColumn('public_image_upload_token');
            }
        });
    }
};