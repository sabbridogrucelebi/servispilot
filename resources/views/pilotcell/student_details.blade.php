@extends('layouts.app')

@section('title', 'Öğrenci Detayı - ' . $student->name)

@section('content')
<div class="min-h-[calc(100vh-64px)] bg-slate-50 flex flex-col" x-data="debtManager({{ $student->monthly_fee ?? 0 }})">
    <!-- Üst Başlık -->
    <div class="bg-white border-b border-gray-100 p-6 flex items-center justify-between shadow-sm z-10">
        <div class="flex items-center gap-4">
            <a href="{{ route('pilotcell.school.routes.show', ['school_id' => $school->id, 'route_id' => $route->id]) }}" class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-100 flex shrink-0 items-center justify-center text-emerald-600 font-bold text-lg">
                        {{ mb_substr($student->name, 0, 1) }}
                    </div>
                    {{ $student->name }}
                </h1>
                <p class="text-sm text-slate-500 mt-1 ml-14">{{ $school->company_name }} &bull; {{ $route->name }} ({{ $route->service_no }})</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif Kayıt
            </span>
            <a href="{{ route('pilotcell.school.routes.students.users', ['school_id' => $school->id, 'route_id' => $route->id, 'student_id' => $student->id]) }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-200 transition-colors border border-slate-200">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Kullanıcılar
            </a>
            <button @click="showDebtModal = true" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Borçlandır
            </button>
        </div>
    </div>

    <!-- İçerik -->
    <div class="flex-1 p-6 lg:p-8 max-w-7xl mx-auto w-full">
        
        @if(session('success'))
            <div class="mb-6 rounded-xl bg-emerald-50 p-4 border border-emerald-100 flex items-center gap-3 text-emerald-800">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Sol Kolon: Öğrenci Temel Bilgileri -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Profil Özeti -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="h-24 bg-gradient-to-r from-emerald-500 to-teal-400"></div>
                    <div class="px-6 pb-6 relative">
                        <div class="w-20 h-20 rounded-2xl bg-white border-4 border-white shadow-md flex items-center justify-center text-3xl font-bold text-emerald-600 -mt-10 mb-4 mx-auto">
                            {{ mb_substr($student->name, 0, 1) }}
                        </div>
                        <div class="text-center">
                            <h2 class="text-xl font-bold text-slate-800">{{ $student->name }}</h2>
                            <p class="text-sm text-slate-500 font-medium mt-1">Sınıf: {{ $student->grade ?? 'Belirtilmedi' }}</p>
                            <p class="text-xs text-slate-400 mt-1">{{ $student->student_no ? 'Öğrenci No: '.$student->student_no : '' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Finansal Özet -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Finansal Bilgiler
                    </h3>
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 text-center">
                        <div class="text-xs text-slate-500 font-medium mb-1">Aylık Servis Ücreti</div>
                        <div class="text-3xl font-bold text-emerald-600">{{ number_format($student->monthly_fee, 2, ',', '.') }} <span class="text-lg">₺</span></div>
                    </div>
                </div>
            </div>

            <!-- Sağ Kolon: İletişim ve Veli Bilgileri -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Adres Bilgileri -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Adres ve İletişim
                    </h3>
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <p class="text-slate-700 leading-relaxed">{{ $student->address ?? 'Adres bilgisi girilmemiş.' }}</p>
                        @if($student->phone)
                            <div class="mt-4 pt-4 border-t border-slate-200 flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                <span class="text-sm font-medium text-slate-700">Öğrenci Telefonu: {{ $student->phone }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Veli Bilgileri -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Veli Bilgileri
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- 1. Veli -->
                        <div class="p-4 border border-slate-100 rounded-xl bg-white shadow-sm hover:border-amber-200 transition-colors">
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">1. Veli</div>
                            @if($student->parent1_name)
                                <div class="font-bold text-slate-800 text-lg mb-1">{{ $student->parent1_name }}</div>
                                <div class="flex items-center gap-2 text-slate-600 text-sm">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    {{ $student->parent1_phone ?? 'Telefon belirtilmedi' }}
                                </div>
                            @else
                                <div class="text-sm text-slate-500 italic py-2">1. Veli bilgisi girilmemiş.</div>
                            @endif
                        </div>

                        <!-- 2. Veli -->
                        <div class="p-4 border border-slate-100 rounded-xl bg-white shadow-sm hover:border-amber-200 transition-colors">
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">2. Veli</div>
                            @if($student->parent2_name)
                                <div class="font-bold text-slate-800 text-lg mb-1">{{ $student->parent2_name }}</div>
                                <div class="flex items-center gap-2 text-slate-600 text-sm">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    {{ $student->parent2_phone ?? 'Telefon belirtilmedi' }}
                                </div>
                            @else
                                <div class="text-sm text-slate-500 italic py-2">2. Veli bilgisi girilmemiş.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Araç ve Servis Bilgileri -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        Araç ve Servis Bilgileri
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <!-- Servis Detayı -->
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Güzergah & Servis No</div>
                            <div class="font-bold text-slate-800">{{ $route->name }}</div>
                            <div class="text-sm text-slate-500 mt-0.5">Servis No: <span class="font-semibold text-slate-700">{{ $route->service_no }}</span></div>
                            <div class="text-sm text-slate-500 mt-1 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                                Plaka: <span class="font-bold text-slate-700">{{ $route->vehicle->plate ?? 'Belirtilmedi' }}</span>
                            </div>
                        </div>

                        <!-- Şoför -->
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Şoför</div>
                            <div class="font-bold text-slate-800">{{ $route->driver_name ?? ($route->driver->user->name ?? 'Belirtilmedi') }}</div>
                            <div class="flex items-center gap-2 text-slate-600 text-sm mt-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                {{ $route->driver_phone ?? 'Telefon belirtilmedi' }}
                            </div>
                        </div>

                        <!-- Hostes -->
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Rehber / Hostes</div>
                            <div class="font-bold text-slate-800">{{ $route->hostess_name ?: 'Belirtilmedi' }}</div>
                            <div class="flex items-center gap-2 text-slate-600 text-sm mt-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                {{ $route->hostess_phone ?: 'Telefon belirtilmedi' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Borçlar ve Ödemeler Tablosu -->
        <div class="mt-8 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            @php
                $totalDebt = $student->debts->sum('amount');
                $totalPaid = $student->debts->sum('paid_amount');
                $totalRemaining = max(0, $totalDebt - $totalPaid);
            @endphp
            
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row justify-between items-center gap-4">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Aylık Servis Ücretleri / Borçlandırma Tablosu
                </h3>
                
                @if($student->debts->count() > 0)
                <div class="flex items-center gap-3 bg-white p-2 rounded-xl shadow-sm border border-slate-100">
                    <div class="px-3 text-center">
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Toplam Borç</div>
                        <div class="font-bold text-slate-700">{{ number_format($totalDebt, 2, ',', '.') }} ₺</div>
                    </div>
                    <div class="w-px h-8 bg-slate-100"></div>
                    <div class="px-3 text-center">
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Toplam Tahsilat</div>
                        <div class="font-bold text-emerald-600">{{ number_format($totalPaid, 2, ',', '.') }} ₺</div>
                    </div>
                    <div class="w-px h-8 bg-slate-100"></div>
                    <div class="px-3 text-center">
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Kalan Bakiye</div>
                        <div class="font-bold text-red-500">{{ number_format($totalRemaining, 2, ',', '.') }} ₺</div>
                    </div>
                </div>
                @endif
            </div>
            
            @if($student->debts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Dönem / Ay</th>
                                <th class="px-6 py-4 font-semibold text-right">Borç Tutarı</th>
                                <th class="px-6 py-4 font-semibold text-right">Tahsil Edilen</th>
                                <th class="px-6 py-4 font-semibold text-right">Kalan Borç</th>
                                <th class="px-6 py-4 font-semibold text-center">Durum</th>
                                <th class="px-6 py-4 font-semibold text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($student->debts as $debt)
                                <tr class="hover:bg-slate-50 transition-colors" x-data="{ showPaymentForm: false }">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800">{{ $debt->month_name }} {{ $debt->year }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="font-bold text-slate-400">{{ number_format($debt->amount, 2, ',', '.') }} ₺</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="font-bold text-emerald-600">{{ number_format($debt->paid_amount, 2, ',', '.') }} ₺</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="font-bold text-red-500">{{ number_format(max(0, $debt->amount - $debt->paid_amount), 2, ',', '.') }} ₺</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($debt->is_paid)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                Ödendi
                                            </span>
                                        @elseif($debt->paid_amount > 0)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                                Eksik Ödendi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700 ring-1 ring-inset ring-red-600/20">
                                                Ödenmedi
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right relative">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="showPaymentForm = !showPaymentForm" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                                Tahsilat Gir
                                            </button>
                                            
                                            <form action="{{ route('pilotcell.school.routes.students.debts.destroy', ['school_id' => $school->id, 'route_id' => $route->id, 'student_id' => $student->id, 'debt_id' => $debt->id]) }}" method="POST" onsubmit="return confirm('Bu ayın borçlandırma kaydını iptal etmek (silmek) istediğinize emin misiniz?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Borcu Sil / İptal Et">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                        
                                        <!-- Tahsilat Formu -->
                                        <div x-show="showPaymentForm" @click.away="showPaymentForm = false" style="display:none;" class="absolute right-6 top-14 z-20 w-64 rounded-xl bg-white p-4 shadow-xl ring-1 ring-slate-900/5 text-left">
                                            <form action="{{ route('pilotcell.school.routes.students.debts.payment', ['school_id' => $school->id, 'route_id' => $route->id, 'student_id' => $student->id, 'debt_id' => $debt->id]) }}" method="POST">
                                                @csrf
                                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Ödenen Toplam Tutar (₺)</label>
                                                <div class="relative mb-3">
                                                    <input type="number" step="0.01" name="paid_amount" value="{{ $debt->paid_amount > 0 ? $debt->paid_amount : $debt->amount }}" class="w-full rounded-lg border-slate-200 pl-3 pr-8 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                                    <span class="absolute right-3 top-2 text-slate-400 font-bold text-sm">₺</span>
                                                </div>
                                                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold text-white hover:bg-indigo-700 transition-colors">
                                                    Tahsilatı Kaydet
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center text-slate-500">
                    <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <p class="font-medium">Henüz bu öğrenci için herhangi bir borçlandırma yapılmamış.</p>
                    <p class="text-sm mt-1">Yukarıdaki "Borçlandır" butonunu kullanarak aylık ücretleri tanımlayabilirsiniz.</p>
                </div>
            @endif
        </div>
    </div>
    <!-- End of main flex content -->

<!-- Borçlandır Modal -->
<div x-show="showDebtModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div x-show="showDebtModal" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
             class="fixed inset-0 transition-opacity" @click="showDebtModal = false" aria-hidden="true">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        </div>

        <!-- Modal panel -->
        <div x-show="showDebtModal" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="inline-block transform overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle border border-slate-100">
            
            <form action="{{ route('pilotcell.school.routes.students.debts.store', ['school_id' => $school->id, 'route_id' => $route->id, 'student_id' => $student->id]) }}" method="POST">
                @csrf
                
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </span>
                        Öğrenciyi Borçlandır
                    </h3>
                    <button type="button" @click="showDebtModal = false" class="text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 rounded-lg p-2 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="bg-slate-50 px-6 py-5 border-b border-slate-100 flex items-center gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Eğitim Yılı</label>
                        <select name="year" required class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm font-semibold focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                            <option value="{{ date('Y')+1 }}">{{ date('Y')+1 }}</option>
                            <option value="{{ date('Y')-1 }}">{{ date('Y')-1 }}</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-indigo-700 mb-1.5">Standart Aylık Ücret</label>
                        <div class="relative">
                            <input type="number" step="0.01" x-model="baseAmount" @input="updateAllSelected()" class="w-full rounded-xl border-indigo-200 bg-indigo-50/50 pl-4 pr-8 py-2.5 text-sm font-bold text-indigo-900 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <span class="absolute right-3 top-2.5 text-indigo-400 font-bold">₺</span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">Burayı değiştirdiğinizde seçili tüm aylar otomatik güncellenir.</p>
                    </div>
                </div>

                <div class="bg-white px-6 py-4 overflow-y-auto max-h-[50vh]">
                    <p class="text-sm font-semibold text-slate-600 mb-4 pb-2 border-b border-slate-100">Borçlandırılacak ayları seçip gerekirse tutarları ay bazında özelleştirebilirsiniz:</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3">
                        <template x-for="(month, index) in months" :key="index">
                            <div class="flex items-center gap-3 p-3 rounded-xl border transition-colors" :class="month.selected ? 'border-indigo-200 bg-indigo-50/30' : 'border-slate-100 bg-slate-50/50 hover:bg-slate-50'">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" :name="'months[]'" :value="index" x-model="month.selected" @change="handleCheckboxChange(index)" class="w-5 h-5 text-indigo-600 bg-white border-slate-300 rounded focus:ring-indigo-600 focus:ring-2">
                                </div>
                                <div class="w-20 font-bold" :class="month.selected ? 'text-indigo-900' : 'text-slate-500'" x-text="month.name"></div>
                                <div class="flex-1 transition-opacity" :class="month.selected ? 'opacity-100' : 'opacity-30 pointer-events-none'">
                                    <div class="relative">
                                        <input type="number" step="0.01" :name="month.selected ? 'amounts['+index+']' : ''" x-model="month.amount" class="w-full rounded-lg border-slate-200 pl-3 pr-7 py-1.5 text-sm font-semibold focus:border-indigo-500 focus:ring-indigo-500">
                                        <span class="absolute right-2 top-1.5 text-slate-400 text-xs font-bold">₺</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                
                <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="showDebtModal = false" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-200 transition-colors">
                        İptal
                    </button>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-colors">
                        Borçlandırmayı Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('debtManager', (initialAmount) => ({
            showDebtModal: false,
            baseAmount: initialAmount,
            months: {
                1: { selected: false, amount: initialAmount, name: 'Ocak' },
                2: { selected: false, amount: initialAmount, name: 'Şubat' },
                3: { selected: false, amount: initialAmount, name: 'Mart' },
                4: { selected: false, amount: initialAmount, name: 'Nisan' },
                5: { selected: false, amount: initialAmount, name: 'Mayıs' },
                6: { selected: false, amount: initialAmount, name: 'Haziran' },
                7: { selected: false, amount: initialAmount, name: 'Temmuz' },
                8: { selected: false, amount: initialAmount, name: 'Ağustos' },
                9: { selected: false, amount: initialAmount, name: 'Eylül' },
                10: { selected: false, amount: initialAmount, name: 'Ekim' },
                11: { selected: false, amount: initialAmount, name: 'Kasım' },
                12: { selected: false, amount: initialAmount, name: 'Aralık' },
            },
            
            handleCheckboxChange(index) {
                if (this.months[index].selected) {
                    this.months[index].amount = this.baseAmount;
                }
            },
            
            updateAllSelected() {
                for (let i in this.months) {
                    if (this.months[i].selected) {
                        this.months[i].amount = this.baseAmount;
                    }
                }
            }
        }));
    });
</script>
</div>
@endsection
