<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    <div class="xl:col-span-8 rounded-[30px] border border-slate-200/60 bg-white/90 backdrop-blur shadow-xl overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-lg font-bold text-slate-800">Araç Çalışma Raporu</h3>
            <p class="mt-1 text-sm text-slate-500">Bu araca ait sefer ve çalışma kayıtları</p>
        </div>

        @if($recentTrips->count())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Tarih</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Hat</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Durum</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Fiyat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentTrips as $trip)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-slate-700">{{ optional($trip->trip_date)->format('d.m.Y') ?: '-' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $trip->route_name ?? ($trip->serviceRoute->name ?? '-') }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $trip->status ?? 'Tamamlandı' }}</td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-slate-800">{{ number_format($trip->trip_price ?? 0, 2, ',', '.') }} ₺</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6">
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                    <div class="text-4xl mb-3">📈</div>
                    <div class="text-base font-semibold text-slate-700">Sefer kaydı bulunmuyor</div>
                    <div class="mt-1 text-sm text-slate-500">Bu araca bağlı çalışma raporları burada görünecek.</div>
                </div>
            </div>
        @endif
    </div>

    <div class="xl:col-span-4 rounded-[30px] border border-slate-200/60 bg-white/90 backdrop-blur shadow-xl overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-lg font-bold text-slate-800">Rapor İşlemleri</h3>
            <p class="mt-1 text-sm text-slate-500">Yazdırma ve dışa aktarma</p>
        </div>

        <div class="p-6 space-y-4">
            <button type="button" class="w-full rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">Excel İndir</button>
            <button type="button" class="w-full rounded-2xl bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700">Yazdır</button>
        </div>
    </div>
</div>