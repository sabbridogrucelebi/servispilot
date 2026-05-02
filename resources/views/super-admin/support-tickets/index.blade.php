@extends('layouts.super-admin')

@section('title', 'Destek Talepleri (Helpdesk)')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <!-- Page header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-[0_8px_16px_-6px_rgba(99,102,241,0.5)] border border-white/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">Destek Talepleri</h1>
                    <p class="text-sm font-medium text-slate-500 mt-1">Sistemdeki tüm firmalardan gelen talepleri yönetin.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white/70 backdrop-blur-md shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] rounded-[24px] mb-8 border border-slate-200/60 p-5">
        <form action="{{ route('super-admin.support-tickets.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="w-full sm:w-64">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Durum Filtresi</label>
                <div class="relative">
                    <select name="status" class="form-select w-full bg-slate-50/50 border-slate-200/60 rounded-xl appearance-none font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm">
                        <option value="">Tüm Talepler</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Açık (Yanıt Bekleyen)</option>
                        <option value="answered" {{ request('status') === 'answered' ? 'selected' : '' }}>Yanıtlananlar</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Çözülenler (Kapalı)</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="group relative px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-semibold rounded-xl shadow-[0_4px_0_rgb(67,56,202)] active:shadow-none active:translate-y-[4px] transition-all hover:brightness-110">
                    Filtrele
                </button>
                @if(request()->filled('status'))
                    <a href="{{ route('super-admin.support-tickets.index') }}" class="px-6 py-2.5 bg-white text-slate-600 font-semibold rounded-xl border border-slate-200 shadow-sm hover:bg-slate-50 hover:-translate-y-0.5 transition-all">
                        Sıfırla
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-800 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] rounded-[32px] border border-slate-200 dark:border-slate-700/60 overflow-hidden relative">
        <div class="overflow-x-auto">
            <table class="table-auto w-full dark:text-slate-300">
                <thead class="text-xs uppercase text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-700/20 border-b border-slate-200 dark:border-slate-700/60">
                    <tr>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">No</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Firma</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Konu</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Öncelik</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Durum</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Tarih</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-right">İşlem</div></th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200 dark:divide-slate-700/60">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap text-slate-500">
                                #{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-5 py-4 font-medium text-slate-800 dark:text-slate-100">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center mr-3 text-xs font-bold">{{ substr($ticket->company->name ?? 'S', 0, 1) }}</div>
                                    <div>
                                        <div>{{ $ticket->company->name ?? 'Bilinmeyen Firma' }}</div>
                                        <div class="text-xs text-slate-500 font-normal">{{ $ticket->user->name ?? 'Kullanıcı' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 font-medium text-slate-800 dark:text-slate-100 max-w-xs truncate" title="{{ $ticket->subject }}">
                                {{ $ticket->subject }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($ticket->priority === 'urgent')
                                    <span class="bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 px-2.5 py-1 rounded-full text-xs font-semibold border border-rose-200 dark:border-rose-500/30">Acil</span>
                                @elseif($ticket->priority === 'high')
                                    <span class="bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 px-2.5 py-1 rounded-full text-xs font-semibold border border-orange-200 dark:border-orange-500/30">Yüksek</span>
                                @elseif($ticket->priority === 'low')
                                    <span class="bg-slate-100 dark:bg-slate-500/20 text-slate-600 dark:text-slate-400 px-2.5 py-1 rounded-full text-xs font-semibold border border-slate-200 dark:border-slate-500/30">Düşük</span>
                                @else
                                    <span class="bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 px-2.5 py-1 rounded-full text-xs font-semibold border border-blue-200 dark:border-blue-500/30">Normal</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($ticket->status === 'open')
                                    <span class="bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 px-2.5 py-1 rounded-full text-xs font-semibold">Açık (Yanıt Bekliyor)</span>
                                @elseif($ticket->status === 'answered')
                                    <span class="bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 px-2.5 py-1 rounded-full text-xs font-semibold">Yanıtlandı</span>
                                @else
                                    <span class="bg-slate-100 dark:bg-slate-500/20 text-slate-500 dark:text-slate-400 px-2.5 py-1 rounded-full text-xs font-semibold">Kapatıldı</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-500">
                                {{ $ticket->created_at->format('d.m.Y H:i') }}
                                <div class="text-[10px] text-slate-400">{{ $ticket->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-5 py-5 whitespace-nowrap text-right font-medium">
                                <a href="{{ route('super-admin.support-tickets.show', $ticket) }}" class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-indigo-500/10 to-purple-500/10 text-indigo-600 hover:text-white hover:from-indigo-500 hover:to-purple-600 font-semibold rounded-lg transition-all duration-300 group">
                                    <span>Yanıtla</span>
                                    <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-500 dark:text-slate-400">
                                Harika! Hiç açık destek talebi yok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $tickets->links() }}
    </div>

</div>
@endsection
