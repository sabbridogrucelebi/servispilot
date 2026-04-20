@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-6">Firma Ayarları</h1>

@if(session('success'))
    <div class="mb-4 bg-green-100 text-green-700 px-4 py-3 rounded">
        {{ session('success') }}
    </div>
@endif

<form action="{{ route('company-settings.update') }}" method="POST" class="bg-white p-6 rounded shadow">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block mb-1">Firma Adı</label>
            <input type="text" name="name" value="{{ old('name', $company->name) }}" class="w-full border rounded px-3 py-2">
            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block mb-1">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $company->slug) }}" class="w-full border rounded px-3 py-2">
            @error('slug')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block mb-1">Telefon</label>
            <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="w-full border rounded px-3 py-2">
            @error('phone')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block mb-1">E-posta</label>
            <input type="email" name="email" value="{{ old('email', $company->email) }}" class="w-full border rounded px-3 py-2">
            @error('email')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block mb-1">Vergi No</label>
            <input type="text" name="tax_no" value="{{ old('tax_no', $company->tax_no) }}" class="w-full border rounded px-3 py-2">
            @error('tax_no')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block mb-1">Şehir</label>
            <input type="text" name="city" value="{{ old('city', $company->city) }}" class="w-full border rounded px-3 py-2">
            @error('city')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="col-span-2">
            <label class="block mb-1">Adres</label>
            <textarea name="address" rows="4" class="w-full border rounded px-3 py-2">{{ old('address', $company->address) }}</textarea>
            @error('address')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center mt-2">
            <input type="checkbox" name="is_active" {{ $company->is_active ? 'checked' : '' }} class="mr-2">
            <label>Aktif</label>
        </div>
    </div>

    <div class="mt-6">
        <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded">
            Güncelle
        </button>
    </div>
</form>
@endsection