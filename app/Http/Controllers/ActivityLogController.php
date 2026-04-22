<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->filled('module'), function ($query) use ($request) {
                $query->where('module', $request->module);
            })
            ->when($request->filled('action'), function ($query) use ($request) {
                $query->where('action', $request->action);
            })
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->user_id);
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $modules = [
            'vehicles'     => 'Araçlar',
            'drivers'      => 'Personeller',
            'trips'        => 'Seferler',
            'fuels'        => 'Yakıtlar',
            'documents'    => 'Belgeler',
            'customers'    => 'Müşteriler',
            'maintenances' => 'Bakımlar',
            'penalties'    => 'Cezalar',
            'users'        => 'Kullanıcılar',
        ];

        $users = \App\Models\User::orderBy('name')->get();

        return view('activity-logs.index', compact('logs', 'modules', 'users'));
    }
}