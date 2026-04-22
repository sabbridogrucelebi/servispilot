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

                <div class="sidebar-scroll min-h-0 flex-1 overflow-y-auto px-4 pb-12">
                    <div class="space-y-6">

                        @if($user && $user->isSuperAdmin())
                        <div class="pt-2">
                            <div class="px-4 mb-2 text-[10px] font-extrabold uppercase tracking-[0.25em] text-rose-500 opacity-80">Platform Yönetimi</div>
                            <div class="space-y-1">
                                <a href="{{ route('super-admin.dashboard') }}"
                                   class="group flex items-center gap-4 rounded-2xl px-4 py-3 transition-all duration-300 {{ request()->routeIs('super-admin.*') ? 'bg-rose-500/10 text-rose-400' : 'hover:bg-white/5 hover:text-slate-200' }}">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ request()->routeIs('super-admin.*') ? 'bg-rose-500/20 text-rose-400' : 'bg-slate-800/50 text-slate-500' }} group-hover:scale-110 transition-all shadow-inner">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04a11.357 11.357 0 00-1.018 4.772c0 4.113 2.193 7.713 5.5 9.69a11.354 11.354 0 0011.001 0c3.307-1.977 5.5-5.577 5.5-9.69a11.357 11.357 0 00-1.018-4.772z"></path></svg>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold tracking-tight">Süper Admin</span>
                                        <span class="text-[9px] font-medium opacity-40">Sistem Yetkilisi</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                        @endif

                        <div class="pt-2">
                            <div class="px-4 mb-2 text-[10px] font-extrabold uppercase tracking-[0.25em] text-slate-600">Ana Menü</div>
                            <div class="space-y-1">

                                @php
                                    $user = auth()->user();
                                @endphp

                                @foreach($navItems as $item)
                                    @php
                                        $isActive = request()->routeIs(explode('.', $item['route'])[0].'.*') || request()->routeIs($item['route']);
                                        $canAccess = !$item['module'] || $user->canAccessModule($item['module']);
                                    @endphp

                                    @if($canAccess)
                                        <a href="{{ route($item['route']) }}"
                                           class="group relative flex items-center gap-4 rounded-2xl px-4 py-2.5 transition-all duration-500
                                           {{ $isActive
                                              ? 'nav-item-active text-white'
                                              : 'text-slate-300 hover:bg-white/[0.05] hover:text-white' }}">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-all duration-500 {{ $isActive ? 'bg-indigo-500 shadow-[0_0_20px_rgba(79,70,229,0.5)] text-white' : 'bg-slate-800/60 text-slate-400 group-hover:bg-slate-700 group-hover:text-indigo-300' }} group-hover:scale-110">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path></svg>
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <span class="text-[13px] font-bold tracking-tight truncate">{{ $item['label'] }}</span>
                                                <span class="text-[9px] font-bold {{ $isActive ? 'text-indigo-200/60' : 'text-slate-500' }} uppercase tracking-widest truncate">{{ $item['sub'] }}</span>
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
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="ml-72 flex-1 min-w-0 min-h-screen flex flex-col bg-slate-50">
            <main class="flex-1 p-6 lg:p-8">
                <div class="h-full rounded-[40px] border border-white bg-white/60 shadow-[0_20px_50px_-12px_rgba(0,0,0,0.08)] backdrop-blur-xl overflow-hidden flex flex-col">
                    
                    <!-- GLASS HEADER -->
                    <div class="glass-header border-b border-slate-200/40 px-8 py-6 shrink-0">
                        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                            <!-- Left: Title & Subtitle -->
                            <div class="flex items-center gap-6">
                                <div class="hidden h-12 w-1.5 shadow-lg rounded-full bg-gradient-to-b from-indigo-500 to-purple-600 md:block"></div>
                                <div>
                                    <h1 class="text-3xl font-black tracking-tight text-slate-900">
                                        @yield('title', 'Sistem Paneli')
                                    </h1>
                                    <p class="mt-1 text-[11px] font-bold text-slate-400 flex items-center gap-2 uppercase tracking-widest">
                                        <span class="relative flex h-2 w-2">
                                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                        </span>
                                        @yield('subtitle', 'Operasyonel Kontrol Merkezi')
                                    </p>
                                </div>
                            </div>

                            <!-- Right: Actions & Profile -->
                            <div class="flex items-center gap-4 sm:gap-6">
                                <!-- Destek Butonu -->
                                <a href="{{ route('support') }}" 
                                   class="group relative hidden lg:flex items-center gap-3 overflow-hidden rounded-2xl bg-slate-900 px-5 py-2.5 transition-all hover:bg-slate-800 hover:shadow-xl hover:shadow-slate-200 border border-white/10">
                                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-teal-600 opacity-0 transition-opacity group-hover:opacity-10"></div>
                                    <div class="relative flex flex-col items-start leading-none">
                                        <span class="text-[7px] font-black uppercase tracking-[0.2em] text-slate-500 group-hover:text-emerald-400 transition-colors">Yazılım Mimarı</span>
                                        <span class="text-[10px] font-black text-white mt-1">DESTEK AL</span>
                                    </div>
                                    <div class="relative flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white transition-all shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    </div>
                                </a>

                                <!-- Canlı Sistem Saati & Çıkış -->
                                <div class="hidden items-center gap-4 md:flex">
                                    <div class="flex flex-col items-end">
                                        <div class="text-[8px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1 leading-none">Canlı Sistem Saati</div>
                                        <div class="flex items-center gap-2">
                                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-indigo-50 border border-indigo-100/50 shadow-sm group hover:scale-105 transition-all">
                                                <span class="text-[11px] font-black text-indigo-600" id="clock-date">{{ now()->format('d.m.Y') }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 shadow-lg group hover:scale-105 transition-all">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                <span class="text-[11px] font-black text-white tracking-wider" id="clock-time">{{ now()->format('H:i:s') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hızlı Çıkış -->
                                    <form method="POST" action="{{ route('logout') }}" class="inline">
                                        @csrf
                                        <button type="submit" class="group relative flex h-11 w-11 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-600 hover:text-white transition-all shadow-sm hover:shadow-rose-200 active:scale-90" title="Güvenli Çıkış">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                                <div class="relative group" x-data="{ open: false }">
                                    <button @click="open = !open" class="relative flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-md font-black text-indigo-600 shadow-lg border border-white hover:scale-105 transition-all active:scale-95">
                                        <div class="absolute -inset-1 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 opacity-20 blur group-hover:opacity-100 transition duration-500"></div>
                                        <span class="relative text-sm">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</span>
                                        <div class="absolute -bottom-1 -right-1 h-3.5 w-3.5 rounded-full bg-emerald-500 border-2 border-white shadow-lg"></div>
                                    </button>

                                    <!-- User Dropdown -->
                                    <div x-show="open" @click.away="open = false" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 translate-y-4"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         class="absolute right-0 mt-4 w-64 origin-top-right rounded-[32px] bg-white p-4 shadow-2xl ring-1 ring-black/5 z-50" x-cloak>
                                        <div class="px-4 py-3 border-b border-slate-50">
                                            <div class="text-sm font-black text-slate-900 leading-tight">{{ $user->name }}</div>
                                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tight mt-0.5">{{ $user->email }}</div>
                                        </div>
                                        <div class="p-2 space-y-1">
                                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-indigo-600 rounded-2xl transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                Profilim
                                            </a>
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit" class="flex w-full items-center gap-3 px-4 py-3 text-sm font-bold text-rose-600 hover:bg-rose-50 rounded-2xl transition-all text-left">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                                    Güvenli Çıkış
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MAIN SCROLLABLE CONTENT -->
                    <div class="flex-1 overflow-y-auto p-6 lg:p-8 bg-slate-50/50">
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
            const dateEl = document.getElementById('clock-date');
            const timeEl = document.getElementById('clock-time');
            if (dateEl && timeEl) {
                dateEl.innerText = now.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
                timeEl.innerText = now.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
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