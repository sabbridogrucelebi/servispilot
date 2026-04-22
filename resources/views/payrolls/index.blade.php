@extends('layouts.app')

@section('title', 'Maaş ve Finansal Yönetim')
@section('subtitle', 'Personel hakediş, kesinti ve ödeme merkezi')

@section('content')
<div class="space-y-6" x-data="{ 
    calculateNet(id) {
        const base = parseFloat(document.getElementById('base_' + id).value) || 0;
        const trips = parseFloat(document.getElementById('trips_' + id).value) || 0;
        const extra = parseFloat(document.getElementById('extra_' + id).value) || 0;
        
        const bank = parseFloat(document.getElementById('bank_' + id).value) || 0;
        const penalty = parseFloat(document.getElementById('penalty_' + id).value) || 0;
        const advance = parseFloat(document.getElementById('advance_' + id).value) || 0;
        const deduction = parseFloat(document.getElementById('deduction_' + id).value) || 0;
        
        const net = (base + trips + extra) - (bank + penalty + advance + deduction);
        document.getElementById('net_display_' + id).innerText = net.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
    }
}">
    <!-- Üst Bar -->
    <div class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('payrolls.index') }}" method="GET" class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-400">Çalışma Dönemi</label>
                    <input type="month" name="period" value="{{ $period }}" onchange="this.form.submit()"
                           class="rounded-2xl border-slate-200 bg-slate-50 py-3 px-4 text-sm font-bold text-slate-900 focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <button type="button" onclick="document.getElementById('bulkSaveForm').submit()" 
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-8 py-4 text-sm font-black text-white shadow-lg shadow-emerald-200 transition-all hover:bg-emerald-700 hover:scale-[1.02]">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                TÜMÜNÜ KAYDET VE GÜNCELLE
            </button>
        </form>
    </div>

    <!-- Dev Yatay Tablo -->
    <div class="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm">
        <form id="bulkSaveForm" action="{{ route('payrolls.bulk-store') }}" method="POST">
            @csrf
            <input type="hidden" name="period" value="{{ $period }}">
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-900 border-b border-slate-800">
                            <th class="px-4 py-5 font-black text-white/50 text-center w-12">NO</th>
                            <th class="px-4 py-5 font-black text-white uppercase tracking-widest w-48">PERSONEL</th>
                            <th class="px-4 py-5 font-black text-white uppercase tracking-widest text-right">ANA MAAŞ</th>
                            
                            <th class="px-4 py-5 font-black text-blue-400 uppercase tracking-widest text-center bg-blue-900/20 w-32">BANKAYA YATAN</th>
                            <th class="px-4 py-5 font-black text-emerald-400 uppercase tracking-widest text-right w-32">EK HAKEDİŞ</th>
                            <th class="px-4 py-5 font-black text-rose-400 uppercase tracking-widest text-center bg-rose-900/10 w-32">TRAFİK CEZASI</th>
                            
                            <th class="px-4 py-5 font-black text-orange-400 uppercase tracking-widest text-center w-32">AVANS</th>
                            <th class="px-4 py-5 font-black text-slate-400 uppercase tracking-widest text-center w-32">KESİNTİ</th>
                            <th class="px-4 py-5 font-black text-amber-400 uppercase tracking-widest text-center bg-amber-900/10 w-32">EKSTRA (+)</th>
                            
                            <th class="px-4 py-5 font-black text-white uppercase tracking-widest text-right">NET ÖDENECEK</th>
                            <th class="px-4 py-5 font-black text-white text-center">İŞLEM</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($calculatedPayrolls as $index => $item)
                            @php
                                $driver = $item['driver'];
                                $calc = $item['calculation'];
                                $ex = $item['existing'];
                                $id = $driver->id;
                                
                                $bank = $ex ? $ex->bank_payment : 0;
                                $penalty = $ex ? $ex->traffic_penalty : 0;
                                $advance = $ex ? $ex->advance_payment : 0;
                                $deduction = $ex ? $ex->deduction : 0;
                                $extraBonus = $ex ? $ex->extra_bonus : 0;
                                
                                $liveNet = ($calc['base_salary'] + $calc['extra_earnings'] + $extraBonus) - ($bank + $penalty + $advance + $deduction);
                            @endphp
                            <tr class="transition-colors hover:bg-slate-50/80 group" x-data="{ 
                                showExtraNote: {{ $extraBonus > 0 ? 'true' : 'false' }},
                                showDeductionNote: {{ $deduction > 0 ? 'true' : 'false' }}
                            }">
                                <td class="px-4 py-4 text-center font-bold text-slate-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-4">
                                    <div class="font-extrabold text-slate-900 whitespace-nowrap">{{ $driver->full_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">{{ $driver->vehicle?->plate ?? 'ARAÇSIZ' }}</div>
                                </td>
                                
                                <input type="hidden" id="base_{{ $id }}" name="payrolls[{{ $id }}][base_salary]" value="{{ $calc['base_salary'] }}">
                                <input type="hidden" id="trips_{{ $id }}" name="payrolls[{{ $id }}][extra_earnings]" value="{{ $calc['extra_earnings'] }}">

                                <td class="px-4 py-4 text-right font-black text-slate-900 text-sm">
                                    {{ number_format($calc['base_salary'], 2, ',', '.') }} ₺
                                </td>

                                <!-- Bankaya Yatan -->
                                <td class="px-2 py-4 bg-blue-50/30">
                                    <input type="number" step="0.01" id="bank_{{ $id }}" name="payrolls[{{ $id }}][bank_payment]" value="{{ $bank }}"
                                           @input="calculateNet({{ $id }})"
                                           class="w-full rounded-xl border-blue-100 bg-white py-2 px-3 text-right font-black text-blue-700 text-sm focus:ring-2 focus:ring-blue-500">
                                </td>

                                <!-- Ek Hakediş (Otomatik) -->
                                <td class="px-4 py-4 text-right font-black text-emerald-600 text-sm">
                                    +{{ number_format($calc['extra_earnings'], 2, ',', '.') }} ₺
                                </td>

                                <!-- Trafik Cezası -->
                                <td class="px-2 py-4 bg-rose-50/30">
                                    <input type="number" step="0.01" id="penalty_{{ $id }}" name="payrolls[{{ $id }}][traffic_penalty]" value="{{ $penalty }}"
                                           @input="calculateNet({{ $id }})"
                                           class="w-full rounded-xl border-rose-100 bg-white py-2 px-3 text-right font-black text-rose-700 text-sm focus:ring-2 focus:ring-rose-500">
                                </td>

                                <!-- Avans -->
                                <td class="px-2 py-4">
                                    <input type="number" step="0.01" id="advance_{{ $id }}" name="payrolls[{{ $id }}][advance_payment]" value="{{ $advance }}"
                                           @input="calculateNet({{ $id }})"
                                           class="w-full rounded-xl border-orange-100 bg-white py-2 px-3 text-right font-black text-orange-700 text-sm focus:ring-2 focus:ring-orange-500">
                                </td>

                                <!-- Kesinti -->
                                <td class="px-2 py-4">
                                    <div class="space-y-2">
                                        <input type="number" step="0.01" id="deduction_{{ $id }}" name="payrolls[{{ $id }}][deduction]" value="{{ $deduction }}"
                                               @input="calculateNet({{ $id }}); showDeductionNote = ($event.target.value > 0)"
                                               class="w-full rounded-xl border-slate-200 bg-white py-2 px-3 text-right font-black text-slate-700 text-sm focus:ring-2 focus:ring-slate-500">
                                        
                                        <div x-show="showDeductionNote" x-transition>
                                            <input type="text" name="payrolls[{{ $id }}][deduction_notes]" value="{{ $ex->deduction_notes ?? '' }}"
                                                   placeholder="Kesinti sebebi..."
                                                   class="w-full rounded-lg border-rose-200 bg-rose-50 py-1 px-2 text-[10px] font-bold text-rose-900 placeholder-rose-300">
                                        </div>
                                    </div>
                                </td>

                                <!-- Ekstra (+) -->
                                <td class="px-2 py-4 bg-amber-50/30">
                                    <div class="space-y-2">
                                        <input type="number" step="0.01" id="extra_{{ $id }}" name="payrolls[{{ $id }}][extra_bonus]" value="{{ $extraBonus }}"
                                               @input="calculateNet({{ $id }}); showExtraNote = ($event.target.value > 0)"
                                               class="w-full rounded-xl border-amber-200 bg-white py-2 px-3 text-right font-black text-amber-700 text-sm focus:ring-2 focus:ring-amber-500">
                                        
                                        <div x-show="showExtraNote" x-transition>
                                            <input type="text" name="payrolls[{{ $id }}][extra_notes]" value="{{ $ex->extra_notes ?? '' }}"
                                                   placeholder="Ekstra sebebi..."
                                                   class="w-full rounded-lg border-rose-200 bg-rose-50 py-1 px-2 text-[10px] font-bold text-rose-900 placeholder-rose-300">
                                        </div>
                                    </div>
                                </td>

                                <!-- Net Ödenecek -->
                                <td class="px-4 py-4 text-right">
                                    <div id="net_display_{{ $id }}" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 font-black text-white text-sm shadow-lg shadow-slate-200">
                                        {{ number_format($liveNet, 2, ',', '.') }} ₺
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-center">
                                    <a href="{{ route('payrolls.report', ['driver' => $id, 'period' => $period]) }}" 
                                       class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-900 hover:text-white transition-all">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>
@endsection