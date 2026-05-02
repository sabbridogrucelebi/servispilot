<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\VehicleMaintenance;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\MaintenancesExport;
use Maatwebsite\Excel\Facades\Excel;

class MaintenanceApiController extends BaseApiController
{
    /**
     * Bakım kayıtlarını listeler
     */
    public function index(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'maintenances.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $query = VehicleMaintenance::with(['vehicle:id,plate']);

        if ($request->user()->is_super_admin && $request->headers->has('x-tenant-id')) {
            $query->where('company_id', $request->header('x-tenant-id'));
        }

        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('service_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('service_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('service_name', 'like', '%' . $search . '%')
                    ->orWhere('maintenance_type', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                        $vehicleQuery->where('plate', 'like', '%' . $search . '%');
                    });
            });
        }

        $maintenances = $query->orderByDesc('service_date')->orderByDesc('id')->get();

        $totalMaintenances = $maintenances->count();
        $totalAmount = (float) $maintenances->sum('amount');
        
        $thisMonthMaintenances = $maintenances->filter(function ($item) {
            return optional($item->service_date)?->format('Y-m') === now()->format('Y-m');
        })->count();

        $upcomingMaintenances = $maintenances->filter(function ($item) {
            return !is_null($item->next_service_date)
                && optional($item->next_service_date)->startOfDay()->gte(now()->startOfDay());
        })->count();

        return $this->successResponse([
            'maintenances' => $maintenances,
            'summary' => [
                'total_count' => $totalMaintenances,
                'total_cost' => $totalAmount,
                'this_month_count' => $thisMonthMaintenances,
                'upcoming_count' => $upcomingMaintenances,
            ]
        ], 'Bakım kayıtları başarıyla getirildi.');
    }

    /**
     * Tek bir bakım detayını getirir
     */
    public function show(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'maintenances.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $companyId = $this->getCompanyId();
        
        $maintenance = VehicleMaintenance::where('company_id', $companyId)
            ->with(['vehicle:id,plate'])
            ->find($id);

        if (!$maintenance) {
            return $this->errorResponse('Kayıt bulunamadı.', 404);
        }

        return $this->successResponse($maintenance, 'Bakım detayı başarıyla getirildi.');
    }

    /**
     * Yeni bakım ekler
     */
    public function store(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'maintenances.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_date' => 'required|date',
            'maintenance_type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'km' => 'nullable|integer|min:0',
            'amount' => 'nullable|numeric|min:0',
            'service_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $toUpper = function (?string $value): ?string {
            if ($value === null) return null;
            $value = trim($value);
            return $value === '' ? null : mb_strtoupper($value, 'UTF-8');
        };

        $maintenanceType = $toUpper($validated['maintenance_type']);
        $nextServiceKm = $this->calculateNextServiceKm(
            (int) $validated['vehicle_id'],
            $maintenanceType,
            $validated['km'] ?? null
        );

        $vehicle = \App\Models\Fleet\Vehicle::find($validated['vehicle_id']);

        $maintenance = VehicleMaintenance::create([
            'company_id' => $vehicle ? $vehicle->company_id : $this->getCompanyId(),
            'vehicle_id' => $validated['vehicle_id'],
            'created_by' => $request->user()->id,
            'service_date' => $validated['service_date'],
            'maintenance_type' => $maintenanceType,
            'title' => $toUpper($validated['title']),
            'km' => $validated['km'] ?? null,
            'amount' => isset($validated['amount']) && $validated['amount'] !== null && $validated['amount'] !== ''
                ? $validated['amount']
                : 0,
            'service_name' => $toUpper($validated['service_name'] ?? null),
            'description' => $toUpper($validated['description'] ?? null),
            'status' => 'completed',
            'next_service_km' => $nextServiceKm,
        ]);

        return $this->successResponse($maintenance, 'Bakım kaydı başarıyla eklendi.', 201);
    }

    /**
     * Bakım günceller
     */
    public function update(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'maintenances.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $maintenance = VehicleMaintenance::where('company_id', $this->getCompanyId())->find($id);

        if (!$maintenance) {
            return $this->errorResponse('Kayıt bulunamadı.', 404);
        }

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_date' => 'required|date',
            'maintenance_type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'km' => 'nullable|integer|min:0',
            'amount' => 'nullable|numeric|min:0',
            'service_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $toUpper = function (?string $value): ?string {
            if ($value === null) return null;
            $value = trim($value);
            return $value === '' ? null : mb_strtoupper($value, 'UTF-8');
        };

        $maintenanceType = $toUpper($validated['maintenance_type']);
        $nextServiceKm = $this->calculateNextServiceKm(
            (int) $validated['vehicle_id'],
            $maintenanceType,
            $validated['km'] ?? null
        );

        $vehicle = \App\Models\Fleet\Vehicle::find($validated['vehicle_id']);

        $maintenance->update([
            'company_id' => $vehicle ? $vehicle->company_id : $maintenance->company_id,
            'vehicle_id' => $validated['vehicle_id'],
            'service_date' => $validated['service_date'],
            'maintenance_type' => $maintenanceType,
            'title' => $toUpper($validated['title']),
            'km' => $validated['km'] ?? null,
            'amount' => isset($validated['amount']) && $validated['amount'] !== null && $validated['amount'] !== ''
                ? $validated['amount']
                : 0,
            'service_name' => $toUpper($validated['service_name'] ?? null),
            'description' => $toUpper($validated['description'] ?? null),
            'next_service_km' => $nextServiceKm,
        ]);

        return $this->successResponse($maintenance, 'Bakım kaydı başarıyla güncellendi.');
    }

    /**
     * Bakım siler
     */
    public function destroy(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'maintenances.delete')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $maintenance = VehicleMaintenance::where('company_id', $this->getCompanyId())->find($id);

        if (!$maintenance) {
            return $this->errorResponse('Kayıt bulunamadı.', 404);
        }

        $maintenance->delete();

        return $this->successResponse(null, 'Bakım kaydı başarıyla silindi.');
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
            
        $mechanics = \App\Models\Mechanic::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name');

        $noteSuggestions = \App\Models\VehicleMaintenance::query()
            ->where('company_id', $companyId)
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->select('description')
            ->distinct()
            ->orderBy('description')
            ->pluck('description');

        $titleSuggestions = \App\Models\VehicleMaintenance::query()
            ->where('company_id', $companyId)
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->select('title')
            ->distinct()
            ->orderBy('title')
            ->pluck('title');

        return $this->successResponse([
            'vehicles' => $vehicles,
            'mechanics' => $mechanics,
            'noteSuggestions' => $noteSuggestions,
            'titleSuggestions' => $titleSuggestions,
        ], 'Form seçenekleri başarıyla getirildi.');
    }

    protected function calculateNextServiceKm(int $vehicleId, ?string $maintenanceType, $km): ?int
    {
        if (empty($vehicleId) || is_null($km) || $km === '') {
            return null;
        }

        $vehicle = \App\Models\Fleet\Vehicle::with('maintenanceSetting')->find($vehicleId);

        if (!$vehicle || !$vehicle->maintenanceSetting) {
            return null;
        }

        $type = mb_strtoupper(trim((string) $maintenanceType), 'UTF-8');
        $currentKm = (int) $km;

        if ($type === 'YAĞ BAKIMI') {
            $interval = (int) ($vehicle->maintenanceSetting->oil_change_interval_km ?? 0);
            return $interval > 0 ? $currentKm + $interval : null;
        }

        if ($type === 'ALT YAĞLAMA') {
            $interval = (int) ($vehicle->maintenanceSetting->under_lubrication_interval_km ?? 0);
            return $interval > 0 ? $currentKm + $interval : null;
        }

        return null;
    }

    public function settings(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'maintenances.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $companyId = $this->getCompanyId();

        $vehicles = \App\Models\Fleet\Vehicle::with('maintenanceSetting')
            ->where('company_id', $companyId)
            ->orderBy('plate')
            ->get(['id', 'plate', 'brand', 'model']);
            
        $mechanics = \App\Models\Mechanic::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return $this->successResponse([
            'vehicles' => $vehicles,
            'mechanics' => $mechanics,
        ], 'Ayarlar başarıyla getirildi.');
    }

    public function saveSettings(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'maintenances.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $companyId = $this->getCompanyId();
        $settings = $request->input('settings', []);

        foreach ($settings as $settingData) {
            if (!isset($settingData['vehicle_id'])) continue;
            
            // Sadece bu şirkete ait araçların ayarlarını güncelle
            $vehicle = \App\Models\Fleet\Vehicle::where('company_id', $companyId)->find($settingData['vehicle_id']);
            if (!$vehicle) continue;

            \App\Models\VehicleMaintenanceSetting::updateOrCreate(
                ['vehicle_id' => $settingData['vehicle_id']],
                [
                    'oil_change_interval_km' => $settingData['oil_change_interval_km'] ?? null,
                    'under_lubrication_interval_km' => $settingData['under_lubrication_interval_km'] ?? null,
                ]
            );
        }

        return $this->successResponse(null, 'Ayarlar başarıyla kaydedildi.');
    }

    public function storeMechanic(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'vehicles.view')) {
            return $this->errorResponse('Yetkiniz yok.', 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $mechanic = \App\Models\Mechanic::create([
            'company_id' => $this->getCompanyId(),
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return $this->successResponse($mechanic, 'Usta başarıyla eklendi.');
    }

    public function updateMechanic(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'vehicles.view')) {
            return $this->errorResponse('Yetkiniz yok.', 403);
        }

        $mechanic = \App\Models\Mechanic::where('company_id', $this->getCompanyId())->find($id);
        if (!$mechanic) return $this->errorResponse('Usta bulunamadı.', 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $oldName = $mechanic->name;
        $mechanic->update(['name' => $validated['name']]);

        if ($oldName !== $validated['name']) {
            \App\Models\VehicleMaintenance::where('company_id', $this->getCompanyId())
                ->where('service_name', $oldName)
                ->update(['service_name' => $validated['name']]);
        }

        return $this->successResponse($mechanic, 'Usta başarıyla güncellendi.');
    }

    public function toggleMechanic(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'vehicles.view')) {
            return $this->errorResponse('Yetkiniz yok.', 403);
        }

        $mechanic = \App\Models\Mechanic::where('company_id', $this->getCompanyId())->find($id);
        if (!$mechanic) return $this->errorResponse('Usta bulunamadı.', 404);

        $mechanic->update(['is_active' => !$mechanic->is_active]);

        return $this->successResponse($mechanic, 'Usta durumu güncellendi.');
    }

    public function destroyMechanic(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'vehicles.view')) {
            return $this->errorResponse('Yetkiniz yok.', 403);
        }

        $mechanic = \App\Models\Mechanic::where('company_id', $this->getCompanyId())->find($id);
        if (!$mechanic) return $this->errorResponse('Usta bulunamadı.', 404);

        $mechanic->delete();

        return $this->successResponse(null, 'Usta başarıyla silindi.');
    }

    public function exportPdf(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'maintenances.view')) {
            return $this->errorResponse('Yetkiniz yok.', 403);
        }

        $query = VehicleMaintenance::with(['vehicle:id,plate', 'creator']);

        if ($request->user()->is_super_admin && $request->headers->has('x-tenant-id')) {
            $query->withoutGlobalScope(\App\Models\Scopes\CompanyScope::class)
                  ->where('company_id', $request->header('x-tenant-id'));
        }

        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('service_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('service_date', '<=', $request->end_date);
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('service_name', 'like', '%' . $search . '%')
                  ->orWhere('maintenance_type', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                      $vehicleQuery->where('plate', 'like', '%' . $search . '%');
                  });
            });
        }

        $maintenances = $query->orderByDesc('service_date')->orderByDesc('id')->get();

        $filters = [
            'search' => $request->search,
            'vehicle_id' => $request->vehicle_id,
            'maintenance_type' => $request->maintenance_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ];

        $pdf = Pdf::loadView('maintenances.pdf', [
            'maintenances' => $maintenances,
            'filters' => $filters,
            'generatedAt' => now(),
            'totalAmount' => (float) $maintenances->sum('amount'),
        ])->setPaper('a4', 'landscape');

        $fileName = 'Bakim_Raporu_' . now()->format('d-m-Y_H-i') . '.pdf';

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    public function exportExcel(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'maintenances.view')) {
            return $this->errorResponse('Yetkiniz yok.', 403);
        }

        $fileName = 'Bakim_Raporu_' . now()->format('d-m-Y_H-i') . '.xlsx';
        
        $filters = $request->all();
        if ($request->user()->is_super_admin && $request->headers->has('x-tenant-id')) {
            $filters['company_id'] = $request->header('x-tenant-id');
        }

        return Excel::download(new MaintenancesExport($filters), $fileName);
    }
}
