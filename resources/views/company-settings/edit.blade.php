@extends('layouts.app')

@section('title', 'Firma Ayarları')
@section('subtitle', 'Kurumsal kimlik ve sistem yapılandırması')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">

    @if(session('success'))
        <div class="animate-in fade-in slide-in-from-top duration-500 rounded-3xl border border-emerald-200 bg-emerald-50/80 backdrop-blur-sm px-6 py-4 text-sm font-bold text-emerald-700 shadow-xl flex items-center gap-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('company-settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Sidebar: Section Info -->
            <div class="lg:col-span-1 space-y-8">
                <div class="sticky top-8">
                    <div class="group relative overflow-hidden rounded-[35px] bg-slate-900 p-8 text-white shadow-2xl transition-all hover:scale-[1.02]">
                        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-indigo-500/10 blur-3xl"></div>
                        <div class="absolute -left-10 -bottom-10 h-40 w-40 rounded-full bg-purple-500/10 blur-3xl"></div>
                        
                        <div class="relative">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 text-2xl shadow-xl mb-6">
                                🏢
                            </div>
                            <h3 class="text-xl font-black tracking-tight">Firma Profili</h3>
                            <p class="mt-3 text-sm font-medium text-slate-400 leading-relaxed">
                                Kurumsal bilgileriniz sistem genelindeki faturalarda, raporlarda ve iletişim kanallarında kullanılacaktır.
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 grid grid-cols-2 gap-4">
                        <div class="rounded-[24px] bg-white border border-slate-100 p-4 shadow-sm">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Son Güncelleme</div>
                            <div class="mt-1 text-xs font-bold text-slate-800">{{ $company->updated_at?->diffForHumans() ?? 'Yeni' }}</div>
                        </div>
                        <div class="rounded-[24px] bg-white border border-slate-100 p-4 shadow-sm">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Durum</div>
                            <div class="mt-1 flex items-center gap-1.5">
                                <div class="h-1.5 w-1.5 rounded-full {{ $company->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300' }}"></div>
                                <span class="text-xs font-bold text-slate-800">{{ $company->is_active ? 'Aktif' : 'Pasif' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Content: Form Sections -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Section: Temel Bilgiler -->
                <div class="rounded-[35px] border border-white bg-white/60 backdrop-blur-xl shadow-2xl shadow-slate-200/50 overflow-hidden transition-all hover:shadow-indigo-100/40">
                    <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h4 class="text-lg font-black text-slate-800 tracking-tight">Temel Bilgiler</h4>
                        </div>
                    </div>

                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 ml-1">Firma Adı</label>
                            <div class="relative">
                                <input type="text" name="name" value="{{ old('name', $company->name) }}" required
                                       class="w-full rounded-2xl border-slate-200 bg-white/50 px-5 py-3.5 text-sm font-bold text-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none shadow-sm"
                                       placeholder="Resmi firma adını giriniz">
                            </div>
                            @error('name') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 uppercase tracking-wide">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between ml-1">
                                <label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Firma Kısa Adı (Slug)</label>
                                @if(!auth()->user()->isSuperAdmin())
                                    <span class="flex items-center gap-1 text-[9px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-100">
                                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                                        Yalnızca Süper Admin Değiştirebilir
                                    </span>
                                @endif
                            </div>
                            <div class="relative">
                                <input type="text" name="slug" value="{{ old('slug', $company->slug) }}"
                                       @if(!auth()->user()->isSuperAdmin()) readonly @endif
                                       class="w-full rounded-2xl border-slate-200 bg-white/50 px-5 py-3.5 text-sm font-bold text-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none shadow-sm {{ !auth()->user()->isSuperAdmin() ? 'cursor-not-allowed opacity-75 bg-slate-50' : '' }}"
                                       placeholder="firma-adi">
                            </div>
                            @error('slug') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 uppercase tracking-wide">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 ml-1">Vergi Numarası</label>
                            <div class="relative">
                                <input type="text" name="tax_no" value="{{ old('tax_no', $company->tax_no) }}"
                                       class="w-full rounded-2xl border-slate-200 bg-white/50 px-5 py-3.5 text-sm font-bold text-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none shadow-sm"
                                       placeholder="Vergi dairesi ve no">
                            </div>
                            @error('tax_no') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 uppercase tracking-wide">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 ml-1">Platform Durumu</label>
                            <div class="flex items-center gap-4 h-[54px] px-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $company->is_active) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-500/10 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 shadow-inner"></div>
                                    <span class="ml-3 text-sm font-bold text-slate-600">Sistem Erişimi Aktif</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: İletişim Bilgileri -->
                <div class="rounded-[35px] border border-white bg-white/60 backdrop-blur-xl shadow-2xl shadow-slate-200/50 overflow-hidden transition-all hover:shadow-indigo-100/40">
                    <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100 text-purple-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <h4 class="text-lg font-black text-slate-800 tracking-tight">İletişim Bilgileri</h4>
                        </div>
                    </div>

                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 ml-1">Telefon</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors">📞</span>
                                <input type="text" name="phone" value="{{ old('phone', $company->phone) }}"
                                       class="w-full rounded-2xl border-slate-200 bg-white/50 pl-12 pr-5 py-3.5 text-sm font-bold text-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none shadow-sm"
                                       placeholder="Örn: 05xx xxx xx xx">
                            </div>
                            @error('phone') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 uppercase tracking-wide">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 ml-1">E-posta Adresi</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors">✉️</span>
                                <input type="email" name="email" value="{{ old('email', $company->email) }}"
                                       class="w-full rounded-2xl border-slate-200 bg-white/50 pl-12 pr-5 py-3.5 text-sm font-bold text-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none shadow-sm"
                                       placeholder="iletisim@firma.com">
                            </div>
                            @error('email') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 uppercase tracking-wide">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 ml-1">Şehir</label>
                            <div class="relative">
                                <input type="text" name="city" value="{{ old('city', $company->city) }}"
                                       class="w-full rounded-2xl border-slate-200 bg-white/50 px-5 py-3.5 text-sm font-bold text-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none shadow-sm"
                                       placeholder="Örn: İstanbul">
                            </div>
                            @error('city') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 uppercase tracking-wide">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2 md:col-span-1">
                            <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 ml-1">Adres Detayı</label>
                            <textarea name="address" rows="3"
                                      class="w-full rounded-2xl border-slate-200 bg-white/50 px-5 py-3.5 text-sm font-bold text-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none shadow-sm resize-none"
                                      placeholder="Açık adres bilgilerini buraya yazınız...">{{ old('address', $company->address) }}</textarea>
                            @error('address') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 uppercase tracking-wide">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Lisans ve Kotalar -->
                <div class="rounded-[35px] border border-white bg-white/60 backdrop-blur-xl shadow-2xl shadow-slate-200/50 overflow-hidden transition-all hover:shadow-indigo-100/40 mt-6">
                    <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <h4 class="text-lg font-black text-slate-800 tracking-tight">Lisans ve Kotalar</h4>
                        </div>
                    </div>

                    <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 ml-1">Lisans Süresi</label>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3.5 text-sm font-bold text-slate-600 shadow-sm flex items-center justify-between">
                                <span>{{ $company->license_expires_at ? $company->license_expires_at->format('d.m.Y') : 'Süresiz' }}</span>
                                @if($company->license_expires_at)
                                    @php $days = $company->licenseDaysRemaining(); @endphp
                                    @if($days !== null && $days <= 7)
                                        <span class="text-[10px] bg-rose-100 text-rose-600 px-2 py-0.5 rounded-full font-black animate-pulse">Son {{ $days }} Gün!</span>
                                    @else
                                        <span class="text-[10px] bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full font-black">{{ $days }} Gün</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 ml-1">Araç Kotası</label>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3.5 text-sm font-bold shadow-sm flex items-center justify-between">
                                @php
                                    $currentVehicles = $company->vehicles()->count();
                                    $maxVehicles = $company->max_vehicles ?: 'Sınırsız';
                                    $vehicleWarning = is_numeric($maxVehicles) && $currentVehicles >= $maxVehicles;
                                @endphp
                                <span class="{{ $vehicleWarning ? 'text-rose-600' : 'text-slate-600' }}">{{ $currentVehicles }} / {{ $maxVehicles }}</span>
                                <div class="w-1/2 bg-slate-200 rounded-full h-1.5 ml-3">
                                    <div class="{{ $vehicleWarning ? 'bg-rose-500' : 'bg-indigo-500' }} h-1.5 rounded-full" style="width: {{ is_numeric($maxVehicles) && $maxVehicles > 0 ? min(100, ($currentVehicles / $maxVehicles) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 ml-1">Kullanıcı Kotası</label>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3.5 text-sm font-bold shadow-sm flex items-center justify-between">
                                @php
                                    $currentUsers = $company->users()->count();
                                    $maxUsers = $company->max_users ?: 'Sınırsız';
                                    $userWarning = is_numeric($maxUsers) && $currentUsers >= $maxUsers;
                                @endphp
                                <span class="{{ $userWarning ? 'text-rose-600' : 'text-slate-600' }}">{{ $currentUsers }} / {{ $maxUsers }}</span>
                                <div class="w-1/2 bg-slate-200 rounded-full h-1.5 ml-3">
                                    <div class="{{ $userWarning ? 'bg-rose-500' : 'bg-indigo-500' }} h-1.5 rounded-full" style="width: {{ is_numeric($maxUsers) && $maxUsers > 0 ? min(100, ($currentUsers / $maxUsers) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex items-center justify-end gap-4 pt-4">
                    <button type="submit" 
                            class="group relative flex items-center gap-3 overflow-hidden rounded-[24px] bg-slate-900 px-10 py-4.5 text-sm font-black text-white shadow-2xl transition-all hover:scale-[1.03] hover:shadow-indigo-500/25 active:scale-95 border border-white/10">
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-600 opacity-0 transition-opacity group-hover:opacity-100"></div>
                        <span class="relative">DEĞİŞİKLİKLERİ KAYDET</span>
                        <div class="relative flex h-6 w-6 items-center justify-center rounded-lg bg-white/10 text-white group-hover:rotate-12 transition-transform">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection