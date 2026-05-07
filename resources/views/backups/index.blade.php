@extends('layouts.app')
@section('title', 'Yedeklemeler & Veri Güvenliği')

@section('content')
<div class="space-y-6">
    <div class="relative overflow-hidden rounded-[32px] bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 shadow-[0_25px_60px_-15px_rgba(15,23,42,0.5)]">
        <div class="pointer-events-none absolute inset-0 opacity-40">
            <div class="absolute -top-24 -left-16 h-80 w-80 rounded-full bg-indigo-500/40 blur-[90px]"></div>
            <div class="absolute top-1/2 right-1/4 h-72 w-72 rounded-full bg-blue-500/30 blur-[80px]"></div>
        </div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.04]" style="background-image:linear-gradient(rgba(255,255,255,.6) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.6) 1px,transparent 1px);background-size:32px 32px;"></div>

        <div class="relative z-10 p-6 lg:p-10">
            <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-6">
                    <div class="relative group">
                        <div class="flex h-24 w-24 items-center justify-center rounded-[28px] bg-white/10 backdrop-blur-xl border border-white/20 text-5xl shadow-2xl transition-transform duration-500 group-hover:scale-105">
                            <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Objects/Floppy%20Disk.png" alt="Backup" class="w-16 h-16 drop-shadow-2xl" />
                        </div>
                    </div>
                    <div>
                        <h1 class="text-4xl lg:text-5xl font-black tracking-tight text-white leading-none">Veri Yedekleme</h1>
                        <p class="mt-2 text-slate-300 font-medium">Sistem her gece otomatik yedek alır. Maksimum 7 günlük geçmiş kayıtlar tutulur.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4">
        <div class="flex items-center gap-3">
            <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <p class="text-sm font-medium text-emerald-600">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4">
        <div class="flex items-center gap-3">
            <svg class="h-5 w-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <p class="text-sm font-medium text-rose-600">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/50 p-6">
            <h2 class="text-lg font-black text-slate-900">Mevcut Yedekler</h2>
        </div>
        
        <div class="p-6">
            @if(count($backups) === 0)
                <div class="text-center py-12">
                    <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Objects/Card%20File%20Box.png" alt="Boş" class="mx-auto w-24 h-24 drop-shadow-xl" />
                    <h3 class="mt-4 text-lg font-bold text-slate-900">Henüz Yedek Yok</h3>
                    <p class="mt-1 text-sm text-slate-500">Sistem ilk yedeği bu gece saat 00:00'da oluşturacaktır.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($backups as $backup)
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900">{{ $backup['name'] }}</h3>
                                <div class="mt-1 flex items-center gap-3 text-xs text-slate-500">
                                    <span class="flex items-center gap-1"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> {{ $backup['date'] }}</span>
                                    <span class="flex items-center gap-1"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" /></svg> {{ $backup['size'] }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 w-full md:w-auto">
                            <form action="{{ route('backups.restore') }}" method="POST" class="flex items-center gap-2 w-full md:w-auto" onsubmit="return confirm('Seçili modülün yedeğini geri yüklemek istiyor musunuz? Mevcut verileriniz yedekteki kayıtlarla güncellenecek.');">
                                @csrf
                                <input type="hidden" name="file" value="{{ $backup['name'] }}">
                                <select name="module" required class="flex-1 md:w-48 rounded-xl border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="" disabled selected>Modül Seçin (Geri Yükle)</option>
                                    <option value="araclar">Araçlar</option>
                                    <option value="yakitlar">Yakıt Verileri</option>
                                    <option value="seferler">Puantaj & Seferler</option>
                                    <option value="personeller">Personeller</option>
                                    <option value="musteriler">Müşteriler</option>
                                    <option value="bakimlar">Bakım / Tamir</option>
                                    <option value="cezalar">Cezalar</option>
                                    <option value="maaslar">Maaşlar</option>
                                </select>
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-slate-800 focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 transition-all">
                                    Yükle
                                </button>
                            </form>
                            
                            <a href="{{ route('backups.download', $backup['name']) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
