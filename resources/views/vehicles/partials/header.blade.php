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
                <div class="absolute -right-6 -bottom-6 opacity-100 group-hover:scale-110 group-hover:rotate-6 transition-all duration-700 text-emerald-300/40 mix-blend-overlay drop-shadow-2xl">
                    <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
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
                <div class="absolute -right-6 -bottom-6 opacity-100 group-hover:scale-110 group-hover:rotate-6 transition-all duration-700 text-orange-200/40 mix-blend-overlay drop-shadow-2xl">
                    <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26" /></svg>
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
                <div class="absolute -right-6 -bottom-6 opacity-100 group-hover:scale-110 group-hover:rotate-6 transition-all duration-700 text-indigo-300/40 mix-blend-overlay drop-shadow-2xl">
                    <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
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
                <div class="absolute -right-6 -bottom-6 opacity-100 group-hover:scale-110 group-hover:rotate-6 transition-all duration-700 text-white/40 mix-blend-overlay drop-shadow-2xl">
                    @if($profit >= 0)
                        <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg>
                    @else
                        <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.51m-3.182 5.51l-5.511-3.181" /></svg>
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
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
            {{-- Atanan Şoför --}}
            <div class="group relative overflow-hidden rounded-[20px] bg-white border border-slate-200/80 p-4 shadow hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-500"></div>
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-[14px] bg-gradient-to-br from-indigo-500 to-blue-600 text-white shadow-md">
                            <svg class="w-5 h-5 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                        </div>
                        @if($driverFullName)
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)] animate-pulse"></span>
                        @endif
                    </div>
                    <div>
                        <div class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Şoför</div>
                        @if($driverFullName)
                            <div class="mt-0.5 text-xs font-black text-slate-900 truncate" title="{{ $driverFullName }}">{{ $driverFullName }}</div>
                            @if($driverPhone)
                                <div class="text-[10px] font-bold text-indigo-600 truncate">{{ $driverPhone }}</div>
                            @endif
                        @else
                            <div class="mt-0.5 text-xs font-black text-slate-400">Atanmamış</div>
                            <div class="text-[10px] font-bold text-rose-500">Atama Bekliyor</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Güncel KM --}}
            <div class="group relative overflow-hidden rounded-[20px] bg-white border border-slate-200/80 p-4 shadow hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-[14px] bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md">
                            <svg class="w-5 h-5 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20.247V15a3 3 0 013-3h0a3 3 0 013 3v5.247M12 9v2m0-6h.01M12 3a9 9 0 100 18 9 9 0 000-18z" /></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Kilometre</div>
                        <div class="mt-0.5 text-sm font-black text-slate-900">{{ $formattedKm }}</div>
                        <div class="text-[10px] font-bold text-emerald-600">KM (Kayıt)</div>
                    </div>
                </div>
            </div>

            {{-- Koltuk Kapasitesi --}}
            <div class="group relative overflow-hidden rounded-[20px] bg-white border border-slate-200/80 p-4 shadow hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-purple-500 to-fuchsia-500"></div>
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-[14px] bg-gradient-to-br from-purple-500 to-fuchsia-500 text-white shadow-md">
                            <svg class="w-5 h-5 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Koltuk</div>
                        <div class="mt-0.5 text-sm font-black text-slate-900">{{ $vehicle->seat_count ?: 'Bilinmiyor' }}</div>
                        <div class="text-[10px] font-bold text-purple-600">Yolcu Kapasitesi</div>
                    </div>
                </div>
            </div>

            {{-- Ruhsat Sahibi --}}
            <div class="group relative overflow-hidden rounded-[20px] bg-white border border-slate-200/80 p-4 shadow hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-sky-500 to-blue-500"></div>
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-[14px] bg-gradient-to-br from-sky-500 to-blue-500 text-white shadow-md">
                            <svg class="w-5 h-5 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Ruhsat Sahibi</div>
                        <div class="mt-0.5 text-xs font-black text-slate-900 truncate" title="{{ $vehicle->license_owner ?: 'Bilinmiyor' }}">{{ $vehicle->license_owner ?: 'Bilinmiyor' }}</div>
                        <div class="text-[10px] font-bold text-sky-600 truncate">{{ $vehicle->owner_tax_or_tc_no ?: 'TC/VKN Yok' }}</div>
                    </div>
                </div>
            </div>

            {{-- Muayene --}}
            <div class="group relative overflow-hidden rounded-[20px] bg-white border border-slate-200/80 p-4 shadow hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-500 to-orange-500"></div>
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-[14px] bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-md">
                            <svg class="w-5 h-5 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" /></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Muayene</div>
                        <div class="mt-0.5 text-xs font-black text-slate-900">{{ $inspectionInfo['text'] }}</div>
                        <span class="mt-0.5 inline-flex items-center rounded-full px-1.5 py-0.5 text-[8px] font-black uppercase tracking-wider {{ $inspectionInfo['class'] }}">
                            {{ $inspectionInfo['status'] }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Kasko --}}
            <div class="group relative overflow-hidden rounded-[20px] bg-white border border-slate-200/80 p-4 shadow hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-rose-500 to-pink-500"></div>
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-[14px] bg-gradient-to-br from-rose-500 to-pink-600 text-white shadow-md">
                            <svg class="w-5 h-5 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Kasko</div>
                        <div class="mt-0.5 text-xs font-black text-slate-900">{{ $kaskoInfo['text'] }}</div>
                        <span class="mt-0.5 inline-flex items-center rounded-full px-1.5 py-0.5 text-[8px] font-black uppercase tracking-wider {{ $kaskoInfo['class'] }}">
                            {{ $kaskoInfo['status'] }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
