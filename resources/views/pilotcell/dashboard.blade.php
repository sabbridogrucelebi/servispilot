@extends('layouts.app')

@section('title', 'PilotCell - Canlı Servis İzleme')

@section('content')
<div class="h-[calc(100vh-64px)] flex flex-col md:flex-row">
    <!-- Sol Taraf: Aktif Seferler ve Okullar -->
    <div class="w-full md:w-80 bg-white border-r border-gray-100 flex flex-col h-full z-10 shadow-sm relative">
        
        <!-- Okullar Bölümü (Üst) -->
        <div class="p-4 border-b border-gray-100 bg-white">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                🎓 Kayıtlı Okullar
            </h2>
            <p class="text-xs text-slate-500 mt-1">Müşteri türü Okul olan kayıtlar</p>
        </div>
        
        <div class="flex-1 overflow-y-auto p-3 space-y-2 border-b border-gray-100 bg-slate-50/50">
            @forelse($schoolCustomers as $school)
                <div class="bg-white border border-slate-200 rounded-xl p-2.5 shadow-sm flex items-center justify-between gap-3 hover:border-indigo-200 hover:shadow-md transition-all group">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-10 h-10 rounded-lg bg-indigo-50 flex shrink-0 items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="font-bold text-sm text-slate-800 truncate group-hover:text-indigo-700 transition-colors">{{ $school->company_name }}</h4>
                            <p class="text-[11px] font-medium text-slate-500 truncate">{{ $school->authorized_person ?? 'Yetkili Belirtilmedi' }}</p>
                        </div>
                    </div>
                    <a href="{{ route('pilotcell.school', $school->id) }}" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:bg-indigo-100 hover:text-indigo-600 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </a>
                </div>
            @empty
                <div class="text-[12px] font-medium text-slate-400 text-center py-10 flex flex-col items-center">
                    <svg class="w-12 h-12 mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                    Kayıtlı okul bulunmuyor.
                </div>
            @endforelse
        </div>
    </div>
        
    <!-- Sağ Taraf: Harita -->
    <div class="flex-1 relative bg-slate-100 z-0 h-full">
        <div id="pilotcell-map" class="w-full h-full"></div>
    </div>
</div>
@endsection

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    .vehicle-marker {
        transition: all 0.5s ease-out;
    }
    .custom-popup .leaflet-popup-content-wrapper {
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
        padding: 0;
        overflow: hidden;
    }
    .custom-popup .leaflet-popup-content {
        margin: 0;
        width: 220px !important;
    }
    .custom-popup .leaflet-popup-tip {
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
    }
</style>
@endpush

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Init Map
        const map = L.map('pilotcell-map', {
            zoomControl: false,
            attributionControl: false
        }).setView([39.92077, 32.85411], 6); // Turkey default center

        L.control.zoom({ position: 'bottomright' }).addTo(map);

        // Add Tile Layer (OpenStreetMap)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
            subdomains: 'abcd'
        }).addTo(map);

        // Marker Storage
        const markers = {};
        const activeTrips = @json($activeTrips);
        let bounds = L.latLngBounds();

        // Custom Vehicle Icon
        const vehicleIcon = L.divIcon({
            className: 'vehicle-marker',
            html: `
                <div class="relative flex items-center justify-center w-10 h-10">
                    <div class="absolute w-full h-full bg-indigo-500 rounded-full opacity-20 animate-ping"></div>
                    <div class="relative w-8 h-8 bg-indigo-600 rounded-full border-2 border-white shadow-md flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    </div>
                </div>
            `,
            iconSize: [40, 40],
            iconAnchor: [20, 20],
            popupAnchor: [0, -20]
        });

        // Initialize Echo Listeners for Active Trips
        if (typeof window.Echo !== 'undefined') {
            activeTrips.forEach(trip => {
                // Her sefer için ayrı bir private channel dinleniyor
                window.Echo.private(`pilotcell.trip.${trip.id}`)
                    .listen('.location.updated', (e) => {
                        updateVehicleLocation(trip.id, trip, e);
                    });
                
                // İlk yüklemede en son konumu API'den çek (Opsiyonel - Daha sağlam bir UX için)
                fetchLatestLocation(trip);
            });
        } else {
            console.error("Laravel Echo bulunamadı. WebSockets çalışmayacak.");
        }

        // Fetch latest location manually to paint initial map
        function fetchLatestLocation(trip) {
            fetch(`/api/v1/pilotcell/location/latest/${trip.id}`, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Sanctum cookie based auth works without bearer too
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data && data.data && data.data.lat && data.data.lng) {
                    updateVehicleLocation(trip.id, trip, data.data);
                }
            })
            .catch(err => console.log('Konum çekilemedi:', err));
        }

        function updateVehicleLocation(tripId, trip, locationData) {
            const latLng = [parseFloat(locationData.lat), parseFloat(locationData.lng)];
            const speed = locationData.speed || 0;
            const timeStr = new Date(locationData.recorded_at).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });

            // Update Sidebar UI
            const speedEl = document.getElementById(`speed-${tripId}`);
            const timeEl = document.getElementById(`time-${tripId}`);
            if (speedEl) speedEl.innerHTML = `<svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg> ${speed} km/s`;
            if (timeEl) timeEl.innerText = timeStr;

            // Update Map Marker
            if (markers[tripId]) {
                markers[tripId].setLatLng(latLng);
                // Rotate icon based on heading if available (requires extra css logic, skipping for simplicity)
            } else {
                // Create new marker
                const popupContent = `
                    <div class="bg-indigo-600 px-4 py-3">
                        <h3 class="text-white font-bold text-sm truncate">${trip.route?.name || 'Bilinmeyen Rota'}</h3>
                        <p class="text-indigo-100 text-xs">${trip.vehicle?.plate || ''}</p>
                    </div>
                    <div class="bg-white p-3 space-y-2">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-500">Şoför:</span>
                            <span class="font-medium text-slate-800">${trip.driver?.name || 'Atanmadı'}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-500">Hız:</span>
                            <span class="font-bold text-emerald-600" id="popup-speed-${tripId}">${speed} km/s</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-500">Son Güncelleme:</span>
                            <span class="font-medium text-slate-800" id="popup-time-${tripId}">${timeStr}</span>
                        </div>
                    </div>
                `;

                markers[tripId] = L.marker(latLng, { icon: vehicleIcon })
                    .addTo(map)
                    .bindPopup(popupContent, { className: 'custom-popup' });
                
                bounds.extend(latLng);
                map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
            } else {
                // Update popup if open
                const popupSpeedEl = document.getElementById(`popup-speed-${tripId}`);
                const popupTimeEl = document.getElementById(`popup-time-${tripId}`);
                if (popupSpeedEl) popupSpeedEl.innerText = `${speed} km/s`;
                if (popupTimeEl) popupTimeEl.innerText = timeStr;
            }
        }

        // Global function to focus from sidebar
        window.focusTrip = function(tripId) {
            if (markers[tripId]) {
                const latLng = markers[tripId].getLatLng();
                map.flyTo(latLng, 16, { duration: 1.5 });
                markers[tripId].openPopup();
            }
        };
    });
</script>
@endpush
