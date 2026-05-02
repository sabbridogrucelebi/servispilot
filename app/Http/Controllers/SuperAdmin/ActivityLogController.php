<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with(['company', 'user'])->latest();

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        $logs = $query->paginate(50)->withQueryString();

        $companies = \App\Models\Company::orderBy('name')->get();

        return view('super-admin.logs.index', compact('logs', 'companies'));
    }
}
