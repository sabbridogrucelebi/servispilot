<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PilotCell\PcTrip;
use App\Models\PilotCell\PcTripAttendance;
use Illuminate\Http\Request;

class PilotCellTripApiController extends BaseApiController
{
    /**
     * Get active trips for the company
     * 
     * GET /api/v1/pilotcell/trips/active
     */
    public function activeTrips()
    {
        $trips = PcTrip::with(['route', 'driver', 'vehicle'])
            ->where('company_id', $this->getCompanyId())
            ->where('status', 'active')
            ->get();

        return $this->successResponse($trips);
    }

    /**
     * Update student attendance for a trip
     * 
     * POST /api/v1/pilotcell/attendance/update
     */
    public function updateAttendance(Request $request)
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:pc_trips,id',
            'student_id' => 'required|exists:pc_students,id',
            'status' => 'required|in:boarded,alighted,absent',
            'timestamp' => 'required|date',
        ]);

        $trip = PcTrip::findOrFail($validated['trip_id']);

        if ($trip->company_id !== $this->getCompanyId()) {
            return $this->errorResponse('Yetkisiz işlem.', 403);
        }

        $attendance = PcTripAttendance::updateOrCreate(
            [
                'pc_trip_id' => $validated['trip_id'],
                'pc_student_id' => $validated['student_id'],
            ],
            [
                'boarding_status' => $validated['status'],
                $validated['status'] === 'boarded' ? 'boarded_at' : ($validated['status'] === 'alighted' ? 'alighted_at' : null) => $validated['timestamp']
            ]
        );

        return $this->successResponse($attendance, 'Yoklama başarıyla güncellendi.');
    }

    /**
     * Start a new trip
     * 
     * POST /api/v1/pilotcell/trips/start
     */
    public function start(Request $request)
    {
        $validated = $request->validate([
            'route_id' => 'required|exists:pc_routes,id',
            'direction' => 'required|in:morning,evening',
        ]);

        $route = \App\Models\PilotCell\PcRoute::findOrFail($validated['route_id']);

        if ($route->company_id !== $this->getCompanyId()) {
            return $this->errorResponse('Yetkisiz işlem.', 403);
        }

        // Complete any existing active trips for this route/direction to avoid duplicates
        PcTrip::where('pc_route_id', $route->id)
            ->where('status', 'active')
            ->update(['status' => 'completed', 'ended_at' => now()]);

        $trip = PcTrip::create([
            'company_id' => $route->company_id,
            'pc_route_id' => $route->id,
            'direction' => $validated['direction'],
            'vehicle_id' => $route->vehicle_id,
            'driver_id' => $route->driver_id,
            'started_at' => now(),
            'trip_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        // Send push notification to all parents on this route
        $students = \Illuminate\Support\Facades\DB::table('pc_students')
            ->join('pc_route_students', 'pc_students.id', '=', 'pc_route_students.pc_student_id')
            ->where('pc_route_students.pc_route_id', $route->id)
            ->whereNotNull('pc_students.parent_user_id')
            ->select('pc_students.parent_user_id', 'pc_students.name')
            ->get();

        $directionText = $validated['direction'] === 'morning' ? 'sabah servisine' : 'akşam dağıtım servisine';
        
        foreach ($students as $student) {
            \App\Jobs\SendFcmpushNotification::dispatch(
                $student->parent_user_id,
                'Servis Başladı 🚌',
                "Servis aracı {$student->name} isimli öğrenci için {$directionText} başladı."
            );
        }

        return $this->successResponse($trip, 'Sefer başarıyla başlatıldı.');
    }

    /**
     * End an active trip
     * 
     * POST /api/v1/pilotcell/trips/{id}/end
     */
    public function end(Request $request, $id)
    {
        $trip = PcTrip::findOrFail($id);

        if ($trip->company_id !== $this->getCompanyId()) {
            return $this->errorResponse('Yetkisiz işlem.', 403);
        }

        $trip->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        return $this->successResponse($trip, 'Sefer başarıyla bitirildi.');
    }
}
