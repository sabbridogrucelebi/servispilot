@extends('layouts.app')

@section('title', 'Belgeler')
@section('subtitle', 'Araç, Şoför ve Şirket belgelerinizi yönetin')

@section('content')
    <div class="space-y-6">

        @if(session('success'))
            <div class="rounded-[24px] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-[20px] bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-lg shadow-indigo-200/60">
                    📄
                </div>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Belgeler</h2>
                    <p class="mt-1 text-sm text-slate-500">Sistemdeki tüm belgeleri takip et ve süresi dolanları izle</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if(auth()->user()->hasPermission('documents.create'))
                <a href="{{ route('documents.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200/50 hover:scale-[1.02] transition">
                    <span class="text-base">+</span>
                    <span>Yeni Belge Ekle</span>
                </a>
                @endif
            </div>
        </div>

        <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-5">
                <h3 class="text-lg font-bold text-slate-900">Belge Listesi</h3>
                <p class="mt-1 text-sm text-slate-500">Kayıtlı tüm dokümanların durumu</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-[0.1em] text-slate-500 text-xs">Sahibi</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-[0.1em] text-slate-500 text-xs">Belge Türü</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-[0.1em] text-slate-500 text-xs">Belge Adı</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-[0.1em] text-slate-500 text-xs">Başlangıç</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-[0.1em] text-slate-500 text-xs">Bitiş</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-[0.1em] text-slate-500 text-xs">Durum</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-[0.1em] text-slate-500 text-xs">Dosya</th>
                            <th class="px-6 py-4 text-right font-bold uppercase tracking-[0.1em] text-slate-500 text-xs">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($documents as $document)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-6 py-5">
                                    <div class="font-semibold text-slate-800">
                                        @if($document->documentable_type === 'App\Models\Fleet\Vehicle')
                                            🚗 Araç - {{ $document->documentable?->plate ?? '-' }}
                                        @elseif($document->documentable_type === 'App\Models\Fleet\Driver')
                                            👤 Şoför - {{ $document->documentable?->full_name ?? '-' }}
                                        @else
                                            🏢 Firma Genel
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-5 text-slate-700 font-medium">{{ $document->document_type }}</td>
                                <td class="px-6 py-5 text-slate-700">{{ $document->document_name }}</td>
                                <td class="px-6 py-5 text-slate-600">{{ $document->start_date?->format('d.m.Y') ?? '-' }}</td>
                                <td class="px-6 py-5 text-slate-600 font-medium">
                                    @if($document->end_date)
                                        @if($document->end_date->isPast())
                                            <span class="text-rose-600">{{ $document->end_date->format('d.m.Y') }}</span>
                                        @elseif($document->end_date->diffInDays(now()) <= 15)
                                            <span class="text-amber-600">{{ $document->end_date->format('d.m.Y') }}</span>
                                        @else
                                            <span class="text-emerald-600">{{ $document->end_date->format('d.m.Y') }}</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="px-6 py-5">
                                    @if($document->is_active)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Aktif</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700">Pasif</span>
                                    @endif
                                </td>

                                <td class="px-6 py-5">
                                    @if($document->file_path)
                                        <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition">
                                            📄 İncele
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400">Yok</span>
                                    @endif
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex items-center justify-end gap-2">
                                        @if(auth()->user()->hasPermission('documents.edit'))
                                        <a href="{{ route('documents.edit', $document) }}" class="rounded-xl bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-600 hover:bg-indigo-100 transition">
                                            Düzenle
                                        </a>
                                        @endif

                                        @if(auth()->user()->hasPermission('documents.delete'))
                                        <form action="{{ route('documents.destroy', $document) }}" method="POST" class="inline" onsubmit="return confirm('Bu belgeyi silmek istediğine emin misin?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-100 transition">
                                                Sil
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="text-4xl mb-3">🗂️</div>
                                        <h4 class="text-base font-bold text-slate-900">Belge Bulunamadı</h4>
                                        <p class="mt-1 text-sm text-slate-500">Sistemde henüz kayıtlı belge bulunmuyor.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection