@extends('layouts.super-admin')

@section('title', 'Firmalar')
@section('subtitle', 'Tüm firmaların listesi ve yönetimi')

@section('content')

    {{-- ARAMA & FİLTRE --}}
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <form method="GET" action="{{ route('super-admin.companies.index') }}" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Firma adı, e-posta, şehir ara..."
                   class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-indigo-400 focus:ring-indigo-400 w-72">

            <select name="status" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="">Tüm Durumlar</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="passive" {{ request('status') === 'passive' ? 'selected' : '' }}>Pasif</option>
            </select>

            <button type="submit" class="rounded-2xl bg-slate-800 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-700 transition">
                Filtrele
            </button>
        </form>

        <a href="{{ route('super-admin.companies.create') }}"
           class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-3 text-sm font-semibold text-white shadow-lg hover:scale-[1.02] transition">
            ➕ Yeni Firma Ekle
        </a>
    </div>

    {{-- FİRMA TABLOSU --}}
    <div class="rounded-[24px] border border-slate-200/60 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Firma Adı</th>
                        <th class="px-6 py-4 font-semibold">İletişim</th>
                        <th class="px-6 py-4 font-semibold text-center">Kullanıcı</th>
                        <th class="px-6 py-4 font-semibold text-center">Araç</th>
                        <th class="px-6 py-4 font-semibold text-center">Lisans</th>
                        <th class="px-6 py-4 font-semibold text-center">Bitiş</th>
                        <th class="px-6 py-4 font-semibold text-center">Durum</th>
                        <th class="px-6 py-4 font-semibold text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($companies as $company)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $company->name }}</div>
                                <div class="text-xs text-slate-500">{{ $company->city ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-slate-700">{{ $company->phone ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $company->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-semibold">{{ $company->users_count }}</span>
                                <span class="text-xs text-slate-400">/ {{ $company->max_users }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-semibold">{{ $company->vehicles_count }}</span>
                                <span class="text-xs text-slate-400">/ {{ $company->max_vehicles }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $licenseColors = [
                                        'trial'    => 'bg-amber-100 text-amber-700',
                                        'standard' => 'bg-indigo-100 text-indigo-700',
                                        'premium'  => 'bg-violet-100 text-violet-700',
                                    ];
                                @endphp
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $licenseColors[$company->license_type] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ ucfirst($company->license_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-xs">
                                @if($company->license_expires_at)
                                    <span class="{{ $company->isLicenseActive() ? 'text-slate-600' : 'text-rose-600 font-semibold' }}">
                                        {{ $company->license_expires_at->format('d.m.Y') }}
                                    </span>
                                @else
                                    <span class="text-emerald-600 font-semibold">Süresiz</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($company->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Pasif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('super-admin.companies.show', $company) }}"
                                       class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition">
                                        Detay
                                    </a>
                                    <a href="{{ route('super-admin.companies.edit', $company) }}"
                                       class="rounded-xl bg-indigo-100 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-200 transition">
                                        Düzenle
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                Henüz firma eklenmemiş.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($companies->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $companies->withQueryString()->links() }}
            </div>
        @endif
    </div>

@endsection
