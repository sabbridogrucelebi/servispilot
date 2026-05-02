@extends('layouts.app')

@section('title', 'Ödeme ve Fatura Detayı')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-4xl mx-auto">

    <div class="mb-8">
        <a href="{{ route('billing.index') }}" class="flex items-center text-sm font-bold text-slate-500 hover:text-indigo-500 transition-colors mb-4">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Geri Dön
        </a>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Fatura Detayı: {{ $invoice->invoice_no }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Payment Details -->
        <div class="md:col-span-2 space-y-8">
            
            <!-- Invoice Status -->
            <div class="bg-white/70 backdrop-blur-md p-8 rounded-[32px] border border-slate-200/60 shadow-xl">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <div class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Ödenecek Tutar</div>
                        <div class="text-4xl font-black text-slate-800">{{ number_format($invoice->amount, 2) }} {{ $invoice->currency }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Fatura Durumu</div>
                        @if($invoice->status === 'paid')
                            <span class="bg-emerald-100 text-emerald-600 px-4 py-1.5 rounded-full text-xs font-black border border-emerald-200 uppercase">ÖDENDİ</span>
                        @elseif($invoice->status === 'pending_approval')
                            <span class="bg-amber-100 text-amber-600 px-4 py-1.5 rounded-full text-xs font-black border border-amber-200 uppercase">ONAY BEKLİYOR</span>
                        @else
                            <span class="bg-rose-100 text-rose-600 px-4 py-1.5 rounded-full text-xs font-black border border-rose-200 uppercase">ÖDEME BEKLİYOR</span>
                        @endif
                    </div>
                </div>

                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200/60">
                    <h3 class="font-black text-slate-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Banka Hesap Bilgilerimiz (Havale/EFT)
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 bg-white rounded-xl border border-slate-200 shadow-sm">
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase">Banka</div>
                                <div class="font-bold text-slate-700">{{ \App\Models\GlobalSetting::where('key', 'bank_name')->value('value') ?? 'Lütfen Super Admin panelinden banka bilgisi giriniz.' }}</div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white rounded-xl border border-slate-200 shadow-sm">
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase">Hesap Sahibi</div>
                                <div class="font-bold text-slate-700">{{ \App\Models\GlobalSetting::where('key', 'bank_account_holder')->value('value') ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white rounded-xl border border-slate-200 shadow-sm group">
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase">IBAN</div>
                                <div class="font-mono font-bold text-indigo-600">{{ \App\Models\GlobalSetting::where('key', 'bank_iban')->value('value') ?? '-' }}</div>
                            </div>
                            @php
                                $rawIban = str_replace(' ', '', \App\Models\GlobalSetting::where('key', 'bank_iban')->value('value') ?? '');
                            @endphp
                            <button onclick="copyToClipboard('{{ $rawIban }}')" class="p-2 hover:bg-slate-50 rounded-lg text-slate-400 hover:text-indigo-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="mt-4 p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs font-bold text-amber-700">
                        ⚠️ Lütfen açıklama kısmına faturanızın numarasını ({{ $invoice->invoice_no }}) yazmayı unutmayın.
                    </div>
                </div>
            </div>

            <!-- Receipt Upload -->
            @if($invoice->status !== 'paid')
                <div class="bg-white/70 backdrop-blur-md p-8 rounded-[32px] border border-slate-200/60 shadow-xl">
                    <h3 class="text-xl font-black text-slate-800 mb-6">Dekont Yükle</h3>
                    <form action="{{ route('billing.invoice.upload-receipt', $invoice) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Banka Dekontu (PDF, JPG veya PNG)</label>
                            <div class="flex items-center justify-center w-full">
                                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-[20px] cursor-pointer bg-slate-50 hover:bg-slate-100 transition-all">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 mb-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                        <p class="mb-2 text-sm text-slate-500"><span class="font-black text-indigo-600">Tıkla veya sürükle</span></p>
                                        <p class="text-xs text-slate-400">Maksimum 10MB</p>
                                    </div>
                                    <input type="file" name="receipt" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required />
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="w-full py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-black rounded-[20px] shadow-lg shadow-indigo-500/30 hover:shadow-xl transition-all hover:-translate-y-1 active:scale-95">
                            DEKONTU GÖNDER VE ONAYA SUN
                        </button>
                    </form>
                </div>
            @endif

        </div>

        <!-- Sidebar / Summary -->
        <div class="space-y-6">
            <div class="bg-indigo-600 p-8 rounded-[32px] text-white shadow-xl relative overflow-hidden">
                <h4 class="text-indigo-200 text-xs font-bold uppercase tracking-wider mb-4">Özet</h4>
                <div class="space-y-4">
                    <div class="flex justify-between border-b border-white/10 pb-4">
                        <span class="text-sm font-bold opacity-80">Alt Toplam</span>
                        <span class="text-sm font-black">{{ number_format($invoice->amount, 2) }} TRY</span>
                    </div>
                    <div class="flex justify-between border-b border-white/10 pb-4">
                        <span class="text-sm font-bold opacity-80">KDV (%20)</span>
                        <span class="text-sm font-black">Dahil</span>
                    </div>
                    <div class="flex justify-between pt-2">
                        <span class="text-lg font-bold">Toplam</span>
                        <span class="text-2xl font-black">{{ number_format($invoice->amount, 2) }} TRY</span>
                    </div>
                </div>
            </div>

            @if($invoice->status === 'rejected')
                <div class="bg-rose-50 border border-rose-200 rounded-[24px] p-6">
                    <div class="text-rose-800 font-bold text-sm mb-2">Red Nedeni:</div>
                    <div class="text-rose-700 text-xs font-medium">{{ str_replace('Reddedildi: ', '', $invoice->admin_notes) }}</div>
                </div>
            @endif
        </div>

    </div>

</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('IBAN kopyalandı!');
    });
}
</script>
@endsection
