<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Personel Maas Dokumu - {{ $driver->full_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        @page { size: A4; margin: 12mm; }
        body { 
            font-family: 'Inter', sans-serif; 
            color: #1e293b; 
            line-height: 1.4; 
            margin: 0; 
            padding: 0; 
            background: white; 
            font-size: 11px;
            -webkit-print-color-adjust: exact;
        }
        .container { width: 100%; max-width: 185mm; margin: 0 auto; }
        
        /* Premium Header */
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start; 
            border-bottom: 4px solid #0f172a;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 { 
            font-family: 'Outfit', sans-serif;
            margin: 0; 
            font-size: 28px; 
            font-weight: 900; 
            color: #0f172a; 
            letter-spacing: -1px;
            line-height: 1;
        }
        .header p { 
            margin: 5px 0 0 0; 
            color: #2563eb; 
            font-weight: 800; 
            font-size: 13px;
            letter-spacing: 1px;
            text-transform: uppercase; 
        }
        .company-info { text-align: right; }
        .company-name { 
            font-family: 'Outfit', sans-serif;
            font-size: 20px; 
            font-weight: 900; 
            color: #0f172a; 
            letter-spacing: -0.5px;
        }
        .system-tag { 
            display: inline-block;
            background: #0f172a;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 900;
            margin-top: 4px;
            letter-spacing: 1px;
        }

        /* Aesthetic Info Grid */
        .info-grid { display: flex; gap: 15px; margin-bottom: 25px; }
        .info-card { 
            flex: 1; 
            background: #f8fafc; 
            border: 1px solid #e2e8f0; 
            padding: 15px; 
            border-radius: 16px;
        }
        .label { 
            font-size: 9px; 
            font-weight: 800; 
            color: #64748b; 
            text-transform: uppercase; 
            letter-spacing: 1.5px;
            margin-bottom: 6px; 
        }
        .value { 
            font-family: 'Outfit', sans-serif;
            font-size: 16px; 
            font-weight: 800; 
            color: #0f172a; 
        }
        .amount-lg { font-size: 24px; color: #0f172a; }

        /* Modern Table */
        table { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 25px; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; }
        th { background: #0f172a; color: white; padding: 12px 15px; text-align: left; font-size: 10px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
        td { padding: 12px 15px; border-bottom: 1px solid #f1f5f9; font-size: 11px; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        
        .route-name { font-weight: 700; color: #2563eb; font-size: 12px; }
        .customer-name { font-weight: 800; color: #0f172a; font-size: 10px; text-transform: uppercase; margin-bottom: 2px; }
        
        /* Financial Summary Box */
        .financials { display: flex; gap: 20px; margin-top: 30px; }
        .notes-column { flex: 1.2; }
        .total-card { 
            flex: 1; 
            background: #0f172a; 
            color: white; 
            padding: 25px; 
            border-radius: 24px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .summary-line { 
            display: flex; 
            justify-content: space-between; 
            font-size: 11px; 
            margin-bottom: 8px; 
            color: #94a3b8; 
            font-weight: 600;
        }
        .net-total { 
            border-top: 1px solid rgba(255,255,255,0.1); 
            margin-top: 15px; 
            padding-top: 15px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .net-label { font-family: 'Outfit', sans-serif; font-size: 12px; font-weight: 800; color: #60a5fa; letter-spacing: 1px; }
        .net-amount { font-family: 'Outfit', sans-serif; font-size: 32px; font-weight: 900; }

        /* Elegant Signatures */
        .signatures { margin-top: 60px; display: flex; gap: 50px; }
        .sig-space { 
            flex: 1; 
            text-align: center; 
            border-top: 2px solid #0f172a; 
            padding-top: 10px; 
        }
        .sig-title { font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; color: #64748b; }
        .sig-name { font-size: 13px; font-weight: 800; color: #0f172a; margin-top: 5px; }

        @media print {
            body { -webkit-print-color-adjust: exact; }
            .container { margin: 0; width: 100%; }
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

    <div class="container">
        <!-- Aesthetic Header -->
        <div class="header">
            <div>
                <h1>PERSONEL HAKEDİŞ</h1>
                <p>{{ \Carbon\Carbon::parse($period)->translatedFormat('F Y') }} DÖNEMİ</p>
            </div>
            <div class="company-info">
                <div class="company-name">IRMAK TURİZM</div>
                <div class="system-tag">SERVISPILOT PRO</div>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="info-grid">
            <div class="info-card">
                <div class="label">PERSONEL BİLGİLERİ</div>
                <div class="value">{{ $driver->full_name }}</div>
                <div style="font-size: 10px; color: #64748b; font-weight: 600; margin-top: 2px;">TC No: {{ $driver->tc_no ?? '-----------' }}</div>
            </div>
            <div class="info-card" style="text-align: right;">
                <div class="label">AYLIK ANA MAAŞ</div>
                <div class="value amount-lg">{{ number_format($report['base_salary'], 2, ',', '.') }} ₺</div>
            </div>
        </div>

        <!-- Styled Table -->
        <table>
            <thead>
                <tr>
                    <th>Müşteri & Güzergah Detayı</th>
                    <th style="text-align: center; width: 80px;">SABAH</th>
                    <th style="text-align: center; width: 80px;">AKŞAM</th>
                    <th style="text-align: right; width: 120px;">TOPLAM</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['details'] as $summary)
                    <tr>
                        <td>
                            <div class="customer-name">{{ $summary['customer_name'] }}</div>
                            <div class="route-name">{{ $summary['route_name'] }}</div>
                        </td>
                        <td style="text-align: center; font-weight: 800; font-size: 12px;">{{ $summary['morning_count'] }}</td>
                        <td style="text-align: center; font-weight: 800; font-size: 12px;">{{ $summary['evening_count'] }}</td>
                        <td style="text-align: right; font-weight: 900; font-size: 12px; color: #0f172a;">{{ number_format($summary['total_fee'], 2, ',', '.') }} ₺</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Aesthetic Financials -->
        <div class="financials">
            <div class="notes-column">
                <div class="label" style="margin-bottom: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">Ek Ödeme & Kesinti Detayları</div>
                <div style="padding-left: 5px;">
                    @if($extraBonus > 0) 
                        <div style="margin-bottom: 8px;">
                            <span style="background: #ecfdf5; color: #059669; padding: 2px 6px; border-radius: 4px; font-weight: 800; font-size: 9px; margin-right: 5px;">EKSTRA</span>
                            <span style="font-weight: 700; color: #10b981;">+{{ number_format($extraBonus, 2, ',', '.') }} ₺</span>
                            <span style="color: #64748b; font-size: 10px; margin-left: 5px;">({{ $extraNotes ?: 'Bonus' }})</span>
                        </div>
                    @endif
                    @if($deduction > 0) 
                        <div style="margin-bottom: 8px;">
                            <span style="background: #fef2f2; color: #dc2626; padding: 2px 6px; border-radius: 4px; font-weight: 800; font-size: 9px; margin-right: 5px;">KESİNTİ</span>
                            <span style="font-weight: 700; color: #ef4444;">-{{ number_format($deduction, 2, ',', '.') }} ₺</span>
                            <span style="color: #64748b; font-size: 10px; margin-left: 5px;">({{ $deductionNotes ?: 'İcra/Kesinti' }})</span>
                        </div>
                    @endif
                    @if($extraBonus == 0 && $deduction == 0)
                        <div style="color: #94a3b8; font-style: italic; font-size: 10px;">Bu dönem için herhangi bir ek ödeme veya kesinti kaydı bulunmamaktadır.</div>
                    @endif
                </div>
            </div>
            
            <div class="total-card">
                <div class="summary-line">
                    <span>Toplam Hakediş</span>
                    <span style="color: white;">+{{ number_format($report['base_salary'] + $report['extra_earnings'] + $extraBonus, 2, ',', '.') }} ₺</span>
                </div>
                <div class="summary-line">
                    <span>Toplam Kesintiler</span>
                    <span style="color: #fca5a5;">-{{ number_format($bank + $penalty + $advance + $deduction, 2, ',', '.') }} ₺</span>
                </div>
                <div class="net-total">
                    <span class="net-label">NET ÖDENECEK</span>
                    <span class="net-amount">{{ number_format($finalNet, 2, ',', '.') }} ₺</span>
                </div>
            </div>
        </div>

        <!-- Elegant Signatures -->
        <div class="signatures">
            <div class="sig-space">
                <div class="sig-title">İşveren Tasdik</div>
                <div class="sig-name">IRMAK TURİZM</div>
            </div>
            <div class="sig-space">
                <div class="sig-title">Personel Tebellüğ</div>
                <div class="sig-name">{{ $driver->full_name }}</div>
            </div>
        </div>

        <div style="margin-top: 50px; text-align: center; color: #94a3b8; font-size: 9px; font-weight: 500; letter-spacing: 0.5px;">
            Bu belge güvenli ServisPilot PRO dijital altyapısı ile {{ now()->format('d.m.Y H:i') }} tarihinde oluşturulmuştur.
        </div>
    </div>
</body>
</html>
