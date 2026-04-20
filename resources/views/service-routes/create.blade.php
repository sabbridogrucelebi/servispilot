@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Yeni Servis Hattı Ekle</h1>

    <form action="{{ route('service-routes.store') }}" method="POST" class="bg-white p-6 rounded shadow">
        @csrf

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block mb-1">Müşteri</label>
                <select name="customer_id" class="w-full border rounded px-3 py-2">
                    <option value="">Seçiniz</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->company_name }}
                        </option>
                    @endforeach
                </select>
                @error('customer_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1">Hat Adı</label>
                <input type="text" name="route_name" value="{{ old('route_name') }}" class="w-full border rounded px-3 py-2">
                @error('route_name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1">Hat Türü</label>
                <select name="route_type" class="w-full border rounded px-3 py-2">
                    <option value="">Seçiniz</option>
                    <option value="Sabah" {{ old('route_type') == 'Sabah' ? 'selected' : '' }}>Sabah</option>
                    <option value="Akşam" {{ old('route_type') == 'Akşam' ? 'selected' : '' }}>Akşam</option>
                    <option value="Mesai" {{ old('route_type') == 'Mesai' ? 'selected' : '' }}>Mesai</option>
                    <option value="Öğrenci" {{ old('route_type') == 'Öğrenci' ? 'selected' : '' }}>Öğrenci</option>
                    <option value="Turizm" {{ old('route_type') == 'Turizm' ? 'selected' : '' }}>Turizm</option>
                </select>
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
                <label class="block mb-1">Fiyat</label>
                <input type="number" step="0.01" name="price" value="{{ old('price') }}" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block mb-1">Başlangıç Noktası</label>
                <input type="text" name="start_location" value="{{ old('start_location') }}" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block mb-1">Bitiş Noktası</label>
                <input type="text" name="end_location" value="{{ old('end_location') }}" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block mb-1">Çıkış Saati</label>
                <input type="time" name="departure_time" value="{{ old('departure_time') }}" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block mb-1">Varış Saati</label>
                <input type="time" name="arrival_time" value="{{ old('arrival_time') }}" class="w-full border rounded px-3 py-2">
            </div>

            <div class="flex items-center mt-7">
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