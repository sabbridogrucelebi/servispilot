<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\Fleet\Driver;
use Carbon\Carbon;

class PayrollService
{
    /**
     */
    public function calculateMonthlyPayroll(Driver $driver, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $driverStart = $driver->start_date ? Carbon::parse($driver->start_date)->startOfDay() : null;
        $driverLeave = $driver->leave_date ? Carbon::parse($driver->leave_date)->endOfDay() : null;

        // Araç ID'si yoksa ama eski bir şoförse, son çalıştığı aracı bulmaya çalışalım
        $effectiveVehicleId = $driver->vehicle_id;
        if (!$effectiveVehicleId) {
            $lastTripWithVehicle = Trip::where('driver_id', $driver->id)
                ->whereNotNull('vehicle_id')
                ->orderBy('trip_date', 'desc')
                ->first();
            if ($lastTripWithVehicle) {
                $effectiveVehicleId = $lastTripWithVehicle->vehicle_id;
            }
        }

        $trips = Trip::with(['serviceRoute'])
            ->where(function ($q) use ($driver, $effectiveVehicleId) {
                // 1. Şoför ID'si doğrudan eşleşenler
                $q->where('driver_id', $driver->id);
                
                // 2. Şoför ID'si eşleşmese bile araç şoförün aracıysa (Tenure filtresi aşağıda hakedişi netleştirecek)
                if ($effectiveVehicleId) {
                    $q->orWhere(function ($sq) use ($effectiveVehicleId) {
                        $sq->where('vehicle_id', $effectiveVehicleId)
                           ->orWhere('morning_vehicle_id', $effectiveVehicleId)
                           ->orWhere('evening_vehicle_id', $effectiveVehicleId);
                    });
                }
            })
            // GÜVENLİK FİLTRESİ: Sadece şoförün çalıştığı tarih aralığındaki seferleri getir
            ->when($driverStart, function($q) use ($driverStart) {
                return $q->where('trip_date', '>=', $driverStart->toDateString());
            })
            ->when($driverLeave, function($q) use ($driverLeave) {
                return $q->where('trip_date', '<=', $driverLeave->toDateString());
            })
            ->whereBetween('trip_date', [$startDate, $endDate])
            ->get();

        // --- 1. ANA MAAŞ HESAPLAMA (TARİH BAZLI) ---
        // Kullanıcı isteği: Ana maaş puantaja göre değil, işe giriş/çıkış tarihlerine göre hesaplanır.
        
        $monthStart = $startDate->copy()->startOfDay();
        $monthEnd = $endDate->copy()->endOfDay();

        // Ay içindeki fiili çalışma başlangıç ve bitişini belirle
        $periodStart = $driverStart ? ($driverStart->gt($monthStart) ? $driverStart->copy() : $monthStart->copy()) : $monthStart->copy();
        $periodEnd = $driverLeave ? ($driverLeave->lt($monthEnd) ? $driverLeave->copy() : $monthEnd->copy()) : $monthEnd->copy();

        $workDays = 0;
        
        if ($periodStart->lte($periodEnd)) {
            // Takvim günü farkı (+1 ekleyerek kapsayıcı yapıyoruz)
            $workDays = (float) $periodStart->diffInDays($periodEnd->startOfDay()) + 1;

            // Vardiya düzeltmeleri (Yarım gün hakedişler)
            
            // Eğer bu ay işe başladıysa ve "Akşam" başladıysa sabahı düş
            if ($driverStart && $periodStart->equalTo($driverStart) && $driver->start_shift === 'evening') {
                $workDays -= 0.5;
            }

            // Eğer bu ay işten ayrıldıysa ve sadece "Sabah" veya "Akşam" yapıp bıraktıysa düzelt
            if ($driverLeave && $periodEnd->equalTo($driverLeave->startOfDay())) {
                if ($driver->leave_shift === 'morning') {
                    // Sadece sabah çalıştı, akşamı düş
                    $workDays -= 0.5;
                }
                // Not: 'evening' veya 'full_day' ise tam gün sayılır (düşüş yapılmaz)
            }
            
            // Ayın tamamını çalıştıysa (Ay başından ay sonuna kadar aktifse) 30 güne sabitle
            $isFullMonth = true;
            if ($driverStart && $driverStart->gt($monthStart)) $isFullMonth = false;
            if ($driverLeave && $driverLeave->lt($monthEnd)) $isFullMonth = false;
            
            if ($isFullMonth) {
                $workDays = 30.0;
            }
            
            // Hiçbir durumda 30 günü aşamaz (Kullanıcı 30 gün üzerinden baz alınacak dedi)
            if ($workDays > 30) {
                $workDays = 30.0;
            }
        }

        // --- 2. EKSTRA HAKEDİŞ HESAPLAMA (PUANTAJ BAZLI) ---
        $groupedDetails = [];
        $totalExtraEarnings = 0;
        
        foreach ($trips as $trip) {
            $route = $trip->serviceRoute;
            if (!$route) continue;

            // --- VARDİYA BAZLI HAKEDİŞ FİLTRESİ ---
            $tripDate = $trip->trip_date->startOfDay();
            $canDoMorning = true;
            $canDoEvening = true;

            // İşe giriş günü kontrolü
            if ($driverStart && $tripDate->equalTo($driverStart)) {
                if ($driver->start_shift === 'evening') {
                    $canDoMorning = false;
                }
            }

            // İşten ayrılma günü kontrolü
            if ($driverLeave && $tripDate->equalTo($driverLeave->startOfDay())) {
                if ($driver->leave_shift === 'morning') {
                    $canDoEvening = false;
                }
            }

            // Route service_type kontrolü
            if ($route->service_type === 'morning') {
                $canDoEvening = false;
            }
            if ($route->service_type === 'evening') {
                $canDoMorning = false;
            }

            // --- BACAĞI KİM SÜRDÜ KONTROLÜ ---
            $driverDroveMorning = false;
            $driverDroveEvening = false;

            if ($effectiveVehicleId) {
                if ((string)$trip->morning_vehicle_id === (string)$effectiveVehicleId) $driverDroveMorning = true;
                if ((string)$trip->evening_vehicle_id === (string)$effectiveVehicleId) $driverDroveEvening = true;
            }

            // Yeni Yapı: Farklı Şoför (Sabah/Akşam) manuel seçildiyse:
            if ($trip->morning_driver_id) {
                // Eğer manuel olarak bu bacak için şoför atanmışsa, 
                // bu değerlendirilen şoför O DEĞİLSE, aracın sahibi bile olsa gidememiştir.
                if ($trip->morning_driver_id === $driver->id) {
                    $driverDroveMorning = true;
                } else {
                    $driverDroveMorning = false;
                }
            }
            
            if ($trip->evening_driver_id) {
                // Aynı mantık akşam için:
                if ($trip->evening_driver_id === $driver->id) {
                    $driverDroveEvening = true;
                } else {
                    $driverDroveEvening = false;
                }
            }

            // Eski/Legacy Yapı: Tek bir driver_id manuel seçildiyse (Geriye Dönük Uyumluluk):
            if ($trip->driver_id && !$trip->morning_driver_id && !$trip->evening_driver_id) {
                if ($trip->driver_id === $driver->id) {
                    if ($trip->morning_vehicle_id) $driverDroveMorning = true;
                    if ($trip->evening_vehicle_id) $driverDroveEvening = true;
                    
                    if (empty($trip->morning_vehicle_id) && empty($trip->evening_vehicle_id) && $trip->vehicle_id) {
                         $driverDroveMorning = true;
                         $driverDroveEvening = true;
                    }
                } else {
                    $driverDroveMorning = false;
                    $driverDroveEvening = false;
                }
            }

            if (!$driverDroveMorning) $canDoMorning = false;
            if (!$driverDroveEvening) $canDoEvening = false;

            $morningEarning = 0;
            $eveningEarning = 0;

            // Ücret Hesaplama Mantığı (Ek Hakedişler)
            if ($route->fee_type === 'paid') {
                $morningEarning = $canDoMorning ? ($route->morning_fee ?? 0) : 0;
                $eveningEarning = $canDoEvening ? ($route->evening_fee ?? 0) : 0;
            } else {
                if ($canDoMorning && $trip->morning_vehicle_id && (string)$trip->morning_vehicle_id !== (string)$route->morning_vehicle_id) {
                    $morningEarning = $route->fallback_morning_fee ?? 0;
                }
                if ($canDoEvening && $trip->evening_vehicle_id && (string)$trip->evening_vehicle_id !== (string)$route->evening_vehicle_id) {
                    $eveningEarning = $route->fallback_evening_fee ?? 0;
                }
            }

            // Hafta sonu kontrolleri...
            if ($trip->trip_date->isSaturday()) {
                if ($route->saturday_pricing) {
                    if ($canDoMorning && $morningEarning == 0) $morningEarning = $route->fallback_morning_fee ?? 0;
                    if ($canDoEvening && $eveningEarning == 0) $eveningEarning = $route->fallback_evening_fee ?? 0;
                } else {
                    $morningEarning = 0;
                    $eveningEarning = 0;
                }
            }
            if ($trip->trip_date->isSunday()) {
                if ($route->sunday_pricing) {
                    if ($canDoMorning && $morningEarning == 0) $morningEarning = $route->fallback_morning_fee ?? 0;
                    if ($canDoEvening && $eveningEarning == 0) $eveningEarning = $route->fallback_evening_fee ?? 0;
                } else {
                    $morningEarning = 0;
                    $eveningEarning = 0;
                }
            }

            // KABALA (Sabit Maaş) Kontrolü: Şoför sabit maaşlıysa ek hakedişleri sıfırla
            if ($driver->is_fixed_salary) {
                $morningEarning = 0;
                $eveningEarning = 0;
            }

            $tripTotal = round($morningEarning + $eveningEarning, 2);
            
            // Eğer hakediş varsa VEYA şoför sabit maaşlı olup sefere gerçekten çıktıysa (canDoMorning/Evening true ise) rapora ekle
            if ($tripTotal > 0 || ($driver->is_fixed_salary && ($canDoMorning || $canDoEvening))) {
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
                
                $groupedDetails[$routeKey]['total_fee'] = round($groupedDetails[$routeKey]['total_fee'] + $tripTotal, 2);
                $groupedDetails[$routeKey]['dates'][] = [
                    'date' => $trip->trip_date->translatedFormat('d.m.Y l'),
                    'morning' => $morningEarning,
                    'evening' => $eveningEarning,
                    'total' => $tripTotal
                ];
                
                $totalExtraEarnings = round($totalExtraEarnings + $tripTotal, 2);
            }
        }

        $baseSalary = (float)($driver->base_salary ?? 0);
        $actualBaseSalary = round(($baseSalary / 30) * $workDays, 2);

        return [
            'base_salary' => $actualBaseSalary,
            'original_base_salary' => $baseSalary,
            'work_days' => $workDays,
            'extra_earnings' => $totalExtraEarnings,
            'net_salary' => round($actualBaseSalary + $totalExtraEarnings, 2),
            'details' => array_values($groupedDetails)
        ];
    }
}
