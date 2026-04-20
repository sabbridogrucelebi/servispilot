<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

    <div class="xl:col-span-8 rounded-[30px] border border-slate-200/60 bg-white/90 backdrop-blur shadow-xl overflow-hidden">

        <div class="border-b border-slate-100 px-6 py-5 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Araç Bakımları</h3>
                <p class="mt-1 text-sm text-slate-500">Bakım kayıtları, maliyet ve raporlama alanı</p>
            </div>

            <a href="{{ route('maintenances.create', ['vehicle_id' => $vehicle->id]) }}"
               class="inline-flex items-center rounded-2xl bg-gradient-to-r from-slate-700 to-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow">
                Bakım Ekle
            </a>
        </div>

        @if($vehicleMaintenances->count())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Bakım</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Tür</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Tarih</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">KM</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Servis</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase text-slate-500">Tutar</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach($vehicleMaintenances as $maintenance)
                            <tr class="hover:bg-indigo-50/30 transition">

                                <!-- BAKIM -->
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-slate-800">
                                        {{ $maintenance->title ?? '-' }}
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1">
                                        {{ $maintenance->description ?: 'Açıklama yok' }}
                                    </div>
                                </td>

                                <!-- TÜR -->
                                <td class="px-6 py-4 text-sm font-semibold text-slate-700">
                                    {{ $maintenance->maintenance_type ?? '-' }}
                                </td>

                                <!-- TARİH -->
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-slate-800">
                                        {{ optional($maintenance->service_date)->format('d.m.Y') ?: '-' }}
                                    </div>

                                    <div class="text-xs text-slate-500 mt-1">
                                        @if($maintenance->next_service_date)
                                            Sonraki: {{ optional($maintenance->next_service_date)->format('d.m.Y') }}
                                        @else
                                            Sonraki tarih yok
                                        @endif
                                    </div>
                                </td>

                                <!-- KM -->
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-slate-800">
                                        {{ $maintenance->km ? number_format($maintenance->km,0,',','.') . ' KM' : '-' }}
                                    </div>

                                    <div class="text-xs text-slate-500 mt-1">
                                        @if($maintenance->next_service_km)
                                            Sonraki: {{ number_format($maintenance->next_service_km,0,',','.') }} KM
                                        @else
                                            Sonraki KM yok
                                        @endif
                                    </div>
                                </td>

                                <!-- SERVİS -->
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $maintenance->service_name ?? '-' }}
                                </td>

                                <!-- TUTAR -->
                                <td class="px-6 py-4 text-right text-sm font-semibold text-slate-800">
                                    {{ number_format($maintenance->amount ?? 0, 2, ',', '.') }} ₺
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6">
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                    <div class="text-4xl mb-3">🛠️</div>
                    <div class="text-base font-semibold text-slate-700">Henüz bakım kaydı yok</div>
                    <div class="mt-1 text-sm text-slate-500">Bakım ekleyerek başlayabilirsin.</div>
                </div>
            </div>
        @endif

    </div>

    <!-- SAĞ PANEL -->
    <div class="xl:col-span-4 rounded-[30px] border border-slate-200/60 bg-white/90 backdrop-blur shadow-xl overflow-hidden">

        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-lg font-bold text-slate-800">Bakım İşlemleri</h3>
            <p class="mt-1 text-sm text-slate-500">Rapor / yazdır / excel alanı</p>
        </div>

        <div class="p-6 space-y-4">

            <a href="{{ route('maintenances.export.excel', ['vehicle_id' => $vehicle->id]) }}"
               class="block w-full text-center rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                Excel İndir
            </a>

            <a href="{{ route('maintenances.export.pdf', ['vehicle_id' => $vehicle->id]) }}"
               class="block w-full text-center rounded-2xl bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700">
                PDF İndir
            </a>

            <a href="{{ route('maintenances.create', ['vehicle_id' => $vehicle->id]) }}"
               class="block w-full text-center rounded-2xl bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                Bakım Ekle
            </a>

        </div>
    </div>

</div>