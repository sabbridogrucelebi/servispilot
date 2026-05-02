<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fleet\Driver;

class PersonnelController extends Controller
{
    public function index(Request $request)
    {
        $drivers = Driver::where('company_id', $request->user()->company_id)
            ->with('vehicle')
            ->orderBy('first_name')
            ->get();

        $formatted = $drivers->map(function ($driver) {
            return [
                'id' => $driver->id,
                'full_name' => $driver->full_name,
                'phone' => $driver->phone,
                'is_active' => $driver->is_active,
                'vehicle_plate' => $driver->vehicle ? $driver->vehicle->plate : 'Atanmamış',
            ];
        });

        return response()->json($formatted);
    }
}
