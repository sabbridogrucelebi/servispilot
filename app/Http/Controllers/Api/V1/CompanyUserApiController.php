<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

class CompanyUserApiController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_unless(auth()->user()->isCompanyAdmin(), 403, 'Bu işlem için şirket yöneticisi yetkisine sahip olmalısınız.');

        $users = User::where('company_id', $this->getCompanyId())
            ->orderBy('name')
            ->get();

        return $this->successResponse($users);
    }

    /**
     * Get available permissions for assignment.
     */
    public function options()
    {
        abort_unless(auth()->user()->isCompanyAdmin(), 403);

        $navItems = config('navigation.items', []);
        $activePermissionKeys = collect($navItems)->pluck('permission')->filter()->unique()->toArray();
        
        $activePermissionKeys = array_merge($activePermissionKeys, [
            'vehicles.create', 'vehicles.edit', 'vehicles.delete',
            'drivers.create',  'drivers.edit',  'drivers.delete',
            'customers.create', 'customers.edit', 'customers.delete',
            'trips.create', 'trips.edit', 'trips.delete',
            'fuels.create', 'fuels.edit', 'fuels.delete',
            'fuel_stations.create', 'fuel_stations.edit', 'fuel_stations.delete',
            'maintenances.create', 'maintenances.edit', 'maintenances.delete',
            'penalties.create', 'penalties.edit', 'penalties.delete',
            'documents.create', 'documents.edit', 'documents.delete',
            'payrolls.create', 'payrolls.edit', 'payrolls.delete',
            'company_users.create', 'company_users.edit', 'company_users.delete',
            'reports.view', 'financials.view'
        ]);

        $permissions = Permission::whereIn('key', $activePermissionKeys)
            ->orderBy('label')
            ->get(['id', 'key', 'label']);

        return $this->successResponse([
            'permissions' => $permissions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->isCompanyAdmin(), 403, 'Bu işlem için şirket yöneticisi yetkisine sahip olmalısınız.');

        $company = auth()->user()->company;
        if (!is_null($company->max_users) && $company->users()->count() >= $company->max_users) {
            return response()->json([
                'success' => false,
                'message' => 'Lisans paketinize ait maksimum kullanıcı kotasına (' . $company->max_users . ' Kullanıcı) ulaştınız. Lütfen lisansınızı yükseltin.'
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:company_admin,operation,accounting,viewer'],
            'is_active' => ['boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $user = User::create([
            'company_id' => $this->getCompanyId(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'], // Mutator will hash it
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        $permissionIds = $validated['permissions'] ?? [];

        if ($validated['role'] === 'company_admin') {
            $permissionIds = Permission::pluck('id')->toArray();
        }

        $user->permissions()->sync($permissionIds);

        return $this->successResponse($user, 'Kullanıcı başarıyla eklendi.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        abort_unless(auth()->user()->isCompanyAdmin(), 403);

        $user = User::with('permissions:id,key')->where('company_id', $this->getCompanyId())->findOrFail($id);
        
        $user->permission_ids = $user->permissions->pluck('id')->toArray();
        $user->unsetRelation('permissions');

        return $this->successResponse($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->isCompanyAdmin(), 403);

        $user = User::where('company_id', $this->getCompanyId())->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],
            'role' => ['required', 'in:company_admin,operation,accounting,viewer'],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        // Kendisi üzerinde işlem yapıyorsa rolünü ve aktifliğini değiştirmesini engelleyelim
        if ($user->id === auth()->id()) {
            if ($validated['role'] !== 'company_admin') {
                return response()->json(['success' => false, 'message' => 'Kendi rolünüzü değiştiremezsiniz.'], 403);
            }
            if ($request->has('is_active') && !$request->boolean('is_active')) {
                return response()->json(['success' => false, 'message' => 'Kendi hesabınızı pasife alamazsınız.'], 403);
            }
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->is_active = $request->boolean('is_active', $user->is_active);

        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        $permissionIds = $validated['permissions'] ?? [];

        if ($validated['role'] === 'company_admin') {
            $permissionIds = Permission::pluck('id')->toArray();
        }

        $user->permissions()->sync($permissionIds);

        return $this->successResponse($user, 'Kullanıcı başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        abort_unless(auth()->user()->isCompanyAdmin(), 403);

        $user = User::where('company_id', $this->getCompanyId())->findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Kendi hesabınızı silemezsiniz.'
            ], 403);
        }

        $user->delete();

        return $this->successResponse(null, 'Kullanıcı silindi.');
    }
}
