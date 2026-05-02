@extends('layouts.customer-portal')

@section('content')
@php
    $activeTab = request('tab', 'company');

    $tabClass = function ($key) use ($activeTab) {
        return $activeTab === $key
            ? 'inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/10'
            : 'inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 hover:border-slate-300 hover:bg-slate-50 transition';
    };

    $activeContract = $activeContract ?? null;
    $serviceRoutes = $serviceRoutes ?? collect();
@endphp

<div class="space-y-6">

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="relative overflow-hidden rounded-[36px] border border-slate-200/70 bg-white shadow-xl shadow-slate-200/60">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/70"></div>
        <div class="absolute -top-16 -right-16 h-56 w-56 rounded-full bg-blue-100/40 blur-3xl"></div>
        <div class="absolute -bottom-16 -left-10 h-56 w-56 rounded-full bg-indigo-100/30 blur-3xl"></div>

        <div class="relative px-6 py-8 md:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[22px] bg-gradient-to-br from-blue-600 via-indigo-600 to-violet-600 text-3xl text-white shadow-lg shadow-blue-500/20">
                        🏢
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                Müşteri Portalı
                            </span>

                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                Aktif Erişim
                            </span>
                        </div>

                        <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900 md:text-4xl">
                            {{ $customer->company_name }}
                        </h1>

                        <p class="mt-2 text-sm font-medium text-slate-500 md:text-base">
                            {{ $customer->company_title ?: 'Firma ünvanı belirtilmemiş.' }}
                        </p>

                        <div class="mt-4 flex flex-wrap gap-5 text-sm text-slate-500">
                            <div>
                                <span class="font-semibold text-slate-700">Yetkili:</span>
                                {{ $customer->authorized_person ?: '-' }}
                            </div>
                            <div>
                                <span class="font-semibold text-slate-700">Telefon:</span>
                                {{ $customer->authorized_phone ?: '-' }}
                            </div>
                            <div>
                                <span class="font-semibold text-slate-700">E-Posta:</span>
                                {{ $customer->email ?: '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-3xl bg-white/80 px-5 py-4 shadow-sm ring-1 ring-slate-200/70 backdrop-blur">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            Geçerli Sözleşme
                        </div>
                        <div class="mt-2 text-lg font-bold text-slate-900">
                            {{ $activeContract ? $activeContract->year . ' Sözleşmesi' : 'Aktif sözleşme yok' }}
                        </div>
                    </div>

                    <div class="rounded-3xl bg-white/80 px-5 py-4 shadow-sm ring-1 ring-slate-200/70 backdrop-blur">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            Portal Kullanıcısı
                        </div>
                        <div class="mt-2 text-lg font-bold text-slate-900">
                            {{ $user->name }}
                        </div>
                        <div class="mt-1 text-xs text-slate-500">
                            {{ $user->username ?: '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

        <div class="relative overflow-hidden rounded-[30px] bg-gradient-to-br from-blue-600 via-indigo-600 to-violet-600 p-5 text-white shadow-xl shadow-blue-500/20">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <div class="text-xs font-bold uppercase tracking-[0.14em] text-white/75">Firma Adı</div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 text-xl shadow-sm">
                        🏢
                    </div>
                </div>
                <div class="mt-5 text-2xl font-black leading-tight">{{ $customer->company_name }}</div>
                <div class="mt-2 text-xs text-white/75">Portalda görüntülenen firma</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[30px] bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-500 p-5 text-white shadow-xl shadow-emerald-500/20">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <div class="text-xs font-bold uppercase tracking-[0.14em] text-white/75">Yetkili</div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 text-xl shadow-sm">
                        👤
                    </div>
                </div>
                <div class="mt-5 text-2xl font-black leading-tight">{{ $customer->authorized_person ?: '-' }}</div>
                <div class="mt-2 text-xs text-white/75">Tanımlı irtibat kişisi</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[30px] bg-gradient-to-br from-violet-500 via-fuchsia-500 to-pink-500 p-5 text-white shadow-xl shadow-violet-500/20">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <div class="text-xs font-bold uppercase tracking-[0.14em] text-white/75">Telefon</div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 text-xl shadow-sm">
                        📞
                    </div>
                </div>
                <div class="mt-5 text-2xl font-black leading-tight">{{ $customer->authorized_phone ?: '-' }}</div>
                <div class="mt-2 text-xs text-white/75">Kayıtlı iletişim numarası</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[30px] bg-gradient-to-br from-amber-400 via-orange-500 to-rose-500 p-5 text-white shadow-xl shadow-orange-500/20">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <div class="text-xs font-bold uppercase tracking-[0.14em] text-white/75">Sözleşme Sayısı</div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 text-xl shadow-sm">
                        📄
                    </div>
                </div>
                <div class="mt-5 text-2xl font-black leading-tight">{{ $contracts->count() }}</div>
                <div class="mt-2 text-xs text-white/75">Sistemde kayıtlı toplam sözleşme</div>
            </div>
        </div>

    </div>

    <div class="rounded-[30px] border border-slate-200/70 bg-white p-4 shadow-lg shadow-slate-200/40">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('customer.portal.dashboard', ['tab' => 'company']) }}" class="{{ $tabClass('company') }}">
                <span>🏢</span>
                <span>Firma Bilgileri</span>
            </a>

            <a href="{{ route('customer.portal.dashboard', ['tab' => 'services']) }}" class="{{ $tabClass('services') }}">
                <span>🛣️</span>
                <span>Servisler</span>
            </a>

            <a href="{{ route('customer.portal.dashboard', ['tab' => 'invoices']) }}" class="{{ $tabClass('invoices') }}">
                <span>🧾</span>
                <span>Faturalar</span>
            </a>

            <a href="{{ route('customer.portal.dashboard', ['tab' => 'contracts']) }}" class="{{ $tabClass('contracts') }}">
                <span>📄</span>
                <span>Sözleşmeler</span>
            </a>
        </div>
    </div>

    @if($activeTab === 'company')
        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-12">
            <div class="2xl:col-span-8 overflow-hidden rounded-[30px] border border-slate-200/70 bg-white shadow-lg shadow-slate-200/40">
                <div class="border-b border-slate-100 px-6 py-5">
                    <h3 class="text-lg font-bold text-slate-900">Firma Bilgileri</h3>
                    <p class="mt-1 text-sm text-slate-500">Size ait kayıtlı firma bilgileri</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div class="border-b border-r border-slate-100 px-6 py-5">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Firma Ünvanı</div>
                        <div class="mt-2 text-sm font-semibold text-slate-800">{{ $customer->company_title ?: '-' }}</div>
                    </div>

                    <div class="border-b border-slate-100 px-6 py-5">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">E-Posta</div>
                        <div class="mt-2 text-sm font-semibold text-slate-800">{{ $customer->email ?: '-' }}</div>
                    </div>

                    <div class="border-b border-r border-slate-100 px-6 py-5">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Müşteri Türü</div>
                        <div class="mt-2 text-sm font-semibold text-slate-800">{{ $customer->customer_type ?: '-' }}</div>
                    </div>

                    <div class="border-b border-slate-100 px-6 py-5">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">KDV Oranı</div>
                        <div class="mt-2 text-sm font-semibold text-slate-800">
                            %{{ $customer->vat_rate ? rtrim(rtrim((string) $customer->vat_rate, '0'), '.') : '0' }}
                        </div>
                    </div>

                    <div class="border-b border-slate-100 px-6 py-5 md:col-span-2">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Adres</div>
                        <div class="mt-2 text-sm leading-7 text-slate-700">{{ $customer->address ?: '-' }}</div>
                    </div>

                    <div class="px-6 py-5 md:col-span-2">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Notlar</div>
                        <div class="mt-2 text-sm leading-7 text-slate-700">{{ $customer->notes ?: 'Not bulunmuyor.' }}</div>
                    </div>
                </div>
            </div>

            <div class="2xl:col-span-4 space-y-6">
                <div class="overflow-hidden rounded-[30px] border border-slate-200/70 bg-white shadow-lg shadow-slate-200/40">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h3 class="text-lg font-bold text-slate-900">Portal Özeti</h3>
                        <p class="mt-1 text-sm text-slate-500">Müşteri görünümü</p>
                    </div>

                    <div class="space-y-4 px-6 py-6">
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Kullanıcı</div>
                            <div class="mt-2 text-sm font-bold text-slate-900">{{ $user->name }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $user->username ?: '-' }}</div>
                        </div>

                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Tevkifat</div>
                            <div class="mt-2 text-sm font-bold text-slate-900">{{ $customer->withholding_rate ?: 'Yok' }}</div>
                        </div>

                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Portal Tipi</div>
                            <div class="mt-2 text-sm font-bold text-slate-900">Müşteri Özel Erişim</div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[30px] border border-slate-200/70 bg-white shadow-lg shadow-slate-200/40">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h3 class="text-lg font-bold text-slate-900">Aktif Sözleşme</h3>
                        <p class="mt-1 text-sm text-slate-500">Güncel sözleşme durumu</p>
                    </div>

                    <div class="px-6 py-6">
                        @if($activeContract)
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4">
                                <div class="text-sm font-semibold text-emerald-700">Geçerli Sözleşme</div>
                                <div class="mt-2 text-lg font-bold text-slate-900">{{ $activeContract->year }}</div>
                                <div class="mt-2 text-xs text-slate-600">
                                    {{ $activeContract->start_date?->format('d.m.Y') }} - {{ $activeContract->end_date?->format('d.m.Y') }}
                                </div>
                            </div>
                        @else
                            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4">
                                <div class="text-sm font-semibold text-rose-700">Aktif sözleşme bulunamadı</div>
                                <div class="mt-2 text-xs text-slate-600">Lütfen yönetici ile iletişime geçin.</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($activeTab === 'services')
        <div class="overflow-hidden rounded-[30px] border border-slate-200/70 bg-white shadow-lg shadow-slate-200/40">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Servisler</h3>
                    <p class="mt-1 text-sm text-slate-500">Firmanıza ait tanımlı güzergah ve araç bilgileri</p>
                </div>

                <div class="text-sm font-medium text-slate-400">
                    Toplam {{ $serviceRoutes->count() }} kayıt
                </div>
            </div>

            <div class="p-6">
                @if($serviceRoutes->count())
                    <div class="space-y-4">
                        @foreach($serviceRoutes as $route)
                            <div class="rounded-[26px] border border-slate-200 bg-slate-50/70 p-5 shadow-sm">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-500 text-2xl text-white shadow">
                                        🛣️
                                    </div>

                                    <div class="w-full">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="text-lg font-bold text-slate-900">{{ $route->route_name }}</div>

                                            @if($route->is_active)
                                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">
                                                    Pasif
                                                </span>
                                            @endif
                                        </div>

                                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                                            <div class="rounded-2xl bg-white px-4 py-4">
                                                <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Güzergah Adı</div>
                                                <div class="mt-2 text-sm font-semibold text-slate-900">{{ $route->route_name ?: '-' }}</div>
                                            </div>

                                            <div class="rounded-2xl bg-white px-4 py-4">
                                                <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Servis Türü</div>
                                                <div class="mt-2 text-sm font-semibold text-slate-900">
                                                    @if($route->service_type === 'both')
                                                        Sabah ve Akşam
                                                    @elseif($route->service_type === 'morning')
                                                        Sadece Sabah
                                                    @elseif($route->service_type === 'evening')
                                                        Sadece Akşam
                                                    @elseif($route->service_type === 'shift')
                                                        Vardiya
                                                    @else
                                                        -
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="rounded-2xl bg-white px-4 py-4">
                                                <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Araç Cinsi</div>
                                                <div class="mt-2 text-sm font-semibold text-slate-900">{{ $route->vehicle_type ?: '-' }}</div>
                                            </div>

                                            <div class="rounded-2xl bg-white px-4 py-4">
                                                <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Durum</div>
                                                <div class="mt-2 text-sm font-semibold text-slate-900">{{ $route->is_active ? 'Aktif' : 'Pasif' }}</div>
                                            </div>
                                        </div>

                                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                                            <div class="rounded-2xl bg-white px-4 py-4">
                                                <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                                    Sabah Seferini Yapacak Araç
                                                </div>
                                                <div class="mt-2 text-sm font-semibold text-slate-900">
                                                    {{ $route->service_type !== 'shift' ? ($route->morningVehicle?->plate ?? '-') : '-' }}
                                                </div>
                                            </div>

                                            <div class="rounded-2xl bg-white px-4 py-4">
                                                <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                                    Akşam Seferini Yapacak Araç
                                                </div>
                                                <div class="mt-2 text-sm font-semibold text-slate-900">
                                                    {{ $route->service_type !== 'shift' ? ($route->eveningVehicle?->plate ?? '-') : '-' }}
                                                </div>
                                            </div>

                                            <div class="rounded-2xl bg-white px-4 py-4">
                                                <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                                    Vardiya Toplama Aracı
                                                </div>
                                                <div class="mt-2 text-sm font-semibold text-slate-900">
                                                    {{ $route->service_type === 'shift' ? ($route->morningVehicle?->plate ?? '-') : '-' }}
                                                </div>
                                            </div>

                                            <div class="rounded-2xl bg-white px-4 py-4">
                                                <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                                    Vardiya Dağıtım Aracı
                                                </div>
                                                <div class="mt-2 text-sm font-semibold text-slate-900">
                                                    {{ $route->service_type === 'shift' ? ($route->eveningVehicle?->plate ?? '-') : '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-16 text-center">
                        <div class="mx-auto max-w-md">
                            <div class="mb-3 text-5xl">🛣️</div>
                            <div class="text-base font-semibold text-slate-700">Henüz servis kaydı yok</div>
                            <div class="mt-1 text-sm text-slate-500">
                                Firmanıza ait tanımlı güzergah bilgisi bulunmuyor.
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($activeTab === 'invoices')
        <div class="overflow-hidden rounded-[30px] border border-slate-200/70 bg-white shadow-lg shadow-slate-200/40">
            <div class="border-b border-slate-100 px-6 py-5 bg-gradient-to-r from-slate-50 to-white flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Aylık Puantaj ve Fatura Özeti</h3>
                    <p class="mt-1 text-sm text-slate-500">Seçili aya ait sefer hakedişlerinizi görüntüleyin</p>
                </div>
                
                <form action="{{ route('customer.portal.dashboard') }}" method="GET" class="flex items-end gap-3">
                    <input type="hidden" name="tab" value="invoices">
                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-500 uppercase tracking-wider">Ay</label>
                        <select name="month" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400 outline-none">
                            @foreach($monthOptions as $mNum => $mName)
                                <option value="{{ $mNum }}" {{ $selectedMonth == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-500 uppercase tracking-wider">Yıl</label>
                        <select name="year" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400 outline-none">
                            @foreach($yearOptions as $y)
                                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-slate-700 transition">Göster</button>
                </form>
            </div>

            <div class="p-6 md:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-2xl font-black text-slate-900 tracking-tight uppercase">
                        {{ mb_strtoupper($monthOptions[$selectedMonth], 'UTF-8') }} {{ $selectedYear }} YILI PUANTAJI
                    </h4>
                    <a href="{{ route('customer.portal.trips', ['month' => $selectedMonth, 'year' => $selectedYear]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-bold text-indigo-700 hover:bg-indigo-100 transition">
                        <span>Puantajı İncele (Salt Okunur)</span>
                        <span>↗️</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="rounded-[24px] border border-slate-100 bg-slate-50 p-5 shadow-sm">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Ara Toplam</div>
                        <div class="text-2xl font-extrabold text-slate-800">₺{{ number_format($invoiceSummary['subtotal'], 2, ',', '.') }}</div>
                    </div>

                    <div class="rounded-[24px] border border-slate-100 bg-slate-50 p-5 shadow-sm">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">KDV (%{{ rtrim(rtrim((string)$invoiceSummary['vat_rate'], '0'), '.') }})</div>
                        <div class="text-2xl font-extrabold text-slate-800">₺{{ number_format($invoiceSummary['vat_amount'], 2, ',', '.') }}</div>
                    </div>

                    <div class="rounded-[24px] border border-slate-100 bg-slate-50 p-5 shadow-sm">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Tevkifat ({{ $invoiceSummary['withholding_rate'] ?: 'Yok' }})</div>
                        <div class="text-2xl font-extrabold text-slate-800">₺{{ number_format($invoiceSummary['withholding_amount'], 2, ',', '.') }}</div>
                    </div>

                    <div class="rounded-[24px] border border-transparent bg-gradient-to-br from-indigo-900 to-slate-900 p-5 shadow-lg text-white">
                        <div class="text-xs font-bold uppercase tracking-wider text-indigo-200 mb-1">Net Fatura Tutarı</div>
                        <div class="text-3xl font-black text-white">₺{{ number_format($invoiceSummary['net_total'], 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($activeTab === 'contracts')
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2 overflow-hidden rounded-[30px] border border-slate-200/70 bg-white shadow-lg shadow-slate-200/40">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Sözleşme Listesi</h3>
                        <p class="mt-1 text-sm text-slate-500">Firmanıza ait kayıtlı sözleşmeler</p>
                    </div>

                    <div class="text-sm font-medium text-slate-400">
                        Toplam {{ $contracts->count() }} kayıt
                    </div>
                </div>

                <div class="p-6">
                    @if($contracts->count())
                        <div class="space-y-4">
                            @foreach($contracts as $contract)
                                @php
                                    $isActive = $contract->is_active;
                                @endphp

                                <div class="rounded-[26px] border border-slate-200 bg-slate-50/70 p-5 shadow-sm">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="flex items-start gap-4">
                                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-500 text-2xl text-white shadow">
                                                📄
                                            </div>

                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <div class="text-lg font-bold text-slate-900">
                                                        {{ $contract->year }} Sözleşmesi
                                                    </div>

                                                    @if($isActive)
                                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                                            Geçerli Sözleşme
                                                        </span>
                                                    @else
                                                        <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">
                                                            Süresi Doldu
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="mt-3 grid grid-cols-1 gap-3 text-sm text-slate-600 md:grid-cols-3">
                                                    <div>
                                                        <span class="font-semibold text-slate-800">Başlangıç:</span>
                                                        {{ $contract->start_date?->format('d.m.Y') }}
                                                    </div>

                                                    <div>
                                                        <span class="font-semibold text-slate-800">Bitiş:</span>
                                                        {{ $contract->end_date?->format('d.m.Y') }}
                                                    </div>

                                                    <div>
                                                        <span class="font-semibold text-slate-800">Dosya:</span>
                                                        {{ $contract->original_name ?: 'Sözleşme dosyası' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <a href="{{ asset('storage/' . $contract->file_path) }}"
                                               target="_blank"
                                               class="inline-flex items-center gap-2 rounded-2xl bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                                <span>👁️</span>
                                                <span>Görüntüle</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-6 py-14 text-center">
                            <div class="mx-auto max-w-md">
                                <div class="mb-3 text-5xl">📄</div>
                                <div class="text-base font-semibold text-slate-700">Henüz sözleşme yok</div>
                                <div class="mt-1 text-sm text-slate-500">
                                    Firmanıza ait kayıtlı sözleşme bulunmuyor.
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="overflow-hidden rounded-[30px] border border-slate-200/70 bg-white shadow-lg shadow-slate-200/40">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h3 class="text-lg font-bold text-slate-900">Sözleşme Özeti</h3>
                        <p class="mt-1 text-sm text-slate-500">Güncel sözleşme görünümü</p>
                    </div>

                    <div class="space-y-4 px-6 py-6">
                        @if($activeContract)
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4">
                                <div class="text-sm font-semibold text-emerald-700">Geçerli Sözleşme</div>
                                <div class="mt-2 text-lg font-bold text-slate-900">{{ $activeContract->year }}</div>
                                <div class="mt-2 text-xs text-slate-600">
                                    {{ $activeContract->start_date?->format('d.m.Y') }} - {{ $activeContract->end_date?->format('d.m.Y') }}
                                </div>
                            </div>
                        @else
                            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4">
                                <div class="text-sm font-semibold text-rose-700">Aktif sözleşme bulunamadı</div>
                                <div class="mt-2 text-xs text-slate-600">Lütfen yönetici ile iletişime geçin.</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
