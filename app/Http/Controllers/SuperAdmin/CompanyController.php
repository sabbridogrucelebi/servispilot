<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Company;
use App\Models\User;
use App\Models\Permission;
use App\Models\CompanyModule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::withCount('users', 'vehicles', 'drivers');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $companies = $query->orderByDesc('created_at')->paginate(20);

        return view('super-admin.companies.index', compact('companies'));
    }

    public function create()
    {
        $modules = CompanyModule::ALL_MODULES;

        return view('super-admin.companies.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'phone'              => ['nullable', 'string', 'max:50'],
            'email'              => ['nullable', 'email', 'max:255'],
            'tax_no'             => ['nullable', 'string', 'max:100'],
            'city'               => ['nullable', 'string', 'max:100'],
            'address'            => ['nullable', 'string'],
            'license_type'       => ['required', 'in:trial,standard,premium'],
            'license_expires_at' => ['nullable', 'date'],
            'max_vehicles'       => ['required', 'integer', 'min:1'],
            'max_users'          => ['required', 'integer', 'min:1'],
            'modules'            => ['nullable', 'array'],
            'modules.*'          => ['string'],
            // Admin kullanıcı bilgileri
            'admin_name'         => ['required', 'string', 'max:255'],
            'admin_email'        => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password'     => ['required', 'string', 'min:8'],
        ]);

        $company = Company::create([
            'name'               => $validated['name'],
            'slug'               => Str::slug($validated['name'] . '-' . now()->timestamp),
            'phone'              => $validated['phone'] ?? null,
            'email'              => $validated['email'] ?? null,
            'tax_no'             => $validated['tax_no'] ?? null,
            'city'               => $validated['city'] ?? null,
            'address'            => $validated['address'] ?? null,
            'is_active'          => $request->has('is_active'),
            'license_type'       => $validated['license_type'],
            'license_expires_at' => $validated['license_expires_at'] ?? null,
            'max_vehicles'       => $validated['max_vehicles'],
            'max_users'          => $validated['max_users'],
        ]);

        // Modülleri ata
        $selectedModules = $validated['modules'] ?? array_keys(CompanyModule::ALL_MODULES);
        foreach ($selectedModules as $moduleKey) {
            $company->modules()->create([
                'module_key' => $moduleKey,
                'is_active'  => true,
            ]);
        }

        // Admin kullanıcı oluştur
        $adminUser = User::create([
            'company_id' => $company->id,
            'name'       => $validated['admin_name'],
            'email'      => $validated['admin_email'],
            'password'   => $validated['admin_password'],
            'role'       => 'company_admin',
            'is_active'  => true,
        ]);

        // Admin'e tüm yetkileri ver
        $permissionIds = Permission::pluck('id')->toArray();
        $adminUser->permissions()->sync($permissionIds);

        return redirect()
            ->route('super-admin.companies.index')
            ->with('success', $company->name . ' firması ve yönetici kullanıcısı başarıyla oluşturuldu.');
    }

    public function show(Company $company)
    {
        $company->loadCount('users', 'vehicles', 'drivers', 'customers');
        $company->load('modules');

        $users = User::where('company_id', $company->id)
            ->orderBy('name')
            ->get();

        $allModules = CompanyModule::ALL_MODULES;
        $activeModuleKeys = $company->modules
            ->where('is_active', true)
            ->pluck('module_key')
            ->toArray();

        return view('super-admin.companies.show', compact(
            'company',
            'users',
            'allModules',
            'activeModuleKeys'
        ));
    }

    public function edit(Company $company)
    {
        $company->load('modules');
        $allModules = CompanyModule::ALL_MODULES;
        $activeModuleKeys = $company->modules
            ->where('is_active', true)
            ->pluck('module_key')
            ->toArray();

        return view('super-admin.companies.edit', compact('company', 'allModules', 'activeModuleKeys'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'phone'              => ['nullable', 'string', 'max:50'],
            'email'              => ['nullable', 'email', 'max:255'],
            'tax_no'             => ['nullable', 'string', 'max:100'],
            'city'               => ['nullable', 'string', 'max:100'],
            'address'            => ['nullable', 'string'],
            'license_type'       => ['required', 'in:trial,standard,premium'],
            'license_expires_at' => ['nullable', 'date'],
            'max_vehicles'       => ['required', 'integer', 'min:1'],
            'max_users'          => ['required', 'integer', 'min:1'],
            'modules'            => ['nullable', 'array'],
            'modules.*'          => ['string'],
        ]);

        $company->update([
            'name'               => $validated['name'],
            'phone'              => $validated['phone'] ?? null,
            'email'              => $validated['email'] ?? null,
            'tax_no'             => $validated['tax_no'] ?? null,
            'city'               => $validated['city'] ?? null,
            'address'            => $validated['address'] ?? null,
            'is_active'          => $request->has('is_active'),
            'license_type'       => $validated['license_type'],
            'license_expires_at' => $validated['license_expires_at'] ?? null,
            'max_vehicles'       => $validated['max_vehicles'],
            'max_users'          => $validated['max_users'],
        ]);

        // Modülleri güncelle
        $selectedModules = $validated['modules'] ?? [];

        foreach (CompanyModule::ALL_MODULES as $key => $label) {
            $company->modules()->updateOrCreate(
                ['module_key' => $key],
                ['is_active' => in_array($key, $selectedModules)]
            );
        }

        return redirect()
            ->route('super-admin.companies.show', $company)
            ->with('success', 'Firma bilgileri güncellendi.');
    }

    public function destroy(Company $company)
    {
        // Firmaya ait kullanıcıları pasif yap
        $company->users()->update(['is_active' => false]);
        $company->update(['is_active' => false]);

        return redirect()
            ->route('super-admin.companies.index')
            ->with('success', $company->name . ' firması devre dışı bırakıldı.');
    }

    /**
     * Firmaya yeni kullanıcı ekle (Super Admin panelinden)
     */
    public function storeUser(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required', 'in:company_admin,operation,accounting,viewer'],
        ]);

        $user = User::create([
            'company_id' => $company->id,
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => $validated['password'],
            'role'       => $validated['role'],
            'is_active'  => true,
        ]);

        if ($validated['role'] === 'company_admin') {
            $permissionIds = Permission::pluck('id')->toArray();
            $user->permissions()->sync($permissionIds);
        }

        return redirect()
            ->route('super-admin.companies.show', $company)
            ->with('success', 'Kullanıcı başarıyla oluşturuldu.');
    }

    /**
     * Modülleri toplu güncelle
     */
    public function updateModules(Request $request, Company $company)
    {
        $selectedModules = $request->input('modules', []);

        foreach (CompanyModule::ALL_MODULES as $key => $label) {
            $company->modules()->updateOrCreate(
                ['module_key' => $key],
                ['is_active' => in_array($key, $selectedModules)]
            );
        }

        return redirect()
            ->route('super-admin.companies.show', $company)
            ->with('success', 'Modül ayarları güncellendi.');
    }
}
