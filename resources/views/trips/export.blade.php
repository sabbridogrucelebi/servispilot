<table>
    {{-- ROW 1: Boş (PHP styles ile doldurulacak) --}}
    <tr><td></td></tr>
    {{-- ROW 2: Boş (PHP styles ile doldurulacak) --}}
    <tr><td></td></tr>
    {{-- ROW 3: Başlık Satırı --}}
    <tr>
        <th>GÜZERGAH</th>
        @foreach($monthDays as $day)
            <th>{{ $day['date']->format('d') }} {{ mb_substr($day['day_name'], 0, 3) }}</th>
        @endforeach
        <th>TOPLAM</th>
    </tr>
    {{-- DATA ROWS --}}
    @foreach($serviceRoutes as $route)
        @php
            $routeSum = 0;
        @endphp
        <tr>
            <td>{{ $route->route_name }}</td>
            @foreach($monthDays as $day)
                @php
                    $cell = $matrix[$day['date_key']][$route->id] ?? null;
                    $price = $cell['value'] ?? '';
                    if ($price !== '' && $price !== null) $routeSum += (float)$price;
                @endphp
                <td>@if($price !== '' && $price !== null){{ (int)(float)$price }}@endif</td>
            @endforeach
            <td>{{ $routeSum > 0 ? (int)$routeSum : '' }}</td>
        </tr>
    @endforeach
    {{-- SUMMARY ROWS --}}
    <tr>
        <td>Ara Toplam</td>
        <td>{{ number_format($summary['subtotal'], 2, ',', '.') }} ₺</td>
    </tr>
    <tr>
        <td>KDV (%{{ $summary['vat_rate'] }})</td>
        <td>{{ number_format($summary['vat_amount'], 2, ',', '.') }} ₺</td>
    </tr>
    @if($summary['withholding_amount'] > 0)
    <tr>
        <td>Tevkifat (%{{ $summary['withholding_rate'] }})</td>
        <td>{{ number_format($summary['withholding_amount'], 2, ',', '.') }} ₺</td>
    </tr>
    @endif
    <tr>
        <td>Net Fatura Tutarı</td>
        <td>{{ number_format($summary['net_total'], 2, ',', '.') }} ₺</td>
    </tr>
</table>
