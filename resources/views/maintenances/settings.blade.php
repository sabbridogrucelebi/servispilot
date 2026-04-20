@extends('layouts.app')

@section('title', 'Bakım Ayarları')
@section('subtitle', 'Araç bazlı yağ bakım ve alt yağlama kilometre aralıklarını yönetin')

@section('content')

<div class="space-y-6">

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">Bakım Ayarları</h2>
            <p class="mt-2 text-sm font-medium text-slate-500">
                Her araç için yağ bakım ve alt yağlama kilometre aralıklarını ayrı ayrı belirleyin.
            </p>
        </div>

        <a href="{{ route('maintenances.index') }}"
           class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
            ← Bakım Listesine Dön
        </a>
    </div>

    <form action="{{ route('maintenances.settings.store') }}" method="POST">
        @csrf

        <div class="overflow-hidden rounded-[30px] border border-slate-200/60 bg-white/90 shadow-xl backdrop-blur">
            <div class="border-b border-slate-100 px-6 py-5">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Araç Bazlı Bakım Aralıkları</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Örnek: Otokar Sultan için yağ bakımı 10.000 KM, alt yağlama 5.000 KM.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px]">
                    <thead class="border-b border-slate-100 bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Araç</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Marka / Model</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Yağ Bakım KM Aralığı</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Alt Yağlama KM Aralığı</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($vehicles as $index => $vehicle)
                            <tr class="hover:bg-indigo-50/30 transition">
                                <td class="px-6 py-5">
                                    <div class="text-sm font-extrabold tracking-wide text-slate-900">
                                        {{ $vehicle->plate }}
                                    </div>
                                </td>

                                <td class="px-6 py-5 text-sm text-slate-600">
                                    {{ $vehicle->brand ?? '-' }} {{ $vehicle->model ?? '' }}
                                </td>

                                <td class="px-6 py-5">
                                    <input type="hidden" name="settings[{{ $index }}][vehicle_id]" value="{{ $vehicle->id }}">

                                    <input type="number"
                                           min="0"
                                           name="settings[{{ $index }}][oil_change_interval_km]"
                                           value="{{ old("settings.$index.oil_change_interval_km", optional($vehicle->maintenanceSetting)->oil_change_interval_km) }}"
                                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500"
                                           placeholder="ÖRN: 10000">
                                </td>

                                <td class="px-6 py-5">
                                    <input type="number"
                                           min="0"
                                           name="settings[{{ $index }}][under_lubrication_interval_km]"
                                           value="{{ old("settings.$index.under_lubrication_interval_km", optional($vehicle->maintenanceSetting)->under_lubrication_interval_km) }}"
                                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500"
                                           placeholder="ÖRN: 5000">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10">
                                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                                        <div class="mb-3 text-4xl">🚐</div>
                                        <div class="text-base font-semibold text-slate-700">Araç bulunamadı</div>
                                        <div class="mt-1 text-sm text-slate-500">Bakım ayarları için önce araç kaydı olmalı.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 bg-slate-50/70 px-6 py-5">
                <div class="flex justify-end">
                    <button type="submit"
                            class="rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200/60 transition hover:scale-[1.01]">
                        Ayarları Kaydet
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>

@endsection