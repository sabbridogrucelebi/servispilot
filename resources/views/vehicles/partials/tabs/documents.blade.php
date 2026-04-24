<div class="space-y-6" x-data="{ 
    archiveOpen: false,
    docType: '{{ old('document_type') }}',
    docName: '{{ old('document_name') }}',
    docIssuer: '{{ old('issuer_name') }}',
    handleTypeChange() {
        if (this.docType === 'Ruhsat') {
            this.docName = 'RUHSAT BELGESİ';
        } else if (this.docType === 'Muayene') {
            this.docName = 'MUAYENE RAPORU';
            this.docIssuer = 'TÜVTÜRK';
        } else if (this.docType === 'Sigorta') {
            this.docName = 'SİGORTA POLİÇESİ';
        } else if (this.docType === 'Kasko') {
            this.docName = 'KASKO POLİÇESİ';
        } else if (this.docType === 'Egzoz') {
            this.docName = 'EGZOZ MUAYENE RAPORU';
            this.docIssuer = 'ÇEV.ŞEH.BAKANLIĞI';
        } else if (this.docType === 'Koltuk Sigortası') {
            this.docName = 'KOLTUK SİGORTASI';
        } else if (this.docType === 'İMM Poliçesi') {
            this.docName = 'İMM POLİÇESİ';
        }
    }
}">

    <div class="rounded-[30px] border border-slate-200/70 bg-white/95 backdrop-blur shadow-[0_18px_45px_rgba(15,23,42,0.07)] overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-5 bg-gradient-to-r from-violet-50 via-white to-slate-50">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Araç Belgeleri</h3>
                    <p class="mt-1 text-sm text-slate-500">Aktif belgeler, kalan süre ve toplu indirme alanı</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        @click="archiveOpen = !archiveOpen"
                        class="inline-flex items-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-700 transition"
                    >
                        Arşiv Belgeler
                    </button>

                    <a href="{{ route('vehicles.documents.zip', $vehicle) }}"
                       class="inline-flex items-center rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 transition">
                        Toplu İndir
                    </a>

                    <button type="button"
                            id="openDocumentModal"
                            class="inline-flex items-center rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow hover:scale-[1.01] transition">
                        + Yeni Belge Yükle
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-hidden">
            @if($activeVehicleDocuments->count())
                <table class="w-full table-fixed">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="w-[30%] px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Belge Adı</th>
                            <th class="w-[22%] px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Belge Türü</th>
                            <th class="w-[12%] px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Başlangıç</th>
                            <th class="w-[12%] px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Bitiş</th>
                            <th class="w-[10%] px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Kalan Süre</th>
                            <th class="w-[14%] px-4 py-4 text-right text-xs font-bold uppercase tracking-[0.12em] text-slate-500">İşlemler</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($activeVehicleDocuments as $document)
                            @php
                                $badge = $remainingBadge($document->end_date);
                            @endphp

                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-4 py-4 align-top">
                                    <div class="text-sm font-semibold text-slate-800 break-words">
                                        {{ $document->document_name ?: '-' }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500 break-words">
                                        {{ $document->notes ?: 'Not yok' }}
                                    </div>
                                </td>

                                <td class="px-4 py-4 align-top text-sm text-slate-600 break-words">
                                    {{ $document->document_type ?: '-' }}
                                </td>

                                <td class="px-4 py-4 align-top text-sm text-slate-600">
                                    {{ optional($document->start_date)->format('d.m.Y') ?: '-' }}
                                </td>

                                <td class="px-4 py-4 align-top text-sm text-slate-600">
                                    {{ optional($document->end_date)->format('d.m.Y') ?: '-' }}
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badge['class'] }}">
                                        {{ $badge['text'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @if(!empty($document->file_path))
                                            <a href="{{ asset('storage/' . $document->file_path) }}"
                                               target="_blank"
                                               class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition">
                                                Görüntüle
                                            </a>
                                        @endif

                                        <form action="{{ route('vehicles.documents.destroy', [$vehicle, $document]) }}"
                                              method="POST"
                                              class="relative z-50"
                                              onsubmit="return confirm('Bu belgeyi silmek istediğine emin misin?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition">
                                                Sil
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-6">
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-500">
                        Aktif belge bulunmuyor. Sağ üstteki “Yeni Belge Yükle” butonundan ekleyebilirsin.
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div
        x-show="archiveOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="rounded-[30px] border border-slate-200/70 bg-white/95 backdrop-blur shadow-[0_18px_45px_rgba(15,23,42,0.07)] overflow-hidden"
        style="display: none;"
    >
        <div class="border-b border-slate-100 px-6 py-5 bg-gradient-to-r from-slate-100 via-white to-slate-50">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Arşiv Belgeler</h3>
                    <p class="mt-1 text-sm text-slate-500">Süresi geçen veya yeni versiyon geldiği için arşive alınan belgeler</p>
                </div>

                <button
                    type="button"
                    @click="archiveOpen = false"
                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition"
                >
                    Kapat
                </button>
            </div>
        </div>

        <div class="overflow-x-hidden">
            @if($archivedVehicleDocuments->count())
                <table class="w-full table-fixed">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="w-[32%] px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Belge Adı</th>
                            <th class="w-[26%] px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Belge Türü</th>
                            <th class="w-[14%] px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Bitiş</th>
                            <th class="w-[12%] px-4 py-4 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Durum</th>
                            <th class="w-[16%] px-4 py-4 text-right text-xs font-bold uppercase tracking-[0.12em] text-slate-500">İşlemler</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($archivedVehicleDocuments as $document)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-4 py-4 align-top text-sm font-semibold text-slate-800 break-words">
                                    {{ $document->document_name ?: '-' }}
                                </td>

                                <td class="px-4 py-4 align-top text-sm text-slate-600 break-words">
                                    {{ $document->document_type ?: '-' }}
                                </td>

                                <td class="px-4 py-4 align-top text-sm text-slate-600">
                                    {{ optional($document->end_date)->format('d.m.Y') ?: '-' }}
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                        Arşivde
                                    </span>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @if(!empty($document->file_path))
                                            <a href="{{ asset('storage/' . $document->file_path) }}"
                                               target="_blank"
                                               class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition">
                                                Görüntüle
                                            </a>
                                        @endif

                                        <form action="{{ route('vehicles.documents.destroy', [$vehicle, $document]) }}"
                                              method="POST"
                                              class="relative z-50"
                                              onsubmit="return confirm('Arşivdeki bu belgeyi kalıcı olarak silmek istediğine emin misin?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition">
                                                Sil
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-6">
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-500">
                        Arşiv belgesi bulunmuyor.
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div id="documentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
        <div class="w-full max-w-2xl rounded-[30px] bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 bg-gradient-to-r from-blue-50 via-white to-slate-50">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Yeni Belge Yükle</h3>
                    <p class="mt-1 text-sm text-slate-500">Bu araca ait yeni belge ekleyin</p>
                </div>

                <button type="button"
                        id="closeDocumentModal"
                        class="rounded-xl bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200 transition">
                    Kapat
                </button>
            </div>

            <form action="{{ route('vehicles.documents.store', $vehicle) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Belge Adı</label>
                    <input type="text"
                           name="document_name"
                           x-model="docName"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-800 shadow-sm focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 outline-none transition-colors"
                           placeholder="Örn: Ruhsat Ön Yüz">
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Belge Türü</label>
                        <select name="document_type"
                                x-model="docType"
                                @change="handleTypeChange()"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-800 shadow-sm focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 outline-none transition-colors">
                            <option value="">Belge türünü seçin</option>
                            <option value="Ruhsat">Ruhsat</option>
                            <option value="Muayene">Muayene</option>
                            <option value="Sigorta">Sigorta</option>
                            <option value="Kasko">Kasko</option>
                            <option value="Egzoz">Egzoz</option>
                            <option value="Koltuk Sigortası">Koltuk Sigortası</option>
                            <option value="İMM Poliçesi">İMM Poliçesi</option>
                            <option value="Vergi / Harç">Vergi / Harç</option>
                            <option value="Diğer">Diğer</option>
                        </select>
                    </div>

                    {{-- Gizli: Belge Veren Kurum otomatik atanır --}}
                    <input type="hidden" name="issuer_name" x-model="docIssuer">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Başlangıç Tarihi</label>
                        <input type="date"
                               name="start_date"
                               value="{{ old('start_date') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-800 shadow-sm focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Bitiş Tarihi</label>
                        <input type="date"
                               name="end_date"
                               value="{{ old('end_date') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-800 shadow-sm focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Belge Dosyası</label>
                    <input type="file"
                           name="file"
                           accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Not</label>
                    <textarea name="notes"
                              rows="4"
                              class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-800 shadow-sm focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 outline-none"
                              placeholder="Belge ile ilgili açıklama...">{{ old('notes') }}</textarea>
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button"
                            id="closeDocumentModalFooter"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Vazgeç
                    </button>

                    <button type="submit"
                            class="rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow hover:scale-[1.01] transition">
                        Belgeyi Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>