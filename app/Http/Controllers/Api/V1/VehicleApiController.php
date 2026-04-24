<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Services\VehicleReadService;
use Illuminate\Http\Request;

class VehicleApiController extends BaseApiController
{
    protected VehicleReadService $vehicleService;

    public function __construct(VehicleReadService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    public function index(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'vehicles.view')) {
            return $this->error('Araçları görüntüleme yetkiniz yok.', 403);
        }

        $companyId = $this->getCompanyId();
        $filters = $request->only(['search', 'status', 'filter']);
        $perPage = $request->input('per_page', 20);

        if ($perPage > 50) {
            $perPage = 50;
        }

        $result = $this->vehicleService->getVehicles($companyId, $filters, $perPage);

        return $this->success(
            'Araç listesi getirildi.',
            ['vehicles' => $result['vehicles'], 'kpi' => $result['kpi']],
            $result['pagination']
        );
    }

    public function show(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'vehicles.view')) {
            return $this->error('Araç detayını görüntüleme yetkiniz yok.', 403);
        }

        $companyId = $this->getCompanyId();
        $result = $this->vehicleService->getVehicleDetail($companyId, $id);

        if (!$result) {
            return $this->error('Araç bulunamadı veya yetkisiz erişim.', 404);
        }

        return $this->success('Araç detayı getirildi.', $result);
    }

    public function documents(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'documents.view')) {
            return $this->error('Belgeleri görüntüleme yetkiniz yok.', 403);
        }

        $companyId = $this->getCompanyId();
        $result = $this->vehicleService->getDocuments($companyId, $id);

        if (!$result) {
            return $this->error('Araç bulunamadı veya yetkisiz erişim.', 404);
        }

        return $this->success('Araç belgeleri getirildi.', $result);
    }

    public function fuels(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'fuels.view')) {
            return $this->error('Yakıt kayıtlarını görüntüleme yetkiniz yok.', 403);
        }

        $companyId = $this->getCompanyId();
        $filters = $request->only(['search', 'start_date', 'end_date']);
        $perPage = $request->input('per_page', 20);
        if ($perPage > 50) $perPage = 50;

        $result = $this->vehicleService->getFuels($companyId, $id, $filters, $perPage);

        if (!$result) {
            return $this->error('Araç bulunamadı veya yetkisiz erişim.', 404);
        }

        return $this->success(
            'Araç yakıt kayıtları getirildi.',
            ['fuels' => $result['fuels'], 'summary' => $result['summary']],
            $result['pagination']
        );
    }

    public function maintenances(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'maintenances.view')) {
            return $this->error('Bakım kayıtlarını görüntüleme yetkiniz yok.', 403);
        }

        $companyId = $this->getCompanyId();
        $filters = $request->only(['search', 'start_date', 'end_date']);
        $perPage = $request->input('per_page', 20);
        if ($perPage > 50) $perPage = 50;

        $result = $this->vehicleService->getMaintenances($companyId, $id, $filters, $perPage);

        if (!$result) {
            return $this->error('Araç bulunamadı veya yetkisiz erişim.', 404);
        }

        return $this->success(
            'Araç bakım kayıtları getirildi.',
            ['maintenances' => $result['maintenances']],
            $result['pagination']
        );
    }

    public function penalties(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'penalties.view')) {
            return $this->error('Ceza kayıtlarını görüntüleme yetkiniz yok.', 403);
        }

        $companyId = $this->getCompanyId();
        $filters = $request->only(['search', 'start_date', 'end_date']);
        $perPage = $request->input('per_page', 20);
        if ($perPage > 50) $perPage = 50;

        $result = $this->vehicleService->getPenalties($companyId, $id, $filters, $perPage);

        if (!$result) {
            return $this->error('Araç bulunamadı veya yetkisiz erişim.', 404);
        }

        return $this->success(
            'Araç ceza kayıtları getirildi.',
            ['penalties' => $result['penalties']],
            $result['pagination']
        );
    }

    public function gallery(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'vehicles.view')) {
            return $this->error('Araç görsellerini görüntüleme yetkiniz yok.', 403);
        }

        $companyId = $this->getCompanyId();
        $result = $this->vehicleService->getGallery($companyId, $id);

        if (!$result) {
            return $this->error('Araç bulunamadı veya yetkisiz erişim.', 404);
        }

        return $this->success('Araç görselleri getirildi.', $result);
    }

    public function reports(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'reports.view')) {
            return $this->error('Raporları görüntüleme yetkiniz yok.', 403);
        }

        $companyId = $this->getCompanyId();
        $reportsMonth = $request->input('reports_month', now()->format('Y-m'));

        $result = $this->vehicleService->getReports($companyId, $id, $reportsMonth);

        if (!$result) {
            return $this->error('Araç bulunamadı veya yetkisiz erişim.', 404);
        }

        return $this->success('Araç raporu getirildi.', $result);
    }
}
