@extends('layouts.app')

@section('title', 'Bakım Düzenle')
@section('subtitle', 'Kayıtlı bakım ve tamir kaydını güncelleyin')

@section('content')

<div class="max-w-7xl mx-auto">

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700 shadow-sm">
            <div class="mb-2 font-semibold">Lütfen aşağıdaki hataları düzeltin:</div>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('maintenances.update', $maintenance) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="overflow-hidden rounded-[30px] border border-slate-200/70 bg-white/95 backdrop-blur shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
            <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h2 class="text-[32px] font-extrabold tracking-tight text-slate-900">
                            Bakım Kaydını Düzenle
                        </h2>
                        <p class="mt-2 text-sm font-medium text-slate-500">
                            Kayıtlı bakım bilgisini güncelleyebilirsiniz.
                        </p>
                    </div>

                    <a href="{{ route('maintenances.index') }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Listeye Dön
                    </a>
                </div>
            </div>

            <div class="p-6 md:p-7">
                <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                            Araç <span class="text-rose-500">*</span>
                        </label>
                        <select name="vehicle_id" id="vehicle_id"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100">
                            <option value="">Araç seçiniz</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}"
                                    {{ (string) old('vehicle_id', $maintenance->vehicle_id) === (string) $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->plate }}{{ $vehicle->brand ? ' - ' . $vehicle->brand : '' }}{{ $vehicle->model ? ' ' . $vehicle->model : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                            Tarih <span class="text-rose-500">*</span>
                        </label>
                        <input type="date"
                               name="service_date"
                               value="{{ old('service_date', optional($maintenance->service_date)->format('Y-m-d')) }}"
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100">
                        @error('service_date')
                            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                            Kategori <span class="text-rose-500">*</span>
                        </label>
                        <select name="maintenance_type" id="maintenance_type"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold uppercase tracking-wide text-slate-800 shadow-sm outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100">
                            <option value="">Kategori seçiniz</option>
                            @foreach($maintenanceTypes as $type)
                                <option value="{{ $type }}" {{ old('maintenance_type', $maintenance->maintenance_type) === $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                        @error('maintenance_type')
                            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="xl:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                            Araca Yapılan İşlem Adı <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="title"
                               value="{{ old('title', $maintenance->title) }}"
                               list="title-suggestions"
                               class="titlecase-input w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100"
                               placeholder="">
                        <datalist id="title-suggestions">
                            @foreach($titleSuggestions as $titleSuggestion)
                                <option value="{{ $titleSuggestion }}"></option>
                            @endforeach
                        </datalist>
                        @error('title')
                            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Araç Bakım KM'si</label>
                        <input type="number"
                               name="km"
                               id="km"
                               value="{{ old('km', $maintenance->km) }}"
                               min="0"
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100"
                               placeholder="">
                        @error('km')
                            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Bir Sonraki Bakım KM</label>
                        <input type="text"
                               id="next_service_km_preview"
                               value=""
                               readonly
                               class="w-full rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-bold text-indigo-700 shadow-sm outline-none"
                               placeholder="">
                        <p id="next_service_km_help" class="mt-2 text-xs text-slate-500">
                            YAĞ BAKIMI veya ALT YAĞLAMA seçildiğinde otomatik hesaplanır.
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Tutar</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-500">₺</span>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="amount"
                                   value="{{ old('amount', $maintenance->amount) }}"
                                   class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                                   placeholder="">
                        </div>
                        @error('amount')
                            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Usta</label>
                        <select
                            id="mechanic_select"
                            onchange="toggleMechanicInput(this.value)"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold uppercase tracking-wide text-slate-800 shadow-sm outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100"
                        >
                            <option value="">Seçiniz</option>
                            @foreach($masters as $master)
                                <option value="{{ $master }}" {{ old('service_name', $maintenance->service_name) === $master ? 'selected' : '' }}>
                                    {{ mb_strtoupper($master) }}
                                </option>
                            @endforeach
                            <option value="other" {{ old('service_name', $maintenance->service_name) && !$masters->contains(old('service_name', $maintenance->service_name)) ? 'selected' : '' }}>DİĞER</option>
                        </select>

                        <div id="custom_mechanic_div" class="{{ old('service_name', $maintenance->service_name) && !$masters->contains(old('service_name', $maintenance->service_name)) ? 'block' : 'hidden' }} mt-3">
                            <input
                                type="text"
                                id="mechanic_custom_input"
                                name="service_name"
                                value="{{ old('service_name', $maintenance->service_name) && !$masters->contains(old('service_name', $maintenance->service_name)) ? old('service_name', $maintenance->service_name) : '' }}"
                                class="titlecase-input w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100"
                                placeholder="Usta adı giriniz"
                                {{ old('service_name', $maintenance->service_name) && !$masters->contains(old('service_name', $maintenance->service_name)) ? '' : 'disabled' }}
                            >
                        </div>
                        <input type="hidden" id="mechanic_hidden_input" name="service_name" value="{{ $masters->contains(old('service_name', $maintenance->service_name)) ? old('service_name', $maintenance->service_name) : '' }}" {{ $masters->contains(old('service_name', $maintenance->service_name)) ? '' : 'disabled' }}>
                        
                        @error('service_name')
                            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="xl:col-span-3">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Not</label>
                        <textarea name="description"
                                  rows="5"
                                  class="titlecase-input w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100"
                                  placeholder="">{{ old('description', $maintenance->description) }}</textarea>
                        @error('description')
                            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            <div class="border-t border-slate-100 bg-slate-50/70 px-6 py-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="text-xs font-medium text-slate-500">
                        Kayıt güncellendikten sonra bakım listesine döneceksiniz.
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('maintenances.index') }}"
                           class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            Vazgeç
                        </a>

                        <button type="submit"
                                class="rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200/60 transition hover:scale-[1.01]">
                            Değişiklikleri Kaydet
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    /* No uppercase transform, handled via JS */
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function toTitleCaseTr(str) {
            if (!str) return '';
            return str.split(' ').map(function(word) {
                if (!word) return '';
                var first = word.charAt(0).toLocaleUpperCase('tr-TR');
                var rest = word.slice(1).toLocaleLowerCase('tr-TR');
                return first + rest;
            }).join(' ');
        }

        document.querySelectorAll('.titlecase-input').forEach(function (element) {
            element.addEventListener('input', function () {
                const start = this.selectionStart;
                const end = this.selectionEnd;
                this.value = toTitleCaseTr(this.value);
                this.setSelectionRange(start, end);
            });
        });

        const maintenanceSettings = @json($maintenanceSettings ?? []);
        const vehicleSelect = document.getElementById('vehicle_id');
        const typeSelect = document.getElementById('maintenance_type');
        const kmInput = document.getElementById('km');
        const nextKmPreview = document.getElementById('next_service_km_preview');
        const nextKmHelp = document.getElementById('next_service_km_help');

        function formatNumber(value) {
            return new Intl.NumberFormat('tr-TR').format(value);
        }

        function calculateNextKm() {
            const vehicleId = vehicleSelect.value;
            const type = (typeSelect.value || '').toLocaleUpperCase('tr-TR').trim();
            const km = parseInt(kmInput.value || 0, 10);

            nextKmPreview.value = '';
            nextKmHelp.textContent = 'YAĞ BAKIMI veya ALT YAĞLAMA seçildiğinde otomatik hesaplanır.';

            if (!vehicleId || !type || !km || !maintenanceSettings[vehicleId]) {
                return;
            }

            const settings = maintenanceSettings[vehicleId];
            let nextKm = null;

            if (type === 'YAĞ BAKIMI' && settings.oil_change_interval_km) {
                nextKm = km + parseInt(settings.oil_change_interval_km, 10);
                nextKmHelp.textContent = 'Araç için tanımlı yağ bakım km aralığına göre hesaplandı.';
            } else if (type === 'ALT YAĞLAMA' && settings.under_lubrication_interval_km) {
                nextKm = km + parseInt(settings.under_lubrication_interval_km, 10);
                nextKmHelp.textContent = 'Araç için tanımlı alt yağlama km aralığına göre hesaplandı.';
            } else if (type === 'YAĞ BAKIMI' || type === 'ALT YAĞLAMA') {
                nextKmHelp.textContent = 'Bu araç için bakım ayarlarında km aralığı tanımlanmamış.';
            }

            if (nextKm !== null) {
                nextKmPreview.value = formatNumber(nextKm) + ' KM';
            }
        }

        vehicleSelect.addEventListener('change', calculateNextKm);
        typeSelect.addEventListener('change', function() {
            calculateNextKm();

            // Otomatik başlık (title) doldurma
            const titleInput = document.querySelector('input[name="title"]');
            const cat = (this.value || '').toUpperCase();
            let newTitle = titleInput.value;

            if (!newTitle || newTitle.endsWith('Yapıldı') || newTitle.endsWith('Bakımı')) {
                if (cat === 'YAĞ BAKIMI') newTitle = 'Yağ Bakımı Yapıldı';
                else if (cat === 'ALT YAĞLAMA') newTitle = 'Alt Yağlama Yapıldı';
                else if (cat === 'LASTİK BAKIMI') newTitle = 'Lastik Bakımı Yapıldı';
                else if (cat === 'AKÜ BAKIMI') newTitle = 'Akü Bakımı Yapıldı';
                else if (cat === 'AĞIR BAKIM') newTitle = 'Ağır Bakım Yapıldı';
                else if (cat === 'ANTFRİZ BAKIMI') newTitle = 'Antfriz Bakımı Yapıldı';
                else if (cat === 'ARIZA/ONARIM' || cat === 'ARIZA / ONARIM') newTitle = 'Arıza Onarım Yapıldı';
                else if (cat === 'MUAYENE') newTitle = 'Muayene Yapıldı';
                else if (cat === 'DİĞER BAKIMLAR') newTitle = 'Bakım Yapıldı';

                titleInput.value = newTitle;
            }
        });
        kmInput.addEventListener('input', calculateNextKm);

        calculateNextKm();
    });

    function toggleMechanicInput(val) {
        const customDiv = document.getElementById('custom_mechanic_div');
        const customInput = document.getElementById('mechanic_custom_input');
        const hiddenInput = document.getElementById('mechanic_hidden_input');

        if (val === 'other') {
            customDiv.classList.remove('hidden');
            customDiv.classList.add('block');
            customInput.disabled = false;
            hiddenInput.disabled = true;
            hiddenInput.value = '';
        } else {
            customDiv.classList.add('hidden');
            customDiv.classList.remove('block');
            customInput.disabled = true;
            hiddenInput.disabled = false;
            hiddenInput.value = val;
        }
    }
</script>

@endsection
