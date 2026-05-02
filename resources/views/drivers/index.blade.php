@extends('layouts.app')

@section('title', 'Personeller')
@section('subtitle', 'Personel kayıtları, belge durumu ve araç atamalarını yönetin')

@section('content')

@php
    $user = auth()->user();
@endphp

<div class="space-y-8" x-data="{ importOpen: false }">

    @if(session('success'))
        <div class="rounded-[24px] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700 shadow-sm animate-fade-in">
            <div class="flex items-center gap-3">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">✓</span>
                {{ session('success') }}
            </div>
        </div>
    @endif

    <!-- ÜST KARTLAR (KPI) -->
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-5">
        <div class="group relative overflow-hidden rounded-[32px] bg-white p-6 shadow-sm border border-slate-100 transition-all hover:shadow-xl hover:-translate-y-1">
            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-indigo-50 opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between">
                    <div class="h-12 w-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-xl shadow-inner">🧑‍✈️</div>
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Toplam Filo</span>
                </div>
                <div class="mt-6">
                    <div class="text-3xl font-black text-slate-900">{{ $totalDrivers }}</div>
                    <div class="mt-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kayıtlı Personel</div>
                </div>
            </div>
        </div>

        <div class="group relative overflow-hidden rounded-[32px] bg-white p-6 shadow-sm border border-slate-100 transition-all hover:shadow-xl hover:-translate-y-1">
            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-emerald-50 opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between">
                    <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-xl shadow-inner">🟢</div>
                    <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Aktif Görev</span>
                </div>
                <div class="mt-6">
                    <div class="text-3xl font-black text-slate-900">{{ $activeDrivers }}</div>
                    <div class="mt-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Aktif Personeller</div>
                </div>
            </div>
        </div>

        <div class="group relative overflow-hidden rounded-[32px] bg-white p-6 shadow-sm border border-slate-100 transition-all hover:shadow-xl hover:-translate-y-1">
            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-slate-50 opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between">
                    <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center text-xl shadow-inner">⚪</div>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Pasif Durum</span>
                </div>
                <div class="mt-6">
                    <div class="text-3xl font-black text-slate-900">{{ $passiveDrivers }}</div>
                    <div class="mt-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pasif Kayıtlar</div>
                </div>
            </div>
        </div>

        <div class="group relative overflow-hidden rounded-[32px] bg-white p-6 shadow-sm border border-slate-100 transition-all hover:shadow-xl hover:-translate-y-1">
            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-rose-50 opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between">
                    <div class="h-12 w-12 rounded-2xl bg-rose-50 flex items-center justify-center text-xl shadow-inner">⚠️</div>
                    <span class="text-[10px] font-black text-rose-600 uppercase tracking-widest">Kritik Belge</span>
                </div>
                <div class="mt-6">
                    <div class="text-3xl font-black text-slate-900">{{ $expiredDocumentCount }}</div>
                    <div class="mt-1 text-[11px] font-bold text-rose-500 uppercase tracking-wider">Süresi Geçmiş</div>
                </div>
            </div>
        </div>

        <div class="group relative overflow-hidden rounded-[32px] bg-white p-6 shadow-sm border border-slate-100 transition-all hover:shadow-xl hover:-translate-y-1">
            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-amber-50 opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between">
                    <div class="h-12 w-12 rounded-2xl bg-amber-50 flex items-center justify-center text-xl shadow-inner">📅</div>
                    <span class="text-[10px] font-black text-amber-600 uppercase tracking-widest">Takip Gereken</span>
                </div>
                <div class="mt-6">
                    <div class="text-3xl font-black text-slate-900">{{ $expiringSoonCount }}</div>
                    <div class="mt-1 text-[11px] font-bold text-amber-500 uppercase tracking-wider">Yakında Bitecek</div>
                </div>
            </div>
        </div>
    </div>

    <!-- FİLTRE VE AKSİYONLAR -->
    <div class="rounded-[32px] border border-slate-200 bg-white/80 backdrop-blur-xl p-8 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
            <div>
                <h3 class="text-2xl font-black text-slate-900">Personel Keşfet</h3>
                <p class="text-sm font-medium text-slate-500">Gelişmiş filtreleme ile istediğiniz personele anında ulaşın.</p>
            </div>
            @if($user->hasPermission('drivers.create'))
            <div class="flex items-center gap-3 w-full xl:w-auto">
                <button @click="importOpen = true" class="flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-3 rounded-2xl font-black text-sm transition-all shadow-sm hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    TOPLU EKLE
                </button>
                <a href="{{ route('drivers.create') }}" class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-black text-sm transition-all shadow-lg shadow-indigo-200 hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    YENİ PERSONEL EKLE
                </a>
            </div>
            @endif
        </div>

        <form method="GET" action="{{ route('drivers.index') }}" class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            <div class="relative group">
                <div class="absolute inset-y-0 left-5 flex items-center text-slate-400 group-focus-within:text-indigo-500 transition-colors">🔍</div>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Ad, TC, Telefon..."
                       class="w-full rounded-2xl border border-slate-100 bg-slate-50 pl-14 pr-5 py-4 text-sm font-semibold focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
            </div>

            <div class="relative">
                <select name="vehicle_id"
                        class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-semibold focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none appearance-none">
                    <option value="">Tüm Araçlar</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected(request('vehicle_id') == $vehicle->id)>
                            {{ $vehicle->plate }} - {{ $vehicle->brand }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-xs">▼</div>
            </div>

            <div class="relative">
                <select name="status"
                        class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-semibold focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none appearance-none">
                    <option value="">Aktiflik Durumu</option>
                    <option value="active" @selected(request('status') === 'active')>Aktifler</option>
                    <option value="passive" @selected(request('status') === 'passive')>Pasifler</option>
                </select>
                <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-xs">▼</div>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 rounded-2xl bg-slate-900 py-4 text-sm font-bold text-white shadow-xl shadow-slate-200 transition hover:bg-slate-800">
                    Filtrele
                </button>
                <a href="{{ route('drivers.index') }}"
                   class="flex h-[56px] w-[56px] items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50"
                   title="Filtreleri Temizle">
                    ✕
                </a>
            </div>
        </form>
    </div>

    <!-- PERSONEL LİSTESİ (TABLO) -->
    @if($drivers->count())
        <div class="rounded-[32px] border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gradient-to-r from-slate-50 to-slate-100/50 border-b border-slate-200">
                            <th class="py-5 px-6 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Personel</th>
                            <th class="py-5 px-4 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Durum</th>
                            <th class="py-5 px-4 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Telefon</th>
                            <th class="py-5 px-4 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Bağlı Araç</th>
                            <th class="py-5 px-4 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Ana Maaş</th>
                            <th class="py-5 px-4 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Ehliyet</th>
                            <th class="py-5 px-4 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Belge</th>
                            <th class="py-5 px-4 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($drivers as $driver)
                            @php
                                $docStatus = $driver->resolved_document_status ?? [
                                    'label' => 'Uygun',
                                    'class' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                    'priority' => 'ok',
                                ];
                            @endphp
                            <tr class="group hover:bg-indigo-50/30 transition-colors">
                                {{-- Personel Bilgisi --}}
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-4">
                                        <div class="relative shrink-0">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-[16px] bg-slate-100 text-2xl overflow-hidden shadow-inner group-hover:shadow-md transition-shadow">
                                                @if($driver->featured_image)
                                                    <img src="{{ asset('storage/' . $driver->featured_image->file_path) }}" class="h-full w-full object-cover">
                                                @else
                                                    🧑‍✈️
                                                @endif
                                            </div>
                                            <div class="absolute -right-0.5 -bottom-0.5 flex h-4 w-4 items-center justify-center rounded-full border-2 border-white {{ $driver->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></div>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors truncate">{{ $driver->full_name }}</div>
                                            <div class="mt-0.5 text-[11px] font-bold text-slate-400 truncate">{{ $driver->tc_no ?: 'TC Belirtilmemiş' }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Durum --}}
                                <td class="py-4 px-4">
                                    @if($driver->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-[10px] font-black text-emerald-700 uppercase tracking-wider">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 border border-slate-200 px-3 py-1 text-[10px] font-black text-slate-500 uppercase tracking-wider">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            Pasif
                                        </span>
                                    @endif
                                </td>

                                {{-- Telefon --}}
                                <td class="py-4 px-4">
                                    <span class="text-xs font-bold text-slate-700">{{ $driver->phone ?? '-' }}</span>
                                </td>

                                {{-- Bağlı Araç --}}
                                <td class="py-4 px-4">
                                    @if($driver->vehicle)
                                        <span class="inline-flex items-center gap-1.5 rounded-xl bg-blue-50 border border-blue-100 px-3 py-1.5 text-[11px] font-bold text-blue-700">
                                            🚗 {{ $driver->vehicle->plate }}
                                        </span>
                                    @else
                                        <span class="text-xs font-bold text-slate-400">Atanmamış</span>
                                    @endif
                                </td>

                                {{-- Ana Maaş --}}
                                <td class="py-4 px-4">
                                    @if(auth()->user()->hasPermission('financials.view'))
                                        <span class="text-xs font-black text-indigo-600">
                                            {{ $driver->base_salary ? number_format($driver->base_salary, 2, ',', '.') . ' ₺' : '-' }}
                                        </span>
                                    @else
                                        <span class="text-xs font-bold text-slate-400">•••</span>
                                    @endif
                                </td>

                                {{-- Ehliyet --}}
                                <td class="py-4 px-4">
                                    <span class="text-xs font-bold text-slate-700">{{ $driver->license_class ?: '-' }}</span>
                                </td>

                                {{-- Belge Durumu --}}
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider border {{ $docStatus['class'] }}">
                                        {{ $docStatus['label'] }}
                                    </span>
                                </td>

                                {{-- İşlemler --}}
                                <td class="py-4 px-4">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($user->hasPermission('drivers.view'))
                                            <a href="{{ route('drivers.show', $driver) }}"
                                               class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shadow-sm"
                                               title="Detay">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                        @endif
                                        @if($user->hasPermission('drivers.edit'))
                                            <a href="{{ route('drivers.edit', $driver) }}"
                                               class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-600 hover:bg-amber-500 hover:text-white transition-all shadow-sm"
                                               title="Düzenle">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                        @endif
                                        @if($user->hasPermission('drivers.delete'))
                                            <button type="button"
                                                    onclick="confirmDeleteDriver('{{ route('drivers.destroy', $driver) }}')"
                                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white transition-all shadow-sm"
                                                    title="Sil">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($drivers, 'links'))
            <div class="mt-10">
                {{ $drivers->links() }}
            </div>
        @endif
    @else
        <div class="rounded-[40px] border border-dashed border-slate-200 bg-white p-20 text-center shadow-sm">
            <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-slate-50 text-4xl">🔎</div>
            <h3 class="mt-6 text-xl font-black text-slate-900">Aradığınız kriterlere uygun personel bulunamadı.</h3>
            <p class="mt-2 text-sm font-medium text-slate-500">Filtreleri temizlemeyi veya yeni bir arama yapmayı deneyin.</p>
            <div class="mt-8">
                <a href="{{ route('drivers.index') }}" class="inline-flex items-center gap-2 font-bold text-indigo-600 hover:text-indigo-700">
                    Tüm Personelleri Göster →
                </a>
            </div>
        </div>
    @endif


<form id="global-delete-form" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

<!-- EXCEL İÇE AKTARMA MODALI -->
    @if(auth()->user()->hasPermission('drivers.create'))
    <div x-show="importOpen" 
         x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-0">
        
        <div x-show="importOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" 
             @click="importOpen = false"></div>

        <div x-show="importOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-white rounded-[32px] shadow-2xl overflow-hidden w-full max-w-xl transform transition-all border border-slate-100 flex flex-col max-h-[90vh]">
            
            {{-- Header --}}
            <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between shrink-0">
                <div>
                    <h3 class="text-xl font-black text-slate-800">Toplu Personel Ekle</h3>
                    <p class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-widest">Excel Dosyası ile İçe Aktar</p>
                </div>
                <button @click="importOpen = false" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            {{-- Form --}}
            <form action="{{ route('drivers.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col min-h-0">
                @csrf
                <div class="p-8 space-y-6 overflow-y-auto">
                    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5">
                        <div class="flex items-start gap-4">
                            <div class="h-10 w-10 shrink-0 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-indigo-900 mb-1">Örnek Şablonu İndirin</h4>
                                <p class="text-[11px] font-bold text-indigo-700/70 mb-3 leading-relaxed">
                                    Personellerinizi sisteme sorunsuz aktarmak için öncelikle örnek Excel şablonunu indirin ve doğru formatta doldurun.
                                </p>
                                <a href="{{ route('drivers.import.template') }}" class="inline-flex items-center gap-1.5 text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest bg-white px-3 py-1.5 rounded-lg border border-indigo-200 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    ŞABLONU İNDİR
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Excel Sütun Rehberi --}}
                    <div>
                        <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3">Sütun Doldurma Rehberi</h4>
                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 text-[10px] font-medium text-slate-600">
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">A) ad_soyad <span class="text-rose-500">*</span></span>
                                Personel ad soyadı (Örn: Ahmet Yılmaz)
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">B) tc_kimlik_no</span>
                                11 Haneli T.C. Kimlik
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">C) telefon</span>
                                Telefon (Örn: 05551234567)
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">D) eposta</span>
                                E-posta adresi
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">E) dogum_tarihi</span>
                                Örn: 15.08.1990
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">F) ise_giris_tarihi</span>
                                Örn: 01.05.2023
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">G) bagli_arac_plaka</span>
                                Örn: 34ABC123
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">H) maas</span>
                                Sadece Rakam (Örn: 17002)
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">I) ehliyet_sinifi</span>
                                B, C, D, E gibi
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">J) src_turu</span>
                                SRC1, SRC2 gibi
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">K) adres</span>
                                İkametgah adresi
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">L) notlar</span>
                                Ek özel notlar
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Excel Dosyası (.xlsx, .xls, .csv)</label>
                        <input type="file" name="excel_file" required accept=".xlsx,.xls,.csv"
                               class="w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-black file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition-all border border-slate-200 rounded-2xl cursor-pointer">
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
                    <button type="button" @click="importOpen = false" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-200/50 rounded-xl transition-all">
                        İptal
                    </button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-black text-sm transition-all shadow-lg shadow-indigo-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        YÜKLE VE AKTAR
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

<script>
    function confirmDeleteDriver(url) {
        if (confirm('Bu personeli silmek istediğinize emin misiniz? Bu işlem geri alınamaz ve tüm ilişkili veriler (belgeler vb.) temizlenecektir.')) {
            const form = document.getElementById('global-delete-form');
            form.action = url;
            form.submit();
        }
    }
</script>

</div>

@endsection
