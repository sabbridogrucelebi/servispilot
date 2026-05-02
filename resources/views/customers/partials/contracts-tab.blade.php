@php
    $yearOptions = range(now()->year + 5, 2020);
@endphp

<div class="grid grid-cols-1 gap-6 2xl:grid-cols-12">

    <div class="2xl:col-span-4 overflow-hidden rounded-[30px] border border-slate-200/70 bg-white shadow-lg shadow-slate-200/40">
        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-lg font-bold text-slate-900">Yeni Sözleşme Yükle</h3>
            <p class="mt-1 text-sm text-slate-500">Tarattığınız sözleşmeyi yıl bilgisiyle birlikte ekleyin</p>
        </div>

        <form action="{{ route('customers.contracts.store', $customer) }}" method="POST" enctype="multipart/form-data" class="space-y-5 px-6 py-6">
            @csrf

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Yıl</label>
                <select name="year"
                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Yıl seçiniz</option>
                    @foreach($yearOptions as $year)
                        <option value="{{ $year }}" {{ old('year') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Başlangıç Tarihi</label>
                <input type="date"
                       name="start_date"
                       value="{{ old('start_date') }}"
                       class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Bitiş Tarihi</label>
                <input type="date"
                       name="end_date"
                       value="{{ old('end_date') }}"
                       class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Dosya Yükle</label>
                <input type="file"
                       name="contract_file"
                       accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm file:mr-4 file:rounded-xl file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                <p class="mt-2 text-xs text-slate-500">PDF, JPG, JPEG veya PNG yükleyebilirsiniz.</p>
            </div>

            <button type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:bg-slate-800">
                <span>📄</span>
                <span>Kaydet</span>
            </button>
        </form>
    </div>

    <div class="2xl:col-span-8 overflow-hidden rounded-[30px] border border-slate-200/70 bg-white shadow-lg shadow-slate-200/40">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Sözleşme Listesi</h3>
                <p class="mt-1 text-sm text-slate-500">En güncel ve geçerli sözleşme en üstte görünür</p>
            </div>

            <div class="text-sm font-medium text-slate-400">
                Toplam {{ $contracts->count() }} kayıt
            </div>
        </div>

        <div class="p-6">
            @if($contracts->count())
                <div class="space-y-4">
                    @foreach($contracts as $contract)
                        @php
                            $isActive = $contract->is_active;
                            $statusClass = $isActive
                                ? 'bg-emerald-100 text-emerald-700'
                                : 'bg-rose-100 text-rose-700';

                            $statusText = $isActive ? 'Geçerli Sözleşme' : 'Süresi Doldu';
                        @endphp

                        <div class="rounded-[26px] border border-slate-200 bg-slate-50/70 p-5 shadow-sm">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-500 text-2xl text-white shadow">
                                        📑
                                    </div>

                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="text-lg font-bold text-slate-900">
                                                {{ $contract->year }} Sözleşmesi
                                            </div>

                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                                {{ $statusText }}
                                            </span>

                                            @if($loop->first)
                                                <span class="inline-flex rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">
                                                    En Güncel
                                                </span>
                                            @endif
                                        </div>

                                        <div class="mt-3 grid grid-cols-1 gap-3 text-sm text-slate-600 md:grid-cols-3">
                                            <div>
                                                <span class="font-semibold text-slate-800">Başlangıç:</span>
                                                {{ $contract->start_date?->format('d.m.Y') }}
                                            </div>
                                            <div>
                                                <span class="font-semibold text-slate-800">Bitiş:</span>
                                                {{ $contract->end_date?->format('d.m.Y') }}
                                            </div>
                                            <div>
                                                <span class="font-semibold text-slate-800">Dosya:</span>
                                                {{ $contract->original_name ?: 'Sözleşme dosyası' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ asset('storage/' . $contract->file_path) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-2 rounded-2xl bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                        <span>👁️</span>
                                        <span>Görüntüle</span>
                                    </a>

                                    <form action="{{ route('customers.contracts.destroy', [$customer, $contract->id]) }}" method="POST" onsubmit="return confirm('Bu sözleşmeyi silmek istediğine emin misin?')">
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
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-6 py-14 text-center">
                    <div class="mx-auto max-w-md">
                        <div class="mb-3 text-5xl">📄</div>
                        <div class="text-base font-semibold text-slate-700">Henüz sözleşme kaydı yok</div>
                        <div class="mt-1 text-sm text-slate-500">
                            İlk sözleşmeyi yükleyerek müşteri sözleşme arşivini oluşturabilirsiniz.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>
