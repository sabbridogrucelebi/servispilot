@extends('layouts.super-admin')
@section('title', $company->name . ' — Düzenle')
@section('subtitle', 'Firma bilgileri, lisans ve modül düzenleme')
@section('content')
<form method="POST" action="{{ route('super-admin.companies.update', $company) }}">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-[24px] border border-slate-200/60 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-5">🏢 Firma Bilgileri</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Firma Adı *</label>
                    <input type="text" name="name" value="{{ old('name', $company->name) }}" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-semibold text-slate-700 mb-1">Telefon</label><input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"></div>
                    <div><label class="block text-sm font-semibold text-slate-700 mb-1">E-posta</label><input type="email" name="email" value="{{ old('email', $company->email) }}" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-semibold text-slate-700 mb-1">Vergi No</label><input type="text" name="tax_no" value="{{ old('tax_no', $company->tax_no) }}" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"></div>
                    <div><label class="block text-sm font-semibold text-slate-700 mb-1">Şehir</label><input type="text" name="city" value="{{ old('city', $company->city) }}" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"></div>
                </div>
                <div><label class="block text-sm font-semibold text-slate-700 mb-1">Adres</label><textarea name="address" rows="2" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">{{ old('address', $company->address) }}</textarea></div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ $company->is_active ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                    <label for="is_active" class="text-sm font-medium text-slate-700">Firma Aktif</label>
                </div>
            </div>
        </div>
        <div class="rounded-[24px] border border-slate-200/60 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-5">📋 Lisans & Kota</h3>
            <div class="space-y-4">
                <div><label class="block text-sm font-semibold text-slate-700 mb-1">Lisans Tipi</label>
                    <select name="license_type" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                        <option value="trial" {{ $company->license_type === 'trial' ? 'selected' : '' }}>Trial</option>
                        <option value="standard" {{ $company->license_type === 'standard' ? 'selected' : '' }}>Standard</option>
                        <option value="premium" {{ $company->license_type === 'premium' ? 'selected' : '' }}>Premium</option>
                    </select>
                </div>
                <div><label class="block text-sm font-semibold text-slate-700 mb-1">Lisans Bitiş</label><input type="date" name="license_expires_at" value="{{ old('license_expires_at', $company->license_expires_at?->format('Y-m-d')) }}" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"><p class="text-xs text-slate-500 mt-1">Boş = süresiz</p></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-semibold text-slate-700 mb-1">Maks. Araç</label><input type="number" name="max_vehicles" value="{{ old('max_vehicles', $company->max_vehicles) }}" min="1" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"></div>
                    <div><label class="block text-sm font-semibold text-slate-700 mb-1">Maks. Kullanıcı</label><input type="number" name="max_users" value="{{ old('max_users', $company->max_users) }}" min="1" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"></div>
                </div>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mt-8 mb-5">🧩 Modüller</h3>
            <div class="space-y-3">
                @foreach($allModules as $key => $label)
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 cursor-pointer hover:bg-indigo-50 transition">
                        <input type="checkbox" name="modules[]" value="{{ $key }}" {{ in_array($key, $activeModuleKeys) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                        <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
    <div class="mt-6 flex items-center justify-end gap-4">
        <a href="{{ route('super-admin.companies.show', $company) }}" class="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">İptal</a>
        <button type="submit" class="rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-8 py-3 text-sm font-semibold text-white shadow-lg hover:scale-[1.02] transition">Güncelle</button>
    </div>
</form>
@endsection
