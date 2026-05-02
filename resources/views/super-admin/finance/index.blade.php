@extends('layouts.super-admin')

@section('title', 'Finans ve Ödemeler')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <!-- Page header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-[0_8px_16px_-6px_rgba(16,185,129,0.5)] border border-white/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">Finans Yönetimi</h1>
                    <p class="text-sm font-medium text-slate-500 mt-1">Gelen Havale/EFT bildirimlerini ve faturaları yönetin.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards (Premium) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white/70 backdrop-blur-md p-6 rounded-[24px] border border-slate-200/60 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Toplam Tahsilat</div>
            <div class="text-2xl font-black text-slate-800">{{ number_format($invoices->where('status', 'paid')->sum('amount'), 2) }} TRY</div>
        </div>
        <div class="bg-amber-50/70 backdrop-blur-md p-6 rounded-[24px] border border-amber-200/60 shadow-sm">
            <div class="text-amber-600 text-xs font-bold uppercase tracking-wider mb-1">Onay Bekleyen</div>
            <div class="text-2xl font-black text-amber-700">{{ $invoices->where('status', 'pending_approval')->count() }} Adet</div>
        </div>
        <div class="bg-rose-50/70 backdrop-blur-md p-6 rounded-[24px] border border-rose-200/60 shadow-sm">
            <div class="text-rose-600 text-xs font-bold uppercase tracking-wider mb-1">Bekleyen Ödeme</div>
            <div class="text-2xl font-black text-rose-700">{{ number_format($invoices->where('status', 'unpaid')->sum('amount'), 2) }} TRY</div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white/70 backdrop-blur-md shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] rounded-[32px] border border-slate-200/60 overflow-hidden relative">
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead class="text-xs uppercase text-slate-400 bg-slate-50/50 border-b border-slate-200/60">
                    <tr>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Firma</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Fatura No</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Tutar</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Durum</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Dekont</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-right">İşlem</div></th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200/60">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-800">{{ $invoice->company->name }}</div>
                                <div class="text-xs font-bold text-indigo-500 mt-0.5">
                                    {{ $invoice->plan ? 'Plan Seçimi: ' . $invoice->plan->name : ($invoice->admin_notes ?? 'Genel Ödeme') }}
                                </div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap font-medium text-slate-500">
                                {{ $invoice->invoice_no }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap font-bold text-slate-800">
                                {{ number_format($invoice->amount, 2) }} {{ $invoice->currency }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($invoice->status === 'paid')
                                    <span class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-full text-[11px] font-bold border border-emerald-200">Ödendi</span>
                                @elseif($invoice->status === 'pending_approval')
                                    <span class="bg-amber-100 text-amber-600 px-3 py-1 rounded-full text-[11px] font-bold border border-amber-200 animate-pulse">Onay Bekliyor</span>
                                @elseif($invoice->status === 'rejected')
                                    <span class="bg-rose-100 text-rose-600 px-3 py-1 rounded-full text-[11px] font-bold border border-rose-200">Reddedildi</span>
                                @else
                                    <span class="bg-slate-100 text-slate-400 px-3 py-1 rounded-full text-[11px] font-bold border border-slate-200">Ödeme Bekliyor</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($invoice->payment_receipt_path)
                                    <a href="{{ Storage::url($invoice->payment_receipt_path) }}" target="_blank" class="text-indigo-600 hover:text-indigo-700 font-bold flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                        Görüntüle
                                    </a>
                                @else
                                    <span class="text-slate-300 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right font-medium">
                                @if($invoice->status === 'pending_approval')
                                    <div class="flex justify-end gap-2">
                                        <form action="{{ route('super-admin.finance.approve', $invoice) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-xl font-bold text-xs shadow-sm transition-all hover:-translate-y-0.5">Onayla</button>
                                        </form>
                                        <button onclick="rejectInvoice({{ $invoice->id }})" class="bg-rose-500 hover:bg-rose-500 text-white px-3 py-1.5 rounded-xl font-bold text-xs shadow-sm transition-all hover:-translate-y-0.5">Reddet</button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-500">
                                Henüz bir finansal hareket bulunmuyor.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function rejectInvoice(id) {
    const reason = prompt('Reddetme sebebinizi yazın:');
    if (reason) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/super-admin/finance/invoices/${id}/reject`;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        
        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'reason';
        reasonInput.value = reason;
        
        form.appendChild(csrfInput);
        form.appendChild(reasonInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
