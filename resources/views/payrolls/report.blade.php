@extends('layouts.app')

@section('title', 'Personel Hakediş Detayı')

@section('content')
@php
    $ex = \App\Models\Payroll::where('driver_id', $driver->id)->where('period_month', $period)->first();
    $bank = $ex ? (float)$ex->bank_payment : 0;
    $penalty = $ex ? (float)$ex->traffic_penalty : 0;
    $advance = $ex ? (float)$ex->advance_payment : 0;
    $deduction = $ex ? (float)$ex->deduction : 0;
    $extraBonus = $ex ? (float)$ex->extra_bonus : 0;
    $extraNotes = $ex ? $ex->extra_notes : '';
    
    $finalNet = ($report['base_salary'] + $report['extra_earnings'] + $extraBonus) - ($bank + $penalty + $advance + $deduction);
@endphp

<div class="mx-auto max-w-4xl space-y-6" x-data="{ openRoute: null }">
    <!-- Üst Bar / Aksiyonlar -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('payrolls.index', ['period' => $period]) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-blue-600 transition">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Listeye Geri Dön
        </a>
        
        <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-6 py-3 text-sm font-black text-white shadow-lg transition-all hover:bg-slate-800">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 00-2 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            YAZDIR / PDF KAYDET
        </button>
    </div>

    <!-- Hakediş Belgesi (Dikey Form) -->
    <div class="print-area rounded-[32px] border border-slate-200 bg-white p-10 shadow-sm overflow-hidden relative">
        <!-- Logo ve Başlık -->
        <div class="flex items-start justify-between border-b border-slate-100 pb-8">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">PERSONEL MAAŞ DÖKÜMÜ</h1>
                <p class="mt-1 text-lg font-bold text-blue-600">{{ \Carbon\Carbon::parse($period)->translatedFormat('F Y') }} Dönemi</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-black text-slate-300">ServisPilot Pro</div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">IRMAK TURİZM TAŞIMACILIK</div>
            </div>
        </div>

        <!-- Personel Bilgileri -->
        <div class="mt-8 grid grid-cols-2 gap-8 bg-slate-50 p-6 rounded-2xl border border-slate-100">
            <div>
                <div class="text-xs font-black text-slate-400 uppercase tracking-widest">Personel Bilgileri</div>
                <div class="mt-2 text-xl font-black text-slate-900">{{ $driver->full_name }}</div>
                <div class="text-sm font-bold text-slate-500">TC No: {{ $driver->tc_no ?? '---' }}</div>
            </div>
            <div class="text-right">
                <div class="text-xs font-black text-slate-400 uppercase tracking-widest">Ana Maaş</div>
                <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format($report['base_salary'], 2, ',', '.') }} ₺</div>
            </div>
        </div>

        <!-- Sefer Özet Tablosu -->
        <div class="mt-8">
            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                AYLIK EK SEFER ÖZETİ
            </h3>
            <div class="overflow-hidden border border-slate-100 rounded-2xl">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-4 py-4 font-bold">Müşteri / Güzergah</th>
                            <th class="px-4 py-4 font-bold text-center">Sabah</th>
                            <th class="px-4 py-4 font-bold text-center">Akşam</th>
                            <th class="px-4 py-4 font-bold text-right">Toplam Ücret</th>
                            <th class="px-4 py-4 font-bold text-center no-print w-24">DÖKÜM</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($report['details'] as $index => $routeSummary)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-4">
                                    <div class="font-black text-slate-900 uppercase text-[11px]">{{ $routeSummary['customer_name'] }}</div>
                                    <div class="text-sm font-bold text-blue-600 mt-0.5">{{ $routeSummary['route_name'] }}</div>
                                </td>
                                <td class="px-4 py-4 text-center font-black text-slate-700">
                                    <span class="inline-flex items-center justify-center h-7 w-7 rounded-lg bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">{{ $routeSummary['morning_count'] }}</span>
                                </td>
                                <td class="px-4 py-4 text-center font-black text-slate-700">
                                    <span class="inline-flex items-center justify-center h-7 w-7 rounded-lg bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">{{ $routeSummary['evening_count'] }}</span>
                                </td>
                                <td class="px-4 py-4 text-right font-black text-slate-900 text-base">
                                    {{ number_format($routeSummary['total_fee'], 2, ',', '.') }} ₺
                                </td>
                                <td class="px-4 py-4 text-center no-print">
                                    <button @click="openRoute === {{ $index }} ? openRoute = null : openRoute = {{ $index }}" 
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-blue-600 hover:text-white transition-all">
                                        <svg class="h-4 w-4 transition-transform" :class="openRoute === {{ $index }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </td>
                            </tr>
                            
                            <!-- Tarih Dökümü (Panel) -->
                            <tr x-show="openRoute === {{ $index }}" x-collapse class="bg-slate-50/50 no-print">
                                <td colspan="5" class="px-8 py-4">
                                    <div class="grid grid-cols-4 gap-4">
                                        @foreach($routeSummary['dates'] as $dateInfo)
                                            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                                                <div class="text-[10px] font-black text-slate-400">{{ $dateInfo['date'] }}</div>
                                                <div class="mt-2 flex items-center justify-between">
                                                    <div class="space-y-0.5">
                                                        @if($dateInfo['morning'] > 0) <div class="text-[9px] font-bold text-emerald-600">Sabah: {{ number_format($dateInfo['morning'], 0) }}₺</div> @endif
                                                        @if($dateInfo['evening'] > 0) <div class="text-[9px] font-bold text-emerald-600">Akşam: {{ number_format($dateInfo['evening'], 0) }}₺</div> @endif
                                                    </div>
                                                    <div class="text-xs font-black text-slate-900">{{ number_format($dateInfo['total'], 0) }} ₺</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>

                            <!-- Yazdırma Formatında Görünecek Sefer Listesi (Sadece Yazdırırken) -->
                            <tr class="hidden print:table-row bg-slate-50/20">
                                <td colspan="5" class="px-6 py-2 text-[10px] text-slate-500 italic">
                                    Sefer Tarihleri: {{ collect($routeSummary['dates'])->pluck('date')->implode(', ') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400 font-bold italic">Bu dönemde ek hakediş kaydı bulunmamaktadır.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Finansal Özet ve İmza Alanı Aynı Kalıyor... -->
        <div class="mt-10 grid grid-cols-2 gap-12">
            <div>
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-100 pb-2">Ek Ödeme / Notlar</h4>
                <div class="space-y-3">
                    @if($extraBonus > 0)
                        <div class="flex justify-between text-sm font-bold text-emerald-600">
                            <span>Ekstra Ödeme:</span>
                            <span>+{{ number_format($extraBonus, 2, ',', '.') }} ₺</span>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3 text-xs text-slate-600 italic border border-slate-100">
                            <strong>Açıklama:</strong> {{ $extraNotes ?: 'Belirtilmedi' }}
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">Bu ay ekstra ödeme veya not bulunmamaktadır.</p>
                    @endif
                </div>
            </div>

            <div class="space-y-3">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-100 pb-2 text-right">Finansal Özet</h4>
                <div class="flex justify-between text-sm font-bold text-slate-600"><span>Ana Maaş:</span><span>{{ number_format($report['base_salary'], 2, ',', '.') }} ₺</span></div>
                <div class="flex justify-between text-sm font-bold text-emerald-600"><span>Sefer Hakedişleri:</span><span>+{{ number_format($report['extra_earnings'], 2, ',', '.') }} ₺</span></div>
                @if($extraBonus > 0) <div class="flex justify-between text-sm font-bold text-emerald-600"><span>Ekstra Ödeme:</span><span>+{{ number_format($extraBonus, 2, ',', '.') }} ₺</span></div> @endif
                <div class="h-px bg-slate-100 my-2"></div>
                @if($bank > 0) <div class="flex justify-between text-sm font-bold text-rose-500"><span>Bankaya Yatan:</span><span>-{{ number_format($bank, 2, ',', '.') }} ₺</span></div> @endif
                @if($penalty > 0) <div class="flex justify-between text-sm font-bold text-rose-500"><span>Trafik Cezası:</span><span>-{{ number_format($penalty, 2, ',', '.') }} ₺</span></div> @endif
                @if($advance > 0) <div class="flex justify-between text-sm font-bold text-rose-500"><span>Avans Ödemesi:</span><span>-{{ number_format($advance, 2, ',', '.') }} ₺</span></div> @endif
                @if($deduction > 0) <div class="flex justify-between text-sm font-bold text-rose-500"><span>Diğer Kesintiler:</span><span>-{{ number_format($deduction, 2, ',', '.') }} ₺</span></div> @endif
                <div class="h-px bg-slate-200 mt-4"></div>
                <div class="flex justify-between text-2xl font-black text-slate-900 pt-2"><span>NET ÖDENECEK:</span><span class="text-blue-600">{{ number_format($finalNet, 2, ',', '.') }} ₺</span></div>
            </div>
        </div>

        <div class="mt-16 grid grid-cols-2 gap-12 border-t border-dashed border-slate-200 pt-12">
            <div class="text-center">
                <div class="text-xs font-black text-slate-400 uppercase tracking-widest mb-12 text-left">Teslim Eden (İşveren)</div>
                <div class="h-24 border-b border-slate-200 w-full mb-4"></div>
                <div class="text-xs font-bold text-slate-500">IRMAK TURİZM</div>
            </div>
            <div class="text-center">
                <div class="text-xs font-black text-slate-400 uppercase tracking-widest mb-12 text-left">Teslim Alan (Personel)</div>
                <div class="h-24 border-b border-slate-200 w-full mb-4"></div>
                <div class="text-sm font-black text-slate-900">{{ $driver->full_name }}</div>
                <div class="text-[10px] text-slate-400 mt-1 italic">Yukarıda dökümü yapılan seferleri ve finansal kalemleri inceledim, hakediş tutarımı teslim aldım.</div>
            </div>
        </div>

        <style>
            @media print {
                .no-print { display: none !important; }
                body { background: white !important; }
                .print-area { border: none !important; box-shadow: none !important; padding: 0 !important; }
                .mx-auto { max-width: 100% !important; margin: 0 !important; }
                .print\:table-row { display: table-row !important; }
            }
        </style>
    </div>
</div>
@endsection
