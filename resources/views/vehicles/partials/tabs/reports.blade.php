<div>
    {{-- Header & Month Filter --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h3 class="text-xl font-black text-slate-800 flex items-center gap-3">
                <div class="p-2 bg-sky-100 text-sky-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                Aylık Çalışma (Puantaj) Raporu
            </h3>
            <p class="text-sm font-medium text-slate-500 mt-2 ml-12">Bu aracın seçilen aydaki sefer bazlı hakediş ve müşteri analizleri</p>
        </div>

        <form method="GET" action="{{ route('vehicles.show', $vehicle) }}" class="flex items-center gap-3 bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
            <input type="hidden" name="tab" value="reports">
            
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <input type="month" name="reports_month" value="{{ $reportsMonth }}" 
                       class="pl-10 block w-full rounded-xl border-slate-200 focus:border-sky-500 focus:ring-sky-500 sm:text-sm font-bold text-slate-700 bg-slate-50/50"
                       onchange="this.form.submit()">
            </div>
            
            <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white p-2 rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </button>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {{-- Total Morning --}}
        <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-3xl p-6 border border-amber-100 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 text-amber-500/10 transition-transform group-hover:scale-110 duration-500">
                <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div class="relative z-10">
                <div class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-1">Toplam Sabah Seferi</div>
                <div class="text-4xl font-black text-slate-800">{{ number_format($reportTotals['morning']) }}</div>
                <div class="text-xs font-bold text-amber-700/70 mt-2 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Gidiş / Sabah
                </div>
            </div>
        </div>

        {{-- Total Evening --}}
        <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-3xl p-6 border border-indigo-100 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 text-indigo-500/10 transition-transform group-hover:scale-110 duration-500">
                <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </div>
            <div class="relative z-10">
                <div class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-1">Toplam Akşam Seferi</div>
                <div class="text-4xl font-black text-slate-800">{{ number_format($reportTotals['evening']) }}</div>
                <div class="text-xs font-bold text-indigo-700/70 mt-2 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Dönüş / Akşam
                </div>
            </div>
        </div>

        {{-- Total Income --}}
        @if(auth()->user()->hasPermission('financials.view'))
        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-3xl p-6 border border-emerald-100 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 text-emerald-500/10 transition-transform group-hover:scale-110 duration-500">
                <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="relative z-10">
                <div class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-1">Aylık Toplam Hakediş</div>
                <div class="text-4xl font-black text-slate-800">{{ number_format($reportTotals['income'], 2, ',', '.') }} <span class="text-xl">₺</span></div>
                <div class="text-xs font-bold text-emerald-700/70 mt-2 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Araç Bazlı Ciro
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100">
                        <th class="py-5 px-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Müşteri / Kurum</th>
                        <th class="py-5 px-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Sabah (Adet)</th>
                        <th class="py-5 px-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Akşam (Adet)</th>
                        @if(auth()->user()->hasPermission('financials.view'))
                        <th class="py-5 px-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Toplam Kazanç</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($monthlyReports as $report)
                        <tr class="hover:bg-sky-50/30 transition-colors group">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-500 font-black shadow-inner">
                                        {{ mb_substr($report['customer_name'], 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 text-sm group-hover:text-sky-700 transition-colors">{{ $report['customer_name'] }}</div>
                                        <div class="text-[10px] font-black text-slate-400 uppercase mt-0.5">Operasyon</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center justify-center min-w-[32px] h-8 px-2 rounded-lg bg-amber-50 text-amber-600 font-bold text-sm border border-amber-100 shadow-sm">
                                    {{ $report['morning_count'] }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center justify-center min-w-[32px] h-8 px-2 rounded-lg bg-indigo-50 text-indigo-600 font-bold text-sm border border-indigo-100 shadow-sm">
                                    {{ $report['evening_count'] }}
                                </span>
                            </td>
                            @if(auth()->user()->hasPermission('financials.view'))
                            <td class="py-4 px-6 text-right">
                                <span class="font-black text-emerald-600 text-base">
                                    {{ number_format($report['total_price'], 2, ',', '.') }} ₺
                                </span>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-16 text-center">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-slate-50 mb-4">
                                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                                <h4 class="text-base font-black text-slate-700">Sefer Kaydı Bulunamadı</h4>
                                <p class="text-sm font-bold text-slate-400 mt-1">Seçilen ay için bu araca ait herhangi bir operasyon kaydedilmemiş.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($monthlyReports) > 0)
                    <tfoot>
                        <tr class="bg-slate-50/50">
                            <td class="py-4 px-6 text-right font-black text-slate-600 text-[11px] uppercase tracking-wider">GENEL TOPLAM :</td>
                            <td class="py-4 px-6 text-center font-black text-amber-600">{{ number_format($reportTotals['morning']) }}</td>
                            <td class="py-4 px-6 text-center font-black text-indigo-600">{{ number_format($reportTotals['evening']) }}</td>
                            @if(auth()->user()->hasPermission('financials.view'))
                            <td class="py-4 px-6 text-right font-black text-emerald-600 text-lg">{{ number_format($reportTotals['income'], 2, ',', '.') }} ₺</td>
                            @endif
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
