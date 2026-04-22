@extends('layouts.super-admin')

@section('title', 'Yeni Firma Ekle')
@section('subtitle', 'Yeni firma ve yönetici kullanıcı oluştur')

@section('content')

    <form method="POST" action="{{ route('super-admin.companies.store') }}">
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- FİRMA BİLGİLERİ --}}
            <div class="rounded-[24px] border border-slate-200/60 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-5">🏢 Firma Bilgileri</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Firma Adı *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Telefon</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">E-posta</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Vergi No</label>
                            <input type="text" name="tax_no" value="{{ old('tax_no') }}"
                                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Şehir</label>
                            <input type="text" name="city" value="{{ old('city') }}"
                                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Adres</label>
                        <textarea name="address" rows="2"
                                  class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-indigo-400">{{ old('address') }}</textarea>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="is_active" class="text-sm font-medium text-slate-700">Firma Aktif</label>
                    </div>
                </div>
            </div>

            {{-- LİSANS & KOTA --}}
            <div class="rounded-[24px] border border-slate-200/60 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-5">📋 Lisans & Kota</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Lisans Tipi *</label>
                        <select name="license_type" required
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                            <option value="trial" {{ old('license_type') === 'trial' ? 'selected' : '' }}>Trial (Deneme)</option>
                            <option value="standard" {{ old('license_type', 'standard') === 'standard' ? 'selected' : '' }}>Standard</option>
                            <option value="premium" {{ old('license_type') === 'premium' ? 'selected' : '' }}>Premium</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Lisans Bitiş Tarihi</label>
                        <input type="date" name="license_expires_at" value="{{ old('license_expires_at') }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        <p class="text-xs text-slate-500 mt-1">Boş bırakılırsa süresiz lisans verilir.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Maks. Araç</label>
                            <input type="number" name="max_vehicles" value="{{ old('max_vehicles', 50) }}" min="1" required
                                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Maks. Kullanıcı</label>
                            <input type="number" name="max_users" value="{{ old('max_users', 10) }}" min="1" required
                                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        </div>
                    </div>
                </div>

                <h3 class="text-lg font-bold text-slate-900 mt-8 mb-5">👤 Yönetici Kullanıcı</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Ad Soyad *</label>
                        <input type="text" name="admin_name" value="{{ old('admin_name') }}" required
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">E-posta *</label>
                        <input type="email" name="admin_email" value="{{ old('admin_email') }}" required
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Şifre *</label>
                        <input type="text" name="admin_password" value="{{ old('admin_password') }}" required minlength="8"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        <p class="text-xs text-slate-500 mt-1">En az 8 karakter.</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- MODÜLLER --}}
        <div class="mt-6 rounded-[24px] border border-slate-200/60 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-5">🧩 Modüller</h3>
            <p class="text-sm text-slate-500 mb-4">Firma hangi modüllere erişebilecek?</p>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach($modules as $key => $label)
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 cursor-pointer hover:bg-indigo-50 hover:border-indigo-200 transition">
                        <input type="checkbox" name="modules[]" value="{{ $key }}" checked
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- KAYDET --}}
        <div class="mt-6 flex items-center justify-end gap-4">
            <a href="{{ route('super-admin.companies.index') }}"
               class="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                İptal
            </a>
            <button type="submit"
                    class="rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-8 py-3 text-sm font-semibold text-white shadow-lg hover:scale-[1.02] transition">
                Firma Oluştur
            </button>
        </div>

    </form>

@endsection
