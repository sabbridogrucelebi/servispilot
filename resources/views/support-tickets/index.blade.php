@extends('layouts.app')

@section('title', 'Destek Taleplerim')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <!-- Page header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-[0_8px_16px_-6px_rgba(99,102,241,0.5)] border border-white/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">Destek Merkezi</h1>
                    <p class="text-sm font-medium text-slate-500 mt-1">Sistemle ilgili yaşadığınız sorunları veya taleplerinizi bize buradan iletebilirsiniz.</p>
                </div>
            </div>
        </div>

        <div class="flex shrink-0">
            <a href="{{ route('support-tickets.create') }}" class="group relative px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold rounded-xl shadow-[0_6px_0_rgb(67,56,202)] active:shadow-none active:translate-y-[6px] transition-all hover:brightness-110 flex items-center">
                <svg class="w-5 h-5 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Yeni Talep Aç</span>
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white/70 backdrop-blur-md shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] rounded-[32px] border border-slate-200/60 overflow-hidden relative mb-8">
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead class="text-xs uppercase text-slate-400 bg-slate-50/50 border-b border-slate-200/60">
                    <tr>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Talep No</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Konu</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Öncelik</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Durum</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Oluşturulma</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-right">İşlem</div></th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200/60">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-500">#{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-800">{{ $ticket->subject }}</div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($ticket->priority === 'urgent')
                                    <span class="bg-rose-100 text-rose-600 px-3 py-1.5 rounded-full text-[11px] font-bold border border-rose-200">Acil</span>
                                @elseif($ticket->priority === 'high')
                                    <span class="bg-orange-100 text-orange-600 px-3 py-1.5 rounded-full text-[11px] font-bold border border-orange-200">Yüksek</span>
                                @elseif($ticket->priority === 'low')
                                    <span class="bg-slate-100 text-slate-600 px-3 py-1.5 rounded-full text-[11px] font-bold border border-slate-200">Düşük</span>
                                @else
                                    <span class="bg-blue-100 text-blue-600 px-3 py-1.5 rounded-full text-[11px] font-bold border border-blue-200">Normal</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($ticket->status === 'open')
                                    <span class="bg-amber-100 text-amber-600 px-3 py-1.5 rounded-full text-[11px] font-bold border border-amber-200">Açık (Yanıt Bekliyor)</span>
                                @elseif($ticket->status === 'answered')
                                    <span class="bg-emerald-100 text-emerald-600 px-3 py-1.5 rounded-full text-[11px] font-bold border border-emerald-200">Yanıtlandı</span>
                                @else
                                    <span class="bg-slate-100 text-slate-500 px-3 py-1.5 rounded-full text-[11px] font-bold border border-slate-200">Kapatıldı</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="text-slate-500 font-medium">{{ $ticket->created_at->format('d.m.Y H:i') }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $ticket->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right font-medium">
                                <a href="{{ route('support-tickets.show', $ticket) }}" class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-indigo-500/10 to-purple-500/10 text-indigo-600 hover:text-white hover:from-indigo-500 hover:to-purple-600 font-bold rounded-lg transition-all duration-300 group">
                                    <span>Görüntüle</span>
                                    <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                Henüz hiçbir destek talebiniz bulunmuyor.
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
