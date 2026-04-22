@extends('layouts.super-admin')
@section('title', $company->name)
@section('subtitle', 'Firma detayları ve yönetimi')
@section('content')
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-6">
        {{-- FİRMA BİLGİLERİ --}}
        <div class="rounded-[24px] border border-slate-200/60 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-slate-900">🏢 Firma Bilgileri</h3>
                <a href="{{ route('super-admin.companies.edit', $company) }}" class="rounded-xl bg-indigo-100 px-4 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-200 transition">Düzenle</a>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-slate-500">Firma Adı:</span> <span class="font-semibold text-slate-900">{{ $company->name }}</span></div>
                <div><span class="text-slate-500">Şehir:</span> <span class="font-semibold">{{ $company->city ?? '-' }}</span></div>
                <div><span class="text-slate-500">Telefon:</span> <span class="font-semibold">{{ $company->phone ?? '-' }}</span></div>
                <div><span class="text-slate-500">E-posta:</span> <span class="font-semibold">{{ $company->email ?? '-' }}</span></div>
                <div><span class="text-slate-500">Vergi No:</span> <span class="font-semibold">{{ $company->tax_no ?? '-' }}</span></div>
                <div>
                    <span class="text-slate-500">Durum:</span>
                    @if($company->is_active)
                        <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">Aktif</span>
                    @else
                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">Pasif</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- LİSANS --}}
        <div class="rounded-[24px] border border-slate-200/60 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-5">📋 Lisans & Kota</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-slate-500">Lisans Tipi:</span>
                    <span class="ml-1 rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">{{ ucfirst($company->license_type) }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Bitiş:</span>
                    @if($company->license_expires_at)
                        <span class="font-semibold {{ $company->isLicenseActive() ? 'text-slate-900' : 'text-rose-600' }}">
                            {{ $company->license_expires_at->format('d.m.Y') }}
                            @if($company->licenseDaysRemaining() !== null) ({{ $company->licenseDaysRemaining() }} gün) @endif
                        </span>
                    @else
                        <span class="font-semibold text-emerald-600">Süresiz</span>
                    @endif
                </div>
                <div><span class="text-slate-500">Araç:</span> <span class="font-semibold">{{ $company->vehicles_count }} / {{ $company->max_vehicles }}</span></div>
                <div><span class="text-slate-500">Kullanıcı:</span> <span class="font-semibold">{{ $company->users_count }} / {{ $company->max_users }}</span></div>
            </div>
        </div>

        {{-- KULLANICILAR --}}
        <div class="rounded-[24px] border border-slate-200/60 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-200/70 px-6 py-5">
                <h3 class="text-lg font-bold text-slate-900">👥 Kullanıcılar ({{ $users->count() }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Ad Soyad</th>
                            <th class="px-6 py-3 font-semibold">E-posta</th>
                            <th class="px-6 py-3 font-semibold">Rol</th>
                            <th class="px-6 py-3 font-semibold text-center">Durum</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($users as $u)
                            @php
                                $roleLabels = ['company_admin'=>['Yönetici','bg-violet-100 text-violet-700'],'operation'=>['Operasyon','bg-sky-100 text-sky-700'],'accounting'=>['Muhasebe','bg-emerald-100 text-emerald-700'],'viewer'=>['Görüntüleyici','bg-slate-100 text-slate-700']];
                                $role = $roleLabels[$u->role] ?? ['Bilinmeyen','bg-slate-100 text-slate-700'];
                            @endphp
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-6 py-3 font-medium text-slate-900">{{ $u->name }}</td>
                                <td class="px-6 py-3 text-slate-600">{{ $u->email }}</td>
                                <td class="px-6 py-3"><span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $role[1] }}">{{ $role[0] }}</span></td>
                                <td class="px-6 py-3 text-center">
                                    @if($u->is_active)<span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>@else<span class="inline-flex h-2 w-2 rounded-full bg-slate-300"></span>@endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200/70 px-6 py-5 bg-slate-50">
                <h4 class="text-sm font-bold text-slate-800 mb-4">Yeni Kullanıcı Ekle</h4>
                <form method="POST" action="{{ route('super-admin.companies.users.store', $company) }}" class="flex flex-wrap gap-3 items-end">
                    @csrf
                    <div><label class="block text-xs font-medium text-slate-600 mb-1">Ad Soyad</label><input type="text" name="name" required class="rounded-xl border border-slate-200 px-3 py-2 text-sm w-40"></div>
                    <div><label class="block text-xs font-medium text-slate-600 mb-1">E-posta</label><input type="email" name="email" required class="rounded-xl border border-slate-200 px-3 py-2 text-sm w-48"></div>
                    <div><label class="block text-xs font-medium text-slate-600 mb-1">Şifre</label><input type="text" name="password" required minlength="8" class="rounded-xl border border-slate-200 px-3 py-2 text-sm w-32"></div>
                    <div><label class="block text-xs font-medium text-slate-600 mb-1">Rol</label>
                        <select name="role" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            <option value="company_admin">Yönetici</option>
                            <option value="operation">Operasyon</option>
                            <option value="accounting">Muhasebe</option>
                            <option value="viewer">Görüntüleyici</option>
                        </select>
                    </div>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition">Ekle</button>
                </form>
            </div>
        </div>
    </div>

    {{-- MODÜLLER & STATS --}}
    <div class="space-y-6">
        <div class="rounded-[24px] border border-slate-200/60 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-5">🧩 Modüller</h3>
            <form method="POST" action="{{ route('super-admin.companies.modules.update', $company) }}">
                @csrf @method('PUT')
                <div class="space-y-3">
                    @foreach($allModules as $key => $label)
                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 cursor-pointer hover:bg-indigo-50 transition">
                            <input type="checkbox" name="modules[]" value="{{ $key }}" {{ in_array($key, $activeModuleKeys) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                            <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <button type="submit" class="mt-5 w-full rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-3 text-sm font-semibold text-white shadow-lg hover:scale-[1.02] transition">Modülleri Kaydet</button>
            </form>
        </div>
        <div class="rounded-[24px] border border-slate-200/60 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-5">📊 İstatistikler</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Araçlar</span><span class="font-bold">{{ $company->vehicles_count }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Personeller</span><span class="font-bold">{{ $company->drivers_count }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Müşteriler</span><span class="font-bold">{{ $company->customers_count }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Kullanıcılar</span><span class="font-bold">{{ $company->users_count }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Kayıt</span><span class="font-bold">{{ $company->created_at->format('d.m.Y') }}</span></div>
            </div>
        </div>
    </div>
</div>
<div class="mt-6">
    <a href="{{ route('super-admin.companies.index') }}" class="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">← Firmalara Dön</a>
</div>
@endsection
