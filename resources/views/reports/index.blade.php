@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Finansal Raporlar & Analiz</h1>
            <p class="text-slate-500 mt-1">Platformun gelir, gider ve genel durum analizini detaylı inceleyin.</p>
        </div>
        
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-3 bg-white p-2 rounded-xl shadow-sm border border-slate-200">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1 px-1">Başlangıç</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1 px-1">Bitiş</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 text-sm font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-2">
                <i class="ti ti-filter"></i> Filtrele
            </button>
        </form>
    </div>

    <!-- MAIN STATS GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Income -->
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-10">
                <i class="ti ti-wallet text-9xl"></i>
            </div>
            <div class="relative z-10">
                <h3 class="text-blue-100 font-semibold text-sm uppercase tracking-wider mb-2">Toplam Sefer Geliri</h3>
                <div class="text-3xl font-black mb-1">{{ number_format($tripIncome, 2, ',', '.') }} ₺</div>
                <div class="text-blue-200 text-xs">{{ $tripCount }} adet tamamlanmış sefer</div>
            </div>
        </div>

        <!-- Expense -->
        @php $totalExp = $fuelCost + $salaryCost + $maintenanceCost + $penaltyCost; @endphp
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-5">
                <i class="ti ti-receipt-2 text-9xl"></i>
            </div>
            <div class="relative z-10">
                <h3 class="text-slate-500 font-semibold text-sm uppercase tracking-wider mb-2">Toplam Gider</h3>
                <div class="text-3xl font-black text-slate-800 mb-1">{{ number_format($totalExp, 2, ',', '.') }} ₺</div>
                <div class="text-slate-400 text-xs">Yakıt, Maaş, Bakım ve Cezalar</div>
            </div>
        </div>

        <!-- Net Profit -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-5">
                <i class="ti ti-trending-up text-9xl"></i>
            </div>
            <div class="relative z-10">
                <h3 class="text-slate-500 font-semibold text-sm uppercase tracking-wider mb-2">Net Kar / Zarar</h3>
                <div class="text-3xl font-black mb-1 {{ $netProfit >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                    {{ number_format($netProfit, 2, ',', '.') }} ₺
                </div>
                <div class="text-slate-400 text-xs">Seçili tarih aralığında</div>
            </div>
        </div>

        <!-- Documents -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-5">
                <i class="ti ti-files text-9xl"></i>
            </div>
            <div class="relative z-10">
                <h3 class="text-slate-500 font-semibold text-sm uppercase tracking-wider mb-2">İşlem Gören Belge</h3>
                <div class="text-3xl font-black text-slate-800 mb-1">{{ $documentCount }}</div>
                <div class="text-slate-400 text-xs">Seçili aralıkta kaydedilen/biten</div>
            </div>
        </div>
    </div>

    <!-- CHARTS SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Trend Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i class="ti ti-chart-bar text-blue-500"></i> Son 6 Aylık Finansal Trend
            </h2>
            <div class="h-80 w-full relative">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- Breakdown Chart -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i class="ti ti-chart-pie text-emerald-500"></i> Gider Dağılımı (Seçili Dönem)
            </h2>
            <div class="h-64 w-full relative">
                <canvas id="expenseChart"></canvas>
            </div>
            
            <div class="mt-6 space-y-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-orange-400"></span> Yakıt</span>
                    <span class="font-semibold">{{ number_format($fuelCost, 2, ',', '.') }} ₺</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-500"></span> Maaşlar</span>
                    <span class="font-semibold">{{ number_format($salaryCost, 2, ',', '.') }} ₺</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-purple-500"></span> Bakım</span>
                    <span class="font-semibold">{{ number_format($maintenanceCost, 2, ',', '.') }} ₺</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-rose-500"></span> Cezalar</span>
                    <span class="font-semibold">{{ number_format($penaltyCost, 2, ',', '.') }} ₺</span>
                </div>
            </div>
        </div>
    </div>

    <!-- DATA TABLES -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Trips -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-slate-800">Son Sefer Hareketleri</h2>
                <a href="{{ route('reports.trips.csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    <i class="ti ti-download"></i> CSV İndir
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 rounded-lg">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">Tarih</th>
                            <th class="px-4 py-3">Hat</th>
                            <th class="px-4 py-3">Araç / Şoför</th>
                            <th class="px-4 py-3 rounded-r-lg text-right">Tutar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($trips->take(5) as $trip)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $trip->trip_date?->format('d.m.Y') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $trip->serviceRoute?->route_name }}</td>
                            <td class="px-4 py-3">
                                <div class="text-slate-900 font-medium">{{ $trip->vehicle?->plate ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $trip->driver?->full_name ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-600">+{{ number_format($trip->trip_price ?? 0, 2, ',', '.') }} ₺</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Kayıt bulunamadı</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Fuels -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-slate-800">Son Yakıt Alımları</h2>
                <a href="{{ route('reports.fuels.csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    <i class="ti ti-download"></i> CSV İndir
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 rounded-lg">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">Tarih</th>
                            <th class="px-4 py-3">Araç</th>
                            <th class="px-4 py-3">İstasyon</th>
                            <th class="px-4 py-3 rounded-r-lg text-right">Maliyet</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($fuels->take(5) as $fuel)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $fuel->date?->format('d.m.Y') }}</td>
                            <td class="px-4 py-3 font-semibold text-blue-600">{{ $fuel->vehicle?->plate }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $fuel->station?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-bold text-rose-600">-{{ number_format($fuel->total_cost, 2, ',', '.') }} ₺</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Kayıt bulunamadı</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Trend Chart Data
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const trendMonths = {!! json_encode($trendMonths) !!};
    const trendIncomes = {!! json_encode($trendIncomes) !!};
    const trendExpenses = {!! json_encode($trendExpenses) !!};
    const trendProfits = {!! json_encode($trendProfits) !!};

    new Chart(trendCtx, {
        type: 'bar',
        data: {
            labels: trendMonths,
            datasets: [
                {
                    label: 'Gelir',
                    data: trendIncomes,
                    backgroundColor: 'rgba(59, 130, 246, 0.9)',
                    borderRadius: 4,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                },
                {
                    label: 'Gider',
                    data: trendExpenses,
                    backgroundColor: 'rgba(225, 29, 72, 0.9)',
                    borderRadius: 4,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                },
                {
                    label: 'Net Kar',
                    data: trendProfits,
                    type: 'line',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: 'rgba(16, 185, 129, 1)',
                    pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: { usePointStyle: true, boxWidth: 8 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) { label += ': '; }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    grid: { color: '#F1F5F9', drawBorder: false },
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('tr-TR', { notation: "compact", compactDisplay: "short" }).format(value) + ' ₺';
                        }
                    }
                },
                x: {
                    grid: { display: false, drawBorder: false }
                }
            }
        }
    });

    // Expense Chart Data
    const expCtx = document.getElementById('expenseChart').getContext('2d');
    const fCost = {{ $fuelCost }};
    const sCost = {{ $salaryCost }};
    const mCost = {{ $maintenanceCost }};
    const pCost = {{ $penaltyCost }};

    if (fCost + sCost + mCost + pCost > 0) {
        new Chart(expCtx, {
            type: 'doughnut',
            data: {
                labels: ['Yakıt', 'Maaş', 'Bakım', 'Cezalar'],
                datasets: [{
                    data: [fCost, sCost, mCost, pCost],
                    backgroundColor: [
                        '#fb923c', // orange-400
                        '#3b82f6', // blue-500
                        '#a855f7', // purple-500
                        '#f43f5e'  // rose-500
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(context.raw);
                            }
                        }
                    }
                }
            }
        });
    } else {
        document.getElementById('expenseChart').parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-slate-400 text-sm">Gider verisi bulunmuyor</div>';
    }
});
</script>
@endsection
