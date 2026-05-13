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

        <!-- Logo Yükleme Alanı -->
        <div class="bg-white rounded-[32px] p-8 border border-slate-200 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-bl-[100px] -z-10 transition-transform group-hover:scale-110"></div>
            
            <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-3xl flex items-center justify-center mb-6 shadow-sm border border-indigo-200/50">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            
            <h3 class="text-2xl font-black text-slate-900 mb-3 tracking-tight">Firma Logosu</h3>
            <p class="text-slate-500 font-medium leading-relaxed text-sm mb-6">
                Yüklediğiniz logo, personellerin yazdırılan maaş hakediş dökümlerinde ve pdf çıktılarında firma adınızın yerine şık bir şekilde gösterilecektir.
            </p>

            <form action="{{ route('payrolls.settings.upload-logo') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="relative group/upload cursor-pointer">
                    <input type="file" name="logo" id="logo" accept="image/png, image/jpeg, image/jpg, image/svg+xml" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" required onchange="this.form.submit()">
                    <div class="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center transition-all group-hover/upload:border-indigo-500 group-hover/upload:bg-indigo-50/50">
                        <svg class="w-8 h-8 text-slate-400 mx-auto mb-3 group-hover/upload:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <p class="text-sm font-bold text-slate-700">Logo Seçmek İçin Tıklayın</p>
                        <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">Önerilen: 600x200 px (PNG, JPG, SVG)</p>
                    </div>
                </div>
            </form>

            @if(auth()->user()->company->logo_path)
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Mevcut Logo:</p>
                    <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <img src="{{ Storage::url(auth()->user()->company->logo_path) }}" alt="Logo" class="max-h-10 object-contain">
                        <form action="{{ route('payrolls.settings.remove-logo') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-rose-500 hover:bg-rose-100 rounded-lg transition-colors" title="Logoyu Kaldır">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endif
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
                        <tr class="transition-colors hover:bg-slate-50" x-show="matchesSearch('{{ addslashes($driver->full_name) }}', '{{ addslashes($driver->vehicle?->plate ?? '') }}')">
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
            
            matchesSearch(name, plate) {
                if (this.search.trim() === '') return true;
                const s = this.search.toLocaleLowerCase('tr-TR');
                const n = name.toLocaleLowerCase('tr-TR');
                const p = plate.toLocaleLowerCase('tr-TR');
                return n.includes(s) || p.includes(s);
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
