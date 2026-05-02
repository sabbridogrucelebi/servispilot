@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Yeni Sefer Ekle</h1>

    <form action="{{ route('trips.store') }}" method="POST" class="bg-white p-6 rounded shadow">
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
                <label class="block mb-1">Tarih</label>
                <input type="date" name="trip_date" value="{{ old('trip_date', now()->format('Y-m-d')) }}" class="w-full border rounded px-3 py-2">
                @error('trip_date') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1">Araç</label>
                <select name="vehicle_id" class="w-full border rounded px-3 py-2">
                    <option value="">Seçiniz</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1">Şoför</label>
                <select name="driver_id" class="w-full border rounded px-3 py-2">
                    <option value="">Seçiniz</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                            {{ $driver->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1">Sefer Durumu</label>
                <select name="trip_status" class="w-full border rounded px-3 py-2">
                    <option value="Yapıldı" {{ old('trip_status') == 'Yapıldı' ? 'selected' : '' }}>Yapıldı</option>
                    <option value="Yapılmadı" {{ old('trip_status') == 'Yapılmadı' ? 'selected' : '' }}>Yapılmadı</option>
                    <option value="Eksik" {{ old('trip_status') == 'Eksik' ? 'selected' : '' }}>Eksik</option>
                    <option value="Mesai" {{ old('trip_status') == 'Mesai' ? 'selected' : '' }}>Mesai</option>
                </select>
            </div>

            <div>
                <label class="block mb-1">Sefer Fiyatı</label>
                <input type="number" step="0.01" name="trip_price" value="{{ old('trip_price') }}" class="w-full border rounded px-3 py-2">
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
