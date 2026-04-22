<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Toplu Personel Maas Dokumu</title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #1e293b; line-height: 1.5; margin: 0; padding: 0; background: white; font-size: 12px; }
        .page-break { page-break-after: always; padding-bottom: 20px; }
        .container { width: 100%; max-width: 180mm; margin: 0 auto; }
        .header { border-bottom: 3px solid #0f172a; padding-bottom: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 900; color: #0f172a; }
        .header p { margin: 0; color: #2563eb; font-weight: bold; text-transform: uppercase; }
        .company-info { text-align: right; }
        .company-name { font-size: 18px; font-weight: 900; color: #0f172a; }
        .info-grid { display: table; width: 100%; border-spacing: 10px; margin: 0 -10px 20px -10px; }
        .info-box { display: table-cell; width: 50%; background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 10px; }
        .label { font-size: 9px; font-weight: 900; color: #64748b; text-transform: uppercase; margin-bottom: 5px; }
        .value { font-size: 16px; font-weight: 900; color: #0f172a; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #0f172a; color: white; padding: 10px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 10px; border: 1px solid #e2e8f0; font-size: 11px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-black { font-weight: 900; }
        .summary-grid { display: table; width: 100%; margin-top: 20px; }
        .notes-area { display: table-cell; width: 60%; vertical-align: top; }
        .total-box { display: table-cell; width: 40%; background: #0f172a; color: white; padding: 20px; border-radius: 15px; }
        .summary-row { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 5px; color: #94a3b8; }
        .net-row { border-top: 1px solid #334155; margin-top: 10px; padding-top: 10px; display: flex; justify-content: space-between; align-items: center; }
        .net-label { font-size: 12px; font-weight: 900; color: #60a5fa; }
        .net-value { font-size: 24px; font-weight: 900; }
        .signatures { margin-top: 50px; display: table; width: 100%; }
        .sig-box { display: table-cell; width: 50%; text-align: center; }
        .sig-line { border-top: 1px solid #0f172a; width: 80%; margin: 60px auto 10px auto; }
        .sig-label { font-size: 10px; font-weight: 900; text-transform: uppercase; }
    </style>
</head>
<body onload="window.print()">
    @foreach($reports as $index => $data)
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

        <div class="container {{ $index < count($reports) - 1 ? 'page-break' : '' }}">
            <div class="header">
                <div>
                    <h1>MAAŞ DÖKÜMÜ</h1>
                    <p>{{ \Carbon\Carbon::parse($period)->translatedFormat('F Y') }} DÖNEMİ</p>
                </div>
                <div class="company-info">
                    <div class="company-name">IRMAK TURİZM</div>
                    <div style="font-size: 10px; font-weight: bold; color: #94a3b8; letter-spacing: 2px;">SERVISPILOT PRO</div>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-box">
                    <div class="label">Personel Bilgileri</div>
                    <div class="value">{{ $driver->full_name }}</div>
                    <div style="font-size: 10px; color: #64748b; font-weight: bold;">TC: {{ $driver->tc_no ?? '-----------' }}</div>
                </div>
                <div class="info-box" style="text-align: right;">
                    <div class="label">Aylık Ana Maaş</div>
                    <div class="value" style="font-size: 22px;">{{ number_format($report['base_salary'], 2, ',', '.') }} ₺</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Müşteri / Güzergah Bilgisi</th>
                        <th class="text-center w-20">Sabah</th>
                        <th class="text-center w-20">Akşam</th>
                        <th class="text-right w-32">Tutar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['details'] as $summary)
                        <tr>
                            <td>
                                <div class="font-black" style="text-transform: uppercase;">{{ $summary['customer_name'] }}</div>
                                <div style="color: #2563eb; font-weight: bold;">{{ $summary['route_name'] }}</div>
                            </td>
                            <td class="text-center font-black">{{ $summary['morning_count'] }}</td>
                            <td class="text-center font-black">{{ $summary['evening_count'] }}</td>
                            <td class="text-right font-black">{{ number_format($summary['total_fee'], 2, ',', '.') }} ₺</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="summary-grid">
                <div class="notes-area">
                    <div class="label">Ek Ödeme & Kesinti Notları</div>
                    <div style="font-size: 10px; margin-top: 5px;">
                        @if($extraBonus > 0) <div style="color: #059669; font-weight: bold;">+ Ekstra: {{ number_format($extraBonus, 2, ',', '.') }} ₺ ({{ $extraNotes ?: '---' }})</div> @endif
                        @if($deduction > 0) <div style="color: #dc2626; font-weight: bold;">- Kesinti/İcra: {{ number_format($deduction, 2, ',', '.') }} ₺ ({{ $deductionNotes ?: '---' }})</div> @endif
                    </div>
                </div>
                <div class="total-box">
                    <div class="summary-row"><span>Brüt Hakediş:</span><span>+{{ number_format($report['base_salary'] + $report['extra_earnings'] + $extraBonus, 2, ',', '.') }} ₺</span></div>
                    <div class="summary-row" style="color: #fca5a5;"><span>Kesintiler:</span><span>-{{ number_format($bank + $penalty + $advance + $deduction, 2, ',', '.') }} ₺</span></div>
                    <div class="net-row">
                        <span class="net-label">NET ÖDENEN</span>
                        <span class="net-value">{{ number_format($finalNet, 2, ',', '.') }} ₺</span>
                    </div>
                </div>
            </div>

            <div class="signatures">
                <div class="sig-box"><div class="sig-line"></div><div class="sig-label">İşveren</div></div>
                <div class="sig-box"><div class="sig-line"></div><div class="sig-label">Personel: {{ $driver->full_name }}</div></div>
            </div>
        </div>
    @endforeach
</body>
</html>
