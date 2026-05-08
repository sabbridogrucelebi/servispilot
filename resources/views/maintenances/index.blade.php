@extends('layouts.app')

@section('title', 'Bakım / Tamir')
@section('subtitle', 'Araç bakım ve servis kayıtlarını merkezi olarak yönetin')

@section('content')

<div class="space-y-6" x-data="{ importOpen: false }">

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-end">
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('maintenances.settings') }}"
               class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <span class="text-base">⚙️</span>
                <span>Ayarlar</span>
            </a>

            <a href="{{ route('maintenances.export.excel', request()->query()) }}"
               class="inline-flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100">
                <span>📊</span>
                <span>Excel İndir</span>
            </a>

            <a href="{{ route('maintenances.export.pdf', request()->query()) }}"
               class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700 shadow-sm transition hover:bg-rose-100">
                <span>📄</span>
                <span>PDF İndir</span>
            </a>

            @if(auth()->user()->hasPermission('maintenances.create'))
            <div class="flex items-center gap-3 w-full xl:w-auto">
                <button @click="importOpen = true" class="flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-3 rounded-2xl font-black text-sm transition-all shadow-sm hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    TOPLU EKLE
                </button>
                <a href="{{ route('maintenances.create') }}" class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-black text-sm transition-all shadow-lg shadow-indigo-200 hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    YENİ BAKIM EKLE
                </a>
            </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

        <a href="{{ route('maintenances.index') }}" class="block relative overflow-hidden rounded-[28px] bg-gradient-to-br from-blue-500 to-indigo-600 p-5 text-white shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex items-center justify-between">
            <div class="relative z-10">
                <div class="text-sm font-medium text-white/90">Toplam Bakım Kaydı</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ $totalMaintenances }}</div>
                <div class="mt-2 text-xs text-white/80">Sistemde kayıtlı tüm bakım işlemleri</div>
            </div>
            <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Objects/Hammer%20and%20Wrench.png" alt="Kayıtlar" class="relative z-10 w-16 h-16 drop-shadow-xl select-none pointer-events-none flex-shrink-0 ml-2" />
        </a>

        <a href="{{ route('maintenances.index', ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->endOfMonth()->format('Y-m-d')]) }}" class="block relative overflow-hidden rounded-[28px] bg-gradient-to-br from-emerald-500 to-teal-500 p-5 text-white shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex items-center justify-between">
            <div class="relative z-10">
                <div class="text-sm font-medium text-white/90">Bu Ay Yapılan</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ $thisMonthMaintenances }}</div>
                <div class="mt-2 text-xs text-white/80">Bu ay tamamlanan bakım sayısı</div>
            </div>
            <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Objects/Calendar.png" alt="Takvim" class="relative z-10 w-16 h-16 drop-shadow-xl select-none pointer-events-none flex-shrink-0 ml-2" />
        </a>

        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-rose-500 to-pink-500 p-5 text-white shadow-xl flex items-center justify-between">
            <div class="relative z-10">
                <div class="text-sm font-medium text-white/90">Toplam Maliyet</div>
                <div class="mt-3 text-2xl xl:text-3xl font-extrabold tracking-tight truncate">{{ number_format($totalAmount, 2, ',', '.') }} ₺</div>
                <div class="mt-2 text-xs text-white/80">Filtrelenen kayıtların toplam tutarı</div>
            </div>
            <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Objects/Money%20Bag.png" alt="Maliyet" class="relative z-10 w-16 h-16 drop-shadow-xl select-none pointer-events-none flex-shrink-0 ml-2" />
        </div>

    </div>

    <div class="rounded-[30px] border border-slate-200/60 bg-white/90 p-5 shadow-xl backdrop-blur">
        <form method="GET" action="{{ route('maintenances.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                    Arama
                </label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Araç, servis, bakım adı ile ara..."
                       class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                    Araç
                </label>
                <select name="vehicle_id"
                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tüm Araçlar</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ (string) request('vehicle_id') === (string) $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->plate }}{{ $vehicle->brand ? ' - ' . $vehicle->brand : '' }}{{ $vehicle->model ? ' ' . $vehicle->model : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                    Bakım Türü
                </label>
                <select name="maintenance_type"
                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tüm Türler</option>
                    @foreach($maintenanceTypes as $type)
                        <option value="{{ $type }}" {{ request('maintenance_type') === $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                    Başlangıç Tarihi
                </label>
                <input type="date"
                       name="start_date"
                       value="{{ request('start_date') }}"
                       class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                    Bitiş Tarihi
                </label>
                <input type="date"
                       name="end_date"
                       value="{{ request('end_date') }}"
                       class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex flex-wrap justify-end gap-3 pt-2 md:col-span-2 xl:col-span-5">
                <a href="{{ route('maintenances.index') }}"
                   class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Temizle
                </a>

                <button type="submit"
                        class="rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200/60 transition hover:scale-[1.01]">
                    Filtrele
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-[30px] border border-slate-200/60 bg-white/90 shadow-xl backdrop-blur"
         x-data="{
             selectedIds: [],
             allIds: [{{ $maintenances->pluck('id')->implode(',') }}],
             get allSelected() { return this.allIds.length > 0 && this.selectedIds.length === this.allIds.length },
             get someSelected() { return this.selectedIds.length > 0 },
             toggleAll() {
                 if (this.allSelected) {
                     this.selectedIds = [];
                 } else {
                     this.selectedIds = [...this.allIds];
                 }
             },
             toggleId(id) {
                 const idx = this.selectedIds.indexOf(id);
                 if (idx > -1) {
                     this.selectedIds.splice(idx, 1);
                 } else {
                     this.selectedIds.push(id);
                 }
             }
         }">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Bakım Kayıt Listesi</h3>
                <p class="mt-1 text-sm text-slate-500">Tüm bakım ve tamir kayıtlarını detaylı görüntüleyin</p>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-sm font-medium text-slate-400">
                    Toplam {{ $maintenances->count() }} kayıt
                </div>

                <div class="relative">
                    <button type="button"
                            id="columnFilterToggle"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18l-7 8v6l-4 2v-8L3 4z" />
                        </svg>
                    </button>

                    <div id="columnFilterPanel"
                         class="hidden absolute right-0 top-14 z-30 w-80 overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-[0_20px_60px_rgba(15,23,42,0.18)]">
                        <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-5 py-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-sm font-bold text-slate-800">Kolon Görünümü</div>
                                    <div class="mt-1 text-xs leading-5 text-slate-500">
                                        Tabloda görmek istediğin alanları açıp kapatabilirsin.
                                    </div>
                                </div>

                                <button type="button"
                                        id="resetColumnPrefs"
                                        class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-200">
                                    Sıfırla
                                </button>
                            </div>
                        </div>

                        <div class="max-h-[420px] overflow-y-auto p-4">
                            <div class="grid grid-cols-1 gap-2">

                                <label class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/60">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-800">Araç</div>
                                        <div class="text-xs text-slate-500">Plaka ve araç bilgileri</div>
                                    </div>
                                    <input type="checkbox" class="column-toggle h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" data-column="col-arac" checked>
                                </label>

                                <label class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/60">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-800">Bakım</div>
                                        <div class="text-xs text-slate-500">İşlem adı ve açıklama</div>
                                    </div>
                                    <input type="checkbox" class="column-toggle h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" data-column="col-bakim" checked>
                                </label>

                                <label class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/60">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-800">Tür</div>
                                        <div class="text-xs text-slate-500">Bakım kategori bilgisi</div>
                                    </div>
                                    <input type="checkbox" class="column-toggle h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" data-column="col-tur" checked>
                                </label>

                                <label class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/60">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-800">Tarih</div>
                                        <div class="text-xs text-slate-500">Bakım tarihi ve sonraki tarih</div>
                                    </div>
                                    <input type="checkbox" class="column-toggle h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" data-column="col-tarih" checked>
                                </label>

                                <label class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/60">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-800">KM</div>
                                        <div class="text-xs text-slate-500">Bakım KM ve sonraki KM</div>
                                    </div>
                                    <input type="checkbox" class="column-toggle h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" data-column="col-km" checked>
                                </label>

                                <label class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/60">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-800">Servis</div>
                                        <div class="text-xs text-slate-500">Usta / servis adı</div>
                                    </div>
                                    <input type="checkbox" class="column-toggle h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" data-column="col-servis" checked>
                                </label>

                                <label class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/60">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-800">Durum</div>
                                        <div class="text-xs text-slate-500">Tamamlanma durumu</div>
                                    </div>
                                    <input type="checkbox" class="column-toggle h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" data-column="col-durum" checked>
                                </label>

                                <label class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/60">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-800">Tutar</div>
                                        <div class="text-xs text-slate-500">Bakım maliyeti</div>
                                    </div>
                                    <input type="checkbox" class="column-toggle h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" data-column="col-tutar" checked>
                                </label>

                                <label class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/60">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-800">İşlemler</div>
                                        <div class="text-xs text-slate-500">Düzenle ve sil butonları</div>
                                    </div>
                                    <input type="checkbox" class="column-toggle h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" data-column="col-islemler" checked>
                                </label>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Toplu Silme Aksiyonu --}}
        <div x-show="someSelected"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             style="display: none;"
             class="border-b border-rose-100 bg-gradient-to-r from-rose-50 to-pink-50 px-6 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-rose-500 text-white text-xs font-bold" x-text="selectedIds.length"></span>
                <span class="text-sm font-semibold text-rose-800">kayıt seçildi</span>
            </div>
            <form action="{{ route('maintenances.bulk-delete') }}" method="POST" onsubmit="return confirm('Seçilen bakım kayıtlarını silmek istediğine emin misin?')">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-rose-600 to-pink-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-rose-200 transition hover:scale-[1.02]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Seçilenleri Sil
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1320px]">
                <thead class="border-b border-slate-100 bg-slate-50">
                    <tr>
                        <th class="w-12 px-4 py-4">
                            <input type="checkbox"
                                   @click="toggleAll()"
                                   :checked="allSelected"
                                   :indeterminate="someSelected && !allSelected"
                                   class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </th>
                        <th class="col-arac px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Araç</th>
                        <th class="col-bakim px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Bakım</th>
                        <th class="col-tur px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Tür</th>
                        <th class="col-tarih px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Tarih</th>
                        <th class="col-km px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">KM</th>
                        <th class="col-servis px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Servis</th>
                        <th class="col-durum px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Durum</th>
                        <th class="col-tutar px-6 py-4 text-right text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Tutar</th>
                        <th class="col-islemler px-6 py-4 text-center text-xs font-bold uppercase tracking-[0.14em] text-slate-500">İşlemler</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($maintenances as $maintenance)
                        <tr class="transition duration-200 hover:bg-indigo-50/40"
                            :class="selectedIds.includes({{ $maintenance->id }}) ? 'bg-indigo-50/60' : ''">
                            <td class="w-12 px-4 py-5">
                                <input type="checkbox"
                                       value="{{ $maintenance->id }}"
                                       @click="toggleId({{ $maintenance->id }})"
                                       :checked="selectedIds.includes({{ $maintenance->id }})"
                                       class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            </td>
                            <td class="col-arac px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center rounded-2xl shadow">
                                        @php
                                            $mTypeUpper = mb_strtoupper($maintenance->maintenance_type ?? '', 'UTF-8');
                                            $mIconUrl = 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Objects/Hammer%20and%20Wrench.png';
                                            
                                            if (str_contains($mTypeUpper, 'LASTİK') || str_contains($mTypeUpper, 'LASTIK')) {
                                                $mIconUrl = 'https://raw.githubusercontent.com/microsoft/fluentui-emoji/main/assets/Wheel/3D/wheel_3d.png';
                                            } elseif (str_contains($mTypeUpper, 'ALT YAĞ') || str_contains($mTypeUpper, 'ALT YAG')) {
                                                $mIconUrl = 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Objects/Gear.png';
                                            } elseif (str_contains($mTypeUpper, 'YAĞ') || str_contains($mTypeUpper, 'YAG') || str_contains($mTypeUpper, 'FİLTRE') || str_contains($mTypeUpper, 'FILTRE')) {
                                                $mIconUrl = 'https://raw.githubusercontent.com/microsoft/fluentui-emoji/main/assets/Oil%20drum/3D/oil_drum_3d.png';
                                            } elseif (str_contains($mTypeUpper, 'KAPORTA') || str_contains($mTypeUpper, 'KAZA')) {
                                                $mIconUrl = 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Travel%20and%20places/Automobile.png';
                                            }
                                        @endphp
                                        <img src="{{ $mIconUrl }}" alt="Bakım" class="w-12 h-12 drop-shadow-xl" />
                                    </div>

                                    <div>
                                        <div class="text-sm font-extrabold tracking-wide text-slate-900">
                                            {{ $maintenance->vehicle->plate ?? '-' }}
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ $maintenance->vehicle->brand ?? '-' }} {{ $maintenance->vehicle->model ?? '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="col-bakim px-6 py-5">
                                <div class="text-sm font-semibold text-slate-800">
                                    {{ $maintenance->title ?? '-' }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $maintenance->description ?: 'Açıklama yok' }}
                                </div>
                            </td>

                            <td class="col-tur px-6 py-5 text-sm font-semibold text-slate-800">
                                {{ $maintenance->maintenance_type ?: '-' }}
                            </td>

                            <td class="col-tarih px-6 py-5">
                                <div class="text-sm font-semibold text-slate-800">
                                    {{ optional($maintenance->service_date)->format('d.m.Y') ?: '-' }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    @if($maintenance->next_service_date)
                                        Sonraki: {{ optional($maintenance->next_service_date)->format('d.m.Y') }}
                                    @else
                                        Sonraki tarih yok
                                    @endif
                                </div>
                            </td>

                            <td class="col-km px-6 py-5">
                                <div class="text-sm font-semibold text-slate-800">
                                    {{ $maintenance->km ? number_format($maintenance->km, 0, ',', '.') . ' KM' : '-' }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    @if($maintenance->next_service_km)
                                        Sonraki: {{ number_format($maintenance->next_service_km, 0, ',', '.') }} KM
                                    @else
                                        Sonraki KM yok
                                    @endif
                                </div>
                            </td>

                            <td class="col-servis px-6 py-5 text-sm text-slate-600">
                                {{ $maintenance->service_name ?: '-' }}
                            </td>

                            <td class="col-durum px-6 py-5">
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    ● Tamamlandı
                                </span>
                            </td>

                            <td class="col-tutar px-6 py-5 text-right text-sm font-bold text-slate-800">
                                {{ number_format((float) ($maintenance->amount ?? 0), 2, ',', '.') }} ₺
                            </td>

                            <td class="col-islemler px-6 py-5">
                                <div class="flex items-center justify-center gap-2">
                                @if(auth()->user()->hasPermission('maintenances.edit'))
                                    <a href="{{ route('maintenances.edit', $maintenance) }}"
                                       class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">
                                        Düzenle
                                    </a>
                                @endif

                                @if(auth()->user()->hasPermission('maintenances.delete'))
                                    <form action="{{ route('maintenances.destroy', $maintenance) }}" method="POST" onsubmit="return confirm('Bu bakım kaydını silmek istediğine emin misin?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                            Sil
                                        </button>
                                    </form>
                                @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-10">
                                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                                    <div class="mb-3 flex justify-center items-center">
                                        <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Objects/Hammer%20and%20Wrench.png" alt="Bakım Yok" class="w-20 h-20 drop-shadow-xl" />
                                    </div>
                                    <div class="text-base font-semibold text-slate-700">Henüz bakım kaydı bulunmuyor</div>
                                    <div class="mt-1 text-sm text-slate-500">Bakım ekleyerek listeyi oluşturmaya başlayabilirsin.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>



    {{-- Toplu Bakım Ekle Modal --}}
    @if(auth()->user()->hasPermission('maintenances.create'))
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
                    <h3 class="text-xl font-black text-slate-800">Toplu Bakım Ekle</h3>
                    <p class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-widest">Excel Dosyası ile İçe Aktar</p>
                </div>
                <button @click="importOpen = false" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            {{-- Form --}}
            <form action="{{ route('maintenances.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col min-h-0">
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
                                    Bakım kayıtlarını sisteme sorunsuz aktarmak için öncelikle örnek Excel şablonunu indirin ve doğru formatta doldurun.
                                </p>
                                <a href="{{ route('maintenances.import.template') }}" class="inline-flex items-center gap-1.5 text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest bg-white px-3 py-1.5 rounded-lg border border-indigo-200 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    ŞABLONU İNDİR
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Excel Sütun Rehberi --}}
                    <div>
                        <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3">Sütun Doldurma Rehberi</h4>
                        <div class="grid grid-cols-2 gap-3 text-[10px] font-medium text-slate-600">
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">A) plaka <span class="text-rose-500">*</span></span>
                                Aracın plakası (Örn: 34ABC123)
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">B) bakim_turu <span class="text-rose-500">*</span></span>
                                YAĞ BAKIMI, LASTİK BAKIMI vb.
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">C) bakim_adi <span class="text-rose-500">*</span></span>
                                Örn: Yağ Filtresi Değişimi
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">D) servis_adi</span>
                                Usta veya servis firması
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">E) servis_tarihi <span class="text-rose-500">*</span></span>
                                Bakım tarihi (Örn: 15.05.2026)
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">F) km</span>
                                Yapıldığı anki kilometre
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">G) tutar</span>
                                Sadece rakam
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">H) sonraki_servis_tarihi</span>
                                Opsiyonel tarih (Örn: 15.05.2026)
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">I) sonraki_servis_km</span>
                                Opsiyonel KM
                            </div>
                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl">
                                <span class="font-black text-slate-800 block mb-0.5">J) aciklama</span>
                                Ek notlar
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
document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('columnFilterToggle');
    const panel = document.getElementById('columnFilterPanel');
    const checkboxes = document.querySelectorAll('.column-toggle');
    const resetButton = document.getElementById('resetColumnPrefs');

    function setColumnVisibility(columnClass, visible) {
        document.querySelectorAll('.' + columnClass).forEach(function (el) {
            el.style.display = visible ? '' : 'none';
        });
    }

    function applySavedPreferences() {
        checkboxes.forEach(function (checkbox) {
            const columnClass = checkbox.dataset.column;
            const saved = localStorage.getItem('maintenance_table_' + columnClass);

            if (saved === 'hidden') {
                checkbox.checked = false;
                setColumnVisibility(columnClass, false);
            } else {
                checkbox.checked = true;
                setColumnVisibility(columnClass, true);
            }
        });
    }

    if (toggleButton && panel) {
        toggleButton.addEventListener('click', function (event) {
            event.stopPropagation();
            panel.classList.toggle('hidden');
        });

        panel.addEventListener('click', function (event) {
            event.stopPropagation();
        });

        document.addEventListener('click', function () {
            panel.classList.add('hidden');
        });
    }

    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const columnClass = checkbox.dataset.column;
            const visible = checkbox.checked;

            setColumnVisibility(columnClass, visible);
            localStorage.setItem('maintenance_table_' + columnClass, visible ? 'visible' : 'hidden');
        });
    });

    if (resetButton) {
        resetButton.addEventListener('click', function () {
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = true;
                const columnClass = checkbox.dataset.column;
                setColumnVisibility(columnClass, true);
                localStorage.removeItem('maintenance_table_' + columnClass);
            });
        });
    }

    applySavedPreferences();
});
</script>

</div>

@endsection
