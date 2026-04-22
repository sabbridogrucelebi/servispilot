<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Resmi Maas Dokumu - {{ $driver->full_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        @page { size: A4; margin: 10mm; }
        body { 
            font-family: 'Inter', sans-serif; 
            color: #0f172a; 
            line-height: 1.5; 
            margin: 0; 
            padding: 0; 
            background: #ffffff; 
            font-size: 11px;
            -webkit-print-color-adjust: exact;
        }
        .a4-wrapper { width: 100%; max-width: 190mm; margin: 0 auto; padding: 5mm; position: relative; }
        
        /* Decorative Background Elements */
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 80px; font-weight: 900; color: rgba(241, 245, 249, 0.5); z-index: -1; font-family: 'Outfit'; pointer-events: none; text-transform: uppercase; white-space: nowrap; }

        /* Ultra Pro Header */
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
        .doc-id { display: inline-block; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 8px; font-weight: 800; color: #475569; margin-top: 8px; border: 1px solid #e2e8f0; }

        /* Elegant Grid */
        .top-stats { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-card { 
            flex: 1; 
            background: #ffffff; 
            border: 1px solid #e2e8f0; 
            padding: 18px; 
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .stat-card::before { content: ''; position: absolute; left: 0; top: 0; width: 4px; height: 100%; background: #2563eb; }
        .stat-label { font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px; }
        .stat-value { font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 800; color: #0f172a; }
        .stat-amount { font-size: 26px; color: #0f172a; }

        /* Premium Table Design */
        .table-container { background: #ffffff; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 30px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0f172a; color: white; padding: 15px 20px; font-size: 10px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase; text-align: left; }
        td { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        
        .customer-lbl { font-size: 9px; font-weight: 800; color: #64748b; margin-bottom: 3px; }
        .route-lbl { font-size: 13px; font-weight: 800; color: #2563eb; }
        .trip-count { font-size: 14px; font-weight: 900; color: #0f172a; font-family: 'Outfit'; }

        /* Pro Financial Section */
        .bottom-section { display: flex; gap: 30px; align-items: stretch; }
        .notes-column { flex: 1.3; }
        .notes-header { font-size: 10px; font-weight: 900; color: #0f172a; margin-bottom: 15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; letter-spacing: 1px; }
        
        .note-item { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; background: #f8fafc; padding: 10px 15px; border-radius: 12px; border-left: 4px solid #cbd5e1; }
        .note-item.extra { border-left-color: #10b981; background: #f0fdf4; }
        .note-item.deduction { border-left-color: #ef4444; background: #fef2f2; }

        .total-card-pro { 
            flex: 1; 
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); 
            color: white; 
            padding: 30px; 
            border-radius: 32px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .summary-row-pro { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 10px; color: #94a3b8; font-weight: 600; }
        .net-total-pro { border-top: 1px solid rgba(255,255,255,0.1); margin-top: 20px; padding-top: 20px; }
        .net-label-pro { font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 800; color: #60a5fa; letter-spacing: 2px; margin-bottom: 5px; display: block; }
        .net-val-pro { font-family: 'Outfit', sans-serif; font-size: 38px; font-weight: 900; line-height: 1; display: block; }

        /* Pro Signatures */
        .signature-grid { margin-top: 80px; display: flex; gap: 60px; }
        .signature-item { flex: 1; }
        .signature-line { border-top: 2px solid #0f172a; margin-bottom: 10px; padding-top: 10px; }
        .signature-tag { font-size: 9px; font-weight: 900; color: #94a3b8; letter-spacing: 2px; margin-bottom: 5px; }
        .signature-name { font-family: 'Outfit', sans-serif; font-size: 15px; font-weight: 900; color: #0f172a; }

        /* Security Verification */
        .security-foot { margin-top: 50px; display: flex; justify-content: space-between; align-items: flex-end; border-top: 1px solid #f1f5f9; padding-top: 20px; }
        .verify-text { font-size: 8px; color: #94a3b8; font-weight: 600; max-width: 300px; }
        .qr-placeholder { width: 45px; height: 45px; background: #0f172a; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 8px; font-weight: 900; text-align: center; line-height: 1; padding: 5px; }
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
        $docId = 'SP-' . strtoupper(substr(md5($driver->id . $period), 0, 8));
    @endphp

    <div class="a4-wrapper">
        <div class="watermark">IRMAK TURİZM</div>

        <!-- Master Header -->
        <div class="header-main">
            <div class="doc-title-group">
                <h1>HAKEDİŞ DETAYI</h1>
                <p>{{ \Carbon\Carbon::parse($period)->translatedFormat('F Y') }} DÖNEMİ</p>
            </div>
            <div class="brand-group">
                <div class="brand-name">IRMAK TURİZM</div>
                <div class="brand-sub">SERVISPILOT PRO</div>
                <div class="doc-id">DÖKÜMAN NO: {{ $docId }}</div>
            </div>
        </div>

        <!-- Pro Stat Cards -->
        <div class="top-stats">
            <div class="stat-card">
                <div class="stat-label">PERSONEL KAYITLARI</div>
                <div class="stat-value">{{ $driver->full_name }}</div>
                <div style="font-size: 10px; color: #64748b; font-weight: 700; margin-top: 4px;">TC: {{ $driver->tc_no ?? '-----------' }}</div>
            </div>
            <div class="stat-card" style="text-align: right;">
                <div class="stat-label">AYLIK BAZ MAAŞ</div>
                <div class="stat-value stat-amount">{{ number_format($report['base_salary'], 2, ',', '.') }} ₺</div>
            </div>
        </div>

        <!-- Pro Table Container -->
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
                                <div class="customer-lbl">{{ $summary['customer_name'] }}</div>
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

        <!-- Pro Bottom Section -->
        <div class="bottom-section">
            <div class="notes-column">
                <div class="notes-header">EKSTRA ÖDEME & KESİNTİ ANALİZİ</div>
                
                @if($extraBonus > 0)
                    <div class="note-item extra">
                        <div style="flex: 1;">
                            <div style="font-weight: 900; color: #065f46; font-size: 10px;">EKSTRA PRİM / BONUS</div>
                            <div style="font-size: 10px; color: #059669; font-weight: 600;">{{ $extraNotes ?: 'Hizmet ödülü' }}</div>
                        </div>
                        <div style="font-family: 'Outfit'; font-size: 14px; font-weight: 900; color: #059669;">+{{ number_format($extraBonus, 2, ',', '.') }} ₺</div>
                    </div>
                @endif

                @if($deduction > 0)
                    <div class="note-item deduction">
                        <div style="flex: 1;">
                            <div style="font-weight: 900; color: #991b1b; font-size: 10px;">KESİNTİ / İCRA TAHSİLATI</div>
                            <div style="font-size: 10px; color: #dc2626; font-weight: 600;">{{ $deductionNotes ?: 'Yasal kesinti' }}</div>
                        </div>
                        <div style="font-family: 'Outfit'; font-size: 14px; font-weight: 900; color: #dc2626;">-{{ number_format($deduction, 2, ',', '.') }} ₺</div>
                    </div>
                @endif

                @if($extraBonus == 0 && $deduction == 0)
                    <div style="padding: 20px; border-radius: 15px; border: 2px dashed #f1f5f9; text-align: center; color: #94a3b8; font-weight: 600;">
                        Bu dönem ek ödeme veya kesinti bulunmamaktadır.
                    </div>
                @endif
            </div>

            <div class="total-card-pro">
                <div class="summary-row-pro"><span>Brüt Toplam:</span><span>+{{ number_format($report['base_salary'] + $report['extra_earnings'] + $extraBonus, 2, ',', '.') }} ₺</span></div>
                <div class="summary-row-pro"><span>Toplam Mahsup:</span><span style="color: #fca5a5;">-{{ number_format($bank + $penalty + $advance + $deduction, 2, ',', '.') }} ₺</span></div>
                <div class="net-total-pro">
                    <span class="net-label-pro">NET ÖDENECEK TUTAR</span>
                    <span class="net-val-pro">{{ number_format($finalNet, 2, ',', '.') }} ₺</span>
                </div>
            </div>
        </div>

        <!-- Pro Signatures -->
        <div class="signature-grid">
            <div class="signature-item">
                <div class="signature-tag">YETKİLİ ONAYI</div>
                <div class="signature-line"></div>
                <div class="signature-name">IRMAK TURİZM TAŞIMACILIK</div>
            </div>
            <div class="signature-item">
                <div class="signature-tag">PERSONEL İMZA</div>
                <div class="signature-line"></div>
                <div class="signature-name">{{ $driver->full_name }}</div>
            </div>
        </div>

        <!-- Security Verification -->
        <div class="security-foot">
            <div class="verify-text">
                Bu döküman dijital olarak doğrulanmıştır. Belge üzerindeki veriler ServisPilot PRO veritabanı ile eşleşmektedir. İzinsiz kopyalanamaz veya değiştirilemez. 
                <br>Oluşturma: {{ now()->format('d.m.Y H:i:s') }}
            </div>
            <div class="qr-placeholder">
                SP PRO<br>VERIFY
            </div>
        </div>
    </div>
</body>
</html>
