@extends('layouts.app')

@section('title', 'Sistem Logları')
@section('subtitle', 'Tüm kullanıcı işlemlerini ve sistem hareketlerini takip edin')

@section('content')

@php
    $user = auth()->user();
@endphp

<div class="space-y-8">

    <!-- GÜVENLİK BANNER -->
    <div class="rounded-[24px] border border-indigo-100 bg-gradient-to-r from-indigo-50 to-blue-50 p-5 flex items-start gap-4 shadow-sm">
        <div class="h-12 w-12 rounded-2xl bg-white border border-indigo-100 flex items-center justify-center text-xl shadow-sm">🔐</div>
        <div class="flex-1">
            <div class="text-sm font-black text-indigo-900">Firma Yönetici Paneli — Denetim Logları</div>
            <div class="text-xs text-indigo-700 mt-0.5">
                Bu sayfa <strong>sadece firma yöneticileri</strong> tarafından görüntülenebilir. Tüm kullanıcı işlemleri
                (ekleme / güncelleme / silme / dışa aktarma) zaman damgası, IP ve cihaz bilgisiyle birlikte kayıt altına alınır.
            </div>
        </div>
        <span class="hidden md:inline-flex items-center gap-1 px-3 py-1 rounded-full bg-white border border-indigo-100 text-[10px] font-black text-indigo-700 uppercase tracking-wider">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Canlı Kayıt
        </span>
    </div>

    <!-- FİLTRE PANELİ -->
    <div class="rounded-[32px] border border-slate-200 bg-white/80 backdrop-blur-xl p-8 shadow-sm">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="grid gap-5 md:grid-cols-2 lg:grid-cols-6">

            <div class="relative group lg:col-span-2">
                <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Arama</label>
                <input type="text" name="search" value="{{ request('search') }}" maxlength="120"
                       placeholder="Başlık, açıklama, IP..."
                       class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-semibold focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
            </div>

            <div class="relative group">
                <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">İşlemi Yapan</label>
                <select name="user_id"
                        class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-semibold focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none appearance-none">
                    <option value="">Tüm Kullanıcılar</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute right-5 bottom-4 pointer-events-none text-slate-400 text-xs">▼</div>
            </div>

            <div class="relative group">
                <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Modül</label>
                <select name="module"
                        class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-semibold focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none appearance-none">
                    <option value="">Tüm Modüller</option>
                    @foreach($modules as $key => $label)
                        <option value="{{ $key }}" @selected(request('module') === $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute right-5 bottom-4 pointer-events-none text-slate-400 text-xs">▼</div>
            </div>

            <div class="relative group">
                <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">İşlem Tipi</label>
                <select name="action"
                        class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-semibold focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none appearance-none">
                    <option value="">Tüm İşlemler</option>
                    <option value="created"          @selected(request('action') === 'created')>Oluşturma</option>
                    <option value="updated"          @selected(request('action') === 'updated')>Güncelleme</option>
                    <option value="deleted"          @selected(request('action') === 'deleted')>Silme</option>
                    <option value="exported"         @selected(request('action') === 'exported')>Dışa Aktarma</option>
                    <option value="image_uploaded"   @selected(request('action') === 'image_uploaded')>Resim Yükleme</option>
                    <option value="image_deleted"    @selected(request('action') === 'image_deleted')>Resim Silme</option>
                    <option value="document_uploaded"@selected(request('action') === 'document_uploaded')>Belge Yükleme</option>
                    <option value="document_deleted" @selected(request('action') === 'document_deleted')>Belge Silme</option>
                </select>
                <div class="absolute right-5 bottom-4 pointer-events-none text-slate-400 text-xs">▼</div>
            </div>

            <div class="relative group">
                <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Başlangıç</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-semibold focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
            </div>

            <div class="relative group">
                <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Bitiş</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm font-semibold focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
            </div>

            <div class="lg:col-span-6 flex items-end gap-3">
                <button type="submit"
                        class="flex-1 h-[52px] rounded-2xl bg-slate-900 py-4 text-sm font-bold text-white shadow-xl shadow-slate-200 transition hover:bg-slate-800 active:scale-95">
                    Filtrele
                </button>
                <a href="{{ route('activity-logs.index') }}"
                   class="flex h-[52px] w-[52px] items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 active:scale-95"
                   title="Filtreleri Temizle">
                    ✕
                </a>
            </div>
        </form>
    </div>

    <!-- LOG LİSTESİ -->
    <div class="rounded-[32px] border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Zaman</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Kullanıcı</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">İşlem</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Detay</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Cihaz / IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-6">
                                <div class="text-sm font-bold text-slate-900">{{ $log->created_at->format('H:i:s') }}</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tight mt-1">{{ $log->created_at->format('d.m.Y') }}</div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 font-black text-sm">
                                        {{ strtoupper(substr($log->user?->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">{{ $log->user?->name ?? 'Bilinmeyen' }}</div>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">{{ $log->user?->role ?: '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                @php
                                    $actionClass = match($log->action) {
                                        'created', 'image_uploaded', 'document_uploaded' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'updated' => 'bg-amber-50 text-amber-700 border-amber-100',
                                        'deleted', 'image_deleted', 'document_deleted' => 'bg-rose-50 text-rose-700 border-rose-100',
                                        'exported' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                        default => 'bg-slate-50 text-slate-700 border-slate-100'
                                    };
                                    $actionLabel = match($log->action) {
                                        'created' => 'Oluşturuldu',
                                        'updated' => 'Güncellendi',
                                        'deleted' => 'Silindi',
                                        'exported' => 'Dışa Aktarıldı',
                                        'image_uploaded' => 'Resim Yüklendi',
                                        'image_deleted' => 'Resim Silindi',
                                        'document_uploaded' => 'Belge Yüklendi',
                                        'document_deleted' => 'Belge Silindi',
                                        default => $log->action,
                                    };
                                @endphp
                                <div class="flex flex-col gap-2 items-start">
                                    <span class="rounded-full px-3 py-1 text-[9px] font-black uppercase tracking-widest border {{ $actionClass }}">
                                        {{ $actionLabel }}
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                        {{ $modules[$log->module] ?? $log->module }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-6 max-w-md">
                                <div class="text-sm font-bold text-slate-700 leading-relaxed">{{ $log->title }}</div>
                                <div class="text-xs font-medium text-slate-400 mt-1 italic">{{ $log->description }}</div>
                                
                                @if($log->action === 'updated' && $log->new_values)
                                    <div class="mt-3 flex flex-wrap gap-1">
                                        @foreach(array_keys($log->new_values) as $field)
                                            <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-tight">{{ $field }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                <div class="text-xs font-bold text-slate-600">{{ $log->ip_address }}</div>
                                <div class="text-[9px] font-medium text-slate-400 mt-1 truncate max-w-[150px]" title="{{ $log->user_agent }}">
                                    {{ $log->user_agent }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-50 text-3xl">📭</div>
                                <h3 class="mt-6 text-lg font-black text-slate-900">Henüz işlem kaydı bulunmuyor.</h3>
                                <p class="mt-2 text-sm font-medium text-slate-500">Sistem üzerindeki tüm hareketler burada listelenecektir.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="border-t border-slate-100 px-8 py-6 bg-slate-50/50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>

@endsection