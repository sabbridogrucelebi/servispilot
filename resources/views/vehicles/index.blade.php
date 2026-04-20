@extends('layouts.app')

@section('title', 'Araçlar')
@section('subtitle', 'Filonuzdaki tüm araçları yönetin')

@section('content')

@php
    $totalVehicles = $vehicles->count();
    $activeVehicles = $vehicles->where('is_active', true)->count();
    $passiveVehicles = $vehicles->where('is_active', false)->count();

    $vehicleTypeStats = [
        [
            'label' => 'Midibüs Araç Sayısı',
            'count' => $vehicles->where('vehicle_type', 'Midibüs')->count(),
            'note' => 'Midibüs sınıfındaki araçlar',
            'gradient' => 'from-orange-500 to-amber-500',
            'icon' => '🚌',
        ],
        [
            'label' => 'Minibüs Araç Sayısı',
            'count' => $vehicles->where('vehicle_type', 'Minibüs')->count(),
            'note' => 'Minibüs sınıfındaki araçlar',
            'gradient' => 'from-blue-500 to-cyan-500',
            'icon' => '🚐',
        ],
        [
            'label' => 'Binek Araç Sayısı',
            'count' => $vehicles->where('vehicle_type', 'Binek Araç')->count(),
            'note' => 'Binek sınıfındaki araçlar',
            'gradient' => 'from-rose-500 to-red-500',
            'icon' => '🚗',
        ],
        [
            'label' => 'Otobüs Araç Sayısı',
            'count' => $vehicles->where('vehicle_type', 'Otobüs')->count(),
            'note' => 'Otobüs sınıfındaki araçlar',
            'gradient' => 'from-violet-500 to-fuchsia-500',
            'icon' => '🚍',
        ],
        [
            'label' => 'Panelvan Araç Sayısı',
            'count' => $vehicles->where('vehicle_type', 'Panelvan')->count(),
            'note' => 'Panelvan sınıfındaki araçlar',
            'gradient' => 'from-emerald-500 to-teal-500',
            'icon' => '🚚',
        ],
    ];
@endphp

<div class="space-y-6">

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">Araç Yönetimi</h2>
            <p class="mt-2 text-sm font-medium text-slate-500">
                Filonuzdaki araçları görüntüleyin, düzenleyin ve yönetin.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('vehicles.export.excel') }}"
               class="inline-flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700 shadow-sm hover:bg-emerald-100 transition">
                <span>📊</span>
                <span>Excel İndir</span>
            </a>

            <a href="{{ route('vehicles.create') }}"
               class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 hover:scale-[1.02] transition">
                <span class="text-base">+</span>
                <span>Yeni Araç Ekle</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">

        <div class="relative overflow-hidden rounded-[28px] p-5 text-white shadow-xl bg-gradient-to-br from-blue-500 to-indigo-600">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute right-8 bottom-0 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Toplam Araç</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ $totalVehicles }}</div>
                <div class="mt-2 text-xs text-white/75">Kayıtlı tüm filo araçları</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] p-5 text-white shadow-xl bg-gradient-to-br from-emerald-500 to-teal-500">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute right-8 bottom-0 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Aktif Araç</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ $activeVehicles }}</div>
                <div class="mt-2 text-xs text-white/75">Operasyonda kullanılan araçlar</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] p-5 text-white shadow-xl bg-gradient-to-br from-rose-500 to-pink-500">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute right-8 bottom-0 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Pasif Araç</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ $passiveVehicles }}</div>
                <div class="mt-2 text-xs text-white/75">Şu an aktif olmayan araçlar</div>
            </div>
        </div>

        <div id="vehicle-type-kpi-card"
             class="relative overflow-hidden rounded-[28px] p-5 text-white shadow-xl bg-gradient-to-br {{ $vehicleTypeStats[0]['gradient'] }} transition-all duration-700">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute right-8 bottom-0 h-16 w-16 rounded-full bg-white/10"></div>

            <div class="relative flex items-start justify-between gap-3">
                <div>
                    <div id="vehicle-type-kpi-label" class="text-sm font-medium text-white/80">
                        {{ $vehicleTypeStats[0]['label'] }}
                    </div>
                    <div id="vehicle-type-kpi-count" class="mt-3 text-3xl font-extrabold tracking-tight">
                        {{ $vehicleTypeStats[0]['count'] }}
                    </div>
                    <div id="vehicle-type-kpi-note" class="mt-2 text-xs text-white/75">
                        {{ $vehicleTypeStats[0]['note'] }}
                    </div>
                </div>

                <div id="vehicle-type-kpi-icon"
                     class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-2xl shadow-sm">
                    {{ $vehicleTypeStats[0]['icon'] }}
                </div>
            </div>
        </div>

    </div>

    <div class="rounded-[30px] border border-slate-200/60 bg-white/90 backdrop-blur shadow-xl p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="xl:col-span-2">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                    Ara
                </label>
                <input type="text"
                       placeholder="Plaka, marka, model ile ara..."
                       class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                    Durum
                </label>
                <select class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option>Tüm Durumlar</option>
                    <option>Aktif</option>
                    <option>Pasif</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                    Yakıt Tipi
                </label>
                <select class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option>Tüm Yakıt Tipleri</option>
                    <option>Dizel</option>
                    <option>Benzin</option>
                    <option>LPG</option>
                    <option>Benzin + LPG</option>
                </select>
            </div>
        </div>
    </div>

    <div class="rounded-[30px] border border-slate-200/60 bg-white/90 backdrop-blur shadow-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Araç Listesi</h3>
                <p class="mt-1 text-sm text-slate-500">Tüm araç kayıtlarını detaylı görüntüleyin</p>
            </div>

            <div class="text-sm font-medium text-slate-400">
                Toplam {{ $totalVehicles }} kayıt
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px]">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Araç</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Tip / Paket</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Model</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Yakıt / Vites</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Durum</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-[0.14em] text-slate-500">İşlemler</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($vehicles as $vehicle)
                        <tr class="hover:bg-indigo-50/40 transition duration-200">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-500 flex items-center justify-center text-white text-lg shadow">
                                        🚗
                                    </div>

                                    <div>
                                        <div class="text-sm font-extrabold tracking-wide text-slate-900">
                                            {{ $vehicle->plate }}
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ $vehicle->brand ?: '-' }} {{ $vehicle->model ?: '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="text-sm font-semibold text-slate-800">
                                    {{ $vehicle->vehicle_type ?: '-' }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $vehicle->vehicle_package ?: 'Paket belirtilmedi' }}
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="text-sm font-semibold text-slate-800">
                                    {{ $vehicle->model_year ?: '-' }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $vehicle->seat_count ? $vehicle->seat_count . ' koltuk' : 'Koltuk bilgisi yok' }}
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="text-sm font-semibold text-slate-800">
                                    {{ $vehicle->fuel_type ?: '-' }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $vehicle->gear_type ?: 'Vites bilgisi yok' }}
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                @if($vehicle->is_active)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">
                                        ● Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full bg-rose-100 text-rose-700">
                                        ● Pasif
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-5">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('vehicles.show', $vehicle) }}"
                                       class="px-3 py-2 text-xs font-semibold rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">
                                        Detay
                                    </a>

                                    <a href="{{ route('vehicles.edit', $vehicle) }}"
                                       class="px-3 py-2 text-xs font-semibold rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                                        Düzenle
                                    </a>

                                    <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST" onsubmit="return confirm('Bu aracı silmek istediğine emin misin?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-2 text-xs font-semibold rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 transition">
                                            Sil
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="mx-auto max-w-md">
                                    <div class="mb-3 text-4xl">🚫</div>
                                    <div class="text-base font-semibold text-slate-700">Henüz araç kaydı yok</div>
                                    <div class="mt-1 text-sm text-slate-500">
                                        İlk araç kaydını oluşturarak filonuzu yönetmeye başlayın.
                                    </div>
                                    <div class="mt-5">
                                        <a href="{{ route('vehicles.create') }}"
                                           class="inline-flex items-center rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg hover:scale-[1.02] transition">
                                            Yeni Araç Ekle
                                        </a>
                                    </div>
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
        const vehicleTypeStats = @json($vehicleTypeStats);

        if (!vehicleTypeStats || vehicleTypeStats.length === 0) return;

        const card = document.getElementById('vehicle-type-kpi-card');
        const label = document.getElementById('vehicle-type-kpi-label');
        const count = document.getElementById('vehicle-type-kpi-count');
        const note = document.getElementById('vehicle-type-kpi-note');
        const icon = document.getElementById('vehicle-type-kpi-icon');

        const gradients = [
            'from-orange-500', 'to-amber-500',
            'from-blue-500', 'to-cyan-500',
            'from-rose-500', 'to-red-500',
            'from-violet-500', 'to-fuchsia-500',
            'from-emerald-500', 'to-teal-500'
        ];

        let currentIndex = 0;

        function renderCard(index) {
            const item = vehicleTypeStats[index];

            card.classList.remove(...gradients);
            const itemGradients = item.gradient.split(' ');
            card.classList.add(...itemGradients);

            label.textContent = item.label;
            count.textContent = item.count;
            note.textContent = item.note;
            icon.textContent = item.icon;
        }

        renderCard(currentIndex);

        setInterval(() => {
            currentIndex = (currentIndex + 1) % vehicleTypeStats.length;
            renderCard(currentIndex);
        }, 5000);
    });
</script>

@endsection