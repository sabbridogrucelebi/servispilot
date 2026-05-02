@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Servis Hatları</h1>
        <a href="{{ route('service-routes.create') }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg shadow">
            Yeni Servis Hattı Ekle
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-100 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left px-4 py-3">Hat Adı</th>
                    <th class="text-left px-4 py-3">Müşteri</th>
                    <th class="text-left px-4 py-3">Araç</th>
                    <th class="text-left px-4 py-3">Şoför</th>
                    <th class="text-left px-4 py-3">Başlangıç</th>
                    <th class="text-left px-4 py-3">Bitiş</th>
                    <th class="text-left px-4 py-3">Saat</th>
                    <th class="text-left px-4 py-3">Fiyat</th>
                    <th class="text-left px-4 py-3">Durum</th>
                    <th class="text-left px-4 py-3">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($serviceRoutes as $route)
                    <tr class="border-t">
                        <td class="px-4 py-3">{{ $route->route_name }}</td>
                        <td class="px-4 py-3">{{ $route->customer?->company_name }}</td>
                        <td class="px-4 py-3">{{ $route->vehicle?->plate ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $route->driver?->full_name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $route->start_location }}</td>
                        <td class="px-4 py-3">{{ $route->end_location }}</td>
                        <td class="px-4 py-3">
                            {{ $route->departure_time ? \Carbon\Carbon::parse($route->departure_time)->format('H:i') : '-' }}
                            -
                            {{ $route->arrival_time ? \Carbon\Carbon::parse($route->arrival_time)->format('H:i') : '-' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $route->price ? number_format($route->price, 2, ',', '.') . ' ₺' : '-' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($route->is_active)
                                <span class="text-green-600 font-medium">Aktif</span>
                            @else
                                <span class="text-red-600 font-medium">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 space-x-2">
                            <a href="{{ route('service-routes.edit', $route) }}" class="text-blue-600">Düzenle</a>

                            <form action="{{ route('service-routes.destroy', $route) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600" onclick="return confirm('Bu servis hattını silmek istediğine emin misin?')">
                                    Sil
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-6 text-center text-gray-500">
                            Henüz servis hattı kaydı yok.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
