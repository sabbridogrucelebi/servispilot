@extends('layouts.app')

@section('title', 'Sistem Günlüğü')
@section('subtitle', 'Tüm kullanıcı hareketlerini ve veri değişikliklerini takip edin')

@section('content')
<div class="space-y-6 animate-in fade-in duration-700">
    
    <!-- Filtreleme Paneli -->
    <div class="rounded-[32px] border border-slate-200/70 bg-white p-8 shadow-sm">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4 items-end">
            <div>
                <label class="mb-2 block text-[11px] font-black uppercase tracking-widest text-slate-400 ml-1">Kullanıcı</label>
                <select name="user_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-bold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
                    <option value="">Tüm Kullanıcılar</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-[11px] font-black uppercase tracking-widest text-slate-400 ml-1">Modül</label>
                <select name="module" class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-bold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
                    <option value="">Tüm Modüller</option>
                    @foreach($modules as $key => $label)
                        <option value="{{ $key }}" @selected(request('module') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-[11px] font-black uppercase tracking-widest text-slate-400 ml-1">İşlem Tipi</label>
                <select name="action" class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-bold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
                    <option value="">Tüm İşlemler</option>
                    <option value="created" @selected(request('action') === 'created')>Yeni Kayıt</option>
                    <option value="updated" @selected(request('action') === 'updated')>Güncelleme</option>
                    <option value="deleted" @selected(request('action') === 'deleted')>Silme</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 rounded-2xl bg-slate-900 px-6 py-3 text-sm font-black text-white shadow-xl hover:bg-indigo-600 transition-all active:scale-95 uppercase tracking-widest">
                    FİLTRELE
                </button>
                <a href="{{ route('activity-logs.index') }}" class="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-400 hover:text-rose-500 hover:border-rose-100 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
            </div>
        </form>
    </div>

    <!-- Log Listesi -->
    <div class="rounded-[32px] border border-slate-200/70 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Zaman / Kullanıcı</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">İşlem / Modül</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Detay</th>
                        <th class="px-8 py-5 text-right text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Aksiyon</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($logs as $log)
                        @php
                            $actionColor = match($log->action) {
                                'created' => 'emerald',
                                'updated' => 'indigo',
                                'deleted' => 'rose',
                                default => 'slate',
                            };
                            $actionLabel = match($log->action) {
                                'created' => 'EKLENDİ',
                                'updated' => 'GÜNCELLENDİ',
                                'deleted' => 'SİLİNDİ',
                                default => strtoupper($log->action),
                            };
                        @endphp
                        <tr class="group hover:bg-slate-50/50 transition-all">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-{{ $actionColor }}-50 text-{{ $actionColor }}-600 font-black text-xs border border-{{ $actionColor }}-100 shadow-sm">
                                        {{ $log->created_at->format('H:i') }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-slate-800">{{ $log->user?->name ?? 'Sistem' }}</div>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">{{ $log->created_at->format('d/m/Y') }} • IP: {{ $log->ip_address }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-{{ $actionColor }}-50 text-[10px] font-black text-{{ $actionColor }}-600 border border-{{ $actionColor }}-100 mb-1">
                                    {{ $actionLabel }}
                                </div>
                                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">{{ $modules[$log->module] ?? $log->module }}</div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="text-sm font-black text-slate-700">{{ $log->title }}</div>
                                <div class="text-[11px] font-bold text-slate-400 mt-1 leading-relaxed">{{ $log->description }}</div>
                            </td>
                            <td class="px-8 py-6 text-right">
                                @if($log->old_values || $log->new_values)
                                    <button type="button" 
                                            onclick="showLogDetail('{{ $log->id }}')"
                                            class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-[10px] font-black text-white hover:bg-indigo-600 transition-all shadow-lg active:scale-95 uppercase tracking-widest">
                                        İncele
                                    </button>
                                    
                                    <!-- Veri Değişim Detayı (Gizli) -->
                                    <template id="log-detail-{{ $log->id }}">
                                        <div class="space-y-6 p-2">
                                            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                                                <div>
                                                    <h4 class="text-lg font-black text-slate-800">Veri Değişim Detayı</h4>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $log->title }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-[10px] font-black text-slate-500">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                                                    <div class="text-[10px] font-bold text-slate-400">{{ $log->user?->name }}</div>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div class="space-y-3">
                                                    <h5 class="text-[11px] font-black text-rose-500 uppercase tracking-widest ml-1">Eski Veri</h5>
                                                    <div class="rounded-2xl bg-rose-50/50 border border-rose-100 p-4 font-mono text-[11px] text-rose-700 overflow-auto max-h-[300px]">
                                                        @if($log->old_values)
                                                            <pre class="whitespace-pre-wrap">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        @else
                                                            <span class="italic">Veri yok</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="space-y-3">
                                                    <h5 class="text-[11px] font-black text-emerald-500 uppercase tracking-widest ml-1">Yeni Veri</h5>
                                                    <div class="rounded-2xl bg-emerald-50/50 border border-emerald-100 p-4 font-mono text-[11px] text-emerald-700 overflow-auto max-h-[300px]">
                                                        @if($log->new_values)
                                                            <pre class="whitespace-pre-wrap">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        @else
                                                            <span class="italic">Veri yok</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                @else
                                    <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest italic">Detay Yok</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <div class="text-4xl mb-4">📜</div>
                                <p class="text-sm font-bold text-slate-400">Sistemde henüz bir hareket kaydı bulunmuyor.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($logs->hasPages())
            <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Log Detay Modalı -->
<div id="logModal" class="fixed inset-0 z-[60] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeLogModal()"></div>
        <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
        <div class="relative inline-block transform overflow-hidden rounded-[40px] bg-white text-left align-bottom shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl sm:align-middle">
            <div class="bg-white px-8 pt-8 pb-8">
                <div id="modalContent"></div>
                <div class="mt-8 flex justify-end">
                    <button type="button" 
                            onclick="closeLogModal()"
                            class="rounded-2xl bg-slate-100 px-8 py-3 text-sm font-black text-slate-600 hover:bg-slate-200 transition-all uppercase tracking-widest">
                        Kapat
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showLogDetail(logId) {
        const template = document.getElementById('log-detail-' + logId);
        const modalContent = document.getElementById('modalContent');
        const modal = document.getElementById('logModal');
        
        modalContent.innerHTML = template.innerHTML;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeLogModal() {
        const modal = document.getElementById('logModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // ESC tuşu ile kapatma
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeLogModal();
        }
    });
</script>

<style>
    pre {
        font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace;
    }
</style>
@endsection