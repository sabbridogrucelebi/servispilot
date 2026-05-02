<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentApiController extends BaseApiController
{
    /**
     * Belge kayıtlarını listeler
     */
    public function index(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'documents.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $companyId = $this->getCompanyId();
        
        $query = Document::where('company_id', $companyId);

        if ($request->has('owner_type')) {
            $type = $request->owner_type === 'vehicle' ? \App\Models\Fleet\Vehicle::class : \App\Models\Fleet\Driver::class;
            $query->where('documentable_type', $type);
        }
        if ($request->has('owner_id')) {
            $query->where('documentable_id', $request->owner_id);
        }

        $documents = $query->orderByDesc('id')->get();

        return $this->successResponse($documents, 'Belge kayıtları başarıyla getirildi.');
    }

    /**
     * Tek bir belge detayını getirir
     */
    public function show(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'documents.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $companyId = $this->getCompanyId();
        
        $document = Document::where('company_id', $companyId)->find($id);

        if (!$document) {
            return $this->errorResponse('Kayıt bulunamadı.', 404);
        }

        return $this->successResponse($document, 'Belge detayı başarıyla getirildi.');
    }

    /**
     * Yeni belge ekler
     */
    public function store(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'documents.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $validated = $request->validate([
            'owner_type' => 'required|in:vehicle,driver',
            'owner_id' => 'required|integer',
            'document_type' => 'required|string|max:100',
            'document_name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $this->getCompanyId();
        $validated['documentable_type'] = $validated['owner_type'] === 'vehicle' ? \App\Models\Fleet\Vehicle::class : \App\Models\Fleet\Driver::class;
        $validated['documentable_id'] = $validated['owner_id'];
        unset($validated['owner_type'], $validated['owner_id']);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store("documents/{$this->getCompanyId()}", 'public');
            $validated['file_path'] = $path;
        }

        $document = Document::create($validated);

        return $this->successResponse($document, 'Belge kaydı başarıyla eklendi.', 201);
    }

    /**
     * Belge günceller
     */
    public function update(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'documents.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $document = Document::where('company_id', $this->getCompanyId())->find($id);

        if (!$document) {
            return $this->errorResponse('Kayıt bulunamadı.', 404);
        }

        $validated = $request->validate([
            'owner_type' => 'required|in:vehicle,driver',
            'owner_id' => 'required|integer',
            'document_type' => 'required|string|max:100',
            'document_name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes' => 'nullable|string',
        ]);

        $validated['documentable_type'] = $validated['owner_type'] === 'vehicle' ? \App\Models\Fleet\Vehicle::class : \App\Models\Fleet\Driver::class;
        $validated['documentable_id'] = $validated['owner_id'];
        unset($validated['owner_type'], $validated['owner_id']);

        if ($request->hasFile('file')) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $file = $request->file('file');
            $path = $file->store("documents/{$this->getCompanyId()}", 'public');
            $validated['file_path'] = $path;
        }

        $document->update($validated);

        return $this->successResponse($document, 'Belge kaydı başarıyla güncellendi.');
    }

    /**
     * Belge siler
     */
    public function destroy(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'documents.delete')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $document = Document::where('company_id', $this->getCompanyId())->find($id);

        if (!$document) {
            return $this->errorResponse('Kayıt bulunamadı.', 404);
        }

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return $this->successResponse(null, 'Belge kaydı başarıyla silindi.');
    }

    /**
     * Form seçenekleri
     */
    public function options(Request $request)
    {
        return $this->successResponse([], 'Form seçenekleri başarıyla getirildi.');
    }
}
