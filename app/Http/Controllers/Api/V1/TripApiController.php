<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Trip;
use App\Models\Customer;
use App\Models\CustomerServiceRoute;
use App\Models\Fleet\Vehicle;
use App\Models\User;
use Carbon\Carbon;
use App\Services\TripService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
    /**
     * Yeni sefer ekler
     */
    public function store(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'trips.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $validated = $request->validate([
            'service_route_id' => 'required|exists:customer_service_routes,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'morning_vehicle_id' => 'nullable|exists:vehicles,id',
            'evening_vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'trip_date' => 'required|date',
            'trip_status' => 'required|string|max:100',
            'trip_price' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $this->getCompanyId();
        $trip = \App\Models\Trip::create($validated);

        return $this->successResponse($trip, 'Sefer başarıyla eklendi.', 201);
    }

    /**
     * Sefer günceller
     */
    public function update(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'trips.edit')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $trip = \App\Models\Trip::where('company_id', $this->getCompanyId())->find($id);

        if (!$trip) {
            return $this->errorResponse('Sefer bulunamadı.', 404);
        }

        $validated = $request->validate([
            'service_route_id' => 'required|exists:customer_service_routes,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'morning_vehicle_id' => 'nullable|exists:vehicles,id',
            'evening_vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'trip_date' => 'required|date',
            'trip_status' => 'required|string|max:100',
            'trip_price' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $trip->update($validated);

        return $this->successResponse($trip, 'Sefer başarıyla güncellendi.');
    }

    /**
     * Sefer siler
     */
    public function destroy(Request $request, $id)
    {
        if (!$this->userHasPermission($request->user(), 'trips.delete')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $trip = \App\Models\Trip::where('company_id', $this->getCompanyId())->find($id);

        if (!$trip) {
            return $this->errorResponse('Sefer bulunamadı.', 404);
        }

        $trip->delete();

        return $this->successResponse(null, 'Sefer başarıyla silindi.');
    }
    /**
     * Form seçeneklerini getirir
     */
    public function options(Request $request)
    {
        $companyId = $this->getCompanyId();
        
        $routes = \App\Models\CustomerServiceRoute::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'route_name', 'customer_id']);

        $vehicles = \App\Models\Fleet\Vehicle::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'plate', 'seat_count']);

        $drivers = \App\Models\User::where('company_id', $companyId)
            ->where('user_type', 'staff')
            ->where('is_active', true)
            ->get(['id', 'name']);

        return $this->successResponse([
            'routes' => $routes,
            'vehicles' => $vehicles,
            'drivers' => $drivers
        ], 'Form seçenekleri başarıyla getirildi.');
    }

    /**
     * Ortak Matrix Verisi Hesaplayıcı
     */
    private function getMatrixData(Request $request): array
    {
        $now = now();
        $companyId = $this->getCompanyId();

        $selectedCustomerId = $request->integer('customer_id');
        $selectedMonth = (int) $request->input('month', $now->month);
        $selectedYear = (int) $request->input('year', $now->year);

        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = $now->month;
        }

        if ($selectedYear < 2020 || $selectedYear > ($now->year + 5)) {
            $selectedYear = $now->year;
        }

        $selectedCustomer = null;
        $serviceRoutes = collect();
        $monthDays = collect();
        $matrix = [];
        $routeTotals = [];
        $summary = [
            'subtotal' => 0, 'vat_rate' => 0, 'vat_amount' => 0,
            'withholding_rate' => null, 'withholding_amount' => 0,
            'grand_total' => 0, 'net_total' => 0,
        ];

        if ($selectedCustomerId) {
            $selectedCustomer = Customer::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->whereKey($selectedCustomerId)
                ->first();

            if ($selectedCustomer) {
                $serviceRoutes = CustomerServiceRoute::query()
                    ->with(['morningVehicle.drivers', 'eveningVehicle.drivers'])
                    ->where('customer_id', $selectedCustomer->id)
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->orderBy('route_name')
                    ->get();

                $startOfMonth = Carbon::create($selectedYear, $selectedMonth, 1)->startOfDay();
                $endOfMonth = $startOfMonth->copy()->endOfMonth();

                $holidays = $this->getHolidayMap($selectedYear);

                foreach ($serviceRoutes as $route) {
                    $routeTotals[$route->id] = 0;
                }

                $existingTrips = Trip::query()
                    ->with(['vehicle', 'driver', 'morningVehicle', 'eveningVehicle'])
                    ->where('company_id', $companyId)
                    ->whereDate('trip_date', '>=', $startOfMonth->toDateString())
                    ->whereDate('trip_date', '<=', $endOfMonth->toDateString())
                    ->whereIn('service_route_id', $serviceRoutes->pluck('id')->all())
                    ->get()
                    ->groupBy(function (Trip $trip) {
                        return $trip->trip_date->format('Y-m-d') . '_' . $trip->service_route_id;
                    });

                $cursor = $startOfMonth->copy();

                while ($cursor->lte($endOfMonth)) {
                    $dateKey = $cursor->format('Y-m-d');
                    $dayName = $this->getTurkishDayName($cursor);
                    $holidayInfo = $holidays[$dateKey] ?? null;
                    $isSaturday = $cursor->isSaturday();
                    $isSunday = $cursor->isSunday();
                    $isWeekend = $isSaturday || $isSunday;
                    $isHoliday = !is_null($holidayInfo);

                    $monthDays->push([
                        'date' => $cursor->copy(),
                        'date_key' => $dateKey,
                        'display_date' => $cursor->format('d.m.Y'),
                        'day' => $cursor->format('d'),
                        'day_name' => $dayName,
                        'is_saturday' => $isSaturday,
                        'is_sunday' => $isSunday,
                        'is_weekend' => $isWeekend,
                        'is_holiday' => $isHoliday,
                        'holiday_name' => $holidayInfo['name'] ?? null,
                        'holiday_type' => $holidayInfo['type'] ?? null,
                    ]);

                    foreach ($serviceRoutes as $route) {
                        $tripKey = $dateKey . '_' . $route->id;
                        $trip = $existingTrips->get($tripKey)?->first();

                        $enteredPrice = $trip?->trip_price !== null ? (float) $trip->trip_price : null;

                        $defaultMorningVehicleId = $route->morning_vehicle_id;
                        $defaultEveningVehicleId = $route->evening_vehicle_id;
                        $defaultMorningVehiclePlate = $route->morningVehicle?->plate;
                        $defaultEveningVehiclePlate = $route->eveningVehicle?->plate;

                        $formatDriver = function ($vehicle, $date) {
                            if (!$vehicle) return '';
                            $targetDate = $date->startOfDay();
                            $driver = $vehicle->drivers->filter(function($d) use ($targetDate) {
                                $start = $d->start_date ? Carbon::parse($d->start_date)->startOfDay() : null;
                                $leave = $d->leave_date ? Carbon::parse($d->leave_date)->startOfDay() : null;
                                if ($start && $targetDate->lt($start)) return false;
                                if ($leave && $targetDate->gt($leave)) return false;
                                return true;
                            })->first();

                            if (!$driver) return '';
                            $parts = explode(' ', trim($driver->full_name ?? $driver->name));
                            if (count($parts) > 1) {
                                $lastName = array_pop($parts);
                                return implode(' ', $parts) . ' ' . mb_substr($lastName, 0, 1) . '.';
                            }
                            return $parts[0];
                        };

                        $matrix[$dateKey][$route->id] = [
                            'trip_id' => $trip?->id,
                            'value' => $enteredPrice,
                            'display_value' => !is_null($enteredPrice) ? number_format($enteredPrice, 2, ',', '.') : '',
                            'trip_status' => $trip?->trip_status,
                            'vehicle_id' => $trip?->vehicle_id,
                            'vehicle_plate' => $trip?->vehicle?->plate,
                            'morning_vehicle_id' => $trip?->morning_vehicle_id,
                            'morning_vehicle_plate' => $trip?->morningVehicle?->plate,
                            'morning_driver_name' => $formatDriver($trip?->morningVehicle, $cursor),
                            'evening_vehicle_id' => $trip?->evening_vehicle_id,
                            'evening_vehicle_plate' => $trip?->eveningVehicle?->plate,
                            'evening_driver_name' => $formatDriver($trip?->eveningVehicle, $cursor),
                            'driver_id' => $trip?->driver_id,
                            'driver_name' => $trip?->driver?->full_name ?? $trip?->driver?->name,
                            'notes' => $trip?->notes,
                            'has_record' => !is_null($trip),
                            'is_weekend' => $isWeekend,
                            'is_holiday' => $isHoliday,
                            'default_morning_vehicle_id' => $defaultMorningVehicleId,
                            'default_morning_vehicle_plate' => $defaultMorningVehiclePlate,
                            'default_morning_driver_name' => $formatDriver($route->morningVehicle, $cursor),
                            'default_evening_vehicle_id' => $defaultEveningVehicleId,
                            'default_evening_vehicle_plate' => $defaultEveningVehiclePlate,
                            'default_evening_driver_name' => $formatDriver($route->eveningVehicle, $cursor),
                        ];

                        if (!is_null($enteredPrice)) {
                            $routeTotals[$route->id] += $enteredPrice;
                        }
                    }

                    $cursor->addDay();
                }

                $subtotal = collect($routeTotals)->sum();
                $vatRate = (float) ($selectedCustomer->vat_rate ?? 0);
                $vatAmount = $subtotal * ($vatRate / 100);
                $invoiceTotal = $subtotal + $vatAmount;

                $withholdingAmount = 0;
                $withholdingRate = $selectedCustomer->withholding_rate;

                if ($withholdingRate && str_contains($withholdingRate, '/')) {
                    [$numerator, $denominator] = array_pad(explode('/', $withholdingRate), 2, null);
                    $numerator = (float) $numerator;
                    $denominator = (float) $denominator;
                    if ($numerator > 0 && $denominator > 0) {
                        $withholdingAmount = $vatAmount * ($numerator / $denominator);
                    }
                }

                $summary = [
                    'subtotal' => $subtotal,
                    'vat_rate' => $vatRate,
                    'vat_amount' => $vatAmount,
                    'withholding_rate' => $withholdingRate,
                    'withholding_amount' => $withholdingAmount,
                    'grand_total' => $invoiceTotal,
                    'net_total' => $invoiceTotal - $withholdingAmount,
                ];
            }
        }

        $monthOptions = [
            1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan', 5 => 'Mayıs', 6 => 'Haziran',
            7 => 'Temmuz', 8 => 'Ağustos', 9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık',
        ];

        return [
            'companyId' => $companyId,
            'selectedCustomer' => $selectedCustomer,
            'serviceRoutes' => $serviceRoutes,
            'monthDays' => $monthDays,
            'matrix' => $matrix,
            'summary' => $summary,
            'routeTotals' => $routeTotals,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'monthOptions' => $monthOptions,
            'yearOptions' => range($now->year, 2023),
        ];
    }

    /**
     * Matrix verisini döndürür
     */
    public function matrix(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'trips.view')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $data = $this->getMatrixData($request);
        $companyId = $data['companyId'];

        $customers = Customer::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('company_name')
            ->get(['id', 'company_name']);

        $vehicles = Vehicle::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('plate')
            ->get(['id', 'plate']);

        return $this->successResponse([
            'customers' => $customers,
            'selectedCustomer' => $data['selectedCustomer'],
            'vehicles' => $vehicles,
            'serviceRoutes' => $data['serviceRoutes']->map(function ($route) {
                // Sadece gerekli alanları dön (Mobile payload boyutunu küçültmek için)
                $formatDriver = function ($vehicle) {
                    if (!$vehicle) return '';
                    $driver = $vehicle->drivers->where('is_active', true)->first() ?? $vehicle->drivers->first();
                    if (!$driver) return '';
                    $parts = explode(' ', trim($driver->full_name ?? $driver->name));
                    if (count($parts) > 1) {
                        $lastName = array_pop($parts);
                        return implode(' ', $parts) . ' ' . mb_substr($lastName, 0, 1) . '.';
                    }
                    return $parts[0];
                };
                
                return [
                    'id' => $route->id,
                    'route_name' => $route->route_name,
                    'morning_plate' => $route->morningVehicle?->plate,
                    'evening_plate' => $route->eveningVehicle?->plate,
                    'morning_driver' => $formatDriver($route->morningVehicle),
                    'evening_driver' => $formatDriver($route->eveningVehicle),
                ];
            }),
            'monthDays' => $data['monthDays'],
            'matrix' => $data['matrix'],
            'summary' => $data['summary'],
            'routeTotals' => $data['routeTotals'],
            'selectedMonth' => $data['selectedMonth'],
            'selectedYear' => $data['selectedYear'],
        ], 'Matrix verisi başarıyla getirildi.');
    }

    public function exportExcel(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'trips.view')) {
            return response()->json(['message' => 'Yetkisiz işlem.'], 403);
        }

        $viewData = $this->getMatrixData($request);
        if (!$viewData['selectedCustomer']) {
            return response()->json(['message' => 'Müşteri bulunamadı.'], 404);
        }

        $filename = str_replace(' ', '_', $viewData['selectedCustomer']->company_name) . '_' . $viewData['monthOptions'][$viewData['selectedMonth']] . '_Puantaj.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PuantajExport($viewData), $filename);
    }

    public function exportPdf(Request $request)
    {
        if (!$this->userHasPermission($request->user(), 'trips.view')) {
            return response()->json(['message' => 'Yetkisiz işlem.'], 403);
        }

        $viewData = $this->getMatrixData($request);
        if (!$viewData['selectedCustomer']) {
            return response()->json(['message' => 'Müşteri bulunamadı.'], 404);
        }

        // Blade görünümünü PDF'e çeviriyoruz
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('trips.export_pdf', $viewData)
                ->setPaper('a4', 'landscape');
                
        $filename = str_replace(' ', '_', $viewData['selectedCustomer']->company_name) . '_' . $viewData['monthOptions'][$viewData['selectedMonth']] . '_Puantaj.pdf';

        return $pdf->download($filename);
    }

    public function upsertCell(Request $request): JsonResponse
    {
        if (!$this->userHasPermission($request->user(), 'trips.edit') && !$this->userHasPermission($request->user(), 'trips.create')) {
            return $this->errorResponse('Bu işlem için yetkiniz bulunmamaktadır.', 403);
        }

        $validated = $request->validate([
            'service_route_id' => ['required', 'exists:customer_service_routes,id'],
            'trip_date' => ['required', 'date'],
            'trip_price' => ['nullable', 'numeric'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'morning_vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'evening_vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'trip_status' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $serviceRoute = CustomerServiceRoute::query()
            ->where('company_id', $this->getCompanyId())
            ->with(['morningVehicle', 'eveningVehicle'])
            ->findOrFail($validated['service_route_id']);

        $tripDate = Carbon::parse($validated['trip_date']);
        $tripPrice = array_key_exists('trip_price', $validated) ? $validated['trip_price'] : null;
        $driverId = $validated['driver_id'] ?? null;
        $notes = $validated['notes'] ?? null;
        $tripStatus = $validated['trip_status'] ?? 'Yapıldı';

        $defaultMorningVehicleId = $serviceRoute->morning_vehicle_id;
        $defaultEveningVehicleId = $serviceRoute->evening_vehicle_id;

        $morningVehicleId = $validated['morning_vehicle_id'] ?? null;
        $eveningVehicleId = $validated['evening_vehicle_id'] ?? null;
        $singleVehicleId = $validated['vehicle_id'] ?? null;

        if (!$morningVehicleId && !$eveningVehicleId) {
            $morningVehicleId = $defaultMorningVehicleId;
            $eveningVehicleId = $defaultEveningVehicleId;
        }

        if (!$morningVehicleId && $singleVehicleId) {
            $morningVehicleId = $singleVehicleId;
        }

        if (!$eveningVehicleId && $singleVehicleId) {
            $eveningVehicleId = $singleVehicleId;
        }

        $fallbackVehicleId = $morningVehicleId ?: $eveningVehicleId ?: $singleVehicleId;

        // EĞER FİYAT 0 VEYA BOŞSA: Kayıtlı özel plakaları sil ve varsayılana dön
        if ($tripPrice === 0 || $tripPrice === '0' || $tripPrice === null || $tripPrice === '') {
            Trip::where('service_route_id', $serviceRoute->id)
                ->where('company_id', $this->getCompanyId())
                ->where('trip_date', $tripDate->toDateString())
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Özel kayıt silindi, varsayılana dönüldü.',
                'deleted' => true,
            ]);
        }

        // EĞER ŞOFÖR BOŞ GELİRSE
        if (!$driverId && $fallbackVehicleId) {
            $autoDriver = \App\Models\Fleet\Driver::where('vehicle_id', $fallbackVehicleId)
                ->where('is_active', true)
                ->first();
            
            if ($autoDriver) {
                $driverId = $autoDriver->id;
            }
        }

        $trip = Trip::query()->updateOrCreate(
            [
                'service_route_id' => $serviceRoute->id,
                'trip_date' => $tripDate->toDateString(),
                'company_id' => $this->getCompanyId(),
            ],
            [
                'vehicle_id' => $fallbackVehicleId,
                'morning_vehicle_id' => $morningVehicleId,
                'evening_vehicle_id' => $eveningVehicleId,
                'driver_id' => $driverId,
                'trip_status' => $tripStatus,
                'trip_price' => $tripPrice,
                'notes' => $notes,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Puantaj hücresi kaydedildi.',
        ]);
    }

    private function getTurkishDayName(Carbon $date): string
    {
        return match ($date->dayOfWeek) {
            Carbon::SUNDAY => 'Pazar',
            Carbon::MONDAY => 'Pazartesi',
            Carbon::TUESDAY => 'Salı',
            Carbon::WEDNESDAY => 'Çarşamba',
            Carbon::THURSDAY => 'Perşembe',
            Carbon::FRIDAY => 'Cuma',
            Carbon::SATURDAY => 'Cumartesi',
            default => '',
        };
    }

    private function getHolidayMap(int $year): array
    {
        $holidays = [];
        $fixedHolidays = [
            ['date' => "{$year}-01-01", 'name' => 'Yılbaşı', 'type' => 'official'],
            ['date' => "{$year}-04-23", 'name' => '23 Nisan Ulusal Egemenlik ve Çocuk Bayramı', 'type' => 'official'],
            ['date' => "{$year}-05-01", 'name' => 'Emek ve Dayanışma Günü', 'type' => 'official'],
            ['date' => "{$year}-05-19", 'name' => '19 Mayıs Atatürk’ü Anma, Gençlik ve Spor Bayramı', 'type' => 'official'],
            ['date' => "{$year}-07-15", 'name' => '15 Temmuz Demokrasi ve Millî Birlik Günü', 'type' => 'official'],
            ['date' => "{$year}-08-30", 'name' => '30 Ağustos Zafer Bayramı', 'type' => 'official'],
            ['date' => "{$year}-10-29", 'name' => '29 Ekim Cumhuriyet Bayramı', 'type' => 'official'],
        ];

        foreach ($fixedHolidays as $holiday) {
            $holidays[$holiday['date']] = $holiday;
        }

        return $holidays;
    }
}
