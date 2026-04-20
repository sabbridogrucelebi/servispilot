<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bakım / Tamir PDF Raporu</title>
    <style>
        @page {
            margin: 18px 18px 22px 18px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #0f172a;
        }

        .title {
            background: #2563eb;
            color: #fff;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .meta {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .meta td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
        }

        .meta .label {
            width: 160px;
            background: #f8fafc;
            font-weight: 700;
        }

        .summary {
            margin-bottom: 12px;
            font-size: 11px;
            font-weight: 700;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.report th,
        table.report td {
            border: 1px solid #e2e8f0;
            padding: 6px 7px;
            vertical-align: top;
            word-wrap: break-word;
        }

        table.report th {
            background: #334155;
            color: #fff;
            font-size: 10px;
            text-align: left;
        }

        table.report td {
            font-size: 9px;
        }

        .text-right {
            text-align: right;
        }

        .muted {
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="title">Bakım / Tamir PDF Raporu</div>

    <table class="meta">
        <tr>
            <td class="label">Rapor Tarihi</td>
            <td>{{ $generatedAt->format('d.m.Y H:i') }}</td>
            <td class="label">Toplam Kayıt</td>
            <td>{{ $maintenances->count() }}</td>
        </tr>
        <tr>
            <td class="label">Başlangıç Tarihi</td>
            <td>{{ !empty($filters['start_date']) ? \Carbon\Carbon::parse($filters['start_date'])->format('d.m.Y') : 'Belirtilmedi' }}</td>
            <td class="label">Bitiş Tarihi</td>
            <td>{{ !empty($filters['end_date']) ? \Carbon\Carbon::parse($filters['end_date'])->format('d.m.Y') : 'Bugüne kadar' }}</td>
        </tr>
    </table>

    <div class="summary">
        Toplam Maliyet: {{ number_format($totalAmount, 2, ',', '.') }} ₺
    </div>

    <table class="report">
        <thead>
            <tr>
                <th style="width: 8%;">Tarih</th>
                <th style="width: 10%;">Araç</th>
                <th style="width: 12%;">Marka / Model</th>
                <th style="width: 12%;">Bakım Türü</th>
                <th style="width: 18%;">İşlem</th>
                <th style="width: 8%;">KM</th>
                <th style="width: 12%;">Usta</th>
                <th style="width: 8%;">Durum</th>
                <th style="width: 8%;">Tutar</th>
                <th style="width: 14%;">Not</th>
            </tr>
        </thead>
        <tbody>
            @forelse($maintenances as $maintenance)
                <tr>
                    <td>{{ optional($maintenance->service_date)->format('d.m.Y') ?: '-' }}</td>
                    <td>{{ $maintenance->vehicle?->plate ?: '-' }}</td>
                    <td>{{ trim(($maintenance->vehicle?->brand ?: '-') . ' ' . ($maintenance->vehicle?->model ?: '')) }}</td>
                    <td>{{ $maintenance->maintenance_type ?: '-' }}</td>
                    <td>{{ $maintenance->title ?: '-' }}</td>
                    <td>{{ !is_null($maintenance->km) ? number_format((float) $maintenance->km, 0, ',', '.') . ' KM' : '-' }}</td>
                    <td>{{ $maintenance->service_name ?: '-' }}</td>
                    <td>
                        @if($maintenance->status === 'completed')
                            Tamamlandı
                        @elseif($maintenance->status === 'pending')
                            Bekliyor
                        @elseif($maintenance->status === 'planned')
                            Planlandı
                        @else
                            {{ $maintenance->status ?: '-' }}
                        @endif
                    </td>
                    <td class="text-right">{{ number_format((float) ($maintenance->amount ?? 0), 2, ',', '.') }} ₺</td>
                    <td>{{ $maintenance->description ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="muted">Bu filtreye uygun bakım kaydı bulunamadı.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>