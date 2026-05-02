@php
    $currentTab = request('tab', 'general');
    $showVehicleImagePanel = $currentTab === 'general';
    
    $tabs = [
        ['id' => 'general', 'label' => 'Analiz', 'icon' => '📊', 'color' => 'indigo', 'gradient' => 'from-indigo-600 to-blue-600'],
        ['id' => 'documents', 'label' => 'Belgeler', 'icon' => '📁', 'color' => 'amber', 'gradient' => 'from-amber-500 to-orange-600'],
        ['id' => 'fuels', 'label' => 'Yakıt', 'icon' => '⛽', 'color' => 'rose', 'gradient' => 'from-rose-500 to-red-600'],
        ['id' => 'maintenances', 'label' => 'Bakım', 'icon' => '🛠️', 'color' => 'emerald', 'gradient' => 'from-emerald-500 to-teal-600'],
        ['id' => 'penalties', 'label' => 'Cezalar', 'icon' => '🚨', 'color' => 'violet', 'gradient' => 'from-violet-500 to-purple-600'],
        ['id' => 'reports', 'label' => 'Raporlar', 'icon' => '📈', 'color' => 'sky', 'gradient' => 'from-sky-500 to-cyan-600'],
        ['id' => 'images', 'label' => 'Galeri', 'icon' => '🖼️', 'color' => 'fuchsia', 'gradient' => 'from-fuchsia-500 to-pink-600'],
    ];
@endphp

<div class="grid grid-cols-1 {{ $showVehicleImagePanel ? 'xl:grid-cols-12' : '' }} gap-8">
    {{-- Left Content Area --}}
    <div class="{{ $showVehicleImagePanel ? 'xl:col-span-9' : 'w-full' }} space-y-6">
        
        {{-- Navigation Tabs Card --}}
        <div class="glass-card rounded-[40px] p-2 shadow-2xl overflow-hidden">
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
                @foreach($tabs as $tab)
                    @php $isActive = $currentTab === $tab['id']; @endphp
                    <a href="{{ route('vehicles.show', $vehicle) }}?tab={{ $tab['id'] }}"
                       class="group relative flex-1 min-w-[100px] inline-flex flex-col items-center gap-2 rounded-[30px] px-3 py-4 transition-all duration-500 {{ $isActive
                            ? 'bg-gradient-to-br ' . $tab['gradient'] . ' text-white shadow-2xl scale-[1.05] z-10'
                            : 'bg-white hover:bg-' . $tab['color'] . '-50 text-slate-500 hover:text-' . $tab['color'] . '-600' }}">
                        
                        @if(!$isActive)
                            <div class="absolute inset-0 rounded-[30px] border-2 border-transparent group-hover:border-{{ $tab['color'] }}-200 transition-all"></div>
                        @endif

                        <span class="text-3xl transition-transform group-hover:scale-125 duration-500">{{ $tab['icon'] }}</span>
                        <span class="uppercase tracking-[0.2em] text-[10px] font-black">{{ $tab['label'] }}</span>
                        
                        @if($isActive)
                            <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-8 h-1 bg-white rounded-full"></div>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Active Tab Content --}}
        <div class="glass-card rounded-[50px] p-10 min-h-[600px] shadow-2xl relative">
            {{-- Decorative Background --}}
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-slate-50/50 blur-3xl pointer-events-none"></div>
            <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-slate-50/50 blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10">
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

    {{-- Right Sidebar (Only on General Tab) --}}
    @if($showVehicleImagePanel)
        <div class="xl:col-span-3">
            <div class="space-y-6 sticky top-6">
                
                {{-- Hero Showcase --}}
                <div class="glass-card rounded-[40px] overflow-hidden p-3 group">
                    <div class="relative aspect-[3/4] rounded-[32px] overflow-hidden bg-slate-100 shadow-inner">
                        @if($vehicleImages->count())
                            <div id="vehicle-hero-slider" class="h-full w-full">
                                @foreach($vehicleImages as $image)
                                    <div class="vehicle-slide {{ $featuredImage && $featuredImage->id === $image->id ? '' : 'hidden' }} h-full w-full">
                                        <img src="{{ asset('storage/' . $image->file_path) }}"
                                             alt="{{ $image->title }}"
                                             class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-1000">
                                    </div>
                                @endforeach
                            </div>
                            
                            @if($vehicleImages->count() > 1)
                                <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-2 bg-black/20 backdrop-blur-md px-4 py-2 rounded-full">
                                    <button onclick="prevSlide()" class="text-white hover:scale-125 transition-transform">◀</button>
                                    <span class="text-[10px] font-black text-white px-2">GALERİ</span>
                                    <button onclick="nextSlide()" class="text-white hover:scale-125 transition-transform">▶</button>
                                </div>
                            @endif
                        @else
                            <div class="flex h-full items-center justify-center p-8 text-center">
                                <div>
                                    <div class="text-6xl mb-4">📸</div>
                                    <div class="text-sm font-black text-slate-800 uppercase tracking-widest">Görsel Bekleniyor</div>
                                    <p class="text-[10px] font-bold text-slate-400 mt-2">Aracın profesyonel fotoğraflarını Galeri sekmesinden yükleyebilirsiniz.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Quick Stats Panel --}}
                <div class="glass-card rounded-[40px] p-6 space-y-4">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Operasyonel Durum</div>
                    
                    <div class="flex items-center justify-between p-4 rounded-3xl bg-slate-50">
                        <span class="text-xs font-bold text-slate-500 uppercase">Aktiflik</span>
                        <span class="text-xs font-black {{ $vehicle->is_active ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $vehicle->is_active ? 'ÇEVRİMİÇİ' : 'ÇEVRİMDIŞI' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-3xl bg-slate-50">
                        <span class="text-xs font-bold text-slate-500 uppercase">Verimlilik</span>
                        <span class="text-xs font-black text-indigo-600">%88</span>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-3xl bg-slate-50">
                        <span class="text-xs font-bold text-slate-500 uppercase">Güvenlik</span>
                        <span class="text-xs font-black text-emerald-600">TAM</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
