@extends('layouts.super-admin')

@section('title', 'Platform Dashboard')
@section('subtitle', 'Tüm firmaların genel görünümü')

@section('content')

    {{-- İSTATİSTİK KARTLARI --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">

        <div class="rounded-[24px] border border-slate-200/60 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-100 text-2xl">🏢</div>
                <div>
                    <div class="text-3xl font-extrabold text-slate-900">{{ $totalCompanies }}</div>
                    <div class="text-sm font-medium text-slate-500">Toplam Firma</div>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-3 text-xs font-medium">
                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-emerald-700">{{ $activeCompanies }} aktif</span>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-600">{{ $passiveCompanies }} pasif</span>
            </div>
        </div>

        <div class="rounded-[24px] border border-slate-200/60 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-100 text-2xl">👥</div>
                <div>
                    <div class="text-3xl font-extrabold text-slate-900">{{ $totalUsers }}</div>
                    <div class="text-sm font-medium text-slate-500">Toplam Kullanıcı</div>
                </div>
            </div>
        </div>

        <div class="rounded-[24px] border border-slate-200/60 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-100 text-2xl">🚗</div>
                <div>
                    <div class="text-3xl font-extrabold text-slate-900">{{ $totalVehicles }}</div>
                    <div class="text-sm font-medium text-slate-500">Toplam Araç</div>
                </div>
            </div>
        </div>

        <div class="rounded-[24px] border border-slate-200/60 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-2xl">🧑‍✈️</div>
                <div>
                    <div class="text-3xl font-extrabold text-slate-900">{{ $totalDrivers }}</div>
                    <div class="text-sm font-medium text-slate-500">Toplam Personel</div>
                </div>
            </div>
        </div>

    </div>

    {{-- LİSANS UYARILARI --}}
    @if($expiredLicenses > 0 || $expiringIn30Days > 0)
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 mb-8">
            @if($expiredLicenses > 0)
                <div class="rounded-[24px] border border-rose-200 bg-rose-50 p-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-xl">⚠️</div>
                        <div>
                            <div class="text-2xl font-extrabold text-rose-700">{{ $expiredLicenses }}</div>
                            <div class="text-sm font-medium text-rose-600">Lisansı Dolmuş Firma</div>
                        </div>
                    </div>
                </div>
            @endif

            @if($expiringIn30Days > 0)
                <div class="rounded-[24px] border border-amber-200 bg-amber-50 p-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-xl">🔔</div>
                        <div>
                            <div class="text-2xl font-extrabold text-amber-700">{{ $expiringIn30Days }}</div>
                            <div class="text-sm font-medium text-amber-600">30 Gün İçinde Dolacak</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- SON EKLENEN FİRMALAR --}}
    <div class="rounded-[24px] border border-slate-200/60 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200/70 px-6 py-5">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900">Son Eklenen Firmalar</h2>
                <a href="{{ route('super-admin.companies.create') }}"
                   class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-3 text-sm font-semibold text-white shadow-lg hover:scale-[1.02] transition">
                    ➕ Yeni Firma Ekle
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Firma Adı</th>
                        <th class="px-6 py-4 font-semibold">Yönetici</th>
                        <th class="px-6 py-4 font-semibold text-center">Kullanıcı</th>
                        <th class="px-6 py-4 font-semibold text-center">Araç</th>
                        <th class="px-6 py-4 font-semibold text-center">Lisans</th>
                        <th class="px-6 py-4 font-semibold text-center">Durum</th>
                        <th class="px-6 py-4 font-semibold text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentCompanies as $company)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $company->name }}</div>
                                <div class="text-xs text-slate-500">{{ $company->city ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @php $admin = $company->users->first(); @endphp
                                <div class="text-slate-700">{{ $admin->name ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $admin->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-700">
                                    {{ $company->users_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-700">
                                    {{ $company->vehicles_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($company->isLicenseActive())
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        {{ ucfirst($company->license_type) }}
                                    </span>
                                @else
                                    <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700">
                                        Süresi Dolmuş
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($company->is_active)
                                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                @else
                                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('super-admin.companies.show', $company) }}"
                                   class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition">
                                    Detay
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                Henüz firma eklenmemiş.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
