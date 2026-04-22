@extends('layouts.app')

@section('title', 'Toplu Personel Maaş Dökümü')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <!-- Üst Bar -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('payrolls.index', ['period' => $period]) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-blue-600 transition">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Listeye Geri Dön
        </a>
        
        <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-6 py-3 text-sm font-black text-white shadow-lg transition-all hover:bg-slate-800">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            TÜMÜNÜ YAZDIR / PDF KAYDET
        </button>
    </div>

    @foreach($reports as $data)
        @php
            $driver = $data['driver'];
            $report = $data['report'];
            
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

        <!-- Hakediş Belgesi -->
        <div class="print-area rounded-[32px] border border-slate-200 bg-white p-10 shadow-sm overflow-hidden relative mb-8" style="page-break-after: always;">
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

            <div class="mt-8">
                <table class="w-full text-left text-sm border border-slate-100 rounded-2xl overflow-hidden">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-4 py-4 font-bold">Müşteri / Güzergah</th>
                            <th class="px-4 py-4 font-bold text-center">Sabah</th>
                            <th class="px-4 py-4 font-bold text-center">Akşam</th>
                            <th class="px-4 py-4 font-bold text-right">Toplam Ücret</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($report['details'] as $routeSummary)
                            <tr>
                                <td class="px-4 py-4">
                                    <div class="font-black text-slate-900 uppercase text-[11px]">{{ $routeSummary['customer_name'] }}</div>
                                    <div class="text-sm font-bold text-blue-600 mt-0.5">{{ $routeSummary['route_name'] }}</div>
                                </td>
                                <td class="px-4 py-4 text-center font-black text-slate-700">{{ $routeSummary['morning_count'] }}</td>
                                <td class="px-4 py-4 text-center font-black text-slate-700">{{ $routeSummary['evening_count'] }}</td>
                                <td class="px-4 py-4 text-right font-black text-slate-900">{{ number_format($routeSummary['total_fee'], 2, ',', '.') }} ₺</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-10 grid grid-cols-2 gap-12">
                <div class="space-y-6">
                    <div>
                        <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3 border-b border-slate-100 pb-2">Ek Ödemeler / Kesintiler</h4>
                        <div class="space-y-2">
                            @if($extraBonus > 0)
                                <div class="text-[11px] font-bold text-emerald-700">Ekstra: +{{ number_format($extraBonus, 2, ',', '.') }} ₺ ({{ $extraNotes }})</div>
                            @endif
                            @if($deduction > 0)
                                <div class="text-[11px] font-bold text-rose-700">Kesinti: -{{ number_format($deduction, 2, ',', '.') }} ₺ ({{ $deductionNotes }})</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm font-bold text-slate-600"><span>Ana Maaş:</span><span>{{ number_format($report['base_salary'], 2, ',', '.') }} ₺</span></div>
                    <div class="flex justify-between text-sm font-bold text-emerald-600"><span>Sefer Hakedişleri:</span><span>+{{ number_format($report['extra_earnings'], 2, ',', '.') }} ₺</span></div>
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
                </div>
            </div>
        </div>
    @endforeach

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; margin: 0 !important; }
            .print-area { border: none !important; box-shadow: none !important; padding: 0 !important; width: 100% !important; margin-bottom: 0 !important; }
            .mx-auto { max-width: 100% !important; margin: 0 !important; }
        }
    </style>
</div>
@endsection
