<?php

namespace App\Services;

use App\Models\Trip;

class TripService
{
    /**
     * Şirkete ait tüm seferleri (N+1 engellenmiş şekilde) listeler.
     */
    public function getTripList($companyId)
    {
        return Trip::with([
                'serviceRoute.customer', 
                'driver:id,full_name', 
                'vehicle:id,plate',
                'morningVehicle:id,plate',
                'eveningVehicle:id,plate'
            ])
            ->where('company_id', $companyId)
            ->orderBy('trip_date', 'desc')
            ->orderBy('id', 'desc')
            ->take(200) // Performans için son 200 sefer
            ->get()
            ->map(function ($trip) {
                return [
                    'id' => $trip->id,
                    'trip_date' => $trip->trip_date ? $trip->trip_date->format('d.m.Y') : '-',
                    'vehicle_plate' => $trip->display_vehicle_plate ?: 'Atanmamış',
                    'customer_name' => $trip->serviceRoute && $trip->serviceRoute->customer ? $trip->serviceRoute->customer->company_name : 'Bilinmeyen Müşteri',
                    'route_name' => $trip->serviceRoute ? $trip->serviceRoute->route_name : 'Bilinmeyen Rota',
                    'driver_name' => $trip->driver ? $trip->driver->full_name : 'Şoför Atanmamış'
                ];
            });
    }

    /**
     * Belirli bir seferin tüm detaylarını getirir.
     */
    public function getTripDetail($companyId, $tripId)
    {
        return Trip::with([
                'serviceRoute.customer', 
                'driver', 
                'vehicle',
                'morningVehicle',
                'eveningVehicle'
            ])
            ->where('company_id', $companyId)
            ->where('id', $tripId)
            ->first();
    }
}
