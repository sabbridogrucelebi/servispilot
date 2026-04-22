@extends('layouts.app')

@section('title', 'Personel Detayı')
@section('subtitle', 'Personel bilgileri, belgeler, maaşlar ve resimler')

@section('content')
    @php
        $documentBadge = match($documentStatus) {
            'expired' => ['label' => 'Süresi Geçmiş', 'class' => 'bg-rose-100 text-rose-700'],
            'expiring' => ['label' => 'Yakında Bitecek', 'class' => 'bg-amber-100 text-amber-700'],
            default => ['label' => 'Uygun', 'class' => 'bg-emerald-100 text-emerald-700'],
        };

        $phoneForLink = $driver->phone ? preg_replace('/[^0-9]/', '', $driver->phone) : null;
        if ($phoneForLink && str_starts_with($phoneForLink, '0')) {
            $phoneForLink = '90' . substr($phoneForLink, 1);
        }

        $tabClass = function ($tab) use ($activeTab) {
            $active = $activeTab === $tab;

            return $active
                ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-200/70'
                : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50';
        };

        $remainingBadgeClass = function ($document) {
            return match($document->alert_status) {
                'expired' => 'bg-rose-100 text-rose-700',
                'expiring' => 'bg-amber-100 text-amber-700',
                'active_soon' => 'bg-orange-100 text-orange-700',
                'active' => 'bg-emerald-100 text-emerald-700',
                default => 'bg-slate-100 text-slate-600',
            };
        };
    @endphp

    <div class="space-y-6">
        <div class="grid gap-4 xl:grid-cols-12">
            <div class="xl:col-span-9 space-y-5">
                <div class="rounded-[30px] border border-slate-200/60 bg-white/90 shadow-xl overflow-hidden">
                    <div class="flex flex-col gap-5 border-b border-slate-100 px-6 py-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-[22px] bg-gradient-to-br from-blue-500 to-indigo-600 text-3xl text-white shadow-lg">
                                🧑‍✈️
                            </div>

                            <div>
                                <div class="text-[2rem] font-extrabold tracking-tight text-slate-900">
                                    {{ $driver->full_name }}
                                </div>
                                <div class="mt-1 text-sm font-medium text-slate-500">
                                    {{ $driver->vehicle?->plate ?? 'Araç atanmamış' }}
                                    @if($driver->vehicle)
                                        • {{ trim(($driver->vehicle->brand ?? '') . ' ' . ($driver->vehicle->model ?? '')) }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <span class="inline-flex items-center rounded-full {{ $driver->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }} px-4 py-2 text-sm font-semibold">
                                {{ $driver->is_active ? 'Aktif Personel' : 'Pasif Personel' }}
                            </span>

                            @if($driver->phone)
                                <a href="tel:{{ $driver->phone }}"
                                   class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                    Ara
                                </a>
                            @endif

                            @if($phoneForLink)
                                <a href="https://wa.me/{{ $phoneForLink }}"
                                   target="_blank"
                                   class="rounded-2xl bg-green-50 px-4 py-3 text-sm font-semibold text-green-700 transition hover:bg-green-100">
                                    WhatsApp
                                </a>
                            @endif

                            <a href="{{ route('drivers.edit', $driver) }}"
                               class="rounded-2xl bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                                Düzenle
                            </a>

                            <button type="button" onclick="openLeaveModal()"
                                    class="rounded-2xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                                🛑 İşten Ayrıldı
                            </button>

                            <button type="button" onclick="openChangeVehicleModal()"
                                    class="rounded-2xl bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                                🚐 Araç Değiştir
                            </button>

                            <a href="{{ route('drivers.index') }}"
                               class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Listeye Dön
                            </a>
                        </div>
                    </div>

                    <div class="grid gap-4 px-6 py-5 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-[24px] bg-gradient-to-r from-blue-500 to-sky-500 p-5 text-white">
                            <div class="text-sm text-white/80">Ana Maaş</div>
                            <div class="mt-2 text-2xl font-extrabold">
                                {{ $driver->base_salary ? number_format($driver->base_salary, 2, ',', '.') . ' ₺' : '-' }}
                            </div>
                            <div class="mt-1 text-xs text-white/75">Tanımlı sabit maaş</div>
                        </div>

                        <div class="rounded-[24px] bg-gradient-to-r from-orange-500 to-amber-500 p-5 text-white">
                            <div class="text-sm text-white/80">Belge Durumu</div>
                            <div class="mt-2 text-2xl font-extrabold">{{ $documentBadge['label'] }}</div>
                            <div class="mt-1 text-xs text-white/75">Belge ve döküman takibi</div>
                        </div>

                        <div class="rounded-[24px] bg-gradient-to-r from-violet-500 to-fuchsia-500 p-5 text-white">
                            <div class="text-sm text-white/80">Toplam Bordro</div>
                            <div class="mt-2 text-2xl font-extrabold">{{ $totalPayrollCount }}</div>
                            <div class="mt-1 text-xs text-white/75">Kayıtlı maaş dönemi</div>
                        </div>

                        <div class="rounded-[24px] bg-gradient-to-r from-rose-500 to-pink-500 p-5 text-white">
                            <div class="text-sm text-white/80">Toplam Evrak</div>
                            <div class="mt-2 text-2xl font-extrabold">{{ $documentCount }}</div>
                            <div class="mt-1 text-xs text-white/75">Belge ve resim toplamı</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-[30px] border border-slate-200/60 bg-white/90 shadow-xl overflow-hidden">
                    <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-xl text-white shadow-lg">
                                📁
                            </div>
                            <div>
                                <h3 class="text-[1.85rem] font-extrabold tracking-tight text-slate-900">Personel Yönetim Sekmeleri</h3>
                                <p class="mt-1 text-sm font-medium text-slate-500">Personel bilgilerini modüler ve profesyonel şekilde yönetin</p>
                            </div>
                        </div>

                        <div class="inline-flex items-center rounded-full bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-200">
                            ● Aktif personel yönetim merkezi
                        </div>
                    </div>

                    <div class="space-y-5 px-6 py-5">
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('drivers.show', ['driver' => $driver->id, 'tab' => 'general']) }}"
                               class="rounded-2xl px-5 py-3 text-sm font-semibold transition {{ $tabClass('general') }}">
                                📋 Genel Bilgiler
                            </a>

                            <a href="{{ route('drivers.show', ['driver' => $driver->id, 'tab' => 'documents']) }}"
                               class="rounded-2xl px-5 py-3 text-sm font-semibold transition {{ $tabClass('documents') }}">
                                📄 Belge ve Dökümanlar
                            </a>

                            <a href="{{ route('drivers.show', ['driver' => $driver->id, 'tab' => 'payrolls']) }}"
                               class="rounded-2xl px-5 py-3 text-sm font-semibold transition {{ $tabClass('payrolls') }}">
                                💵 Maaşlar
                            </a>

                            <a href="{{ route('drivers.show', ['driver' => $driver->id, 'tab' => 'images']) }}"
                               class="rounded-2xl px-5 py-3 text-sm font-semibold transition {{ $tabClass('images') }}">
                                🖼️ Resimler
                            </a>
                        </div>

                        @if($activeTab === 'general')
                            <div class="grid gap-6 xl:grid-cols-12">
                                <div class="xl:col-span-8 rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                                    <div class="border-b border-slate-100 px-6 py-5">
                                        <h4 class="text-xl font-bold text-slate-900">Genel Personel Bilgileri</h4>
                                        <p class="mt-1 text-sm text-slate-500">Personelin kimlik, iletişim ve iş bilgileri</p>
                                    </div>

                                    <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-3">
                                        <div class="rounded-2xl bg-slate-50 p-4">
                                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Ad Soyad</div>
                                            <div class="mt-2 text-base font-bold text-slate-900">{{ $driver->full_name }}</div>
                                        </div>

                                        <div class="rounded-2xl bg-slate-50 p-4">
                                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">TC Kimlik No</div>
                                            <div class="mt-2 text-base font-bold text-slate-900">{{ $driver->tc_no ?: '-' }}</div>
                                        </div>

                                        <div class="rounded-2xl bg-slate-50 p-4">
                                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Telefon</div>
                                            <div class="mt-2 text-base font-bold text-slate-900">{{ $driver->phone ?: '-' }}</div>
                                        </div>

                                        <div class="rounded-2xl bg-slate-50 p-4">
                                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">E-posta</div>
                                            <div class="mt-2 text-base font-bold text-slate-900">{{ $driver->email ?: '-' }}</div>
                                        </div>

                                        <div class="rounded-2xl bg-slate-50 p-4">
                                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Doğum Tarihi</div>
                                            <div class="mt-2 text-base font-bold text-slate-900">
                                                {{ optional($driver->birth_date)->format('d.m.Y') ?: '-' }}
                                            </div>
                                            <div class="mt-1 text-xs text-slate-500">
                                                {{ !is_null($driverAge) ? $driverAge . ' yaş' : '-' }}
                                            </div>
                                        </div>

                                        <div class="rounded-2xl bg-slate-50 p-4">
                                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">İşe Giriş Tarihi</div>
                                            <div class="mt-2 text-base font-bold text-slate-900">
                                                {{ optional($driver->start_date)->format('d.m.Y') ?: '-' }}
                                            </div>
                                            <div class="mt-1 text-xs text-slate-500">
                                                {{ !is_null($serviceYears) ? $serviceYears . ' yıl hizmet' : '-' }}
                                            </div>
                                        </div>

                                        <div class="rounded-2xl bg-slate-50 p-4">
                                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Bağlı Araç</div>
                                            <div class="mt-2 text-base font-bold text-slate-900">{{ $driver->vehicle?->plate ?? '-' }}</div>
                                            <div class="mt-1 text-xs text-slate-500">
                                                @if($driver->vehicle)
                                                    {{ trim(($driver->vehicle->brand ?? '') . ' ' . ($driver->vehicle->model ?? '')) }}
                                                @else
                                                    Araç atanmamış
                                                @endif
                                            </div>
                                        </div>

                                        <div class="rounded-2xl bg-slate-50 p-4">
                                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Ehliyet Sınıfı</div>
                                            <div class="mt-2 text-base font-bold text-slate-900">{{ $driver->license_class ?: '-' }}</div>
                                        </div>

                                        <div class="rounded-2xl bg-slate-50 p-4">
                                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">SRC Türü</div>
                                            <div class="mt-2 text-base font-bold text-slate-900">{{ $driver->src_type ?: '-' }}</div>
                                        </div>

                                        <div class="rounded-2xl bg-slate-50 p-4">
                                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Ana Maaş</div>
                                            <div class="mt-2 text-base font-bold text-slate-900">
                                                {{ $driver->base_salary ? number_format($driver->base_salary, 2, ',', '.') . ' ₺' : '-' }}
                                            </div>
                                        </div>

                                        <div class="rounded-2xl bg-slate-50 p-4">
                                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Durum</div>
                                            <div class="mt-2">
                                                <span class="inline-flex rounded-full {{ $driver->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }} px-3 py-1 text-xs font-bold">
                                                    {{ $driver->is_active ? 'Aktif' : 'Pasif' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-t border-slate-100 p-6">
                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div class="rounded-2xl bg-slate-50 p-4">
                                                <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Adres</div>
                                                <div class="mt-2 text-sm text-slate-700">{{ $driver->address ?: '-' }}</div>
                                            </div>

                                            <div class="rounded-2xl bg-slate-50 p-4">
                                                <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Notlar</div>
                                                <div class="mt-2 text-sm text-slate-700">{{ $driver->notes ?: '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="xl:col-span-4 space-y-6">
                                    <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                                        <div class="border-b border-slate-100 px-6 py-5">
                                            <h4 class="text-xl font-bold text-slate-900">Personel Vitrin Resmi</h4>
                                            <p class="mt-1 text-sm text-slate-500">Seçilen personel resmi burada gösterilir</p>
                                        </div>

                                        <div class="p-6">
                                            @if($featuredImage)
                                                <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-slate-50">
                                                    <img src="{{ asset('storage/' . $featuredImage->file_path) }}"
                                                         alt="{{ $featuredImage->document_name }}"
                                                         class="h-[320px] w-full object-cover">
                                                </div>

                                                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                    <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Vitrin Resmi</div>
                                                    <div class="mt-2 text-sm font-semibold text-slate-900">{{ $featuredImage->document_name ?: 'Personel Resmi' }}</div>
                                                </div>
                                            @else
                                                <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                                                    <div class="text-4xl">🖼️</div>
                                                    <div class="mt-3 text-base font-semibold text-slate-700">Henüz personel resmi yok</div>
                                                    <div class="mt-1 text-sm text-slate-500">Resimler sekmesinden ilk resmi yükleyebilirsin.</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                                        <div class="border-b border-slate-100 px-6 py-5 bg-amber-50/60">
                                            <h4 class="text-xl font-bold text-slate-900">Belge Durumu Özeti</h4>
                                            <p class="mt-1 text-sm text-slate-500">Takip edilmesi gereken kritik belgeler</p>
                                        </div>

                                        <div class="space-y-3 p-6">
                                            @php
                                                $criticalDocs = $documentDocuments
                                                    ->filter(fn ($doc) => in_array($doc->alert_status, ['expired', 'expiring', 'active_soon'], true))
                                                    ->take(4);
                                            @endphp

                                            @if($criticalDocs->count())
                                                @foreach($criticalDocs as $document)
                                                    <div class="rounded-2xl border p-4 {{ $document->alert_status === 'expired' ? 'bg-rose-50 border-rose-200 text-rose-700' : ($document->alert_status === 'expiring' ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-orange-50 border-orange-200 text-orange-700') }}">
                                                        <div class="text-sm font-bold">{{ $document->document_type }}</div>
                                                        <div class="mt-1 text-xs font-medium">
                                                            Bitiş: {{ optional($document->end_date)->format('d.m.Y') ?: '-' }}
                                                        </div>
                                                        <div class="mt-1 text-xs font-semibold">
                                                            {{ $document->alert_text }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
                                                    Kritik belge uyarısı yok.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($activeTab === 'documents')
                            <div class="grid gap-6 xl:grid-cols-12">
                                <div class="xl:col-span-8 rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                                    <div class="border-b border-slate-100 px-6 py-5">
                                        <h4 class="text-xl font-bold text-slate-900">Belge ve Dökümanlar</h4>
                                        <p class="mt-1 text-sm text-slate-500">Tüm personel belgeleri tek sekmede yönetilir</p>
                                    </div>

                                    @if($documentDocuments->count())
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full text-sm">
                                                <thead class="bg-slate-50 text-slate-500">
                                                    <tr>
                                                        <th class="px-6 py-4 text-left font-bold">Belge Türü</th>
                                                        <th class="px-6 py-4 text-left font-bold">Başlangıç</th>
                                                        <th class="px-6 py-4 text-left font-bold">Bitiş</th>
                                                        <th class="px-6 py-4 text-left font-bold">Kalan Süre</th>
                                                        <th class="px-6 py-4 text-left font-bold">Dosya</th>
                                                        <th class="px-6 py-4 text-left font-bold">İşlem</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100">
                                                    @foreach($documentDocuments as $document)
                                                        <tr class="hover:bg-slate-50/70">
                                                            <td class="px-6 py-5">
                                                                <div class="font-semibold text-slate-900">{{ $document->document_type ?: '-' }}</div>
                                                                <div class="mt-1 text-xs text-slate-500">{{ $document->notes ?: '-' }}</div>
                                                            </td>
                                                            <td class="px-6 py-5">
                                                                {{ optional($document->start_date)->format('d.m.Y') ?: '-' }}
                                                            </td>
                                                            <td class="px-6 py-5">
                                                                {{ optional($document->end_date)->format('d.m.Y') ?: '-' }}
                                                            </td>
                                                            <td class="px-6 py-5">
                                                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $remainingBadgeClass($document) }}">
                                                                    {{ $document->alert_text }}
                                                                </span>
                                                            </td>
                                                            <td class="px-6 py-5">
                                                                @if($document->file_path)
                                                                    <a href="{{ asset('storage/' . $document->file_path) }}"
                                                                       target="_blank"
                                                                       class="text-indigo-600 font-semibold hover:text-indigo-800">
                                                                        Dosyayı Aç
                                                                    </a>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td class="px-6 py-5">
                                                                <form action="{{ route('drivers.documents.destroy', [$driver, $document]) }}" method="POST" onsubmit="return confirm('Bu belgeyi silmek istediğine emin misin?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                            class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-100">
                                                                        Sil
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="p-8 text-center text-sm text-slate-500">Henüz belge kaydı yok.</div>
                                    @endif
                                </div>

                                <div class="xl:col-span-4 rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                                    <div class="border-b border-slate-100 px-6 py-5">
                                        <h4 class="text-xl font-bold text-slate-900">Yeni Belge Yükle</h4>
                                        <p class="mt-1 text-sm text-slate-500">Belge süresi otomatik hesaplanır</p>
                                    </div>

                                    <form action="{{ route('drivers.documents.store', $driver) }}" method="POST" enctype="multipart/form-data" class="space-y-4 p-6" id="driverDocumentForm">
                                        @csrf
                                        <input type="hidden" name="redirect_tab" value="documents">
                                        <input type="hidden" id="driverBirthDate" value="{{ optional($driver->birth_date)->format('Y-m-d') }}">

                                        <div>
                                            <label class="mb-2 block text-sm font-semibold text-slate-700">Belge Türü</label>
                                            <select name="document_type" id="document_type"
                                                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                                                @foreach($driverDocumentTypes as $type)
                                                    <option value="{{ $type }}">{{ $type }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div>
                                                <label class="mb-2 block text-sm font-semibold text-slate-700">Başlangıç</label>
                                                <input type="date" name="start_date" id="start_date"
                                                       class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                                            </div>

                                            <div>
                                                <label class="mb-2 block text-sm font-semibold text-slate-700">Bitiş</label>
                                                <input type="date" name="end_date" id="end_date"
                                                       class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                                            </div>
                                        </div>

                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600" id="document_auto_info">
                                            Belge türüne göre bitiş tarihi otomatik hesaplanabilir.
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-semibold text-slate-700">Dosya</label>
                                            <input type="file" name="file"
                                                   class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-semibold text-slate-700">Not</label>
                                            <textarea name="notes" rows="3"
                                                      class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"></textarea>
                                        </div>

                                        <button type="submit"
                                                class="w-full rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-4 text-sm font-semibold text-white shadow transition hover:scale-[1.01]">
                                            Belgeyi Yükle
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif

                        @if($activeTab === 'payrolls')
                            <div class="grid gap-6 xl:grid-cols-12">
                                <div class="xl:col-span-8 rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                                    <div class="border-b border-slate-100 px-6 py-5">
                                        <h4 class="text-xl font-bold text-slate-900">Maaş Kayıtları</h4>
                                        <p class="mt-1 text-sm text-slate-500">Personelin bordro ve maaş geçmişi</p>
                                    </div>

                                    @if($driver->payrolls->count())
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full text-sm">
                                                <thead class="bg-slate-50 text-slate-500">
                                                    <tr>
                                                        <th class="px-6 py-4 text-left font-bold">Dönem</th>
                                                        <th class="px-6 py-4 text-left font-bold">Brüt Maaş</th>
                                                        <th class="px-6 py-4 text-left font-bold">Net Maaş</th>
                                                        <th class="px-6 py-4 text-left font-bold">Durum</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100">
                                                    @foreach($driver->payrolls as $payroll)
                                                        <tr class="hover:bg-slate-50/70">
                                                            <td class="px-6 py-5 font-semibold text-slate-900">
                                                                {{ $payroll->period_month ? \Carbon\Carbon::parse($payroll->period_month)->format('m.Y') : '-' }}
                                                            </td>
                                                            <td class="px-6 py-5">
                                                                {{ !is_null($payroll->gross_salary) ? number_format((float) $payroll->gross_salary, 2, ',', '.') . ' ₺' : '-' }}
                                                            </td>
                                                            <td class="px-6 py-5 font-semibold text-slate-900">
                                                                {{ !is_null($payroll->net_salary) ? number_format((float) $payroll->net_salary, 2, ',', '.') . ' ₺' : '-' }}
                                                            </td>
                                                            <td class="px-6 py-5">
                                                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                                                                    Kayıtlı
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="p-8 text-center text-sm text-slate-500">Henüz maaş kaydı yok.</div>
                                    @endif
                                </div>

                                <div class="xl:col-span-4 space-y-6">
                                    <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                                        <div class="border-b border-slate-100 px-6 py-5">
                                            <h4 class="text-xl font-bold text-slate-900">Maaş Özeti</h4>
                                            <p class="mt-1 text-sm text-slate-500">Toplam bordro görünümü</p>
                                        </div>

                                        <div class="space-y-4 p-6">
                                            <div class="rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 p-5 text-white">
                                                <div class="text-sm text-white/80">Toplam Net Maaş</div>
                                                <div class="mt-2 text-2xl font-extrabold">{{ number_format($totalNetSalary, 2, ',', '.') }} ₺</div>
                                            </div>

                                            <div class="rounded-2xl bg-gradient-to-r from-indigo-500 to-violet-500 p-5 text-white">
                                                <div class="text-sm text-white/80">Toplam Brüt Maaş</div>
                                                <div class="mt-2 text-2xl font-extrabold">{{ number_format($totalGrossSalary, 2, ',', '.') }} ₺</div>
                                            </div>

                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Son Bordro</div>
                                                <div class="mt-2 text-base font-bold text-slate-900">
                                                    {{ $lastPayroll?->period_month ? \Carbon\Carbon::parse($lastPayroll->period_month)->format('m.Y') : '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($activeTab === 'images')
                            <div class="grid gap-6 xl:grid-cols-12">
                                <div class="xl:col-span-8 rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                                    <div class="border-b border-slate-100 px-6 py-5">
                                        <h4 class="text-xl font-bold text-slate-900">Personel Resimleri</h4>
                                        <p class="mt-1 text-sm text-slate-500">Vesikalık, profil ve diğer görseller</p>
                                    </div>

                                    @if($imageDocuments->count())
                                        <div class="grid gap-5 p-6 md:grid-cols-2 xl:grid-cols-3">
                                            @foreach($imageDocuments as $image)
                                                <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm">
                                                    <div class="aspect-[4/3] bg-slate-100">
                                                        <img src="{{ asset('storage/' . $image->file_path) }}"
                                                             alt="{{ $image->document_name }}"
                                                             class="h-full w-full object-cover">
                                                    </div>

                                                    <div class="p-4">
                                                        <div class="text-sm font-semibold text-slate-900">{{ $image->document_name ?: 'Personel Resmi' }}</div>
                                                        <div class="mt-1 text-xs text-slate-500">{{ $image->document_type ?: '-' }}</div>

                                                        <div class="mt-3 flex flex-wrap items-center gap-2">
                                                            <a href="{{ asset('storage/' . $image->file_path) }}"
                                                               target="_blank"
                                                               class="rounded-xl bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                                                                Görüntüle
                                                            </a>

                                                            <form action="{{ route('drivers.documents.destroy', [$driver, $image]) }}" method="POST" onsubmit="return confirm('Bu resmi silmek istediğine emin misin?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                        class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-100">
                                                                    Sil
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="p-8 text-center text-sm text-slate-500">Henüz resim yüklenmemiş.</div>
                                    @endif
                                </div>

                                <div class="xl:col-span-4 rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                                    <div class="border-b border-slate-100 px-6 py-5">
                                        <h4 class="text-xl font-bold text-slate-900">Yeni Resim Yükle</h4>
                                        <p class="mt-1 text-sm text-slate-500">Personel resmi veya belge görseli ekleyin</p>
                                    </div>

                                    <form action="{{ route('drivers.documents.store', $driver) }}" method="POST" enctype="multipart/form-data" class="space-y-4 p-6">
                                        @csrf
                                        <input type="hidden" name="redirect_tab" value="images">

                                        <div>
                                            <label class="mb-2 block text-sm font-semibold text-slate-700">Belge Türü</label>
                                            <select name="document_type" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                                                <option value="Kimlik">Personel Resmi</option>
                                                <option value="Kimlik">Vesikalık</option>
                                                <option value="Kimlik">Profil Fotoğrafı</option>
                                                <option value="Kimlik">Diğer Resim</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-semibold text-slate-700">Resim Dosyası</label>
                                            <input type="file" name="file" accept=".jpg,.jpeg,.png,.webp"
                                                   class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-semibold text-slate-700">Not</label>
                                            <textarea name="notes" rows="3"
                                                      class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"></textarea>
                                        </div>

                                        <button type="submit"
                                                class="w-full rounded-2xl bg-gradient-to-r from-violet-500 to-fuchsia-500 px-5 py-4 text-sm font-semibold text-white shadow transition hover:scale-[1.01]">
                                            Resmi Yükle
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="xl:col-span-3"></div>
        </div>
    </div>

    <!-- İşten Ayrılma Modalı -->
    <div id="leaveModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-[32px] bg-white p-8 shadow-2xl">
            <h3 class="text-2xl font-black text-slate-900">🛑 İşten Ayrılma Kaydı</h3>
            <p class="mt-2 text-sm text-slate-500 font-medium">Şoförün ayrılış tarihini ve son vardiyasını seçin.</p>
            
            <form action="{{ route('drivers.leave-work', $driver) }}" method="POST" class="mt-6 space-y-5">
                @csrf
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-400">Ayrılma Tarihi</label>
                    <input type="date" name="leave_date" value="{{ date('Y-m-d') }}" required
                           class="w-full rounded-2xl border-slate-200 bg-slate-50 py-3 px-4 text-sm font-bold text-slate-900 focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-400">Son Yapılan Sefer</label>
                    <select name="leave_shift" required
                            class="w-full rounded-2xl border-slate-200 bg-slate-50 py-3 px-4 text-sm font-bold text-slate-900 focus:border-blue-500 focus:ring-blue-500">
                        <option value="morning">Sabah Seferini Yaptı Bıraktı</option>
                        <option value="evening">Akşam Seferini Yaptı Bıraktı</option>
                        <option value="full_day">Tüm Gün Seferlerini Yaptı Bıraktı</option>
                    </select>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeLeaveModal()" class="flex-1 rounded-2xl border border-slate-200 py-3 text-sm font-bold text-slate-600">Vazgeç</button>
                    <button type="submit" class="flex-1 rounded-2xl bg-rose-600 py-3 text-sm font-bold text-white shadow-lg shadow-rose-200">Kaydı Onayla</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Araç Değiştirme Modalı -->
    <div id="changeVehicleModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-[32px] bg-white p-8 shadow-2xl">
            <h3 class="text-2xl font-black text-slate-900">🚐 Araç Değiştir / Ata</h3>
            <p class="mt-2 text-sm text-slate-500 font-medium">Şoförü atamak istediğiniz aracı seçin.</p>
            
            <form action="{{ route('drivers.change-vehicle', $driver) }}" method="POST" class="mt-6 space-y-5">
                @csrf
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-400">Yeni Araç Seçin</label>
                    <select name="vehicle_id" required
                            class="w-full rounded-2xl border-slate-200 bg-slate-50 py-3 px-4 text-sm font-bold text-slate-900 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Araç Seçiniz...</option>
                        @foreach(\App\Models\Fleet\Vehicle::orderBy('plate')->get() as $v)
                            <option value="{{ $v->id }}" {{ $driver->vehicle_id == $v->id ? 'selected' : '' }}>
                                {{ $v->plate }} ({{ $v->brand }} {{ $v->model }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeChangeVehicleModal()" class="flex-1 rounded-2xl border border-slate-200 py-3 text-sm font-bold text-slate-600">Vazgeç</button>
                    <button type="submit" class="flex-1 rounded-2xl bg-blue-600 py-3 text-sm font-bold text-white shadow-lg shadow-blue-200">Atamayı Güncelle</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openLeaveModal() { document.getElementById('leaveModal').classList.replace('hidden', 'flex'); }
        function closeLeaveModal() { document.getElementById('leaveModal').classList.replace('flex', 'hidden'); }
        
        function openChangeVehicleModal() { document.getElementById('changeVehicleModal').classList.replace('hidden', 'flex'); }
        function closeChangeVehicleModal() { document.getElementById('changeVehicleModal').classList.replace('flex', 'hidden'); }

        document.addEventListener('DOMContentLoaded', function () {
            // ... mevcut script içeriği ...
            const typeEl = document.getElementById('document_type');
            const startEl = document.getElementById('start_date');
            const endEl = document.getElementById('end_date');
            const infoEl = document.getElementById('document_auto_info');
            const birthDateEl = document.getElementById('driverBirthDate');

            if (!typeEl || !startEl || !endEl || !infoEl) {
                return;
            }

            function addMonths(date, months) {
                const d = new Date(date);
                const day = d.getDate();
                d.setMonth(d.getMonth() + months);

                if (d.getDate() < day) {
                    d.setDate(0);
                }

                return d;
            }

            function addYears(date, years) {
                const d = new Date(date);
                d.setFullYear(d.getFullYear() + years);
                return d;
            }

            function formatDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function updateEndDate() {
                const type = typeEl.value;
                const startValue = startEl.value;
                const birthValue = birthDateEl ? birthDateEl.value : '';

                infoEl.className = 'rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600';
                infoEl.textContent = 'Belge türüne göre bitiş tarihi otomatik hesaplanabilir.';

                if (type === 'SRC 1 Belgesi' || type === 'SRC 2 Belgesi') {
                    if (birthValue) {
                        const birthDate = new Date(birthValue);
                        const srcEnd = addYears(birthDate, 69);
                        endEl.value = formatDate(srcEnd);
                        endEl.readOnly = true;
                        infoEl.className = 'rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-xs text-indigo-700';
                        infoEl.textContent = 'SRC belgesi bitiş tarihi, personelin doğum tarihine göre 69 yaş kuralıyla otomatik hesaplandı.';
                    } else {
                        endEl.value = '';
                        endEl.readOnly = false;
                        infoEl.className = 'rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700';
                        infoEl.textContent = 'SRC bitiş tarihi için personelin doğum tarihi gerekli.';
                    }
                    return;
                }

                if ((type === 'Psikoteknik Belgesi' || type === 'MYK Belgesi') && startValue) {
                    const startDate = new Date(startValue);
                    const endDate = addYears(startDate, 5);
                    endEl.value = formatDate(endDate);
                    endEl.readOnly = true;
                    infoEl.className = 'rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs text-emerald-700';
                    infoEl.textContent = 'Bu belge için bitiş tarihi başlangıç tarihinden 5 yıl sonrası olarak otomatik hesaplandı.';
                    return;
                }

                if (type === 'Adli Sicil Kaydı' && startValue) {
                    const startDate = new Date(startValue);
                    const endDate = addMonths(startDate, 6);
                    endEl.value = formatDate(endDate);
                    endEl.readOnly = true;
                    infoEl.className = 'rounded-2xl border border-orange-200 bg-orange-50 px-4 py-3 text-xs text-orange-700';
                    infoEl.textContent = 'Adli Sicil Kaydı için bitiş tarihi başlangıç tarihinden 6 ay sonrası olarak otomatik hesaplandı.';
                    return;
                }

                endEl.readOnly = false;
            }

            typeEl.addEventListener('change', updateEndDate);
            startEl.addEventListener('change', updateEndDate);

            updateEndDate();
        });
    </script>
@endsection