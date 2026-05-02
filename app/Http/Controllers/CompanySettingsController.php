<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompanySettingsController extends Controller
{
    public function edit()
    {
        abort_unless(auth()->user()->isCompanyAdmin() || auth()->user()->isSuperAdmin(), 403, 'Bu sayfaya erişim yetkiniz yok.');

        $company = auth()->user()->company;

        abort_unless($company, 404, 'Firma kaydı bulunamadı.');

        return view('company-settings.edit', compact('company'));
    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->isCompanyAdmin() || auth()->user()->isSuperAdmin(), 403, 'Bu işlem için yetkiniz yok.');

        $company = auth()->user()->company;

        abort_unless($company, 404, 'Firma kaydı bulunamadı.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:companies,slug,' . $company->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'tax_no' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'tax_no' => $validated['tax_no'] ?? null,
            'city' => $validated['city'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => $request->has('is_active'),
        ];

        // Sadece Süper Admin slug değiştirebilir
        if (auth()->user()->isSuperAdmin() && !empty($validated['slug'])) {
            $updateData['slug'] = $validated['slug'];
        }

        $company->update($updateData);

        return redirect()->route('company-settings.edit')->with('success', 'Firma ayarları güncellendi.');
    }
}