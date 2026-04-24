@extends('layouts.app')

@section('title', 'Personeller')
@section('subtitle', 'Personel kayıtları, belge durumu ve araç atamalarını yönetin')

@section('content')

@php
    $user = auth()->user();
@endphp

<div class="space-y-8">

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
            <a href="{{ route('drivers.create') }}"
               class="inline-flex items-center gap-3 rounded-2xl bg-indigo-600 px-8 py-4 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition hover:scale-[1.02] active:scale-95">
                <span class="text-xl">+</span> Yeni Personel Tanımla
            </a>
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

    <!-- PERSONEL KARTLARI (GRID) -->
    @if($drivers->count())
        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @foreach($drivers as $driver)
                @php
                    $docStatus = $driver->resolved_document_status ?? [
                        'label' => 'Uygun',
                        'class' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                        'priority' => 'ok',
                    ];
                @endphp

                <div class="group relative flex flex-col rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-2xl hover:shadow-indigo-100 hover:-translate-y-2">
                    
                    {{-- Header Area --}}
                    <div class="flex items-start justify-between">
                        <div class="relative">
                            <div class="flex h-20 w-20 items-center justify-center rounded-[24px] bg-slate-50 text-4xl shadow-inner group-hover:scale-110 transition-transform duration-500 overflow-hidden">
                                @if($driver->featured_image)
                                    <img src="{{ asset('storage/' . $driver->featured_image->file_path) }}" class="h-full w-full object-cover">
                                @else
                                    🧑‍✈️
                                @endif
                            </div>
                            <div class="absolute -right-1 -top-1 flex h-6 w-6 items-center justify-center rounded-full border-4 border-white {{ $driver->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></div>
                        </div>

                        <div class="flex flex-col items-end gap-2">
                             <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest border {{ $docStatus['class'] }}">
                                {{ $docStatus['label'] }}
                            </span>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                #{{ str_pad($driver->id, 4, '0', STR_PAD_LEFT) }}
                            </div>
                        </div>
                    </div>

                    {{-- Identity Area --}}
                    <div class="mt-6">
                        <h4 class="text-xl font-black text-slate-900 group-hover:text-indigo-600 transition-colors leading-tight">{{ $driver->full_name }}</h4>
                        <div class="mt-2 flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider">
                             <span>{{ $driver->tc_no ?: 'TC BELİRTİLMEMİŞ' }}</span>
                        </div>
                    </div>

                    {{-- Info Grid --}}
                    <div class="mt-6 grid grid-cols-2 gap-4">
                        <div class="rounded-2xl bg-slate-50 p-3 border border-slate-100">
                            <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Bağlı Araç</div>
                            <div class="mt-1 text-xs font-bold text-slate-900 truncate">{{ $driver->vehicle?->plate ?? 'ATANMAMIŞ' }}</div>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3 border border-slate-100">
                            <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Ana Maaş</div>
                            <div class="mt-1 text-xs font-bold text-indigo-600">{{ $driver->base_salary ? number_format($driver->base_salary, 2, ',', '.') . ' ₺' : '-' }}</div>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3 border border-slate-100">
                            <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Telefon</div>
                            <div class="mt-1 text-xs font-bold text-slate-900">{{ $driver->phone ?? '-' }}</div>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3 border border-slate-100">
                            <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Ehliyet</div>
                            <div class="mt-1 text-xs font-bold text-slate-900">{{ $driver->license_class ?: '-' }}</div>
                        </div>
                    </div>

                    {{-- Footer Info --}}
                    <div class="mt-6 flex items-center justify-between border-t border-slate-50 pt-5">
                         <div class="flex flex-col">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">İşe Giriş</span>
                            <span class="text-xs font-bold text-slate-700">{{ optional($driver->start_date)->format('d.m.Y') ?: '-' }}</span>
                         </div>
                         <div class="flex flex-col items-end">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">E-Posta</span>
                            <span class="text-xs font-bold text-slate-700 truncate max-w-[120px]">{{ $driver->email ?: '-' }}</span>
                         </div>
                    </div>

                    {{-- Actions Overlay --}}
                    <div class="mt-6 flex items-center gap-2">
                        @if($user->hasPermission('drivers.view'))
                        <a href="{{ route('drivers.show', $driver) }}"
                           class="flex-1 rounded-xl bg-indigo-50 py-3 text-center text-xs font-black text-indigo-600 uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                            Detay
                        </a>
                        @endif
                        
                        @if($user->hasPermission('drivers.edit'))
                        <a href="{{ route('drivers.edit', $driver) }}"
                           class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-600 hover:bg-slate-900 hover:text-white transition-all">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </a>
                        @endif

                        @if($user->hasPermission('drivers.delete'))
                        <button type="button" 
                                onclick="confirmDeleteDriver('{{ route('drivers.destroy', $driver) }}')"
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                        @endif
                    </div>
                </div>
            @endforeach
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

</div>

<form id="global-delete-form" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

<script>
    function confirmDeleteDriver(url) {
        if (confirm('Bu personeli silmek istediğinize emin misiniz? Bu işlem geri alınamaz ve tüm ilişkili veriler (belgeler vb.) temizlenecektir.')) {
            const form = document.getElementById('global-delete-form');
            form.action = url;
            form.submit();
        }
    }
</script>

@endsection