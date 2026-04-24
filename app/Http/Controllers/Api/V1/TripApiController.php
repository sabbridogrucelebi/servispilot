<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\TripService;
use Illuminate\Http\Request;

class TripApiController extends BaseApiController
{
    protected $tripService;

    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    /**
     * Seferleri listeler
     */
    public function index(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'trips.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $companyId = $this->getCompanyId();
        $trips = $this->tripService->getTripList($companyId);

        return $this->successResponse($trips, 'Sefer listesi başarıyla getirildi.');
    }

    /**
     * Tek bir seferin detaylarını getirir
     */
    public function show(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'trips.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $companyId = $this->getCompanyId();
        $trip = $this->tripService->getTripDetail($companyId, $id);

        if (!$trip) {
            return $this->errorResponse('Sefer bulunamadı.', 404);
        }

        // Accessor olan özelliklerin JSON yanıtına eklenmesi
        $trip->append(['day_name', 'formatted_price', 'display_vehicle_plate']);

        return $this->successResponse($trip, 'Sefer detayları başarıyla getirildi.');
    }
}
