@extends('layouts.app')

@section('title', 'Kullanıcı Düzenle')
@section('subtitle', 'Kullanıcı bilgilerini ve yetkilerini güncelleyin')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('company-users.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-indigo-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Listeye Dön
        </a>

        @if($companyUser->id !== auth()->id())
            <form action="{{ route('company-users.destroy', $companyUser) }}" method="POST" onsubmit="return confirm('Bu kullanıcıyı silmek istediğine emin misin?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs font-black text-rose-500 hover:text-rose-700 uppercase tracking-widest">Kullanıcıyı Sil</button>
            </form>
        @endif
    </div>

    <form action="{{ route('company-users.update', $companyUser) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid gap-6">
            <!-- Temel Bilgiler -->
            <div class="rounded-[32px] border border-slate-200 bg-white p-8 shadow-sm">
                <div class="mb-8 flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900 tracking-tight">Hesap Bilgileri</h2>
                        <p class="text-sm font-medium text-slate-400">{{ $companyUser->name }} kullanıcısını yönetiyorsunuz</p>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="px-2 text-[10px] font-black uppercase tracking-widest text-slate-500">Ad Soyad</label>
                        <input type="text" name="name" value="{{ old('name', $companyUser->name) }}" 
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-900 transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
                        @error('name') <p class="px-2 text-xs font-bold text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="px-2 text-[10px] font-black uppercase tracking-widest text-slate-500">E-posta Adresi</label>
                        <input type="email" name="email" value="{{ old('email', $companyUser->email) }}" 
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-900 transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
                        @error('email') <p class="px-2 text-xs font-bold text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="px-2 text-[10px] font-black uppercase tracking-widest text-slate-500">Yeni Şifre</label>
                        <input type="password" name="password" placeholder="Değiştirmek istemiyorsanız boş bırakın" autocomplete="new-password"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-900 transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
                        @error('password') <p class="px-2 text-xs font-bold text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="px-2 text-[10px] font-black uppercase tracking-widest text-slate-500">Şifre Tekrar</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-900 transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
                    </div>

                    <div class="space-y-2">
                        <label class="px-2 text-[10px] font-black uppercase tracking-widest text-slate-500">Rol ve Yetki</label>
                        <select name="role" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-900 transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 appearance-none">
                            <option value="company_admin" {{ old('role', $companyUser->role) == 'company_admin' ? 'selected' : '' }}>Firma Yöneticisi</option>
                            <option value="operation" {{ old('role', $companyUser->role) == 'operation' ? 'selected' : '' }}>Operasyon</option>
                            <option value="accounting" {{ old('role', $companyUser->role) == 'accounting' ? 'selected' : '' }}>Muhasebe</option>
                            <option value="viewer" {{ old('role', $companyUser->role) == 'viewer' ? 'selected' : '' }}>Gözlemci (Sadece Görüntüler)</option>
                        </select>
                        @error('role') <p class="px-2 text-xs font-bold text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center px-4 mt-8">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ $companyUser->is_active ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ml-3 text-sm font-black text-slate-700 uppercase tracking-tighter">Hesap Aktif</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Menü Yetkileri -->
            @include('company-users.partials.permissions')

            <!-- Submit -->
            <div class="flex items-center justify-end gap-4 mt-4">
                <a href="{{ route('company-users.index') }}" class="px-8 py-4 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">Vazgeç</a>
                <button type="submit" 
                        class="group relative inline-flex items-center justify-center gap-3 overflow-hidden rounded-3xl bg-indigo-600 px-12 py-4 text-sm font-bold text-white shadow-xl shadow-indigo-200 transition-all hover:bg-indigo-700 hover:scale-105 active:scale-95">
                    <span>Değişiklikleri Kaydet</span>
                    <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
