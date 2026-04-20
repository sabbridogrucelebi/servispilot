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

        if ($selectedCustomerId) {
            $selectedCustomer = Customer::query()
                ->where('is_active', true)
                ->whereKey($selectedCustomerId)
                ->first();

            if ($selectedCustomer) {
                $serviceRoutes = CustomerServiceRoute::query()
                    ->with(['morningVehicle', 'eveningVehicle'])
                    ->where('customer_id', $selectedCustomer->id)
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
                            'evening_vehicle_id' => $trip?->evening_vehicle_id,
                            'evening_vehicle_plate' => $trip?->eveningVehicle?->plate,
                            'driver_id' => $trip?->driver_id,
                            'driver_name' => $trip?->driver?->full_name,
                            'notes' => $trip?->notes,
                            'has_record' => !is_null($trip),
                            'is_weekend' => $isWeekend,
                            'is_holiday' => $isHoliday,
                            'default_morning_vehicle_id' => $defaultMorningVehicleId,
                            'default_morning_vehicle_plate' => $defaultMorningVehiclePlate,
                            'default_evening_vehicle_id' => $defaultEveningVehicleId,
                            'default_evening_vehicle_plate' => $defaultEveningVehiclePlate,
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

        $yearOptions = range($now->year + 3, 2020);

        return view('trips.index', [
            'customers' => $customers,
            'vehicles' => $vehicles,
            'selectedCustomer' => $selectedCustomer,
            'selectedCustomerId' => $selectedCustomerId,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'monthOptions' => $monthOptions,
            'yearOptions' => $yearOptions,
            'serviceRoutes' => $serviceRoutes,
            'monthDays' => $monthDays,
            'matrix' => $matrix,
            'routeTotals' => $routeTotals,
            'summary' => $summary,
        ]);
    }

    public function upsertCell(Request $request): JsonResponse
    {
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
            ->with(['morningVehicle', 'eveningVehicle'])
            ->findOrFail($validated['service_route_id']);

        $tripDate = Carbon::parse($validated['trip_date']);
        $tripPrice = array_key_exists('trip_price', $validated) ? $validated['trip_price'] : null;
        $driverId = $validated['driver_id'] ?? null;
        $notes = $validated['notes'] ?? null;
        $tripStatus = $validated['trip_status'] ?? 'Yapıldı';

        if ($tripPrice === null || $tripPrice === '') {
            $existingTrip = Trip::query()
                ->where('service_route_id', $serviceRoute->id)
                ->whereDate('trip_date', $tripDate->toDateString())
                ->first();

            if ($existingTrip) {
                $existingTrip->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Hücre kaydı temizlendi.',
                'deleted' => true,
            ]);
        }

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
                'trip_status' => $tripStatus,
                'trip_price' => $tripPrice,
                'notes' => $notes,
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

        Trip::create($validated);

        return redirect()->route('trips.index')->with('success', 'Sefer başarıyla eklendi.');
    }

    public function show(Trip $trip)
    {
        $trip->load(['serviceRoute', 'vehicle', 'driver', 'morningVehicle', 'eveningVehicle']);

        return view('trips.show', compact('trip'));
    }

    public function edit(Trip $trip)
    {
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

        return redirect()->route('trips.index')->with('success', 'Sefer güncellendi.');
    }

    public function destroy(Trip $trip)
    {
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