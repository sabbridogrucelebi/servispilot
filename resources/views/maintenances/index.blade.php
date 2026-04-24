@extends('layouts.app')

@section('title', 'Bakım / Tamir')
@section('subtitle', 'Araç bakım ve servis kayıtlarını merkezi olarak yönetin')

@section('content')

<div class="space-y-6">

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
            <a href="{{ route('maintenances.create') }}"
               class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-slate-700 to-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-500/20 transition hover:scale-[1.02]">
                <span class="text-base">+</span>
                <span>Yeni Bakım Ekle</span>
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-blue-500 to-indigo-600 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Toplam Bakım Kaydı</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ $totalMaintenances }}</div>
                <div class="mt-2 text-xs text-white/75">Sistemde kayıtlı tüm bakım işlemleri</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-emerald-500 to-teal-500 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Bu Ay Yapılan</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ $thisMonthMaintenances }}</div>
                <div class="mt-2 text-xs text-white/75">Bu ay tamamlanan bakım sayısı</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-rose-500 to-pink-500 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Toplam Maliyet</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($totalAmount, 2, ',', '.') }} ₺</div>
                <div class="mt-2 text-xs text-white/75">Filtrelenen kayıtların toplam tutarı</div>
            </div>
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
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-700 to-slate-900 text-lg text-white shadow">
                                        🛠️
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
                                    <div class="mb-3 text-4xl">🛠️</div>
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

</div>

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

@endsection