<div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
    <div class="xl:col-span-8 space-y-8">
        {{-- Identity Card --}}
        <div class="group relative overflow-hidden rounded-[40px] bg-white shadow-2xl transition-all duration-500 hover:shadow-indigo-500/10">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 via-white to-blue-50/50 opacity-100"></div>
            
            <div class="relative z-10 border-b border-slate-100/50 px-8 py-6 bg-white/50 backdrop-blur-sm flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Genel Araç Kimliği</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Temel Sınıflandırma ve Model Detayları</p>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl shadow-xl shadow-indigo-200">🆔</div>
            </div>

            <div class="relative z-10 grid grid-cols-1 md:grid-cols-3 gap-6 p-8">
                <div class="rounded-3xl bg-indigo-600 p-6 text-white shadow-xl shadow-indigo-200 transition-transform group-hover:scale-[1.02]">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-200">Plaka</div>
                    <div class="mt-2 text-2xl font-black">{{ $vehicle->plate ?: '-' }}</div>
                </div>

                <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-md transition-all">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Marka</div>
                    <div class="mt-2 text-xl font-black text-slate-800">{{ $vehicle->brand ?: '-' }}</div>
                </div>

                <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-md transition-all">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Model</div>
                    <div class="mt-2 text-xl font-black text-slate-800">{{ $vehicle->model ?: '-' }}</div>
                </div>

                <div class="rounded-3xl border border-slate-100 bg-slate-50/50 p-6">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Araç Tipi</div>
                    <div class="mt-2 text-lg font-bold text-slate-700">{{ $vehicle->vehicle_type ?: '-' }}</div>
                </div>

                <div class="rounded-3xl border border-slate-100 bg-slate-50/50 p-6">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Model Yılı</div>
                    <div class="mt-2 text-lg font-bold text-slate-700">{{ $vehicle->model_year ?: '-' }}</div>
                </div>

                <div class="rounded-3xl bg-blue-50 p-6 border border-blue-100">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-400">Koltuk Kapasitesi</div>
                    <div class="mt-2 text-xl font-black text-blue-600">{{ $vehicle->seat_count ?: '-' }} Kişi</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- License Info --}}
            <div class="group relative overflow-hidden rounded-[40px] bg-white shadow-2xl transition-all duration-500 hover:shadow-purple-500/10">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-50/50 via-white to-violet-50/50 opacity-100"></div>
                
                <div class="relative z-10 border-b border-slate-100/50 px-8 py-6 bg-white/50 backdrop-blur-sm flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Ruhsat & Sahiplik</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Yasal Kayıt Detayları</p>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-purple-500 text-white flex items-center justify-center text-xl shadow-lg shadow-purple-100">📄</div>
                </div>

                <div class="relative z-10 space-y-4 p-8">
                    <div class="rounded-2xl bg-purple-50 p-5 border border-purple-100">
                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-purple-400">Seri Numarası</div>
                        <div class="mt-1 text-base font-black text-purple-700">{{ $vehicle->license_serial_no ?: '-' }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white p-5">
                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Ruhsat Sahibi</div>
                        <div class="mt-1 text-base font-bold text-slate-800">{{ $vehicle->license_owner ?: '-' }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white p-5">
                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Vergi / T.C. No</div>
                        <div class="mt-1 text-base font-bold text-slate-800">{{ $vehicle->owner_tax_or_tc_no ?: '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Technical Info --}}
            <div class="group relative overflow-hidden rounded-[40px] bg-white shadow-2xl transition-all duration-500 hover:shadow-cyan-500/10">
                <div class="absolute inset-0 bg-gradient-to-br from-cyan-50/50 via-white to-sky-50/50 opacity-100"></div>
                
                <div class="relative z-10 border-b border-slate-100/50 px-8 py-6 bg-white/50 backdrop-blur-sm flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Teknik Donanım</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Motor ve Mekanik Veriler</p>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-cyan-500 text-white flex items-center justify-center text-xl shadow-lg shadow-cyan-100">⚙️</div>
                </div>

                <div class="relative z-10 space-y-4 p-8">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-2xl bg-cyan-50 p-5 border border-cyan-100">
                            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-cyan-400">Vites</div>
                            <div class="mt-1 text-base font-black text-cyan-700">{{ $vehicle->gear_type ?: '-' }}</div>
                        </div>
                        <div class="rounded-2xl bg-sky-50 p-5 border border-sky-100">
                            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-sky-400">Yakıt</div>
                            <div class="mt-1 text-base font-black text-sky-700">{{ $vehicle->fuel_type ?: '-' }}</div>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white p-5">
                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Motor Numarası</div>
                        <div class="mt-1 text-xs font-bold text-slate-800 break-all">{{ $vehicle->engine_no ?: '-' }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white p-5">
                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Şasi Numarası</div>
                        <div class="mt-1 text-xs font-bold text-slate-800 break-all">{{ $vehicle->chassis_no ?: '-' }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white p-5">
                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Dış Renk</div>
                        <div class="mt-1 text-base font-bold text-slate-800">{{ $displayColor }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-[40px] overflow-hidden bg-white shadow-2xl border border-slate-100 group transition-all duration-500 hover:shadow-slate-200">
            <div class="border-b border-slate-100 px-8 py-6 bg-slate-50 flex items-center justify-between">
                <h3 class="text-lg font-black text-slate-800 uppercase tracking-widest">Özel Notlar</h3>
                <span class="text-xl">📝</span>
            </div>

            <div class="p-8">
                @if($vehicle->notes)
                    <div class="rounded-[30px] bg-slate-50 border border-slate-100 p-6 text-sm font-medium leading-loose text-slate-600 italic">
                        "{{ $vehicle->notes }}"
                    </div>
                @else
                    <div class="rounded-[30px] border-2 border-dashed border-slate-200 p-10 text-center">
                        <div class="text-4xl mb-4 opacity-30">🏜️</div>
                        <p class="text-sm font-bold text-slate-400">Bu araç için henüz bir not girilmemiş.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="xl:col-span-4 space-y-8">
        {{-- Dates Status Card --}}
        <div class="rounded-[40px] bg-white shadow-2xl overflow-hidden border border-slate-100">
            <div class="bg-slate-900 p-8 text-white">
                <h3 class="text-xl font-black tracking-tight">Kritik Takvim</h3>
                <p class="text-[10px] font-black text-white/50 uppercase tracking-[0.3em] mt-2">Yasal Geçerlilik Süreleri</p>
            </div>

            <div class="p-8 space-y-4">
                @php
                    $dates = [
                        ['label' => 'TÜVTÜRK Muayene', 'info' => $inspectionInfo, 'icon' => '🔍'],
                        ['label' => 'Egzoz Emisyon', 'info' => $exhaustInfo, 'icon' => '💨'],
                        ['label' => 'Trafik Sigortası', 'info' => $insuranceInfo, 'icon' => '📄'],
                        ['label' => 'Kasko Poliçesi', 'info' => $kaskoInfo, 'icon' => '🛡️'],
                        ['label' => 'İMM Poliçesi', 'info' => $immInfo, 'icon' => '💼'],
                    ];
                @endphp

                @foreach($dates as $date)
                    <div class="group flex items-center justify-between p-5 rounded-3xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:shadow-xl transition-all duration-500">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-xl bg-white flex items-center justify-center shadow-sm text-lg group-hover:scale-110 transition-transform">{{ $date['icon'] }}</div>
                            <div>
                                <div class="text-sm font-black text-slate-800">{{ $date['label'] }}</div>
                                <div class="text-[10px] font-bold text-slate-400 mt-0.5">{{ $date['info']['text'] }}</div>
                            </div>
                        </div>
                        <span class="rounded-full px-4 py-1.5 text-[10px] font-black uppercase tracking-widest {{ $date['info']['class'] }} shadow-sm">
                            {{ $date['info']['status'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Maintenance Health --}}
        <div class="rounded-[40px] bg-white shadow-2xl overflow-hidden border border-slate-100">
             <div class="bg-indigo-600 p-8 text-white relative overflow-hidden">
                <div class="absolute -right-4 -top-4 text-8xl opacity-10 rotate-12">🛠️</div>
                <h3 class="text-xl font-black tracking-tight relative z-10">Bakım Sağlığı</h3>
                <p class="text-[10px] font-black text-white/70 uppercase tracking-[0.3em] mt-2 relative z-10">KM Bazlı Tahminleme</p>
            </div>

            <div class="p-8 space-y-8">
                @php $mStatus = $vehicle->maintenance_status; @endphp

                @if($mStatus['has_setting'])
                    @if($mStatus['has_oil_setting'])
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Yağ Değişimi</span>
                                <span class="text-sm font-black {{ $mStatus['oil_remaining'] < 1000 ? 'text-rose-600' : 'text-indigo-600' }}">
                                    {{ $mStatus['oil_remaining'] !== null ? number_format($mStatus['oil_remaining'], 0, ',', '.') . ' KM KALDI' : 'KAYIT BEKLENİYOR' }}
                                </span>
                            </div>
                            <div class="h-4 w-full rounded-full bg-slate-100 shadow-inner overflow-hidden p-1">
                                <div class="h-full rounded-full transition-all duration-1000 {{ $mStatus['oil_remaining'] < 1000 ? 'bg-gradient-to-r from-rose-500 to-pink-500' : 'bg-gradient-to-r from-indigo-500 to-blue-500' }}" 
                                     style="width: {{ $mStatus['oil_percent'] }}%"></div>
                            </div>
                        </div>
                    @endif

                    @if($mStatus['has_lube_setting'])
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Alt Yağlama</span>
                                <span class="text-sm font-black {{ $mStatus['lube_remaining'] < 500 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $mStatus['lube_remaining'] !== null ? number_format($mStatus['lube_remaining'], 0, ',', '.') . ' KM KALDI' : 'KAYIT BEKLENİYOR' }}
                                </span>
                            </div>
                            <div class="h-4 w-full rounded-full bg-slate-100 shadow-inner overflow-hidden p-1">
                                <div class="h-full rounded-full transition-all duration-1000 {{ $mStatus['lube_remaining'] < 500 ? 'bg-gradient-to-r from-orange-500 to-rose-500' : 'bg-gradient-to-r from-emerald-500 to-teal-500' }}" 
                                     style="width: {{ $mStatus['lube_percent'] }}%"></div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="py-10 text-center">
                        <div class="text-5xl mb-6">⚙️</div>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Takip Aktif Değil</h4>
                        <p class="mt-2 text-xs font-medium text-slate-400">Bu araç için bakım periyotları henüz tanımlanmamış.</p>
                        <a href="{{ route('vehicles.show', $vehicle) }}?tab=maintenances" class="mt-8 inline-flex rounded-2xl bg-slate-900 px-8 py-4 text-[10px] font-black text-white hover:bg-indigo-600 transition-all uppercase tracking-widest">
                            YAPILANDIR →
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>