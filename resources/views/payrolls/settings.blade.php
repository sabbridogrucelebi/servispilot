@extends('layouts.app')

@section('title', 'Maaş ve Finansal Ayarlar')
@section('subtitle', 'Sabit maaş (Kabala) anlaşmaları ve özel ödeme tanımları')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <a href="{{ route('payrolls.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Maaş Listesine Dön
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Sol Panel: Açıklama ve Uyarılar -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-[32px] p-8 border border-slate-200 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-[100px] -z-10 transition-transform group-hover:scale-110"></div>
            
            <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-3xl flex items-center justify-center mb-6 shadow-sm border border-blue-200/50">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            
            <h3 class="text-2xl font-black text-slate-900 mb-3 tracking-tight">Sabit Maaş (Kabala) Anlaşması</h3>
            <p class="text-slate-500 font-medium leading-relaxed text-sm">
                Bu listeden "Sadece Ana Maaş" seçeneğini aktif ettiğiniz personeller, gittikleri <strong class="text-slate-900">ekstra seferler için ek hakediş almazlar</strong>. Sistem bu personellerin tüm seferlerini listeler ancak fiyatlandırmalarını otomatik olarak 0 ₺ kabul eder.
            </p>

            <div class="mt-8 space-y-3">
                <div class="flex items-start gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-xs font-bold text-slate-600 leading-snug">Aktif olanlar sadece net belirlediğiniz bazı maaşları alırlar.</p>
                </div>
                <div class="flex items-start gap-3 p-4 rounded-2xl bg-amber-50 border border-amber-100">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <p class="text-xs font-bold text-amber-800 leading-snug">Geçmişe dönük aylarda bu ayarı değiştirirseniz, o ayın kilidini açıp sayfayı yenilemeniz gerekebilir.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sağ Panel: Liste -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-[32px] border border-slate-200 shadow-sm overflow-hidden" x-data="fixedSalaryManager()">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h4 class="text-lg font-black text-slate-900 tracking-tight">Personel Maaş Anlaşmaları</h4>
                    <p class="text-xs font-bold text-slate-500 mt-1">Firmanızdaki aktif şoförlerin listesi</p>
                </div>
                
                <div class="relative">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="search" placeholder="Personel Ara..." class="w-64 pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm placeholder:text-slate-400">
                </div>
            </div>

            <div class="max-h-[600px] overflow-y-auto custom-scrollbar">
                <table class="w-full text-left">
                    <thead class="sticky top-0 bg-slate-900 z-10">
                        <tr>
                            <th class="px-6 py-4 font-black text-white text-xs tracking-widest uppercase">Personel Bilgisi</th>
                            <th class="px-6 py-4 font-black text-white/60 text-xs tracking-widest uppercase text-center">Ana Maaş Seçimi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($drivers as $driver)
                        <tr class="transition-colors hover:bg-slate-50" x-show="matchesSearch('{{ strtolower($driver->full_name) }}')">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center font-black text-slate-500 shadow-inner border border-slate-200">
                                        {{ mb_substr($driver->full_name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-black text-slate-900 text-sm">{{ $driver->full_name }}</div>
                                        <div class="text-[10px] font-bold text-slate-500 mt-1 flex items-center gap-2 uppercase tracking-wide">
                                            <span class="px-1.5 py-0.5 rounded-md bg-slate-100">{{ $driver->vehicle?->plate ?? 'ARAÇSIZ' }}</span>
                                            <span>Maaş: {{ number_format($driver->base_salary, 2, ',', '.') }} ₺</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <label class="relative inline-flex items-center cursor-pointer group" x-data="{ isChecked: {{ $driver->is_fixed_salary ? 'true' : 'false' }} }">
                                    <input type="checkbox" 
                                           class="sr-only peer" 
                                           x-model="isChecked"
                                           @change="toggleStatus({{ $driver->id }}, isChecked)">
                                    <div :class="isChecked ? 'bg-blue-600' : 'bg-slate-200'" class="w-14 h-7 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all shadow-inner group-hover:scale-105 transition-colors duration-300"></div>
                                    <span class="ml-3 text-xs font-black transition-colors duration-300" :class="isChecked ? 'text-blue-600' : 'text-slate-400'">
                                        SADECE ANA MAAŞ
                                    </span>
                                </label>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('fixedSalaryManager', () => ({
            search: '',
            
            matchesSearch(name) {
                if (this.search === '') return true;
                return name.includes(this.search.toLowerCase());
            },

            toggleStatus(driverId, isFixed) {
                fetch('{{ route('payrolls.settings.toggle-fixed-salary') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        driver_id: driverId,
                        is_fixed_salary: isFixed
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if(res.success) {
                        // Alert or toast could be added here
                    }
                })
                .catch(err => {
                    alert('Bir hata oluştu!');
                    window.location.reload();
                });
            }
        }));
    });
</script>
@endsection
