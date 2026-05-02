@extends('layouts.super-admin')

@section('title', 'Paket Yönetimi')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <!-- Page header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-[0_8px_16px_-6px_rgba(99,102,241,0.5)] border border-white/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">Abonelik Paketleri</h1>
                    <p class="text-sm font-medium text-slate-500 mt-1">Sistemdeki abonelik planlarını ve kotalarını yönetin.</p>
                </div>
            </div>
        </div>
        <div class="flex shrink-0">
            <a href="{{ route('super-admin.plans.create') }}" class="group relative px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold rounded-xl shadow-[0_6px_0_rgb(67,56,202)] active:shadow-none active:translate-y-[6px] transition-all hover:brightness-110 flex items-center">
                <svg class="w-5 h-5 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Yeni Paket Ekle</span>
            </a>
        </div>
    </div>

    <!-- Plans Table -->
    <div class="bg-white/70 backdrop-blur-md shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] rounded-[32px] border border-slate-200/60 overflow-hidden relative">
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead class="text-xs uppercase text-slate-400 bg-slate-50/50 border-b border-slate-200/60">
                    <tr>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Paket Adı</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Fiyat</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Kotalar</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Durum</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-right">İşlem</div></th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200/60">
                    @forelse($plans as $plan)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="font-bold text-slate-800">{{ $plan->name }}</div>
                                    @if($plan->is_popular)
                                        <span class="ml-2 bg-amber-100 text-amber-600 text-[10px] font-bold px-2 py-0.5 rounded-full border border-amber-200">Popüler</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500 max-w-xs truncate">{{ $plan->description }}</div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-bold text-indigo-600">{{ number_format($plan->price, 2) }} {{ $plan->currency }}</div>
                                <div class="text-[10px] text-slate-400">Yıllık: {{ number_format($plan->yearly_price, 2) }} {{ $plan->currency }}</div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex gap-2">
                                    <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-[11px] font-bold">🚗 {{ $plan->max_vehicles }} Araç</span>
                                    <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-[11px] font-bold">👥 {{ $plan->max_users }} Kullanıcı</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($plan->is_active)
                                    <span class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-full text-[11px] font-bold border border-emerald-200">Aktif</span>
                                @else
                                    <span class="bg-slate-100 text-slate-400 px-3 py-1 rounded-full text-[11px] font-bold border border-slate-200">Pasif</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('super-admin.plans.edit', $plan) }}" class="p-2 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <form action="{{ route('super-admin.plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('Silmek istediğinize emin misiniz?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-100 transition shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-500">
                                Henüz bir paket tanımlanmamış.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
