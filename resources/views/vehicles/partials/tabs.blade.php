@php
    $currentTab = request('tab', 'general');
    $showVehicleImagePanel = $currentTab === 'general';
@endphp

<div class="grid grid-cols-1 {{ $showVehicleImagePanel ? 'xl:grid-cols-12' : '' }} gap-6">
    {{-- Sol Alan --}}
    <div class="{{ $showVehicleImagePanel ? 'xl:col-span-9' : 'w-full' }}">
        <div class="rounded-[30px] border border-slate-200/70 bg-white/95 shadow-[0_20px_60px_rgba(15,23,42,0.08)] overflow-hidden">
            {{-- Üst Başlık --}}
            <div class="relative border-b border-slate-100 px-6 md:px-7 py-6 bg-gradient-to-r from-slate-50 via-white to-indigo-50/40">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-[20px] bg-gradient-to-br from-indigo-600 to-blue-500 text-white shadow-lg shadow-indigo-200/70">
                            <span class="text-2xl">🚐</span>
                        </div>

                        <div>
                            <h3 class="text-[24px] font-bold tracking-tight text-slate-900">
                                Araç Yönetim Sekmeleri
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Araçla ilgili tüm alanları modüler, düzenli ve profesyonel şekilde yönetin
                            </p>
                        </div>
                    </div>

                    <div class="inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        Aktif araç yönetim merkezi
                    </div>
                </div>
            </div>

            {{-- Sekmeler --}}
            <div class="px-5 md:px-6 pt-5 pb-4 bg-white">
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('vehicles.show', $vehicle) }}?tab=general"
                       class="group inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold transition-all duration-300 {{ $currentTab === 'general'
                            ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-lg shadow-indigo-200 scale-[1.01]'
                            : 'border border-slate-200 bg-slate-50 text-slate-700 hover:border-indigo-200 hover:bg-white hover:text-indigo-700 hover:shadow-md' }}">
                        <span class="text-base">📋</span>
                        <span>Genel Bilgiler</span>
                    </a>

                    <a href="{{ route('vehicles.show', $vehicle) }}?tab=documents"
                       class="group inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold transition-all duration-300 {{ $currentTab === 'documents'
                            ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-lg shadow-indigo-200 scale-[1.01]'
                            : 'border border-slate-200 bg-slate-50 text-slate-700 hover:border-indigo-200 hover:bg-white hover:text-indigo-700 hover:shadow-md' }}">
                        <span class="text-base">📄</span>
                        <span>Araç Belgeleri</span>
                    </a>

                    <a href="{{ route('vehicles.show', $vehicle) }}?tab=fuels"
                       class="group inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold transition-all duration-300 {{ $currentTab === 'fuels'
                            ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-lg shadow-indigo-200 scale-[1.01]'
                            : 'border border-slate-200 bg-slate-50 text-slate-700 hover:border-indigo-200 hover:bg-white hover:text-indigo-700 hover:shadow-md' }}">
                        <span class="text-base">⛽</span>
                        <span>Araç Yakıtları</span>
                    </a>

                    <a href="{{ route('vehicles.show', $vehicle) }}?tab=maintenances"
                       class="group inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold transition-all duration-300 {{ $currentTab === 'maintenances'
                            ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-lg shadow-indigo-200 scale-[1.01]'
                            : 'border border-slate-200 bg-slate-50 text-slate-700 hover:border-indigo-200 hover:bg-white hover:text-indigo-700 hover:shadow-md' }}">
                        <span class="text-base">🛠️</span>
                        <span>Araç Bakımları</span>
                    </a>

                    <a href="{{ route('vehicles.show', $vehicle) }}?tab=penalties"
                       class="group inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold transition-all duration-300 {{ $currentTab === 'penalties'
                            ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-lg shadow-indigo-200 scale-[1.01]'
                            : 'border border-slate-200 bg-slate-50 text-slate-700 hover:border-indigo-200 hover:bg-white hover:text-indigo-700 hover:shadow-md' }}">
                        <span class="text-base">🚨</span>
                        <span>Trafik Cezaları</span>
                    </a>

                    <a href="{{ route('vehicles.show', $vehicle) }}?tab=reports"
                       class="group inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold transition-all duration-300 {{ $currentTab === 'reports'
                            ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-lg shadow-indigo-200 scale-[1.01]'
                            : 'border border-slate-200 bg-slate-50 text-slate-700 hover:border-indigo-200 hover:bg-white hover:text-indigo-700 hover:shadow-md' }}">
                        <span class="text-base">📈</span>
                        <span>Çalışma Raporu</span>
                    </a>

                    <a href="{{ route('vehicles.show', $vehicle) }}?tab=images"
                       class="group inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold transition-all duration-300 {{ $currentTab === 'images'
                            ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-lg shadow-indigo-200 scale-[1.01]'
                            : 'border border-slate-200 bg-slate-50 text-slate-700 hover:border-indigo-200 hover:bg-white hover:text-indigo-700 hover:shadow-md' }}">
                        <span class="text-base">🖼️</span>
                        <span>Araç Resimleri</span>
                    </a>
                </div>
            </div>

            {{-- İçerik Alanı --}}
            <div class="px-5 md:px-6 pb-6">
                <div class="rounded-[26px] border border-slate-200 bg-gradient-to-b from-slate-50 to-white p-4 md:p-6 min-h-[340px] shadow-inner">
                    @if($currentTab === 'general')
                        @include('vehicles.partials.tabs.general')
                    @elseif($currentTab === 'documents')
                        @include('vehicles.partials.tabs.documents')
                    @elseif($currentTab === 'fuels')
                        @include('vehicles.partials.tabs.fuels')
                    @elseif($currentTab === 'maintenances')
                        @include('vehicles.partials.tabs.maintenances')
                    @elseif($currentTab === 'penalties')
                        @include('vehicles.partials.tabs.penalties')
                    @elseif($currentTab === 'reports')
                        @include('vehicles.partials.tabs.reports')
                    @elseif($currentTab === 'images')
                        @include('vehicles.partials.tabs.images')
                    @else
                        @include('vehicles.partials.tabs.general')
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Sağ Alan: Sadece Genel Bilgiler sekmesinde göster --}}
    @if($showVehicleImagePanel)
        <div class="xl:col-span-3">
            <div class="rounded-[30px] border border-slate-200/70 bg-white/95 shadow-[0_20px_60px_rgba(15,23,42,0.08)] overflow-hidden sticky top-6">
                <div class="border-b border-slate-100 px-6 py-5 bg-gradient-to-r from-slate-50 via-white to-sky-50/40">
                    <div class="flex items-start gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-[18px] bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-lg shadow-sky-200/70">
                            <span class="text-xl">🖼️</span>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Araç Vitrin Görseli</h3>
                            <p class="mt-1 text-sm text-slate-500">Seçilen araç resmi burada gösterilir</p>
                        </div>
                    </div>
                </div>

                <div class="p-5">
                    <div class="relative overflow-hidden rounded-[26px] border border-slate-200 bg-slate-100 min-h-[320px] shadow-inner">
                        @if($vehicleImages->count())
                            <div id="vehicle-hero-slider" class="relative h-[320px] w-full">
                                @foreach($vehicleImages as $image)
                                    <div class="vehicle-slide {{ $featuredImage && $featuredImage->id === $image->id ? '' : 'hidden' }} absolute inset-0">
                                        <img src="{{ asset('storage/' . $image->file_path) }}"
                                             alt="{{ $image->title ?: 'Araç resmi' }}"
                                             class="h-full w-full object-cover">
                                    </div>
                                @endforeach
                            </div>

                            @if($vehicleImages->count() > 1)
                                <button type="button"
                                        id="vehicle-prev-slide"
                                        class="absolute left-3 top-1/2 -translate-y-1/2 flex h-11 w-11 items-center justify-center rounded-full bg-white/90 text-lg font-bold text-slate-700 shadow-lg transition hover:bg-white hover:scale-105">
                                    ‹
                                </button>

                                <button type="button"
                                        id="vehicle-next-slide"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 flex h-11 w-11 items-center justify-center rounded-full bg-white/90 text-lg font-bold text-slate-700 shadow-lg transition hover:bg-white hover:scale-105">
                                    ›
                                </button>
                            @endif

                            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-slate-900/30 to-transparent"></div>
                        @else
                            <div class="flex h-[320px] items-center justify-center px-6 text-center">
                                <div>
                                    <div class="mb-3 text-5xl">🚘</div>
                                    <div class="text-base font-semibold text-slate-700">Henüz araç resmi yok</div>
                                    <div class="mt-1 text-sm text-slate-500">Resimler sekmesinden yükleme yapabilirsin</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($featuredImage)
                        <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Vitrin resmi
                            </div>
                            <div class="mt-1 text-sm font-semibold text-slate-800">
                                {{ $featuredImage->title ?: 'Araç görseli' }}
                            </div>
                        </div>
                    @endif

                    @if($vehicleImages->count() > 1)
                        <div class="mt-4 flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm">
                            <span class="text-slate-500">Toplam görsel</span>
                            <span class="font-bold text-slate-800">{{ $vehicleImages->count() }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>