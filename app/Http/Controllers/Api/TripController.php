<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $trips = Trip::where('company_id', $request->user()->company_id)
            ->with(['serviceRoute.customer', 'vehicle', 'driver'])
            ->latest('date')
            ->take(50)
            ->get();

        $formatted = $trips->map(function ($trip) {
            return [
                'id' => $trip->id,
                'date' => $trip->date->format('d.m.Y'),
                'customer' => $trip->serviceRoute ? $trip->serviceRoute->customer->name : 'Silinmiş',
                'route' => $trip->serviceRoute ? $trip->serviceRoute->name : 'Silinmiş',
                'vehicle_plate' => $trip->vehicle ? $trip->vehicle->plate : 'Silinmiş',
                'driver' => $trip->driver ? $trip->driver->full_name : 'Silinmiş',
            ];
        });

        return response()->json($formatted);
    }
}
