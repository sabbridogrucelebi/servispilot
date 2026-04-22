<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

class CompanyUserController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->isCompanyAdmin(), 403);

        $users = User::where('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->get();

        return view('company-users.index', compact('users'));
    }

    public function create()
    {
        abort_unless(auth()->user()->isCompanyAdmin(), 403);

        // Sidebar'daki öğelerin permission'larını al
        $navItems = config('navigation.items', []);
        $activePermissionKeys = collect($navItems)->pluck('permission')->filter()->unique()->toArray();

        $permissions = Permission::whereIn('key', $activePermissionKeys)
            ->orderBy('label')
            ->get();

        return view('company-users.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->isCompanyAdmin(), 403);

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
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:company_admin,operation,accounting,viewer'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $user = User::create([
            'company_id' => auth()->user()->company_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'is_active' => $request->has('is_active'),
        ]);

        $permissionIds = $validated['permissions'] ?? [];

        if ($validated['role'] === 'company_admin') {
            $permissionIds = Permission::pluck('id')->toArray();
        }

        $user->permissions()->sync($permissionIds);

        return redirect()->route('company-users.index')->with('success', 'Kullanıcı başarıyla eklendi.');
    }

    public function show(User $companyUser)
    {
        abort_unless(auth()->user()->isCompanyAdmin(), 403);
        abort_unless($companyUser->company_id === auth()->user()->company_id, 403);

        return view('company-users.show', compact('companyUser'));
    }

    public function edit(User $companyUser)
    {
        abort_unless(auth()->user()->isCompanyAdmin(), 403);
        abort_unless($companyUser->company_id === auth()->user()->company_id, 403);

        // Sidebar'daki öğelerin permission'larını al
        $navItems = config('navigation.items', []);
        $activePermissionKeys = collect($navItems)->pluck('permission')->filter()->unique()->toArray();

        $permissions = Permission::whereIn('key', $activePermissionKeys)
            ->orderBy('label')
            ->get();
            
        $selectedPermissions = $companyUser->permissions()->pluck('permissions.id')->toArray();

        return view('company-users.edit', compact('companyUser', 'permissions', 'selectedPermissions'));
    }

    public function update(Request $request, User $companyUser)
    {
        abort_unless(auth()->user()->isCompanyAdmin(), 403);
        abort_unless($companyUser->company_id === auth()->user()->company_id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email,' . $companyUser->id,
            ],
            'role' => ['required', 'in:company_admin,operation,accounting,viewer'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $companyUser->name = $validated['name'];
        $companyUser->email = $validated['email'];
        $companyUser->role = $validated['role'];
        $companyUser->is_active = $request->has('is_active');

        if (!empty($validated['password'])) {
            $companyUser->password = $validated['password'];
        }

        $companyUser->save();

        $permissionIds = $validated['permissions'] ?? [];

        if ($validated['role'] === 'company_admin') {
            $permissionIds = Permission::pluck('id')->toArray();
        }

        $companyUser->permissions()->sync($permissionIds);

        return redirect()->route('company-users.index')->with('success', 'Kullanıcı güncellendi.');
    }

    public function destroy(User $companyUser)
    {
        abort_unless(auth()->user()->isCompanyAdmin(), 403);
        abort_unless($companyUser->company_id === auth()->user()->company_id, 403);

        if ($companyUser->id === auth()->id()) {
            return redirect()->route('company-users.index')->with('success', 'Kendi hesabını silemezsin.');
        }

        $companyUser->delete();

        return redirect()->route('company-users.index')->with('success', 'Kullanıcı silindi.');
    }
}