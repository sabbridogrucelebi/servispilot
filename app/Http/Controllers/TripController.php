<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Customer;
use App\Models\CustomerServiceRoute;
use App\Models\Fleet\Driver;
use App\Models\Fleet\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('run_migrate')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                echo "Veritabanı başarıyla güncellendi! Çıktı:<br><pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";
                exit;
            } catch (\Exception $e) {
                echo "Hata oluştu: " . $e->getMessage();
                exit;
            }
        }

        abort_unless(auth()->user()->hasPermission('trips.view'), 403);

        $now = now();

        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('company_name')
            ->get();

        $vehicles = Vehicle::query()
            ->where('is_active', true)
            ->orderBy('plate')
            ->get(['id', 'plate', 'brand', 'model']);

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
            'subtotal' => 0,
            'vat_rate' => 0,
            'vat_amount' => 0,
            'withholding_rate' => null,
            'withholding_amount' => 0,
            'grand_total' => 0,
            'net_total' => 0,
        ];
        
        $drivers = Driver::query()
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        if ($selectedCustomerId) {
            $selectedCustomer = Customer::query()
                ->where('is_active', true)
                ->whereKey($selectedCustomerId)
                ->first();

            if ($selectedCustomer) {
                $serviceRoutes = CustomerServiceRoute::query()
                    ->with(['morningVehicle.drivers', 'eveningVehicle.drivers'])
                    ->where('customer_id', $selectedCustomer->id)
                    ->where('is_active', true)
                    ->orderBy('id', 'asc')
                    ->get();

                $startOfMonth = Carbon::create($selectedYear, $selectedMonth, 1)->startOfDay();
                $endOfMonth = $startOfMonth->copy()->endOfMonth();

                $holidays = $this->getHolidayMap($selectedYear);

                foreach ($serviceRoutes as $route) {
                    $routeTotals[$route->id] = 0;
                }

                $existingTrips = Trip::query()
                    ->with([
                        'vehicle',
                        'driver',
                        'morningVehicle',
                        'eveningVehicle',
                    ])
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
                            // Bu tarihte bu araçta çalışan şoförü bul (Giriş/Çıkış tarihlerine göre)
                            $targetDate = $date->startOfDay();
                            $driver = $vehicle->drivers->filter(function($d) use ($targetDate) {
                                $start = $d->start_date ? Carbon::parse($d->start_date)->startOfDay() : null;
                                $leave = $d->leave_date ? Carbon::parse($d->leave_date)->startOfDay() : null;
                                
                                // İşe başlamış mı?
                                if ($start && $targetDate->lt($start)) return false;
                                // İşten ayrılmış mı?
                                if ($leave && $targetDate->gt($leave)) return false;
                                
                                return true;
                            })->first();

                            if (!$driver) return '';

                            $parts = explode(' ', trim($driver->full_name));
                            if (count($parts) > 1) {
                                $lastName = array_pop($parts);
                                $firstName = implode(' ', $parts);
                                return $firstName . ' ' . mb_substr($lastName, 0, 1) . '.';
                            }
                            return $parts[0];
                        };

                        $matrix[$dateKey][$route->id] = [
                            'trip_id' => $trip?->id,
                            'value' => $enteredPrice,
                            'display_value' => !is_null($enteredPrice)
                                ? number_format($enteredPrice, 2, ',', '.')
                                : '',
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
                            'morning_driver_id' => $trip?->morning_driver_id,
                            'evening_driver_id' => $trip?->evening_driver_id,
                            'driver_name' => $trip?->driver?->full_name,
                            'morning_manual_driver_name' => $trip?->morningDriver?->full_name,
                            'evening_manual_driver_name' => $trip?->eveningDriver?->full_name,
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

                $netTotal = $invoiceTotal - $withholdingAmount;

                $summary = [
                    'subtotal' => $subtotal,
                    'vat_rate' => $vatRate,
                    'vat_amount' => $vatAmount,
                    'withholding_rate' => $withholdingRate,
                    'withholding_amount' => $withholdingAmount,
                    'grand_total' => $invoiceTotal,
                    'net_total' => $netTotal,
                ];
            }
        }

        $monthOptions = [
            1 => 'Ocak',
            2 => 'Şubat',
            3 => 'Mart',
            4 => 'Nisan',
            5 => 'Mayıs',
            6 => 'Haziran',
            7 => 'Temmuz',
            8 => 'Ağustos',
            9 => 'Eylül',
            10 => 'Ekim',
            11 => 'Kasım',
            12 => 'Aralık',
        ];

        $yearOptions = range(now()->year, 2023); // En yüksek yıl (şu anki yıl) en üstte görünsün

        $viewData = [
            'customers' => $customers,
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'selectedCustomerId' => $selectedCustomerId,
            'selectedCustomer' => $selectedCustomer,
            'serviceRoutes' => $serviceRoutes,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'monthDays' => $monthDays,
            'matrix' => $matrix,
            'monthOptions' => $monthOptions,
            'yearOptions' => $yearOptions,
            'summary' => $summary,
            'routeTotals' => $routeTotals,
        ];

        if ($request->get('export') === 'excel' && $selectedCustomer) {
            if ($request->filled('hidden_routes')) {
                $hiddenIds = explode(',', $request->get('hidden_routes'));
                $viewData['serviceRoutes'] = $viewData['serviceRoutes']->reject(function($route) use ($hiddenIds) {
                    return in_array((string)$route->id, $hiddenIds);
                })->values(); // reset keys
            }
            $filename = str_replace(' ', '_', $selectedCustomer->company_name) . '_' . $monthOptions[$selectedMonth] . '_Puantaj.xlsx';
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PuantajExport($viewData), $filename);
        }

        return view('trips.index', $viewData);
    }

    public function upsertCell(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->hasPermission('trips.edit') || auth()->user()->hasPermission('trips.create'), 403);

        $validated = $request->validate([
            'service_route_id' => ['required', 'exists:customer_service_routes,id'],
            'trip_date' => ['required', 'date'],
            'trip_price' => ['nullable', 'numeric'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'morning_vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'evening_vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'morning_driver_id' => ['nullable', 'exists:drivers,id'],
            'evening_driver_id' => ['nullable', 'exists:drivers,id'],
            'trip_status' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $serviceRoute = CustomerServiceRoute::query()
            ->with(['morningVehicle', 'eveningVehicle'])
            ->findOrFail($validated['service_route_id']);

        $tripDate = Carbon::parse($request->input('trip_date'));
        $tripPrice = $request->input('trip_price');
        
        $morningVehicleId = $request->input('morning_vehicle_id');
        $eveningVehicleId = $request->input('evening_vehicle_id');
        $driverId = $request->input('driver_id');
        $morningDriverId = $request->input('morning_driver_id');
        $eveningDriverId = $request->input('evening_driver_id');
        $tripStatus = $request->input('trip_status', 'Yapıldı');
        $notes = $request->input('notes');

        // trip_price zorunluluğunu kaldırıyoruz, boş olsa bile kayıt edilebilir.
        if (array_key_exists('trip_price', $validated) && ($validated['trip_price'] === '' || $validated['trip_price'] === null)) {
            // Eğer özellikle silmek istiyorsa trip_status = 'İptal' veya benzeri bir mantık kurulabilir.
            // Ama şimdilik fiyat boşsa sadece fiyatı null yapıp aracı kaydedeceğiz.
        }

        $defaultMorningVehicleId = $serviceRoute->morning_vehicle_id;
        $defaultEveningVehicleId = $serviceRoute->evening_vehicle_id;

        $morningVehicleId = $validated['morning_vehicle_id'] ?? $morningVehicleId;
        $eveningVehicleId = $validated['evening_vehicle_id'] ?? $eveningVehicleId;
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
                ->where('trip_date', $tripDate->toDateString())
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Özel kayıt silindi, varsayılana dönüldü.',
                'trip' => [
                    'id' => null,
                    'trip_date' => $tripDate->format('Y-m-d'),
                    'trip_price' => null,
                    'display_trip_price' => '',
                    'trip_status' => 'Yapıldı',
                    'vehicle_id' => null,
                    'vehicle_plate' => $serviceRoute->morningVehicle?->plate ?: $serviceRoute->eveningVehicle?->plate,
                    'morning_vehicle_id' => $defaultMorningVehicleId,
                    'morning_vehicle_plate' => $serviceRoute->morningVehicle?->plate,
                    'evening_vehicle_id' => $defaultEveningVehicleId,
                    'evening_vehicle_plate' => $serviceRoute->eveningVehicle?->plate,
                    'driver_id' => null,
                    'driver_name' => null,
                    'notes' => null,
                    'default_morning_vehicle_id' => $defaultMorningVehicleId,
                    'default_morning_vehicle_plate' => $serviceRoute->morningVehicle?->plate,
                    'default_evening_vehicle_id' => $defaultEveningVehicleId,
                    'default_evening_vehicle_plate' => $serviceRoute->eveningVehicle?->plate,
                ],
            ]);
        }

        // AUTO-FILL İPTAL EDİLDİ:
        // Artık sabah ve akşam araçları ayrı ayrı şoförlerle eşleştiği için
        // driver_id alanını otomatik doldurmuyoruz. PayrollService doğrudan
        // vehicle_id'ler üzerinden veya morning_driver_id/evening_driver_id üzerinden 
        // eşleştirme yapacak.

        $trip = Trip::query()->updateOrCreate(
            [
                'service_route_id' => $serviceRoute->id,
                'trip_date' => $tripDate->toDateString(),
            ],
            [
                'vehicle_id' => $fallbackVehicleId,
                'morning_vehicle_id' => $morningVehicleId,
                'evening_vehicle_id' => $eveningVehicleId,
                'driver_id' => $driverId,
                'morning_driver_id' => $morningDriverId,
                'evening_driver_id' => $eveningDriverId,
                'trip_status' => $tripStatus,
                'trip_price' => $tripPrice,
                'notes' => $notes,
                'company_id' => $serviceRoute->company_id,
            ]
        );

        $trip->load([
            'vehicle',
            'driver',
            'morningVehicle',
            'eveningVehicle',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Puantaj hücresi kaydedildi.',
            'trip' => [
                'id' => $trip->id,
                'trip_date' => $trip->trip_date?->format('Y-m-d'),
                'trip_price' => $trip->trip_price !== null ? (float) $trip->trip_price : null,
                'display_trip_price' => $trip->trip_price !== null
                    ? number_format((float) $trip->trip_price, 2, ',', '.')
                    : '',
                'trip_status' => $trip->trip_status,
                'vehicle_id' => $trip->vehicle_id,
                'vehicle_plate' => $trip->vehicle?->plate,
                'morning_vehicle_id' => $trip->morning_vehicle_id,
                'morning_vehicle_plate' => $trip->morningVehicle?->plate,
                'evening_vehicle_id' => $trip->evening_vehicle_id,
                'evening_vehicle_plate' => $trip->eveningVehicle?->plate,
                'driver_id' => $trip->driver_id,
                'driver_name' => $trip->driver?->full_name,
                'notes' => $trip->notes,
                'default_morning_vehicle_id' => $defaultMorningVehicleId,
                'default_morning_vehicle_plate' => $serviceRoute->morningVehicle?->plate,
                'default_evening_vehicle_id' => $defaultEveningVehicleId,
                'default_evening_vehicle_plate' => $serviceRoute->eveningVehicle?->plate,
            ],
        ]);
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('trips.create'), 403);

        $serviceRoutes = CustomerServiceRoute::query()
            ->orderBy('route_name')
            ->get();

        $vehicles = Vehicle::query()
            ->where('is_active', true)
            ->orderBy('plate')
            ->get();

        $drivers = Driver::query()
            ->orderBy('full_name')
            ->get();

        return view('trips.create', compact('serviceRoutes', 'vehicles', 'drivers'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('trips.create'), 403);

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

        $route = \App\Models\CustomerServiceRoute::find($validated['service_route_id']);
        if ($route) {
            $validated['company_id'] = $route->company_id;
        }

        Trip::create($validated);

        return redirect()->route('trips.index')->with('success', 'Sefer başarıyla eklendi.');
    }

    public function show(Trip $trip)
    {
        abort_unless(auth()->user()->hasPermission('trips.view'), 403);

        $trip->load(['serviceRoute', 'vehicle', 'driver', 'morningVehicle', 'eveningVehicle']);

        return view('trips.show', compact('trip'));
    }

    public function edit(Trip $trip)
    {
        abort_unless(auth()->user()->hasPermission('trips.edit'), 403);

        $serviceRoutes = CustomerServiceRoute::query()
            ->orderBy('route_name')
            ->get();

        $vehicles = Vehicle::query()
            ->where('is_active', true)
            ->orderBy('plate')
            ->get();

        $drivers = Driver::query()
            ->orderBy('full_name')
            ->get();

        return view('trips.edit', compact('trip', 'serviceRoutes', 'vehicles', 'drivers'));
    }

    public function update(Request $request, Trip $trip)
    {
        abort_unless(auth()->user()->hasPermission('trips.edit'), 403);

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

        $route = \App\Models\CustomerServiceRoute::find($validated['service_route_id']);
        if ($route) {
            $validated['company_id'] = $route->company_id;
        }

        $trip->update($validated);

        return redirect()->route('trips.index')->with('success', 'Sefer güncellendi.');
    }

    public function destroy(Trip $trip)
    {
        abort_unless(auth()->user()->hasPermission('trips.delete'), 403);

        $trip->delete();

        return redirect()->route('trips.index')->with('success', 'Sefer silindi.');
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

        $dynamicReligiousHolidays = [
            2025 => [
                ['date' => '2025-03-30', 'name' => 'Ramazan Bayramı 1. Gün', 'type' => 'religious'],
                ['date' => '2025-03-31', 'name' => 'Ramazan Bayramı 2. Gün', 'type' => 'religious'],
                ['date' => '2025-04-01', 'name' => 'Ramazan Bayramı 3. Gün', 'type' => 'religious'],
                ['date' => '2025-06-06', 'name' => 'Kurban Bayramı 1. Gün', 'type' => 'religious'],
                ['date' => '2025-06-07', 'name' => 'Kurban Bayramı 2. Gün', 'type' => 'religious'],
                ['date' => '2025-06-08', 'name' => 'Kurban Bayramı 3. Gün', 'type' => 'religious'],
                ['date' => '2025-06-09', 'name' => 'Kurban Bayramı 4. Gün', 'type' => 'religious'],
            ],
            2026 => [
                ['date' => '2026-03-20', 'name' => 'Ramazan Bayramı 1. Gün', 'type' => 'religious'],
                ['date' => '2026-03-21', 'name' => 'Ramazan Bayramı 2. Gün', 'type' => 'religious'],
                ['date' => '2026-03-22', 'name' => 'Ramazan Bayramı 3. Gün', 'type' => 'religious'],
                ['date' => '2026-05-27', 'name' => 'Kurban Bayramı 1. Gün', 'type' => 'religious'],
                ['date' => '2026-05-28', 'name' => 'Kurban Bayramı 2. Gün', 'type' => 'religious'],
                ['date' => '2026-05-29', 'name' => 'Kurban Bayramı 3. Gün', 'type' => 'religious'],
                ['date' => '2026-05-30', 'name' => 'Kurban Bayramı 4. Gün', 'type' => 'religious'],
            ],
            2027 => [
                ['date' => '2027-03-10', 'name' => 'Ramazan Bayramı 1. Gün', 'type' => 'religious'],
                ['date' => '2027-03-11', 'name' => 'Ramazan Bayramı 2. Gün', 'type' => 'religious'],
                ['date' => '2027-03-12', 'name' => 'Ramazan Bayramı 3. Gün', 'type' => 'religious'],
                ['date' => '2027-05-16', 'name' => 'Kurban Bayramı 1. Gün', 'type' => 'religious'],
                ['date' => '2027-05-17', 'name' => 'Kurban Bayramı 2. Gün', 'type' => 'religious'],
                ['date' => '2027-05-18', 'name' => 'Kurban Bayramı 3. Gün', 'type' => 'religious'],
                ['date' => '2027-05-19', 'name' => 'Kurban Bayramı 4. Gün', 'type' => 'religious'],
            ],
        ];

        foreach ($dynamicReligiousHolidays[$year] ?? [] as $holiday) {
            $holidays[$holiday['date']] = $holiday;
        }

        return $holidays;
    }
}