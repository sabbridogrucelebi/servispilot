@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Belge Düzenle</h1>

    <form action="{{ route('documents.update', $document) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block mb-1">Belge Sahibi Türü</label>
                <select name="owner_type" class="w-full border rounded px-3 py-2">
                    <option value="vehicle" {{ old('owner_type', $ownerType) == 'vehicle' ? 'selected' : '' }}>Araç</option>
                    <option value="driver" {{ old('owner_type', $ownerType) == 'driver' ? 'selected' : '' }}>Şoför</option>
                </select>
                @error('owner_type')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block mb-1">Belge Sahibi</label>
                <select name="owner_id" class="w-full border rounded px-3 py-2">
                    <optgroup label="Araçlar">
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ old('owner_id', $ownerType === 'vehicle' ? $document->documentable_id : null) == $vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}
                            </option>
                        @endforeach
                    </optgroup>

                    <optgroup label="Şoförler">
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ old('owner_id', $ownerType === 'driver' ? $document->documentable_id : null) == $driver->id ? 'selected' : '' }}>
                                {{ $driver->full_name }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
                @error('owner_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block mb-1">Belge Türü</label>
                <input
                    type="text"
                    name="document_type"
                    value="{{ old('document_type', $document->document_type) }}"
                    class="w-full border rounded px-3 py-2">
                @error('document_type')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block mb-1">Belge Adı</label>
                <input
                    type="text"
                    name="document_name"
                    value="{{ old('document_name', $document->document_name) }}"
                    class="w-full border rounded px-3 py-2">
                @error('document_name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block mb-1">Başlangıç Tarihi</label>
                <input
                    type="date"
                    name="start_date"
                    value="{{ old('start_date', $document->start_date?->format('Y-m-d')) }}"
                    class="w-full border rounded px-3 py-2">
                @error('start_date')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block mb-1">Bitiş Tarihi</label>
                <input
                    type="date"
                    name="end_date"
                    value="{{ old('end_date', $document->end_date?->format('Y-m-d')) }}"
                    class="w-full border rounded px-3 py-2">
                @error('end_date')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-span-2">
                <label class="block mb-1">Dosya (PDF / JPG / PNG)</label>
                <input
                    type="file"
                    name="file"
                    class="w-full border rounded px-3 py-2">
                @error('file')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror

                @if($document->file_path)
                    <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="text-blue-600 underline mt-2 inline-block">
                        Mevcut Dosyayı Gör
                    </a>
                @endif
            </div>

            <div class="flex items-center mt-2">
                <input type="checkbox" name="is_active" {{ $document->is_active ? 'checked' : '' }} class="mr-2">
                <label>Aktif</label>
            </div>
        </div>

        <div class="mt-4">
            <label class="block mb-1">Notlar</label>
            <textarea name="notes" rows="4" class="w-full border rounded px-3 py-2">{{ old('notes', $document->notes) }}</textarea>
            @error('notes')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded">
                Güncelle
            </button>
        </div>
    </form>
@endsection