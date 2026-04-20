<?php

namespace App\Http\Controllers;

use App\Models\RouteStop;
use App\Models\ServiceRoute;
use Illuminate\Http\Request;

class RouteStopController extends Controller
{
    public function index()
    {
        $routeStops = RouteStop::with('serviceRoute')->orderBy('service_route_id')->orderBy('stop_order')->get();
        return view('route-stops.index', compact('routeStops'));
    }

    public function create()
    {
        $serviceRoutes = ServiceRoute::orderBy('route_name')->get();
        return view('route-stops.create', compact('serviceRoutes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_route_id' => 'required|exists:service_routes,id',
            'stop_name' => 'required|string|max:255',
            'stop_order' => 'required|integer|min:1',
            'stop_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        RouteStop::create($validated);

        return redirect()->route('route-stops.index')->with('success', 'Durak başarıyla eklendi.');
    }

    public function show(RouteStop $routeStop)
    {
        return view('route-stops.show', compact('routeStop'));
    }

    public function edit(RouteStop $routeStop)
    {
        $serviceRoutes = ServiceRoute::orderBy('route_name')->get();
        return view('route-stops.edit', compact('routeStop', 'serviceRoutes'));
    }

    public function update(Request $request, RouteStop $routeStop)
    {
        $validated = $request->validate([
            'service_route_id' => 'required|exists:service_routes,id',
            'stop_name' => 'required|string|max:255',
            'stop_order' => 'required|integer|min:1',
            'stop_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $routeStop->update($validated);

        return redirect()->route('route-stops.index')->with('success', 'Durak güncellendi.');
    }

    public function destroy(RouteStop $routeStop)
    {
        $routeStop->delete();

        return redirect()->route('route-stops.index')->with('success', 'Durak silindi.');
    }
}