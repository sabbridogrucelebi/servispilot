@extends('layouts.app')

@section('title', 'Araç Takip')
@section('subtitle', 'Entegrasyon Ayarları ve Canlı Takip')

@section('content')
<div class="relative w-full space-y-6">
    @if(session('success'))
        <div class="rounded-3xl bg-emerald-500/10 border border-emerald-500/20 p-5 text-emerald-400 font-bold flex items-center gap-4 animate-in fade-in slide-in-from-top-4">
            <div class="h-10 w-10 rounded-xl bg-emerald-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            {{ session('success') }}
        </div>
    @endif

    @if(!$setting)
        <!-- Welcome Screen -->
        <div class="relative group w-full">
            <div class="absolute -inset-1 rounded-[40px] bg-gradient-to-tr from-indigo-500/20 to-purple-600/20 blur opacity-70 group-hover:opacity-100 transition duration-500"></div>
            <div class="relative rounded-[40px] border border-white bg-white/40 shadow-xl backdrop-blur-xl p-12 text-center">
                <div class="max-w-3xl mx-auto">
                    <div class="inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-indigo-500 text-white text-4xl shadow-2xl shadow-indigo-500/40 mb-8 animate-bounce">
                        🛰️
                    </div>
                    <h2 class="text-4xl font-black text-slate-900 mb-6 tracking-tight">Araç Takip Entegrasyonu</h2>
                    <p class="text-lg text-slate-500 font-medium leading-relaxed mb-12">
                        Sistemimiz Arvento, Trio Mobil ve Mobiliz servisleri ile tam entegre çalışmaktadır.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
                        @foreach(['arvento' => 'A', 'trio_mobil' => 'T', 'mobiliz' => 'M'] as $provider => $initial)
                            <button type="button" onclick="selectProvider('{{ $provider }}')" class="group/card relative p-8 rounded-[35px] border-2 border-white bg-white/60 hover:bg-white hover:border-indigo-500 transition-all duration-500 hover:scale-105 shadow-sm hover:shadow-2xl">
                                <div class="h-16 w-16 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mb-6 group-hover/card:bg-indigo-50 transition-colors text-2xl font-black text-slate-400 group-hover/card:text-indigo-500">
                                    {{ $initial }}
                                </div>
                                <h3 class="text-xl font-black text-slate-900 mb-2 capitalize">{{ str_replace('_', ' ', $provider) }}</h3>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($setting)
        <!-- Dashboard Content -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 w-full">
            <div class="bg-white/70 backdrop-blur-xl p-6 rounded-[30px] border border-white shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-indigo-100 flex items-center justify-center text-xl">🛰️</div>
                    <div class="overflow-hidden">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sistem</p>
                        <p class="text-lg font-black text-slate-900 truncate capitalize">{{ str_replace('_', ' ', $setting->provider) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/70 backdrop-blur-xl p-6 rounded-[30px] border border-white shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-emerald-100 flex items-center justify-center text-xl text-emerald-600">🟢</div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Aktif Araç</p>
                        <p class="text-2xl font-black text-slate-900">{{ is_array($vehicles) ? count($vehicles) : 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white/70 backdrop-blur-xl p-6 rounded-[30px] border border-white shadow-sm flex items-center justify-between gap-4">
                <div class="flex items-center gap-4 overflow-hidden">
                    <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center text-xl">👤</div>
                    <div class="overflow-hidden">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Kullanıcı</p>
                        <p class="text-lg font-black text-slate-900 truncate">{{ $setting->username }}</p>
                    </div>
                </div>
                <button type="button" onclick="selectProvider('{{ $setting->provider }}')" class="shrink-0 px-6 py-3 rounded-2xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition shadow-lg shadow-slate-900/10">Ayarları Değiştir</button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 w-full">
            <!-- List -->
            <div class="lg:col-span-4 space-y-4 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
                @if(is_array($vehicles) && count($vehicles) > 0)
                    @foreach($vehicles as $vehicle)
                        <div class="group bg-white/70 backdrop-blur-md p-5 rounded-[28px] border border-white shadow-sm hover:shadow-xl transition-all duration-300 cursor-pointer">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-4 overflow-hidden">
                                    <div class="h-12 w-12 shrink-0 rounded-2xl bg-slate-100 flex items-center justify-center text-xl group-hover:bg-indigo-500 group-hover:text-white transition-colors">🚚</div>
                                    <div class="overflow-hidden">
                                        <h4 class="text-lg font-black text-slate-900 truncate">{{ $vehicle['LicensePlate'] ?? 'Plakasız' }}</h4>
                                        <p class="text-[10px] font-bold text-slate-400 truncate">{{ $vehicle['Address'] ?? 'Konum alınıyor...' }}</p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-lg font-black text-indigo-600">{{ $vehicle['Speed'] ?? 0 }} <span class="text-[10px]">km/h</span></div>
                                    <div class="flex items-center gap-1 justify-end mt-1">
                                        <div class="h-1.5 w-1.5 rounded-full {{ ($vehicle['Speed'] ?? 0) > 0 ? 'bg-emerald-500' : 'bg-amber-500' }}"></div>
                                        <span class="text-[9px] font-black text-slate-400 uppercase">{{ ($vehicle['Speed'] ?? 0) > 0 ? 'Hareketli' : 'Duran' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-10 text-center bg-white/40 rounded-[30px] border border-white border-dashed">
                        <p class="text-slate-400 font-bold">Araç verisi bekleniyor...</p>
                    </div>
                @endif
            </div>

            <!-- Map -->
            <div class="lg:col-span-8 min-h-[600px]">
                <div class="relative h-full rounded-[40px] border border-white bg-white shadow-2xl overflow-hidden">
                    <div id="map" class="absolute inset-0 w-full h-full z-0"></div>
                    
                    @if(!(is_array($vehicles) && count($vehicles) > 0))
                        <div class="absolute inset-0 z-10 flex items-center justify-center p-8">
                            <div class="bg-white/80 backdrop-blur-md p-10 rounded-[40px] border border-white shadow-2xl max-w-sm w-full text-center">
                                <div class="animate-spin-slow inline-flex h-16 w-16 items-center justify-center rounded-full bg-indigo-500 text-white text-2xl mb-6">🧭</div>
                                <h3 class="text-xl font-black text-slate-900 mb-2">Harita Hazırlanıyor</h3>
                                <p class="text-xs text-slate-500 font-medium leading-relaxed">Konum verileri geldiğinde burada görünecek.</p>
                            </div>
                        </div>
                    @endif

                    <div class="absolute top-6 right-6 flex items-center gap-3 bg-white/90 backdrop-blur-md px-4 py-2 rounded-xl shadow-lg border border-white z-10">
                        <div class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                        </div>
                        <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Canlı</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal (Fixed at the end to prevent layout push) -->
<div id="setupForm" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="closeSetup()"></div>
    <div class="relative w-full max-w-xl rounded-[40px] bg-white shadow-[0_35px_60px_-15px_rgba(0,0,0,0.3)] overflow-hidden border border-white animate-in zoom-in-95 duration-300">
        <form action="{{ route('vehicle-tracking.store') }}" method="POST">
            @csrf
            <input type="hidden" name="provider" id="providerInput" value="{{ $setting->provider ?? '' }}">
            
            <div class="p-10">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-indigo-500 flex items-center justify-center text-white text-xl shadow-lg">⚙️</div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-900" id="selectedProviderTitle">Entegrasyon Ayarları</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Lütfen API bilgilerinizi giriniz</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeSetup()" class="h-10 w-10 rounded-xl bg-slate-50 text-slate-400 hover:bg-slate-100 transition-colors flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-4">Kullanıcı Adı</label>
                        <input type="text" name="username" value="{{ $setting->username ?? '' }}" required class="w-full px-6 py-4 rounded-2xl bg-slate-50 border border-slate-100 text-slate-900 font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-4">Şifre (Panel Şifresi)</label>
                        <input type="password" name="password" required class="w-full px-6 py-4 rounded-2xl bg-slate-50 border border-slate-100 text-slate-900 font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-4">PIN1 (App ID)</label>
                            <input type="text" name="app_id" value="{{ $setting->app_id ?? '' }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border border-slate-100 text-slate-900 font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-4">PIN2 (App Key)</label>
                            <input type="text" name="app_key" value="{{ $setting->app_key ?? '' }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border border-slate-100 text-slate-900 font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none">
                        </div>
                    </div>
                </div>

                <div class="mt-10">
                    <button type="submit" class="w-full py-5 rounded-[24px] bg-slate-900 text-white font-black text-sm hover:scale-[1.02] transition shadow-2xl">
                        Ayarları Kaydet ve Bağlan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initMap" async defer></script>

<script>
    let map;
    let markers = [];
    const vehicles = @json($vehicles);

    function initMap() {
        const defaultCenter = { lat: 39.9334, lng: 32.8597 };
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 6,
            center: defaultCenter,
            styles: [
                { "featureType": "all", "elementType": "labels.text.fill", "stylers": [{ "color": "#7c93a3" }, { "lightness": "-10" }] },
                { "featureType": "water", "elementType": "geometry", "stylers": [{ "color": "#c9c9c9" }] }
            ],
            mapTypeControl: false, streetViewControl: false, fullscreenControl: true
        });

        if (Array.isArray(vehicles) && vehicles.length > 0) {
            const bounds = new google.maps.LatLngBounds();
            vehicles.forEach(vehicle => {
                if (vehicle.Latitude && vehicle.Longitude) {
                    const position = { lat: parseFloat(vehicle.Latitude), lng: parseFloat(vehicle.Longitude) };
                    const marker = new google.maps.Marker({
                        position: position, map: map, title: vehicle.LicensePlate,
                        icon: {
                            path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                            scale: 5, fillColor: (vehicle.Speed > 0) ? "#10b981" : "#f59e0b",
                            fillOpacity: 1, strokeWeight: 2, strokeColor: "#ffffff",
                            rotation: parseFloat(vehicle.Course || 0)
                        }
                    });
                    const infoWindow = new google.maps.InfoWindow({
                        content: `<div style="padding: 10px;"><b>${vehicle.LicensePlate}</b><br><small>${vehicle.Speed} km/h</small></div>`
                    });
                    marker.addListener("click", () => infoWindow.open(map, marker));
                    markers.push(marker);
                    bounds.extend(position);
                }
            });
            if (markers.length > 0) map.fitBounds(bounds);
        }
    }

    function selectProvider(provider) {
        document.getElementById('providerInput').value = provider;
        document.getElementById('setupForm').classList.remove('hidden');
    }

    function closeSetup() {
        document.getElementById('setupForm').classList.add('hidden');
    }

    window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeSetup(); });
</script>

<style>
    @keyframes spin-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .animate-spin-slow { animation: spin-slow 8s linear infinite; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>
@endsection
