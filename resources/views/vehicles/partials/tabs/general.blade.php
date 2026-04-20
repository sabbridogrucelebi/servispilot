<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    <div class="xl:col-span-8 space-y-6">
        <div class="rounded-[30px] border border-slate-200/70 bg-white/95 backdrop-blur shadow-[0_18px_45px_rgba(15,23,42,0.07)] overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-5 bg-gradient-to-r from-blue-50 via-white to-slate-50">
                <h3 class="text-lg font-bold text-slate-800">Genel Araç Bilgileri</h3>
                <p class="mt-1 text-sm text-slate-500">Aracın kimlik, sınıf ve kullanım bilgileri</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 p-6">
                <div class="rounded-2xl border border-blue-100 bg-blue-50/40 p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Plaka</div>
                    <div class="mt-2 text-base font-bold text-slate-900">{{ $vehicle->plate ?: '-' }}</div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Marka</div>
                    <div class="mt-2 text-base font-semibold text-slate-800">{{ $vehicle->brand ?: '-' }}</div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Model</div>
                    <div class="mt-2 text-base font-semibold text-slate-800">{{ $vehicle->model ?: '-' }}</div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Araç Tipi</div>
                    <div class="mt-2 text-base font-semibold text-slate-800">{{ $vehicle->vehicle_type ?: '-' }}</div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Araç Paketi</div>
                    <div class="mt-2 text-base font-semibold text-slate-800">{{ $vehicle->vehicle_package ?: '-' }}</div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Model Yılı</div>
                    <div class="mt-2 text-base font-semibold text-slate-800">{{ $vehicle->model_year ?: '-' }}</div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Tescil Tarihi</div>
                    <div class="mt-2 text-base font-semibold text-slate-800">{{ optional($vehicle->registration_date)->format('d.m.Y') ?: '-' }}</div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Koltuk Sayısı</div>
                    <div class="mt-2 text-base font-semibold text-slate-800">{{ $vehicle->seat_count ?: '-' }}</div>
                </div>

                <div class="rounded-2xl border border-indigo-100 bg-indigo-50/40 p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Toplam Sefer</div>
                    <div class="mt-2 text-base font-semibold text-slate-800">{{ $tripCount ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-[30px] border border-slate-200/70 bg-white/95 backdrop-blur shadow-[0_18px_45px_rgba(15,23,42,0.07)] overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-5 bg-gradient-to-r from-violet-50 via-white to-slate-50">
                    <h3 class="text-lg font-bold text-slate-800">Ruhsat ve Sahip Bilgileri</h3>
                    <p class="mt-1 text-sm text-slate-500">Ruhsat ve sahiplik alanları</p>
                </div>

                <div class="space-y-4 p-6">
                    <div class="rounded-2xl border border-violet-100 bg-violet-50/40 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Ruhsat Belge Seri No</div>
                        <div class="mt-2 text-base font-semibold text-slate-800">{{ $vehicle->license_serial_no ?: '-' }}</div>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Ruhsat Sahibi</div>
                        <div class="mt-2 text-base font-semibold text-slate-800">{{ $vehicle->license_owner ?: '-' }}</div>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Ruhsat Sahibi Vergi / T.C. No</div>
                        <div class="mt-2 text-base font-semibold text-slate-800">{{ $vehicle->owner_tax_or_tc_no ?: '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-[30px] border border-slate-200/70 bg-white/95 backdrop-blur shadow-[0_18px_45px_rgba(15,23,42,0.07)] overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-5 bg-gradient-to-r from-cyan-50 via-white to-slate-50">
                    <h3 class="text-lg font-bold text-slate-800">Teknik Bilgiler</h3>
                    <p class="mt-1 text-sm text-slate-500">Motor, şasi ve donanım bilgileri</p>
                </div>

                <div class="space-y-4 p-6">
                    <div class="rounded-2xl border border-cyan-100 bg-cyan-50/40 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Vites Türü</div>
                        <div class="mt-2 text-base font-semibold text-slate-800">{{ $vehicle->gear_type ?: '-' }}</div>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Yakıt Tipi</div>
                        <div class="mt-2 text-base font-semibold text-slate-800">{{ $vehicle->fuel_type ?: '-' }}</div>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Renk</div>
                        <div class="mt-2 text-base font-semibold text-slate-800">{{ $displayColor }}</div>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Motor No</div>
                        <div class="mt-2 text-base font-semibold text-slate-800 break-all">{{ $vehicle->engine_no ?: '-' }}</div>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Şasi No</div>
                        <div class="mt-2 text-base font-semibold text-slate-800 break-all">{{ $vehicle->chassis_no ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-[30px] border border-slate-200/70 bg-white/95 backdrop-blur shadow-[0_18px_45px_rgba(15,23,42,0.07)] overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-5 bg-gradient-to-r from-slate-100 via-white to-slate-50">
                <h3 class="text-lg font-bold text-slate-800">Notlar</h3>
                <p class="mt-1 text-sm text-slate-500">Araçla ilgili ek açıklamalar</p>
            </div>

            <div class="p-6">
                @if($vehicle->notes)
                    <div class="rounded-[22px] border border-slate-200 bg-slate-50/90 p-5 text-sm leading-7 text-slate-700">
                        {{ $vehicle->notes }}
                    </div>
                @else
                    <div class="rounded-[22px] border border-dashed border-slate-300 bg-slate-50/90 p-6 text-sm text-slate-500">
                        Bu araç için not girilmemiş.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="xl:col-span-4 space-y-6">
        <div class="rounded-[30px] border border-slate-200/70 bg-white/95 backdrop-blur shadow-[0_18px_45px_rgba(15,23,42,0.07)] overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-5 bg-gradient-to-r from-amber-50 via-white to-slate-50">
                <h3 class="text-lg font-bold text-slate-800">Belge ve Tarih Durumu</h3>
                <p class="mt-1 text-sm text-slate-500">Takip edilmesi gereken kritik tarihler</p>
            </div>

            <div class="space-y-4 p-6">
                <div class="rounded-2xl border border-slate-100 bg-slate-50/90 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">Muayene Tarihi</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $inspectionInfo['text'] }}</div>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $inspectionInfo['class'] }}">{{ $inspectionInfo['status'] }}</span>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/90 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">Egzoz Tarihi</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $exhaustInfo['text'] }}</div>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $exhaustInfo['class'] }}">{{ $exhaustInfo['status'] }}</span>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/90 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">Sigorta Bitiş Tarihi</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $insuranceInfo['text'] }}</div>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $insuranceInfo['class'] }}">{{ $insuranceInfo['status'] }}</span>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/90 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">Kasko Bitiş Tarihi</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $kaskoInfo['text'] }}</div>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $kaskoInfo['class'] }}">{{ $kaskoInfo['status'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-[30px] border border-slate-200/70 bg-white/95 backdrop-blur shadow-[0_18px_45px_rgba(15,23,42,0.07)] overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-5 bg-gradient-to-r from-emerald-50 via-white to-slate-50">
                <h3 class="text-lg font-bold text-slate-800">Hızlı Özet</h3>
                <p class="mt-1 text-sm text-slate-500">Önemli araç bilgileri</p>
            </div>

            <div class="space-y-3 p-6">
                <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-3">
                    <span class="text-sm text-slate-500">Plaka</span>
                    <span class="font-semibold text-slate-800">{{ $vehicle->plate ?: '-' }}</span>
                </div>

                <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-3">
                    <span class="text-sm text-slate-500">Araç Tipi</span>
                    <span class="font-semibold text-slate-800">{{ $vehicle->vehicle_type ?: '-' }}</span>
                </div>

                <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-3">
                    <span class="text-sm text-slate-500">Yakıt Tipi</span>
                    <span class="font-semibold text-slate-800">{{ $vehicle->fuel_type ?: '-' }}</span>
                </div>

                <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-3">
                    <span class="text-sm text-slate-500">Vites Türü</span>
                    <span class="font-semibold text-slate-800">{{ $vehicle->gear_type ?: '-' }}</span>
                </div>

                <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-3">
                    <span class="text-sm text-slate-500">Toplam Sefer</span>
                    <span class="font-semibold text-slate-800">{{ $tripCount ?? 0 }}</span>
                </div>

                <div class="flex items-center justify-between rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-3">
                    <span class="text-sm text-slate-600">Net Karlılık</span>
                    <span class="font-bold {{ $profitTextClass }}">{{ number_format($profit, 2, ',', '.') }} ₺</span>
                </div>
            </div>
        </div>
    </div>
</div>