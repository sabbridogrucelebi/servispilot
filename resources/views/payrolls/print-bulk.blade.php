<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Toplu Resmi Maas Dokumu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        @page { size: A4; margin: 10mm; }
        body { font-family: 'Inter', sans-serif; color: #0f172a; line-height: 1.5; margin: 0; padding: 0; background: #ffffff; font-size: 11px; -webkit-print-color-adjust: exact; }
        .page-break { page-break-after: always; }
        .a4-wrapper { width: 100%; max-width: 190mm; margin: 0 auto; padding: 5mm; position: relative; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 80px; font-weight: 900; color: rgba(241, 245, 249, 0.4); z-index: -1; font-family: 'Outfit'; pointer-events: none; text-transform: uppercase; white-space: nowrap; }
        .header-main { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 5px solid #0f172a; padding-bottom: 20px; }
        .doc-title-group h1 { font-family: 'Outfit', sans-serif; margin: 0; font-size: 32px; font-weight: 900; letter-spacing: -1.5px; line-height: 0.9; color: #0f172a; }
        .doc-title-group p { margin: 8px 0 0 0; font-size: 14px; font-weight: 700; color: #2563eb; letter-spacing: 2px; text-transform: uppercase; }
        .brand-name { font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 900; color: #0f172a; line-height: 1; }
        .brand-sub { font-size: 10px; font-weight: 800; color: #94a3b8; letter-spacing: 3px; margin-top: 5px; }
        .stat-card { flex: 1; background: #ffffff; border: 1px solid #e2e8f0; padding: 18px; border-radius: 20px; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .stat-card::before { content: ''; position: absolute; left: 0; top: 0; width: 4px; height: 100%; background: #2563eb; }
        .stat-label { font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px; }
        .stat-value { font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 800; color: #0f172a; }
        .table-container { background: #ffffff; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 30px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0f172a; color: white; padding: 15px 20px; font-size: 10px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase; text-align: left; }
        td { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .trip-count { font-size: 14px; font-weight: 900; color: #0f172a; font-family: 'Outfit'; }
        .total-card-pro { flex: 1; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; padding: 30px; border-radius: 32px; display: flex; flex-direction: column; justify-content: center; }
        .net-val-pro { font-family: 'Outfit', sans-serif; font-size: 38px; font-weight: 900; line-height: 1; display: block; }
        .signature-line { border-top: 2px solid #0f172a; margin-bottom: 10px; padding-top: 10px; }
    </style>
</head>
<body onload="window.print()">
    @foreach($reports as $index => $data)
        @php
            $driver = $data['driver'];
            $report = $data['report'];
            $ex = \App\Models\Payroll::where('driver_id', $driver->id)->where('period_month', $period)->first();
            $bank = $ex ? (float)$ex->bank_payment : 0; $penalty = $ex ? (float)$ex->traffic_penalty : 0; $advance = $ex ? (float)$ex->advance_payment : 0; $deduction = $ex ? (float)$ex->deduction : 0;
            $extraBonus = $ex ? (float)$ex->extra_bonus : 0; $extraNotes = $ex ? $ex->extra_notes : ''; $deductionNotes = $ex ? $ex->deduction_notes : '';
            $finalNet = ($report['base_salary'] + $report['extra_earnings'] + $extraBonus) - ($bank + $penalty + $advance + $deduction);
            $docId = 'SP-' . strtoupper(substr(md5($driver->id . $period), 0, 8));
        @endphp

        <div class="a4-wrapper {{ $index < count($reports) - 1 ? 'page-break' : '' }}">
            <div class="watermark">IRMAK TURİZM</div>
            <div class="header-main">
                <div class="doc-title-group"><h1>HAKEDİŞ DETAYI</h1><p>{{ \Carbon\Carbon::parse($period)->translatedFormat('F Y') }} DÖNEMİ</p></div>
                <div class="brand-group text-right">
                    <div class="brand-name">IRMAK TURİZM</div><div class="brand-sub">SERVISPILOT PRO</div>
                    <div style="background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 8px; font-weight: 800; color: #475569; margin-top: 8px; border: 1px solid #e2e8f0;">NO: {{ $docId }}</div>
                </div>
            </div>

            <div style="display: flex; gap: 20px; margin-bottom: 30px;">
                <div class="stat-card">
                    <div class="stat-label">PERSONEL KAYITLARI</div><div class="stat-value">{{ $driver->full_name }}</div>
                    <div style="font-size: 10px; color: #64748b; font-weight: 700; margin-top: 4px;">TC: {{ $driver->tc_no ?? '-----------' }}</div>
                </div>
                <div class="stat-card" style="text-align: right;">
                    <div class="stat-label">AYLIK BAZ MAAŞ</div><div class="stat-value" style="font-size: 26px;">{{ number_format($report['base_salary'], 2, ',', '.') }} ₺</div>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead><tr><th>OPERASYONEL GÜZERGAH DETAYI</th><th style="text-align: center;">SABAH</th><th style="text-align: center;">AKŞAM</th><th style="text-align: right;">HAKEDİŞ</th></tr></thead>
                    <tbody>
                        @foreach($report['details'] as $summary)
                            <tr>
                                <td><div style="font-size: 9px; font-weight: 800; color: #64748b; margin-bottom: 3px;">{{ $summary['customer_name'] }}</div><div style="font-size: 13px; font-weight: 800; color: #2563eb;">{{ $summary['route_name'] }}</div></td>
                                <td style="text-align: center;"><span class="trip-count">{{ $summary['morning_count'] }}</span></td>
                                <td style="text-align: center;"><span class="trip-count">{{ $summary['evening_count'] }}</span></td>
                                <td style="text-align: right;"><span class="trip-count">{{ number_format($summary['total_fee'], 2, ',', '.') }} ₺</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="display: flex; gap: 30px; align-items: stretch;">
                <div style="flex: 1.3;">
                    <div style="font-size: 10px; font-weight: 900; color: #0f172a; margin-bottom: 15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; letter-spacing: 1px;">EKSTRA ÖDEME & KESİNTİ ANALİZİ</div>
                    @if($extraBonus > 0) <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; background: #f0fdf4; padding: 10px 15px; border-radius: 12px; border-left: 4px solid #10b981;"><div style="flex: 1;"><div style="font-weight: 900; color: #065f46; font-size: 10px;">EKSTRA PRİM</div><div style="font-size: 10px; color: #059669; font-weight: 600;">{{ $extraNotes ?: 'Bonus' }}</div></div><div style="font-family: 'Outfit'; font-size: 14px; font-weight: 900; color: #059669;">+{{ number_format($extraBonus, 2, ',', '.') }} ₺</div></div> @endif
                    @if($deduction > 0) <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; background: #fef2f2; padding: 10px 15px; border-radius: 12px; border-left: 4px solid #ef4444;"><div style="flex: 1;"><div style="font-weight: 900; color: #991b1b; font-size: 10px;">KESİNTİ / İCRA</div><div style="font-size: 10px; color: #dc2626; font-weight: 600;">{{ $deductionNotes ?: 'Yasal' }}</div></div><div style="font-family: 'Outfit'; font-size: 14px; font-weight: 900; color: #dc2626;">-{{ number_format($deduction, 2, ',', '.') }} ₺</div></div> @endif
                </div>
                <div class="total-card-pro">
                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 10px; color: #94a3b8; font-weight: 600;"><span>Brüt Toplam:</span><span>+{{ number_format($report['base_salary'] + $report['extra_earnings'] + $extraBonus, 2, ',', '.') }} ₺</span></div>
                    <div style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 20px; padding-top: 20px;"><span style="font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 800; color: #60a5fa; letter-spacing: 2px; margin-bottom: 5px; display: block;">NET ÖDENECEK TUTAR</span><span class="net-val-pro">{{ number_format($finalNet, 2, ',', '.') }} ₺</span></div>
                </div>
            </div>

            <div style="margin-top: 80px; display: flex; gap: 60px;">
                <div style="flex: 1;"><div class="signature-line"></div><div style="font-size: 9px; font-weight: 900; color: #94a3b8; letter-spacing: 2px;">YETKİLİ ONAYI</div></div>
                <div style="flex: 1;"><div class="signature-line"></div><div style="font-size: 9px; font-weight: 900; color: #94a3b8; letter-spacing: 2px;">PERSONEL: {{ $driver->full_name }}</div></div>
            </div>
        </div>
    @endforeach
</body>
</html>
