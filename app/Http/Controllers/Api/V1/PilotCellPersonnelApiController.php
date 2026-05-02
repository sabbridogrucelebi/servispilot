<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PilotCell\PcRoute;
use App\Models\PilotCell\PcStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PilotCellPersonnelApiController extends BaseApiController
{
    /**
     * Get routes assigned to the authenticated personnel user
     * 
     * GET /api/v1/pilotcell/personnel/my-routes
     */
    public function myRoutes(Request $request)
    {
        $user = $request->user();

        $query = PcRoute::with(['customer', 'vehicle', 'students' => function($q) {
            // Include lat/lng from spatial point columns for both morning (pickup) and evening (dropoff)
            $q->select('*', 
                DB::raw('ST_Y(pickup_location) as morning_lat, ST_X(pickup_location) as morning_lng'),
                DB::raw('ST_Y(dropoff_location) as evening_lat, ST_X(dropoff_location) as evening_lng')
            );
        }]);

        if ($user->role === 'personnel') {
            $query->whereHas('users', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif ($user->isCompanyAdmin() || $user->isSuperAdmin() || $user->role === 'operation') {
            $query->where('company_id', $user->company_id);
        } else {
            return $this->errorResponse('Bu işlemi yapmak için yetkiniz bulunmuyor.', 403);
        }

        $routes = $query->get();

        // Get today's absences for all students in these routes
        $allStudentIds = $routes->pluck('students')->flatten()->pluck('id')->unique()->toArray();
        $todayAbsentIds = \App\Models\PilotCell\PcAbsence::whereIn('pc_student_id', $allStudentIds)
            ->where('absence_date', now()->toDateString())
            ->pluck('pc_student_id')
            ->toArray();

        // Hide raw binary spatial columns and add absence flag
        $routes->each(function($route) use ($todayAbsentIds) {
            $route->students->each(function($student) use ($todayAbsentIds) {
                $student->is_absent_today = in_array($student->id, $todayAbsentIds);
            });
            $route->students->makeHidden(['pickup_location', 'dropoff_location']);
        });

        return $this->successResponse($routes);
    }

    /**
     * Lightweight endpoint: returns today's absent student IDs for a route
     * Designed for 5-second polling — minimal DB query, minimal payload
     * 
     * GET /api/v1/pilotcell/personnel/route-absences?route_id=X
     */
    public function routeAbsences(Request $request)
    {
        $user = $request->user();
        $routeId = $request->query('route_id');

        if (!$routeId) {
            return $this->errorResponse('route_id gerekli.', 422);
        }

        // Get student IDs for this route
        $studentIds = DB::table('pc_route_students')
            ->where('pc_route_id', $routeId)
            ->pluck('pc_student_id')
            ->toArray();

        // Get today's absent ones
        $absentIds = \App\Models\PilotCell\PcAbsence::whereIn('pc_student_id', $studentIds)
            ->where('absence_date', now()->toDateString())
            ->pluck('pc_student_id')
            ->toArray();

        return $this->successResponse([
            'absent_student_ids' => $absentIds,
            'checked_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Set a student's pickup location
     * 
     * POST /api/v1/pilotcell/personnel/set-student-point
     */
    public function setStudentPoint(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['personnel', 'company_admin', 'operation']) && !$user->isSuperAdmin()) {
            return $this->errorResponse('Yetkisiz işlem.', 403);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:pc_students,id',
            'type' => 'required|in:morning,evening',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $student = PcStudent::where('company_id', $user->company_id)->findOrFail($validated['student_id']);

        // Check if the personnel has access to this student's route
        $hasAccess = PcRoute::whereHas('users', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->whereHas('students', function($q) use ($student) {
            $q->where('pc_students.id', $student->id);
        })->exists();

        if (!$hasAccess) {
            return $this->errorResponse('Bu öğrencinin konumunu güncelleme yetkiniz yok.', 403);
        }

        $column = $validated['type'] === 'morning' ? 'pickup_location' : 'dropoff_location';

        // Set spatial point using update() instead of assigning property directly
        $student->update([
            $column => DB::raw("ST_GeomFromText('POINT({$validated['lng']} {$validated['lat']})')")
        ]);

        return $this->successResponse(null, 'Öğrencinin noktası başarıyla kaydedildi.');
    }

    /**
     * Set a student's pickup or dropoff radius
     * 
     * POST /api/v1/pilotcell/personnel/set-student-radius
     */
    public function setStudentRadius(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['personnel', 'company_admin', 'operation']) && !$user->isSuperAdmin()) {
            return $this->errorResponse('Yetkisiz işlem.', 403);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:pc_students,id',
            'type' => 'required|in:morning,evening',
            'radius' => 'required|integer|min:10|max:5000',
        ]);

        $student = PcStudent::where('company_id', $user->company_id)->findOrFail($validated['student_id']);

        // Check if the personnel has access to this student's route
        $hasAccess = PcRoute::whereHas('users', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->whereHas('students', function($q) use ($student) {
            $q->where('pc_students.id', $student->id);
        })->exists();

        if (!$hasAccess) {
            return $this->errorResponse('Bu öğrencinin çapını güncelleme yetkiniz yok.', 403);
        }

        $column = $validated['type'] === 'morning' ? 'pickup_radius' : 'dropoff_radius';

        $student->update([
            $column => $validated['radius']
        ]);

        return $this->successResponse(null, 'Öğrencinin bildirim mesafesi başarıyla kaydedildi.');
    }

    /**
     * Set pickup and dropoff radius for all students in a route
     * 
     * POST /api/v1/pilotcell/personnel/set-bulk-radius
     */
    public function setBulkRadius(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['personnel', 'company_admin', 'operation']) && !$user->isSuperAdmin()) {
            return $this->errorResponse('Yetkisiz işlem.', 403);
        }

        $validated = $request->validate([
            'route_id' => 'required|exists:pc_routes,id',
            'morning_radius' => 'nullable|integer|min:10|max:5000',
            'evening_radius' => 'nullable|integer|min:10|max:5000',
        ]);

        // Check if the personnel has access to this route
        if ($user->role === 'personnel') {
            $hasAccess = PcRoute::where('id', $validated['route_id'])
                ->whereHas('users', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->exists();
        } else {
            $hasAccess = PcRoute::where('id', $validated['route_id'])
                ->where('company_id', $user->company_id)->exists();
        }

        if (!$hasAccess) {
            return $this->errorResponse('Bu güzergah üzerinde yetkiniz yok.', 403);
        }

        // Get all student IDs for this route
        $studentIds = DB::table('pc_route_students')
            ->where('pc_route_id', $validated['route_id'])
            ->pluck('pc_student_id');

        if ($studentIds->isEmpty()) {
            return $this->errorResponse('Bu güzergahta öğrenci bulunmuyor.', 404);
        }

        $updateData = [];
        if (!empty($validated['morning_radius'])) {
            $updateData['pickup_radius'] = $validated['morning_radius'];
        }
        if (!empty($validated['evening_radius'])) {
            $updateData['dropoff_radius'] = $validated['evening_radius'];
        }

        if (empty($updateData)) {
            return $this->errorResponse('Güncellenecek çap değeri girilmedi.', 400);
        }

        PcStudent::whereIn('id', $studentIds)
            ->where('company_id', $user->company_id)
            ->update($updateData);

        return $this->successResponse(null, 'Tüm öğrencilerin çap değerleri başarıyla güncellendi.');
    }
}
