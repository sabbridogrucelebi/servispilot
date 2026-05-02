<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\PilotCell\LocationUpdated;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessLocationUpdate;
use App\Models\PilotCell\PcTrip;
use App\Models\PilotCell\PcLocationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PilotCellLocationApiController extends BaseApiController
{
    /**
     * Update driver location for a trip
     * 
     * POST /api/v1/pilotcell/location/update
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'trip_id'  => 'required|exists:pc_trips,id',
            'lat'      => 'required|numeric',
            'lng'      => 'required|numeric',
            'accuracy' => 'required|numeric',
            'speed'    => 'nullable|numeric',
            'heading'  => 'nullable|numeric',
            'recorded_at' => 'required|date',
        ]);

        // CRITICAL: Accuracy check (Threshold: 20 meters)
        if ($validated['accuracy'] > 20) {
            return $this->errorResponse('Düşük GPS hassasiyeti (>' . $validated['accuracy'] . 'm). Konum kaydedilmedi.', 422);
        }

        $trip = PcTrip::findOrFail($validated['trip_id']);

        // Verify company access
        if ($trip->company_id !== $this->getCompanyId()) {
            return $this->errorResponse('Yetkisiz işlem.', 403);
        }

        // 1. Broadcast real-time location (ShouldBroadcastNow for zero latency)
        try {
            broadcast(new LocationUpdated($trip, $validated))->toOthers();
        } catch (\Exception $e) {
            Log::warning('Location broadcast failed: ' . $e->getMessage());
        }

        // Update the trip with the latest location synchronously so the parent app gets it immediately
        $trip->update([
            'last_lat' => $validated['lat'],
            'last_lng' => $validated['lng'],
            'last_speed' => $validated['speed'] ?? 0,
            'last_heading' => $validated['heading'] ?? 0,
            'last_location_at' => \Carbon\Carbon::parse($validated['recorded_at']),
        ]);

        // 2. Dispatch background job for persistence and geofencing
        ProcessLocationUpdate::dispatch($trip, $validated, $this->getCompanyId());

        return $this->successResponse(null, 'Konum başarıyla güncellendi.');
    }

    /**
     * Get latest location for a trip
     * 
     * GET /api/v1/pilotcell/location/latest/{tripId}
     */
    public function latest($tripId)
    {
        $trip = PcTrip::findOrFail($tripId);
        
        if ($trip->company_id !== $this->getCompanyId()) {
            return $this->errorResponse('Yetkisiz işlem.', 403);
        }

        $attendance = null;
        if (auth()->user()->user_type === 'customer_portal') {
            $student = \App\Models\PilotCell\PcStudent::where('parent_user_id', auth()->id())
                ->orWhere('parent2_user_id', auth()->id())
                ->first();
            
            if ($student) {
                $att = \App\Models\PilotCell\PcTripAttendance::where('pc_trip_id', $trip->id)
                    ->where('pc_student_id', $student->id)
                    ->first();
                if ($att) {
                    $attendance = [
                        'status' => $att->boarding_status,
                        'boarded_at' => $att->boarded_at,
                        'alighted_at' => $att->alighted_at
                    ];
                }
            }
        }

        if ($trip->last_lat && $trip->last_lng) {
            return $this->successResponse([
                'lat' => $trip->last_lat,
                'lng' => $trip->last_lng,
                'speed' => $trip->last_speed,
                'heading' => $trip->last_heading,
                'recorded_at' => $trip->last_location_at,
                'status' => $trip->status,
                'student_attendance' => $attendance,
            ]);
        }

        return $this->successResponse([
            'status' => $trip->status,
            'student_attendance' => $attendance,
        ]);
    }

    /**
     * Get location history for a trip (for playback)
     * 
     * GET /api/v1/pilotcell/location/history/{tripId}
     */
    public function history($tripId)
    {
        $trip = PcTrip::findOrFail($tripId);
        
        if ($trip->company_id !== $this->getCompanyId()) {
            return $this->errorResponse('Yetkisiz işlem.', 403);
        }

        $history = PcLocationLog::where('pc_trip_id', $tripId)
            ->withLatLng()
            ->orderBy('recorded_at', 'asc')
            ->get();

        return $this->successResponse($history);
    }
}
