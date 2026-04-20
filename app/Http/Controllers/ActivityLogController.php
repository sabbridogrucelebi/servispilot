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
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('activity-logs.index', compact('logs'));
    }
}