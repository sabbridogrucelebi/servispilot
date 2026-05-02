<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Puantaj Raporu</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 4px 0 0;
            font-size: 10px;
            color: #6b7280;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 3px 2px;
            text-align: center;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
            font-size: 8px;
        }
        .route-name {
            text-align: left;
            font-weight: bold;
            width: 120px;
            font-size: 8px;
            padding-left: 6px;
        }
        .weekend {
            background-color: #fef2f2;
        }
        .holiday {
            background-color: #f5f3ff;
        }
        .total-col {
            background-color: #eff6ff;
            font-weight: bold;
            color: #1d4ed8;
        }
        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .summary-wrapper {
            width: 100%;
        }
        .summary-table {
            width: 250px;
            float: right;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #d1d5db;
            border-radius: 4px;
        }
        .summary-table th, .summary-table td {
            padding: 6px 10px;
            border: none;
            border-bottom: 1px solid #e5e7eb;
        }
        .summary-table tr:last-child th, .summary-table tr:last-child td {
            border-bottom: none;
        }
        .summary-table th {
            text-align: left;
            background-color: #f9fafb;
            color: #4b5563;
            font-size: 9px;
            width: 50%;
        }
        .summary-table td {
            text-align: right;
            font-weight: bold;
            color: #111827;
            font-size: 10px;
            width: 50%;
        }
        .footer {
            position: fixed;
            bottom: -20px;
            left: 0px;
            right: 0px;
            height: 20px;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
        }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $selectedCustomer->company_name ?? 'Puantaj Raporu' }}</h1>
        <p><strong>{{ $monthOptions[$selectedMonth] ?? '' }} {{ $selectedYear }}</strong> Dönemi Servis Kayıtları &bull; <i>FiloMERKEZ Premium PDF Motoru</i></p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="route-name">GÜZERGAH</th>
                @foreach($monthDays as $day)
                    <th class="{{ $day['is_holiday'] ? 'holiday' : ($day['is_weekend'] ? 'weekend' : '') }}">
                        {{ $day['date']->format('d') }}<br>
                        <span style="font-size: 6px; color: #6b7280;">{{ mb_substr($day['day_name'], 0, 3) }}</span>
                    </th>
                @endforeach
                <th class="total-col">TOPLAM</th>
            </tr>
        </thead>
        <tbody>
            @foreach($serviceRoutes as $route)
                @php
                    $routeSum = 0;
                @endphp
                <tr>
                    <td class="route-name">{{ $route->route_name }}</td>
                    @foreach($monthDays as $day)
                        @php
                            $cell = $matrix[$day['date_key']][$route->id] ?? null;
                            $price = $cell['value'] ?? '';
                            if ($price !== '' && $price !== null) $routeSum += (float)$price;
                        @endphp
                        <td class="{{ $day['is_holiday'] ? 'holiday' : ($day['is_weekend'] ? 'weekend' : '') }}">
                            @if($price !== '' && $price !== null)
                                {{ (int)(float)$price }}
                            @endif
                        </td>
                    @endforeach
                    <td class="total-col">{{ $routeSum > 0 ? (int)$routeSum : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-wrapper">
        <table class="summary-table">
            <tr>
                <th>Ara Toplam</th>
                <td>{{ number_format($summary['subtotal'], 2, ',', '.') }} ₺</td>
            </tr>
            <tr>
                <th>KDV (%{{ $summary['vat_rate'] }})</th>
                <td>{{ number_format($summary['vat_amount'], 2, ',', '.') }} ₺</td>
            </tr>
            @if($summary['withholding_amount'] > 0)
            <tr>
                <th>Tevkifat (%{{ $summary['withholding_rate'] }})</th>
                <td>{{ number_format($summary['withholding_amount'], 2, ',', '.') }} ₺</td>
            </tr>
            @endif
            <tr>
                <th style="font-size: 11px; color: #111827; background-color: #f0fdf4;">Net Fatura Tutarı</th>
                <td style="font-size: 11px; color: #047857; background-color: #f0fdf4;">{{ number_format($summary['net_total'], 2, ',', '.') }} ₺</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Oluşturulma Tarihi: {{ date('d.m.Y H:i') }} &bull; FiloMERKEZ Bulut Platformu &bull; Sayfa <span class="page-number"></span>
    </div>

</body>
</html>
