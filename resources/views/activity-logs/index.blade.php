@extends('layouts.app')

@section('title', 'İşlem Kayıtları')
@section('subtitle', 'Sistemde yapılan hareketleri görüntüleyin')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-[32px] border border-slate-200/70 bg-white shadow-[0_20px_60px_rgba(15,23,42,0.08)] overflow-hidden">
        <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 via-white to-indigo-50/40 px-6 py-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-[26px] font-bold tracking-tight text-slate-900">İşlem Kayıtları</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Yakıt, ödeme, toplu içe aktarma ve diğer sistem hareketleri
                    </p>
                </div>

                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    Panele Dön
                </a>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <form method="GET" action="{{ route('activity-logs.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3 xl:grid-cols-5">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">Modül</label>
                    <select name="module" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm">
                        <option value="">Tümü</option>
                        <option value="fuel" {{ request('module') === 'fuel' ? 'selected' : '' }}>Yakıt</option>
                        <option value="fuel_station_payment" {{ request('module') === 'fuel_station_payment' ? 'selected' : '' }}>İstasyon Ödemesi</option>
                        <option value="fuel_station" {{ request('module') === 'fuel_station' ? 'selected' : '' }}>İstasyon</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">İşlem</label>
                    <select name="action" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm">
                        <option value="">Tümü</option>
                        <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Eklendi</option>
                        <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>Güncellendi</option>
                        <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>Silindi</option>
                        <option value="imported" {{ request('action') === 'imported' ? 'selected' : '' }}>İçe Aktarıldı</option>
                        <option value="bulk_paid" {{ request('action') === 'bulk_paid' ? 'selected' : '' }}>Toplu Ödeme</option>
                    </select>
                </div>

                <div class="md:col-span-1 xl:col-span-3 flex items-end justify-end gap-3">
                    <a href="{{ route('activity-logs.index') }}"
                       class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Temizle
                    </a>

                    <button type="submit"
                            class="rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200/60 transition hover:scale-[1.01]">
                        Filtrele
                    </button>
                </div>
            </form>

            <div class="overflow-x-auto rounded-[24px] border border-slate-200/70">
                <table class="w-full min-w-[1100px]">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Tarih</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Kullanıcı</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Modül</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">İşlem</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Başlık</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Açıklama</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logs as $log)
                            @php
                                $actionClasses = match($log->action) {
                                    'created' => 'bg-emerald-100 text-emerald-700',
                                    'updated' => 'bg-amber-100 text-amber-700',
                                    'deleted' => 'bg-rose-100 text-rose-700',
                                    'imported' => 'bg-sky-100 text-sky-700',
                                    'bulk_paid' => 'bg-violet-100 text-violet-700',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-5 py-4 text-sm text-slate-700">
                                    {{ optional($log->created_at)->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-700">
                                    {{ $log->user?->name ?? '-' }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $log->module }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $actionClasses }}">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm font-semibold text-slate-800">
                                    {{ $log->title }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-500">
                                    {{ $log->description ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Kayıt bulunamadı.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection