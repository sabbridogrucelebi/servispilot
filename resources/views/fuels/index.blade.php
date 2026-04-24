@extends('layouts.app')

@section('title', 'Yakıt Kayıtları')
@section('subtitle', 'Yakıt operasyon ve istasyon yönetimi')

@section('content')
@php
    use Carbon\Carbon;

    $fuels = collect($fuels ?? []);
    $stationSummaries = collect($stationSummaries ?? []);
    $vehiclesForFilter = $fuels->pluck('vehicle')->filter()->unique('id')->sortBy('plate')->values();

    $fuelTypes = [
        'Dizel' => 'bg-emerald-100 text-emerald-700',
        'Benzin' => 'bg-rose-100 text-rose-700',
        'LPG' => 'bg-violet-100 text-violet-700',
        'Eurodiesel' => 'bg-sky-100 text-sky-700',
        'AdBlue' => 'bg-cyan-100 text-cyan-700',
    ];

    $calculatedRows = collect();

    foreach ($fuels->groupBy('vehicle_id') as $vehicleId => $vehicleRows) {
        $sortedRows = $vehicleRows
            ->sortBy(function ($row) {
                return sprintf(
                    '%s-%010d-%010d',
                    optional($row->date)->format('Ymd') ?? '00000000',
                    (int) ($row->km ?? 0),
                    (int) $row->id
                );
            })
            ->values();

        $previousRow = null;

        foreach ($sortedRows as $row) {
            $row->km_difference = null;
            $row->km_per_liter = null;

            if (
                $previousRow &&
                !is_null($row->km) &&
                !is_null($previousRow->km) &&
                (float) $row->km > (float) $previousRow->km
            ) {
                $kmDifference = (float) $row->km - (float) $previousRow->km;
                $row->km_difference = $kmDifference;

                if ((float) $row->liters > 0) {
                    $row->km_per_liter = $kmDifference / (float) $row->liters;
                }
            }

            $calculatedRows->push($row);
            $previousRow = $row;
        }
    }

    $stationPaymentsMap = $stationSummaries
        ->mapWithKeys(function ($station) {
            return [
                mb_strtolower(trim($station->name ?? '')) => (float) ($station->total_paid ?? 0)
            ];
        });

    $paymentStatusMap = [];

    foreach ($calculatedRows
                ->filter(fn ($row) => filled($row->station?->name ?? $row->station_name))
                ->groupBy(function ($row) {
                    return mb_strtolower(trim($row->station?->name ?? $row->station_name ?? ''));
                }) as $stationKey => $stationRows) {

        $remainingPayment = (float) ($stationPaymentsMap[$stationKey] ?? 0);

        $sortedStationRows = $stationRows
            ->sortBy(function ($row) {
                return sprintf(
                    '%s-%010d-%010d',
                    optional($row->date)->format('Ymd') ?? '00000000',
                    (int) $row->id,
                    (int) ($row->km ?? 0)
                );
            })
            ->values();

        foreach ($sortedStationRows as $row) {
            $rowTotal = (float) ($row->total_cost ?? 0);

            if ($remainingPayment >= $rowTotal && $rowTotal > 0) {
                $paymentStatusMap[$row->id] = true;
                $remainingPayment -= $rowTotal;
            } else {
                $paymentStatusMap[$row->id] = false;
            }
        }
    }

    foreach ($calculatedRows as $row) {
        $row->is_paid = $paymentStatusMap[$row->id] ?? false;
    }

    $displayRows = $calculatedRows
        ->sortByDesc(function ($row) {
            return sprintf(
                '%s-%010d-%010d',
                optional($row->date)->format('Ymd') ?? '00000000',
                (int) ($row->km ?? 0),
                (int) $row->id
            );
        })
        ->values();

    $totalLiters = (float) $displayRows->sum(fn ($row) => (float) ($row->liters ?? 0));
    $totalAmount = (float) $displayRows->sum(fn ($row) => (float) ($row->total_cost ?? 0));
    $totalReceiptCount = (int) $displayRows->count();

    $stationCards = $stationSummaries->values()->map(function ($station) {
        $debt = (float) ($station->current_debt ?? 0);

        return [
            'name' => $station->name ?? '-',
            'debt' => $debt,
            'from' => $debt > 0 ? 'from-rose-500' : 'from-emerald-500',
            'to'   => $debt > 0 ? 'to-pink-500' : 'to-teal-500',
            'status_text' => $debt > 0 ? 'Ödeme Bekliyor' : 'Ödendi',
        ];
    });

    $defaultStationCard = [
        'name' => 'Cari İstasyon Yok',
        'debt' => 0,
        'from' => 'from-emerald-500',
        'to' => 'to-teal-500',
        'status_text' => 'Ödendi',
    ];

    $filterStartDate = request('start_date');
    $filterEndDate = request('end_date');
    $filterVehicleId = request('vehicle_id');
    $filterStation = request('station');
    $filterFuelType = request('fuel_type');
    $filterSearch = request('search');

    $filteredRows = $displayRows->filter(function ($row) use (
        $filterStartDate,
        $filterEndDate,
        $filterVehicleId,
        $filterStation,
        $filterFuelType,
        $filterSearch
    ) {
        $rowDate = optional($row->date)->format('Y-m-d');

        if ($filterStartDate && (!$rowDate || $rowDate < $filterStartDate)) {
            return false;
        }

        if ($filterEndDate && (!$rowDate || $rowDate > $filterEndDate)) {
            return false;
        }

        if ($filterVehicleId && (string) $row->vehicle_id !== (string) $filterVehicleId) {
            return false;
        }

        $rowStation = $row->station?->name ?? $row->station_name ?? '';
        if ($filterStation && mb_strtolower($rowStation) !== mb_strtolower($filterStation)) {
            return false;
        }

        $rowFuelType = $row->fuel_type ?? 'Dizel';
        if ($filterFuelType && $rowFuelType !== $filterFuelType) {
            return false;
        }

        if ($filterSearch) {
            $haystack = mb_strtolower(implode(' ', [
                $row->vehicle?->plate ?? '',
                $row->vehicle?->brand ?? '',
                $row->vehicle?->model ?? '',
                $rowStation,
                $row->notes ?? '',
            ]));

            if (!str_contains($haystack, mb_strtolower($filterSearch))) {
                return false;
            }
        }

        return true;
    })->values();

    $stationOptions = $displayRows
        ->map(fn ($row) => $row->station?->name ?? $row->station_name)
        ->filter()
        ->unique()
        ->sort()
        ->values();

    $fuelTypeOptions = $displayRows
        ->map(fn ($row) => $row->fuel_type ?? 'Dizel')
        ->filter()
        ->unique()
        ->sort()
        ->values();

    $perPage = 10;
    $currentPage = max((int) request('page', 1), 1);
    $totalFiltered = $filteredRows->count();
    $lastPage = max((int) ceil($totalFiltered / $perPage), 1);
    $currentPage = min($currentPage, $lastPage);

    $paginatedRows = $filteredRows
        ->slice(($currentPage - 1) * $perPage, $perPage)
        ->values();

    $queryWithoutPage = request()->except('page');
@endphp

<div
    class="space-y-6"
    x-data='{
        stationCards: @json($stationCards->count() ? $stationCards : collect([$defaultStationCard])),
        currentStationIndex: 0,
        showImportModal: false,
        init() {
            if (this.stationCards.length > 1) {
                setInterval(() => {
                    this.currentStationIndex = (this.currentStationIndex + 1) % this.stationCards.length;
                }, 5000);
            }
        },
        currentStationCard() {
            return this.stationCards[this.currentStationIndex] ?? {
                name: "Cari İstasyon Yok",
                debt: 0,
                from: "from-emerald-500",
                to: "to-teal-500",
                status_text: "Ödendi"
            };
        },
        stationCardClass() {
            const card = this.currentStationCard();
            return `${card.from} ${card.to}`;
        }
    }'
>
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm">
            <div class="mb-2 font-semibold">Lütfen aşağıdaki hataları düzeltin:</div>
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-[32px] border border-slate-200/70 bg-white/95 shadow-[0_20px_60px_rgba(15,23,42,0.08)] overflow-hidden">
        <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 via-white to-blue-50/40 px-6 py-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-[20px] bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-200/70">
                        ⛽
                    </div>

                    <div>
                        <h2 class="text-[26px] font-bold tracking-tight text-slate-900">Yakıt Yönetimi</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Yakıt kayıtları, km tüketim analizi, istasyon takibi ve borç raporlaması
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ Route::has('fuel-stations.index') ? route('fuel-stations.index') : '#' }}"
                       class="inline-flex items-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 {{ Route::has('fuel-stations.index') ? '' : 'pointer-events-none opacity-60' }}">
                        Petrol İstasyonları
                    </a>

                    <a href="{{ route('activity-logs.index', ['module' => 'fuel']) }}"
                       class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        İşlem Kayıtları
                    </a>

                    <button type="button"
                            @click="showImportModal = true"
                            class="inline-flex items-center rounded-2xl border border-violet-200 bg-violet-50 px-4 py-3 text-sm font-semibold text-violet-700 transition hover:bg-violet-100">
                        Excel Yükle
                    </button>

                    <a href="{{ route('reports.fuels.csv', request()->query()) }}"
                       class="inline-flex items-center rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                        Excel İndir
                    </a>

                    @if(auth()->user()->hasPermission('fuels.create'))
                    <a href="{{ route('fuels.create') }}"
                       class="inline-flex items-center rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-200/60 transition hover:scale-[1.01]">
                        + Yeni Yakıt Kaydı Ekle
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[26px] bg-gradient-to-br from-sky-500 to-blue-600 p-5 text-white shadow-xl">
                    <div class="text-sm font-medium text-white/80">Toplam Yakıt Tutarı</div>
                    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($totalAmount, 2, ',', '.') }} ₺</div>
                    <div class="mt-2 text-xs text-white/75">Kayıtlı toplam yakıt maliyeti</div>
                </div>

                <div class="rounded-[26px] bg-gradient-to-br from-emerald-500 to-teal-500 p-5 text-white shadow-xl">
                    <div class="text-sm font-medium text-white/80">Toplam Litre</div>
                    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($totalLiters, 2, ',', '.') }}</div>
                    <div class="mt-2 text-xs text-white/75">Sisteme işlenen toplam litre</div>
                </div>

                <div class="rounded-[26px] bg-gradient-to-br from-indigo-500 to-blue-600 p-5 text-white shadow-xl">
                    <div class="text-sm font-medium text-white/80">Yakıt Fiş Adedi</div>
                    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($totalReceiptCount, 0, ',', '.') }}</div>
                    <div class="mt-2 text-xs text-white/75">Toplam kayıtlı yakıt fişi sayısı</div>
                </div>

                <div class="rounded-[26px] p-5 text-white shadow-xl transition-all duration-500 bg-gradient-to-br" :class="stationCardClass()">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-white/80">İstasyon Cari Borcu</div>
                            <div class="mt-1 text-xs text-white/75" x-text="currentStationCard().name"></div>
                        </div>

                        <div class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-semibold text-white/90">
                            5 sn dönüşüm
                        </div>
                    </div>

                    <div class="mt-4 text-3xl font-extrabold tracking-tight">
                        <span x-text="new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(currentStationCard().debt)"></span> ₺
                    </div>

                    <div class="mt-3 inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white/95" x-text="currentStationCard().status_text"></div>

                    <div class="mt-2 text-xs text-white/75">
                        Borç varsa kırmızı, ödeme tamamlandıysa yeşil görünür
                    </div>
                </div>
            </div>

            <div class="rounded-[28px] border border-slate-200/70 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 via-white to-violet-50/30 px-6 py-5">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Yakıt Kayıt Listesi</h3>
                            <p class="mt-1 text-sm text-slate-500">Araç bazlı km, litre, tüketim ve maliyet görünümü</p>
                        </div>

                        <form method="GET" action="{{ route('fuels.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6 w-full xl:w-auto">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">Başlangıç</label>
                                <input type="date"
                                       name="start_date"
                                       value="{{ $filterStartDate }}"
                                       class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">Bitiş</label>
                                <input type="date"
                                       name="end_date"
                                       value="{{ $filterEndDate }}"
                                       class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">Araç</label>
                                <select name="vehicle_id"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100">
                                    <option value="">Tümü</option>
                                    @foreach($vehiclesForFilter as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ (string) $filterVehicleId === (string) $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->plate }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">İstasyon</label>
                                <select name="station"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100">
                                    <option value="">Tümü</option>
                                    @foreach($stationOptions as $stationOption)
                                        <option value="{{ $stationOption }}" {{ $filterStation === $stationOption ? 'selected' : '' }}>
                                            {{ $stationOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">Yakıt Türü</label>
                                <select name="fuel_type"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100">
                                    <option value="">Tümü</option>
                                    @foreach($fuelTypeOptions as $fuelTypeOption)
                                        <option value="{{ $fuelTypeOption }}" {{ $filterFuelType === $fuelTypeOption ? 'selected' : '' }}>
                                            {{ $fuelTypeOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">Arama</label>
                                <input type="text"
                                       name="search"
                                       value="{{ $filterSearch }}"
                                       placeholder="Plaka / not / istasyon"
                                       class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100">
                            </div>

                            <div class="xl:col-span-6 flex flex-wrap items-center justify-end gap-3">
                                <a href="{{ route('fuels.index') }}"
                                   class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Temizle
                                </a>

                                <button type="submit"
                                        class="rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200/60 transition hover:scale-[1.01]">
                                    Filtrele
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1450px] text-sm">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Tarih</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Araç</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">İstasyon</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Yakıt Türü</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">KM</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">KM Farkı</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Litre</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Birim Fiyat</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Toplam</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">KM / Litre</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Not</th>
                                <th class="px-4 py-4 text-right text-xs font-bold uppercase tracking-[0.12em] text-slate-500">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($paginatedRows as $fuel)
                                @php
                                    $fuelType = $fuel->fuel_type ?? 'Dizel';
                                    $fuelTypeClass = $fuelTypes[$fuelType] ?? 'bg-slate-100 text-slate-700';
                                @endphp

                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-4 py-4 text-slate-700">
                                        {{ Carbon::parse($fuel->date)->format('d.m.Y') }}
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="font-semibold text-slate-800">{{ $fuel->vehicle?->plate ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">{{ $fuel->vehicle?->brand ?? '' }} {{ $fuel->vehicle?->model ?? '' }}</div>
                                    </td>

                                    <td class="px-4 py-4 text-slate-600">
                                        {{ $fuel->station?->name ?? $fuel->station_name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $fuelTypeClass }}">
                                            {{ $fuelType }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 text-slate-700">
                                        {{ !is_null($fuel->km) ? number_format((float) $fuel->km, 0, ',', '.') : '-' }}
                                    </td>

                                    <td class="px-4 py-4 text-slate-700">
                                        {{ !is_null($fuel->km_difference) ? number_format((float) $fuel->km_difference, 0, ',', '.') . ' KM' : '-' }}
                                    </td>

                                    <td class="px-4 py-4 text-slate-700">
                                        {{ number_format((float) ($fuel->liters ?? 0), 2, ',', '.') }}
                                    </td>

                                    <td class="px-4 py-4 text-slate-700">
                                        {{ number_format((float) ($fuel->price_per_liter ?? 0), 2, ',', '.') }} ₺
                                    </td>

                                    <td class="px-4 py-4 font-semibold {{ $fuel->is_paid ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ number_format((float) ($fuel->total_cost ?? 0), 2, ',', '.') }} ₺
                                    </td>

                                    <td class="px-4 py-4 text-slate-700">
                                        {{ !is_null($fuel->km_per_liter) ? number_format((float) $fuel->km_per_liter, 2, ',', '.') : '-' }}
                                    </td>

                                    <td class="px-4 py-4 text-slate-500 max-w-[220px] truncate">
                                        {{ $fuel->notes ?: '-' }}
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            @if(auth()->user()->hasPermission('fuels.edit'))
                                            <a href="{{ route('fuels.edit', $fuel) }}"
                                               class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">
                                                Düzenle
                                            </a>
                                            @endif

                                            @if(auth()->user()->hasPermission('fuels.delete'))
                                            <form action="{{ route('fuels.destroy', $fuel) }}" method="POST" class="inline" onsubmit="return confirm('Bu yakıt kaydını silmek istediğine emin misin?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                                    Sil
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="px-4 py-10 text-center text-slate-500">
                                        Filtreye uygun yakıt kaydı bulunamadı.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($lastPage > 1)
                    <div class="border-t border-slate-100 bg-white px-6 py-4">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div class="text-sm text-slate-500">
                                Toplam <span class="font-semibold text-slate-700">{{ number_format($totalFiltered, 0, ',', '.') }}</span> kayıt,
                                sayfa <span class="font-semibold text-slate-700">{{ $currentPage }}</span> / <span class="font-semibold text-slate-700">{{ $lastPage }}</span>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @php
                                    $prevPage = max($currentPage - 1, 1);
                                    $nextPage = min($currentPage + 1, $lastPage);
                                @endphp

                                <a href="{{ route('fuels.index', array_merge($queryWithoutPage, ['page' => 1])) }}"
                                   class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 {{ $currentPage === 1 ? 'pointer-events-none opacity-40' : '' }}">
                                    İlk
                                </a>

                                <a href="{{ route('fuels.index', array_merge($queryWithoutPage, ['page' => $prevPage])) }}"
                                   class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 {{ $currentPage === 1 ? 'pointer-events-none opacity-40' : '' }}">
                                    Önceki
                                </a>

                                @for($page = max(1, $currentPage - 2); $page <= min($lastPage, $currentPage + 2); $page++)
                                    <a href="{{ route('fuels.index', array_merge($queryWithoutPage, ['page' => $page])) }}"
                                       class="rounded-xl px-3 py-2 text-sm font-semibold transition {{ $page === $currentPage ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-md' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                                        {{ $page }}
                                    </a>
                                @endfor

                                <a href="{{ route('fuels.index', array_merge($queryWithoutPage, ['page' => $nextPage])) }}"
                                   class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 {{ $currentPage === $lastPage ? 'pointer-events-none opacity-40' : '' }}">
                                    Sonraki
                                </a>

                                <a href="{{ route('fuels.index', array_merge($queryWithoutPage, ['page' => $lastPage])) }}"
                                   class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 {{ $currentPage === $lastPage ? 'pointer-events-none opacity-40' : '' }}">
                                    Son
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <template x-teleport="body">
        <div x-cloak x-show="showImportModal" x-transition.opacity class="fixed inset-0 z-[9999]" style="display:none;">
            <div class="absolute inset-0 bg-slate-900/55 backdrop-blur-[3px]" @click="showImportModal = false"></div>

            <div class="absolute inset-0 flex items-center justify-center p-4 sm:p-6">
                <div
                    x-show="showImportModal"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                    class="w-full max-w-2xl overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-[0_40px_120px_rgba(15,23,42,0.28)]"
                    @click.stop
                >
                    <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 via-white to-violet-50/40 px-6 py-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Excel ile Yakıt Yükle</h3>
                                <p class="mt-1 text-sm text-slate-500">Toplu yakıt kayıtlarını Excel dosyasından içe aktar</p>
                            </div>

                            <button type="button"
                                    @click="showImportModal = false"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-700">
                                ✕
                            </button>
                        </div>
                    </div>

                    <form action="{{ route('fuels.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                        @csrf

                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                            <p class="text-sm font-medium text-slate-700">Excel dosyanı seç</p>
                            <p class="mt-1 text-xs text-slate-500">xlsx, xls veya csv yükleyebilirsin</p>

                            <input type="file"
                                   name="file"
                                   accept=".xlsx,.xls,.csv"
                                   required
                                   class="mt-4 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                        </div>

                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700">
                            Şablon sütun sırası: <strong>Plaka, Tarih, KM, Litre, Birim Fiyat, Yakıt Türü, İstasyon, Not</strong>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="button"
                                    @click="showImportModal = false"
                                    class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Vazgeç
                            </button>

                            <button type="submit"
                                    class="rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-violet-200/60 transition hover:scale-[1.01]">
                                Yüklemeyi Başlat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endsection