@php
    $serviceRoutes = $serviceRoutes ?? collect();
    $activeVehicles = $activeVehicles ?? collect();

    $oldServiceType = old('service_type', 'both');
    $oldFeeType = old('fee_type', 'free');
    $oldSaturdayPricing = old('saturday_pricing', 'yes');
    $oldSundayPricing = old('sunday_pricing', 'yes');
@endphp

<div
    x-data="{
        openRouteModal: {{ $errors->has('route_name') || $errors->has('service_type') || $errors->has('vehicle_type') || $errors->has('morning_vehicle_id') || $errors->has('evening_vehicle_id') || $errors->has('fee_type') || $errors->has('morning_fee') || $errors->has('evening_fee') || $errors->has('fallback_morning_fee') || $errors->has('fallback_evening_fee') || $errors->has('saturday_pricing') || $errors->has('sunday_pricing') ? 'true' : 'false' }},
        feeType: '{{ $oldFeeType }}',
        serviceType: '{{ $oldServiceType }}',
        saturdayPricing: '{{ $oldSaturdayPricing }}',
        sundayPricing: '{{ $oldSundayPricing }}',
        firstVehicleLabel() {
            return this.serviceType === 'shift' ? 'Vardiya Toplama Aracı' : 'Sabah Seferini Yapacak Araç';
        },
        secondVehicleLabel() {
            return this.serviceType === 'shift' ? 'Vardiya Dağıtım Aracı' : 'Akşam Seferini Yapacak Araç';
        }
    }"
    class="space-y-6"
>

    <div class="overflow-hidden rounded-[30px] border border-slate-200/70 bg-white shadow-lg shadow-slate-200/40">
        <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Servis Güzergahları</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Bu müşteriye özel tanımlanan güzergahları, araçları ve ücret yapılarını yönetin
                </p>
            </div>

            <button type="button"
                    @click="openRouteModal = true"
                    class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:scale-[1.02]">
                <span class="text-base">+</span>
                <span>Güzergah Tanımla</span>
            </button>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-blue-600 via-indigo-600 to-violet-600 p-5 text-white shadow-xl shadow-blue-500/20">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
                    <div class="relative">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-white/75">Toplam Güzergah</div>
                        <div class="mt-4 text-3xl font-black">{{ $serviceRoutes->count() }}</div>
                        <div class="mt-2 text-xs text-white/75">Bu müşteri için tanımlı rota sayısı</div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-500 p-5 text-white shadow-xl shadow-emerald-500/20">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
                    <div class="relative">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-white/75">Ücretli Güzergah</div>
                        <div class="mt-4 text-3xl font-black">{{ $serviceRoutes->where('fee_type', 'paid')->count() }}</div>
                        <div class="mt-2 text-xs text-white/75">Sefer ücreti tanımlı rota sayısı</div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-violet-500 via-fuchsia-500 to-pink-500 p-5 text-white shadow-xl shadow-violet-500/20">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
                    <div class="relative">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-white/75">Ücretsiz Güzergah</div>
                        <div class="mt-4 text-3xl font-black">{{ $serviceRoutes->where('fee_type', 'free')->count() }}</div>
                        <div class="mt-2 text-xs text-white/75">Ücretsiz rota tanımları</div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-amber-400 via-orange-500 to-rose-500 p-5 text-white shadow-xl shadow-orange-500/20">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
                    <div class="relative">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-white/75">Aktif Rota</div>
                        <div class="mt-4 text-3xl font-black">{{ $serviceRoutes->where('is_active', true)->count() }}</div>
                        <div class="mt-2 text-xs text-white/75">Puantajda kullanılabilir güzergah</div>
                    </div>
                </div>
            </div>

            @if($serviceRoutes->count())
                <div class="mt-6 space-y-4">
                    @foreach($serviceRoutes as $route)
                        <div x-data="{
                                openEditModal: false,
                                editServiceType: '{{ $route->service_type ?? 'both' }}',
                                editFeeType: '{{ $route->fee_type ?? 'free' }}',
                                editSaturdayPricing: '{{ $route->saturday_pricing ? 'yes' : 'no' }}',
                                editSundayPricing: '{{ $route->sunday_pricing ? 'yes' : 'no' }}',
                                firstEditVehicleLabel() {
                                    return this.editServiceType === 'shift' ? 'Vardiya Toplama Aracı' : 'Sabah Seferini Yapacak Araç';
                                },
                                secondEditVehicleLabel() {
                                    return this.editServiceType === 'shift' ? 'Vardiya Dağıtım Aracı' : 'Akşam Seferini Yapacak Araç';
                                }
                            }"
                            class="rounded-[26px] border border-slate-200 bg-slate-50/70 p-5 shadow-sm">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-500 text-2xl text-white shadow">
                                        🛣️
                                    </div>

                                    <div>
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

                                        <div class="mt-3 grid grid-cols-1 gap-3 text-sm text-slate-600 md:grid-cols-2 xl:grid-cols-4">
                                            <div>
                                                <span class="font-semibold text-slate-800">Araç Cinsi:</span>
                                                {{ $route->vehicle_type ?: '-' }}
                                            </div>
                                            <div>
                                                <span class="font-semibold text-slate-800">Servis Türü:</span>
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
                                            <div>
                                                <span class="font-semibold text-slate-800">Ücret Türü:</span>
                                                {{ $route->fee_type === 'paid' ? 'Ücretli' : 'Ücretsiz' }}
                                            </div>
                                            <div>
                                                <span class="font-semibold text-slate-800">Hafta Sonu:</span>
                                                Cmt {{ $route->saturday_pricing ? 'Evet' : 'Hayır' }} / Paz {{ $route->sunday_pricing ? 'Evet' : 'Hayır' }}
                                            </div>
                                        </div>

                                        <div class="mt-3 grid grid-cols-1 gap-3 text-sm text-slate-600 md:grid-cols-2 xl:grid-cols-4">
                                            <div>
                                                <span class="font-semibold text-slate-800">
                                                    {{ $route->service_type === 'shift' ? 'Toplama Aracı:' : 'Sabah Aracı:' }}
                                                </span>
                                                {{ $route->morningVehicle?->plate ?? '-' }}
                                            </div>
                                            <div>
                                                <span class="font-semibold text-slate-800">
                                                    {{ $route->service_type === 'shift' ? 'Dağıtım Aracı:' : 'Akşam Aracı:' }}
                                                </span>
                                                {{ $route->eveningVehicle?->plate ?? '-' }}
                                            </div>
                                            <div>
                                                <span class="font-semibold text-slate-800">
                                                    {{ $route->service_type === 'shift' ? 'Toplama Ücreti:' : 'Sabah Ücret:' }}
                                                </span>
                                                {{ $route->morning_fee !== null ? number_format((float) $route->morning_fee, 2, ',', '.') : '-' }}
                                            </div>
                                            <div>
                                                <span class="font-semibold text-slate-800">
                                                    {{ $route->service_type === 'shift' ? 'Dağıtım Ücreti:' : 'Akşam Ücret:' }}
                                                </span>
                                                {{ $route->evening_fee !== null ? number_format((float) $route->evening_fee, 2, ',', '.') : '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button"
                                            @click="openEditModal = true"
                                            class="inline-flex items-center gap-2 rounded-2xl bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                                        <span>✏️</span>
                                        <span>Düzenle</span>
                                    </button>

                                    <form action="{{ route('customers.service-routes.toggle-status', [$customer, $route->id]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center gap-2 rounded-2xl bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">
                                            <span>{{ $route->is_active ? '🔒' : '✅' }}</span>
                                            <span>{{ $route->is_active ? 'Pasif Yap' : 'Aktif Et' }}</span>
                                        </button>
                                    </form>

                                    <form action="{{ route('customers.service-routes.destroy', [$customer, $route->id]) }}" method="POST" class="inline" onsubmit="return confirm('Bu güzergahı silmek istediğine emin misin?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-2 rounded-2xl bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                                            <span>🗑️</span>
                                            <span>Sil</span>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div x-show="openEditModal"
                                 x-transition.opacity
                                 class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-6"
                                 style="display:none;">
                                <div @click.away="openEditModal = false"
                                     x-transition
                                     class="w-full max-w-5xl overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-2xl shadow-slate-900/20">
                                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                                        <div>
                                            <h3 class="text-xl font-bold text-slate-900">Güzergah Düzenle</h3>
                                            <p class="mt-1 text-sm text-slate-500">
                                                Güzergah bilgilerini, araçları ve ücret kurallarını güncelleyin
                                            </p>
                                        </div>

                                        <button type="button"
                                                @click="openEditModal = false"
                                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 transition hover:bg-slate-200">
                                            ✕
                                        </button>
                                    </div>

                                    <form action="{{ route('customers.service-routes.update', [$customer, $route->id]) }}" method="POST" class="space-y-6 px-6 py-6">
                                        @csrf
                                        @method('PUT')

                                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                            <div class="md:col-span-2">
                                                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                                    Güzergah Adı
                                                </label>
                                                <input type="text"
                                                       name="route_name"
                                                       value="{{ $route->route_name }}"
                                                       class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                            </div>

                                            <div>
                                                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                                    Servis Türü
                                                </label>
                                                <select name="service_type"
                                                        x-model="editServiceType"
                                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                                    <option value="both">Sabah ve Akşam</option>
                                                    <option value="morning">Sadece Sabah</option>
                                                    <option value="evening">Sadece Akşam</option>
                                                    <option value="shift">Vardiya</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                                    Araç Cinsi
                                                </label>
                                                <select name="vehicle_type"
                                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                                    @foreach(['MİNİBÜS (16+1)', 'MİDİBÜS (27+1)', 'OTOBÜS (45+1)', 'TAKSİ', 'VİP ARAÇ'] as $vehicleType)
                                                        <option value="{{ $vehicleType }}" {{ $route->vehicle_type === $vehicleType ? 'selected' : '' }}>
                                                            {{ $vehicleType }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                            <div x-show="editServiceType === 'both' || editServiceType === 'morning' || editServiceType === 'shift'" x-transition>
                                                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400"
                                                       x-text="firstEditVehicleLabel()">
                                                </label>
                                                <select name="morning_vehicle_id"
                                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                                    <option value="">Araç seçiniz</option>
                                                    @foreach($activeVehicles as $vehicle)
                                                        <option value="{{ $vehicle->id }}" {{ (string) $route->morning_vehicle_id === (string) $vehicle->id ? 'selected' : '' }}>
                                                            {{ $vehicle->plate ?? '-' }} - {{ $vehicle->brand ?? '' }} {{ $vehicle->model ?? '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div x-show="editServiceType === 'both' || editServiceType === 'evening' || editServiceType === 'shift'" x-transition>
                                                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400"
                                                       x-text="secondEditVehicleLabel()">
                                                </label>
                                                <select name="evening_vehicle_id"
                                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                                    <option value="">Araç seçiniz</option>
                                                    @foreach($activeVehicles as $vehicle)
                                                        <option value="{{ $vehicle->id }}" {{ (string) $route->evening_vehicle_id === (string) $vehicle->id ? 'selected' : '' }}>
                                                            {{ $vehicle->plate ?? '-' }} - {{ $vehicle->brand ?? '' }} {{ $vehicle->model ?? '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                            <div>
                                                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                                    Ücret Türü
                                                </label>
                                                <select name="fee_type"
                                                        x-model="editFeeType"
                                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                                    <option value="free" {{ $route->fee_type === 'free' ? 'selected' : '' }}>Ücretsiz</option>
                                                    <option value="paid" {{ $route->fee_type === 'paid' ? 'selected' : '' }}>Ücretli</option>
                                                </select>
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                                        Cumartesi Ücret
                                                    </label>
                                                    <select name="saturday_pricing"
                                                            x-model="editSaturdayPricing"
                                                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                                        <option value="yes">Evet</option>
                                                        <option value="no">Hayır</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                                        Pazar Ücret
                                                    </label>
                                                    <select name="sunday_pricing"
                                                            x-model="editSundayPricing"
                                                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                                        <option value="yes">Evet</option>
                                                        <option value="no">Hayır</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div x-show="editFeeType === 'paid'" x-transition class="space-y-5 rounded-[28px] border border-slate-200 bg-slate-50/70 p-5">
                                            <div>
                                                <h4 class="text-sm font-bold text-slate-900">Standart Sefer Ücretleri</h4>
                                                <p class="mt-1 text-xs text-slate-500">
                                                    Tanımlı araçlar kendi güzergahına gittiğinde esas alınacak ücretler
                                                </p>
                                            </div>

                                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                                <div x-show="editServiceType === 'both' || editServiceType === 'morning' || editServiceType === 'shift'" x-transition>
                                                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                                        <span x-text="editServiceType === 'shift' ? 'Toplama Ücreti' : 'Sabah Sefer Ücreti'"></span>
                                                    </label>
                                                    <input type="number"
                                                           step="0.01"
                                                           name="morning_fee"
                                                           value="{{ $route->morning_fee }}"
                                                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                                </div>

                                                <div x-show="editServiceType === 'both' || editServiceType === 'evening' || editServiceType === 'shift'" x-transition>
                                                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                                        <span x-text="editServiceType === 'shift' ? 'Dağıtım Ücreti' : 'Akşam Sefer Ücreti'"></span>
                                                    </label>
                                                    <input type="number"
                                                           step="0.01"
                                                           name="evening_fee"
                                                           value="{{ $route->evening_fee }}"
                                                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rounded-[28px] border border-dashed border-slate-200 bg-slate-50 p-5">
                                            <div>
                                                <h4 class="text-sm font-bold text-slate-900">Tanımlı Araç Dışı Ücret Kuralı</h4>
                                                <p class="mt-1 text-xs leading-6 text-slate-500">
                                                    Eğer puantaj işlenirken bu güzergah için tanımladığınız araçlar dışında başka bir araç plakası girilirse,
                                                    aşağıdaki ücretler şoför maaşına yazılsın.
                                                </p>
                                            </div>

                                            <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                                                <div x-show="editServiceType === 'both' || editServiceType === 'morning' || editServiceType === 'shift'" x-transition>
                                                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                                        <span x-text="editServiceType === 'shift' ? 'Tanımsız Araç Toplama Ücreti' : 'Tanımsız Araç Sabah Ücreti'"></span>
                                                    </label>
                                                    <input type="number"
                                                           step="0.01"
                                                           name="fallback_morning_fee"
                                                           value="{{ $route->fallback_morning_fee }}"
                                                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                                </div>

                                                <div x-show="editServiceType === 'both' || editServiceType === 'evening' || editServiceType === 'shift'" x-transition>
                                                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                                        <span x-text="editServiceType === 'shift' ? 'Tanımsız Araç Dağıtım Ücreti' : 'Tanımsız Araç Akşam Ücreti'"></span>
                                                    </label>
                                                    <input type="number"
                                                           step="0.01"
                                                           name="fallback_evening_fee"
                                                           value="{{ $route->fallback_evening_fee }}"
                                                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                                            <button type="button"
                                                    @click="openEditModal = false"
                                                    class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                                Vazgeç
                                            </button>

                                            <button type="submit"
                                                    class="rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:scale-[1.02]">
                                                Güncelle
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-6 rounded-[28px] border border-dashed border-slate-200 bg-slate-50 px-6 py-14 text-center">
                    <div class="mx-auto max-w-xl">
                        <div class="mb-4 text-5xl">🛣️</div>
                        <div class="text-lg font-bold text-slate-800">Henüz güzergah tanımı yok</div>
                        <div class="mt-2 text-sm leading-7 text-slate-500">
                            Yeni güzergah tanımladığınızda bu müşteriye ait servis planları burada listelenecek.
                            İlerleyen aşamada puantaj ekranında araç hangi müşterinin hangi güzergahına gittiyse,
                            burada tanımlanan ücret ve araç kuralları esas alınacak.
                        </div>

                        <div class="mt-6">
                            <button type="button"
                                    @click="openRouteModal = true"
                                    class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:bg-slate-800">
                                <span>+</span>
                                <span>İlk Güzergahı Oluştur</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div x-show="openRouteModal"
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-6"
         style="display: none;">

        <div @click.away="openRouteModal = false"
             x-transition
             class="w-full max-w-5xl overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-2xl shadow-slate-900/20">

            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Güzergah Tanımla</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Müşteriye özel servis güzergahını ve ücret yapısını oluşturun
                    </p>
                </div>

                <button type="button"
                        @click="openRouteModal = false"
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 transition hover:bg-slate-200">
                    ✕
                </button>
            </div>

            <form action="{{ route('customers.service-routes.store', $customer) }}" method="POST" class="space-y-6 px-6 py-6">
                @csrf

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            Güzergah Adı
                        </label>
                        <input type="text"
                               name="route_name"
                               value="{{ old('route_name') }}"
                               placeholder="Örn: Koraks Sabah Personel Servisi - Merkez Hat"
                               class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                        @error('route_name')
                            <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            Servis Türü
                        </label>
                        <select name="service_type"
                                x-model="serviceType"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                            <option value="both">Sabah ve Akşam</option>
                            <option value="morning">Sadece Sabah</option>
                            <option value="evening">Sadece Akşam</option>
                            <option value="shift">Vardiya</option>
                        </select>
                        @error('service_type')
                            <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            Araç Cinsi
                        </label>
                        <select name="vehicle_type"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                            <option value="">Seçiniz</option>
                            @foreach(['MİNİBÜS (16+1)', 'MİDİBÜS (27+1)', 'OTOBÜS (45+1)', 'TAKSİ', 'VİP ARAÇ'] as $vehicleType)
                                <option value="{{ $vehicleType }}" {{ old('vehicle_type') === $vehicleType ? 'selected' : '' }}>
                                    {{ $vehicleType }}
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_type')
                            <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div x-show="serviceType === 'both' || serviceType === 'morning' || serviceType === 'shift'" x-transition>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400"
                               x-text="firstVehicleLabel()">
                        </label>
                        <select name="morning_vehicle_id"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                            <option value="">Araç seçiniz</option>
                            @forelse($activeVehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ (string) old('morning_vehicle_id') === (string) $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->plate ?? '-' }} - {{ $vehicle->brand ?? '' }} {{ $vehicle->model ?? '' }}
                                </option>
                            @empty
                                <option value="">Aktif araç bulunamadı</option>
                            @endforelse
                        </select>
                        @error('morning_vehicle_id')
                            <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div x-show="serviceType === 'both' || serviceType === 'evening' || serviceType === 'shift'" x-transition>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400"
                               x-text="secondVehicleLabel()">
                        </label>
                        <select name="evening_vehicle_id"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                            <option value="">Araç seçiniz</option>
                            @forelse($activeVehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ (string) old('evening_vehicle_id') === (string) $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->plate ?? '-' }} - {{ $vehicle->brand ?? '' }} {{ $vehicle->model ?? '' }}
                                </option>
                            @empty
                                <option value="">Aktif araç bulunamadı</option>
                            @endforelse
                        </select>
                        @error('evening_vehicle_id')
                            <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            Ücret Türü
                        </label>
                        <select name="fee_type"
                                x-model="feeType"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                            <option value="free">Ücretsiz</option>
                            <option value="paid">Ücretli</option>
                        </select>
                        @error('fee_type')
                            <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                Cumartesi Ücret
                            </label>
                            <select name="saturday_pricing"
                                    x-model="saturdayPricing"
                                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                <option value="yes">Evet</option>
                                <option value="no">Hayır</option>
                            </select>
                            @error('saturday_pricing')
                                <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                Pazar Ücret
                            </label>
                            <select name="sunday_pricing"
                                    x-model="sundayPricing"
                                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                <option value="yes">Evet</option>
                                <option value="no">Hayır</option>
                            </select>
                            @error('sunday_pricing')
                                <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div x-show="feeType === 'paid'" x-transition class="space-y-5 rounded-[28px] border border-slate-200 bg-slate-50/70 p-5">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Standart Sefer Ücretleri</h4>
                        <p class="mt-1 text-xs text-slate-500">
                            Tanımlı araçlar kendi güzergahına gittiğinde esas alınacak ücretler
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div x-show="serviceType === 'both' || serviceType === 'morning' || serviceType === 'shift'" x-transition>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                <span x-text="serviceType === 'shift' ? 'Toplama Ücreti' : 'Sabah Sefer Ücreti'"></span>
                            </label>
                            <input type="number"
                                   step="0.01"
                                   name="morning_fee"
                                   value="{{ old('morning_fee') }}"
                                   placeholder="Örn: 1250"
                                   class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                            @error('morning_fee')
                                <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div x-show="serviceType === 'both' || serviceType === 'evening' || serviceType === 'shift'" x-transition>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                <span x-text="serviceType === 'shift' ? 'Dağıtım Ücreti' : 'Akşam Sefer Ücreti'"></span>
                            </label>
                            <input type="number"
                                   step="0.01"
                                   name="evening_fee"
                                   value="{{ old('evening_fee') }}"
                                   placeholder="Örn: 1250"
                                   class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                            @error('evening_fee')
                                <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-[28px] border border-dashed border-slate-200 bg-slate-50 p-5">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Tanımlı Araç Dışı Ücret Kuralı</h4>
                        <p class="mt-1 text-xs leading-6 text-slate-500">
                            Eğer puantaj işlenirken bu güzergah için tanımladığınız araçlar dışında başka bir araç plakası girilirse,
                            aşağıdaki ücretler şoför maaşına yazılsın.
                        </p>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div x-show="serviceType === 'both' || serviceType === 'morning' || serviceType === 'shift'" x-transition>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                <span x-text="serviceType === 'shift' ? 'Tanımsız Araç Toplama Ücreti' : 'Tanımsız Araç Sabah Ücreti'"></span>
                            </label>
                            <input type="number"
                                   step="0.01"
                                   name="fallback_morning_fee"
                                   value="{{ old('fallback_morning_fee') }}"
                                   placeholder="Örn: 1000"
                                   class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                            @error('fallback_morning_fee')
                                <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div x-show="serviceType === 'both' || serviceType === 'evening' || serviceType === 'shift'" x-transition>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                <span x-text="serviceType === 'shift' ? 'Tanımsız Araç Dağıtım Ücreti' : 'Tanımsız Araç Akşam Ücreti'"></span>
                            </label>
                            <input type="number"
                                   step="0.01"
                                   name="fallback_evening_fee"
                                   value="{{ old('fallback_evening_fee') }}"
                                   placeholder="Örn: 1000"
                                   class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                            @error('fallback_evening_fee')
                                <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4">
                    <div class="text-sm font-semibold text-slate-700">Bilgilendirme</div>
                    <div class="mt-1 text-xs leading-6 text-slate-500">
                        Bu ekrandaki tanımlar ilerleyen aşamada puantaj ve maaş hesaplarında kullanılacak.
                        Cumartesi veya pazar için "Hayır" seçilirse, o gün araç puantaja işlense bile ücret yazılmayacak; sadece plaka kaydı tutulacak.
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <button type="button"
                            @click="openRouteModal = false"
                            class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Vazgeç
                    </button>

                    <button type="submit"
                            class="rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:scale-[1.02]">
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>