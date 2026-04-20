@php
    $imageTypeLabels = [
        'front' => 'Araç Ön Resmi',
        'right_side' => 'Sağ Yan',
        'left_side' => 'Sol Yan',
        'rear' => 'Arka',
        'interior_1' => 'İç Resim 1',
        'interior_2' => 'İç Resim 2',
        'dashboard' => 'Göğüs',
        'other' => 'Diğer Resimler',
    ];
@endphp

<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

    <div class="xl:col-span-8 rounded-[30px] border border-slate-200/60 bg-white/90 backdrop-blur shadow-xl overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-5 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Araç Resimleri</h3>
                <p class="mt-1 text-sm text-slate-500">Araç görselleri, vitrin resmi ve şoför linki ile gelen yüklemeler</p>
            </div>
        </div>

        @if($vehicleImages->count())
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 p-6">
                @foreach($vehicleImages as $image)
                    @php
                        $fileUrl = asset('storage/' . $image->file_path);
                        $downloadName = ($vehicle->plate ?? 'arac') . '_' . ($image->title ?: 'gorsel') . '.' . pathinfo($image->file_path, PATHINFO_EXTENSION);
                        $downloadName = str_replace(['/', '\\', ' '], ['-', '-', '_'], $downloadName);
                    @endphp

                    <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm">
                        <div class="aspect-[4/3] bg-slate-100">
                            <img src="{{ $fileUrl }}"
                                 class="h-full w-full object-cover"
                                 alt="{{ $image->title ?: 'Araç resmi' }}">
                        </div>

                        <div class="p-4">
                            <div class="text-sm font-semibold text-slate-800">
                                {{ $image->image_type_label }}
                            </div>

                            <div class="mt-1 text-xs text-slate-500">
                                {{ $image->title ?: 'Araç Görseli' }}
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-600">
                                    Kaynak: {{ $image->upload_source_label }}
                                </span>

                                @if($image->is_featured)
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold text-emerald-700">
                                        Vitrin
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                @if(!$image->is_featured)
                                    <form action="{{ route('vehicles.images.featured', [$vehicle, $image]) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition">
                                            Vitrin Yap
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ $fileUrl }}"
                                   download="{{ $downloadName }}"
                                   class="rounded-xl bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition">
                                    İndir
                                </a>

                                <a href="{{ $fileUrl }}"
                                   target="_blank"
                                   class="rounded-xl bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                                    Görüntüle
                                </a>

                                <form action="{{ route('vehicles.images.destroy', [$vehicle, $image]) }}" method="POST" onsubmit="return confirm('Bu resmi silmek istediğine emin misin?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition">
                                        Sil
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6">
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                    <div class="text-4xl mb-3">🖼️</div>
                    <div class="text-base font-semibold text-slate-700">Henüz resim yüklenmemiş</div>
                    <div class="mt-1 text-sm text-slate-500">İlk araç resmini sağdaki formdan yükleyebilirsin.</div>
                </div>
            </div>
        @endif
    </div>

    <div class="xl:col-span-4 space-y-6">
        <div class="rounded-[30px] border border-slate-200/60 bg-white/90 backdrop-blur shadow-xl overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-5">
                <h3 class="text-lg font-bold text-slate-800">Şoför İçin Hızlı Yükleme Linki</h3>
                <p class="mt-1 text-sm text-slate-500">Bu linki şoföre gönder, telefondan kamerayla resim yüklesin</p>
            </div>

            <div class="p-6 space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.10em] text-slate-400">Paylaşılacak Link</div>
                    <div class="mt-2 break-all text-sm font-medium text-slate-700">
                        {{ $publicImageUploadUrl }}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button"
                            onclick="navigator.clipboard.writeText('{{ $publicImageUploadUrl }}'); this.innerText='Kopyalandı'; setTimeout(() => this.innerText='Linki Kopyala', 1500);"
                            class="rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                        Linki Kopyala
                    </button>

                    <a href="{{ $publicImageUploadUrl }}"
                       target="_blank"
                       class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Önizle
                    </a>
                </div>

                <div class="rounded-2xl bg-amber-50 p-4 text-xs leading-6 text-amber-700">
                    Şoför bu linke telefondan girer. Ön, sağ yan, sol yan, arka, iç resimler ve göğüs fotoğrafını kameradan çekip yükleyebilir.
                </div>
            </div>
        </div>

        <div class="rounded-[30px] border border-slate-200/60 bg-white/90 backdrop-blur shadow-xl overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-5">
                <h3 class="text-lg font-bold text-slate-800">Yeni Resim Yükle</h3>
                <p class="mt-1 text-sm text-slate-500">Panelden manuel yeni görsel ekleyin</p>
            </div>

            <form action="{{ route('vehicles.images.store', $vehicle) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Resim Tipi</label>
                    <select name="image_type"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-800 shadow-sm focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 outline-none">
                        @foreach($imageTypeLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Resim Başlığı</label>
                    <input type="text"
                           name="title"
                           value="{{ old('title') }}"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-800 shadow-sm focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 outline-none"
                           placeholder="Örn: Ön görünüm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Resim Dosyası</label>
                    <input type="file"
                           name="image"
                           accept=".jpg,.jpeg,.png,.webp"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 outline-none">
                </div>

                <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 w-full cursor-pointer">
                    <input type="checkbox"
                           name="is_featured"
                           value="1"
                           class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <div class="text-sm font-semibold text-slate-800">Vitrin resmi olarak ayarla</div>
                        <div class="text-xs text-slate-500">Ana detay ekranında bu görsel gösterilsin</div>
                    </div>
                </label>

                <button type="submit"
                        class="w-full rounded-2xl bg-gradient-to-r from-violet-500 to-fuchsia-500 px-4 py-3 text-sm font-semibold text-white shadow hover:scale-[1.01] transition">
                    Resim Yükle
                </button>

                <div class="rounded-2xl bg-slate-50 p-4 text-xs leading-6 text-slate-500">
                    Desteklenen formatlar: JPG, JPEG, PNG, WEBP. Maksimum dosya boyutu: 4 MB.
                </div>
            </form>
        </div>
    </div>

</div>