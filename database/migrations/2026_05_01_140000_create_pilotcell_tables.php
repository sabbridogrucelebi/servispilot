<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Students
        Schema::create('pc_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('student_no')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->geometry('pickup_location', 'point', 4326);
            $table->integer('geofence_radius')->default(500); // meters
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->spatialIndex('pickup_location');
        });

        // 2. Routes
        Schema::create('pc_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->enum('direction', ['morning', 'evening'])->default('morning');
            $table->time('start_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Route Students (Pivot)
        Schema::create('pc_route_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pc_route_id')->constrained('pc_routes')->cascadeOnDelete();
            $table->foreignId('pc_student_id')->constrained('pc_students')->cascadeOnDelete();
            $table->integer('stop_order')->default(0);
            $table->timestamps();
        });

        // 4. Trips
        Schema::create('pc_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pc_route_id')->constrained('pc_routes')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->date('trip_date');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamps();
        });

        // 5. Trip Attendance
        Schema::create('pc_trip_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pc_trip_id')->constrained('pc_trips')->cascadeOnDelete();
            $table->foreignId('pc_student_id')->constrained('pc_students')->cascadeOnDelete();
            $table->enum('boarding_status', ['pending', 'boarded', 'alighted', 'absent'])->default('pending');
            $table->timestamp('boarded_at')->nullable();
            $table->timestamp('alighted_at')->nullable();
            $table->timestamps();
        });

        // 6. Location Logs (Spatial)
        Schema::create('pc_location_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pc_trip_id')->constrained('pc_trips')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->geometry('location', 'point', 4326);
            $table->decimal('accuracy', 10, 2);
            $table->decimal('speed', 10, 2)->nullable();
            $table->decimal('heading', 10, 2)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->spatialIndex('location');
        });

        // 7. Geofence Notifications
        Schema::create('pc_geofence_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pc_trip_id')->constrained('pc_trips')->cascadeOnDelete();
            $table->foreignId('pc_student_id')->constrained('pc_students')->cascadeOnDelete();
            $table->enum('notification_type', ['approaching', 'arrived', 'departed']);
            $table->timestamp('sent_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pc_geofence_notifications');
        Schema::dropIfExists('pc_location_logs');
        Schema::dropIfExists('pc_trip_attendance');
        Schema::dropIfExists('pc_trips');
        Schema::dropIfExists('pc_route_students');
        Schema::dropIfExists('pc_routes');
        Schema::dropIfExists('pc_students');
    }
};
