<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Company;
use App\Models\User;
use App\Models\CompanyModule;
use App\Models\GlobalAnnouncement;
use App\Enums\CompanyStatus;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\Driver;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCompanies   = Company::count();
        $trialCompanies   = Company::where('status', CompanyStatus::Trial)->count();
        $suspendedCompanies = Company::where('status', CompanyStatus::Suspended)->count();
        $activeAnnouncements = GlobalAnnouncement::where('is_active', true)->count();
        
        // Gerçek MRR ve Finansal Veriler
        $monthlyMrr = \App\Models\Subscription::where('status', 'active')
            ->where('billing_cycle', 'monthly')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.price');

        $yearlyMrr = \App\Models\Subscription::where('status', 'active')
            ->where('billing_cycle', 'yearly')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.yearly_price') / 12;

        $totalMrr = $monthlyMrr + $yearlyMrr;
        $totalRevenue = \App\Models\Invoice::where('status', 'paid')->sum('amount');

        // Tablo verisi için şirketler
        $companies = Company::with(['users' => function ($q) {
                $q->where('role', 'company_admin')->limit(1);
            }])
            ->withCount('vehicles', 'users')
            ->orderByDesc('created_at')
            ->get();
            
        $announcements = GlobalAnnouncement::orderByDesc('created_at')->get();

        // Eski tokenleri temizle ve yeni token üret
        auth()->user()->tokens()->where('name', 'web_admin_panel')->delete();
        $apiToken = auth()->user()->createToken('web_admin_panel')->plainTextToken;

        return view('super-admin.dashboard', compact(
            'totalCompanies',
            'trialCompanies',
            'suspendedCompanies',
            'activeAnnouncements',
            'totalMrr',
            'totalRevenue',
            'companies',
            'announcements',
            'apiToken'
        ));
    }
}
