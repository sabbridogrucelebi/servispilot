@php
    $currentTab = request('tab', 'general');

    $canEditV = auth()->user()->hasPermission('vehicles.edit');
    $canDeleteV = auth()->user()->hasPermission('vehicles.delete');

    $driverPhoneClean = $driverPhone ? preg_replace('/\s+/', '', $driverPhone) : null;

    // KPI'lar için küçük yardımcı
    $allCriticalDates = collect([
        ['label' => 'Muayene',  'info' => $inspectionInfo],
        ['label' => 'Egzoz',    'info' => $exhaustInfo],
        ['label' => 'Sigorta',  'info' => $insuranceInfo],
        ['label' => 'Kasko',    'info' => $kaskoInfo],
    ]);

    $expiredCount = $allCriticalDates->filter(fn($d) => str_contains($d['info']['class'], 'rose'))->count();
    $warningCount = $allCriticalDates->filter(fn($d) => str_contains($d['info']['class'], 'amber'))->count();
@endphp

<div class="space-y-6">
    {{-- ════════════════════════════════════════════════════════════════ --}}
    {{--  HERO BANNER                                                       --}}
    {{-- ════════════════════════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden rounded-[32px] bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 shadow-[0_25px_60px_-15px_rgba(15,23,42,0.5)]">
        {{-- Dekoratif arka plan --}}
        <div class="pointer-events-none absolute inset-0 opacity-40">
            <div class="absolute -top-24 -left-16 h-80 w-80 rounded-full bg-indigo-500/40 blur-[90px]"></div>
            <div class="absolute top-1/2 right-1/4 h-72 w-72 rounded-full bg-purple-500/30 blur-[80px]"></div>
            <div class="absolute -bottom-24 -right-16 h-80 w-80 rounded-full bg-blue-500/40 blur-[90px]"></div>
        </div>
        {{-- Noise grid --}}
        <div class="pointer-events-none absolute inset-0 opacity-[0.04]" style="background-image:linear-gradient(rgba(255,255,255,.6) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.6) 1px,transparent 1px);background-size:32px 32px;"></div>

        <div class="relative z-10 p-6 lg:p-10">
            <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">

                {{-- SOL: Plaka & Araç Bilgileri --}}
                <div class="flex items-center gap-6">
                    <div class="relative group">
                        <div class="flex h-24 w-24 lg:h-28 lg:w-28 items-center justify-center rounded-[28px] bg-white/10 backdrop-blur-xl border border-white/20 text-5xl lg:text-6xl shadow-2xl transition-transform duration-500 group-hover:scale-105">
                            @if($vehicle->vehicle_type === 'Otobüs')
                                <svg class="w-12 h-12 drop-shadow-[0_2px_4px_rgba(255,255,255,0.4)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                            @elseif($vehicle->vehicle_type === 'Minibüs')
                                <svg class="w-12 h-12 drop-shadow-[0_2px_4px_rgba(255,255,255,0.4)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                            @else
                                <svg class="w-12 h-12 drop-shadow-[0_2px_4px_rgba(255,255,255,0.4)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                            @endif
                        </div>
                        <span class="absolute -right-1 -bottom-1 flex h-7 w-7 items-center justify-center rounded-full border-4 border-slate-900 {{ $vehicle->is_active ? 'bg-emerald-400' : 'bg-rose-500' }} shadow-xl">
                            @if($vehicle->is_active)
                                <span class="h-2 w-2 rounded-full bg-white animate-ping"></span>
                            @endif
                        </span>
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 border border-white/20 px-3 py-1 text-[10px] font-black text-white/80 uppercase tracking-[0.2em]">
                                <span class="h-1.5 w-1.5 rounded-full {{ $vehicle->is_active ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                {{ $vehicle->is_active ? 'Aktif Operasyon' : 'Pasif Durum' }}
                            </span>
                            @if($vehicle->model_year)
                                <span class="rounded-full bg-indigo-500/20 border border-indigo-400/30 px-3 py-1 text-[10px] font-black text-indigo-200 uppercase tracking-[0.2em]">
                                    {{ $vehicle->model_year }} Model
                                </span>
                            @endif
                            @if($expiredCount > 0)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-500/30 border border-rose-400/40 px-3 py-1 text-[10px] font-black text-rose-100 uppercase tracking-widest animate-pulse">
                                    <svg class="w-3.5 h-3.5 drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    {{ $expiredCount }} Süresi Geçmiş
                                </span>
                            @elseif($warningCount > 0)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/20 border border-amber-400/30 px-3 py-1 text-[10px] font-black text-amber-100 uppercase tracking-widest">
                                    <svg class="w-3.5 h-3.5 drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ $warningCount }} Uyarı
                                </span>
                            @endif
                        </div>

                        <h1 class="mt-3 text-4xl lg:text-5xl xl:text-6xl font-black tracking-tight text-white leading-none">
                            {{ $vehicle->plate }}
                        </h1>

                        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm font-bold uppercase tracking-widest">
                            <span class="text-indigo-300">{{ $vehicle->brand ?: '—' }}</span>
                            <span class="text-slate-600">•</span>
                            <span class="text-slate-200">{{ $vehicle->model ?: '—' }}</span>
                            <span class="text-slate-600">•</span>
                            <span class="text-blue-300">{{ $vehicle->vehicle_type ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                {{-- SAĞ: Aksiyon butonları --}}
                <div class="flex flex-wrap items-center gap-3">
                    <button onclick="toggleAIChat()"
                            class="group relative inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-indigo-500 to-purple-600 px-5 py-3.5 text-sm font-black text-white shadow-xl shadow-indigo-900/40 transition-all hover:-translate-y-0.5 hover:shadow-2xl">
                        <span class="group-hover:rotate-12 transition-transform">
                            <svg class="w-5 h-5 drop-shadow-[0_2px_2px_rgba(255,255,255,0.4)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.792 0-5.484-.41-8.032-1.187-1.717-.293-2.3-2.379-1.067-3.61L5 14.5" /></svg>
                        </span>
                        <span class="uppercase tracking-widest text-xs">AI Analiz</span>
                    </button>

                    <div class="h-10 w-px bg-white/10"></div>

                    @if($canEditV)
                        <a href="{{ route('vehicles.edit', $vehicle) }}"
                           class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-xl border border-white/20 text-white hover:bg-amber-500 hover:border-amber-400 hover:text-white transition-all shadow-lg"
                           title="Düzenle">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                    @endif

                    @if($canDeleteV)
                        <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST"
                              onsubmit="return confirm('⚠️ KRİTİK: {{ $vehicle->plate }} plakalı aracı ve tüm geçmişini silmek istediğinize emin misiniz? Bu işlem geri alınamaz.');"
                              class="inline">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-xl border border-white/20 text-white hover:bg-rose-600 hover:border-rose-500 transition-all shadow-lg"
                                    title="Aracı Sil">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('vehicles.index') }}"
                       class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-xl border border-white/20 text-white hover:bg-white hover:text-slate-900 transition-all shadow-lg"
                       title="Listeye Dön">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════ --}}
    {{--  KPI KARTLARI - sadece genel sekme                                 --}}
    {{-- ════════════════════════════════════════════════════════════════ --}}
    @if($currentTab === 'general')
        @if(auth()->user()->hasPermission('financials.view'))
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            {{-- Hasılat --}}
            <div class="group relative overflow-hidden rounded-[24px] bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-600 p-5 text-white shadow-xl shadow-emerald-500/25 transition-all hover:-translate-y-1 hover:shadow-2xl">
                <div class="absolute -right-2 -bottom-2 opacity-100 group-hover:scale-110 group-hover:rotate-6 transition-all duration-700 drop-shadow-2xl">
                    <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Objects/Money%20Bag.png" alt="Hasılat" class="w-24 h-24 drop-shadow-2xl" />
                </div>
                <div class="relative z-10">
                    <div class="text-[9px] font-black uppercase tracking-[0.2em] text-emerald-100/80">Toplam Hasılat</div>
                    <div class="mt-2 text-2xl lg:text-3xl font-black tracking-tight leading-none">
                        {{ number_format($income, 0, ',', '.') }}
                        <span class="text-lg opacity-80">₺</span>
                    </div>
                    <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-white/15 px-2 py-1 text-[9px] font-bold backdrop-blur">
                        <span class="h-1 w-1 rounded-full bg-white"></span>
                        Gelir Akışı
                    </div>
                </div>
            </div>

            {{-- Yakıt Gideri --}}
            <div class="group relative overflow-hidden rounded-[24px] bg-gradient-to-br from-orange-500 via-orange-600 to-rose-600 p-5 text-white shadow-xl shadow-orange-500/25 transition-all hover:-translate-y-1 hover:shadow-2xl">
                <div class="absolute -right-2 -bottom-2 opacity-100 group-hover:scale-110 group-hover:rotate-6 transition-all duration-700 drop-shadow-2xl">
                    <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Travel%20and%20places/Fuel%20Pump.png" alt="Yakıt" class="w-24 h-24 drop-shadow-2xl" />
                </div>
                <div class="relative z-10">
                    <div class="text-[9px] font-black uppercase tracking-[0.2em] text-orange-100/80">Yakıt Gideri</div>
                    <div class="mt-2 text-2xl lg:text-3xl font-black tracking-tight leading-none">
                        {{ number_format($fuel, 0, ',', '.') }}
                        <span class="text-lg opacity-80">₺</span>
                    </div>
                    <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-white/15 px-2 py-1 text-[9px] font-bold backdrop-blur">
                        <span class="h-1 w-1 rounded-full bg-white"></span>
                        Tüketim
                    </div>
                </div>
            </div>

            {{-- Personel --}}
            <div class="group relative overflow-hidden rounded-[24px] bg-gradient-to-br from-indigo-500 via-indigo-600 to-purple-700 p-5 text-white shadow-xl shadow-indigo-500/25 transition-all hover:-translate-y-1 hover:shadow-2xl">
                <div class="absolute -right-2 -bottom-2 opacity-100 group-hover:scale-110 group-hover:rotate-6 transition-all duration-700 drop-shadow-2xl">
                    <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/People/Man%20Pilot.png" alt="Personel" class="w-24 h-24 drop-shadow-2xl" />
                </div>
                <div class="relative z-10">
                    <div class="text-[9px] font-black uppercase tracking-[0.2em] text-indigo-100/80">Personel & Maaş</div>
                    <div class="mt-2 text-2xl lg:text-3xl font-black tracking-tight leading-none">
                        {{ number_format($salary, 0, ',', '.') }}
                        <span class="text-lg opacity-80">₺</span>
                    </div>
                    <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-white/15 px-2 py-1 text-[9px] font-bold backdrop-blur">
                        <span class="h-1 w-1 rounded-full bg-white"></span>
                        Net Giderler
                    </div>
                </div>
            </div>

            {{-- Karlılık --}}
            <div class="group relative overflow-hidden rounded-[24px] p-5 text-white shadow-xl transition-all hover:-translate-y-1 hover:shadow-2xl {{ $profit >= 0 ? 'bg-gradient-to-br from-blue-500 via-blue-600 to-cyan-600 shadow-blue-500/25' : 'bg-gradient-to-br from-rose-500 via-rose-600 to-pink-700 shadow-rose-500/25' }}">
                <div class="absolute -right-2 -bottom-2 opacity-100 group-hover:scale-110 group-hover:rotate-6 transition-all duration-700 drop-shadow-2xl">
                    @if($profit >= 0)
                        <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Symbols/Check%20Mark%20Button.png" alt="Kâr" class="w-24 h-24 drop-shadow-2xl" />
                    @else
                        <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Symbols/Cross%20Mark.png" alt="Zarar" class="w-24 h-24 drop-shadow-2xl" />
                    @endif
                </div>
                <div class="relative z-10">
                    <div class="text-[9px] font-black uppercase tracking-[0.2em] text-white/80">Net Karlılık</div>
                    <div class="mt-2 text-2xl lg:text-3xl font-black tracking-tight leading-none">
                        {{ number_format($profit, 0, ',', '.') }}
                        <span class="text-lg opacity-80">₺</span>
                    </div>
                    <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-white/15 px-2 py-1 text-[9px] font-bold backdrop-blur">
                        <span class="h-1 w-1 rounded-full bg-white"></span>
                        {{ $profit >= 0 ? 'Pozitif Durum' : 'Negatif Durum' }}
                    </div>
                </div>
            </div>
            </div>
        @endif

        {{-- ════════════════════════════════════════════════════════════ --}}
        {{--  BİLGİ KARTLARI: Şoför / KM / Muayene / Kasko / Kapasite vs  --}}
        {{-- ════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 2xl:grid-cols-4 gap-4 lg:gap-6">
            {{-- Atanan Şoför --}}
            <div class="group relative overflow-hidden rounded-[24px] bg-white border border-slate-100 p-6 shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-indigo-500 to-blue-500 opacity-90"></div>
                
                <div class="relative z-10 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 shadow-inner border border-indigo-100/50 group-hover:scale-110 transition-transform duration-500">
                            <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/People/Man%20Pilot.png" alt="Şoför" class="w-10 h-10 drop-shadow-md" />
                        </div>
                        @if($driverFullName)
                            <span class="h-3 w-3 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.6)] animate-pulse"></span>
                        @endif
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Şoför</div>
                        @if($driverFullName)
                            <div class="mt-1 text-base font-black text-slate-800 truncate" title="{{ $driverFullName }}">{{ $driverFullName }}</div>
                            @if($driverPhone)
                                <div class="mt-0.5 text-xs font-bold text-indigo-600 truncate">{{ $driverPhone }}</div>
                            @endif
                        @else
                            <div class="mt-1 text-base font-black text-slate-400">Atanmamış</div>
                            <div class="mt-0.5 text-xs font-bold text-rose-500">Atama Bekliyor</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Güncel KM --}}
            <div class="group relative overflow-hidden rounded-[24px] bg-white border border-slate-100 p-6 shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-emerald-500 to-teal-500 opacity-90"></div>
                
                <div class="relative z-10 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 shadow-inner border border-emerald-100/50 group-hover:scale-110 transition-transform duration-500">
                            <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Travel%20and%20places/Oncoming%20Automobile.png" alt="KM" class="w-10 h-10 drop-shadow-md" />
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Kilometre</div>
                        <div class="mt-1 text-xl font-black tracking-tight text-slate-800">{{ $formattedKm }}</div>
                        <div class="mt-0.5 text-xs font-bold text-emerald-600">KM (Kayıt)</div>
                    </div>
                </div>
            </div>

            {{-- Koltuk Kapasitesi --}}
            <div class="group relative overflow-hidden rounded-[24px] bg-white border border-slate-100 p-6 shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-purple-500 to-fuchsia-500 opacity-90"></div>
                
                <div class="relative z-10 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-purple-50 shadow-inner border border-purple-100/50 group-hover:scale-110 transition-transform duration-500">
                            <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Travel%20and%20places/Seat.png" alt="Koltuk" class="w-10 h-10 drop-shadow-md" />
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Koltuk</div>
                        <div class="mt-1 text-xl font-black tracking-tight text-slate-800">{{ $vehicle->seat_count ?: 'Bilinmiyor' }}</div>
                        <div class="mt-0.5 text-xs font-bold text-purple-600">Yolcu Kapasitesi</div>
                    </div>
                </div>
            </div>

            {{-- Ruhsat Sahibi --}}
            <div class="group relative overflow-hidden rounded-[24px] bg-white border border-slate-100 p-6 shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-sky-500 to-blue-500 opacity-90"></div>
                
                <div class="relative z-10 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-50 shadow-inner border border-sky-100/50 group-hover:scale-110 transition-transform duration-500">
                            <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Objects/Briefcase.png" alt="Ruhsat" class="w-10 h-10 drop-shadow-md" />
                        </div>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Ruhsat Sahibi</div>
                        <div class="mt-1 text-base font-black text-slate-800 truncate" title="{{ $vehicle->license_owner ?: 'Bilinmiyor' }}">{{ $vehicle->license_owner ?: 'Bilinmiyor' }}</div>
                        <div class="mt-0.5 text-xs font-bold text-sky-600 truncate">{{ $vehicle->owner_tax_or_tc_no ?: 'TC/VKN Yok' }}</div>
                    </div>
                </div>
            </div>

            {{-- Muayene (Gizlendi) --}}
            <!--
            <div class="group relative overflow-hidden rounded-[24px] bg-white border border-slate-100 p-6 shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-amber-500 to-orange-500 opacity-90"></div>
                
                <div class="relative z-10 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 shadow-inner border border-amber-100/50 group-hover:scale-110 transition-transform duration-500">
                            <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Objects/Magnifying%20Glass%20Tilted%20Right.png" alt="Muayene" class="w-10 h-10 drop-shadow-md" />
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Muayene</div>
                        <div class="mt-1 text-base font-black text-slate-800">{{ $inspectionInfo['text'] }}</div>
                        <span class="mt-1.5 inline-flex items-center rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-wider {{ $inspectionInfo['class'] }} shadow-sm">
                            {{ $inspectionInfo['status'] }}
                        </span>
                    </div>
                </div>
            </div>
            -->

            {{-- Kasko (Gizlendi) --}}
            <!--
            <div class="group relative overflow-hidden rounded-[24px] bg-white border border-slate-100 p-6 shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-rose-500 to-pink-500 opacity-90"></div>
                
                <div class="relative z-10 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 shadow-inner border border-rose-100/50 group-hover:scale-110 transition-transform duration-500">
                            <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Objects/Shield.png" alt="Kasko" class="w-10 h-10 drop-shadow-md" />
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Kasko</div>
                        <div class="mt-1 text-base font-black text-slate-800">{{ $kaskoInfo['text'] }}</div>
                        <span class="mt-1.5 inline-flex items-center rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-wider {{ $kaskoInfo['class'] }} shadow-sm">
                            {{ $kaskoInfo['status'] }}
                        </span>
                    </div>
                </div>
            </div>
            -->
        </div>
    @endif
</div>
