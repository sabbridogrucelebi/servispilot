<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerPortalUserController extends Controller
{
    public function store(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'company_id' => $customer->company_id,
            'customer_id' => $customer->id,
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'password' => $validated['password'],
            'role' => 'viewer',
            'user_type' => 'customer_portal',
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('customers.show', [
                'customer' => $customer,
                'tab' => 'users',
            ])
            ->with('success', 'Müşteri portal kullanıcısı başarıyla oluşturuldu.');
    }

    public function update(Request $request, Customer $customer, User $portalUser)
    {
        abort_unless(
            $portalUser->customer_id === $customer->id && $portalUser->user_type === 'customer_portal',
            404
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($portalUser->id),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($portalUser->id),
            ],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $portalUser->name = $validated['name'];
        $portalUser->username = $validated['username'];
        $portalUser->email = $validated['email'] ?? null;
        $portalUser->is_active = $request->has('is_active');

        if (!empty($validated['password'])) {
            $portalUser->password = $validated['password'];
        }

        $portalUser->save();

        return redirect()
            ->route('customers.show', [
                'customer' => $customer,
                'tab' => 'users',
            ])
            ->with('success', 'Portal kullanıcısı güncellendi.');
    }

    public function toggleStatus(Customer $customer, User $portalUser)
    {
        abort_unless(
            $portalUser->customer_id === $customer->id && $portalUser->user_type === 'customer_portal',
            404
        );

        $portalUser->is_active = !$portalUser->is_active;
        $portalUser->save();

        return redirect()
            ->route('customers.show', [
                'customer' => $customer,
                'tab' => 'users',
            ])
            ->with('success', $portalUser->is_active
                ? 'Portal kullanıcısı aktif edildi.'
                : 'Portal kullanıcısı pasif yapıldı.');
    }

    public function destroy(Customer $customer, User $portalUser)
    {
        abort_unless(
            $portalUser->customer_id === $customer->id && $portalUser->user_type === 'customer_portal',
            404
        );

        $portalUser->delete();

        return redirect()
            ->route('customers.show', [
                'customer' => $customer,
                'tab' => 'users',
            ])
            ->with('success', 'Portal kullanıcısı silindi.');
    }
}