@extends('layouts.app')

@section('title', 'Güzergah Detayı - ' . $route->name)

@section('content')
<div class="h-[calc(100vh-64px)] flex flex-col" x-data="{ showStudentModal: false, activeEditModal: null, showPersonnelUserModal: false, personnelType: '', p_name: '', p_phone: '' }">
    <!-- Üst Başlık -->
    <div class="bg-white border-b border-gray-100 p-6 flex items-center justify-between shadow-sm z-10">
        <div class="flex items-center gap-4">
            <a href="{{ route('pilotcell.school', $school->id) }}" class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 flex shrink-0 items-center justify-center text-indigo-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                    </div>
                    {{ $route->name }}
                    <span class="text-lg text-slate-400 font-normal ml-2">({{ $route->service_no }})</span>
                </h1>
                <p class="text-sm text-slate-500 mt-1 ml-12">{{ $school->company_name }} - {{ $route->vehicle ? $route->vehicle->plate : 'Araç Atanmamış' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('pilotcell.school.routes.users', ['school_id' => $school->id, 'route_id' => $route->id]) }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Kullanıcılar
            </a>
            <button @click="showStudentModal = true" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Yeni Öğrenci Ekle
            </button>
        </div>
    </div>

    <!-- İçerik -->
    <div class="flex-1 bg-slate-50 p-6 overflow-y-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- İstatistik Kartı 1 -->
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div>
                    <div class="text-sm font-medium text-slate-500">Kayıtlı Öğrenci</div>
                    <div class="text-2xl font-bold text-slate-900">{{ $route->students->count() }}</div>
                </div>
            </div>

            <!-- Şoför Kartı -->
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div>
                    <div class="text-sm font-medium text-slate-500">Şoför</div>
                    <div class="text-lg font-bold text-slate-900">{{ $route->driver_name ?? 'Belirtilmedi' }}</div>
                    <div class="text-xs text-slate-400">{{ $route->driver_phone ?? '-' }}</div>
                </div>
            </div>

            <!-- Hostes Kartı -->
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div>
                    <div class="text-sm font-medium text-slate-500">Hostes</div>
                    <div class="text-lg font-bold text-slate-900">{{ $route->hostess_name ?? 'Belirtilmedi' }}</div>
                    <div class="text-xs text-slate-400">{{ $route->hostess_phone ?? '-' }}</div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl bg-emerald-50 p-4 border border-emerald-100 flex items-center gap-3 text-emerald-800">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Alt Alan: Öğrenciler Tablosu -->
        @if($route->students->count() > 0)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Güzergaha Kayıtlı Öğrenciler</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Öğrenci Bilgisi</th>
                                <th class="px-6 py-4 font-semibold">Adres</th>
                                <th class="px-6 py-4 font-semibold">1. Veli</th>
                                <th class="px-6 py-4 font-semibold">2. Veli</th>
                                <th class="px-6 py-4 font-semibold">Aylık Ücret</th>
                                <th class="px-6 py-4 font-semibold text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($route->students as $student)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800">{{ $student->name }}</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">Sınıf: <span class="font-medium text-indigo-600">{{ $student->grade ?? '-' }}</span></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs text-slate-600 max-w-[200px] truncate" title="{{ $student->address }}">{{ $student->address ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($student->parent1_name)
                                            <div class="font-medium text-slate-800">{{ $student->parent1_name }}</div>
                                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $student->parent1_phone ?? '-' }}</div>
                                        @else
                                            <span class="text-slate-400 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($student->parent2_name)
                                            <div class="font-medium text-slate-800">{{ $student->parent2_name }}</div>
                                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $student->parent2_phone ?? '-' }}</div>
                                        @else
                                            <span class="text-slate-400 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-emerald-600">{{ number_format($student->monthly_fee, 2, ',', '.') }} ₺</div>
                                    </td>
                                    <td class="px-6 py-4 flex items-center justify-end gap-2">
                                        <!-- View Details Button -->
                                        <a href="{{ route('pilotcell.school.routes.students.show', ['school_id' => $school->id, 'route_id' => $route->id, 'student_id' => $student->id]) }}" class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Öğrenci Detayı">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>
                                        <!-- Edit Button -->
                                        <button @click="activeEditModal = {{ $student->id }}" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Düzenle">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <!-- Delete Form -->
                                        <form action="{{ route('pilotcell.school.routes.students.destroy', ['school_id' => $school->id, 'route_id' => $route->id, 'student_id' => $student->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Bu öğrenciyi silmek istediğinize emin misiniz? Bu işlem geri alınamaz.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Sil">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Düzenle Modal (Tam Ekran) -->
                                <template x-teleport="body">
                                    <div x-show="activeEditModal === {{ $student->id }}" style="display: none;" class="fixed inset-0 z-[100] bg-slate-50 flex flex-col"
                                         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-10" x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-10">
                                    
                                    <form action="{{ route('pilotcell.school.routes.students.update', ['school_id' => $school->id, 'route_id' => $route->id, 'student_id' => $student->id]) }}" method="POST" class="flex flex-col h-full">
                                        @csrf
                                        @method('PUT')
                                        
                                        <!-- Header -->
                                        <div class="bg-white px-6 py-4 border-b border-slate-200 flex items-center justify-between shrink-0 shadow-sm z-10">
                                            <h3 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                                                <span class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                </span>
                                                Öğrenci Düzenle
                                            </h3>
                                            <div class="flex items-center gap-3">
                                                <button type="button" @click="activeEditModal = null" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                                                    İptal Et
                                                </button>
                                                <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-colors">
                                                    Değişiklikleri Kaydet
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Body -->
                                        <div class="flex-1 overflow-y-auto p-6 md:p-10">
                                            <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
                                                <!-- Sol Kolon: Öğrenci Bilgileri -->
                                                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-5">
                                                    <h4 class="text-sm font-bold text-indigo-600 uppercase tracking-wider flex items-center gap-2 pb-3 border-b border-indigo-50">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                                                        Öğrenci Bilgileri
                                                    </h4>
                                                    
                                                    <div>
                                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Öğrenci Ad Soyad <span class="text-red-500">*</span></label>
                                                        <input type="text" name="name" value="{{ $student->name }}" required class="w-full rounded-xl border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Öğrenci Ad Soyad">
                                                    </div>
                                                    
                                                    <div class="grid grid-cols-2 gap-4">
                                                        <div>
                                                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Sınıfı</label>
                                                            <input type="text" name="grade" value="{{ $student->grade }}" class="w-full rounded-xl border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Örn: 8-A">
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Aylık Servis Ücreti</label>
                                                            <div class="relative">
                                                                <input type="number" step="0.01" name="monthly_fee" value="{{ $student->monthly_fee }}" class="w-full rounded-xl border-slate-200 pl-4 pr-8 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="0.00">
                                                                <span class="absolute right-4 top-3 text-slate-400 font-bold">₺</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div>
                                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Adresi</label>
                                                        <textarea name="address" rows="3" class="w-full rounded-xl border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm resize-none" placeholder="Açık Adres Bilgisi...">{{ $student->address }}</textarea>
                                                    </div>
                                                </div>
                                                
                                                <!-- Sağ Kolon: Veli Bilgileri -->
                                                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-5">
                                                    <h4 class="text-sm font-bold text-emerald-600 uppercase tracking-wider flex items-center gap-2 pb-3 border-b border-emerald-50">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                                        Veli Bilgileri
                                                    </h4>
                                                    
                                                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-4">
                                                        <div>
                                                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">1. Veli Ad Soyad</label>
                                                            <input type="text" name="parent1_name" value="{{ $student->parent1_name }}" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" placeholder="1. Veli Ad Soyad">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">1. Veli Cep Telefonu</label>
                                                            <input type="text" name="parent1_phone" value="{{ $student->parent1_phone }}" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" placeholder="05XX XXX XX XX">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-4">
                                                        <div>
                                                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">2. Veli Ad Soyad</label>
                                                            <input type="text" name="parent2_name" value="{{ $student->parent2_name }}" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" placeholder="2. Veli Ad Soyad">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">2. Veli Cep Telefonu</label>
                                                            <input type="text" name="parent2_phone" value="{{ $student->parent2_phone }}" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" placeholder="05XX XXX XX XX">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                </template>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Henüz Öğrenci Eklenmemiş</h3>
                <p class="text-sm text-slate-500 mt-2 max-w-md mx-auto mb-6">Bu güzergaha henüz kayıtlı öğrenci bulunmuyor. Hemen yeni öğrenci ekleyerek listeyi oluşturmaya başlayabilirsiniz.</p>
                <button @click="showStudentModal = true" class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-2.5 text-sm font-bold text-emerald-700 hover:bg-emerald-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Yeni Öğrenci Ekle
                </button>
            </div>
        @endif
    </div>

    <!-- Yeni Öğrenci Ekle Modal (Tam Ekran) -->
    <template x-teleport="body">
        <div x-show="showStudentModal" style="display: none;" class="fixed inset-0 z-[100] bg-slate-50 flex flex-col"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-10" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-10">
        
        <form action="{{ route('pilotcell.school.routes.students.store', ['school_id' => $school->id, 'route_id' => $route->id]) }}" method="POST" class="flex flex-col h-full">
            @csrf
            
            <!-- Header -->
            <div class="bg-white px-6 py-4 border-b border-slate-200 flex items-center justify-between shrink-0 shadow-sm z-10">
                <h3 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </span>
                    Güzergaha Yeni Öğrenci Ekle
                </h3>
                <div class="flex items-center gap-3">
                    <button type="button" @click="showStudentModal = false" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                        İptal Et
                    </button>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 transition-colors">
                        Öğrenciyi Kaydet
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="flex-1 overflow-y-auto p-6 md:p-10">
                <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Sol Kolon: Öğrenci Bilgileri -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-5">
                        <h4 class="text-sm font-bold text-indigo-600 uppercase tracking-wider flex items-center gap-2 pb-3 border-b border-indigo-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                            Öğrenci Bilgileri
                        </h4>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Öğrenci Ad Soyad <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required class="w-full rounded-xl border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Öğrenci Ad Soyad">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Sınıfı</label>
                                <input type="text" name="grade" class="w-full rounded-xl border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Örn: 8-A">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Aylık Servis Ücreti</label>
                                <div class="relative">
                                    <input type="number" step="0.01" name="monthly_fee" class="w-full rounded-xl border-slate-200 pl-4 pr-8 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="0.00">
                                    <span class="absolute right-4 top-3 text-slate-400 font-bold">₺</span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Adresi</label>
                            <textarea name="address" rows="3" class="w-full rounded-xl border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm resize-none" placeholder="Açık Adres Bilgisi..."></textarea>
                        </div>
                    </div>
                    
                    <!-- Sağ Kolon: Veli Bilgileri -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-5">
                        <h4 class="text-sm font-bold text-emerald-600 uppercase tracking-wider flex items-center gap-2 pb-3 border-b border-emerald-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Veli Bilgileri
                        </h4>
                        
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">1. Veli Ad Soyad</label>
                                <input type="text" name="parent1_name" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" placeholder="1. Veli Ad Soyad">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">1. Veli Cep Telefonu</label>
                                <input type="text" name="parent1_phone" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" placeholder="05XX XXX XX XX">
                            </div>
                        </div>
                        
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">2. Veli Ad Soyad</label>
                                <input type="text" name="parent2_name" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" placeholder="2. Veli Ad Soyad">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">2. Veli Cep Telefonu</label>
                                <input type="text" name="parent2_phone" class="w-full rounded-lg border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" placeholder="05XX XXX XX XX">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    </template>

</div>
@endsection
