<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fleet\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\MaintenancesExport;
use App\Models\VehicleMaintenance;
use App\Models\VehicleMaintenanceSetting;
use Maatwebsite\Excel\Facades\Excel;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filteredQuery($request);

        $maintenances = $query->get();

        $vehicles = Vehicle::orderBy('plate')->get();

        $totalMaintenances = $maintenances->count();
        $totalAmount = (float) $maintenances->sum('amount');

        $thisMonthMaintenances = $maintenances->filter(function ($item) {
            return optional($item->service_date)?->format('Y-m') === now()->format('Y-m');
        })->count();

        $upcomingMaintenances = $maintenances->filter(function ($item) {
            return !is_null($item->next_service_date)
                && optional($item->next_service_date)->startOfDay()->gte(now()->startOfDay());
        })->count();

        $maintenanceTypes = VehicleMaintenance::query()
            ->whereNotNull('maintenance_type')
            ->where('maintenance_type', '!=', '')
            ->select('maintenance_type')
            ->distinct()
            ->orderBy('maintenance_type')
            ->pluck('maintenance_type');

        return view('maintenances.index', compact(
            'maintenances',
            'vehicles',
            'totalMaintenances',
            'totalAmount',
            'thisMonthMaintenances',
            'upcomingMaintenances',
            'maintenanceTypes'
        ));
    }

    public function exportExcel(Request $request)
    {
        $fileName = 'Bakim_Raporu_' . now()->format('d-m-Y_H-i') . '.xlsx';

        return Excel::download(new MaintenancesExport($request->all()), $fileName);
    }

    public function exportPdf(Request $request)
    {
        $maintenances = $this->filteredQuery($request)->get();

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

        return $pdf->download($fileName);
    }

    protected function filteredQuery(Request $request)
    {
        $query = VehicleMaintenance::with(['vehicle', 'creator'])
            ->latest('service_date')
            ->latest('id');

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('maintenance_type')) {
            $query->where('maintenance_type', $request->maintenance_type);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('service_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('service_date', '<=', $request->end_date);
        }

        if (!$request->filled('start_date') && !$request->filled('end_date')) {
            $query->whereDate('service_date', '<=', now()->toDateString());
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('service_name', 'like', '%' . $search . '%')
                    ->orWhere('maintenance_type', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                        $vehicleQuery->where('plate', 'like', '%' . $search . '%')
                            ->orWhere('brand', 'like', '%' . $search . '%')
                            ->orWhere('model', 'like', '%' . $search . '%');
                    });
            });
        }

        return $query;
    }

    public function settings()
    {
        $vehicles = Vehicle::with('maintenanceSetting')
            ->orderBy('plate')
            ->get();

        return view('maintenances.settings', compact('vehicles'));
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'settings' => ['nullable', 'array'],
            'settings.*.vehicle_id' => ['required', 'exists:vehicles,id'],
            'settings.*.oil_change_interval_km' => ['nullable', 'integer', 'min:0'],
            'settings.*.under_lubrication_interval_km' => ['nullable', 'integer', 'min:0'],
        ]);

        foreach (($data['settings'] ?? []) as $settingRow) {
            VehicleMaintenanceSetting::updateOrCreate(
                [
                    'company_id' => auth()->user()->company_id,
                    'vehicle_id' => $settingRow['vehicle_id'],
                ],
                [
                    'oil_change_interval_km' => $settingRow['oil_change_interval_km'] !== null && $settingRow['oil_change_interval_km'] !== ''
                        ? $settingRow['oil_change_interval_km']
                        : null,
                    'under_lubrication_interval_km' => $settingRow['under_lubrication_interval_km'] !== null && $settingRow['under_lubrication_interval_km'] !== ''
                        ? $settingRow['under_lubrication_interval_km']
                        : null,
                ]
            );
        }

        return redirect()
            ->route('maintenances.settings')
            ->with('success', 'Bakım ayarları başarıyla kaydedildi.');
    }

    public function create(Request $request)
    {
        $vehicles = Vehicle::with('maintenanceSetting')
            ->orderBy('plate')
            ->get();

        $maintenanceTypes = collect([
            'YAĞ BAKIMI',
            'ALT YAĞLAMA',
            'LASTİK BAKIMI',
            'AKÜ BAKIMI',
            'AĞIR BAKIM',
            'ANTFRİZ BAKIMI',
            'DİĞER BAKIMLAR',
        ]);

        $masters = VehicleMaintenance::query()
            ->whereNotNull('service_name')
            ->where('service_name', '!=', '')
            ->select('service_name')
            ->distinct()
            ->orderBy('service_name')
            ->pluck('service_name');

        $titleSuggestions = VehicleMaintenance::query()
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->select('title')
            ->distinct()
            ->orderBy('title')
            ->pluck('title');

        $noteSuggestions = VehicleMaintenance::query()
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->select('description')
            ->distinct()
            ->orderBy('description')
            ->pluck('description');

        $selectedVehicleId = $request->get('vehicle_id');

        $maintenanceSettings = $vehicles->mapWithKeys(function ($vehicle) {
            return [
                $vehicle->id => [
                    'oil_change_interval_km' => optional($vehicle->maintenanceSetting)->oil_change_interval_km,
                    'under_lubrication_interval_km' => optional($vehicle->maintenanceSetting)->under_lubrication_interval_km,
                ],
            ];
        });

        return view('maintenances.create', compact(
            'vehicles',
            'maintenanceTypes',
            'masters',
            'titleSuggestions',
            'noteSuggestions',
            'selectedVehicleId',
            'maintenanceSettings'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'service_date' => ['required', 'date'],
            'maintenance_type' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'km' => ['nullable', 'integer', 'min:0'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'service_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $toUpper = function (?string $value): ?string {
            if ($value === null) {
                return null;
            }

            $value = trim($value);

            if ($value === '') {
                return null;
            }

            return mb_strtoupper($value, 'UTF-8');
        };

        $maintenanceType = $toUpper($data['maintenance_type']);
        $nextServiceKm = $this->calculateNextServiceKm(
            (int) $data['vehicle_id'],
            $maintenanceType,
            $data['km'] ?? null
        );

        VehicleMaintenance::create([
            'company_id' => auth()->user()->company_id,
            'vehicle_id' => $data['vehicle_id'],
            'created_by' => auth()->id(),
            'service_date' => $data['service_date'],
            'maintenance_type' => $maintenanceType,
            'title' => $toUpper($data['title']),
            'km' => $data['km'] ?? null,
            'amount' => isset($data['amount']) && $data['amount'] !== null && $data['amount'] !== ''
                ? $data['amount']
                : 0,
            'service_name' => $toUpper($data['service_name'] ?? null),
            'description' => $toUpper($data['description'] ?? null),
            'status' => 'completed',
            'next_service_km' => $nextServiceKm,
        ]);

        return redirect()
            ->route('maintenances.index')
            ->with('success', 'Bakım kaydı başarıyla eklendi.');
    }

    public function show(VehicleMaintenance $maintenance)
    {
        return redirect()->route('maintenances.index');
    }

    public function edit(VehicleMaintenance $maintenance)
    {
        $vehicles = Vehicle::with('maintenanceSetting')
            ->orderBy('plate')
            ->get();

        $maintenanceTypes = collect([
            'YAĞ BAKIMI',
            'ALT YAĞLAMA',
            'LASTİK BAKIMI',
            'AKÜ BAKIMI',
            'AĞIR BAKIM',
            'ANTFRİZ BAKIMI',
            'DİĞER BAKIMLAR',
        ]);

        $masters = VehicleMaintenance::query()
            ->whereNotNull('service_name')
            ->where('service_name', '!=', '')
            ->select('service_name')
            ->distinct()
            ->orderBy('service_name')
            ->pluck('service_name');

        $titleSuggestions = VehicleMaintenance::query()
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->select('title')
            ->distinct()
            ->orderBy('title')
            ->pluck('title');

        $noteSuggestions = VehicleMaintenance::query()
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->select('description')
            ->distinct()
            ->orderBy('description')
            ->pluck('description');

        $maintenanceSettings = $vehicles->mapWithKeys(function ($vehicle) {
            return [
                $vehicle->id => [
                    'oil_change_interval_km' => optional($vehicle->maintenanceSetting)->oil_change_interval_km,
                    'under_lubrication_interval_km' => optional($vehicle->maintenanceSetting)->under_lubrication_interval_km,
                ],
            ];
        });

        return view('maintenances.edit', compact(
            'maintenance',
            'vehicles',
            'maintenanceTypes',
            'masters',
            'titleSuggestions',
            'noteSuggestions',
            'maintenanceSettings'
        ));
    }

    public function update(Request $request, VehicleMaintenance $maintenance)
    {
        $data = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'service_date' => ['required', 'date'],
            'maintenance_type' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'km' => ['nullable', 'integer', 'min:0'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'service_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $toUpper = function (?string $value): ?string {
            if ($value === null) {
                return null;
            }

            $value = trim($value);

            if ($value === '') {
                return null;
            }

            return mb_strtoupper($value, 'UTF-8');
        };

        $maintenanceType = $toUpper($data['maintenance_type']);
        $nextServiceKm = $this->calculateNextServiceKm(
            (int) $data['vehicle_id'],
            $maintenanceType,
            $data['km'] ?? null
        );

        $maintenance->update([
            'vehicle_id' => $data['vehicle_id'],
            'service_date' => $data['service_date'],
            'maintenance_type' => $maintenanceType,
            'title' => $toUpper($data['title']),
            'km' => $data['km'] ?? null,
            'amount' => isset($data['amount']) && $data['amount'] !== null && $data['amount'] !== ''
                ? $data['amount']
                : 0,
            'service_name' => $toUpper($data['service_name'] ?? null),
            'description' => $toUpper($data['description'] ?? null),
            'next_service_km' => $nextServiceKm,
        ]);

        return redirect()
            ->route('maintenances.index')
            ->with('success', 'Bakım kaydı başarıyla güncellendi.');
    }

    public function destroy(VehicleMaintenance $maintenance)
    {
        $maintenance->delete();

        return redirect()
            ->route('maintenances.index')
            ->with('success', 'Bakım kaydı başarıyla silindi.');
    }

    protected function calculateNextServiceKm(int $vehicleId, ?string $maintenanceType, $km): ?int
    {
        if (empty($vehicleId) || is_null($km) || $km === '') {
            return null;
        }

        $vehicle = Vehicle::with('maintenanceSetting')->find($vehicleId);

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
}