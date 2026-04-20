<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ($title ?? '') ? $title . ' • ' : '' }}ServisPilot</title>

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
        <aside class="fixed left-0 top-0 z-40 h-screen w-[240px] overflow-hidden bg-gradient-to-b from-[#1b245a] to-[#2b3ea8] text-white shadow-2xl">
            <div class="flex h-full flex-col">

                <div class="shrink-0 border-b border-white/10 px-5 py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-2xl">
                            🚌
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="text-[18px] leading-none font-extrabold tracking-tight text-white whitespace-nowrap">
                                ServisPilot
                            </div>
                            <div class="mt-1 text-[10px] uppercase tracking-[0.14em] text-white/70 overflow-hidden text-ellipsis whitespace-nowrap">
                                {{ $company->name ?? 'Ana Firma' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sidebar-scroll min-h-0 flex-1 overflow-y-auto px-3 py-5 space-y-6">

                    <div>
                        <div class="px-3 text-[11px] font-bold uppercase tracking-[0.2em] text-white/40">Yönetim</div>
                        <div class="mt-3 space-y-2">

                            <a href="{{ route('dashboard') }}"
                               class="flex items-start gap-3 rounded-2xl px-4 py-4 transition {{ request()->routeIs('dashboard') ? 'bg-white/12 shadow-lg' : 'hover:bg-white/10' }}">
                                <div class="mt-0.5 text-lg">🏠</div>
                                <div>
                                    <div class="text-sm font-semibold text-white">Ana Sayfa</div>
                                    <div class="text-xs text-white/60">Genel görünüm</div>
                                </div>
                            </a>

                            <a href="{{ route('vehicles.index') }}"
                               class="flex items-start gap-3 rounded-2xl px-4 py-4 transition {{ request()->routeIs('vehicles.*') ? 'bg-white/12 shadow-lg' : 'hover:bg-white/10' }}">
                                <div class="mt-0.5 text-lg">🚗</div>
                                <div>
                                    <div class="text-sm font-semibold text-white">Araçlar</div>
                                    <div class="text-xs text-white/60">Filo yönetimi</div>
                                </div>
                            </a>

                            <a href="{{ route('drivers.index') }}"
                               class="flex items-start gap-3 rounded-2xl px-4 py-4 transition {{ request()->routeIs('drivers.*') ? 'bg-white/12 shadow-lg' : 'hover:bg-white/10' }}">
                                <div class="mt-0.5 text-lg">🧑‍✈️</div>
                                <div>
                                    <div class="text-sm font-semibold text-white">Personeller</div>
                                    <div class="text-xs text-white/60">Personel kayıtları</div>
                                </div>
                            </a>

                            <a href="{{ route('maintenances.index') }}"
                               class="flex items-start gap-3 rounded-2xl px-4 py-4 transition {{ request()->routeIs('maintenances.*') ? 'bg-white/12 shadow-lg' : 'hover:bg-white/10' }}">
                                <div class="mt-0.5 text-lg">🛠️</div>
                                <div>
                                    <div class="text-sm font-semibold text-white">Bakım / Tamir</div>
                                    <div class="text-xs text-white/60">Araç bakım ve servis yönetimi</div>
                                </div>
                            </a>

                            <a href="{{ route('fuels.index') }}"
                               class="flex items-start gap-3 rounded-2xl px-4 py-4 transition {{ request()->routeIs('fuels.*') || request()->routeIs('fuel-stations.*') ? 'bg-white/12 shadow-lg' : 'hover:bg-white/10' }}">
                                <div class="mt-0.5 text-lg">⛽</div>
                                <div>
                                    <div class="text-sm font-semibold text-white">Yakıt</div>
                                    <div class="text-xs text-white/60">Yakıt ve maliyet kayıtları</div>
                                </div>
                            </a>

                            <a href="{{ route('traffic-penalties.index') }}"
                               class="flex items-start gap-3 rounded-2xl px-4 py-4 transition {{ request()->routeIs('traffic-penalties.*') ? 'bg-white/12 shadow-lg' : 'hover:bg-white/10' }}">
                                <div class="mt-0.5 text-lg">🚨</div>
                                <div>
                                    <div class="text-sm font-semibold text-white">Trafik Cezaları</div>
                                    <div class="text-xs text-white/60">Ceza ve ödeme takibi</div>
                                </div>
                            </a>

                            <a href="{{ route('trips.index') }}"
                               class="flex items-start gap-3 rounded-2xl px-4 py-4 transition {{ request()->routeIs('trips.*') ? 'bg-white/12 shadow-lg' : 'hover:bg-white/10' }}">
                                <div class="mt-0.5 text-lg">🗓️</div>
                                <div>
                                    <div class="text-sm font-semibold text-white">Puantaj / Sefer</div>
                                    <div class="text-xs text-white/60">Günlük operasyon akışı</div>
                                </div>
                            </a>

                            <a href="{{ route('payrolls.index') }}"
                               class="flex items-start gap-3 rounded-2xl px-4 py-4 transition {{ request()->routeIs('payrolls.*') ? 'bg-white/12 shadow-lg' : 'hover:bg-white/10' }}">
                                <div class="mt-0.5 text-lg">💵</div>
                                <div>
                                    <div class="text-sm font-semibold text-white">Maaşlar</div>
                                    <div class="text-xs text-white/60">Personel ödeme takibi</div>
                                </div>
                            </a>

                            <a href="{{ route('customers.index') }}"
                               class="flex items-start gap-3 rounded-2xl px-4 py-4 transition {{ request()->routeIs('customers.*') ? 'bg-white/12 shadow-lg' : 'hover:bg-white/10' }}">
                                <div class="mt-0.5 text-lg">🏢</div>
                                <div>
                                    <div class="text-sm font-semibold text-white">Müşteriler</div>
                                    <div class="text-xs text-white/60">Firma ve cari yönetimi</div>
                                </div>
                            </a>

                            <a href="{{ route('service-routes.index') }}"
                               class="flex items-start gap-3 rounded-2xl px-4 py-4 transition {{ request()->routeIs('service-routes.*') ? 'bg-white/12 shadow-lg' : 'hover:bg-white/10' }}">
                                <div class="mt-0.5 text-lg">🛣️</div>
                                <div>
                                    <div class="text-sm font-semibold text-white">Servis Hatları</div>
                                    <div class="text-xs text-white/60">Hat ve plan yönetimi</div>
                                </div>
                            </a>

                            <a href="{{ route('route-stops.index') }}"
                               class="flex items-start gap-3 rounded-2xl px-4 py-4 transition {{ request()->routeIs('route-stops.*') ? 'bg-white/12 shadow-lg' : 'hover:bg-white/10' }}">
                                <div class="mt-0.5 text-lg">📍</div>
                                <div>
                                    <div class="text-sm font-semibold text-white">Duraklar</div>
                                    <div class="text-xs text-white/60">Rota durak listesi</div>
                                </div>
                            </a>

                            <a href="{{ route('reports.index') }}"
                               class="flex items-start gap-3 rounded-2xl px-4 py-4 transition {{ request()->routeIs('reports.*') ? 'bg-white/12 shadow-lg' : 'hover:bg-white/10' }}">
                                <div class="mt-0.5 text-lg">📈</div>
                                <div>
                                    <div class="text-sm font-semibold text-white">Raporlar</div>
                                    <div class="text-xs text-white/60">Analiz ve çıktı</div>
                                </div>
                            </a>

                            <a href="{{ route('company-users.index') }}"
                               class="flex items-start gap-3 rounded-2xl px-4 py-4 transition {{ request()->routeIs('company-users.*') ? 'bg-white/12 shadow-lg' : 'hover:bg-white/10' }}">
                                <div class="mt-0.5 text-lg">👥</div>
                                <div>
                                    <div class="text-sm font-semibold text-white">Kullanıcılar</div>
                                    <div class="text-xs text-white/60">Yetki ve ekip yönetimi</div>
                                </div>
                            </a>

                            <a href="{{ route('company-settings.edit') }}"
                               class="flex items-start gap-3 rounded-2xl px-4 py-4 transition {{ request()->routeIs('company-settings.*') ? 'bg-white/12 shadow-lg' : 'hover:bg-white/10' }}">
                                <div class="mt-0.5 text-lg">⚙️</div>
                                <div>
                                    <div class="text-sm font-semibold text-white">Firma Ayarları</div>
                                    <div class="text-xs text-white/60">Temel sistem ayarları</div>
                                </div>
                            </a>

                        </div>
                    </div>

                </div>

                <div class="shrink-0 border-t border-white/10 p-3">
                    <div class="rounded-[24px] bg-white/10 p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-sm font-bold text-indigo-700">
                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-white">{{ $user->name ?? 'Kullanıcı' }}</div>
                                <div class="truncate text-xs text-white/60">{{ $user->email ?? '' }}</div>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <a href="{{ route('profile.edit') }}"
                               class="rounded-2xl bg-white/10 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-white/15 transition">
                                Profil
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-slate-800 hover:bg-slate-100 transition">
                                    Çıkış
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
                                    @yield('title', 'Panel')
                                </h1>
                                <p class="mt-1 text-sm font-medium text-slate-500">
                                    @yield('subtitle', 'Yönetim ekranı')
                                </p>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-slate-700">
                                        {{ now()->format('d.m.Y H:i') }}
                                    </div>
                                </div>

                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-sm font-bold text-white shadow-lg">
                                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
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