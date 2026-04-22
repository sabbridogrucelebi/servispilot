<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\Fleet\Driver;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Belirli bir ay ve personel için hakediş hesaplar.
     */
    public function calculateMonthlyPayroll(Driver $driver, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $trips = Trip::with(['serviceRoute'])
            ->where(function ($q) use ($driver) {
                $q->where('driver_id', $driver->id);
                
                // Şoför ID'si boşsa ama araç şoförün aracıysa yine ona yaz
                if ($driver->vehicle_id) {
                    $q->orWhere(function ($sq) use ($driver) {
                        $sq->whereNull('driver_id')
                           ->where('vehicle_id', $driver->vehicle_id);
                    });
                }
            })
            ->whereBetween('trip_date', [$startDate, $endDate])
            ->get();

        $groupedDetails = [];
        $totalExtraEarnings = 0;

        foreach ($trips as $trip) {
            $route = $trip->serviceRoute;
            if (!$route) continue;

            // ... (İşten Ayrılma Kontrolü kısmı aynı kalıyor)
            $isMorningOnly = false;
            if ($driver->leave_date) {
                $leaveDate = \Carbon\Carbon::parse($driver->leave_date);
                if ($trip->trip_date->gt($leaveDate)) continue;
                if ($trip->trip_date->equalTo($leaveDate) && $driver->leave_shift === 'morning') {
                    $isMorningOnly = true;
                }
            }

            $morningEarning = 0;
            $eveningEarning = 0;

            // Ücret Hesaplama Mantığı
            if ($route->fee_type === 'paid') {
                $morningEarning = $route->morning_fee ?? 0;
                $eveningEarning = $route->evening_fee ?? 0;
            } else {
                if ($trip->morning_vehicle_id && (string)$trip->morning_vehicle_id !== (string)$route->morning_vehicle_id) {
                    $morningEarning = $route->fallback_morning_fee ?? 0;
                }
                if ($trip->evening_vehicle_id && (string)$trip->evening_vehicle_id !== (string)$route->evening_vehicle_id) {
                    $eveningEarning = $route->fallback_evening_fee ?? 0;
                }
            }

            if ($isMorningOnly) $eveningEarning = 0;

            // Hafta sonu kontrolleri...
            if ($trip->trip_date->isSaturday() && $route->saturday_pricing) {
                if ($morningEarning == 0) $morningEarning = $route->fallback_morning_fee ?? 0;
                if ($eveningEarning == 0) $eveningEarning = $route->fallback_evening_fee ?? 0;
            }
            if ($trip->trip_date->isSunday() && $route->sunday_pricing) {
                if ($morningEarning == 0) $morningEarning = $route->fallback_morning_fee ?? 0;
                if ($eveningEarning == 0) $eveningEarning = $route->fallback_evening_fee ?? 0;
            }

            $tripTotal = $morningEarning + $eveningEarning;
            
            if ($tripTotal > 0) {
                $routeKey = $route->id;
                if (!isset($groupedDetails[$routeKey])) {
                    $groupedDetails[$routeKey] = [
                        'customer_name' => $route->customer?->company_name ?? 'Bilinmeyen Müşteri',
                        'route_name' => $route->route_name,
                        'morning_count' => 0,
                        'evening_count' => 0,
                        'total_fee' => 0,
                        'dates' => []
                    ];
                }

                if ($morningEarning > 0) $groupedDetails[$routeKey]['morning_count']++;
                if ($eveningEarning > 0) $groupedDetails[$routeKey]['evening_count']++;
                
                $groupedDetails[$routeKey]['total_fee'] += $tripTotal;
                $groupedDetails[$routeKey]['dates'][] = [
                    'date' => $trip->trip_date->format('d.m.Y'),
                    'morning' => $morningEarning,
                    'evening' => $eveningEarning,
                    'total' => $tripTotal
                ];
                
                $totalExtraEarnings += $tripTotal;
            }
        }

        return [
            'base_salary' => $driver->base_salary ?? 0,
            'extra_earnings' => $totalExtraEarnings,
            'net_salary' => ($driver->base_salary ?? 0) + $totalExtraEarnings,
            'details' => array_values($groupedDetails)
        ];
    }
}
