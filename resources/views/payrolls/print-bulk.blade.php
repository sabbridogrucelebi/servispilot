<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Toplu Resmi Maas Dokumu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        @page { size: A4; margin: 0; }
        body { font-family: 'Inter', sans-serif; color: #0f172a; line-height: 1.5; margin: 0; padding: 0; background: #ffffff; font-size: 11px; -webkit-print-color-adjust: exact; }
        .page-break { page-break-after: always; }
        .a4-wrapper { width: 100%; max-width: 190mm; margin: 0 auto; padding: 15mm; position: relative; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 80px; font-weight: 900; color: rgba(241, 245, 249, 0.4); z-index: -1; font-family: 'Outfit'; pointer-events: none; text-transform: uppercase; white-space: nowrap; }
        .header-main { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 5px solid #0f172a; padding-bottom: 15px; }
        .doc-title-group h1 { font-family: 'Outfit', sans-serif; margin: 0; font-size: 28px; font-weight: 900; color: #0f172a; }
        .doc-title-group p { margin: 5px 0 0 0; font-size: 12px; font-weight: 700; color: #2563eb; letter-spacing: 2px; text-transform: uppercase; }
        .stat-card { flex: 1; background: #ffffff; border: 1px solid #e2e8f0; padding: 15px; border-radius: 16px; position: relative; overflow: hidden; }
        .stat-card::before { content: ''; position: absolute; left: 0; top: 0; width: 4px; height: 100%; background: #2563eb; }
        .table-container { background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0f172a; color: white; padding: 12px 15px; font-size: 9px; font-weight: 800; text-transform: uppercase; text-align: left; }
        td { padding: 10px 15px; border-bottom: 1px solid #f1f5f9; }
        .total-card-eco { flex: 1; background: #ffffff; border: 2px solid #0f172a; padding: 20px; border-radius: 28px; }
        .summary-row-eco { display: flex; justify-content: space-between; font-size: 10px; margin-bottom: 4px; color: #64748b; font-weight: 600; }
        .total-brut-eco { display: flex; justify-content: space-between; font-size: 11px; margin: 6px 0; padding: 6px 0; border-top: 1px dashed #e2e8f0; border-bottom: 1px dashed #e2e8f0; font-weight: 900; color: #0f172a; }
        .net-total-eco { margin-top: 10px; display: flex; justify-content: space-between; align-items: center; border-top: 2px solid #0f172a; padding-top: 10px; }
        .net-amount-eco { font-family: 'Outfit', sans-serif; font-size: 28px; font-weight: 800; color: #0f172a; white-space: nowrap; }
        .signature-grid { margin-top: 80px; display: flex; gap: 80px; }
        .signature-line { border-top: 2px solid #0f172a; margin-bottom: 10px; padding-top: 8px; }
    </style>
</head>
<body onload="window.print()">
    @foreach($reports as $index => $data)
        @php
            $driver = $data['driver']; $report = $data['report'];
            $ex = \App\Models\Payroll::where('driver_id', $driver->id)->where('period_month', $period)->first();
            $bank = $ex ? (float)$ex->bank_payment : 0; $penalty = $ex ? (float)$ex->traffic_penalty : 0; $advance = $ex ? (float)$ex->advance_payment : 0; $deduction = $ex ? (float)$ex->deduction : 0;
            $extraBonus = $ex ? (float)$ex->extra_bonus : 0;
            $finalNet = ($report['base_salary'] + $report['extra_earnings'] + $extraBonus) - ($bank + $penalty + $advance + $deduction);
        @endphp

        <div class="a4-wrapper {{ $index < count($reports) - 1 ? 'page-break' : '' }}">
            <div class="watermark">IRMAK TURİZM</div>
            <div class="header-main">
                <div class="doc-title-group"><h1>HAKEDİŞ DETAYI</h1><p>{{ \Carbon\Carbon::parse($period)->translatedFormat('F Y') }} DÖNEMİ</p></div>
                <div class="brand-group text-right"><div style="font-family: 'Outfit'; font-size: 20px; font-weight: 900;">IRMAK TURİZM</div><div style="font-size: 9px; font-weight: 800; color: #94a3b8; letter-spacing: 2px;">SERVISPILOT PRO</div></div>
            </div>

            <div style="display: flex; gap: 20px; margin-bottom: 25px;">
                <div class="stat-card"><div style="font-size: 8px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px;">PERSONEL</div><div style="font-family: 'Outfit'; font-size: 16px; font-weight: 800;">{{ $driver->full_name }}</div></div>
                <div class="stat-card" style="text-align: right;"><div style="font-size: 8px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px;">ANA MAAŞ</div><div style="font-family: 'Outfit'; font-size: 22px; font-weight: 800;">{{ number_format($report['base_salary'], 2, ',', '.') }} ₺</div></div>
            </div>

            <div class="table-container">
                <table>
                    <thead><tr><th>GÜZERGAH DETAYI</th><th style="text-align: center;">SABAH</th><th style="text-align: center;">AKŞAM</th><th style="text-align: right;">HAKEDİŞ</th></tr></thead>
                    <tbody>
                        @foreach($report['details'] as $summary)
                            <tr>
                                <td><div style="font-size: 8px; color: #64748b;">{{ $summary['customer_name'] }}</div><div style="font-weight: 800; color: #2563eb;">{{ $summary['route_name'] }}</div></td>
                                <td style="text-align: center; font-weight: 800;">{{ $summary['morning_count'] }}</td>
                                <td style="text-align: center; font-weight: 800;">{{ $summary['evening_count'] }}</td>
                                <td style="text-align: right; font-weight: 900;">{{ number_format($summary['total_fee'], 2, ',', '.') }} ₺</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="display: flex; gap: 30px; align-items: stretch;">
                <div style="flex: 1.2;"><div style="font-size: 9px; font-weight: 900; color: #0f172a; margin-bottom: 8px;">DÜZELTMELER ANALİZİ</div>@if($extraBonus > 0) <div style="font-weight: 700; color: #059669; font-size: 10px;">+ EKSTRA: {{ number_format($extraBonus, 2, ',', '.') }} ₺</div> @endif @if($deduction > 0) <div style="font-weight: 700; color: #dc2626; font-size: 10px;">- KESİNTİ: {{ number_format($deduction, 2, ',', '.') }} ₺</div> @endif</div>
                <div class="total-card-eco">
                    <div class="summary-row-eco"><span>Maaş + Seferler:</span><span>+{{ number_format($report['base_salary'] + $report['extra_earnings'], 2, ',', '.') }} ₺</span></div>
                    <div class="summary-row-eco" style="color: #059669;"><span>Ekstra (+):</span><span>+{{ number_format($extraBonus, 2, ',', '.') }} ₺</span></div>
                    <div class="total-brut-eco"><span>TOPLAM BRÜT:</span><span>{{ number_format($report['base_salary'] + $report['extra_earnings'] + $extraBonus, 2, ',', '.') }} ₺</span></div>
                    <div class="summary-row-eco"><span>Banka/Avans/Ceza:</span><span>-{{ number_format($bank + $advance + $penalty + $deduction, 2, ',', '.') }} ₺</span></div>
                    <div class="net-total-eco"><span style="font-family: 'Outfit'; font-size: 11px; font-weight: 900; color: #2563eb;">NET ÖDENEN</span><span class="net-amount-eco">{{ number_format($finalNet, 2, ',', '.') }} ₺</span></div>
                </div>
            </div>

            <div class="signature-grid">
                <div class="signature-item" style="flex:1; text-align:center;"><div class="signature-line"></div><div style="font-size: 9px; font-weight: 900;">İŞVEREN</div></div>
                <div class="signature-item" style="flex:1; text-align:center;"><div class="signature-line"></div><div style="font-size: 9px; font-weight: 900;">PERSONEL: {{ $driver->full_name }}</div></div>
            </div>
        </div>
    @endforeach
</body>
</html>
