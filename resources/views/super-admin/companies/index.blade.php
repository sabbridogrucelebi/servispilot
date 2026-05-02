@extends('layouts.super-admin')

@section('title', 'Firmalar')
@section('subtitle', 'Tüm firmaların listesi ve yönetimi')

@section('content')
    {{-- KPI KARTLARI --}}
    <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Toplam Firmalar -->
        <div class="group relative overflow-hidden rounded-[24px] p-6 shadow-[0_15px_30px_-5px_rgba(99,102,241,0.4)] border-t border-l border-white/40 transition-all duration-300 hover:shadow-[0_25px_50px_-12px_rgba(99,102,241,0.6)] hover:-translate-y-1.5" style="background: linear-gradient(135deg, #6366f1, #4338ca);">
            <div class="absolute -left-10 -top-10 h-40 w-40 rounded-full bg-white/20 blur-3xl"></div>
            
            <!-- Sağ Alt 3D Filigran İkon -->
            <div class="absolute transition-transform duration-500 group-hover:scale-110 group-hover:rotate-0 select-none pointer-events-none drop-shadow-2xl" style="bottom: -15px; right: 0px; font-size: 100px; opacity: 1; transform: rotate(-12deg);">
                🏢
            </div>

            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="text-[13px] font-semibold text-indigo-100 tracking-wide drop-shadow-sm">Toplam Firma</div>
                <div class="mt-2 text-4xl font-black text-white drop-shadow-md">{{ $totalCompanies }}</div>
            </div>
        </div>

        <!-- Aktif Firmalar -->
        <div class="group relative overflow-hidden rounded-[24px] p-6 shadow-[0_15px_30px_-5px_rgba(16,185,129,0.4)] border-t border-l border-white/40 transition-all duration-300 hover:shadow-[0_25px_50px_-12px_rgba(16,185,129,0.6)] hover:-translate-y-1.5" style="background: linear-gradient(135deg, #10b981, #047857);">
            <div class="absolute -left-10 -top-10 h-40 w-40 rounded-full bg-white/20 blur-3xl"></div>
            
            <!-- Sağ Alt 3D Filigran İkon -->
            <div class="absolute transition-transform duration-500 group-hover:scale-110 group-hover:rotate-0 select-none pointer-events-none drop-shadow-2xl" style="bottom: -15px; right: 0px; font-size: 100px; opacity: 1; transform: rotate(-12deg);">
                🚀
            </div>

            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="text-[13px] font-semibold text-emerald-100 tracking-wide drop-shadow-sm">Aktif Firma</div>
                <div class="mt-2 text-4xl font-black text-white drop-shadow-md">{{ $activeCompanies }}</div>
            </div>
        </div>

        <!-- Pasif Firmalar -->
        <div class="group relative overflow-hidden rounded-[24px] p-6 shadow-[0_15px_30px_-5px_rgba(244,63,94,0.4)] border-t border-l border-white/40 transition-all duration-300 hover:shadow-[0_25px_50px_-12px_rgba(244,63,94,0.6)] hover:-translate-y-1.5" style="background: linear-gradient(135deg, #f43f5e, #be123c);">
            <div class="absolute -left-10 -top-10 h-40 w-40 rounded-full bg-white/20 blur-3xl"></div>
            
            <!-- Sağ Alt 3D Filigran İkon -->
            <div class="absolute transition-transform duration-500 group-hover:scale-110 group-hover:rotate-0 select-none pointer-events-none drop-shadow-2xl" style="bottom: -15px; right: 0px; font-size: 100px; opacity: 1; transform: rotate(-12deg);">
                🛑
            </div>

            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="text-[13px] font-semibold text-rose-100 tracking-wide drop-shadow-sm">Pasif Firma</div>
                <div class="mt-2 text-4xl font-black text-white drop-shadow-md">{{ $passiveCompanies }}</div>
            </div>
        </div>

        <!-- Süresi Yaklaşanlar -->
        <div class="group relative overflow-hidden rounded-[24px] p-6 shadow-[0_15px_30px_-5px_rgba(245,158,11,0.4)] border-t border-l border-white/40 transition-all duration-300 hover:shadow-[0_25px_50px_-12px_rgba(245,158,11,0.6)] hover:-translate-y-1.5" style="background: linear-gradient(135deg, #f59e0b, #c2410c);">
            <div class="absolute -left-10 -top-10 h-40 w-40 rounded-full bg-white/20 blur-3xl"></div>
            
            <!-- Sağ Alt 3D Filigran İkon -->
            <div class="absolute transition-transform duration-500 group-hover:scale-110 group-hover:rotate-0 select-none pointer-events-none drop-shadow-2xl" style="bottom: -15px; right: 0px; font-size: 100px; opacity: 1; transform: rotate(-12deg);">
                ⏳
            </div>

            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="text-[13px] font-semibold text-orange-100 tracking-wide drop-shadow-sm">Süresi Yaklaşan</div>
                <div class="mt-2 text-4xl font-black text-white drop-shadow-md">{{ $expiringSoonCompanies }}</div>
            </div>
        </div>
    </div>

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
           class="group relative inline-flex items-center gap-2.5 rounded-2xl bg-gradient-to-b from-indigo-500 to-violet-600 px-7 py-3.5 text-[15px] font-extrabold text-white shadow-[0_6px_0_0_#312e81,0_15px_20px_rgba(79,70,229,0.4)] transition-all hover:translate-y-1 hover:shadow-[0_2px_0_0_#312e81,0_10px_15px_rgba(79,70,229,0.4)] active:translate-y-1.5 active:shadow-none">
            <div class="absolute inset-0 rounded-2xl bg-gradient-to-tr from-white/0 via-white/20 to-white/0 opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
            <svg class="w-5 h-5 drop-shadow-md transition-transform group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
            <span class="drop-shadow-md tracking-wide">Yeni Firma Ekle</span>
        </a>
    </div>

    {{-- FİRMA TABLOSU --}}
    <div class="rounded-[32px] border border-slate-200/60 bg-white shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] overflow-hidden">
        <div class="overflow-x-auto p-2">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-gradient-to-r from-[#1a1a2e] to-[#16213e] text-xs font-semibold tracking-wide text-white">
                    <tr>
                        <th class="px-8 py-5 rounded-tl-[24px]">Firma Adı</th>
                        <th class="px-8 py-5">İletişim</th>
                        <th class="px-8 py-5 text-center">Kullanıcı</th>
                        <th class="px-8 py-5 text-center">Araç</th>
                        <th class="px-8 py-5 text-center">Lisans</th>
                        <th class="px-8 py-5 text-center">Bitiş</th>
                        <th class="px-8 py-5 text-center">Durum</th>
                        <th class="px-8 py-5 text-right rounded-tr-[24px]">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/80">
                    @forelse($companies as $company)
                        <tr class="group hover:bg-indigo-50/40 transition-all duration-300">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-5">
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 text-lg font-black text-slate-600 shadow-inner group-hover:from-indigo-100 group-hover:to-violet-100 group-hover:text-indigo-700 transition-colors">
                                        {{ strtoupper(mb_substr($company->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-[16px] font-extrabold text-slate-800 group-hover:text-indigo-900 transition-colors tracking-tight">{{ $company->name }}</div>
                                        <div class="text-[13px] font-medium text-slate-500 mt-1">{{ $company->city ?? 'Belirtilmedi' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="text-[15px] font-semibold text-slate-700">{{ $company->phone ?? '-' }}</div>
                                <div class="text-[13px] text-slate-500 mt-1">{{ $company->email ?? '-' }}</div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-50 border border-slate-100 shadow-sm">
                                    <span class="text-[15px] font-bold text-slate-800">{{ $company->users_count }}</span>
                                    <span class="text-xs font-semibold text-slate-400">/ {{ $company->max_users }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-50 border border-slate-100 shadow-sm">
                                    <span class="text-[15px] font-bold text-slate-800">{{ $company->vehicles_count }}</span>
                                    <span class="text-xs font-semibold text-slate-400">/ {{ $company->max_vehicles }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                @php
                                    $licenseColors = [
                                        'trial'    => 'bg-amber-50 text-amber-700 border-amber-200 shadow-sm',
                                        'standard' => 'bg-indigo-50 text-indigo-700 border-indigo-200 shadow-sm',
                                        'premium'  => 'bg-violet-50 text-violet-700 border-violet-200 shadow-sm',
                                    ];
                                @endphp
                                <span class="inline-flex items-center justify-center rounded-xl border px-4 py-2 text-xs font-extrabold tracking-wide uppercase {{ $licenseColors[$company->license_type] ?? 'bg-slate-50 text-slate-700 border-slate-200 shadow-sm' }}">
                                    {{ $company->license_type }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-center">
                                @if($company->license_expires_at)
                                    <div class="inline-flex items-center gap-2 {{ $company->isLicenseActive() ? 'text-slate-600 font-medium' : 'text-rose-700 font-bold bg-rose-50 border border-rose-100 px-3 py-1.5 rounded-lg shadow-sm' }}">
                                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span class="text-[13px]">{{ $company->license_expires_at->format('d.m.Y') }}</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-emerald-700 font-bold text-[13px] bg-emerald-50 border border-emerald-100 px-3 py-1.5 rounded-lg shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Süresiz
                                    </span>
                                @endif
                            </td>
                            <td class="px-8 py-6 text-center">
                                @if($company->is_active)
                                    <div class="inline-flex items-center justify-center gap-2 rounded-full bg-emerald-50 border border-emerald-200 px-4 py-2 text-xs font-extrabold text-emerald-700 shadow-sm">
                                        <span class="relative flex h-2.5 w-2.5">
                                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                          <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                        </span>
                                        AKTİF
                                    </div>
                                @else
                                    <div class="inline-flex items-center justify-center gap-2 rounded-full bg-slate-50 border border-slate-300 px-4 py-2 text-xs font-extrabold text-slate-600 shadow-sm">
                                        <span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                                        PASİF
                                    </div>
                                @endif
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('super-admin.companies.show', $company) }}"
                                       title="Detay"
                                       class="inline-flex h-10 items-center justify-center rounded-xl bg-slate-100 px-4 text-xs font-extrabold text-slate-600 hover:bg-slate-200 hover:text-slate-900 hover:scale-105 transition-all shadow-sm">
                                        Detay
                                    </a>
                                    
                                    <form action="{{ route('super-admin.companies.impersonate', $company) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" title="Firma Paneline Doğrudan Giriş Yap"
                                                class="inline-flex h-10 items-center justify-center rounded-xl bg-emerald-50 border border-emerald-100 px-3 text-emerald-600 hover:bg-emerald-600 hover:text-white hover:scale-105 transition-all shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>
                                    </form>

                                    <a href="{{ route('super-admin.companies.edit', $company) }}"
                                       title="Düzenle"
                                       class="inline-flex h-10 items-center justify-center rounded-xl bg-indigo-50 border border-indigo-100 px-4 text-xs font-extrabold text-indigo-700 hover:bg-indigo-600 hover:text-white hover:scale-105 transition-all shadow-sm ml-1">
                                        Düzenle
                                    </a>

                                    <form action="{{ route('super-admin.companies.destroy', $company) }}" method="POST" class="inline-block"
                                          onsubmit="return confirm('⚠️ DİKKAT!\n\nBu firmayı silmek üzeresiniz.\nFirmaya ait Tümü (Araçlar, Şoförler, Puantajlar, Faturalar, Seferler, Kullanıcılar vs.) SİLİNECEK ve bu işlem GERİ ALINAMAZ!\n\nDevam etmek istediğinize emin misiniz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Firmayı ve Tüm Verilerini Kalıcı Sil"
                                                class="inline-flex h-10 items-center justify-center rounded-xl bg-rose-50 border border-rose-100 px-3 text-rose-600 hover:bg-rose-600 hover:text-white hover:scale-105 transition-all shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <div class="text-4xl mb-4 opacity-50">📭</div>
                                    <p class="text-sm font-semibold">Henüz firma eklenmemiş veya eşleşen sonuç yok.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($companies->hasPages())
            <div class="border-t border-slate-100 bg-slate-50/50 px-8 py-5">
                {{ $companies->withQueryString()->links() }}
            </div>
        @endif
    </div>

@endsection
