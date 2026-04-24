<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\VehicleTrackingSetting;

class VehicleTrackingController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasPermission('vehicles.view'), 403);

        $companyId = auth()->user()->company_id;
        $setting = VehicleTrackingSetting::where('company_id', $companyId)->where('is_active', true)->first();
        
        $vehicles = [];
        if ($setting && $setting->provider === 'arvento') {
            $arvento = new \App\Services\ArventoService($setting);
            $vehicles = $arvento->getVehicleStatus();
        }

        return view('vehicle-tracking.index', compact('setting', 'vehicles'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('vehicles.edit'), 403);

        $companyId = auth()->user()->company_id;

        $request->validate([
            'provider' => 'required|in:arvento,trio_mobil,mobiliz',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        VehicleTrackingSetting::updateOrCreate(
            ['company_id' => $companyId],
            [
                'provider' => $request->provider,
                'username' => $request->username,
                'password' => $request->password,
                'app_id' => $request->app_id,
                'app_key' => $request->app_key,
                'api_key' => $request->api_key,
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Araç takip sistem ayarları başarıyla kaydedildi.');
    }
}
