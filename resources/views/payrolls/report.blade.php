@extends('layouts.app')

@section('title', 'Personel Hakediş Detayı')

@section('content')
@php
    $ex = \App\Models\Payroll::where('driver_id', $driver->id)->where('period_month', $period)->first();
    $bank = $ex ? (float)$ex->bank_payment : 0;
    $penalty = $ex ? (float)$ex->traffic_penalty : 0;
    $advance = $ex ? (float)$ex->advance_payment : 0;
    $deduction = $ex ? (float)$ex->deduction : 0;
    $deductionNotes = $ex ? $ex->deduction_notes : '';
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

    <!-- Hakediş Belgesi (PRO Yazdırma Düzeni) -->
    <div class="print-container bg-white shadow-sm overflow-hidden border border-slate-200 rounded-[32px] p-6 md:p-12">
        <!-- Logo ve Başlık -->
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight">PERSONEL MAAŞ DÖKÜMÜ</h1>
                <p class="text-sm font-bold text-blue-600 uppercase">{{ \Carbon\Carbon::parse($period)->translatedFormat('F Y') }} DÖNEMİ HAKEDİŞİ</p>
            </div>
            <div class="text-right">
                <div class="text-xl font-black text-slate-900">ServisPilot <span class="text-blue-600">PRO</span></div>
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">IRMAK TURİZM TAŞIMACILIK</div>
            </div>
        </div>

        <!-- Personel & Maaş Özeti -->
        <div class="mt-6 grid grid-cols-2 gap-4">
            <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/50">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">PERSONEL BİLGİLERİ</div>
                <div class="text-lg font-black text-slate-900 uppercase">{{ $driver->full_name }}</div>
                <div class="text-xs font-bold text-slate-500">TC: {{ $driver->tc_no ?? '-----------' }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/50 text-right">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">AYLIK ANA MAAŞ</div>
                <div class="text-2xl font-black text-slate-900">{{ number_format($report['base_salary'], 2, ',', '.') }} ₺</div>
            </div>
        </div>

        <!-- Sefer Hakediş Tablosu -->
        <div class="mt-6">
            <div class="text-[10px] font-black text-slate-900 uppercase tracking-widest mb-3 flex items-center gap-2">
                <div class="h-1.5 w-1.5 rounded-full bg-blue-600"></div>
                AYLIK EK SEFER HAKEDİŞ DETAYLARI
            </div>
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white">
                        <th class="p-3 text-left border border-slate-900">MÜŞTERİ / GÜZERGAH ADI</th>
                        <th class="p-3 text-center border border-slate-900 w-20">SABAH</th>
                        <th class="p-3 text-center border border-slate-900 w-20">AKŞAM</th>
                        <th class="p-3 text-right border border-slate-900 w-32">TOPLAM TUTAR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($report['details'] as $index => $routeSummary)
                        <tr>
                            <td class="p-3 border border-slate-200">
                                <div class="font-black text-slate-900 uppercase text-[10px]">{{ $routeSummary['customer_name'] }}</div>
                                <div class="font-bold text-blue-600">{{ $routeSummary['route_name'] }}</div>
                            </td>
                            <td class="p-3 text-center border border-slate-200 font-black text-slate-700">{{ $routeSummary['morning_count'] }}</td>
                            <td class="p-3 text-center border border-slate-200 font-black text-slate-700">{{ $routeSummary['evening_count'] }}</td>
                            <td class="p-3 text-right border border-slate-200 font-black text-slate-900">{{ number_format($routeSummary['total_fee'], 2, ',', '.') }} ₺</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Ödemeler ve Kesintiler -->
        <div class="mt-6 grid grid-cols-2 gap-8">
            <div class="space-y-4">
                <div>
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 border-b border-slate-100 pb-1">EK ÖDEME & NOTLAR</div>
                    @if($extraBonus > 0)
                        <div class="text-xs font-bold text-emerald-700">+{{ number_format($extraBonus, 2, ',', '.') }} ₺ ({{ $extraNotes ?: 'Ekstra Ödeme' }})</div>
                    @endif
                    @if($deduction > 0)
                        <div class="text-xs font-bold text-rose-700">-{{ number_format($deduction, 2, ',', '.') }} ₺ ({{ $deductionNotes ?: 'Kesinti/İcra' }})</div>
                    @endif
                    @if($extraBonus == 0 && $deduction == 0)
                        <div class="text-[10px] text-slate-400 italic">Herhangi bir ek ödeme veya kesinti bulunmamaktadır.</div>
                    @endif
                </div>
            </div>

            <div class="bg-slate-900 rounded-2xl p-6 text-white space-y-2">
                <div class="flex justify-between text-[10px] font-bold text-white/50 uppercase"><span>Hakediş Toplamı:</span><span>+{{ number_format($report['base_salary'] + $report['extra_earnings'] + $extraBonus, 2, ',', '.') }} ₺</span></div>
                <div class="flex justify-between text-[10px] font-bold text-rose-400 uppercase"><span>Kesintiler Toplamı:</span><span>-{{ number_format($bank + $penalty + $advance + $deduction, 2, ',', '.') }} ₺</span></div>
                <div class="pt-2 mt-2 border-t border-white/10 flex justify-between items-center">
                    <span class="text-xs font-black uppercase tracking-widest text-blue-400">NET ÖDENECEK:</span>
                    <span class="text-2xl font-black">{{ number_format($finalNet, 2, ',', '.') }} ₺</span>
                </div>
            </div>
        </div>

        <!-- İmza Alanı -->
        <div class="mt-12 grid grid-cols-2 gap-20">
            <div class="text-center">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-16 text-left">TESLİM EDEN (İŞVEREN)</div>
                <div class="border-b border-slate-900 w-full mb-2"></div>
                <div class="text-[10px] font-black text-slate-900">IRMAK TURİZM TAŞIMACILIK</div>
            </div>
            <div class="text-center">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-16 text-left">TESLİM ALAN (PERSONEL)</div>
                <div class="border-b border-slate-900 w-full mb-2"></div>
                <div class="text-[10px] font-black text-slate-900 uppercase">{{ $driver->full_name }}</div>
            </div>
        </div>

        <div class="mt-8 text-center border-t border-slate-100 pt-4">
            <p class="text-[8px] text-slate-400 italic uppercase">Bu belge ServisPilot PRO sistemi tarafından {{ now()->format('d.m.Y H:i') }} tarihinde oluşturulmuştur.</p>
        </div>
    </div>

    <style>
        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                border: none !important;
                box-shadow: none !important;
                width: 210mm !important;
                height: 297mm !important;
                margin: 0 auto !important;
                padding: 15mm !important;
                border-radius: 0 !important;
            }
            .bg-slate-50\/50 { background-color: #f8fafc !important; -webkit-print-color-adjust: exact; }
            .bg-slate-900 { background-color: #0f172a !important; -webkit-print-color-adjust: exact; }
            .text-white { color: white !important; }
            .text-blue-600 { color: #2563eb !important; }
            .text-rose-400 { color: #fb7185 !important; }
        }
    </style>
</div>
@endsection
