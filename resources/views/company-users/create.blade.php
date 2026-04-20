@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-6">Yeni Kullanıcı Ekle</h1>

<form action="{{ route('company-users.store') }}" method="POST" class="bg-white p-6 rounded shadow">
    @csrf

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block mb-1">Ad Soyad</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2">
            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block mb-1">E-posta</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded px-3 py-2">
            @error('email')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block mb-1">Şifre</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2">
            @error('password')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block mb-1">Şifre Tekrar</label>
            <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block mb-1">Rol</label>
            <select name="role" class="w-full border rounded px-3 py-2">
                <option value="company_admin" {{ old('role') == 'company_admin' ? 'selected' : '' }}>Firma Admini</option>
                <option value="operation" {{ old('role') == 'operation' ? 'selected' : '' }}>Operasyon</option>
                <option value="accounting" {{ old('role') == 'accounting' ? 'selected' : '' }}>Muhasebe</option>
                <option value="viewer" {{ old('role') == 'viewer' ? 'selected' : '' }}>Görüntüleyici</option>
            </select>
            @error('role')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center mt-7">
            <input type="checkbox" name="is_active" checked class="mr-2">
            <label>Aktif</label>
        </div>
    </div>

    <div class="mt-8">
        <h2 class="text-lg font-bold mb-4">Menü Yetkileri</h2>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($permissions as $permission)
                <label class="flex items-center gap-2 border rounded px-3 py-2">
                    <input
                        type="checkbox"
                        name="permissions[]"
                        value="{{ $permission->id }}"
                        {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                    >
                    <span>{{ $permission->label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="mt-6">
        <button type="submit" class="bg-green-600 text-white px-5 py-2 rounded">
            Kaydet
        </button>
    </div>
</form>
@endsection