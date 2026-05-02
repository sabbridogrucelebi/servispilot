<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>{{ $vehicle->plate }} Bakım Kayıtları</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #4F46E5; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #1e1b4b; }
        .header p { margin: 5px 0; color: #64748b; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px; }
        .main-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .main-table th { background-color: #f1f5f9; color: #475569; text-align: left; padding: 10px; border: 1px solid #e2e8f0; }
        .main-table td { padding: 10px; border: 1px solid #e2e8f0; }
        .total-row { background-color: #f8fafc; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #94a3b8; padding: 10px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $company->name ?? 'FiloMERKEZ' }}</h1>
        <p>Araç Bakım Raporu</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>Araç Plaka:</strong></td>
            <td width="35%">{{ $vehicle->plate }}</td>
            <td width="15%"><strong>Rapor Tarihi:</strong></td>
            <td width="35%">{{ now()->format('d.m.Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Araç Bilgisi:</strong></td>
            <td>{{ $vehicle->brand }} {{ $vehicle->model }}</td>
            <td><strong>Tarih Aralığı:</strong></td>
            <td>{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d.m.Y') : 'Tüm Zamanlar' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d.m.Y') : 'Bugün' }}</td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th>Bakım Başlığı / Detay</th>
                <th>Tür</th>
                <th>Tarih</th>
                <th>KM</th>
                <th>Servis</th>
                <th align="right">Tutar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($maintenances as $m)
            <tr>
                <td>
                    <strong>{{ $m->title }}</strong><br>
                    <small style="color: #64748b">{{ $m->description }}</small>
                </td>
                <td>{{ $m->maintenance_type }}</td>
                <td>{{ $m->service_date ? $m->service_date->format('d.m.Y') : '-' }}</td>
                <td>{{ number_format($m->km, 0, ',', '.') }} KM</td>
                <td>{{ $m->service_name }}</td>
                <td align="right">{{ number_format($m->amount, 2, ',', '.') }} ₺</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" align="right">GENEL TOPLAM</td>
                <td align="right">{{ number_format($totalCost, 2, ',', '.') }} ₺</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Bu rapor FiloMERKEZ sistemi tarafından otomatik olarak oluşturulmuştur.
    </div>
</body>
</html>
