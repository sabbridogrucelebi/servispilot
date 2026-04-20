@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Genel operasyon ve finans görünümü')

@section('content')

@php
    $chartLabels = ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu'];

    $incomeSeries = [
        12000,
        18000,
        22000,
        26000,
        31000,
        37000,
        43000,
        (float) $monthlyIncome,
    ];

    $operationSeries = [
        8,
        11,
        13,
        16,
        18,
        21,
        24,
        max((int) $todayTrips, 12),
    ];
@endphp

<div class="space-y-8">

    <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

        <div class="relative overflow-hidden rounded-[28px] p-6 text-white shadow-lg bg-gradient-to-br from-sky-500 via-blue-500 to-indigo-500">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="absolute right-10 bottom-0 w-20 h-20 bg-white/10 rounded-full"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-sm text-white/80">Toplam Araç</div>
                    <div class="text-3xl font-bold mt-3">{{ $vehicleCount }}</div>
                    <div class="text-xs text-white/70 mt-2">Aktif araç durumu</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-xl">
                    🚗
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] p-6 text-white shadow-lg bg-gradient-to-br from-cyan-400 via-teal-400 to-emerald-500">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="absolute right-10 bottom-0 w-20 h-20 bg-white/10 rounded-full"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-sm text-white/80">Aylık Gelir</div>
                    <div class="text-3xl font-bold mt-3">{{ number_format($monthlyIncome, 0, ',', '.') }} ₺</div>
                    <div class="text-xs text-white/70 mt-2">Bu ay toplam tahsilat</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-xl">
                    💰
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] p-6 text-white shadow-lg bg-gradient-to-br from-violet-500 via-purple-500 to-fuchsia-500">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="absolute right-10 bottom-0 w-20 h-20 bg-white/10 rounded-full"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-sm text-white/80">Toplam Şoför</div>
                    <div class="text-3xl font-bold mt-3">{{ $driverCount }}</div>
                    <div class="text-xs text-white/70 mt-2">Sistem kullanıcı havuzu</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-xl">
                    👨‍✈️
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] p-6 text-white shadow-lg bg-gradient-to-br from-amber-400 via-orange-400 to-rose-400">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="absolute right-10 bottom-0 w-20 h-20 bg-white/10 rounded-full"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-sm text-white/80">Bugünkü Sefer</div>
                    <div class="text-3xl font-bold mt-3">{{ $todayTrips }}</div>
                    <div class="text-xs text-white/70 mt-2">Bugün planlanan hareket</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-xl">
                    📍
                </div>
            </div>
        </div>

    </section>

    <section class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        <div class="xl:col-span-8 space-y-6">

            <div class="bg-white rounded-[30px] border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Gelir Varlığı</h3>
                        <p class="text-sm text-slate-500">Operasyon ve tahsilat eğilimi</p>
                    </div>
                    <div class="rounded-2xl bg-slate-100 px-4 py-2 text-xs text-slate-600">
                        Son 8 Ay
                    </div>
                </div>

                <div class="rounded-[24px] bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 p-5 h-[360px] relative overflow-hidden">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.10),transparent_30%)]"></div>
                    <div class="relative h-full">
                        <canvas id="dashboardRevenueChart" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div class="bg-white rounded-[30px] border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Yaklaşan Belgeler</h3>
                            <p class="text-sm text-slate-500">Takip edilmesi gereken evrak listesi</p>
                        </div>
                        <a href="{{ route('documents.index') }}" class="text-sm font-medium text-indigo-600">Tümünü Gör</a>
                    </div>

                    <div class="space-y-4 min-h-[260px]">
                        @forelse($upcomingDocuments->take(4) as $document)
                            @php
                                $ownerText = '-';

                                if ($document->documentable_type === 'App\Models\Fleet\Vehicle') {
                                    $ownerText = 'Araç - ' . ($document->documentable?->plate ?? '-');
                                } elseif ($document->documentable_type === 'App\Models\Fleet\Driver') {
                                    $ownerText = 'Şoför - ' . ($document->documentable?->full_name ?? '-');
                                }

                                $daysLeft = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($document->end_date)->startOfDay(), false);
                            @endphp

                            <div class="flex items-center justify-between rounded-[22px] border border-slate-100 bg-slate-50 px-4 py-4">
                                <div class="min-w-0">
                                    <div class="font-semibold text-slate-800 text-sm truncate">{{ $document->document_name }}</div>
                                    <div class="text-xs text-slate-500 mt-1 truncate">{{ $ownerText }}</div>
                                </div>
                                <div class="text-right ml-4">
                                    <div class="text-xs text-slate-500">{{ $document->end_date?->format('d.m.Y') }}</div>
                                    <div class="mt-1 inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $daysLeft <= 7 ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600' }}">
                                        {{ $daysLeft }} gün
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="h-[260px] rounded-[22px] border border-dashed border-slate-200 bg-slate-50 flex items-center justify-center text-center text-slate-500 px-6">
                                Yaklaşan belge kaydı yok.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-[30px] border border-slate-200 shadow-sm p-6">
                    <div class="mb-5">
                        <h3 class="text-lg font-bold text-slate-800">Durum Özeti</h3>
                        <p class="text-sm text-slate-500">Anlık sistem uyarıları</p>
                    </div>

                    <div class="space-y-4 min-h-[260px]">
                        <div class="rounded-[22px] bg-gradient-to-r from-red-50 to-rose-50 border border-red-100 p-4 flex items-center justify-between">
                            <span class="text-sm text-slate-600">Süresi Geçmiş Belgeler</span>
                            <span class="text-lg font-bold text-red-600">{{ $expiredDocumentsCount }}</span>
                        </div>

                        <div class="rounded-[22px] bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-100 p-4 flex items-center justify-between">
                            <span class="text-sm text-slate-600">7 Gün İçinde Bitecek</span>
                            <span class="text-lg font-bold text-amber-600">{{ $documentsExpiringIn7DaysCount }}</span>
                        </div>

                        <div class="rounded-[22px] bg-gradient-to-r from-sky-50 to-cyan-50 border border-sky-100 p-4 flex items-center justify-between">
                            <span class="text-sm text-slate-600">30 Gün İçinde Bitecek</span>
                            <span class="text-lg font-bold text-sky-600">{{ $documentsExpiringIn30DaysCount }}</span>
                        </div>

                        <div class="rounded-[22px] bg-gradient-to-r from-violet-50 to-purple-50 border border-violet-100 p-4 flex items-center justify-between">
                            <span class="text-sm text-slate-600">Toplam Şoför</span>
                            <span class="text-lg font-bold text-violet-600">{{ $driverCount }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="xl:col-span-4 space-y-6">

            <div class="bg-white rounded-[30px] border border-slate-200 shadow-sm p-6">
                <div class="mb-5">
                    <h3 class="text-lg font-bold text-slate-800">Finans Özeti</h3>
                    <p class="text-sm text-slate-500">Gelir ve gider blokları</p>
                </div>

                <div class="space-y-4">
                    <div class="rounded-[24px] p-5 text-white shadow bg-gradient-to-r from-sky-500 to-blue-500">
                        <div class="text-sm text-white/80">Toplam Gelir</div>
                        <div class="text-2xl font-bold mt-2">{{ number_format($monthlyIncome, 2, ',', '.') }} ₺</div>
                    </div>

                    <div class="rounded-[24px] p-5 text-white shadow bg-gradient-to-r from-rose-500 to-orange-400">
                        <div class="text-sm text-white/80">Yakıt Gideri</div>
                        <div class="text-2xl font-bold mt-2">{{ number_format($totalFuel, 2, ',', '.') }} ₺</div>
                    </div>

                    <div class="rounded-[24px] p-5 text-white shadow bg-gradient-to-r from-amber-400 to-yellow-500">
                        <div class="text-sm text-white/80">Maaş Gideri</div>
                        <div class="text-2xl font-bold mt-2">{{ number_format($totalSalary, 2, ',', '.') }} ₺</div>
                    </div>

                    <div class="rounded-[24px] p-5 text-white shadow bg-gradient-to-r from-emerald-500 to-teal-400">
                        <div class="text-sm text-white/80">Net Kar</div>
                        <div class="text-2xl font-bold mt-2">{{ number_format($netProfit, 2, ',', '.') }} ₺</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[30px] border border-slate-200 shadow-sm p-6">
                <div class="mb-5">
                    <h3 class="text-lg font-bold text-slate-800">Genel Özet</h3>
                    <p class="text-sm text-slate-500">Hızlı performans görünümü</p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="text-sm text-slate-500">Araç Sayısı</span>
                        <span class="font-semibold text-slate-800">{{ $vehicleCount }}</span>
                    </div>

                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="text-sm text-slate-500">Şoför Sayısı</span>
                        <span class="font-semibold text-slate-800">{{ $driverCount }}</span>
                    </div>

                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="text-sm text-slate-500">Bugünkü Sefer</span>
                        <span class="font-semibold text-slate-800">{{ $todayTrips }}</span>
                    </div>

                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="text-sm text-slate-500">Aylık Gelir</span>
                        <span class="font-semibold text-emerald-600">{{ number_format($monthlyIncome, 0, ',', '.') }} ₺</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-500">Net Karlılık</span>
                        <span class="font-semibold {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ number_format($netProfit, 0, ',', '.') }} ₺
                        </span>
                    </div>
                </div>
            </div>

        </div>

    </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('dashboardRevenueChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        const gradientIncome = ctx.createLinearGradient(0, 0, 0, 300);
        gradientIncome.addColorStop(0, 'rgba(34, 211, 238, 0.45)');
        gradientIncome.addColorStop(1, 'rgba(34, 211, 238, 0.02)');

        const gradientOperation = ctx.createLinearGradient(0, 0, 0, 300);
        gradientOperation.addColorStop(0, 'rgba(52, 211, 153, 0.35)');
        gradientOperation.addColorStop(1, 'rgba(52, 211, 153, 0.02)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        label: 'Gelir',
                        data: @json($incomeSeries),
                        borderColor: '#22d3ee',
                        backgroundColor: gradientIncome,
                        fill: true,
                        borderWidth: 3,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#22d3ee',
                        pointBorderWidth: 0
                    },
                    {
                        label: 'Operasyon',
                        data: @json($operationSeries),
                        borderColor: '#34d399',
                        backgroundColor: gradientOperation,
                        fill: true,
                        borderWidth: 3,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#34d399',
                        pointBorderWidth: 0,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#cbd5e1',
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        titleColor: '#fff',
                        bodyColor: '#e2e8f0',
                        borderColor: 'rgba(148, 163, 184, 0.2)',
                        borderWidth: 1,
                        padding: 12
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(148, 163, 184, 0.08)'
                        },
                        ticks: {
                            color: '#94a3b8'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.08)'
                        },
                        ticks: {
                            color: '#94a3b8',
                            callback: function(value) {
                                return value.toLocaleString('tr-TR') + ' ₺';
                            }
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            color: '#94a3b8'
                        }
                    }
                }
            }
        });
    });
</script>

@endsection