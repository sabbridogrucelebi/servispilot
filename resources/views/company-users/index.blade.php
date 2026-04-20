@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Firma Kullanıcıları</h1>

    <a href="{{ route('company-users.create') }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg shadow">
        Yeni Kullanıcı Ekle
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
                <th class="text-left px-4 py-3">Ad Soyad</th>
                <th class="text-left px-4 py-3">E-posta</th>
                <th class="text-left px-4 py-3">Rol</th>
                <th class="text-left px-4 py-3">Durum</th>
                <th class="text-left px-4 py-3">İşlem</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr class="border-t">
                    <td class="px-4 py-3">{{ $user->name }}</td>
                    <td class="px-4 py-3">{{ $user->email }}</td>
                    <td class="px-4 py-3">{{ $user->role }}</td>
                    <td class="px-4 py-3">
                        @if($user->is_active)
                            <span class="text-green-600 font-medium">Aktif</span>
                        @else
                            <span class="text-red-600 font-medium">Pasif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 space-x-2">
                        <a href="{{ route('company-users.edit', $user) }}" class="text-blue-600">Düzenle</a>

                        @if($user->id !== auth()->id())
                            <form action="{{ route('company-users.destroy', $user) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600" onclick="return confirm('Bu kullanıcıyı silmek istediğine emin misin?')">
                                    Sil
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                        Henüz kullanıcı kaydı yok.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection