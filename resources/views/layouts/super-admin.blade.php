<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ($title ?? '') ? $title . ' • ' : '' }}ServisPilot Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .sidebar-scroll {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .sidebar-scroll::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased overflow-x-hidden">

@php $user = auth()->user(); @endphp

<div class="min-h-screen">
    <div class="flex">

        <!-- SIDEBAR -->
        <aside class="fixed left-0 top-0 z-40 h-screen w-[240px] overflow-hidden bg-gradient-to-b from-[#1a1a2e] to-[#16213e] text-white shadow-2xl">
            <div class="flex h-full flex-col">

                <div class="shrink-0 border-b border-white/10 px-5 py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-orange-500 text-2xl shadow-lg">
                            🛡️
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-[18px] leading-none font-extrabold tracking-tight text-white whitespace-nowrap">
                                ServisPilot
                            </div>
                            <div class="mt-1 text-[10px] uppercase tracking-[0.14em] text-rose-300 overflow-hidden text-ellipsis whitespace-nowrap">
                                Platform Yönetimi
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sidebar-scroll min-h-0 flex-1 overflow-y-auto px-3 py-5 space-y-6">

                    <div>
                        <div class="px-3 text-[11px] font-bold uppercase tracking-[0.2em] text-white/40">Platform</div>
                        <div class="mt-3 space-y-2">

                            <a href="{{ route('super-admin.dashboard') }}"
                               class="flex items-start gap-3 rounded-2xl px-4 py-4 transition {{ request()->routeIs('super-admin.dashboard') ? 'bg-white/12 shadow-lg' : 'hover:bg-white/10' }}">
                                <div class="mt-0.5 text-lg">📊</div>
                                <div>
                                    <div class="text-sm font-semibold text-white">Dashboard</div>
                                    <div class="text-xs text-white/60">Platform istatistikleri</div>
                                </div>
                            </a>

                            <a href="{{ route('super-admin.companies.index') }}"
                               class="flex items-start gap-3 rounded-2xl px-4 py-4 transition {{ request()->routeIs('super-admin.companies.*') ? 'bg-white/12 shadow-lg' : 'hover:bg-white/10' }}">
                                <div class="mt-0.5 text-lg">🏢</div>
                                <div>
                                    <div class="text-sm font-semibold text-white">Firmalar</div>
                                    <div class="text-xs text-white/60">Firma & lisans yönetimi</div>
                                </div>
                            </a>

                        </div>
                    </div>

                    <div>
                        <div class="px-3 text-[11px] font-bold uppercase tracking-[0.2em] text-white/40">Hızlı Erişim</div>
                        <div class="mt-3 space-y-2">
                            <a href="{{ route('dashboard') }}"
                               class="flex items-start gap-3 rounded-2xl px-4 py-4 transition hover:bg-white/10">
                                <div class="mt-0.5 text-lg">🚌</div>
                                <div>
                                    <div class="text-sm font-semibold text-white">Firma Paneli</div>
                                    <div class="text-xs text-white/60">Normal panele geç</div>
                                </div>
                            </a>
                        </div>
                    </div>

                </div>

                <div class="shrink-0 border-t border-white/10 p-3">
                    <div class="rounded-[24px] bg-white/10 p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-orange-500 text-sm font-bold text-white shadow-lg">
                                {{ strtoupper(substr($user->name ?? 'S', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-white">{{ $user->name ?? 'Admin' }}</div>
                                <div class="truncate text-xs text-rose-300">Super Admin</div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-slate-800 hover:bg-slate-100 transition">
                                    Çıkış Yap
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </aside>

        <!-- MAIN -->
        <div class="ml-[240px] flex-1 min-w-0">
            <main class="min-h-screen p-6 lg:p-7">
                <div class="rounded-[34px] border border-slate-200/60 bg-white/70 shadow-xl backdrop-blur-sm overflow-hidden">
                    <div class="border-b border-slate-200/70 bg-white/70 px-6 py-5 lg:px-7">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div>
                                <h1 class="text-[2.05rem] font-extrabold tracking-tight text-slate-900">
                                    @yield('title', 'Super Admin')
                                </h1>
                                <p class="mt-1 text-sm font-medium text-slate-500">
                                    @yield('subtitle', 'Platform yönetim ekranı')
                                </p>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-slate-700">
                                        {{ now()->format('d.m.Y H:i') }}
                                    </div>
                                </div>
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-orange-500 text-sm font-bold text-white shadow-lg">
                                    {{ strtoupper(substr($user->name ?? 'S', 0, 1)) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-100/80 p-6 lg:p-7">
                        @if(session('success'))
                            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-medium text-rose-800">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </div>
            </main>
        </div>

    </div>
</div>

</body>
</html>
