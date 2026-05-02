<div class="grid grid-cols-1 xl:grid-cols-12 gap-6" x-data="{ importOpen: false }">

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
               class="block w-full text-center rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 transition">
                Excel İndir
            </a>

            <a href="{{ route('maintenances.export.pdf', ['vehicle_id' => $vehicle->id]) }}"
               class="block w-full text-center rounded-2xl bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-100 transition">
                PDF İndir
            </a>

            <a href="{{ route('maintenances.create', ['vehicle_id' => $vehicle->id]) }}"
               class="block w-full text-center rounded-2xl bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition">
                Bakım Ekle
            </a>

            <button type="button"
                    @click="importOpen = !importOpen"
                    class="w-full text-center rounded-2xl bg-gradient-to-r from-indigo-50 to-violet-50 border border-indigo-200 px-4 py-3 text-sm font-bold text-indigo-700 hover:from-indigo-100 hover:to-violet-100 transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Toplu Bakım Ekle (Excel)
            </button>

        </div>

        {{-- Toplu Bakım Ekleme Paneli --}}
        <div x-show="importOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             style="display: none;"
             class="border-t border-slate-100 p-6 space-y-4 bg-gradient-to-b from-indigo-50/30 to-white">

            <div class="rounded-2xl bg-indigo-50 border border-indigo-100 p-4">
                <h4 class="text-sm font-bold text-indigo-900 mb-1">📥 Şablonu İndirin</h4>
                <p class="text-[11px] text-indigo-700/70 mb-3 leading-relaxed">
                    Bakım kayıtlarınızı toplu olarak eklemek için önce şablonu indirin, doldurun ve yükleyin. Boş alanlar hata vermez.
                </p>
                <a href="{{ route('maintenances.import.template') }}"
                   class="inline-flex items-center gap-1.5 text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest bg-white px-3 py-1.5 rounded-lg border border-indigo-200 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    ŞABLONU İNDİR
                </a>
            </div>

            <form action="{{ route('maintenances.import') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Excel Dosyası (.xlsx, .xls, .csv)</label>
                    <input type="file" name="excel_file" required accept=".xlsx,.xls,.csv"
                           class="w-full text-sm text-slate-500 file:mr-3 file:py-2.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition-all border border-slate-200 rounded-2xl cursor-pointer">
                </div>
                <button type="submit"
                        class="w-full bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white px-4 py-3 rounded-2xl font-bold text-sm transition-all shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    YÜKLE VE AKTAR
                </button>
            </form>
        </div>
    </div>

</div>
