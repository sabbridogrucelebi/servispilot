<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ($title ?? '') ? $title . ' • ' : '' }}ServisPilot</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200..800&display=swap');

        :root {
            --sidebar-bg: #0f172a; /* Biraz daha derin lacivert */
            --sidebar-hover: rgba(255, 255, 255, 0.08);
            --accent-primary: #818cf8; /* Daha parlak indigo */
            --accent-secondary: #c084fc; /* Daha parlak mor */
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
        }

        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.1) transparent;
        }

        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 10px; }

        .glass-header {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .mesh-gradient {
            background-color: var(--sidebar-bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.25) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(168, 85, 247, 0.15) 0px, transparent 50%),
                radial-gradient(at 50% 100%, rgba(79, 70, 229, 0.1) 0px, transparent 50%);
        }

        .nav-item-active {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.15) 0%, transparent 100%);
            border-left: 4px solid var(--accent-primary);
            box-shadow: inset 4px 0 10px -2px rgba(99, 102, 241, 0.3);
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased overflow-x-hidden">

@php
    $user = auth()->user();
    $company = $user?->company;

    $expiredVehicleDocuments = collect();
    $driverDocumentAlerts = collect();

    if ($user && $company) {
        $expiredVehicleDocuments = \App\Models\Document::query()
            ->where('documentable_type', \App\Models\Fleet\Vehicle::class)
            ->where('is_active', true)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', now()->toDateString())
            ->with('documentable')
            ->orderBy('end_date')
            ->get()
            ->filter(fn ($doc) => $doc->documentable);

        $driverDocumentAlerts = collect(\App\Http\Controllers\DriverController::getDriverDocumentAlertsForLayout());
    }
@endphp

<div class="min-h-screen">
    <div class="flex">

        <!-- SIDEBAR -->
        <aside class="fixed left-0 top-0 z-40 h-screen w-72 mesh-gradient text-slate-400 shadow-[20px_0_40px_rgba(0,0,0,0.2)] transition-all duration-500 overflow-hidden border-r border-white/5">
            <div class="flex h-full flex-col">

                <!-- Brand Area -->
                <div class="shrink-0 px-8 py-10">
                    <div class="flex items-center gap-4">
                        <div class="relative group">
                            <div class="absolute -inset-1 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 opacity-40 blur-sm group-hover:opacity-100 transition duration-500"></div>
                            <div class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-900 border border-white/10 text-2xl">
                                🚀
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="text-2xl font-black tracking-tighter text-white leading-none">
                                Servis<span class="text-indigo-500">Pilot</span>
                            </div>
                            <div class="mt-2 text-[9px] font-bold uppercase tracking-[0.3em] text-slate-500 whitespace-nowrap">
                                {{ $company->name ?? 'Kurumsal Sürüm' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sidebar-scroll min-h-0 flex-1 overflow-y-auto px-4 pb-32">
                    <div class="space-y-10">

                        @if($user && $user->isSuperAdmin())
                        <div>
                            <div class="px-4 mb-4 text-[10px] font-extrabold uppercase tracking-[0.25em] text-rose-500 opacity-80">Platform Yönetimi</div>
                            <div class="space-y-1.5">
                                <a href="{{ route('super-admin.dashboard') }}"
                                   class="group flex items-center gap-4 rounded-2xl px-4 py-3.5 transition-all duration-300 {{ request()->routeIs('super-admin.*') ? 'bg-rose-500/10 text-rose-400' : 'hover:bg-white/5 hover:text-slate-200' }}">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ request()->routeIs('super-admin.*') ? 'bg-rose-500/20 text-rose-400' : 'bg-slate-800/50 text-slate-500' }} group-hover:scale-110 transition-all shadow-inner">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04a11.357 11.357 0 00-1.018 4.772c0 4.113 2.193 7.713 5.5 9.69a11.354 11.354 0 0011.001 0c3.307-1.977 5.5-5.577 5.5-9.69a11.357 11.357 0 00-1.018-4.772z"></path></svg>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold tracking-tight">Süper Admin</span>
                                        <span class="text-[10px] font-medium opacity-40">Sistem Yetkilisi</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                        @endif

                        <div>
                            <div class="px-4 mb-4 text-[10px] font-extrabold uppercase tracking-[0.25em] text-slate-600">Ana Menü</div>
                            <div class="space-y-1.5">

                                @php
                                    $navItems = [
                                        ['route' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Ana Sayfa', 'sub' => 'Genel Bakış', 'module' => null],
                                        ['route' => 'vehicles.index', 'icon' => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0', 'label' => 'Araçlar', 'sub' => 'Filo Yönetimi', 'module' => 'vehicles'],
                                        ['route' => 'vehicle-tracking.index', 'icon' => 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z', 'label' => 'Araç Takip', 'sub' => 'Canlı İzleme', 'module' => 'vehicles'],
                                        ['route' => 'drivers.index', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'label' => 'Personeller', 'sub' => 'Personel Yönetimi', 'module' => 'drivers'],
                                        ['route' => 'maintenances.index', 'icon' => 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 11-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z', 'label' => 'Bakım / Tamir', 'sub' => 'Servis ve Bakım', 'module' => 'maintenances'],
                                        ['route' => 'fuels.index', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'label' => 'Yakıt', 'sub' => 'Yakıt Takibi', 'module' => 'fuels'],
                                        ['route' => 'traffic-penalties.index', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'label' => 'Trafik Cezaları', 'sub' => 'Yasal ve Uyumluluk', 'module' => 'traffic_penalties'],
                                        ['route' => 'trips.index', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'label' => 'Puantaj / Sefer', 'sub' => 'Operasyon Kayıtları', 'module' => 'trips'],
                                        ['route' => 'payrolls.index', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Maaşlar', 'sub' => 'Finansal Kayıtlar', 'module' => 'payrolls'],
                                        ['route' => 'customers.index', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'label' => 'Müşteriler', 'sub' => 'Müşteri Yönetimi', 'module' => 'customers'],
                                        ['route' => 'service-routes.index', 'icon' => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7', 'label' => 'Servis Hatları', 'sub' => 'Rota Planlama', 'module' => 'service_routes'],
                                        ['route' => 'route-stops.index', 'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Duraklar', 'sub' => 'Konum İşaretleri', 'module' => 'route_stops'],
                                        ['route' => 'reports.index', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'label' => 'Raporlar', 'sub' => 'Analiz Merkezi', 'module' => 'reports'],
                                        ['route' => 'company-users.index', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'label' => 'Kullanıcılar', 'sub' => 'Erişim Kontrolü', 'module' => null],
                                        ['route' => 'company-settings.edit', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Ayarlar', 'sub' => 'Sistem Yapılandırması', 'module' => null],
                                    ];
                                @endphp

                                @foreach($navItems as $item)
                                    @php
                                        $isActive = request()->routeIs(explode('.', $item['route'])[0].'.*') || request()->routeIs($item['route']);
                                        $canAccess = !$item['module'] || $user->canAccessModule($item['module']);
                                    @endphp

                                    @if($canAccess)
                                        <a href="{{ route($item['route']) }}"
                                           class="group relative flex items-center gap-4 rounded-2xl px-4 py-3 transition-all duration-500
                                           {{ $isActive
                                              ? 'nav-item-active text-white'
                                              : 'text-slate-300 hover:bg-white/[0.05] hover:text-white' }}">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-all duration-500 {{ $isActive ? 'bg-indigo-500 shadow-[0_0_20px_rgba(79,70,229,0.5)] text-white' : 'bg-slate-800/60 text-slate-400 group-hover:bg-slate-700 group-hover:text-indigo-300' }} group-hover:scale-110">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path></svg>
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <span class="text-[13.5px] font-bold tracking-tight truncate">{{ $item['label'] }}</span>
                                                <span class="text-[9.5px] font-bold {{ $isActive ? 'text-indigo-200/60' : 'text-slate-500' }} uppercase tracking-widest truncate">{{ $item['sub'] }}</span>
                                            </div>
                                            @if($isActive)
                                                <div class="ml-auto flex items-center">
                                                    <div class="h-2 w-2 rounded-full bg-indigo-400 shadow-[0_0_10px_rgba(129,140,248,0.8)] animate-pulse"></div>
                                                </div>
                                            @endif
                                        </a>
                                    @endif
                                @endforeach

                            </div>
                        </div>

                    </div>
                </div>

                <!-- Footer User Area -->
                <div class="shrink-0 p-6 bg-slate-900/50 backdrop-blur-2xl border-t border-white/5 space-y-4">
                    <!-- Developer Signature -->
                    <div class="flex items-center justify-between px-2 mb-2">
                        <div class="flex flex-col">
                            <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 leading-tight">Yazılım Mimarı</span>
                            <a href="https://wa.me/905305283669" target="_blank" class="group flex items-center gap-1.5 transition-all">
                                <span class="text-xs font-bold text-indigo-300 group-hover:text-indigo-200">Sabri DOĞRU</span>
                                <div class="h-1 w-1 rounded-full bg-emerald-500"></div>
                            </a>
                        </div>
                        <a href="https://wa.me/905305283669" target="_blank" class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-500 hover:bg-emerald-500 hover:text-white transition-all shadow-lg shadow-emerald-500/10">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                    </div>

                    <div class="relative group p-4 rounded-3xl bg-white/[0.04] border border-white/10 shadow-2xl transition-all duration-500 hover:bg-white/[0.06]">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <div class="absolute -inset-1 rounded-2xl bg-indigo-500 opacity-20 blur group-hover:opacity-40 transition duration-500"></div>
                                <div class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-900 border border-white/20 text-md font-black text-indigo-400">
                                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full bg-emerald-500 border-2 border-slate-900 shadow-lg"></div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-[13px] font-black text-white leading-tight">{{ $user->name ?? 'Operator' }}</div>
                                <div class="truncate text-[10px] font-bold text-slate-500 uppercase tracking-tighter mt-0.5">{{ $user->email ?? '' }}</div>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <a href="{{ route('profile.edit') }}"
                               class="flex items-center justify-center gap-2 rounded-xl bg-indigo-600/10 py-2.5 text-[11px] font-black text-indigo-400 hover:bg-indigo-600 hover:text-white transition-all shadow-inner">
                                Profili Düzenle
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-rose-500/10 py-2.5 text-[11px] font-black text-rose-400 hover:bg-rose-600 hover:text-white transition-all">
                                    Oturumu Kapat
                                </button>
                            </form>
                        </div>
                    <style>
        [x-cloak] { display: none !important; }
        
        /* GLOBAL YAZDIRMA AYARLARI - SADECE BELGE KALSIN */
        @media print {
            aside, header, nav, footer, .no-print, button, .sidebar-wrapper, .top-nav {
                display: none !important;
            }
            main, .content-wrapper, #app {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                display: block !important;
            }
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .mx-auto {
                max-width: 100% !important;
            }
        }
    </style>
                    </div>
                </div>

            </div>
        </aside>

        <!-- MAIN -->
        <div class="ml-72 flex-1 min-w-0">
            <main class="min-h-screen p-8 lg:p-10">
                <div class="rounded-[40px] border border-white bg-white/40 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] backdrop-blur-xl overflow-hidden transition-all duration-500">
                    <div class="glass-header border-b border-slate-200/40 px-10 py-8">
                        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                            <div class="flex items-center gap-6">
                                <div class="hidden h-14 w-1.5 shadow-lg rounded-full bg-gradient-to-b from-indigo-500 to-purple-600 md:block"></div>
                                <div>
                                    <h1 class="text-4xl font-black tracking-tight text-slate-900">
                                        @yield('title', 'Sistem Paneli')
                                    </h1>
                                    <p class="mt-2 text-[13px] font-bold text-slate-400 flex items-center gap-2 uppercase tracking-widest">
                                        <span class="relative flex h-2 w-2">
                                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                        </span>
                                        @yield('subtitle', 'Operasyonel Kontrol Merkezi')
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-8">
                                <div class="hidden flex-col items-end md:flex">
                                    <div class="text-[9px] font-black uppercase tracking-[0.3em] text-slate-400 mb-1">Güncel Sistem Saati</div>
                                    <div class="text-md font-black text-indigo-600 bg-indigo-50 px-5 py-2 rounded-2xl border border-indigo-100 shadow-sm transition-all hover:scale-105" id="global-live-clock">
                                        {{ now()->format('d.m.Y H:i:s') }}
                                    </div>
                                </div>

                                <div class="relative group">
                                    <div class="absolute -inset-1 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 opacity-20 blur group-hover:opacity-100 transition duration-500"></div>
                                    <div class="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-md font-black text-indigo-600 shadow-xl border border-white">
                                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-100/80 p-6 lg:p-7">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>

    </div>
</div>

@if($expiredVehicleDocuments->count())
    <div id="expiredVehicleDocumentsGlobalModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/55 p-4">
        <div class="w-full max-w-2xl rounded-[30px] bg-white shadow-2xl overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-2xl">
                        ⚠️
                    </div>
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-900">Günü Geçen Araç Belgeleri Var</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Lütfen aşağıdaki araç belgelerini güncelleyiniz.
                        </p>
                    </div>
                </div>
            </div>

            <div class="max-h-[60vh] overflow-y-auto p-6 space-y-4">
                @foreach($expiredVehicleDocuments as $document)
                    <div class="rounded-[24px] border border-rose-200 bg-rose-50 p-5">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <div class="text-base font-bold text-slate-900">
                                    {{ optional($document->documentable)->plate ?? 'Araç' }} plakalı aracınızın
                                    <span class="text-rose-700">{{ $document->document_type }}</span>
                                    belgesinin günü geçti.
                                </div>
                                <div class="mt-1 text-sm text-slate-600">
                                    Belge adı: {{ $document->document_name ?: '-' }}
                                </div>
                                <div class="mt-1 text-sm text-slate-600">
                                    Son geçerlilik tarihi:
                                    <span class="font-semibold text-rose-700">
                                        {{ optional($document->end_date)->format('d.m.Y') ?: '-' }}
                                    </span>
                                </div>
                            </div>

                            <a href="{{ route('vehicles.show', ['vehicle' => $document->documentable_id, 'tab' => 'documents']) }}"
                               class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-rose-600 to-pink-600 px-5 py-3 text-sm font-semibold text-white shadow hover:scale-[1.01] transition">
                                Belgeyi Güncelle
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-slate-100 px-6 py-4 bg-slate-50 flex justify-end">
                <button type="button"
                        id="closeExpiredVehicleDocumentsGlobalModal"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Daha Sonra Kapat
                </button>
            </div>
        </div>
    </div>
@endif

@if($driverDocumentAlerts->count())
    <div id="driverDocumentsGlobalModal" class="fixed inset-0 z-[61] flex items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-3xl rounded-[30px] bg-white shadow-2xl overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $driverDocumentAlerts->contains(fn ($alert) => $alert['status'] === 'expired') ? 'bg-rose-100' : 'bg-amber-100' }} text-2xl">
                        📄
                    </div>
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-900">Personel Belge Uyarıları Var</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Süresi geçen veya 7 gün içinde bitecek personel belgelerini güncelleyiniz.
                        </p>
                    </div>
                </div>
            </div>

            <div class="max-h-[65vh] overflow-y-auto p-6 space-y-4">
                @foreach($driverDocumentAlerts as $alert)
                    <div class="rounded-[24px] border {{ $alert['status'] === 'expired' ? 'border-rose-200 bg-rose-50' : 'border-amber-200 bg-amber-50' }} p-5">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <div class="text-base font-bold text-slate-900">
                                    <span class="{{ $alert['status'] === 'expired' ? 'text-rose-700' : 'text-amber-700' }}">
                                        {{ $alert['driver_name'] }}
                                    </span>
                                    adlı personele ait
                                    <span class="{{ $alert['status'] === 'expired' ? 'text-rose-700' : 'text-amber-700' }}">
                                        {{ $alert['document_type'] }}
                                    </span>
                                    belgesi
                                    {{ $alert['status'] === 'expired' ? 'süresi dolmuş durumda.' : 'bitmek üzere.' }}
                                </div>

                                <div class="mt-2 text-sm text-slate-600">
                                    Son geçerlilik tarihi:
                                    <span class="font-semibold {{ $alert['status'] === 'expired' ? 'text-rose-700' : 'text-amber-700' }}">
                                        {{ \Illuminate\Support\Carbon::parse($alert['end_date'])->format('d.m.Y') }}
                                    </span>
                                </div>

                                <div class="mt-1 text-sm font-semibold {{ $alert['status'] === 'expired' ? 'text-rose-700' : 'text-amber-700' }}">
                                    @if($alert['status'] === 'expired')
                                        {{ abs((int) $alert['remaining_days']) }} gün önce süresi doldu
                                    @else
                                        {{ (int) $alert['remaining_days'] }} gün kaldı
                                    @endif
                                </div>
                            </div>

                            <a href="{{ $alert['route'] }}"
                               class="inline-flex items-center justify-center rounded-2xl {{ $alert['status'] === 'expired' ? 'bg-gradient-to-r from-rose-600 to-pink-600' : 'bg-gradient-to-r from-amber-500 to-orange-500' }} px-5 py-3 text-sm font-semibold text-white shadow hover:scale-[1.01] transition">
                                Belgeyi Güncelle
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-slate-100 px-6 py-4 bg-slate-50 flex justify-end">
                <button type="button"
                        id="closeDriverDocumentsGlobalModal"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Daha Sonra Kapat
                </button>
            </div>
        </div>
    </div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function updateGlobalClock() {
            const now = new Date();
            const clockEl = document.getElementById('global-live-clock');
            if (clockEl) {
                const dateString = now.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
                const timeString = now.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                clockEl.innerText = dateString + ' ' + timeString;
            }
        }
        setInterval(updateGlobalClock, 1000);
        updateGlobalClock();

        const vehicleModal = document.getElementById('expiredVehicleDocumentsGlobalModal');
        const vehicleCloseBtn = document.getElementById('closeExpiredVehicleDocumentsGlobalModal');

        if (vehicleModal && vehicleCloseBtn) {
            vehicleCloseBtn.addEventListener('click', function () {
                vehicleModal.style.display = 'none';
            });

            vehicleModal.addEventListener('click', function (e) {
                if (e.target === vehicleModal) {
                    vehicleModal.style.display = 'none';
                }
            });
        }

        const driverModal = document.getElementById('driverDocumentsGlobalModal');
        const driverCloseBtn = document.getElementById('closeDriverDocumentsGlobalModal');

        if (driverModal && driverCloseBtn) {
            driverCloseBtn.addEventListener('click', function () {
                driverModal.style.display = 'none';
            });

            driverModal.addEventListener('click', function (e) {
                if (e.target === driverModal) {
                    driverModal.style.display = 'none';
                }
            });
        }
    });
</script>

</body>
</html>