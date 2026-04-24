<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\PersonnelService;
use Illuminate\Http\Request;

class PersonnelApiController extends BaseApiController
{
    protected $personnelService;

    public function __construct(PersonnelService $personnelService)
    {
        $this->personnelService = $personnelService;
    }

    /**
     * Personelleri (Sürücüleri) listeler
     */
    public function index(Request $request)
    {
        // Yetki kontrolü
        if (!$this->userHasPermission($request->user(), 'drivers.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $companyId = $this->getCompanyId();
        $personnel = $this->personnelService->getPersonnelList($companyId);

        return $this->successResponse($personnel, 'Personel listesi başarıyla getirildi.');
    }

    /**
     * Tek bir personelin detaylarını getirir
     */
    public function show(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'drivers.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $companyId = $this->getCompanyId();
        $driver = $this->personnelService->getPersonnelDetail($companyId, $id);

        if (!$driver) {
            return $this->errorResponse('Personel bulunamadı.', 404);
        }

        return $this->successResponse($driver, 'Personel detayları başarıyla getirildi.');
    }
}
