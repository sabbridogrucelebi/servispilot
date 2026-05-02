<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\RouteStop;
use Illuminate\Http\Request;

class RouteStopApiController extends BaseApiController
{
    /**
     * Durak kayıtlarını listeler
     */
    public function index(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'customers.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $companyId = $this->getCompanyId();
        
        $query = RouteStop::where('company_id', $companyId);

        if ($request->has('service_route_id')) {
            $query->where('service_route_id', $request->service_route_id);
        }

        $stops = $query->orderBy('stop_order')->get();

        return $this->successResponse($stops, 'Durak kayıtları başarıyla getirildi.');
    }

    /**
     * Yeni durak ekler
     */
    public function store(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'customers.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $validated = $request->validate([
            'service_route_id' => 'required|exists:service_routes,id',
            'stop_name' => 'required|string|max:255',
            'stop_order' => 'required|integer',
            'stop_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $this->getCompanyId();
        $validated['is_active'] = $validated['is_active'] ?? true;

        $stop = RouteStop::create($validated);

        return $this->successResponse($stop, 'Durak başarıyla eklendi.', 201);
    }

    /**
     * Durak detayını getirir
     */
    public function show(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'customers.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $stop = RouteStop::where('company_id', $this->getCompanyId())->findOrFail($id);

        return $this->successResponse($stop, 'Durak detayları getirildi.');
    }

    /**
     * Durak günceller
     */
    public function update(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'customers.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $stop = RouteStop::where('company_id', $this->getCompanyId())->findOrFail($id);

        $validated = $request->validate([
            'service_route_id' => 'required|exists:service_routes,id',
            'stop_name' => 'required|string|max:255',
            'stop_order' => 'required|integer',
            'stop_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $stop->update($validated);

        return $this->successResponse($stop, 'Durak başarıyla güncellendi.');
    }

    /**
     * Durak siler
     */
    public function destroy(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'customers.delete')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $stop = RouteStop::where('company_id', $this->getCompanyId())->findOrFail($id);
        $stop->delete();

        return $this->successResponse(null, 'Durak başarıyla silindi.');
    }
}
