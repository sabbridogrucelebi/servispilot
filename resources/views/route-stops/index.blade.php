@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Duraklar</h1>
        <a href="{{ route('route-stops.create') }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg shadow">
            Yeni Durak Ekle
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
                    <th class="text-left px-4 py-3">Hat</th>
                    <th class="text-left px-4 py-3">Durak Adı</th>
                    <th class="text-left px-4 py-3">Sıra</th>
                    <th class="text-left px-4 py-3">Saat</th>
                    <th class="text-left px-4 py-3">Konum</th>
                    <th class="text-left px-4 py-3">Durum</th>
                    <th class="text-left px-4 py-3">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routeStops as $stop)
                    <tr class="border-t">
                        <td class="px-4 py-3">{{ $stop->serviceRoute?->route_name }}</td>
                        <td class="px-4 py-3">{{ $stop->stop_name }}</td>
                        <td class="px-4 py-3">{{ $stop->stop_order }}</td>
                        <td class="px-4 py-3">{{ $stop->stop_time ? \Carbon\Carbon::parse($stop->stop_time)->format('H:i') : '-' }}</td>
                        <td class="px-4 py-3">{{ $stop->location }}</td>
                        <td class="px-4 py-3">
                            @if($stop->is_active)
                                <span class="text-green-600 font-medium">Aktif</span>
                            @else
                                <span class="text-red-600 font-medium">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 space-x-2">
                            <a href="{{ route('route-stops.edit', $stop) }}" class="text-blue-600">Düzenle</a>

                            <form action="{{ route('route-stops.destroy', $stop) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600" onclick="return confirm('Bu durağı silmek istediğine emin misin?')">
                                    Sil
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                            Henüz durak kaydı yok.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
