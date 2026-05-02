@extends('layouts.app')

@section('title', 'Okul Detayı - ' . $school->company_name)

@section('content')
<div class="h-[calc(100vh-64px)] flex flex-col" x-data="{ showRouteModal: false }">
    <!-- Üst Başlık -->
    <div class="bg-white border-b border-gray-100 p-6 flex items-center justify-between shadow-sm z-10">
        <div class="flex items-center gap-4">
            <a href="{{ route('pilotcell.dashboard') }}" class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 flex shrink-0 items-center justify-center text-indigo-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                    </div>
                    {{ $school->company_name }}
                </h1>
                <p class="text-sm text-slate-500 mt-1 ml-12">Okul / Kurum Öğrenci Taşıma Takip Ekranı</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showRouteModal = true" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Yeni Güzergah
            </button>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-bold text-emerald-700 border border-emerald-100">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Sistem Aktif
            </span>
        </div>
    </div>

    <!-- İçerik -->
    <div class="flex-1 bg-slate-50 p-6 overflow-y-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- İstatistik Kartı 1 -->
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <div class="text-sm font-medium text-slate-500">Kayıtlı Öğrenci</div>
                    <div class="text-2xl font-bold text-slate-900">0</div>
                </div>
            </div>

            <!-- İstatistik Kartı 2 -->
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                </div>
                <div>
                    <div class="text-sm font-medium text-slate-500">Aktif Sefer</div>
                    <div class="text-2xl font-bold text-slate-900">0</div>
                </div>
            </div>

            <!-- İstatistik Kartı 3 -->
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <div class="text-sm font-medium text-slate-500">Güzergah Sayısı</div>
                    <div class="text-2xl font-bold text-slate-900">{{ $routes->count() }}</div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl bg-emerald-50 p-4 border border-emerald-100 flex items-center gap-3 text-emerald-800">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Alt Alan: Seferler ve Harita vs gelebilir -->
        @if($routes->count() > 0)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Tanımlı Güzergahlar</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Servis No</th>
                                <th class="px-6 py-4 font-semibold">Güzergah Adı</th>
                                <th class="px-6 py-4 font-semibold">Araç</th>
                                <th class="px-6 py-4 font-semibold">Şoför Bilgisi</th>
                                <th class="px-6 py-4 font-semibold">Hostes Bilgisi</th>
                                <th class="px-6 py-4 font-semibold text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($routes as $route)
                                <tr class="hover:bg-slate-50 transition-colors" x-data="{ showEditModal_{{ $route->id }}: false }">
                                    <td class="px-6 py-4 font-bold text-indigo-600">
                                        {{ $route->service_no ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-slate-800">
                                        {{ $route->name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($route->vehicle)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-medium text-xs border border-slate-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                                {{ $route->vehicle->plate }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-xs">Belirtilmedi</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($route->driver_name)
                                            <div class="font-medium text-slate-800">{{ $route->driver_name }}</div>
                                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $route->driver_phone ?? '-' }}</div>
                                        @else
                                            <span class="text-slate-400 text-xs">Belirtilmedi</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($route->hostess_name)
                                            <div class="font-medium text-slate-800">{{ $route->hostess_name }}</div>
                                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $route->hostess_phone ?? '-' }}</div>
                                        @else
                                            <span class="text-slate-400 text-xs">Belirtilmedi</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 flex items-center justify-end gap-2">
                                        <!-- View Details Button -->
                                        <a href="{{ route('pilotcell.school.routes.show', ['school_id' => $school->id, 'route_id' => $route->id]) }}" class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Güzergah Detayı">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>
                                        <!-- Edit Button -->
                                        <button @click="showEditModal_{{ $route->id }} = true" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Düzenle">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <!-- Delete Form -->
                                        <form action="{{ route('pilotcell.school.routes.destroy', ['school_id' => $school->id, 'route_id' => $route->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Bu güzergahı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Sil">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit Modal for this specific route -->
                                <div x-show="showEditModal_{{ $route->id }}" style="display: none;" class="fixed inset-0 z-[60] overflow-y-auto">
                                    <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                        <!-- Background overlay -->
                                        <div x-show="showEditModal_{{ $route->id }}" 
                                             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
                                             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
                                             class="fixed inset-0 transition-opacity" @click="showEditModal_{{ $route->id }} = false" aria-hidden="true">
                                            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
                                        </div>

                                        <!-- Modal panel -->
                                        <div x-show="showEditModal_{{ $route->id }}" 
                                             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                                             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                                             class="inline-block transform overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle border border-slate-100">
                                            
                                            <form action="{{ route('pilotcell.school.routes.update', ['school_id' => $school->id, 'route_id' => $route->id]) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                
                                                <div class="bg-white px-6 pt-6 pb-4">
                                                    <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-4">
                                                        <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                                                            <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                            </span>
                                                            Güzergahı Düzenle
                                                        </h3>
                                                        <button type="button" @click="showEditModal_{{ $route->id }} = false" class="text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 rounded-lg p-2 transition-colors">
                                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    </div>
                                                    
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                                        <!-- Temel Bilgiler -->
                                                        <div class="col-span-1 md:col-span-2 bg-slate-50 p-4 rounded-xl space-y-4">
                                                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Güzergah & Araç</h4>
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                <div>
                                                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Servis No <span class="text-red-500">*</span></label>
                                                                    <input type="text" name="service_no" value="{{ $route->service_no }}" required class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Örn: S-01">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Güzergah Adı <span class="text-red-500">*</span></label>
                                                                    <input type="text" name="name" value="{{ $route->name }}" required class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Örn: Çankaya - Okul">
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Araç Seçimi <span class="text-red-500">*</span></label>
                                                                <select name="vehicle_id" required class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                                                    <option value="">Araç Seçin</option>
                                                                    @foreach($vehicles as $vehicle)
                                                                        <option value="{{ $vehicle->id }}" {{ $route->vehicle_id == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Şoför Bilgileri -->
                                                        <div class="bg-indigo-50/50 p-4 rounded-xl space-y-4 border border-indigo-50">
                                                            <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                                Şoför Bilgileri
                                                            </h4>
                                                            <div>
                                                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Ad Soyad</label>
                                                                <input type="text" name="driver_name" value="{{ $route->driver_name }}" class="w-full rounded-xl border-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Şoför Ad Soyad">
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Telefon</label>
                                                                <input type="text" name="driver_phone" value="{{ $route->driver_phone }}" class="w-full rounded-xl border-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="05XX XXX XX XX">
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Hostes Bilgileri -->
                                                        <div class="bg-emerald-50/50 p-4 rounded-xl space-y-4 border border-emerald-50">
                                                            <h4 class="text-xs font-bold text-emerald-600 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                                                Hostes Bilgileri
                                                            </h4>
                                                            <div>
                                                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Ad Soyad</label>
                                                                <input type="text" name="hostess_name" value="{{ $route->hostess_name }}" class="w-full rounded-xl border-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Hostes Ad Soyad">
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Telefon</label>
                                                                <input type="text" name="hostess_phone" value="{{ $route->hostess_phone }}" class="w-full rounded-xl border-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="05XX XXX XX XX">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100">
                                                    <button type="button" @click="showEditModal_{{ $route->id }} = false" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-200 transition-colors">
                                                        İptal
                                                    </button>
                                                    <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-colors">
                                                        Değişiklikleri Kaydet
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Henüz Güzergah Tanımlanmamış</h3>
                <p class="text-sm text-slate-500 mt-2 max-w-md mx-auto mb-6">Bu okul için henüz herhangi bir öğrenci taşıma güzergahı (servis) tanımlanmamış. Hemen yeni bir güzergah ekleyerek işlemlere başlayabilirsiniz.</p>
                <button @click="showRouteModal = true" class="inline-flex items-center gap-2 rounded-xl bg-indigo-50 px-4 py-2.5 text-sm font-bold text-indigo-700 hover:bg-indigo-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Yeni Güzergah Tanımla
                </button>
            </div>
        @endif
    </div>

    <!-- Yeni Güzergah Modal -->
    <div x-show="showRouteModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showRouteModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 transition-opacity" 
                 @click="showRouteModal = false"
                 aria-hidden="true">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <!-- Modal panel -->
            <div x-show="showRouteModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block transform overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle border border-slate-100">
                
                <form action="{{ route('pilotcell.school.routes.store', $school->id) }}" method="POST">
                    @csrf
                    
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-4">
                            <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                </span>
                                Yeni Güzergah Ekle
                            </h3>
                            <button type="button" @click="showRouteModal = false" class="text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 rounded-lg p-2 transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Temel Bilgiler -->
                            <div class="col-span-1 md:col-span-2 bg-slate-50 p-4 rounded-xl space-y-4">
                                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Güzergah & Araç</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Servis No <span class="text-red-500">*</span></label>
                                        <input type="text" name="service_no" required class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Örn: S-01">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Güzergah Adı <span class="text-red-500">*</span></label>
                                        <input type="text" name="name" required class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Örn: Çankaya - Okul">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Araç Seçimi <span class="text-red-500">*</span></label>
                                    <select name="vehicle_id" required class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                        <option value="">Araç Seçin</option>
                                        @foreach($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}">{{ $vehicle->plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Şoför Bilgileri -->
                            <div class="bg-indigo-50/50 p-4 rounded-xl space-y-4 border border-indigo-50">
                                <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    Şoför Bilgileri
                                </h4>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Ad Soyad</label>
                                    <input type="text" name="driver_name" class="w-full rounded-xl border-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Şoför Ad Soyad">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Telefon</label>
                                    <input type="text" name="driver_phone" class="w-full rounded-xl border-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="05XX XXX XX XX">
                                </div>
                            </div>
                            
                            <!-- Hostes Bilgileri -->
                            <div class="bg-emerald-50/50 p-4 rounded-xl space-y-4 border border-emerald-50">
                                <h4 class="text-xs font-bold text-emerald-600 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    Hostes Bilgileri
                                </h4>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Ad Soyad</label>
                                    <input type="text" name="hostess_name" class="w-full rounded-xl border-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Hostes Ad Soyad">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Telefon</label>
                                    <input type="text" name="hostess_phone" class="w-full rounded-xl border-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="05XX XXX XX XX">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="showRouteModal = false" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-200 transition-colors">
                            İptal
                        </button>
                        <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-colors">
                            Güzergahı Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
