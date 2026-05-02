<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Enums\CompanyStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\DB;

class SuperAdminCompanyApiController extends Controller
{
    // Lisans bilgilerini getir
    public function getLicense($id)
    {
        $company = Company::findOrFail($id);
        return response()->json([
            'license_type' => $company->license_type,
            'license_expires_at' => $company->license_expires_at,
            'max_vehicles' => $company->max_vehicles,
            'max_users' => $company->max_users,
        ]);
    }

    // Lisans güncelle
    public function updateLicense(Request $request, $id)
    {
        $request->validate([
            'license_type' => 'required|string',
            'license_expires_at' => 'nullable|date',
            'max_vehicles' => 'required|integer|min:1',
            'max_users' => 'required|integer|min:1',
        ]);

        $company = Company::findOrFail($id);
        $company->update($request->only('license_type', 'license_expires_at', 'max_vehicles', 'max_users'));

        return response()->json(['message' => 'License updated successfully', 'company' => $company]);
    }

    // Modülleri getir
    public function getModules($id)
    {
        $company = Company::with('modules')->findOrFail($id);
        return response()->json($company->modules);
    }

    // Modülleri güncelle (Toplu atama)
    public function updateModules(Request $request, $id)
    {
        $request->validate([
            'modules' => 'required|array',
            'modules.*.module_key' => 'required|string',
            'modules.*.is_active' => 'required|boolean',
        ]);

        $company = Company::findOrFail($id);
        
        DB::transaction(function () use ($company, $request) {
            foreach ($request->modules as $mod) {
                $company->modules()->updateOrCreate(
                    ['module_key' => $mod['module_key']],
                    ['is_active' => $mod['is_active']]
                );
            }
        });

        return response()->json(['message' => 'Modules updated successfully', 'modules' => $company->modules()->get()]);
    }

    // Status güncelle
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', new Enum(CompanyStatus::class)],
        ]);

        $company = Company::findOrFail($id);
        $company->status = $request->status;
        $company->save();

        return response()->json(['message' => 'Company status updated', 'status' => $company->status]);
    }

    // Impersonation
    public function impersonate(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        
        // Find the primary company admin
        $admin = User::where('company_id', $company->id)
                     ->where('role', 'company_admin')
                     ->first();
                     
        if (!$admin) {
            return response()->json(['message' => 'No company admin found for this company'], 404);
        }

        // Log the action (kendi Log Activity modelimizi kullanarak veya paket kullanarak, bizde LogsActivity var ama activity() helper'i var mı? 
        // Bakalım Spatie Activitylog kurulu mu. composer.json'da spatie/laravel-activitylog yok. 
        // App\Models\ActivityLog var. Biz elle insert edelim)
        \App\Models\ActivityLog::create([
            'user_id' => $request->user()->id,
            'company_id' => $company->id,
            'module' => 'Platform Yönetimi',
            'action' => 'impersonate_company',
            'title' => 'Süper Admin Girişi (Impersonation)',
            'description' => "Super Admin logged in as Company Admin ({$admin->email}) for company: {$company->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $token = $admin->createToken('impersonation_token', ['*'])->plainTextToken;

        return response()->json([
            'message' => 'Impersonation successful',
            'token' => $token,
            'user' => $admin
        ]);
    }
}
