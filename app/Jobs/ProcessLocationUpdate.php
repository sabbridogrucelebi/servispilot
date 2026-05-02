<?php

namespace App\Jobs;

use App\Models\PilotCell\PcLocationLog;
use App\Models\PilotCell\PcTrip;
use App\Models\PilotCell\PcStudent;
use App\Models\PilotCell\PcGeofenceNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessLocationUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $trip;
    protected $data;
    protected $companyId;

    public function __construct(PcTrip $trip, array $data, $companyId)
    {
        $this->trip = $trip;
        $this->data = $data;
        $this->companyId = $companyId;
    }

    public function handle(): void
    {
        // 1. Persist Location using MySQL Spatial POINT
        // Use ST_GeomFromText for POINT creation
        $point = "POINT(" . $this->data['lat'] . " " . $this->data['lng'] . ")";
        
        PcLocationLog::create([
            'company_id'  => $this->companyId,
            'pc_trip_id'  => $this->trip->id,
            'driver_id'   => $this->trip->driver_id,
            'vehicle_id'  => $this->trip->vehicle_id,
            'location'    => DB::raw("ST_GeomFromText('$point', 4326)"),
            'accuracy'    => $this->data['accuracy'],
            'speed'       => $this->data['speed'] ?? 0,
            'heading'     => $this->data['heading'] ?? 0,
            'recorded_at' => Carbon::parse($this->data['recorded_at']),
        ]);

        // 2. Geofence Check
        // Find students on this route whose pickup location is within geofence_radius
        // MySQL 8.0+ ST_Distance_Sphere is highly accurate
        $students = DB::table('pc_students')
            ->join('pc_route_students', 'pc_students.id', '=', 'pc_route_students.pc_student_id')
            ->where('pc_route_students.pc_route_id', $this->trip->pc_route_id)
            ->where('pc_students.company_id', $this->companyId)
            ->whereRaw("ST_Distance_Sphere(pickup_location, ST_GeomFromText(?, 4326)) <= geofence_radius", [$point])
            ->select('pc_students.id', 'pc_students.name', 'pc_students.parent_user_id')
            ->get();

        foreach ($students as $student) {
            // Check if notification already sent for this trip/student/type within reasonable time
            $alreadyNotified = PcGeofenceNotification::where('pc_trip_id', $this->trip->id)
                ->where('pc_student_id', $student->id)
                ->where('notification_type', 'approaching')
                ->exists();

            if (!$alreadyNotified) {
                PcGeofenceNotification::create([
                    'company_id' => $this->companyId,
                    'pc_trip_id' => $this->trip->id,
                    'pc_student_id' => $student->id,
                    'notification_type' => 'approaching',
                    'sent_at' => now(),
                ]);

                // FCM Push Notification to Parent
                if ($student->parent_user_id) {
                    \App\Jobs\SendFcmpushNotification::dispatch(
                        $student->parent_user_id,
                        'Servis Yaklaşıyor 🚌',
                        "{$student->name} isimli öğrencinin servisi durağa yaklaşmak üzere."
                    );
                }
            }
        }
    }
}
