@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Sistem Logları</h1>
            <p class="text-slate-500 mt-1">Platform üzerindeki tüm kullanıcı işlemlerini ve sistem hareketlerini izleyin.</p>
        </div>
        
        <button type="button" onclick="document.getElementById('filterPanel').classList.toggle('hidden')" class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg font-medium shadow-sm transition-colors flex items-center gap-2">
            <i class="ti ti-filter"></i> Gelişmiş Filtreler
        </button>
    </div>

    <!-- Filtre Paneli -->
    <div id="filterPanel" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 {{ request()->except('page') ? '' : 'hidden' }}">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Arama</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Başlık, açıklama veya IP..." class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Modül</label>
                <select name="module" class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                    <option value="">Tüm Modüller</option>
                    @foreach($modules as $key => $label)
                        <option value="{{ $key }}" {{ request('module') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">İşlem Tipi</label>
                <select name="action" class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                    <option value="">Tümü</option>
                    <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Ekleme (Oluşturma)</option>
                    <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Güncelleme</option>
                    <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Silme</option>
                    <option value="exported" {{ request('action') == 'exported' ? 'selected' : '' }}>Dışa Aktarım (Export)</option>
                    <option value="image_uploaded" {{ request('action') == 'image_uploaded' ? 'selected' : '' }}>Görsel Ekleme</option>
                    <option value="document_uploaded" {{ request('action') == 'document_uploaded' ? 'selected' : '' }}>Belge Ekleme</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Kullanıcı</label>
                <select name="user_id" class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                    <option value="">Tüm Kullanıcılar</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Başlangıç Tarihi</label>
                <input type="date" name="from" value="{{ request('from') }}" class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Bitiş Tarihi</label>
                <input type="date" name="to" value="{{ request('to') }}" class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
            </div>

            <div class="lg:col-span-2 flex items-end justify-end gap-3">
                @if(request()->except('page'))
                    <a href="{{ route('activity-logs.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700 px-4 py-2">Temizle</a>
                @endif
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium shadow-sm transition-colors">
                    Sonuçları Getir
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold">İşlem / Modül</th>
                        <th class="px-6 py-4 font-semibold">Detay</th>
                        <th class="px-6 py-4 font-semibold">Kullanıcı & IP</th>
                        <th class="px-6 py-4 font-semibold text-right">Tarih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        @php
                            $actionColor = match($log->action) {
                                'created', 'document_uploaded', 'image_uploaded' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'updated' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'deleted', 'document_deleted', 'image_deleted' => 'bg-rose-100 text-rose-700 border-rose-200',
                                'exported' => 'bg-purple-100 text-purple-700 border-purple-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200'
                            };

                            $moduleIcon = match($log->module) {
                                'vehicles' => 'ti-car',
                                'drivers' => 'ti-steering-wheel',
                                'trips' => 'ti-route',
                                'fuels' => 'ti-gas-station',
                                'maintenances' => 'ti-tool',
                                'penalties' => 'ti-file-alert',
                                'customers' => 'ti-building',
                                'documents' => 'ti-files',
                                'users' => 'ti-users',
                                default => 'ti-box'
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                                        <i class="ti {{ $moduleIcon }} text-xl"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $modules[$log->module] ?? ucfirst($log->module) }}</div>
                                        @php
                                            $actionNames = [
                                                'created' => 'OLUŞTURULDU',
                                                'updated' => 'GÜNCELLENDİ',
                                                'deleted' => 'SİLİNDİ',
                                                'exported' => 'DIŞA AKTARILDI',
                                                'image_uploaded' => 'GÖRSEL EKLENDİ',
                                                'image_deleted' => 'GÖRSEL SİLİNDİ',
                                                'document_uploaded' => 'BELGE EKLENDİ',
                                                'document_deleted' => 'BELGE SİLİNDİ'
                                            ];
                                        @endphp
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border {{ $actionColor }}">
                                                {{ $actionNames[$log->action] ?? strtoupper($log->action) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">{{ $log->title }}</div>
                                <div class="text-slate-500 text-xs mt-1 max-w-md truncate">{{ $log->description }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">{{ $log->user->name ?? 'Sistem / Bot' }}</div>
                                <div class="text-slate-400 text-xs flex items-center gap-1 mt-1">
                                    <i class="ti ti-world"></i> {{ $log->ip_address ?? 'Bilinmiyor' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-slate-900 font-medium">{{ $log->created_at->format('d.m.Y H:i') }}</div>
                                <div class="text-slate-400 text-xs mt-1">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-4">
                                    <i class="ti ti-search text-2xl"></i>
                                </div>
                                <h3 class="text-sm font-semibold text-slate-900">Sonuç Bulunamadı</h3>
                                <p class="text-sm text-slate-500 mt-1">Arama kriterlerinize uygun sistem logu bulunmuyor.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($logs->hasPages())
            <div class="border-t border-slate-200 px-6 py-4 bg-slate-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
