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

        // Finansal verileri gizle (yetki yoksa)
        if (!$this->userHasPermission($request->user(), 'financials.view')) {
            $result['stats'] = ['revenue' => 0, 'fuel' => 0, 'salary' => 0, 'net' => 0];
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

    public function storeGallery(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'vehicles.edit')) {
            return $this->error('Araç görsellerini düzenleme yetkiniz yok.', 403);
        }

        $request->validate([
            'image' => 'required|image|max:5120',
            'type' => 'required|string|max:100',
            'title' => 'nullable|string|max:255',
            'is_featured' => 'nullable|in:0,1,true,false',
        ]);

        $companyId = $this->getCompanyId();
        $vehicle = \App\Models\Fleet\Vehicle::where('company_id', $companyId)->find($id);

        if (!$vehicle) {
            return $this->error('Araç bulunamadı veya yetkisiz erişim.', 404);
        }

        $path = $request->file('image')->store('vehicle-images', 'public');
        $isFeatured = filter_var($request->input('is_featured', false), FILTER_VALIDATE_BOOLEAN);

        if ($isFeatured) {
            $vehicle->images()->update(['is_featured' => false]);
        }

        $vehicle->images()->create([
            'company_id' => $companyId,
            'image_type_label' => $request->input('type'),
            'title' => $request->input('title'),
            'file_path' => $path,
            'is_featured' => $isFeatured,
            'source' => 'manual'
        ]);

        return $this->success('Görsel başarıyla eklendi.', null, null, 201);
    }

    public function deleteGallery(Request $request, $id, $imageId)
    {
        if (!$this->userHasPermission($request->user(), 'vehicles.edit')) {
            return $this->error('Araç görsellerini düzenleme yetkiniz yok.', 403);
        }

        $companyId = $this->getCompanyId();
        $vehicle = \App\Models\Fleet\Vehicle::where('company_id', $companyId)->find($id);

        if (!$vehicle) {
            return $this->error('Araç bulunamadı.', 404);
        }

        $image = $vehicle->images()->find($imageId);
        if (!$image) {
            return $this->error('Görsel bulunamadı.', 404);
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($image->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($image->file_path);
        }

        $image->delete();

        return $this->success('Görsel başarıyla silindi.');
    }

    public function setFeaturedGallery(Request $request, $id, $imageId)
    {
        if (!$this->userHasPermission($request->user(), 'vehicles.edit')) {
            return $this->error('Araç görsellerini düzenleme yetkiniz yok.', 403);
        }

        $companyId = $this->getCompanyId();
        $vehicle = \App\Models\Fleet\Vehicle::where('company_id', $companyId)->find($id);

        if (!$vehicle) {
            return $this->error('Araç bulunamadı.', 404);
        }

        $image = $vehicle->images()->find($imageId);
        if (!$image) {
            return $this->error('Görsel bulunamadı.', 404);
        }

        $vehicle->images()->update(['is_featured' => false]);
        $image->update(['is_featured' => true]);

        return $this->success('Vitrin resmi güncellendi.');
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

        // Finansal verileri gizle (yetki yoksa)
        if (!$this->userHasPermission($request->user(), 'financials.view')) {
            if (isset($result['totals'])) {
                $result['totals']['income'] = 0;
            }
            if (isset($result['details'])) {
                foreach ($result['details'] as &$detail) {
                    $detail['total_price'] = 0;
                }
            }
        }

        return $this->success('Araç raporu getirildi.', $result);
    }
}
