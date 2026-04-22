@php
    $currentTab = request('tab', 'general');
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div class="flex flex-wrap items-center gap-5">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-[24px] bg-gradient-to-br from-indigo-500 to-blue-500 text-3xl text-white shadow-lg">
                    🚗
                </div>

                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">
                        {{ $vehicle->plate }}
                    </h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">
                        {{ $vehicle->brand ?: '-' }} {{ $vehicle->model ?: '' }} · {{ $vehicle->vehicle_type ?: 'Araç tipi yok' }}
                    </p>
                </div>
            </div>

            <div class="hidden h-12 w-px bg-slate-200 xl:block"></div>

            <div class="flex items-center gap-4 rounded-[24px] border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-100 text-2xl">
                    🧑‍✈️
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Araç Şoförü</div>
                    <div class="mt-1 text-base font-bold text-slate-900">
                        {{ $driverFullName ?: 'Atanmış şoför yok' }}
                    </div>
                    <div class="mt-1 text-sm text-slate-500">
                        @if($driverFullName)
                            {{ $driverAge ? $driverAge . ' yaş' : 'Yaş bilgisi yok' }}
                            ·
                            {{ $driverPhone ?: 'Telefon bilgisi yok' }}
                        @else
                            Şoför atandığında burada görünecek
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 rounded-[24px] border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-2xl">
                    🛞
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Güncel KM</div>
                    <div class="mt-1 text-base font-bold text-slate-900">{{ $formattedKm }} KM</div>
                    <div class="mt-1 text-sm text-slate-500">
                        Son yakıt / kilometre verisine göre gösterilir
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if($vehicle->is_active)
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-4 py-2 text-sm font-bold text-emerald-700">
                    ● Aktif Araç
                </span>
            @else
                <span class="inline-flex items-center gap-2 rounded-full bg-rose-100 px-4 py-2 text-sm font-bold text-rose-700">
                    ● Pasif Araç
                </span>
            @endif

            <a href="{{ route('vehicles.edit', $vehicle) }}"
               class="inline-flex items-center rounded-2xl bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-100 transition">
                Düzenle
            </a>

            <a href="{{ route('vehicles.index') }}"
               class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                Listeye Dön
            </a>
        </div>
    </div>

    @if($currentTab === 'general')
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 {{ $arventoStats ? 'xl:grid-cols-5' : 'xl:grid-cols-4' }}">
            <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-sky-500 to-blue-600 p-5 text-white shadow-xl">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-sm font-medium text-white/80">Toplam Gelir</div>
                    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($income, 2, ',', '.') }} ₺</div>
                    <div class="mt-2 text-xs text-white/75">Bu araca bağlı toplam sefer geliri</div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-orange-500 to-amber-500 p-5 text-white shadow-xl">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-sm font-medium text-white/80">Yakıt Gideri</div>
                    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($fuel, 2, ',', '.') }} ₺</div>
                    <div class="mt-2 text-xs text-white/75">Araca işlenen toplam yakıt maliyeti</div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-violet-500 to-fuchsia-500 p-5 text-white shadow-xl">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-sm font-medium text-white/80">Maaş Gideri</div>
                    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($salary, 2, ',', '.') }} ₺</div>
                    <div class="mt-2 text-xs text-white/75">Bağlı şoförlerin toplam net maaşı</div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br {{ $profitColorClass }} p-5 text-white shadow-xl">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-sm font-medium text-white/80">Net Karlılık</div>
                    <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($profit, 2, ',', '.') }} ₺</div>
                    <div class="mt-2 text-xs text-white/75">Gelir - yakıt - maaş sonucu</div>
                </div>
            </div>

            @if($arventoStats)
                <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-rose-500 to-red-600 p-5 text-white shadow-xl">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
                    <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
                    <div class="relative">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                            </span>
                            <div class="text-sm font-medium text-white/80">Bugünkü Max Hız</div>
                        </div>
                        <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($arventoStats['max_speed'] ?? 0, 0) }} <span class="text-sm font-normal opacity-80">km/h</span></div>
                        <div class="mt-2 text-xs text-white/75 italic">Arvento Canlı Sistem Verisi</div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>