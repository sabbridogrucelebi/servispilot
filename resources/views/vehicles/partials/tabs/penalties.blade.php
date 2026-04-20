<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    <div class="xl:col-span-9 rounded-[30px] border border-slate-200/60 bg-white/90 backdrop-blur shadow-xl overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-lg font-bold text-slate-800">Trafik Cezaları</h3>
            <p class="mt-1 text-sm text-slate-500">Bu araca ait trafik cezası kayıtları</p>
        </div>

        @if($vehiclePenalties->count())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Ceza No / Plaka</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Tarih</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Madde / Yer</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Ödeme</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Belgeler</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach($vehiclePenalties as $penalty)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-6 py-4 align-top">
                                    <div class="text-sm font-bold text-slate-800">
                                        {{ $penalty->penalty_no ?: '-' }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Plaka: {{ $vehicle->plate ?: '-' }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Şoför: {{ $penalty->driver_name ?: '-' }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 align-top">
                                    <div class="text-sm font-semibold text-slate-800">
                                        {{ optional($penalty->penalty_date)->format('d.m.Y') ?: '-' }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        @if($penalty->penalty_time)
                                            Saat: {{ $penalty->penalty_time }}
                                        @else
                                            Saat bilgisi yok
                                        @endif
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Son indirim: {{ optional($penalty->discount_deadline)->format('d.m.Y') ?: '-' }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 align-top">
                                    <div class="text-sm font-semibold text-slate-800">
                                        {{ $penalty->penalty_article ?: '-' }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $penalty->penalty_location ?: '-' }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 align-top">
                                    @if($penalty->payment_status === 'paid')
                                        <div class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                                            Ödendi
                                        </div>

                                        <div class="mt-2 text-xs text-slate-500">
                                            Ödeme tarihi: {{ optional($penalty->payment_date)->format('d.m.Y') ?: '-' }}
                                        </div>

                                        <div class="mt-1 text-sm font-bold text-slate-800">
                                            {{ number_format((float) $penalty->paid_amount, 2, ',', '.') }} ₺
                                        </div>

                                        @if($penalty->is_discount_eligible)
                                            <div class="mt-1 text-xs font-semibold text-emerald-600">
                                                %25 indirimli ödendi
                                            </div>
                                        @else
                                            <div class="mt-1 text-xs font-semibold text-rose-600">
                                                İndirimsiz ödeme
                                            </div>
                                        @endif
                                    @else
                                        <div class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                                            Bekliyor
                                        </div>

                                        @if($penalty->remaining_days_for_discount > 0)
                                            <div class="mt-2 text-xs font-semibold text-emerald-600">
                                                {{ $penalty->remaining_days_for_discount }} gün indirim kaldı
                                            </div>
                                            <div class="mt-1 text-xs text-slate-500">
                                                Bugün ödenirse:
                                            </div>
                                            <div class="mt-1 text-sm font-bold text-emerald-600">
                                                {{ number_format((float) $penalty->discounted_amount, 2, ',', '.') }} ₺
                                            </div>
                                        @else
                                            <div class="mt-2 text-xs font-semibold text-rose-600">
                                                İndirim süresi doldu
                                            </div>
                                            <div class="mt-1 text-xs text-slate-500">
                                                Bugün ödenirse:
                                            </div>
                                            <div class="mt-1 text-sm font-bold text-slate-800">
                                                {{ number_format((float) $penalty->penalty_amount, 2, ',', '.') }} ₺
                                            </div>
                                        @endif
                                    @endif
                                </td>

                                <td class="px-6 py-4 align-top">
                                    <div class="flex flex-col gap-2">
                                        @if($penalty->traffic_penalty_document)
                                            <a href="{{ asset('storage/' . $penalty->traffic_penalty_document) }}"
                                               target="_blank"
                                               class="inline-flex items-center gap-2 text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                                <span>📄</span>
                                                <span>Ceza Belgesi</span>
                                            </a>
                                        @else
                                            <span class="inline-flex items-center gap-2 text-xs text-slate-400">
                                                <span>📄</span>
                                                <span>Belge yok</span>
                                            </span>
                                        @endif

                                        @if($penalty->payment_receipt)
                                            <a href="{{ asset('storage/' . $penalty->payment_receipt) }}"
                                               target="_blank"
                                               class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-600 hover:text-emerald-800">
                                                <span>🧾</span>
                                                <span>Ödeme Dekontu</span>
                                            </a>
                                        @else
                                            <span class="inline-flex items-center gap-2 text-xs text-slate-400">
                                                <span>🧾</span>
                                                <span>Dekont yok</span>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6">
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                    <div class="text-4xl mb-3">🚨</div>
                    <div class="text-base font-semibold text-slate-700">Ceza kaydı bulunmuyor</div>
                    <div class="mt-1 text-sm text-slate-500">Bu araca ait trafik cezası kaydı henüz eklenmemiş.</div>
                </div>
            </div>
        @endif
    </div>

    <div class="xl:col-span-3 rounded-[30px] border border-slate-200/60 bg-white/90 backdrop-blur shadow-xl overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-lg font-bold text-slate-800">Ceza İşlemleri</h3>
            <p class="mt-1 text-sm text-slate-500">Yeni kayıt ve raporlama alanı</p>
        </div>

        <div class="p-6 space-y-4">
            <a href="{{ route('traffic-penalties.create', ['vehicle_id' => $vehicle->id]) }}"
               class="block w-full rounded-2xl bg-rose-50 px-4 py-3 text-center text-sm font-semibold text-rose-700 hover:bg-rose-100 transition">
                Ceza Ekle
            </a>

            <a href="{{ route('traffic-penalties.export.excel', ['vehicle_id' => $vehicle->id]) }}"
               class="block w-full rounded-2xl bg-emerald-50 px-4 py-3 text-center text-sm font-semibold text-emerald-700 hover:bg-emerald-100 transition">
                Excel İndir
            </a>

            <a href="{{ route('traffic-penalties.export.pdf', ['vehicle_id' => $vehicle->id]) }}"
               class="block w-full rounded-2xl bg-rose-50 px-4 py-3 text-center text-sm font-semibold text-rose-700 hover:bg-rose-100 transition">
                PDF İndir
            </a>
        </div>
    </div>
</div>