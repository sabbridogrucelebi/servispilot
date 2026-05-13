<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Resmi Maas Dokumu - {{ $driver->full_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        @page { size: A4; margin: 0; }
        body { 
            font-family: 'Inter', sans-serif; 
            color: #0f172a; 
            line-height: 1.5; 
            margin: 0; 
            padding: 15mm; 
            background: #ffffff; 
            font-size: 11px;
            -webkit-print-color-adjust: exact;
        }
        .a4-wrapper { width: 100%; max-width: 190mm; margin: 0 auto; position: relative; }
        
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 80px; font-weight: 900; color: rgba(241, 245, 249, 0.5); z-index: -1; font-family: 'Outfit'; pointer-events: none; text-transform: uppercase; white-space: nowrap; }

        .header-main { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px;
            border-bottom: 5px solid #0f172a;
            padding-bottom: 20px;
        }
        .doc-title-group h1 { 
            font-family: 'Outfit', sans-serif;
            margin: 0; font-size: 32px; font-weight: 900; 
            letter-spacing: -1.5px; line-height: 0.9;
            color: #0f172a;
        }
        .doc-title-group p { 
            margin: 8px 0 0 0; font-size: 14px; font-weight: 700; 
            color: #2563eb; letter-spacing: 2px; text-transform: uppercase; 
        }
        
        .brand-group { text-align: right; }
        .brand-name { font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 900; color: #0f172a; line-height: 1; }
        .brand-sub { font-size: 10px; font-weight: 800; color: #94a3b8; letter-spacing: 3px; margin-top: 5px; }

        .top-stats { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-card { 
            flex: 1; 
            background: #ffffff; 
            border: 1px solid #e2e8f0; 
            padding: 18px; 
            border-radius: 20px;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before { content: ''; position: absolute; left: 0; top: 0; width: 4px; height: 100%; background: #2563eb; }
        .stat-label { font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px; }
        .stat-value { font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 800; color: #0f172a; }

        .table-container { background: #ffffff; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0f172a; color: white; padding: 15px 20px; font-size: 10px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase; text-align: left; }
        td { padding: 12px 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        
        .route-lbl { font-size: 13px; font-weight: 800; color: #2563eb; }
        .trip-count { font-size: 14px; font-weight: 900; color: #0f172a; font-family: 'Outfit'; }

        /* Ink-Saving Eco Financial Section */
        .bottom-section { display: flex; gap: 30px; align-items: stretch; }
        .notes-column { flex: 1.3; }
        .total-card-eco { 
            flex: 1; 
            background: #ffffff; 
            border: 2px solid #0f172a;
            padding: 25px; 
            border-radius: 32px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .summary-row-eco { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 5px; color: #64748b; font-weight: 600; }
        .total-brut-eco { display: flex; justify-content: space-between; font-size: 12px; margin: 8px 0; padding: 8px 0; border-top: 1px dashed #e2e8f0; border-bottom: 1px dashed #e2e8f0; font-weight: 900; color: #0f172a; }
        .net-total-eco { margin-top: 15px; display: flex; justify-content: space-between; align-items: center; border-top: 2px solid #0f172a; padding-top: 15px; }
        .net-label-eco { font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 900; color: #2563eb; letter-spacing: 1px; }
        .net-amount-eco { font-family: 'Outfit', sans-serif; font-size: 32px; font-weight: 800; color: #0f172a; white-space: nowrap; }

        .signature-grid { margin-top: 100px; display: flex; gap: 80px; }
        .signature-item { flex: 1; text-align: center; }
        .signature-line { border-top: 2px solid #0f172a; margin-bottom: 15px; padding-top: 10px; }
        .signature-tag { font-size: 10px; font-weight: 900; color: #0f172a; letter-spacing: 2px; text-transform: uppercase; }
        .signature-name { font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 900; color: #0f172a; }

        @media print {
            body { -webkit-print-color-adjust: exact; margin: 0; padding: 15mm; }
        }
    </style>
</head>
<body onload="window.print()">
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

    <div class="a4-wrapper">
        <div class="watermark">{{ auth()->user()->company->name ?? 'FİRMA ADI' }}</div>

        <div class="header-main">
            <div class="doc-title-group">
                <h1>HAKEDİŞ DETAYI</h1>
                <p>{{ \Carbon\Carbon::parse($period)->translatedFormat('F Y') }} DÖNEMİ</p>
            </div>
            <div class="brand-group" style="display: flex; flex-direction: column; align-items: flex-end;">
                @if(auth()->user()->company->logo_path)
                    <img src="{{ Storage::url(auth()->user()->company->logo_path) }}" alt="{{ auth()->user()->company->name }}" style="max-height: 40px; margin-bottom: 4px; object-fit: contain;">
                @else
                    <div class="brand-name">{{ auth()->user()->company->name ?? 'Firma Adı' }}</div>
                @endif
                <div class="brand-sub">PERSONEL HAKEDİŞ DETAYI</div>
            </div>
        </div>

        <div class="top-stats">
            <div class="stat-card">
                <div class="stat-label">PERSONEL KAYITLARI</div>
                <div class="stat-value">{{ $driver->full_name }}</div>
                <div style="font-size: 10px; color: #64748b; font-weight: 700; margin-top: 4px; display: flex; gap: 10px;">
                    <span>TC: {{ $driver->tc_no ?? '-----------' }}</span>
                    @php
                        $start = $driver->start_date ? \Carbon\Carbon::parse($driver->start_date) : null;
                        $leave = $driver->leave_date ? \Carbon\Carbon::parse($driver->leave_date) : null;
                    @endphp
                    @if($start && $start->format('Y-m') === $period)
                        <span style="color: #059669;">• GİRİŞ: {{ $start->format('d.m.Y') }} ({{ $driver->start_shift === 'morning' ? 'SABAH' : 'AKŞAM' }})</span>
                    @endif
                    @if($leave && $leave->format('Y-m') === $period)
                        <span style="color: #dc2626;">• AYRILIŞ: {{ $leave->format('d.m.Y') }} ({{ $driver->leave_shift === 'morning' ? 'SABAH' : 'AKŞAM' }})</span>
                    @endif
                </div>
            </div>
            <div class="stat-card" style="text-align: right;">
                <div class="stat-label">AYLIK BAZ MAAŞ</div>
                <div class="stat-value" style="font-size: 26px;">{{ number_format($report['base_salary'], 2, ',', '.') }} ₺</div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%;">OPERASYONEL GÜZERGAH DETAYI</th>
                        <th style="text-align: center;">SABAH</th>
                        <th style="text-align: center;">AKŞAM</th>
                        <th style="text-align: right;">HAKEDİŞ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['details'] as $summary)
                        <tr>
                            <td>
                                <div style="font-size: 9px; font-weight: 800; color: #64748b; margin-bottom: 2px;">{{ $summary['customer_name'] }}</div>
                                <div class="route-lbl">{{ $summary['route_name'] }}</div>
                            </td>
                            <td style="text-align: center;"><span class="trip-count">{{ $summary['morning_count'] }}</span></td>
                            <td style="text-align: center;"><span class="trip-count">{{ $summary['evening_count'] }}</span></td>
                            <td style="text-align: right;"><span class="trip-count">{{ number_format($summary['total_fee'], 2, ',', '.') }} ₺</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bottom-section">
            <div class="notes-column">
                <div style="font-size: 10px; font-weight: 900; color: #0f172a; margin-bottom: 10px;">EKSTRA ÖDEME & KESİNTİ ANALİZİ</div>
                @if($extraBonus > 0)
                    <div style="margin-bottom: 5px; font-weight: 700; color: #059669;">+ EKSTRA PRİM: {{ number_format($extraBonus, 2, ',', '.') }} ₺ ({{ $extraNotes ?: 'Bonus' }})</div>
                @endif
                @if($deduction > 0)
                    <div style="margin-bottom: 5px; font-weight: 700; color: #dc2626;">- KESİNTİ / İCRA: {{ number_format($deduction, 2, ',', '.') }} ₺ ({{ $deductionNotes ?: 'Yasal' }})</div>
                @endif
                @if($penalty > 0)
                    <div style="margin-bottom: 5px; font-weight: 700; color: #dc2626;">- TRAFİK CEZASI: {{ number_format($penalty, 2, ',', '.') }} ₺</div>
                @endif
            </div>

            <div class="total-card-eco">
                <div class="summary-row-eco">
                    <span>Ana Maaş:</span>
                    <span>+{{ number_format($report['base_salary'], 2, ',', '.') }} ₺</span>
                </div>
                <div class="summary-row-eco">
                    <span>Ek Hakedişler:</span>
                    <span>+{{ number_format($report['extra_earnings'], 2, ',', '.') }} ₺</span>
                </div>
                <div class="summary-row-eco" style="color: #059669;">
                    <span>Ek Ödemeler:</span>
                    <span>+{{ number_format($extraBonus, 2, ',', '.') }} ₺</span>
                </div>
                <div class="total-brut-eco">
                    <span>TOPLAM BRÜT:</span>
                    <span>{{ number_format($report['base_salary'] + $report['extra_earnings'] + $extraBonus, 2, ',', '.') }} ₺</span>
                </div>
                
                <div class="summary-row-eco"><span>Banka Ödemesi:</span><span>-{{ number_format($bank, 2, ',', '.') }} ₺</span></div>
                <div class="summary-row-eco"><span>Trafik Cezası:</span><span>-{{ number_format($penalty, 2, ',', '.') }} ₺</span></div>
                <div class="summary-row-eco"><span>Avans / Ödeme:</span><span>-{{ number_format($advance, 2, ',', '.') }} ₺</span></div>
                <div class="summary-row-eco"><span>Kesinti / İcra:</span><span>-{{ number_format($deduction, 2, ',', '.') }} ₺</span></div>

                <div class="net-total-eco">
                    <span class="net-label-eco">NET ÖDENECEK</span>
                    <span class="net-amount-eco">{{ number_format($finalNet, 2, ',', '.') }} ₺</span>
                </div>
            </div>
        </div>

        <div class="signature-grid">
            <div class="signature-item">
                <div class="signature-line"></div>
                <div class="signature-tag">YETKİLİ ONAYI</div>
                <div class="signature-name">{{ auth()->user()->company->name ?? 'FİRMA YETKİLİSİ' }}</div>
            </div>
            <div class="signature-item">
                <div class="signature-line"></div>
                <div class="signature-tag">PERSONEL İMZA</div>
                <div class="signature-name">{{ $driver->full_name }}</div>
            </div>
        </div>
    </div>
</body>
</html>
