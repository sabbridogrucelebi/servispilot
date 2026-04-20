@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    Carbon::setLocale('tr');

    $recentFuels = collect($recentFuels ?? []);
    $stationSummaries = collect($stationSummaries ?? []);
    $vehicle = $vehicle ?? null;

    $selectedMonth = request('fuel_month');
    $search = trim((string) request('fuel_search'));
    $filterStation = trim((string) request('fuel_station'));
    $filterFuelType = trim((string) request('fuel_type'));
    $filterStartDate = request('fuel_start_date');
    $filterEndDate = request('fuel_end_date');

    /*
    |--------------------------------------------------------------------------
    | Tarih filtresi varsa ay filtresini zorlamıyoruz
    |--------------------------------------------------------------------------
    */
    $hasDateRangeFilter = filled($filterStartDate) || filled($filterEndDate);

    /*
    |--------------------------------------------------------------------------
    | AYLARI ARTIK SADECE AY NUMARASINA GÖRE TOPLUYORUZ
    | Örn: 2026 Nisan + 2027 Nisan = tek NİSAN kutusu
    |--------------------------------------------------------------------------
    */
    $months = $recentFuels
        ->filter(fn ($row) => filled($row->date))
        ->groupBy(fn ($row) => optional($row->date)->format('m'))
        ->map(function ($rows, $monthKey) {
            $date = Carbon::create()->month((int) $monthKey)->locale('tr');

            $years = $rows
                ->map(fn ($row) => optional($row->date)->format('Y'))
                ->filter()
                ->unique()
                ->sortDesc()
                ->values();

            return (object) [
                'key' => $monthKey, // artık sadece 01-12
                'label' => Str::upper($date->translatedFormat('F')),
                'short_label' => Str::upper($date->translatedFormat('F')),
                'total' => (float) $rows->sum('total_cost'),
                'count' => $rows->count(),
                'years' => $years,
                'year_label' => $years->implode(', '),
            ];
        })
        ->sortBy(fn ($item) => (int) $item->key)
        ->values();

    if (!$selectedMonth && !$hasDateRangeFilter && $months->count()) {
        $currentMonthKey = now()->format('m');
        $selectedMonth = $months->firstWhere('key', $currentMonthKey)->key
            ?? $months->last()->key;
    }

    $baseRows = $recentFuels->filter(function ($row) use (
        $selectedMonth,
        $search,
        $filterStation,
        $filterFuelType,
        $filterStartDate,
        $filterEndDate,
        $hasDateRangeFilter
    ) {
        $rowDate = optional($row->date)->format('Y-m-d');
        $rowMonth = optional($row->date)->format('m');
        $rowStation = $row->station?->name ?? $row->station_name ?? '';
        $rowFuelType = $row->fuel_type ?? 'Dizel';

        if (!$hasDateRangeFilter && $selectedMonth && $rowMonth !== $selectedMonth) {
            return false;
        }

        if ($filterStartDate && (!$rowDate || $rowDate < $filterStartDate)) {
            return false;
        }

        if ($filterEndDate && (!$rowDate || $rowDate > $filterEndDate)) {
            return false;
        }

        if ($filterStation && mb_strtolower($rowStation) !== mb_strtolower($filterStation)) {
            return false;
        }

        if ($filterFuelType && $rowFuelType !== $filterFuelType) {
            return false;
        }

        if ($search) {
            $haystack = mb_strtolower(implode(' ', [
                $rowStation,
                $rowFuelType,
                $row->notes ?? '',
                $row->vehicle?->plate ?? '',
                $row->vehicle?->brand ?? '',
                $row->vehicle?->model ?? '',
                (string) ($row->km ?? ''),
            ]));

            if (!str_contains($haystack, mb_strtolower($search))) {
                return false;
            }
        }

        return true;
    })->values();

    $sortedAsc = $baseRows
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
    $processedRows = collect();

    foreach ($sortedAsc as $row) {
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

            if ((float) ($row->liters ?? 0) > 0) {
                $row->km_per_liter = $kmDifference / (float) $row->liters;
            }
        }

        $processedRows->push($row);
        $previousRow = $row;
    }

    $stationPaymentTotals = $stationSummaries
        ->mapWithKeys(function ($station) {
            return [
                mb_strtolower(trim($station->name ?? '')) => (float) ($station->total_paid ?? 0)
            ];
        });

    $paymentStatusMap = [];

    foreach ($processedRows
                ->filter(fn ($row) => filled($row->station?->name ?? $row->station_name))
                ->groupBy(function ($row) {
                    return mb_strtolower(trim($row->station?->name ?? $row->station_name ?? ''));
                }) as $stationKey => $stationRows) {

        $remainingPayment = (float) ($stationPaymentTotals[$stationKey] ?? 0);

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

            if ($rowTotal > 0 && $remainingPayment >= $rowTotal) {
                $paymentStatusMap[$row->id] = true;
                $remainingPayment -= $rowTotal;
            } else {
                $paymentStatusMap[$row->id] = false;
            }
        }
    }

    foreach ($processedRows as $row) {
        $row->is_paid = $paymentStatusMap[$row->id] ?? false;
    }

    $displayRows = $processedRows
        ->sortByDesc(function ($row) {
            return sprintf(
                '%s-%010d-%010d',
                optional($row->date)->format('Ymd') ?? '00000000',
                (int) ($row->km ?? 0),
                (int) $row->id
            );
        })
        ->values();

    $selectedMonthInfo = $months->firstWhere('key', $selectedMonth);
    $selectedMonthTotal = (float) $displayRows->sum(fn ($row) => (float) ($row->total_cost ?? 0));
    $allTimeFuelTotal = (float) $recentFuels->sum(fn ($row) => (float) ($row->total_cost ?? 0));
    $selectedMonthLiters = (float) $displayRows->sum(fn ($row) => (float) ($row->liters ?? 0));
    $selectedMonthCount = (int) $displayRows->count();
    $lastKm = optional($displayRows->first())->km;

    $monthKmRows = $displayRows
        ->filter(fn ($row) => !is_null($row->km))
        ->sortBy('km')
        ->values();

    $monthFirstKm = optional($monthKmRows->first())->km;
    $monthLastKm = optional($monthKmRows->last())->km;
    $selectedMonthKm = (!is_null($monthFirstKm) && !is_null($monthLastKm) && $monthLastKm >= $monthFirstKm)
        ? (float) $monthLastKm - (float) $monthFirstKm
        : 0;

    $stationOptions = $recentFuels
        ->map(fn ($row) => $row->station?->name ?? $row->station_name)
        ->filter()
        ->unique()
        ->sort()
        ->values();

    $fuelTypeOptions = $recentFuels
        ->map(fn ($row) => $row->fuel_type ?? 'Dizel')
        ->filter()
        ->unique()
        ->sort()
        ->values();

    $excelParams = array_filter([
        'vehicle_id' => $vehicle->id ?? request('vehicle_id'),
        'fuel_month' => !$hasDateRangeFilter ? $selectedMonth : null,
        'fuel_search' => $search,
        'fuel_station' => $filterStation,
        'fuel_type' => $filterFuelType,
        'fuel_start_date' => $filterStartDate,
        'fuel_end_date' => $filterEndDate,
    ], fn ($value) => $value !== null && $value !== '');

    $showRouteParamsBase = [
        'vehicle' => $vehicle->id ?? request()->route('vehicle')?->id ?? null,
        'tab' => 'fuels'
    ];
    $showRouteParamsBase = array_filter($showRouteParamsBase, fn ($value) => !is_null($value));

    $monthKmTitle = $selectedMonthInfo
        ? $selectedMonthInfo->short_label . ' AYI YAPILAN KM'
        : ($hasDateRangeFilter ? 'SEÇİLİ TARİH ARALIĞI KM' : 'SEÇİLİ AY YAPILAN KM');
@endphp

<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    <div class="xl:col-span-8 rounded-[30px] border border-slate-200/60 bg-white/90 backdrop-blur shadow-xl overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-5 space-y-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Araç Yakıtları</h3>
                    <p class="mt-1 text-sm text-slate-500">Bu araca ait yakıt kayıtları, ay bazlı özet ve detaylı filtreleme</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('reports.fuels.csv', $excelParams) }}"
                       class="inline-flex items-center rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                        Excel İndir
                    </a>

                    <a href="{{ route('fuels.create') }}"
                       class="inline-flex items-center rounded-2xl bg-gradient-to-r from-orange-500 to-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow">
                        Yakıt Ekle
                    </a>
                </div>
            </div>

            @if($months->count())
                <div class="flex flex-wrap gap-2">
                    @foreach($months as $monthItem)
                        <a href="{{ route('vehicles.show', array_merge($showRouteParamsBase, ['fuel_month' => $monthItem->key])) }}"
                           title="{{ $monthItem->year_label ? $monthItem->label . ' (' . $monthItem->year_label . ')' : $monthItem->label }}"
                           class="inline-flex items-center rounded-2xl px-4 py-2 text-sm font-semibold transition {{ !$hasDateRangeFilter && $selectedMonth === $monthItem->key ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                            {{ $monthItem->short_label }}
                        </a>
                    @endforeach
                </div>
            @endif

            <form method="GET" action="{{ route('vehicles.show', $showRouteParamsBase) }}" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
                <input type="hidden" name="tab" value="fuels">
                @if(!$hasDateRangeFilter && $selectedMonth)
                    <input type="hidden" name="fuel_month" value="{{ $selectedMonth }}">
                @endif

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">Başlangıç</label>
                    <input type="date"
                           name="fuel_start_date"
                           value="{{ $filterStartDate }}"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">Bitiş</label>
                    <input type="date"
                           name="fuel_end_date"
                           value="{{ $filterEndDate }}"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">İstasyon</label>
                    <select name="fuel_station"
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

                <div class="xl:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">Arama</label>
                    <input type="text"
                           name="fuel_search"
                           value="{{ $search }}"
                           placeholder="İstasyon / not / km / yakıt türü"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100">
                </div>

                <div class="md:col-span-2 xl:col-span-6 flex flex-wrap items-center justify-end gap-3">
                    <a href="{{ route('vehicles.show', $showRouteParamsBase) }}"
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

        <div class="overflow-x-auto">
            @if($displayRows->count())
                <table class="w-full min-w-[1100px]">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Tarih</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">İstasyon</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Yakıt Türü</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">KM</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">KM Farkı</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Litre</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Birim Fiyat</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">KM / Litre</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Toplam</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($displayRows as $fuelRow)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-sm font-medium text-slate-700">
                                    {{ optional($fuelRow->date)->format('d.m.Y') ?: '-' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $fuelRow->station?->name ?? $fuelRow->station_name ?? '-' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $fuelRow->fuel_type ?? 'Dizel' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ !is_null($fuelRow->km) ? number_format((float) $fuelRow->km, 0, ',', '.') : '-' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ !is_null($fuelRow->km_difference) ? number_format((float) $fuelRow->km_difference, 0, ',', '.') . ' KM' : '-' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ number_format((float) ($fuelRow->liters ?? 0), 2, ',', '.') }}
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ number_format((float) ($fuelRow->price_per_liter ?? 0), 2, ',', '.') }} ₺
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ !is_null($fuelRow->km_per_liter) ? number_format((float) $fuelRow->km_per_liter, 2, ',', '.') : '-' }}
                                </td>

                                <td class="px-6 py-4 text-right text-sm font-bold {{ $fuelRow->is_paid ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ number_format((float) ($fuelRow->total_cost ?? 0), 2, ',', '.') }} ₺
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-6">
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">
                        Bu filtreye uygun yakıt kaydı bulunmuyor.
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="xl:col-span-4 rounded-[30px] border border-slate-200/60 bg-white/90 backdrop-blur shadow-xl overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-lg font-bold text-slate-800">Yakıt Özeti</h3>
            <p class="mt-1 text-sm text-slate-500">Ay bazlı maliyet görünümü</p>
        </div>

        <div class="p-6 space-y-4">
            <div class="rounded-2xl bg-gradient-to-r from-orange-500 to-amber-500 p-5 text-white">
                <div class="text-sm text-white/80">
                    @if($hasDateRangeFilter)
                        {{ $filterStartDate ? Carbon::parse($filterStartDate)->format('d.m.Y') : '-' }} - {{ $filterEndDate ? Carbon::parse($filterEndDate)->format('d.m.Y') : '-' }} Yakıt Gideri
                    @else
                        {{ $selectedMonthInfo ? $selectedMonthInfo->label . ' AYI TÜM YILLAR YAKIT GİDERİ' : 'Seçili Ay Yakıt Gideri' }}
                    @endif
                </div>
                <div class="mt-2 text-2xl font-extrabold">{{ number_format($selectedMonthTotal, 2, ',', '.') }} ₺</div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                <div class="text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">Bugüne Kadarki Toplam</div>
                <div class="mt-2 text-lg font-bold text-slate-700">{{ number_format($allTimeFuelTotal, 2, ',', '.') }} ₺</div>
            </div>

            <div class="rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-4 text-white">
                <div class="text-xs font-semibold uppercase tracking-[0.10em] text-white/75">{{ $monthKmTitle }}</div>
                <div class="mt-2 text-2xl font-extrabold">{{ number_format($selectedMonthKm, 0, ',', '.') }} KM</div>
                <div class="mt-1 text-xs text-white/75">
                    İlk KM: {{ !is_null($monthFirstKm) ? number_format((float) $monthFirstKm, 0, ',', '.') : '-' }}
                    · Son KM: {{ !is_null($monthLastKm) ? number_format((float) $monthLastKm, 0, ',', '.') : '-' }}
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">Seçili Ay Litre</div>
                    <div class="mt-2 text-lg font-bold text-slate-800">{{ number_format($selectedMonthLiters, 2, ',', '.') }}</div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">Fiş Adedi</div>
                    <div class="mt-2 text-lg font-bold text-slate-800">{{ number_format($selectedMonthCount, 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                <div class="text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">Son KM</div>
                <div class="mt-2 text-lg font-bold text-slate-800">
                    {{ !is_null($lastKm) ? number_format((float) $lastKm, 0, ',', '.') . ' KM' : '-' }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                <div class="text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">Renk Açıklaması</div>
                <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 font-semibold text-emerald-700">Ödendi</span>
                    <span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 font-semibold text-rose-700">Bekliyor</span>
                </div>
            </div>

            <a href="{{ route('fuels.index', ['vehicle_id' => $vehicle->id ?? request('vehicle_id')]) }}"
               class="flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Tüm Yakıt Kayıtları
            </a>
        </div>
    </div>
</div>