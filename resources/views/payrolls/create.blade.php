@extends('layouts.app')

@section('title', 'Yeni Maaş Kaydı')
@section('subtitle', 'Personel hakediş giriş ekranı')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('payrolls.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-blue-600 transition">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Listeye Geri Dön
        </a>
    </div>

    <form action="{{ route('payrolls.store') }}" method="POST" id="payrollForm">
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Form Alanları -->
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-[32px] border border-slate-200 bg-white p-8 shadow-sm">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-bold text-slate-700">Personel / Şoför Seçimi</label>
                            <select name="driver_id" class="w-full rounded-2xl border-slate-200 bg-slate-50/50 py-3 px-4 text-slate-900 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Personel Seçiniz</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                                        {{ $driver->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('driver_id') <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">Maaş Dönemi (Ay)</label>
                            <input type="month" name="period_month" value="{{ old('period_month', now()->format('Y-m')) }}" 
                                   class="w-full rounded-2xl border-slate-200 bg-slate-50/50 py-3 px-4 text-slate-900 focus:border-blue-500 focus:ring-blue-500">
                            @error('period_month') <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">Ana Maaş (Hakediş)</label>
                            <div class="relative">
                                <input type="number" step="0.01" name="base_salary" id="base_salary" value="{{ old('base_salary') }}" 
                                       class="calc-input w-full rounded-2xl border-slate-200 bg-slate-50/50 py-3 pl-4 pr-12 text-slate-900 focus:border-blue-500 focus:ring-blue-500" placeholder="0,00">
                                <span class="absolute inset-y-0 right-4 flex items-center text-slate-400 font-bold">₺</span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700 text-emerald-600">Ek Ödeme / Prim (+)</label>
                            <div class="relative">
                                <input type="number" step="0.01" name="extra_payment" id="extra_payment" value="{{ old('extra_payment', 0) }}" 
                                       class="calc-input w-full rounded-2xl border-emerald-100 bg-emerald-50/30 py-3 pl-4 pr-12 text-emerald-700 focus:border-emerald-500 focus:ring-emerald-500" placeholder="0,00">
                                <span class="absolute inset-y-0 right-4 flex items-center text-emerald-400 font-bold">₺</span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700 text-rose-600">Kesinti / Ceza (-)</label>
                            <div class="relative">
                                <input type="number" step="0.01" name="deduction" id="deduction" value="{{ old('deduction', 0) }}" 
                                       class="calc-input w-full rounded-2xl border-rose-100 bg-rose-50/30 py-3 pl-4 pr-12 text-rose-700 focus:border-rose-500 focus:ring-rose-500" placeholder="0,00">
                                <span class="absolute inset-y-0 right-4 flex items-center text-rose-400 font-bold">₺</span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700 text-rose-600">Avans Ödemesi (-)</label>
                            <div class="relative">
                                <input type="number" step="0.01" name="advance_payment" id="advance_payment" value="{{ old('advance_payment', 0) }}" 
                                       class="calc-input w-full rounded-2xl border-rose-100 bg-rose-50/30 py-3 pl-4 pr-12 text-rose-700 focus:border-rose-500 focus:ring-rose-500" placeholder="0,00">
                                <span class="absolute inset-y-0 right-4 flex items-center text-rose-400 font-bold">₺</span>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-bold text-slate-700">Notlar</label>
                            <textarea name="notes" rows="3" class="w-full rounded-2xl border-slate-200 bg-slate-50/50 py-3 px-4 text-slate-900 focus:border-blue-500 focus:ring-blue-500" placeholder="Maaş detayları, prim sebebi vb.">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sağ Taraf: Canlı Hesaplama Özeti -->
            <div class="space-y-6">
                <div class="sticky top-6 rounded-[32px] bg-slate-900 p-8 text-white shadow-2xl">
                    <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-sm">📊</span>
                        Ödeme Özeti
                    </h3>

                    <div class="space-y-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Ana Maaş</span>
                            <span id="summary_base" class="font-bold">0,00 ₺</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-emerald-400">Ek Ödemeler</span>
                            <span id="summary_extra" class="font-bold">+0,00 ₺</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-rose-400">Toplam Kesinti</span>
                            <span id="summary_deduction" class="font-bold">-0,00 ₺</span>
                        </div>
                        
                        <div class="my-6 h-px bg-slate-800"></div>

                        <div class="space-y-1">
                            <span class="text-xs font-bold uppercase tracking-widest text-slate-500">Net Ödenecek</span>
                            <div class="text-4xl font-black text-blue-500" id="summary_net">0,00 ₺</div>
                        </div>

                        <button type="submit" class="group mt-8 flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 py-4 text-sm font-black transition-all hover:bg-blue-500 active:scale-95">
                            KAYDI TAMAMLA
                            <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </button>
                    </div>
                </div>
                
                <div class="rounded-[32px] border-2 border-dashed border-slate-200 p-6 text-center">
                    <p class="text-xs font-bold text-slate-500">
                        * Tüm hesaplamalar girdiğiniz verilere göre anlık olarak güncellenir.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.calc-input');
        
        function calculate() {
            const base = parseFloat(document.getElementById('base_salary').value) || 0;
            const extra = parseFloat(document.getElementById('extra_payment').value) || 0;
            const deduction = parseFloat(document.getElementById('deduction').value) || 0;
            const advance = parseFloat(document.getElementById('advance_payment').value) || 0;
            
            const totalDeduction = deduction + advance;
            const net = (base + extra) - totalDeduction;
            
            document.getElementById('summary_base').textContent = base.toLocaleString('tr-TR', { minimumFractionDigits: 2 }) + ' ₺';
            document.getElementById('summary_extra').textContent = '+' + extra.toLocaleString('tr-TR', { minimumFractionDigits: 2 }) + ' ₺';
            document.getElementById('summary_deduction').textContent = '-' + totalDeduction.toLocaleString('tr-TR', { minimumFractionDigits: 2 }) + ' ₺';
            document.getElementById('summary_net').textContent = net.toLocaleString('tr-TR', { minimumFractionDigits: 2 }) + ' ₺';
        }

        inputs.forEach(input => {
            input.addEventListener('input', calculate);
        });

        // Sayfa yüklendiğinde bir kez çalıştır
        calculate();
    });
</script>
@endsection