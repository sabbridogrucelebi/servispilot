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
    /**
     * Yeni personel ekler
     */
    public function store(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'drivers.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $validated = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'full_name' => 'required|string|max:255',
            'tc_no' => 'nullable|string|max:20|unique:drivers,tc_no',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'license_class' => 'nullable|string|max:50',
            'src_type' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'start_shift' => 'nullable|string|in:morning,evening',
        ]);

        $validated['company_id'] = $this->getCompanyId();
        $driver = \App\Models\Fleet\Driver::create($validated);

        return $this->successResponse($driver, 'Personel başarıyla eklendi.', 201);
    }

    /**
     * Personel günceller
     */
    public function update(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'drivers.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $driver = \App\Models\Fleet\Driver::where('company_id', $this->getCompanyId())->find($id);

        if (!$driver) {
            return $this->errorResponse('Personel bulunamadı.', 404);
        }

        $validated = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'full_name' => 'required|string|max:255',
            'tc_no' => 'nullable|string|max:20|unique:drivers,tc_no,' . $driver->id,
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'license_class' => 'nullable|string|max:50',
            'src_type' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'start_shift' => 'nullable|string|in:morning,evening',
        ]);

        $driver->update($validated);

        return $this->successResponse($driver, 'Personel başarıyla güncellendi.');
    }

    /**
     * Personel siler
     */
    public function destroy(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'drivers.delete')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $driver = \App\Models\Fleet\Driver::where('company_id', $this->getCompanyId())->find($id);

        if (!$driver) {
            return $this->errorResponse('Personel bulunamadı.', 404);
        }

        $driver->delete();

        return $this->successResponse(null, 'Personel başarıyla silindi.');
    }

    /**
     * Form seçenekleri
     */
    public function options(Request $request)
    {
        $companyId = $this->getCompanyId();
        
        $vehicles = \App\Models\Fleet\Vehicle::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'plate']);

        return $this->successResponse([
            'vehicles' => $vehicles,
        ], 'Form seçenekleri başarıyla getirildi.');
    }

    public function updateStatus(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'drivers.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $driver = \App\Models\Fleet\Driver::where('company_id', $this->getCompanyId())->find($id);

        if (!$driver) return $this->errorResponse('Personel bulunamadı.', 404);

        $request->validate([
            'is_active' => 'required|boolean',
            'leave_date' => 'nullable|date'
        ]);

        $driver->update([
            'is_active' => $request->is_active,
            'leave_date' => $request->leave_date
        ]);

        return $this->successResponse($driver, 'Personel durumu güncellendi.');
    }

    public function updateVehicle(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'drivers.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $driver = \App\Models\Fleet\Driver::where('company_id', $this->getCompanyId())->find($id);

        if (!$driver) return $this->errorResponse('Personel bulunamadı.', 404);

        $request->validate(['vehicle_id' => 'nullable|exists:vehicles,id']);

        $driver->update(['vehicle_id' => $request->vehicle_id]);

        return $this->successResponse($driver, 'Personel aracı güncellendi.');
    }

    public function uploadDocument(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'drivers.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $driver = \App\Models\Fleet\Driver::where('company_id', $this->getCompanyId())->find($id);

        if (!$driver) return $this->errorResponse('Personel bulunamadı.', 404);

        $request->validate([
            'document' => 'required|file|max:10240',
            'type' => 'required|string',
            'title' => 'nullable|string'
        ]);

        $file = $request->file('document');
        $path = $file->store('driver-documents', 'public');

        $document = $driver->documents()->create([
            'company_id' => $this->getCompanyId(),
            'document_type' => $request->input('type'),
            'document_name' => $request->input('title') ?: $file->getClientOriginalName(),
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName()
        ]);

        return $this->successResponse($document, 'Dosya başarıyla yüklendi.', 201);
    }

    public function deleteDocument(Request $request, $id, $documentId)
    {
        if (!$this->userHasPermission($request->user(), 'drivers.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $driver = \App\Models\Fleet\Driver::where('company_id', $this->getCompanyId())->find($id);

        if (!$driver) return $this->errorResponse('Personel bulunamadı.', 404);

        $document = $driver->documents()->find($documentId);

        if (!$document) return $this->errorResponse('Dosya bulunamadı.', 404);

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($document->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return $this->successResponse(null, 'Dosya başarıyla silindi.');
    }
}
