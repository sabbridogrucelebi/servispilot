<div class="overflow-hidden rounded-[30px] border border-slate-200/70 bg-white shadow-lg shadow-slate-200/40">
    <div class="border-b border-slate-100 px-6 py-5 bg-gradient-to-r from-slate-50 to-white flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-slate-900">Aylık Puantaj ve Fatura Özeti</h3>
            <p class="mt-1 text-sm text-slate-500">Müşterinin seçili aya ait sefer hakedişlerini görüntüleyin</p>
        </div>
        
        <form action="{{ route('customers.show', $customer) }}" method="GET" class="flex items-end gap-3">
            <input type="hidden" name="tab" value="invoices">
            <div>
                <label class="mb-1 block text-xs font-bold text-slate-500 uppercase tracking-wider">Ay</label>
                <select name="month" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400 outline-none">
                    @foreach($monthOptions as $mNum => $mName)
                        <option value="{{ $mNum }}" {{ $selectedMonth == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold text-slate-500 uppercase tracking-wider">Yıl</label>
                <select name="year" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400 outline-none">
                    @foreach($yearOptions as $y)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-slate-700 transition">Göster</button>
        </form>
    </div>

    <div class="p-6 md:p-8">
        <div class="flex items-center justify-between mb-6">
            <h4 class="text-2xl font-black text-slate-900 tracking-tight uppercase">
                {{ mb_strtoupper($monthOptions[$selectedMonth], 'UTF-8') }} {{ $selectedYear }} YILI PUANTAJI
            </h4>
            <a href="{{ route('trips.index', ['customer_id' => $customer->id, 'month' => $selectedMonth, 'year' => $selectedYear]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-bold text-indigo-700 hover:bg-indigo-100 transition">
                <span>Puantaj Detayına Git</span>
                <span>↗️</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-[24px] border border-slate-100 bg-slate-50 p-5 shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Ara Toplam</div>
                <div class="text-2xl font-extrabold text-slate-800">₺{{ number_format($invoiceSummary['subtotal'], 2, ',', '.') }}</div>
            </div>

            <div class="rounded-[24px] border border-slate-100 bg-slate-50 p-5 shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">KDV (%{{ rtrim(rtrim((string)$invoiceSummary['vat_rate'], '0'), '.') }})</div>
                <div class="text-2xl font-extrabold text-slate-800">₺{{ number_format($invoiceSummary['vat_amount'], 2, ',', '.') }}</div>
            </div>

            <div class="rounded-[24px] border border-slate-100 bg-slate-50 p-5 shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Tevkifat ({{ $invoiceSummary['withholding_rate'] ?: 'Yok' }})</div>
                <div class="text-2xl font-extrabold text-slate-800">₺{{ number_format($invoiceSummary['withholding_amount'], 2, ',', '.') }}</div>
            </div>

            <div class="rounded-[24px] border border-transparent bg-gradient-to-br from-indigo-900 to-slate-900 p-5 shadow-lg text-white">
                <div class="text-xs font-bold uppercase tracking-wider text-indigo-200 mb-1">Net Fatura Tutarı</div>
                <div class="text-3xl font-black text-white">₺{{ number_format($invoiceSummary['net_total'], 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>