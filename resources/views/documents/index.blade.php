@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Belgeler</h1>
        <a href="{{ route('documents.create') }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg shadow">
            Yeni Belge Ekle
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
                    <th class="text-left px-4 py-3">Sahibi</th>
                    <th class="text-left px-4 py-3">Belge Türü</th>
                    <th class="text-left px-4 py-3">Belge Adı</th>
                    <th class="text-left px-4 py-3">Başlangıç</th>
                    <th class="text-left px-4 py-3">Bitiş</th>
                    <th class="text-left px-4 py-3">Dosya</th>
                    <th class="text-left px-4 py-3">Durum</th>
                    <th class="text-left px-4 py-3">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $document)
                    <tr class="border-t">
                        <td class="px-4 py-3">
                            @if($document->documentable_type === 'App\Models\Fleet\Vehicle')
                                Araç - {{ $document->documentable?->plate ?? '-' }}
                            @elseif($document->documentable_type === 'App\Models\Fleet\Driver')
                                Şoför - {{ $document->documentable?->full_name ?? '-' }}
                            @else
                                -
                            @endif
                        </td>

                        <td class="px-4 py-3">{{ $document->document_type }}</td>
                        <td class="px-4 py-3">{{ $document->document_name }}</td>
                        <td class="px-4 py-3">{{ $document->start_date?->format('d.m.Y') ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $document->end_date?->format('d.m.Y') ?? '-' }}</td>

                        <td class="px-4 py-3">
                            @if($document->file_path)
                                <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="text-green-600 underline">
                                    Gör
                                </a>
                            @else
                                -
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            @if($document->is_active)
                                <span class="text-green-600 font-medium">Aktif</span>
                            @else
                                <span class="text-red-600 font-medium">Pasif</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 space-x-2">
                            <a href="{{ route('documents.edit', $document) }}" class="text-blue-600">Düzenle</a>

                            <form action="{{ route('documents.destroy', $document) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600" onclick="return confirm('Bu belgeyi silmek istediğine emin misin?')">
                                    Sil
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                            Henüz belge kaydı yok.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection