@extends('layouts.super-admin')

@section('title', 'Yeni Firma Ekle')
@section('subtitle', 'Yeni firma ve yönetici kullanıcı oluştur')

@section('content')

    <form method="POST" action="{{ route('super-admin.companies.store') }}">
        @csrf

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">

            {{-- FİRMA BİLGİLERİ --}}
            <div class="rounded-[32px] border border-slate-200/60 bg-white p-8 lg:p-10 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)]">
                <div class="flex items-center gap-4 mb-8">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-blue-50 text-indigo-600 shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Firma Bilgileri</h3>
                        <p class="text-sm text-slate-500 font-medium">Temel şirket tanımlamaları</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-[13px] font-extrabold tracking-wider text-slate-700 uppercase mb-2">Firma Adı <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full rounded-2xl border border-slate-200/60 bg-slate-50/50 px-5 py-4 text-[15px] font-medium text-slate-900 transition-all focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300 shadow-sm placeholder:text-slate-400">
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[13px] font-extrabold tracking-wider text-slate-700 uppercase mb-2">Telefon</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   class="w-full rounded-2xl border border-slate-200/60 bg-slate-50/50 px-5 py-4 text-[15px] font-medium text-slate-900 transition-all focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300 shadow-sm placeholder:text-slate-400">
                        </div>
                        <div>
                            <label class="block text-[13px] font-extrabold tracking-wider text-slate-700 uppercase mb-2">E-posta</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="w-full rounded-2xl border border-slate-200/60 bg-slate-50/50 px-5 py-4 text-[15px] font-medium text-slate-900 transition-all focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300 shadow-sm placeholder:text-slate-400">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[13px] font-extrabold tracking-wider text-slate-700 uppercase mb-2">Vergi No</label>
                            <input type="text" name="tax_no" value="{{ old('tax_no') }}"
                                   class="w-full rounded-2xl border border-slate-200/60 bg-slate-50/50 px-5 py-4 text-[15px] font-medium text-slate-900 transition-all focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300 shadow-sm placeholder:text-slate-400">
                        </div>
                        <div>
                            <label class="block text-[13px] font-extrabold tracking-wider text-slate-700 uppercase mb-2">Şehir</label>
                            <input type="text" name="city" value="{{ old('city') }}"
                                   class="w-full rounded-2xl border border-slate-200/60 bg-slate-50/50 px-5 py-4 text-[15px] font-medium text-slate-900 transition-all focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300 shadow-sm placeholder:text-slate-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[13px] font-extrabold tracking-wider text-slate-700 uppercase mb-2">Adres</label>
                        <textarea name="address" rows="3"
                                  class="w-full rounded-2xl border border-slate-200/60 bg-slate-50/50 px-5 py-4 text-[15px] font-medium text-slate-900 transition-all focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300 shadow-sm placeholder:text-slate-400 resize-none">{{ old('address') }}</textarea>
                    </div>

                    <div class="pt-2">
                        <label class="relative flex items-center gap-4 cursor-pointer group">
                            <input type="checkbox" name="is_active" id="is_active" value="1" checked class="peer sr-only">
                            <div class="h-8 w-14 rounded-full bg-slate-200 transition-colors peer-checked:bg-emerald-500 peer-focus:ring-4 peer-focus:ring-emerald-500/20 shadow-inner"></div>
                            <div class="absolute left-1 top-1 h-6 w-6 rounded-full bg-white transition-transform peer-checked:translate-x-6 shadow-sm"></div>
                            <span class="text-[15px] font-extrabold text-slate-700 group-hover:text-slate-900 transition-colors">Firma Aktif</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- LİSANS & KOTA --}}
            <div class="rounded-[32px] border border-slate-200/60 bg-white p-8 lg:p-10 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)]">
                <div class="flex items-center gap-4 mb-8">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-50 to-fuchsia-50 text-violet-600 shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Lisans & Kota</h3>
                        <p class="text-sm text-slate-500 font-medium">Sistem limitleri ve yöneticiler</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-[13px] font-extrabold tracking-wider text-slate-700 uppercase mb-2">Lisans Tipi <span class="text-rose-500">*</span></label>
                        <select name="license_type" required
                                class="w-full rounded-2xl border border-slate-200/60 bg-slate-50/50 px-5 py-4 text-[15px] font-extrabold text-slate-900 transition-all focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300 shadow-sm appearance-none cursor-pointer">
                            <option value="trial" {{ old('license_type') === 'trial' ? 'selected' : '' }}>Trial (Deneme)</option>
                            <option value="standard" {{ old('license_type', 'standard') === 'standard' ? 'selected' : '' }}>Standard</option>
                            <option value="premium" {{ old('license_type') === 'premium' ? 'selected' : '' }}>Premium</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[13px] font-extrabold tracking-wider text-slate-700 uppercase mb-2">Lisans Bitiş Tarihi</label>
                        <input type="date" name="license_expires_at" value="{{ old('license_expires_at') }}"
                               class="w-full rounded-2xl border border-slate-200/60 bg-slate-50/50 px-5 py-4 text-[15px] font-medium text-slate-900 transition-all focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300 shadow-sm placeholder:text-slate-400">
                        <p class="text-xs font-semibold text-slate-400 mt-2 ml-1">Boş bırakılırsa süresiz lisans tanımlanır.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[13px] font-extrabold tracking-wider text-slate-700 uppercase mb-2">Maks. Araç</label>
                            <input type="number" name="max_vehicles" value="{{ old('max_vehicles', 50) }}" min="1" required
                                   class="w-full rounded-2xl border border-slate-200/60 bg-slate-50/50 px-5 py-4 text-[15px] font-extrabold text-slate-900 transition-all focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300 shadow-sm text-center">
                        </div>
                        <div>
                            <label class="block text-[13px] font-extrabold tracking-wider text-slate-700 uppercase mb-2">Maks. Kullanıcı</label>
                            <input type="number" name="max_users" value="{{ old('max_users', 10) }}" min="1" required
                                   class="w-full rounded-2xl border border-slate-200/60 bg-slate-50/50 px-5 py-4 text-[15px] font-extrabold text-slate-900 transition-all focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300 shadow-sm text-center">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 mt-10 mb-6">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 text-emerald-600 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Kurucu Yönetici</h3>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-[13px] font-extrabold tracking-wider text-slate-700 uppercase mb-2">Ad Soyad <span class="text-rose-500">*</span></label>
                        <input type="text" name="admin_name" value="{{ old('admin_name') }}" required
                               class="w-full rounded-2xl border border-slate-200/60 bg-slate-50/50 px-5 py-4 text-[15px] font-medium text-slate-900 transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 hover:border-slate-300 shadow-sm placeholder:text-slate-400">
                    </div>
                    <div>
                        <label class="block text-[13px] font-extrabold tracking-wider text-slate-700 uppercase mb-2">E-posta <span class="text-rose-500">*</span></label>
                        <input type="email" name="admin_email" value="{{ old('admin_email') }}" required
                               class="w-full rounded-2xl border border-slate-200/60 bg-slate-50/50 px-5 py-4 text-[15px] font-medium text-slate-900 transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 hover:border-slate-300 shadow-sm placeholder:text-slate-400">
                    </div>
                    <div>
                        <label class="block text-[13px] font-extrabold tracking-wider text-slate-700 uppercase mb-2">Şifre <span class="text-rose-500">*</span></label>
                        <input type="text" name="admin_password" value="{{ old('admin_password') }}" required minlength="8"
                               class="w-full rounded-2xl border border-slate-200/60 bg-slate-50/50 px-5 py-4 text-[15px] font-medium text-slate-900 transition-all focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 hover:border-slate-300 shadow-sm placeholder:text-slate-400">
                        <p class="text-xs font-semibold text-slate-400 mt-2 ml-1">En az 8 karakter uzunluğunda güçlü bir şifre girin.</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- MODÜLLER --}}
        <div class="mt-8 rounded-[32px] border border-slate-200/60 bg-white p-8 lg:p-10 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)]">
            <div class="flex items-center gap-4 mb-8">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50 text-amber-600 shadow-inner">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path></svg>
                </div>
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Yetkili Modüller</h3>
                    <p class="text-sm text-slate-500 font-medium">Bu firmanın erişebileceği SaaS bileşenleri</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                @foreach($modules as $key => $label)
                    <label class="relative cursor-pointer group">
                        <input type="checkbox" name="modules[]" value="{{ $key }}" checked class="peer sr-only">
                        <div class="rounded-2xl border-2 border-slate-100 bg-slate-50/50 p-5 transition-all duration-300 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:shadow-[0_8px_16px_-6px_rgba(99,102,241,0.3)] hover:border-indigo-200">
                            <div class="flex items-center justify-between">
                                <span class="text-[14px] font-extrabold text-slate-600 peer-checked:text-indigo-800 group-hover:text-slate-900 transition-colors">{{ $label }}</span>
                                <div class="h-5 w-5 rounded-full border-2 border-slate-300 bg-white transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-500 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- KAYDET --}}
        <div class="mt-8 flex items-center justify-end gap-5">
            <a href="{{ route('super-admin.companies.index') }}"
               class="rounded-2xl px-6 py-4 text-[15px] font-extrabold text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-all">
                İptal Et
            </a>
            <button type="submit"
                    class="group relative inline-flex items-center gap-2.5 rounded-2xl bg-gradient-to-b from-indigo-500 to-violet-600 px-10 py-4 text-[16px] font-extrabold text-white shadow-[0_6px_0_0_#312e81,0_15px_20px_rgba(79,70,229,0.4)] transition-all hover:translate-y-1 hover:shadow-[0_2px_0_0_#312e81,0_10px_15px_rgba(79,70,229,0.4)] active:translate-y-1.5 active:shadow-none">
                <div class="absolute inset-0 rounded-2xl bg-gradient-to-tr from-white/0 via-white/20 to-white/0 opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                <svg class="w-5 h-5 drop-shadow-md transition-transform group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                <span class="drop-shadow-md tracking-wide">Firmayı Oluştur</span>
            </button>
        </div>

    </form>

@endsection
