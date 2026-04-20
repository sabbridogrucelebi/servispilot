<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Trafik Cezaları PDF Raporu</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1e293b;
        }

        .title {
            background: #dc2626;
            color: #ffffff;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            padding: 12px;
            margin-bottom: 16px;
        }

        .meta {
            margin-bottom: 12px;
            font-size: 10px;
            color: #475569;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #334155;
            color: #ffffff;
            font-weight: bold;
            padding: 8px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }

        td {
            padding: 7px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background: #f8fafc;
        }

        .paid {
            color: #059669;
            font-weight: bold;
        }

        .unpaid {
            color: #d97706;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="title">Trafik Cezaları PDF Raporu</div>

    <div class="meta">
        Oluşturulma Tarihi: {{ now()->format('d.m.Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Ceza Tarihi</th>
                <th>Ceza No</th>
                <th>Araç</th>
                <th>Şoför</th>
                <th>Madde</th>
                <th>Yer</th>
                <th>Ceza</th>
                <th>İndirimli</th>
                <th>Durum</th>
                <th>Ödeme Tarihi</th>
                <th>Ödenen</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penalties as $penalty)
                <tr>
                    <td>{{ optional($penalty->penalty_date)->format('d.m.Y') ?: '-' }}</td>
                    <td>{{ $penalty->penalty_no ?: '-' }}</td>
                    <td>{{ $penalty->vehicle?->plate ?: '-' }}</td>
                    <td>{{ $penalty->driver_name ?: '-' }}</td>
                    <td>{{ $penalty->penalty_article ?: '-' }}</td>
                    <td>{{ $penalty->penalty_location ?: '-' }}</td>
                    <td>{{ number_format((float) $penalty->penalty_amount, 2, ',', '.') }} ₺</td>
                    <td>{{ number_format((float) $penalty->discounted_amount, 2, ',', '.') }} ₺</td>
                    <td class="{{ $penalty->payment_status === 'paid' ? 'paid' : 'unpaid' }}">
                        {{ $penalty->payment_status === 'paid' ? 'Ödendi' : 'Ödenmedi' }}
                    </td>
                    <td>{{ optional($penalty->payment_date)->format('d.m.Y') ?: '-' }}</td>
                    <td>{{ number_format((float) $penalty->paid_amount, 2, ',', '.') }} ₺</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11">Kayıt bulunamadı.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>