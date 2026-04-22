@extends('layouts.app')

@section('title', 'Filo Komuta Merkezi')
@section('subtitle', 'Ultra Pro Filo Analiz ve Yönetim Sistemi')

@section('content')

@php
    $totalVehicles = $vehicles->count();
    $activeVehicles = $vehicles->where('is_active', true)->count();
    $maintenanceRequired = $vehicles->filter(function($v) {
        $ms = $v->maintenance_status;
        return $ms['has_setting'] && $ms['oil_remaining'] < 2000;
    })->count();

    $vehicleTypeStats = [
        ['label' => 'Midibüs', 'count' => $vehicles->where('vehicle_type', 'Midibüs')->count(), 'icon' => '🚌', 'gradient' => 'from-orange-500 to-amber-600'],
        ['label' => 'Minibüs', 'count' => $vehicles->where('vehicle_type', 'Minibüs')->count(), 'icon' => '🚐', 'gradient' => 'from-blue-500 to-indigo-600'],
        ['label' => 'Binek', 'count' => $vehicles->where('vehicle_type', 'Binek Araç')->count(), 'icon' => '🚗', 'gradient' => 'from-rose-500 to-pink-600'],
        ['label' => 'Otobüs', 'count' => $vehicles->where('vehicle_type', 'Otobüs')->count(), 'icon' => '🚍', 'gradient' => 'from-violet-500 to-purple-600'],
    ];
@endphp

<style>
    :root {
        --ultra-dark: #0f172a;
        --ultra-accent: #6366f1;
    }
    
    .ultra-gradient-bg {
        background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.15), transparent 40%),
                    radial-gradient(circle at bottom left, rgba(244, 63, 94, 0.1), transparent 40%),
                    #f8fafc;
    }

    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05);
    }

    .stat-card {
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .stat-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 40px 80px -15px rgba(99, 102, 241, 0.15);
    }

    .vehicle-row {
        transition: all 0.4s ease;
    }

    .vehicle-row:hover {
        background: rgba(255, 255, 255, 0.9) !important;
        transform: scale(1.01) translateX(10px);
        box-shadow: -20px 0 50px -10px rgba(99, 102, 241, 0.1);
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    .animate-float {
        animation: float 4s ease-in-out infinite;
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
</style>

<div class="ultra-gradient-bg min-h-screen -mt-10 pt-10 px-4 lg:px-8 space-y-12 pb-32">
    
    {{-- Super Header --}}
    <div class="relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-8 pt-10">
        <div class="space-y-4">
            <div class="inline-flex items-center gap-3 rounded-2xl bg-white/60 backdrop-blur-md px-4 py-2 border border-white shadow-sm">
                <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Filo Durumu: Kusursuz</span>
            </div>
            <h1 class="text-6xl font-black tracking-tighter text-slate-900 lg:text-7xl">
                Filo <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-600">Yönetimi</span>
            </h1>
            <p class="text-lg font-bold text-slate-400 max-w-2xl leading-relaxed">
                Yazılım mimariniz ile tam entegre, veriye dayalı operasyonel kontrol merkezi. 
                <span class="text-indigo-500">Ultra Pro v2.0</span> ile filonuzun her saniyesi kontrolünüz altında.
            </p>
        </div>

        <div class="flex items-center gap-4">
            <a href="{{ route('vehicles.create') }}" 
               class="group relative h-20 px-10 flex items-center gap-4 rounded-[30px] bg-slate-900 text-white overflow-hidden transition-all hover:scale-105 active:scale-95 shadow-2xl shadow-slate-900/40">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-blue-600 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <span class="relative z-10 text-3xl transition-transform group-hover:rotate-90 duration-500">+</span>
                <span class="relative z-10 text-sm font-black uppercase tracking-widest">Yeni Araç Ekle</span>
            </a>
        </div>
    </div>

    {{-- God-Tier Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-8">
        
        <!-- Total Card -->
        <div class="stat-card glass-panel rounded-[40px] p-8 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 h-32 w-32 rounded-full bg-indigo-500/5 blur-3xl transition-all group-hover:bg-indigo-500/15"></div>
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between">
                    <div class="h-16 w-16 rounded-3xl bg-indigo-50 flex items-center justify-center text-3xl shadow-inner group-hover:scale-110 transition-transform">🚐</div>
                    <span class="text-[10px] font-black text-indigo-600/40 uppercase tracking-widest">Toplam Filo</span>
                </div>
                <div class="mt-8">
                    <div class="text-6xl font-black tracking-tighter text-slate-900">{{ $totalVehicles }}</div>
                    <div class="mt-2 flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest">
                        Kayıtlı Araç Sayısı
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Card -->
        <div class="stat-card glass-panel rounded-[40px] p-8 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 h-32 w-32 rounded-full bg-emerald-500/5 blur-3xl transition-all group-hover:bg-emerald-500/15"></div>
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between">
                    <div class="h-16 w-16 rounded-3xl bg-emerald-50 flex items-center justify-center text-3xl shadow-inner group-hover:scale-110 transition-transform">📡</div>
                    <span class="text-[10px] font-black text-emerald-600/40 uppercase tracking-widest">Canlı Operasyon</span>
                </div>
                <div class="mt-8">
                    <div class="text-6xl font-black tracking-tighter text-slate-900">{{ $activeVehicles }}</div>
                    <div class="mt-2 flex items-center gap-2 text-xs font-bold text-emerald-600 uppercase tracking-widest">
                        Sahada Aktif Araçlar
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Card -->
        <div class="stat-card glass-panel rounded-[40px] p-8 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 h-32 w-32 rounded-full bg-amber-500/5 blur-3xl transition-all group-hover:bg-amber-500/15"></div>
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between">
                    <div class="h-16 w-16 rounded-3xl bg-amber-50 flex items-center justify-center text-3xl shadow-inner group-hover:scale-110 transition-transform">🛠️</div>
                    <span class="text-[10px] font-black text-amber-600/40 uppercase tracking-widest">Sağlık Analizi</span>
                </div>
                <div class="mt-8">
                    <div class="text-6xl font-black tracking-tighter text-slate-900">{{ $maintenanceRequired }}</div>
                    <div class="mt-2 flex items-center gap-2 text-xs font-bold text-amber-600 uppercase tracking-widest">
                        Bakım Bekleyenler
                    </div>
                </div>
            </div>
        </div>

        <!-- Critical Alerts -->
        <div class="stat-card bg-gradient-to-br from-rose-500 to-rose-700 rounded-[40px] p-8 relative overflow-hidden group shadow-2xl shadow-rose-500/40">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
            <div class="relative z-10 flex flex-col justify-between h-full text-white">
                <div class="flex items-center justify-between">
                    <div class="h-16 w-16 rounded-3xl bg-white/20 backdrop-blur-xl flex items-center justify-center text-3xl shadow-xl animate-float">🚨</div>
                    <span class="text-[10px] font-black text-white/50 uppercase tracking-widest">Sistem Uyarıları</span>
                </div>
                <div class="mt-8">
                    <div class="text-xl font-black leading-tight">3 aracın muayenesi yaklaştı.</div>
                    <a href="{{ route('documents.index') }}" class="mt-4 inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-white/70 hover:text-white transition-colors">
                        BELGELERİ İNCELE →
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Advanced Search & Actions --}}
    <div class="glass-panel rounded-[50px] p-6 flex flex-col lg:flex-row gap-6">
        <div class="flex-1 relative group">
            <div class="absolute inset-y-0 left-8 flex items-center text-2xl text-slate-400 group-focus-within:text-indigo-500 transition-colors">🔍</div>
            <input type="text" 
                   placeholder="Plaka, Şoför, Marka veya Model ile akıllı filtreleme yapın..." 
                   class="w-full bg-white/50 border-none rounded-[35px] pl-20 pr-10 py-6 text-lg font-bold text-slate-800 placeholder:text-slate-400 focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-inner outline-none">
        </div>
        <div class="flex gap-4">
            <div class="relative min-w-[200px]">
                <select class="w-full h-full bg-white/50 border-none rounded-[35px] px-8 text-sm font-black uppercase tracking-widest text-slate-600 shadow-inner appearance-none outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all cursor-pointer">
                    <option>Tüm Tipler</option>
                    @foreach($vehicleTypeStats as $type)
                        <option>{{ $type['label'] }}</option>
                    @endforeach
                </select>
                <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">▼</div>
            </div>
            <a href="{{ route('vehicles.export.excel') }}" 
               class="h-20 w-20 flex items-center justify-center rounded-[35px] bg-white border border-slate-100 shadow-xl text-2xl hover:bg-emerald-50 hover:text-emerald-600 transition-all hover:scale-105 active:scale-95"
               title="Excel Raporu">📊</a>
        </div>
    </div>

    {{-- Premium Vehicle List --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between px-10 mb-6">
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.4em]">Araç Envanteri & Durum Analizi</div>
            <div class="flex items-center gap-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                <span>{{ $vehicles->count() }} Sonuç Bulundu</span>
            </div>
        </div>

        <div class="space-y-6">
            @forelse($vehicles as $vehicle)
                <div class="vehicle-row glass-panel rounded-[40px] p-6 lg:p-8 flex flex-col lg:flex-row lg:items-center justify-between gap-8 group">
                    {{-- Basic Info --}}
                    <div class="flex items-center gap-8 lg:w-1/3">
                        <div class="relative">
                            <div class="h-24 w-24 rounded-[35px] bg-gradient-to-br from-slate-50 to-slate-200 flex items-center justify-center text-5xl shadow-inner group-hover:scale-110 transition-transform duration-500">
                                {{ $vehicle->vehicle_type === 'Otobüs' ? '🚍' : ($vehicle->vehicle_type === 'Minibüs' ? '🚐' : '🚗') }}
                            </div>
                            <div class="absolute -right-2 -bottom-2 h-8 w-8 rounded-full border-4 border-white {{ $vehicle->is_active ? 'bg-emerald-500 shadow-emerald-500/50' : 'bg-rose-500 shadow-rose-500/50' }} shadow-lg"></div>
                        </div>
                        <div>
                            <div class="text-3xl font-black tracking-tighter text-slate-900 group-hover:text-indigo-600 transition-colors">
                                {{ $vehicle->plate }}
                            </div>
                            <div class="mt-1 flex items-center gap-3">
                                <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">{{ $vehicle->brand }} {{ $vehicle->model }}</span>
                                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                <span class="text-xs font-black text-indigo-500 uppercase tracking-widest">{{ $vehicle->vehicle_type }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Data Visualization --}}
                    <div class="flex flex-wrap items-center gap-10 flex-1 px-8 border-x border-slate-100 hidden lg:flex">
                        <div class="flex flex-col gap-2 min-w-[140px]">
                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <span>🛣️</span> Güncel Kilometre
                            </div>
                            <div class="text-2xl font-black text-slate-800 tracking-tight">
                                @php
                                    $latestFuel = $vehicle->fuels->first();
                                    $displayKm = $latestFuel->km ?? $vehicle->current_km ?? 0;
                                @endphp
                                {{ number_format($displayKm, 0, ',', '.') }} 
                                <span class="text-xs text-indigo-500 ml-1">KM</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 shadow-[0_0_10px_rgba(99,102,241,0.5)]" style="width: 75%"></div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 min-w-[120px]">
                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Bakım Durumu</div>
                            @php $mStatus = $vehicle->maintenance_status; @endphp
                            @if($mStatus['has_setting'] && $mStatus['oil_remaining'] < 2000)
                                <div class="text-sm font-black text-rose-600 flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-rose-500 animate-ping"></span>
                                    ACİL SERVİS
                                </div>
                            @else
                                <div class="text-sm font-black text-emerald-600 flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    KUSURSUZ
                                </div>
                            @endif
                            <div class="text-[9px] font-bold text-slate-400 uppercase">Periyodik: %88</div>
                        </div>

                        <div class="flex flex-col gap-2 min-w-[120px]">
                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Atanan Şoför</div>
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-xl bg-slate-100 flex items-center justify-center text-xs">👤</div>
                                <div class="text-sm font-black text-slate-700 truncate max-w-[100px]">Atanmamış</div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-4">
                        <a href="{{ route('vehicles.show', $vehicle) }}" 
                           class="flex h-16 w-16 items-center justify-center rounded-3xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all duration-500 shadow-sm hover:shadow-indigo-500/30">
                           <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </a>
                        
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('vehicles.edit', $vehicle) }}" 
                               class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-slate-100 text-slate-400 hover:text-amber-500 hover:border-amber-200 transition-all">
                               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST" class="inline" onsubmit="return confirm('Silsen geri gelmez, emin misin?')">
                                @csrf @method('DELETE')
                                <button type="submit" 
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-slate-100 text-slate-400 hover:text-rose-500 hover:border-rose-200 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="glass-panel rounded-[50px] p-20 text-center space-y-6">
                    <div class="text-8xl animate-float">🛸</div>
                    <h3 class="text-3xl font-black text-slate-800">Burada Henüz Kimse Yok</h3>
                    <p class="text-slate-400 font-bold max-w-sm mx-auto">Filo yönetimini başlatmak için ilk aracınızı ekleyerek operasyonu canlandırın.</p>
                    <a href="{{ route('vehicles.create') }}" class="inline-flex items-center gap-4 rounded-3xl bg-indigo-600 px-10 py-5 text-sm font-black text-white shadow-2xl shadow-indigo-500/40 hover:bg-indigo-500 transition-all active:scale-95">
                        İLK ARACI ŞİMDİ EKLE
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection