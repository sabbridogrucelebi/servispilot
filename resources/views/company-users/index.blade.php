@extends('layouts.app')

@section('title', 'Kullanıcı Yönetimi')
@section('subtitle', 'Sistem kullanıcıları, yetkiler ve erişim kontrolü')

@section('content')
<div class="space-y-8">
    <!-- Header Actions & Stats -->
    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        <div class="grid grid-cols-2 gap-4 sm:flex sm:items-center">
            <div class="rounded-3xl border border-slate-200 bg-white px-6 py-4 shadow-sm transition-all hover:shadow-md">
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Toplam Kullanıcı</div>
                <div class="mt-1 text-2xl font-black text-slate-900">{{ $users->count() }}</div>
            </div>
            
            <div class="rounded-3xl border border-slate-200 bg-white px-6 py-4 shadow-sm transition-all hover:shadow-md">
                <div class="text-[10px] font-black uppercase tracking-widest text-emerald-500">Aktif</div>
                <div class="mt-1 text-2xl font-black text-emerald-600">{{ $users->where('is_active', true)->count() }}</div>
            </div>
        </div>

        @if(!auth()->user()->isViewer())
        <a href="{{ route('company-users.create') }}" 
           class="group relative inline-flex items-center justify-center gap-3 overflow-hidden rounded-3xl bg-slate-900 px-8 py-4 text-sm font-bold text-white transition-all hover:bg-slate-800 hover:shadow-xl hover:shadow-slate-200">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-600 opacity-0 transition-opacity group-hover:opacity-10"></div>
            <svg class="h-5 w-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Yeni Kullanıcı Ekle</span>
        </a>
        @endif
    </div>

    @if(session('success'))
        <div class="flex items-center gap-4 rounded-[26px] bg-emerald-50 border border-emerald-100 p-5 text-emerald-700 shadow-sm animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-200">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <p class="text-sm font-bold">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Users Grid -->
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse($users as $user)
            <div class="group relative flex flex-col rounded-[32px] border border-slate-200 bg-white p-6 transition-all duration-500 hover:border-indigo-200 hover:shadow-2xl hover:shadow-indigo-100/50 hover:-translate-y-1">
                <!-- Status Badge -->
                <div class="absolute right-6 top-6">
                    @if($user->is_active)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-emerald-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-slate-500">
                            Pasif
                        </span>
                    @endif
                </div>

                <!-- User Info -->
                <div class="flex items-center gap-5">
                    <div class="relative">
                        <div class="absolute -inset-1 rounded-[22px] bg-gradient-to-tr from-indigo-500 to-purple-500 opacity-0 blur transition-opacity group-hover:opacity-20"></div>
                        <div class="relative flex h-16 w-16 items-center justify-center rounded-[22px] bg-slate-50 border border-slate-100 text-xl font-black text-indigo-600 transition-all group-hover:bg-white group-hover:scale-105">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate text-lg font-black tracking-tight text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $user->name }}</h3>
                        <p class="truncate text-sm font-medium text-slate-400 mt-0.5">{{ $user->email }}</p>
                    </div>
                </div>

                <!-- Role Section -->
                <div class="mt-8 flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 border border-slate-100/50">
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Yetki Rolü</div>
                        <div class="mt-1 text-sm font-extrabold text-slate-700">
                            {{ match($user->role) {
                                'company_admin' => 'Firma Yöneticisi',
                                'accounting' => 'Muhasebe',
                                'operations' => 'Operasyon',
                                'viewer' => 'Gözlemci',
                                default => $user->role
                            } }}
                        </div>
                    </div>
                    <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-white shadow-sm text-indigo-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04a11.357 11.357 0 00-1.018 4.772c0 4.113 2.193 7.713 5.5 9.69a11.354 11.354 0 0011.001 0c3.307-1.977 5.5-5.577 5.5-9.69a11.357 11.357 0 00-1.018-4.772z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Action Buttons -->
                @if(!auth()->user()->isViewer())
                <div class="mt-6 flex items-center gap-3">
                    <a href="{{ route('company-users.edit', $user) }}" 
                       class="flex-1 rounded-2xl bg-indigo-50 py-3 text-center text-xs font-black text-indigo-600 transition-all hover:bg-indigo-600 hover:text-white shadow-sm hover:shadow-lg hover:shadow-indigo-200">
                        Düzenle
                    </a>

                    @if($user->id !== auth()->id())
                        <form action="{{ route('company-users.destroy', $user) }}" method="POST" class="flex-1" onsubmit="return confirm('Bu kullanıcıyı silmek istediğine emin misin?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full rounded-2xl bg-rose-50 py-3 text-center text-xs font-black text-rose-600 transition-all hover:bg-rose-600 hover:text-white shadow-sm hover:shadow-lg hover:shadow-rose-200">
                                Sil
                            </button>
                        </form>
                    @else
                        <div class="flex-1 rounded-2xl bg-slate-100 py-3 text-center text-[10px] font-black text-slate-400 uppercase">
                            Aktif Oturum
                        </div>
                    @endif
                </div>
                @else
                <div class="mt-6 p-4 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Görüntüleme Yetkisi</span>
                </div>
                @endif
            </div>
        @empty
            <div class="col-span-full py-20 text-center">
                <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-[32px] bg-slate-100 text-4xl shadow-inner">
                    👥
                </div>
                <h3 class="text-xl font-black text-slate-900">Henüz kullanıcı kaydı yok</h3>
                <p class="mt-2 text-slate-500">Sisteme erişimi olan ekip arkadaşlarınızı buradan ekleyebilirsiniz.</p>
                <div class="mt-8">
                    <a href="{{ route('company-users.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-8 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition-all hover:scale-105 hover:bg-indigo-700">
                        İlk Kullanıcıyı Ekle
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
