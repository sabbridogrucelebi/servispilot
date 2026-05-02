<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PilotCell\PcStudent;
use App\Models\PilotCell\PcStudentDebt;

class PilotCellParentApiController extends Controller
{
    public function getStudentInfo(Request $request)
    {
        $user = $request->user();

        if ($user->user_type !== 'customer_portal') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $student = PcStudent::with(['debts', 'routes.vehicle'])
            ->where(function($q) use ($user) {
                $q->where('parent_user_id', $user->id)
                  ->orWhere('parent2_user_id', $user->id);
            })
            ->first();

        if (!$student) {
            return response()->json(['message' => 'Öğrenci bulunamadı.'], 404);
        }
        $student->makeHidden(['pickup_location', 'dropoff_location']);

        // Calculate totals
        $totalDebt = $student->debts->sum('amount');
        $totalPaid = $student->debts->sum('paid_amount');
        $remainingDebt = $totalDebt - $totalPaid;

        return response()->json([
            'student' => $student,
            'debts' => $student->debts->sortBy('month_number')->values(),
            'totals' => [
                'total_debt' => $totalDebt,
                'total_paid' => $totalPaid,
                'remaining_debt' => $remainingDebt
            ]
        ]);
    }

    public function updateStudentInfo(Request $request)
    {
        $user = $request->user();

        if ($user->user_type !== 'customer_portal') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $student = PcStudent::where('parent_user_id', $user->id)
            ->orWhere('parent2_user_id', $user->id)
            ->first();

        if (!$student) {
            return response()->json(['message' => 'Öğrenci bulunamadı.'], 404);
        }

        $validated = $request->validate([
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'parent1_name' => 'nullable|string',
            'parent1_phone' => 'nullable|string',
            'parent2_name' => 'nullable|string',
            'parent2_phone' => 'nullable|string',
        ]);

        $student->update($validated);

        return response()->json([
            'message' => 'Bilgiler başarıyla güncellendi.',
            'student' => $student
        ]);
    }

    /**
     * Get absences for the parent's student
     */
    public function getAbsences(Request $request)
    {
        $user = $request->user();

        if ($user->user_type !== 'customer_portal') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $student = PcStudent::where('parent_user_id', $user->id)
            ->orWhere('parent2_user_id', $user->id)
            ->first();

        if (!$student) {
            return response()->json(['message' => 'Öğrenci bulunamadı.'], 404);
        }

        $absences = \App\Models\PilotCell\PcAbsence::where('pc_student_id', $student->id)
            ->where('absence_date', '>=', now()->startOfDay())
            ->orderBy('absence_date')
            ->get()
            ->pluck('absence_date')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->values();

        return response()->json([
            'success' => true,
            'student_name' => $student->name,
            'absences' => $absences,
        ]);
    }

    /**
     * Toggle absence for a specific date
     */
    public function toggleAbsence(Request $request)
    {
        $user = $request->user();

        if ($user->user_type !== 'customer_portal') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $student = PcStudent::where('parent_user_id', $user->id)
            ->orWhere('parent2_user_id', $user->id)
            ->first();

        if (!$student) {
            return response()->json(['message' => 'Öğrenci bulunamadı.'], 404);
        }

        $existing = \App\Models\PilotCell\PcAbsence::where('pc_student_id', $student->id)
            ->where('absence_date', $validated['date'])
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'success' => true,
                'message' => 'Devamsızlık kaldırıldı.',
                'action' => 'removed',
            ]);
        } else {
            \App\Models\PilotCell\PcAbsence::create([
                'company_id' => $student->company_id,
                'pc_student_id' => $student->id,
                'absence_date' => $validated['date'],
                'reported_by' => $user->id,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Gelmeyecek olarak işaretlendi.',
                'action' => 'added',
            ]);
        }
    }

    /**
     * Get active trip for the parent's student to track
     * 
     * GET /api/v1/pilotcell/parent/active-trip
     */
    public function getActiveTrip(Request $request)
    {
        $user = $request->user();

        if ($user->user_type !== 'customer_portal') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $student = PcStudent::where('parent_user_id', $user->id)
            ->orWhere('parent2_user_id', $user->id)
            ->first();

        if (!$student) {
            return response()->json(['message' => 'Öğrenci bulunamadı.'], 404);
        }

        // Find route student is assigned to
        $routeIds = \Illuminate\Support\Facades\DB::table('pc_route_students')
            ->where('pc_student_id', $student->id)
            ->pluck('pc_route_id');

        if ($routeIds->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Öğrenci bir güzergaha kayıtlı değil.']);
        }

        // Find active trip for these routes
        $activeTrip = \App\Models\PilotCell\PcTrip::with(['vehicle'])
            ->whereIn('pc_route_id', $routeIds)
            ->where('status', 'active')
            ->first();

        if (!$activeTrip) {
            return response()->json(['success' => false, 'message' => 'Şu anda aktif bir sefer bulunmuyor.']);
        }

        // Fetch the student with location coordinates using raw SQL
        // Note: In MySQL spatial, X is Longitude and Y is Latitude
        $studentWithLoc = PcStudent::select(
            'id',
            \Illuminate\Support\Facades\DB::raw('ST_Y(pickup_location) as pickup_lat'),
            \Illuminate\Support\Facades\DB::raw('ST_X(pickup_location) as pickup_lng'),
            \Illuminate\Support\Facades\DB::raw('ST_Y(dropoff_location) as dropoff_lat'),
            \Illuminate\Support\Facades\DB::raw('ST_X(dropoff_location) as dropoff_lng')
        )->find($student->id);

        $studentLat = $activeTrip->direction === 'morning' ? $studentWithLoc->pickup_lat : $studentWithLoc->dropoff_lat;
        $studentLng = $activeTrip->direction === 'morning' ? $studentWithLoc->pickup_lng : $studentWithLoc->dropoff_lng;

        return response()->json([
            'success' => true,
            'trip' => $activeTrip,
            'student_location' => [
                'lat' => $studentLat,
                'lng' => $studentLng,
            ]
        ]);
    }
}
