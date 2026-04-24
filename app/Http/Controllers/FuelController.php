<?php

namespace App\Http\Controllers;

use App\Models\Fuel;
use App\Models\FuelStation;
use App\Models\Fleet\Vehicle;
use Illuminate\Http\Request;

class FuelController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasPermission('fuels.view'), 403);

        $fuels = Fuel::with(['vehicle', 'station'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $stationSummaries = FuelStation::with(['fuels', 'payments'])
            ->orderBy('name')
            ->get()
            ->map(function ($station) {
                $totalLiters = (float) $station->fuels->sum('liters');
                $totalAmount = (float) $station->fuels->sum('total_cost');
                $totalPaid = (float) $station->payments->sum('amount');

                return (object) [
                    'id' => $station->id,
                    'name' => $station->name,
                    'total_liters' => $totalLiters,
                    'total_amount' => $totalAmount,
                    'total_paid' => $totalPaid,
                    'current_debt' => $totalAmount - $totalPaid,
                ];
            });

        return view('fuels.index', compact('fuels', 'stationSummaries'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('fuels.create'), 403);

        $vehicles = Vehicle::orderBy('plate')->get();
        $stations = FuelStation::where('is_active', true)->orderBy('name')->get();

        return view('fuels.create', compact('vehicles', 'stations'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('fuels.create'), 403);

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'fuel_station_id' => 'nullable|exists:fuel_stations,id',
            'station_name' => 'nullable|string|max:255',
            'fuel_type' => 'required|string|max:50',
            'date' => 'required|date',
            'liters' => 'required|numeric|min:0',
            'price_per_liter' => 'required|numeric|min:0',
            'km' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        if (!empty($validated['fuel_station_id'])) {
            $station = FuelStation::find($validated['fuel_station_id']);
            $validated['station_name'] = $station?->name;
        }

        $pricing = $this->calculatePricing(
            (float) $validated['liters'],
            (float) $validated['price_per_liter'],
            $validated['fuel_station_id'] ?? null
        );

        $validated['gross_total_cost'] = $pricing['gross_total_cost'];
        $validated['discount_amount'] = $pricing['discount_amount'];
        $validated['total_cost'] = $pricing['total_cost'];

        Fuel::create($validated);

        return redirect()->route('fuels.index')->with('success', 'Yakıt kaydı başarıyla eklendi.');
    }

    public function show(Fuel $fuel)
    {
        abort_unless(auth()->user()->hasPermission('fuels.view'), 403);

        return view('fuels.show', compact('fuel'));
    }

    public function edit(Fuel $fuel)
    {
        abort_unless(auth()->user()->hasPermission('fuels.edit'), 403);

        $vehicles = Vehicle::orderBy('plate')->get();
        $stations = FuelStation::where('is_active', true)->orderBy('name')->get();

        return view('fuels.edit', compact('fuel', 'vehicles', 'stations'));
    }

    public function update(Request $request, Fuel $fuel)
    {
        abort_unless(auth()->user()->hasPermission('fuels.edit'), 403);

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'fuel_station_id' => 'nullable|exists:fuel_stations,id',
            'station_name' => 'nullable|string|max:255',
            'fuel_type' => 'required|string|max:50',
            'date' => 'required|date',
            'liters' => 'required|numeric|min:0',
            'price_per_liter' => 'required|numeric|min:0',
            'km' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        if (!empty($validated['fuel_station_id'])) {
            $station = FuelStation::find($validated['fuel_station_id']);
            $validated['station_name'] = $station?->name;
        }

        $pricing = $this->calculatePricing(
            (float) $validated['liters'],
            (float) $validated['price_per_liter'],
            $validated['fuel_station_id'] ?? null
        );

        $validated['gross_total_cost'] = $pricing['gross_total_cost'];
        $validated['discount_amount'] = $pricing['discount_amount'];
        $validated['total_cost'] = $pricing['total_cost'];

        $fuel->update($validated);

        return redirect()->route('fuels.index')->with('success', 'Yakıt kaydı güncellendi.');
    }

    public function destroy(Fuel $fuel)
    {
        abort_unless(auth()->user()->hasPermission('fuels.delete'), 403);

        $fuel->delete();

        return redirect()->route('fuels.index')->with('success', 'Yakıt kaydı silindi.');
    }

    protected function calculatePricing(float $liters, float $pricePerLiter, ?int $stationId = null): array
    {
        $grossTotal = round($liters * $pricePerLiter, 2);
        $discountAmount = 0;

        if ($stationId) {
            $station = FuelStation::find($stationId);

            if ($station && (float) $station->discount_value > 0) {
                if ($station->discount_type === 'percentage') {
                    $discountAmount = round($grossTotal * ((float) $station->discount_value / 100), 2);
                }

                if ($station->discount_type === 'fixed') {
                    $discountAmount = round((float) $station->discount_value, 2);
                }
            }
        }

        if ($discountAmount > $grossTotal) {
            $discountAmount = $grossTotal;
        }

        return [
            'gross_total_cost' => $grossTotal,
            'discount_amount' => $discountAmount,
            'total_cost' => round($grossTotal - $discountAmount, 2),
        ];
    }
}