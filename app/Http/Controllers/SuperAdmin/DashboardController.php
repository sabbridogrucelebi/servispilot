<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Company;
use App\Models\User;
use App\Models\CompanyModule;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\Driver;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCompanies   = Company::count();
        $activeCompanies  = Company::where('is_active', true)->count();
        $passiveCompanies = $totalCompanies - $activeCompanies;

        $totalUsers    = User::where('is_super_admin', false)->count();
        $totalVehicles = Vehicle::withoutGlobalScopes()->count();
        $totalDrivers  = Driver::withoutGlobalScopes()->count();

        // Lisans durumu
        $expiredLicenses = Company::where('is_active', true)
            ->whereNotNull('license_expires_at')
            ->where('license_expires_at', '<', now())
            ->count();

        $expiringIn30Days = Company::where('is_active', true)
            ->whereNotNull('license_expires_at')
            ->where('license_expires_at', '>=', now())
            ->where('license_expires_at', '<=', now()->addDays(30))
            ->count();

        // Son eklenen firmalar
        $recentCompanies = Company::with(['users' => function ($q) {
                $q->where('role', 'company_admin')->limit(1);
            }])
            ->withCount('users', 'vehicles', 'drivers')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('super-admin.dashboard', compact(
            'totalCompanies',
            'activeCompanies',
            'passiveCompanies',
            'totalUsers',
            'totalVehicles',
            'totalDrivers',
            'expiredLicenses',
            'expiringIn30Days',
            'recentCompanies'
        ));
    }
}
