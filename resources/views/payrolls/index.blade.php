@extends('layouts.app')

@section('title', 'Maaş ve Finansal Yönetim')
@section('subtitle', 'Personel hakediş, kesinti ve ödeme merkezi')

@section('content')
<div class="space-y-6" x-data="{ 
    totals: { base: 0, bank: 0, trips: 0, penalty: 0, advance: 0, deduction: 0, extra: 0, net: 0 },
    saving: false,
    undoStack: [],
    
    // Satırı Kaydet
    saveRow(id) {
        this.saving = true;
        const data = {
            base_salary: parseFloat(document.getElementById('base_' + id).value) || 0,
            extra_earnings: parseFloat(document.getElementById('trips_' + id).value) || 0,
            bank_payment: parseFloat(document.getElementById('bank_' + id).value) || 0,
            traffic_penalty: parseFloat(document.getElementById('penalty_' + id).value) || 0,
            advance_payment: parseFloat(document.getElementById('advance_' + id).value) || 0,
            deduction: parseFloat(document.getElementById('deduction_' + id).value) || 0,
            deduction_notes: document.querySelector(`input[name='payrolls[${id}][deduction_notes]']`)?.value || '',
            extra_bonus: parseFloat(document.getElementById('extra_' + id).value) || 0,
            extra_notes: document.querySelector(`input[name='payrolls[${id}][extra_notes]']`)?.value || ''
        };

        fetch('{{ route('payrolls.update-single') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                driver_id: id,
                period: '{{ $period }}',
                data: data
            })
        })
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                document.getElementById('net_display_' + id).innerText = res.net.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                this.updateTotals();
            }
        })
        .finally(() => {
            setTimeout(() => { this.saving = false; }, 500);
        });
    },

    // Değişikliği Geri Al (Ctrl+Z)
    undo() {
        if (this.undoStack.length === 0) return;
        const lastAction = this.undoStack.pop();
        const el = document.getElementById(lastAction.id);
        if (el) {
            el.value = lastAction.oldValue;
            // Alpine event'lerini tetikle
            el.dispatchEvent(new Event('input'));
            el.dispatchEvent(new Event('change'));
            this.saveRow(lastAction.driverId);
        }
    },

    // Değişiklik Öncesi Durumu Kaydet (Undo için)
    track(id, el) {
        this.undoStack.push({
            id: el.id,
            driverId: id,
            oldValue: el.value
        });
        if (this.undoStack.length > 50) this.undoStack.shift(); // Max 50 geri alma
    },

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
        this.updateTotals();
    },

    updateTotals() {
        let t = { base: 0, bank: 0, trips: 0, penalty: 0, advance: 0, deduction: 0, extra: 0, net: 0 };
        document.querySelectorAll('.val-base').forEach(el => t.base += parseFloat(el.value) || 0);
        document.querySelectorAll('.val-trips').forEach(el => t.trips += parseFloat(el.value) || 0);
        document.querySelectorAll('[id^=bank_]').forEach(el => t.bank += parseFloat(el.value) || 0);
        document.querySelectorAll('[id^=penalty_]').forEach(el => t.penalty += parseFloat(el.value) || 0);
        document.querySelectorAll('[id^=advance_]').forEach(el => t.advance += parseFloat(el.value) || 0);
        document.querySelectorAll('[id^=deduction_]').forEach(el => t.deduction += parseFloat(el.value) || 0);
        document.querySelectorAll('[id^=extra_]').forEach(el => t.extra += parseFloat(el.value) || 0);
        t.net = (t.base + t.trips + t.extra) - (t.bank + t.penalty + t.advance + t.deduction);
        this.totals = t;
    },

    init() {
        this.updateTotals();
        window.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'z') {
                e.preventDefault();
                this.undo();
            }
        });
    }
}">
    <!-- Üst Bar -->
    <div class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                <form action="{{ route('payrolls.index') }}" method="GET">
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-400">Çalışma Dönemi</label>
                    <input type="month" name="period" value="{{ $period }}" onchange="this.form.submit()"
                           class="rounded-2xl border-slate-200 bg-slate-50 py-3 px-4 text-sm font-bold text-slate-900 focus:border-blue-500 focus:ring-blue-500">
                </form>
            </div>

            <div class="flex items-center gap-4">
                <!-- Kayıt Durumu Göstergesi -->
                <div x-show="saving" x-transition class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-50 text-blue-600 font-bold text-xs">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Kaydediliyor...
                </div>
                <div x-show="!saving" x-transition class="flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 text-emerald-600 font-bold text-xs">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Tüm Değişiklikler Kaydedildi
                </div>
                <div class="text-[10px] text-slate-400 font-bold uppercase">Geri Al: Ctrl + Z</div>
            </div>
        </div>
    </div>

    <!-- Dev Yatay Tablo -->
    <div class="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm">
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
                        <th class="px-4 py-5 font-black text-slate-400 uppercase tracking-widest text-center w-32">KESİNTİ / İCRA</th>
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
                                <div class="text-base font-black text-slate-900 whitespace-nowrap">{{ $driver->full_name }}</div>
                                <div class="text-[12px] text-slate-500 font-bold uppercase tracking-wide">{{ $driver->vehicle?->plate ?? 'ARAÇSIZ' }}</div>
                            </td>
                            
                            <input type="hidden" id="base_{{ $id }}" class="val-base" value="{{ $calc['base_salary'] }}">
                            <input type="hidden" id="trips_{{ $id }}" class="val-trips" value="{{ $calc['extra_earnings'] }}">

                            <td class="px-4 py-4 text-right font-black text-slate-900 text-sm">
                                {{ number_format($calc['base_salary'], 2, ',', '.') }} ₺
                            </td>

                            <td class="px-2 py-4 bg-blue-50/30">
                                <input type="number" step="0.01" id="bank_{{ $id }}" value="{{ $bank }}"
                                       @focus="track({{ $id }}, $event.target)"
                                       @input="calculateNet({{ $id }})"
                                       @change="saveRow({{ $id }})"
                                       class="w-full rounded-xl border-blue-100 bg-white py-2 px-3 text-right font-black text-blue-700 text-sm focus:ring-2 focus:ring-blue-500">
                            </td>

                            <td class="px-4 py-4 text-right font-black text-emerald-600 text-sm">
                                +{{ number_format($calc['extra_earnings'], 2, ',', '.') }} ₺
                            </td>

                            <td class="px-2 py-4 bg-rose-50/30">
                                <input type="number" step="0.01" id="penalty_{{ $id }}" value="{{ $penalty }}"
                                       @focus="track({{ $id }}, $event.target)"
                                       @input="calculateNet({{ $id }})"
                                       @change="saveRow({{ $id }})"
                                       class="w-full rounded-xl border-rose-100 bg-white py-2 px-3 text-right font-black text-rose-700 text-sm focus:ring-2 focus:ring-rose-500">
                            </td>

                            <td class="px-2 py-4">
                                <input type="number" step="0.01" id="advance_{{ $id }}" value="{{ $advance }}"
                                       @focus="track({{ $id }}, $event.target)"
                                       @input="calculateNet({{ $id }})"
                                       @change="saveRow({{ $id }})"
                                       class="w-full rounded-xl border-orange-100 bg-white py-2 px-3 text-right font-black text-orange-700 text-sm focus:ring-2 focus:ring-orange-500">
                            </td>

                            <td class="px-2 py-4">
                                <div class="space-y-2">
                                    <input type="number" step="0.01" id="deduction_{{ $id }}" value="{{ $deduction }}"
                                           @focus="track({{ $id }}, $event.target)"
                                           @input="calculateNet({{ $id }}); showDeductionNote = ($event.target.value > 0)"
                                           @change="saveRow({{ $id }})"
                                           class="w-full rounded-xl border-slate-200 bg-white py-2 px-3 text-right font-black text-slate-700 text-sm focus:ring-2 focus:ring-slate-500">
                                    <div x-show="showDeductionNote" x-transition>
                                        <input type="text" name="payrolls[{{ $id }}][deduction_notes]" value="{{ $ex->deduction_notes ?? '' }}"
                                               @change="saveRow({{ $id }})"
                                               placeholder="İcra/Kesinti sebebi..."
                                               class="w-full rounded-lg border-rose-200 bg-rose-50 py-1 px-2 text-[10px] font-bold text-rose-900 placeholder-rose-300">
                                    </div>
                                </div>
                            </td>

                            <td class="px-2 py-4 bg-amber-50/30">
                                <div class="space-y-2">
                                    <input type="number" step="0.01" id="extra_{{ $id }}" value="{{ $extraBonus }}"
                                           @focus="track({{ $id }}, $event.target)"
                                           @input="calculateNet({{ $id }}); showExtraNote = ($event.target.value > 0)"
                                           @change="saveRow({{ $id }})"
                                           class="w-full rounded-xl border-amber-200 bg-white py-2 px-3 text-right font-black text-amber-700 text-sm focus:ring-2 focus:ring-amber-500">
                                    <div x-show="showExtraNote" x-transition>
                                        <input type="text" name="payrolls[{{ $id }}][extra_notes]" value="{{ $ex->extra_notes ?? '' }}"
                                               @change="saveRow({{ $id }})"
                                               placeholder="Ekstra sebebi..."
                                               class="w-full rounded-lg border-rose-200 bg-rose-50 py-1 px-2 text-[10px] font-bold text-rose-900 placeholder-rose-300">
                                    </div>
                                </div>
                            </td>

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
                <tfoot>
                    <tr class="bg-slate-50 border-t-2 border-slate-200">
                        <td colspan="2" class="px-4 py-6 text-right font-black text-slate-900 text-sm uppercase tracking-widest">GENEL TOPLAMLAR</td>
                        <td class="px-4 py-6 text-right font-black text-slate-900 text-sm" x-text="totals.base.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺'"></td>
                        <td class="px-4 py-6 text-center font-black text-blue-700 text-sm bg-blue-50/30" x-text="totals.bank.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺'"></td>
                        <td class="px-4 py-6 text-right font-black text-emerald-600 text-sm" x-text="totals.trips.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺'"></td>
                        <td class="px-4 py-6 text-center font-black text-rose-700 text-sm bg-rose-50/30" x-text="totals.penalty.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺'"></td>
                        <td class="px-4 py-6 text-center font-black text-orange-700 text-sm" x-text="totals.advance.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺'"></td>
                        <td class="px-4 py-6 text-center font-black text-slate-700 text-sm" x-text="totals.deduction.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺'"></td>
                        <td class="px-4 py-6 text-center font-black text-amber-700 text-sm bg-amber-50/30" x-text="totals.extra.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺'"></td>
                        <td class="px-4 py-6 text-right">
                            <div class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2.5 font-black text-white text-sm shadow-lg shadow-blue-200" x-text="totals.net.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺'"></div>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection