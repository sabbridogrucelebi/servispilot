<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ServiceRoute;
use App\Models\Fleet\Driver;
use App\Models\Fleet\Vehicle;
use Illuminate\Http\Request;

class ServiceRouteController extends Controller
{
    public function index()
    {
        $serviceRoutes = ServiceRoute::with(['customer', 'vehicle', 'driver'])->latest()->get();
        return view('service-routes.index', compact('serviceRoutes'));
    }

    public function create()
    {
        $customers = Customer::orderBy('company_name')->get();
        $vehicles = Vehicle::orderBy('plate')->get();
        $drivers = Driver::orderBy('full_name')->get();

        return view('service-routes.create', compact('customers', 'vehicles', 'drivers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'route_name' => 'required|string|max:255',
            'route_type' => 'nullable|string|max:100',
            'start_location' => 'nullable|string|max:255',
            'end_location' => 'nullable|string|max:255',
            'departure_time' => 'nullable',
            'arrival_time' => 'nullable',
            'price' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        ServiceRoute::create($validated);

        return redirect()->route('service-routes.index')->with('success', 'Servis hattı başarıyla eklendi.');
    }

    public function show(ServiceRoute $serviceRoute)
    {
        return view('service-routes.show', compact('serviceRoute'));
    }

    public function edit(ServiceRoute $serviceRoute)
    {
        $customers = Customer::orderBy('company_name')->get();
        $vehicles = Vehicle::orderBy('plate')->get();
        $drivers = Driver::orderBy('full_name')->get();

        return view('service-routes.edit', compact('serviceRoute', 'customers', 'vehicles', 'drivers'));
    }

    public function update(Request $request, ServiceRoute $serviceRoute)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'route_name' => 'required|string|max:255',
            'route_type' => 'nullable|string|max:100',
            'start_location' => 'nullable|string|max:255',
            'end_location' => 'nullable|string|max:255',
            'departure_time' => 'nullable',
            'arrival_time' => 'nullable',
            'price' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $serviceRoute->update($validated);

        return redirect()->route('service-routes.index')->with('success', 'Servis hattı güncellendi.');
    }

    public function destroy(ServiceRoute $serviceRoute)
    {
        $serviceRoute->delete();

        return redirect()->route('service-routes.index')->with('success', 'Servis hattı silindi.');
    }
}