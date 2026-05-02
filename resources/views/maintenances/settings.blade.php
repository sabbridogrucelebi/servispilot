@extends('layouts.app')

@section('title', 'Bakım Ayarları')
@section('subtitle', 'Araç bazlı yağ bakım ve alt yağlama kilometre aralıklarını yönetin')

@section('content')

<div class="space-y-6">

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">Bakım Ayarları</h2>
            <p class="mt-2 text-sm font-medium text-slate-500">
                Her araç için yağ bakım ve alt yağlama kilometre aralıklarını ayrı ayrı belirleyin.
            </p>
        </div>

        <a href="{{ route('maintenances.index') }}"
           class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
            ← Bakım Listesine Dön
        </a>
    </div>

    <form action="{{ route('maintenances.settings.store') }}" method="POST">
        @csrf

        <div class="overflow-hidden rounded-[30px] border border-slate-200/60 bg-white/90 shadow-xl backdrop-blur">
            <div class="border-b border-slate-100 px-6 py-5">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Araç Bazlı Bakım Aralıkları</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Örnek: Otokar Sultan için yağ bakımı 10.000 KM, alt yağlama 5.000 KM.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px]">
                    <thead class="border-b border-slate-100 bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Araç</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Marka / Model</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Yağ Bakım KM Aralığı</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Alt Yağlama KM Aralığı</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($vehicles as $index => $vehicle)
                            <tr class="hover:bg-indigo-50/30 transition">
                                <td class="px-6 py-5">
                                    <div class="text-sm font-extrabold tracking-wide text-slate-900">
                                        {{ $vehicle->plate }}
                                    </div>
                                </td>

                                <td class="px-6 py-5 text-sm text-slate-600">
                                    {{ $vehicle->brand ?? '-' }} {{ $vehicle->model ?? '' }}
                                </td>

                                <td class="px-6 py-5">
                                    <input type="hidden" name="settings[{{ $index }}][vehicle_id]" value="{{ $vehicle->id }}">

                                    <input type="number"
                                           min="0"
                                           name="settings[{{ $index }}][oil_change_interval_km]"
                                           value="{{ old("settings.$index.oil_change_interval_km", optional($vehicle->maintenanceSetting)->oil_change_interval_km) }}"
                                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500"
                                           placeholder="ÖRN: 10000">
                                </td>

                                <td class="px-6 py-5">
                                    <input type="number"
                                           min="0"
                                           name="settings[{{ $index }}][under_lubrication_interval_km]"
                                           value="{{ old("settings.$index.under_lubrication_interval_km", optional($vehicle->maintenanceSetting)->under_lubrication_interval_km) }}"
                                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500"
                                           placeholder="ÖRN: 5000">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10">
                                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                                        <div class="mb-3 text-4xl">🚐</div>
                                        <div class="text-base font-semibold text-slate-700">Araç bulunamadı</div>
                                        <div class="mt-1 text-sm text-slate-500">Bakım ayarları için önce araç kaydı olmalı.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 bg-slate-50/70 px-6 py-5">
                <div class="flex justify-end">
                    <button type="submit"
                            class="rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200/60 transition hover:scale-[1.01]">
                        Ayarları Kaydet
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="mt-8 overflow-hidden rounded-[30px] border border-slate-200/60 bg-white/90 shadow-xl backdrop-blur">
        <div class="border-b border-slate-100 px-6 py-5 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Usta Listesi</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Sistemde tanımlı ustaları yönetin. Pasife alınan ustalar yeni kayıtlarda görünmez.
                </p>
            </div>
            <button type="button"
                    onclick="document.getElementById('addMechanicModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                + Usta Tanımla
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px]">
                <thead class="border-b border-slate-100 bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Usta Adı</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Durum</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-[0.14em] text-slate-500">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($mechanics as $mechanic)
                        <tr class="hover:bg-indigo-50/30 transition">
                            <td class="px-6 py-5">
                                <div class="text-sm font-extrabold tracking-wide text-slate-900">
                                    {{ $mechanic->name }}
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                @if($mechanic->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-md bg-rose-50 px-2 py-1 text-xs font-bold text-rose-700 ring-1 ring-inset ring-rose-600/20">
                                        Pasif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button type="button" 
                                            onclick="openEditMechanicModal({{ $mechanic->id }}, '{{ addslashes($mechanic->name) }}')"
                                            class="text-sm font-bold text-indigo-600 hover:text-indigo-800">
                                        Düzenle
                                    </button>
                                    
                                    <form action="{{ route('maintenances.mechanics.toggle', $mechanic) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-sm font-bold text-slate-500 hover:text-slate-800">
                                            {{ $mechanic->is_active ? 'Pasif Yap' : 'Aktif Yap' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('maintenances.mechanics.destroy', $mechanic) }}" method="POST" class="inline" onsubmit="return confirm('Bu ustayı silmek istediğinize emin misiniz? (Geçmiş kayıtlardaki isimler bozulmaz)');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-bold text-rose-600 hover:text-rose-800">
                                            Sil
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10">
                                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                                    <div class="mb-3 text-4xl">👨‍🔧</div>
                                    <div class="text-base font-semibold text-slate-700">Usta bulunamadı</div>
                                    <div class="mt-1 text-sm text-slate-500">Yeni bir usta tanımlayarak başlayabilirsiniz.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals -->
    <div id="addMechanicModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('addMechanicModal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('maintenances.mechanics.store') }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-bold text-slate-900" id="modal-title">Yeni Usta Ekle</h3>
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Usta Adı Soyadı</label>
                                    <input type="text" name="name" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="Örn: Ahmet Yılmaz">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-100">
                        <button type="submit" class="w-full inline-flex justify-center rounded-2xl border border-transparent shadow-sm px-5 py-3 bg-indigo-600 text-base font-semibold text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Kaydet
                        </button>
                        <button type="button" onclick="document.getElementById('addMechanicModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-2xl border border-slate-300 shadow-sm px-5 py-3 bg-white text-base font-semibold text-slate-700 hover:bg-slate-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            İptal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="editMechanicModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('editMechanicModal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="editMechanicForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-bold text-slate-900" id="modal-title">Usta Düzenle</h3>
                                <p class="text-sm text-amber-600 mt-2 font-medium bg-amber-50 p-3 rounded-xl border border-amber-200">Dikkat: İsmi değiştirirseniz, geçmiş kayıtlardaki isimler de yeni isimle güncellenecektir.</p>
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Usta Adı Soyadı</label>
                                    <input type="text" id="editMechanicName" name="name" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-100">
                        <button type="submit" class="w-full inline-flex justify-center rounded-2xl border border-transparent shadow-sm px-5 py-3 bg-indigo-600 text-base font-semibold text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Güncelle
                        </button>
                        <button type="button" onclick="document.getElementById('editMechanicModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-2xl border border-slate-300 shadow-sm px-5 py-3 bg-white text-base font-semibold text-slate-700 hover:bg-slate-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            İptal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openEditMechanicModal(id, name) {
            document.getElementById('editMechanicName').value = name;
            document.getElementById('editMechanicForm').action = "/maintenances/settings/mechanics/" + id;
            document.getElementById('editMechanicModal').classList.remove('hidden');
        }
    </script>

</div>

@endsection
