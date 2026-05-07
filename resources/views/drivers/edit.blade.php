@extends('layouts.app')

@section('title', 'Personel Düzenle')
@section('subtitle', 'Mevcut personel kaydını güncelleyin')

@section('content')
    <form action="{{ route('drivers.update', $driver) }}" method="POST" class="space-y-6" x-data="{ 
        showShiftModal: false,
        tempStartDate: '{{ old('start_date', optional($driver->start_date)->format('Y-m-d')) }}',
        startShift: '{{ old('start_shift', $driver->start_shift ?? 'morning') }}',
        handleDateChange(val) {
            // Sadece tarih değiştiğinde (veya yeni tarih seçildiğinde) modalı aç
            if(val && val !== '{{ optional($driver->start_date)->format('Y-m-d') }}') {
                this.showShiftModal = true;
            }
        }
    }">
        @csrf
        @method('PUT')

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2 space-y-6">
                <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-900">Kişisel Bilgiler</h3>
                    <p class="mt-1 text-sm text-slate-500">Personelin temel kimlik ve iletişim bilgileri</p>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Ad Soyad</label>
                            <input type="text"
                                   name="full_name"
                                   value="{{ old('full_name', $driver->full_name) }}"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            @error('full_name')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">TC Kimlik No</label>
                            <input type="text"
                                   name="tc_no"
                                   value="{{ old('tc_no', $driver->tc_no) }}"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            @error('tc_no')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Telefon</label>
                            <input type="text"
                                   name="phone"
                                   value="{{ old('phone', $driver->phone) }}"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            @error('phone')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">E-posta</label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email', $driver->email) }}"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            @error('email')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Doğum Tarihi</label>
                            <input type="date"
                                   name="birth_date"
                                   value="{{ old('birth_date', optional($driver->birth_date)->format('Y-m-d')) }}"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            @error('birth_date')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">İşe Giriş Tarihi</label>
                            <input type="date"
                                   name="start_date"
                                   x-model="tempStartDate"
                                   @change="handleDateChange($event.target.value)"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            @error('start_date')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-900">İş Bilgileri</h3>
                    <p class="mt-1 text-sm text-slate-500">Araç atama, sürücü sınıfı ve maaş bilgisi</p>

                    <div>
                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-700">Bağlı Araç</label>
                                <select name="vehicle_id"
                                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                    <option value="">Araç seçiniz</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', $driver->vehicle_id) == $vehicle->id)>
                                            {{ $vehicle->plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vehicle_id')
                                    <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-700">Ana Maaş (30 Günlük)</label>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="base_salary"
                                       value="{{ old('base_salary', $driver->base_salary) }}"
                                       class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                @error('base_salary')
                                    <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-700">Ehliyet Sınıfı</label>
                                <select name="license_class"
                                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                    <option value="">Seçiniz</option>
                                    <option value="D1 - Minibüs" @selected(old('license_class', $driver->license_class) == 'D1 - Minibüs')>D1 - Minibüs</option>
                                    <option value="D - Otobüs" @selected(old('license_class', $driver->license_class) == 'D - Otobüs')>D - Otobüs</option>
                                </select>
                                @error('license_class')
                                    <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Başlangıç Vardiyası Modalı -->
                        <div x-show="showShiftModal" 
                             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                             x-cloak x-transition>
                            <div class="w-full max-w-md rounded-[32px] bg-white p-8 shadow-2xl" @click.away="showShiftModal = false">
                                <div class="text-center mb-6">
                                    <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <h3 class="text-xl font-black text-slate-900">Çalışma Başlangıç Saati</h3>
                                    <p class="text-sm font-bold text-slate-500 mt-1">İşe giriş günü hangi vardiyada başladı?</p>
                                </div>

                                <input type="hidden" name="start_shift" :value="startShift">

                                <div class="grid grid-cols-2 gap-4">
                                    <button type="button" 
                                            @click="startShift = 'morning'; showShiftModal = false"
                                            :class="startShift === 'morning' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600'"
                                            class="flex flex-col items-center justify-center p-6 rounded-3xl border-2 transition-all hover:border-blue-300">
                                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.243 17.243l.707.707M7.757 7.757l.707-.707"/></svg>
                                        <span class="font-black text-xs uppercase">Sabah Başladı</span>
                                        <span class="text-[10px] font-bold opacity-60 mt-1">(Tam Yevmiye)</span>
                                    </button>

                                    <button type="button" 
                                            @click="startShift = 'evening'; showShiftModal = false"
                                            :class="startShift === 'evening' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 bg-white text-slate-600'"
                                            class="flex flex-col items-center justify-center p-6 rounded-3xl border-2 transition-all hover:border-indigo-300">
                                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                                        <span class="font-black text-xs uppercase">Akşam Başladı</span>
                                        <span class="text-[10px] font-bold opacity-60 mt-1">(Yarım Yevmiye)</span>
                                    </button>
                                </div>

                                <button type="button" @click="showShiftModal = false" class="w-full mt-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Vazgeç</button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-700">
                        Ehliyet, SRC, Adli Sicil, Psikoteknik, MYK ve diğer belge süreleri artık
                        <span class="font-semibold">Personel Detayı &gt; Belge ve Dökümanlar</span>
                        sekmesinden belge yüklenerek takip edilir.
                    </div>
                </div>

                <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-900">Adres ve Notlar</h3>

                    <div class="mt-6 space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Adres</label>
                            <textarea name="address"
                                      rows="3"
                                      class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">{{ old('address', $driver->address) }}</textarea>
                            @error('address')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Notlar</label>
                            <textarea name="notes"
                                      rows="4"
                                      class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">{{ old('notes', $driver->notes) }}</textarea>
                            @error('notes')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-900">Durum</h3>

                    <label class="mt-5 inline-flex w-full items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               @checked(old('is_active', $driver->is_active))
                               class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">Aktif personel</div>
                            <div class="text-xs text-slate-500">Kayıt listede aktif olarak görünsün</div>
                        </div>
                    </label>
                </div>

                <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Mevcut Araç</span>
                            <span class="font-semibold text-slate-800">{{ $driver->vehicle?->plate ?? '-' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Maaş</span>
                            <span class="font-semibold text-slate-800">
                                {{ $driver->base_salary ? number_format($driver->base_salary, 2, ',', '.') . ' ₺' : '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3">
                        <button type="submit"
                                class="w-full rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-4 text-sm font-semibold text-white shadow transition hover:scale-[1.01]">
                            Değişiklikleri Kaydet
                        </button>

                        <a href="{{ route('drivers.index') }}"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Vazgeç
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
