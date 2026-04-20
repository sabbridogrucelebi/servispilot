@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="tripMatrixPage()">

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                <span>🗓️</span>
                <span>Aylık Puantaj Yönetimi</span>
            </div>

            <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">
                Puantaj / Sefer Planlama
            </h1>

            <p class="mt-2 text-sm font-medium text-slate-500">
                Müşteri bazlı aylık sefer girişlerini, güzergah tutarlarını ve genel toplamları yönetin.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('trips.create') }}"
               class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <span>＋</span>
                <span>Manuel Sefer Ekle</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-blue-500 to-indigo-600 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Seçili Müşteri</div>
                <div class="mt-3 text-2xl font-extrabold tracking-tight">
                    {{ $selectedCustomer?->company_name ?? 'Seçilmedi' }}
                </div>
                <div class="mt-2 text-xs text-white/75">Puantaj üretilecek cari firma</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-emerald-500 to-teal-500 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Ay / Yıl</div>
                <div class="mt-3 text-2xl font-extrabold tracking-tight">
                    {{ $monthOptions[$selectedMonth] ?? '-' }} {{ $selectedYear }}
                </div>
                <div class="mt-2 text-xs text-white/75">Aktif çalışma dönemi</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-violet-500 to-fuchsia-500 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Aktif Güzergah</div>
                <div class="mt-3 text-2xl font-extrabold tracking-tight">{{ $serviceRoutes->count() }}</div>
                <div class="mt-2 text-xs text-white/75">Bu müşteri için görünür rota sayısı</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-amber-500 to-orange-500 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Aylık Net Toplam</div>
                <div class="mt-3 text-2xl font-extrabold tracking-tight">
                    {{ number_format($summary['net_total'] ?? 0, 2, ',', '.') }} ₺
                </div>
                <div class="mt-2 text-xs text-white/75">KDV ve tevkifat sonrası</div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('trips.index') }}"
          class="rounded-[30px] border border-slate-200/60 bg-white/90 p-5 shadow-xl backdrop-blur">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="xl:col-span-2">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                    Müşteri Seç
                </label>
                <select name="customer_id"
                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Aktif müşteri seçiniz</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ (string) $selectedCustomerId === (string) $customer->id ? 'selected' : '' }}>
                            {{ $customer->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                    Ay
                </label>
                <select name="month"
                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    @foreach($monthOptions as $monthKey => $monthLabel)
                        <option value="{{ $monthKey }}" {{ (int) $selectedMonth === (int) $monthKey ? 'selected' : '' }}>
                            {{ $monthLabel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                    Yıl
                </label>
                <select name="year"
                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    @foreach($yearOptions as $year)
                        <option value="{{ $year }}" {{ (int) $selectedYear === (int) $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('trips.index') }}"
               class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <span>Sıfırla</span>
            </a>

            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:scale-[1.02]">
                <span>Göster</span>
            </button>
        </div>
    </form>

    @if(!$selectedCustomer)
        <div class="rounded-[30px] border border-dashed border-slate-200 bg-slate-50 px-6 py-16 text-center">
            <div class="mx-auto max-w-2xl">
                <div class="mb-4 text-5xl">🧾</div>
                <div class="text-xl font-bold text-slate-800">Önce müşteri seçin</div>
                <div class="mt-2 text-sm leading-7 text-slate-500">
                    Aylık puantaj tablosunu oluşturmak için aktif müşterilerden birini seçip ay / yıl bilgisiyle birlikte görüntüleyin.
                </div>
            </div>
        </div>
    @elseif($selectedCustomer && !$serviceRoutes->count())
        <div class="rounded-[30px] border border-dashed border-slate-200 bg-slate-50 px-6 py-16 text-center">
            <div class="mx-auto max-w-2xl">
                <div class="mb-4 text-5xl">🛣️</div>
                <div class="text-xl font-bold text-slate-800">Bu müşteri için aktif güzergah yok</div>
                <div class="mt-2 text-sm leading-7 text-slate-500">
                    Puantaj tablosunun oluşabilmesi için bu müşteriye ait en az bir aktif servis güzergahı bulunmalıdır.
                </div>
            </div>
        </div>
    @else
        <div class="overflow-hidden rounded-[30px] border border-slate-200/60 bg-white/95 shadow-xl backdrop-blur">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Aylık Puantaj Matrisi</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Hücreye tutarı yazıp Enter’a basarak otomatik kaydedin. Boş bırakıp Enter’a basarsanız kayıt temizlenir.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <template x-for="hiddenRoute in hiddenRoutes" :key="hiddenRoute.id">
                        <button type="button"
                                @click="showRoute(hiddenRoute.id)"
                                class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-200">
                            <span x-text="hiddenRoute.name"></span>
                            <span>Geri Aç</span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1100px] w-full">
                    <thead class="border-b border-slate-100 bg-slate-50">
                        <tr>
                            <th class="sticky left-0 z-20 border-r border-slate-100 bg-slate-50 px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">
                                Tarih
                            </th>
                            <th class="sticky left-[120px] z-20 border-r border-slate-100 bg-slate-50 px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">
                                Gün
                            </th>

                            @foreach($serviceRoutes as $route)
                                <th x-show="!isRouteHidden({{ $route->id }})"
                                    class="min-w-[220px] border-r border-slate-100 px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">
                                    <div class="space-y-2">
                                        <button type="button"
                                                @dblclick="hideRoute({{ $route->id }}, @js($route->route_name))"
                                                class="block text-left">
                                            <div class="text-sm font-extrabold normal-case tracking-normal text-slate-900">
                                                {{ $route->route_name }}
                                            </div>

                                            <div class="mt-1 flex flex-wrap gap-2">
                                                @if($route->service_type === 'both')
                                                    <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-semibold text-blue-700">
                                                        Sabah + Akşam
                                                    </span>
                                                @elseif($route->service_type === 'morning')
                                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                                                        Sadece Sabah
                                                    </span>
                                                @elseif($route->service_type === 'evening')
                                                    <span class="inline-flex rounded-full bg-orange-100 px-2.5 py-1 text-[11px] font-semibold text-orange-700">
                                                        Sadece Akşam
                                                    </span>
                                                @elseif($route->service_type === 'shift')
                                                    <span class="inline-flex rounded-full bg-violet-100 px-2.5 py-1 text-[11px] font-semibold text-violet-700">
                                                        Vardiya
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="mt-2 space-y-1 text-[11px] font-semibold normal-case tracking-normal text-slate-500">
                                                <div>
                                                    {{ $route->service_type === 'shift' ? 'Toplama:' : 'Sabah:' }}
                                                    {{ $route->morningVehicle?->plate ?? '-' }}
                                                </div>
                                                <div>
                                                    {{ $route->service_type === 'shift' ? 'Dağıtım:' : 'Akşam:' }}
                                                    {{ $route->eveningVehicle?->plate ?? '-' }}
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach($monthDays as $day)
                            <tr class="transition hover:bg-slate-50/60">
                                <td class="sticky left-0 z-10 border-r border-slate-100 bg-white px-4 py-3">
                                    <div class="text-sm font-bold text-slate-900">
                                        {{ $day['display_date'] }}
                                    </div>

                                    @if($day['holiday_name'])
                                        <div class="mt-1 text-[11px] font-semibold text-violet-600">
                                            {{ $day['holiday_name'] }}
                                        </div>
                                    @endif
                                </td>

                                <td class="sticky left-[120px] z-10 border-r border-slate-100 bg-white px-4 py-3">
                                    <div class="text-sm font-semibold
                                        @if($day['is_holiday']) text-violet-700
                                        @elseif($day['is_weekend']) text-rose-600
                                        @else text-slate-700
                                        @endif">
                                        {{ $day['day_name'] }}
                                    </div>
                                </td>

                                @foreach($serviceRoutes as $route)
                                    @php
                                        $cell = $matrix[$day['date_key']][$route->id] ?? null;
                                        $isWeekend = $day['is_weekend'] ?? false;
                                        $isHoliday = $day['is_holiday'] ?? false;
                                    @endphp

                                    <td x-show="!isRouteHidden({{ $route->id }})"
                                        class="border-r border-slate-100 px-3 py-3 align-top">
                                        <div class="space-y-2">
                                            <div class="relative">
                                                <input type="text"
                                                       value="{{ $cell['display_value'] ?? '' }}"
                                                       data-trip-cell="true"
                                                       data-route-id="{{ $route->id }}"
                                                       data-date="{{ $day['date_key'] }}"
                                                       data-original="{{ $cell['display_value'] ?? '' }}"
                                                       data-route-name="{{ $route->route_name }}"
                                                       data-default-vehicle="{{ $cell['vehicle_plate'] ?? ($route->morningVehicle?->plate ?? $route->eveningVehicle?->plate ?? '') }}"
                                                       class="trip-cell-input w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-800 shadow-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500 {{ $isHoliday ? 'ring-1 ring-violet-200' : '' }} {{ $isWeekend ? 'ring-1 ring-rose-100' : '' }}"
                                                       placeholder="0,00">

                                                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                                    <div class="hidden h-2.5 w-2.5 rounded-full bg-emerald-500" data-save-dot></div>
                                                </div>
                                            </div>

                                            <div class="min-h-[34px] rounded-xl bg-slate-50 px-3 py-2 text-[11px] font-semibold text-slate-500">
                                                @if(!empty($cell['vehicle_plate']))
                                                    <div>Plaka: <span class="text-slate-700">{{ $cell['vehicle_plate'] }}</span></div>
                                                @else
                                                    <div>Plaka: <span class="text-slate-400">Henüz işlenmedi</span></div>
                                                @endif

                                                @if(!empty($cell['trip_status']))
                                                    <div class="mt-0.5">Durum: <span class="text-slate-700">{{ $cell['trip_status'] }}</span></div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot class="border-t border-slate-100 bg-slate-50">
                        <tr>
                            <td colspan="2" class="sticky left-0 z-10 border-r border-slate-100 bg-slate-50 px-4 py-4 text-right text-sm font-black text-slate-900">
                                Güzergah Toplamları
                            </td>

                            @foreach($serviceRoutes as $route)
                                <td x-show="!isRouteHidden({{ $route->id }})"
                                    class="border-r border-slate-100 px-4 py-4 text-center">
                                    <div class="text-sm font-extrabold text-blue-700">
                                        {{ number_format($routeTotals[$route->id] ?? 0, 2, ',', '.') }} ₺
                                    </div>
                                </td>
                            @endforeach
                        </tr>

                        <tr>
                            <td colspan="{{ 2 + $serviceRoutes->count() }}" class="px-5 py-5">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                                    <div class="rounded-2xl bg-white px-4 py-4 shadow-sm">
                                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Ara Toplam</div>
                                        <div class="mt-2 text-lg font-extrabold text-slate-900">
                                            {{ number_format($summary['subtotal'] ?? 0, 2, ',', '.') }} ₺
                                        </div>
                                    </div>

                                    <div class="rounded-2xl bg-white px-4 py-4 shadow-sm">
                                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">KDV</div>
                                        <div class="mt-2 text-lg font-extrabold text-emerald-700">
                                            %{{ rtrim(rtrim((string) ($summary['vat_rate'] ?? 0), '0'), '.') ?: '0' }}
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ number_format($summary['vat_amount'] ?? 0, 2, ',', '.') }} ₺
                                        </div>
                                    </div>

                                    <div class="rounded-2xl bg-white px-4 py-4 shadow-sm">
                                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Genel Toplam</div>
                                        <div class="mt-2 text-lg font-extrabold text-blue-700">
                                            {{ number_format($summary['grand_total'] ?? 0, 2, ',', '.') }} ₺
                                        </div>
                                    </div>

                                    <div class="rounded-2xl bg-white px-4 py-4 shadow-sm">
                                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Tevkifat</div>
                                        <div class="mt-2 text-lg font-extrabold text-violet-700">
                                            {{ $summary['withholding_rate'] ?: 'Yok' }}
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ number_format($summary['withholding_amount'] ?? 0, 2, ',', '.') }} ₺
                                        </div>
                                    </div>

                                    <div class="rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 px-4 py-4 text-white shadow-sm">
                                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-white/60">Net Tahsilat</div>
                                        <div class="mt-2 text-lg font-extrabold">
                                            {{ number_format($summary['net_total'] ?? 0, 2, ',', '.') }} ₺
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function tripMatrixPage() {
        return {
            hiddenRouteIds: JSON.parse(localStorage.getItem('trip_hidden_route_ids') || '[]'),
            hiddenRoutes: JSON.parse(localStorage.getItem('trip_hidden_routes_meta') || '[]'),

            init() {
                this.bindInputs();
            },

            bindInputs() {
                this.$nextTick(() => {
                    document.querySelectorAll('[data-trip-cell="true"]').forEach((input) => {
                        if (input.dataset.bound === 'true') {
                            return;
                        }

                        input.dataset.bound = 'true';

                        input.addEventListener('keydown', (event) => {
                            if (event.key === 'Enter') {
                                event.preventDefault();
                                this.saveCell(input);
                            }
                        });

                        input.addEventListener('blur', () => {
                            const original = input.dataset.original || '';
                            if ((input.value || '').trim() !== original.trim()) {
                                this.saveCell(input);
                            }
                        });
                    });
                });
            },

            isRouteHidden(routeId) {
                return this.hiddenRouteIds.includes(routeId);
            },

            hideRoute(routeId, routeName) {
                if (!this.hiddenRouteIds.includes(routeId)) {
                    this.hiddenRouteIds.push(routeId);
                }

                if (!this.hiddenRoutes.find(item => Number(item.id) === Number(routeId))) {
                    this.hiddenRoutes.push({ id: routeId, name: routeName });
                }

                this.persistHiddenRoutes();
            },

            showRoute(routeId) {
                this.hiddenRouteIds = this.hiddenRouteIds.filter(id => Number(id) !== Number(routeId));
                this.hiddenRoutes = this.hiddenRoutes.filter(item => Number(item.id) !== Number(routeId));
                this.persistHiddenRoutes();
            },

            persistHiddenRoutes() {
                localStorage.setItem('trip_hidden_route_ids', JSON.stringify(this.hiddenRouteIds));
                localStorage.setItem('trip_hidden_routes_meta', JSON.stringify(this.hiddenRoutes));
            },

            normalizeNumber(value) {
                if (!value) return null;

                let normalized = value.toString().trim();
                if (normalized === '') return null;

                normalized = normalized.replace(/\./g, '').replace(',', '.');

                const parsed = parseFloat(normalized);

                if (isNaN(parsed)) {
                    return null;
                }

                return parsed;
            },

            formatNumber(value) {
                if (value === null || value === undefined || value === '') {
                    return '';
                }

                return Number(value).toLocaleString('tr-TR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            },

            setInputState(input, state) {
                input.classList.remove(
                    'border-slate-200',
                    'border-emerald-400',
                    'border-rose-400',
                    'bg-emerald-50',
                    'bg-rose-50',
                    'opacity-70'
                );

                const dot = input.parentElement.querySelector('[data-save-dot]');

                if (dot) {
                    dot.classList.add('hidden');
                }

                if (state === 'saving') {
                    input.classList.add('opacity-70', 'border-indigo-300');
                }

                if (state === 'success') {
                    input.classList.add('border-emerald-400', 'bg-emerald-50');
                    if (dot) {
                        dot.classList.remove('hidden');
                    }

                    setTimeout(() => {
                        input.classList.remove('border-emerald-400', 'bg-emerald-50');
                        input.classList.add('border-slate-200');
                        if (dot) {
                            dot.classList.add('hidden');
                        }
                    }, 1200);
                }

                if (state === 'error') {
                    input.classList.add('border-rose-400', 'bg-rose-50');

                    setTimeout(() => {
                        input.classList.remove('border-rose-400', 'bg-rose-50');
                        input.classList.add('border-slate-200');
                    }, 1600);
                }

                if (!state) {
                    input.classList.add('border-slate-200');
                }
            },

            async saveCell(input) {
                const routeId = input.dataset.routeId;
                const tripDate = input.dataset.date;
                const routeName = input.dataset.routeName || '';
                const defaultVehicle = input.dataset.defaultVehicle || '';
                const rawValue = (input.value || '').trim();

                let vehicleId = null;
                let tripStatus = 'Yapıldı';
                let notes = null;

                if (rawValue !== '') {
                    const answer = window.confirm(
                        `${routeName} güzergahı için tanımlı araç/plaka: ${defaultVehicle || 'Tanımlı araç yok'}.\n\nTanımlı araç mı gitti?\n\nTamam = Evet\nİptal = Farklı araç / sonra seçilecek`
                    );

                    if (!answer) {
                        tripStatus = 'Farklı Araç';
                        notes = 'Farklı araç ile işlendi. Detay plaka seçimi sonraki aşamada eklenecek.';
                    }
                }

                const tripPrice = this.normalizeNumber(rawValue);

                if (rawValue !== '' && tripPrice === null) {
                    this.setInputState(input, 'error');
                    alert('Lütfen geçerli bir tutar girin.');
                    return;
                }

                this.setInputState(input, 'saving');

                try {
                    const response = await fetch(@json(route('trips.upsert-cell')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({
                            service_route_id: routeId,
                            trip_date: tripDate,
                            trip_price: tripPrice,
                            vehicle_id: vehicleId,
                            trip_status: tripStatus,
                            notes: notes,
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw data;
                    }

                    if (data.deleted) {
                        input.value = '';
                        input.dataset.original = '';

                        const infoBox = input.closest('td').querySelector('.min-h-\\[34px\\]');
                        if (infoBox) {
                            infoBox.innerHTML = '<div>Plaka: <span class="text-slate-400">Henüz işlenmedi</span></div>';
                        }
                    } else if (data.trip) {
                        input.value = data.trip.display_trip_price ?? '';
                        input.dataset.original = data.trip.display_trip_price ?? '';

                        const infoBox = input.closest('td').querySelector('.min-h-\\[34px\\]');
                        if (infoBox) {
                            infoBox.innerHTML = `
                                <div>Plaka: <span class="text-slate-700">${data.trip.vehicle_plate ?? 'Henüz işlenmedi'}</span></div>
                                <div class="mt-0.5">Durum: <span class="text-slate-700">${data.trip.trip_status ?? '-'}</span></div>
                            `;
                        }
                    }

                    this.setInputState(input, 'success');
                } catch (error) {
                    console.error(error);
                    this.setInputState(input, 'error');
                    alert('Kayıt sırasında bir hata oluştu.');
                }
            }
        }
    }

    document.addEventListener('alpine:init', () => {});
    document.addEventListener('DOMContentLoaded', () => {
        if (window.Alpine) {
            return;
        }
    });
</script>
@endpush