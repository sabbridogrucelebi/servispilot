@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-6">Raporlar</h1>

<form method="GET" action="{{ route('reports.index') }}" class="bg-white p-6 rounded shadow mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block mb-1">Başlangıç Tarihi</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block mb-1">Bitiş Tarihi</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="w-full border rounded px-3 py-2">
        </div>

        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded">
                Filtrele
            </button>
        </div>
    </div>
</form>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
    <div class="bg-white p-4 rounded shadow">
        <div class="text-sm text-gray-500">Sefer Sayısı</div>
        <div class="text-2xl font-bold mt-2">{{ $tripCount }}</div>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <div class="text-sm text-gray-500">Sefer Geliri</div>
        <div class="text-2xl font-bold mt-2">{{ number_format($tripIncome, 2, ',', '.') }} ₺</div>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <div class="text-sm text-gray-500">Yakıt Gideri</div>
        <div class="text-2xl font-bold mt-2">{{ number_format($fuelCost, 2, ',', '.') }} ₺</div>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <div class="text-sm text-gray-500">Maaş Gideri</div>
        <div class="text-2xl font-bold mt-2">{{ number_format($salaryCost, 2, ',', '.') }} ₺</div>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <div class="text-sm text-gray-500">Net Kar</div>
        <div class="text-2xl font-bold mt-2 {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ number_format($netProfit, 2, ',', '.') }} ₺
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded shadow">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Sefer Raporu</h2>
            <a href="{{ route('reports.trips.csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="text-blue-600">
                CSV İndir
            </a>
        </div>

        <div class="overflow-auto max-h-96">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3">Tarih</th>
                        <th class="text-left px-4 py-3">Hat</th>
                        <th class="text-left px-4 py-3">Araç</th>
                        <th class="text-left px-4 py-3">Şoför</th>
                        <th class="text-left px-4 py-3">Fiyat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trips as $trip)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $trip->trip_date?->format('d.m.Y') }}</td>
                            <td class="px-4 py-3">{{ $trip->serviceRoute?->route_name }}</td>
                            <td class="px-4 py-3">{{ $trip->vehicle?->plate ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $trip->driver?->full_name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ number_format($trip->trip_price ?? 0, 2, ',', '.') }} ₺</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">Kayıt yok</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Maaş Raporu</h2>
            <a href="{{ route('reports.payrolls.csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="text-blue-600">
                CSV İndir
            </a>
        </div>

        <div class="overflow-auto max-h-96">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3">Ay</th>
                        <th class="text-left px-4 py-3">Şoför</th>
                        <th class="text-left px-4 py-3">Net Maaş</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $payroll)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $payroll->period_month }}</td>
                            <td class="px-4 py-3">{{ $payroll->driver?->full_name }}</td>
                            <td class="px-4 py-3">{{ number_format($payroll->net_salary, 2, ',', '.') }} ₺</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-gray-500">Kayıt yok</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Yakıt Raporu</h2>
            <a href="{{ route('reports.fuels.csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="text-blue-600">
                CSV İndir
            </a>
        </div>

        <div class="overflow-auto max-h-96">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3">Tarih</th>
                        <th class="text-left px-4 py-3">Araç</th>
                        <th class="text-left px-4 py-3">Toplam</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fuels as $fuel)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $fuel->date?->format('d.m.Y') }}</td>
                            <td class="px-4 py-3">{{ $fuel->vehicle?->plate }}</td>
                            <td class="px-4 py-3">{{ number_format($fuel->total_cost, 2, ',', '.') }} ₺</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-gray-500">Kayıt yok</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Belge Raporu</h2>
            <a href="{{ route('reports.documents.csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="text-blue-600">
                CSV İndir
            </a>
        </div>

        <div class="overflow-auto max-h-96">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3">Tür</th>
                        <th class="text-left px-4 py-3">Ad</th>
                        <th class="text-left px-4 py-3">Bitiş</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $document->document_type }}</td>
                            <td class="px-4 py-3">{{ $document->document_name }}</td>
                            <td class="px-4 py-3">{{ $document->end_date?->format('d.m.Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-gray-500">Kayıt yok</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection