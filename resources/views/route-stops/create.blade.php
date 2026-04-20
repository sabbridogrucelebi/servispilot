@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Yeni Durak Ekle</h1>

    <form action="{{ route('route-stops.store') }}" method="POST" class="bg-white p-6 rounded shadow">
        @csrf

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block mb-1">Servis Hattı</label>
                <select name="service_route_id" class="w-full border rounded px-3 py-2">
                    <option value="">Seçiniz</option>
                    @foreach($serviceRoutes as $route)
                        <option value="{{ $route->id }}" {{ old('service_route_id') == $route->id ? 'selected' : '' }}>
                            {{ $route->route_name }}
                        </option>
                    @endforeach
                </select>
                @error('service_route_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1">Durak Adı</label>
                <input type="text" name="stop_name" value="{{ old('stop_name') }}" class="w-full border rounded px-3 py-2">
                @error('stop_name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1">Durak Sırası</label>
                <input type="number" name="stop_order" value="{{ old('stop_order', 1) }}" class="w-full border rounded px-3 py-2">
                @error('stop_order') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1">Durak Saati</label>
                <input type="time" name="stop_time" value="{{ old('stop_time') }}" class="w-full border rounded px-3 py-2">
            </div>

            <div class="col-span-2">
                <label class="block mb-1">Konum</label>
                <input type="text" name="location" value="{{ old('location') }}" class="w-full border rounded px-3 py-2">
            </div>

            <div class="flex items-center mt-2">
                <input type="checkbox" name="is_active" checked class="mr-2">
                <label>Aktif</label>
            </div>
        </div>

        <div class="mt-4">
            <label class="block mb-1">Notlar</label>
            <textarea name="notes" rows="4" class="w-full border rounded px-3 py-2">{{ old('notes') }}</textarea>
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-green-600 text-white px-5 py-2 rounded">
                Kaydet
            </button>
        </div>
    </form>
@endsection