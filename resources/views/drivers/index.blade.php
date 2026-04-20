@extends('layouts.app')

@section('title', 'Personeller')
@section('subtitle', 'Personel kayıtları, belge durumu ve araç atamalarını yönetin')

@section('content')

<div class="space-y-6">

    @if(session('success'))
        <div class="rounded-[24px] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- ÜST KARTLAR -->
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">

        <div class="rounded-[28px] bg-gradient-to-r from-indigo-500 to-violet-500 p-6 text-white shadow-lg">
            <div class="text-sm font-medium text-white/80">Toplam Personel</div>
            <div class="mt-3 text-4xl font-extrabold">{{ $totalDrivers }}</div>
            <div class="mt-2 text-sm text-white/80">Sistemde kayıtlı toplam personel</div>
        </div>

        <div class="rounded-[28px] bg-gradient-to-r from-emerald-500 to-teal-500 p-6 text-white shadow-lg">
            <div class="text-sm font-medium text-white/80">Aktif Personel</div>
            <div class="mt-3 text-4xl font-extrabold">{{ $activeDrivers }}</div>
            <div class="mt-2 text-sm text-white/80">Görevde olan personeller</div>
        </div>

        <div class="rounded-[28px] bg-gradient-to-r from-slate-500 to-slate-700 p-6 text-white shadow-lg">
            <div class="text-sm font-medium text-white/80">Pasif Personel</div>
            <div class="mt-3 text-4xl font-extrabold">{{ $passiveDrivers }}</div>
            <div class="mt-2 text-sm text-white/80">Pasif durumdaki kayıtlar</div>
        </div>

        <div class="rounded-[28px] bg-gradient-to-r from-rose-500 to-pink-500 p-6 text-white shadow-lg">
            <div class="text-sm font-medium text-white/80">Süresi Geçmiş</div>
            <div class="mt-3 text-4xl font-extrabold">{{ $expiredDocumentCount }}</div>
            <div class="mt-2 text-sm text-white/80">Acil güncelleme gereken belge</div>
        </div>

        <div class="rounded-[28px] bg-gradient-to-r from-amber-400 to-orange-500 p-6 text-white shadow-lg">
            <div class="text-sm font-medium text-white/80">Yakında Bitecek</div>
            <div class="mt-3 text-4xl font-extrabold">{{ $expiringSoonCount }}</div>
            <div class="mt-2 text-sm text-white/80">7-30 gün içinde bitecek belge</div>
        </div>

    </div>

    <!-- FİLTRE -->
    <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('drivers.index') }}" class="grid gap-4 lg:grid-cols-5">

            <div class="lg:col-span-2">
                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Arama</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Ad soyad, telefon, TC, ehliyet..."
                       class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Araç</label>
                <select name="vehicle_id"
                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    <option value="">Tüm Araçlar</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected(request('vehicle_id') == $vehicle->id)>
                            {{ $vehicle->plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Durum</label>
                <select name="status"
                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    <option value="">Tümü</option>
                    <option value="active" @selected(request('status') === 'active')>Aktif</option>
                    <option value="passive" @selected(request('status') === 'passive')>Pasif</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Belge Durumu</label>
                <select name="document_status"
                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    <option value="">Tümü</option>
                    <option value="ok" @selected(request('document_status') === 'ok')>Uygun</option>
                    <option value="expiring" @selected(request('document_status') === 'expiring')>Yakında Bitecek</option>
                    <option value="expired" @selected(request('document_status') === 'expired')>Süresi Geçmiş</option>
                </select>
            </div>

            <div class="lg:col-span-5 flex flex-wrap items-end justify-between gap-3">
                <div class="flex flex-wrap items-end gap-3">
                    <button type="submit"
                            class="rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-3 text-sm font-semibold text-white shadow transition hover:scale-[1.01]">
                        Filtrele
                    </button>

                    <a href="{{ route('drivers.index') }}"
                       class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Temizle
                    </a>
                </div>

                <a href="{{ route('drivers.create') }}"
                   class="rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-3 text-sm font-semibold text-white shadow transition hover:scale-[1.01]">
                    + Yeni Personel
                </a>
            </div>

        </form>
    </div>

    <!-- TABLO -->
    <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">

        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-lg font-bold text-slate-900">Personel Listesi</h3>
            <p class="mt-1 text-sm text-slate-500">Personelleri ve belge durumlarını buradan takip edin.</p>
        </div>

        @if($drivers->count())
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">

                    <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-6 py-4 text-left font-bold">Ad Soyad</th>
                        <th class="px-6 py-4 text-left font-bold">İletişim</th>
                        <th class="px-6 py-4 text-left font-bold">Araç</th>
                        <th class="px-6 py-4 text-left font-bold">İşe Giriş</th>
                        <th class="px-6 py-4 text-left font-bold">Maaş</th>
                        <th class="px-6 py-4 text-left font-bold">Belge Durumu</th>
                        <th class="px-6 py-4 text-left font-bold">Durum</th>
                        <th class="px-6 py-4 text-left font-bold">İşlemler</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                    @foreach($drivers as $driver)
                        @php
                            $docStatus = $driver->resolved_document_status ?? [
                                'label' => 'Uygun',
                                'class' => 'bg-emerald-100 text-emerald-700',
                                'priority' => 'ok',
                            ];
                        @endphp

                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-5">
                                <div class="font-bold text-slate-900">
                                    {{ $driver->full_name }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    TC: {{ $driver->tc_no ?: '-' }}
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="font-medium text-slate-800">
                                    {{ $driver->phone ?? '-' }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $driver->email ?: '-' }}
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="font-medium text-slate-800">
                                    {{ $driver->vehicle?->plate ?? '-' }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    @if($driver->vehicle)
                                        {{ trim(($driver->vehicle->brand ?? '') . ' ' . ($driver->vehicle->model ?? '')) }}
                                    @else
                                        Araç atanmamış
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="font-medium text-slate-800">
                                    {{ optional($driver->start_date)->format('d.m.Y') ?: '-' }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    Doğum: {{ optional($driver->birth_date)->format('d.m.Y') ?: '-' }}
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="font-semibold text-slate-900">
                                    {{ $driver->base_salary ? number_format($driver->base_salary, 2, ',', '.') . ' ₺' : '-' }}
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $docStatus['class'] }}">
                                    {{ $docStatus['label'] }}
                                </span>
                            </td>

                            <td class="px-6 py-5">
                                @if($driver->is_active)
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                                        Aktif
                                    </span>
                                @else
                                    <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-xs font-semibold">
                                        Pasif
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-5">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('drivers.show', $driver) }}"
                                       class="rounded-xl bg-sky-100 px-3 py-2 text-xs font-semibold text-sky-700 hover:bg-sky-200">
                                        Detay
                                    </a>

                                    <a href="{{ route('drivers.edit', $driver) }}"
                                       class="rounded-xl bg-indigo-100 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-200">
                                        Düzenle
                                    </a>

                                    <form method="POST"
                                          action="{{ route('drivers.destroy', $driver) }}"
                                          onsubmit="return confirm('Bu personeli silmek istediğinize emin misiniz?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-xl bg-red-100 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-200">
                                            Sil
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>

                </table>
            </div>
        @else
            <div class="p-10 text-center text-slate-400">
                Henüz personel kaydı yok
            </div>
        @endif

    </div>

</div>

@endsection