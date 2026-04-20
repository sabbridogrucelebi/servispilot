@extends('layouts.app')

@section('title', 'Yeni Personel')
@section('subtitle', 'Yeni personel kaydı oluşturun')

@section('content')
    <form action="{{ route('drivers.store') }}" method="POST" class="space-y-6">
        @csrf

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
                                   value="{{ old('full_name') }}"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            @error('full_name')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">TC Kimlik No</label>
                            <input type="text"
                                   name="tc_no"
                                   value="{{ old('tc_no') }}"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            @error('tc_no')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Telefon</label>
                            <input type="text"
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            @error('phone')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">E-posta</label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            @error('email')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Doğum Tarihi</label>
                            <input type="date"
                                   name="birth_date"
                                   value="{{ old('birth_date') }}"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            @error('birth_date')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">İşe Giriş Tarihi</label>
                            <input type="date"
                                   name="start_date"
                                   value="{{ old('start_date') }}"
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

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Bağlı Araç</label>
                            <select name="vehicle_id"
                                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                <option value="">Araç seçiniz</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" @selected(old('vehicle_id') == $vehicle->id)>
                                        {{ $vehicle->plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}
                                    </option>
                                @endforeach
                            </select>
                            @error('vehicle_id')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Ana Maaş</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="base_salary"
                                   value="{{ old('base_salary') }}"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            @error('base_salary')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Ehliyet Sınıfı</label>
                            <input type="text"
                                   name="license_class"
                                   value="{{ old('license_class') }}"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            @error('license_class')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">SRC Türü</label>
                            <input type="text"
                                   name="src_type"
                                   value="{{ old('src_type') }}"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            @error('src_type')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
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
                                      class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Notlar</label>
                            <textarea name="notes"
                                      rows="4"
                                      class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">{{ old('notes') }}</textarea>
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
                               @checked(old('is_active', true))
                               class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">Aktif personel olarak kaydet</div>
                            <div class="text-xs text-slate-500">Liste ekranında aktif olarak görünsün</div>
                        </div>
                    </label>
                </div>

                <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3">
                        <button type="submit"
                                class="w-full rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-4 text-sm font-semibold text-white shadow transition hover:scale-[1.01]">
                            Personeli Kaydet
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