@php
    $currentTab = request('tab', 'general');
@endphp

<div class="space-y-6">
    <div class="relative overflow-hidden rounded-[40px] bg-slate-900 p-8 lg:p-10 shadow-2xl">
        {{-- Mesh Gradient Background --}}
        <div class="absolute inset-0 opacity-30">
            <div class="absolute -left-10 -top-10 h-64 w-64 rounded-full bg-indigo-500 blur-3xl animate-pulse"></div>
            <div class="absolute right-0 bottom-0 h-96 w-96 rounded-full bg-blue-600 blur-3xl opacity-50"></div>
            <div class="absolute left-1/3 top-1/4 h-48 w-48 rounded-full bg-purple-500 blur-3xl"></div>
        </div>

        <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-8">
                <div class="group relative">
                    <div class="flex h-24 w-24 items-center justify-center rounded-[32px] bg-white/10 backdrop-blur-xl border border-white/20 text-5xl text-white shadow-2xl transition-transform group-hover:scale-110 duration-500">
                        {{ $vehicle->vehicle_type === 'Otobüs' ? '🚍' : ($vehicle->vehicle_type === 'Minibüs' ? '🚐' : '🚗') }}
                    </div>
                    <span class="absolute -right-2 -bottom-2 h-8 w-8 rounded-full border-4 border-slate-900 {{ $vehicle->is_active ? 'bg-emerald-400' : 'bg-rose-500' }} shadow-xl animate-bounce"></span>
                </div>

                <div>
                    <div class="flex items-center gap-4">
                        <h2 class="text-5xl font-black tracking-tighter text-white">{{ $vehicle->plate }}</h2>
                        <div class="h-8 w-px bg-white/20"></div>
                        <span class="rounded-full bg-white/10 px-4 py-1.5 text-[10px] font-black text-white border border-white/20 uppercase tracking-[0.2em]">
                            {{ $vehicle->is_active ? '● AKTİF OPERASYON' : '● PASİF DURUM' }}
                        </span>
                    </div>
                    <p class="mt-3 text-lg font-bold text-slate-300 uppercase tracking-widest flex items-center gap-3">
                        <span class="text-indigo-400">{{ $vehicle->brand }}</span>
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-600"></span>
                        <span>{{ $vehicle->model }}</span>
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-600"></span>
                        <span class="text-blue-400">{{ $vehicle->vehicle_type }}</span>
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <button onclick="toggleAIChat()" class="group relative inline-flex items-center gap-4 rounded-3xl bg-white px-8 py-5 text-sm font-black text-slate-900 shadow-2xl transition-all hover:-translate-y-1 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-blue-500 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <span class="relative z-10 text-xl group-hover:scale-125 transition-transform">🤖</span>
                    <span class="relative z-10 uppercase tracking-widest group-hover:text-white transition-colors">Yapay Zeka Analizi</span>
                </button>

                <div class="flex items-center gap-3">
                    <a href="{{ route('vehicles.edit', $vehicle) }}" class="flex h-14 w-14 items-center justify-center rounded-[20px] bg-white/10 backdrop-blur border border-white/20 text-white hover:bg-white hover:text-slate-900 transition-all duration-500 shadow-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </a>
                    <a href="{{ route('vehicles.index') }}" class="flex h-14 w-14 items-center justify-center rounded-[20px] bg-white/10 backdrop-blur border border-white/20 text-white hover:bg-white hover:text-slate-900 transition-all duration-500 shadow-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($currentTab === 'general')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Income -->
            <div class="group relative overflow-hidden rounded-[40px] bg-gradient-to-br from-emerald-500 to-teal-600 p-8 text-white shadow-2xl transition-all hover:-translate-y-2">
                <div class="absolute -right-6 -bottom-6 text-9xl opacity-10 rotate-12 group-hover:scale-125 transition-transform duration-700">💰</div>
                <div class="relative z-10">
                    <span class="text-[11px] font-black uppercase tracking-[0.3em] text-white/70">Toplam Hasılat</span>
                    <div class="mt-4 text-4xl font-black tracking-tighter">{{ number_format($income, 2, ',', '.') }} ₺</div>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="rounded-full bg-white/20 px-3 py-1 text-[10px] font-bold">VERİMLİ ARTIŞ</span>
                    </div>
                </div>
            </div>

            <!-- Fuel -->
            <div class="group relative overflow-hidden rounded-[40px] bg-gradient-to-br from-orange-500 to-rose-500 p-8 text-white shadow-2xl transition-all hover:-translate-y-2">
                <div class="absolute -right-6 -bottom-6 text-9xl opacity-10 rotate-12 group-hover:scale-125 transition-transform duration-700">⛽</div>
                <div class="relative z-10">
                    <span class="text-[11px] font-black uppercase tracking-[0.3em] text-white/70">Yakıt Gideri</span>
                    <div class="mt-4 text-4xl font-black tracking-tighter">{{ number_format($fuel, 2, ',', '.') }} ₺</div>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="rounded-full bg-white/20 px-3 py-1 text-[10px] font-bold">TOPLAM TÜKETİM</span>
                    </div>
                </div>
            </div>

            <!-- Operational -->
            <div class="group relative overflow-hidden rounded-[40px] bg-gradient-to-br from-indigo-500 to-purple-600 p-8 text-white shadow-2xl transition-all hover:-translate-y-2">
                <div class="absolute -right-6 -bottom-6 text-9xl opacity-10 rotate-12 group-hover:scale-125 transition-transform duration-700">🧑‍✈️</div>
                <div class="relative z-10">
                    <span class="text-[11px] font-black uppercase tracking-[0.3em] text-white/70">Personel & Gider</span>
                    <div class="mt-4 text-4xl font-black tracking-tighter">{{ number_format($salary, 2, ',', '.') }} ₺</div>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="rounded-full bg-white/20 px-3 py-1 text-[10px] font-bold">NET MAAŞLAR</span>
                    </div>
                </div>
            </div>

            <!-- Profit -->
            <div class="group relative overflow-hidden rounded-[40px] {{ $profit >= 0 ? 'bg-gradient-to-br from-blue-600 to-cyan-500' : 'bg-gradient-to-br from-rose-600 to-pink-600' }} p-8 text-white shadow-2xl transition-all hover:-translate-y-2">
                <div class="absolute -right-6 -bottom-6 text-9xl opacity-10 rotate-12 group-hover:scale-125 transition-transform duration-700">📈</div>
                <div class="relative z-10">
                    <span class="text-[11px] font-black uppercase tracking-[0.3em] text-white/70">Net Karlılık</span>
                    <div class="mt-4 text-4xl font-black tracking-tighter">{{ number_format($profit, 2, ',', '.') }} ₺</div>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="rounded-full bg-white/20 px-3 py-1 text-[10px] font-bold">DURUM ANALİZİ</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
             <div class="glass-card rounded-[32px] p-6 flex items-center gap-5 shadow-lg border border-slate-100 hover:shadow-xl transition-all group">
                <div class="h-14 w-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-500">🧑‍✈️</div>
                <div>
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Atanan Şoför</div>
                    <div class="text-base font-black text-slate-800">{{ $driverFullName ?: 'ATANMAMIŞ' }}</div>
                </div>
            </div>
            
            <div class="glass-card rounded-[32px] p-6 flex items-center gap-5 shadow-lg border border-slate-100 hover:shadow-xl transition-all group">
                <div class="h-14 w-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-500">🛣️</div>
                <div>
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Güncel KM</div>
                    <div class="text-base font-black text-slate-800">{{ $formattedKm }} KM</div>
                </div>
            </div>

            <div class="glass-card rounded-[32px] p-6 flex items-center gap-5 shadow-lg border border-slate-100 hover:shadow-xl transition-all group">
                <div class="h-14 w-14 rounded-2xl bg-amber-50 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-500">📅</div>
                <div>
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Muayene Tarihi</div>
                    <div class="text-base font-black text-slate-800">{{ $inspectionInfo['text'] }}</div>
                </div>
            </div>

            <div class="glass-card rounded-[32px] p-6 flex items-center gap-5 shadow-lg border border-slate-100 hover:shadow-xl transition-all group">
                <div class="h-14 w-14 rounded-2xl bg-rose-50 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-500">🛡️</div>
                <div>
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Kasko Durumu</div>
                    <div class="text-base font-black text-slate-800">{{ $kaskoInfo['text'] }}</div>
                </div>
            </div>
        </div>
    @endif
</div>