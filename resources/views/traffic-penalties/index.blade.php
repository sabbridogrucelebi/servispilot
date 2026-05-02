@extends('layouts.app')

@section('title', 'Trafik Cezaları')
@section('subtitle', 'Araçlara ait trafik cezası kayıtlarını yönetin')

@section('content')
    <div class="space-y-6">

        @if(session('success'))
            <div class="rounded-[24px] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-[28px] bg-gradient-to-r from-rose-500 to-pink-500 p-6 text-white shadow-lg">
                <div class="text-sm font-medium text-white/80">Toplam Ceza Kaydı</div>
                <div class="mt-3 text-4xl font-extrabold">{{ $totalCount }}</div>
                <div class="mt-2 text-sm text-white/80">Sistemde kayıtlı toplam trafik cezası</div>
            </div>

            <div class="rounded-[28px] bg-gradient-to-r from-amber-400 to-orange-500 p-6 text-white shadow-lg">
                <div class="text-sm font-medium text-white/80">Ödenmemiş Ceza</div>
                <div class="mt-3 text-4xl font-extrabold">{{ $unpaidCount }}</div>
                <div class="mt-2 text-sm text-white/80">Henüz kapatılmamış kayıt</div>
            </div>

            <div class="rounded-[28px] bg-gradient-to-r from-indigo-500 to-violet-500 p-6 text-white shadow-lg">
                <div class="text-sm font-medium text-white/80">Toplam Ceza Tutarı</div>
                <div class="mt-3 text-4xl font-extrabold">{{ number_format($totalAmount, 2, ',', '.') }} ₺</div>
                <div class="mt-2 text-sm text-white/80">Tüm kayıtların toplam maliyeti</div>
            </div>

            <div class="rounded-[28px] bg-gradient-to-r from-emerald-500 to-teal-500 p-6 text-white shadow-lg">
                <div class="text-sm font-medium text-white/80">Tahsil Edilebilir</div>
                <div class="mt-3 text-4xl font-extrabold">{{ number_format($collectableAmount, 2, ',', '.') }} ₺</div>
                <div class="mt-2 text-sm text-white/80">Bugün tahsil edilebilecek tutar</div>
            </div>

            <div class="rounded-[28px] bg-gradient-to-r from-sky-500 to-cyan-500 p-6 text-white shadow-lg">
                <div class="text-sm font-medium text-white/80">Bu Ay Kesilen</div>
                <div class="mt-3 text-4xl font-extrabold">{{ $thisMonthCount }}</div>
                <div class="mt-2 text-sm text-white/80">Bu ay açılan ceza kaydı</div>
            </div>
        </div>

        @if($criticalCount > 0)
            <div class="rounded-[24px] border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-700">
                Dikkat: İndirim süresi 3 gün ve altında kalan {{ $criticalCount }} adet ceza var.
            </div>
        @endif

        <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Filtreler</h3>
                    <p class="mt-1 text-xs text-slate-500">Listeyi filtreleyebilir veya dışa aktarabilirsin.</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('traffic-penalties.export.excel', request()->query()) }}"
                       class="rounded-xl bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                        Excel İndir
                    </a>

                    <a href="{{ route('traffic-penalties.export.pdf', request()->query()) }}"
                       class="rounded-xl bg-rose-50 px-4 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                        PDF İndir
                    </a>

                    <button type="button"
                            id="toggleFilterBtn"
                            onclick="toggleFilters()"
                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
                        <span id="toggleFilterIcon">⌃</span>
                    </button>
                </div>
            </div>

            <div id="filterBox" class="p-6">
                <form method="GET" action="{{ route('traffic-penalties.index') }}" class="grid gap-4 lg:grid-cols-6">
                    <div class="lg:col-span-2">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Arama</label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Ceza no, şoför, madde, yer..."
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
                        <select name="payment_status"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            <option value="">Tüm Durumlar</option>
                            <option value="unpaid" @selected(request('payment_status') === 'unpaid')>Ödenmedi</option>
                            <option value="paid" @selected(request('payment_status') === 'paid')>Ödendi</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-500">İndirim</label>
                        <select name="discount_status"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            <option value="">Tüm Durumlar</option>
                            <option value="active" @selected(request('discount_status') === 'active')>İndirim aktif</option>
                            <option value="critical" @selected(request('discount_status') === 'critical')>3 gün altı kritik</option>
                            <option value="expired" @selected(request('discount_status') === 'expired')>Süresi dolmuş</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Başlangıç</label>
                        <input type="date"
                               name="date_from"
                               value="{{ request('date_from') }}"
                               class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Bitiş</label>
                        <input type="date"
                               name="date_to"
                               value="{{ request('date_to') }}"
                               class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    </div>

                    <div class="lg:col-span-6 flex flex-wrap items-end gap-3">
                        <button type="submit"
                                class="rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-3 text-sm font-semibold text-white shadow transition hover:scale-[1.01]">
                            Filtrele
                        </button>

                        <a href="{{ route('traffic-penalties.index') }}"
                           class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Temizle
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Trafik Cezası Kayıtları</h3>
                    <p class="mt-1 text-sm text-slate-500">İndirim süresi, risk seviyesi ve ödeme durumları otomatik hesaplanır.</p>
                </div>

                @if(auth()->user()->hasPermission('penalties.create'))
                <a href="{{ route('traffic-penalties.create') }}"
                   class="rounded-2xl bg-gradient-to-r from-rose-600 to-pink-600 px-5 py-3 text-sm font-semibold text-white shadow transition hover:scale-[1.01]">
                    + Yeni Ceza Ekle
                </a>
                @endif
            </div>

            @if($trafficPenalties->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-6 py-4 text-left font-bold">Ceza No</th>
                                <th class="px-6 py-4 text-left font-bold">Araç / Şoför</th>
                                <th class="px-6 py-4 text-left font-bold">Tarih</th>
                                <th class="px-6 py-4 text-left font-bold">Madde / Yer</th>
                                <th class="px-6 py-4 text-left font-bold">Ceza</th>
                                <th class="px-6 py-4 text-left font-bold">İndirimli</th>
                                <th class="px-6 py-4 text-left font-bold">Sayaç</th>
                                <th class="px-6 py-4 text-left font-bold">Ödeme</th>
                                <th class="px-6 py-4 text-left font-bold">Belgeler</th>
                                <th class="px-6 py-4 text-left font-bold">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($trafficPenalties as $penalty)
                                @php
                                    $rowClass = '';
                                    if ($penalty->payment_status === 'unpaid') {
                                        if ($penalty->remaining_days_for_discount <= 0) {
                                            $rowClass = 'bg-rose-50/80';
                                        } elseif ($penalty->remaining_days_for_discount <= 3) {
                                            $rowClass = 'bg-amber-50/80';
                                        }
                                    }
                                @endphp

                                <tr class="hover:bg-slate-50/60 {{ $rowClass }}">
                                    <td class="px-6 py-5">
                                        <div class="font-bold text-slate-900">{{ $penalty->penalty_no }}</div>
                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ optional($penalty->penalty_date)->format('d.m.Y') }}
                                            @if($penalty->penalty_time)
                                                • {{ $penalty->penalty_time }}
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="font-semibold text-slate-900">
                                            {{ optional($penalty->vehicle)->plate ?? 'Araç seçilmedi' }}
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $penalty->driver_name }}</div>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="font-semibold text-slate-900">
                                            {{ optional($penalty->penalty_date)->format('d.m.Y') }}
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">
                                            Son indirim: {{ optional($penalty->discount_deadline)->format('d.m.Y') }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="font-semibold text-slate-900">{{ $penalty->penalty_article }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $penalty->penalty_location }}</div>
                                    </td>

                                    <td class="px-6 py-5 font-bold text-slate-900">
                                        {{ number_format($penalty->penalty_amount, 2, ',', '.') }} ₺
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="font-bold text-emerald-600">
                                            {{ number_format($penalty->discounted_amount, 2, ',', '.') }} ₺
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">%25 indirimli tutar</div>
                                    </td>

                                    <td class="px-6 py-5">
                                        @if($penalty->payment_status === 'paid')
                                            <div class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                                Kapatıldı
                                            </div>
                                        @else
                                            @if($penalty->remaining_days_for_discount > 3)
                                                <div class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                                                    {{ $penalty->remaining_days_for_discount }} gün kaldı
                                                </div>
                                            @elseif($penalty->remaining_days_for_discount > 0)
                                                <div class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                                                    {{ $penalty->remaining_days_for_discount }} gün kaldı
                                                </div>
                                            @else
                                                <div class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700">
                                                    Süre doldu
                                                </div>
                                            @endif
                                        @endif
                                    </td>

                                    <td class="px-6 py-5">
                                        @if($penalty->payment_status === 'paid')
                                            <div class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                                                Ödendi
                                            </div>
                                            <div class="mt-2 text-xs text-slate-500">
                                                Ödeme tarihi: {{ optional($penalty->payment_date)->format('d.m.Y') }}
                                            </div>
                                            <div class="mt-1 text-sm font-bold text-slate-900">
                                                {{ number_format($penalty->paid_amount, 2, ',', '.') }} ₺
                                            </div>

                                            @if($penalty->is_discount_eligible)
                                                <div class="mt-1 text-xs font-semibold text-emerald-600">
                                                    %25 indirim uygulandı
                                                </div>
                                            @else
                                                <div class="mt-1 text-xs font-semibold text-rose-600">
                                                    İndirimsiz ödeme
                                                </div>
                                            @endif
                                        @else
                                            <div class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                                                Bekliyor
                                            </div>

                                            <div class="mt-2 text-xs text-slate-500">Bugün ödenirse:</div>
                                            <div class="mt-1 text-sm font-bold {{ $penalty->remaining_days_for_discount > 0 ? 'text-emerald-600' : 'text-slate-900' }}">
                                                {{ number_format($penalty->calculated_payable_amount, 2, ',', '.') }} ₺
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="flex flex-col gap-2">
                                            @if($penalty->traffic_penalty_document)
                                                <a href="{{ asset('storage/' . $penalty->traffic_penalty_document) }}"
                                                   target="_blank"
                                                   class="inline-flex items-center gap-2 text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                                    <span>📄</span>
                                                    <span>Ceza Belgesi</span>
                                                </a>
                                            @else
                                                <span class="inline-flex items-center gap-2 text-xs text-slate-400">
                                                    <span>📄</span>
                                                    <span>Belge yok</span>
                                                </span>
                                            @endif

                                            @if($penalty->payment_receipt)
                                                <a href="{{ asset('storage/' . $penalty->payment_receipt) }}"
                                                   target="_blank"
                                                   class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-600 hover:text-emerald-800">
                                                    <span>🧾</span>
                                                    <span>Ödeme Dekontu</span>
                                                </a>
                                            @else
                                                <span class="inline-flex items-center gap-2 text-xs text-slate-400">
                                                    <span>🧾</span>
                                                    <span>Dekont yok</span>
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if($penalty->payment_status === 'unpaid' && auth()->user()->hasPermission('penalties.edit'))
                                                <button type="button"
                                                        onclick="openQuickPayModal(
                                                            '{{ $penalty->id }}',
                                                            '{{ $penalty->penalty_no }}',
                                                            '{{ number_format($penalty->calculated_payable_amount, 2, ',', '.') }} ₺',
                                                            '{{ $penalty->remaining_days_for_discount }}'
                                                        )"
                                                        class="rounded-xl bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                                                    Hızlı Öde
                                                </button>
                                            @endif

                                            @if(auth()->user()->hasPermission('penalties.edit'))
                                            <a href="{{ route('traffic-penalties.edit', $penalty) }}"
                                               class="rounded-xl bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-600 hover:bg-indigo-100">
                                                Düzenle
                                            </a>
                                            @endif

                                            @if(auth()->user()->hasPermission('penalties.delete'))
                                            <form action="{{ route('traffic-penalties.destroy', $penalty) }}" method="POST" onsubmit="return confirm('Bu kaydı silmek istediğine emin misin?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-100">
                                                    Sil
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $trafficPenalties->links() }}
                </div>
            @else
                <div class="p-10 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-rose-100 text-3xl">
                        🚨
                    </div>
                    <h4 class="mt-4 text-lg font-bold text-slate-900">Henüz trafik cezası kaydı yok</h4>
                    <p class="mt-2 text-sm text-slate-500">İlk kaydı oluşturarak başlayabilirsin.</p>
                </div>
            @endif
        </div>
    </div>

    <div id="quickPayModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-900/50 p-4">
        <div class="w-full max-w-lg overflow-hidden rounded-[28px] bg-white shadow-2xl">
            <div class="border-b border-slate-100 px-6 py-5">
                <h3 class="text-xl font-bold text-slate-900">Hızlı Ödeme</h3>
                <p class="mt-1 text-sm text-slate-500">Ceza kaydını tek adımda kapat.</p>
            </div>

            <form id="quickPayForm" method="POST" enctype="multipart/form-data" class="space-y-5 p-6">
                @csrf

                <div class="rounded-2xl bg-slate-50 p-4">
                    <div class="text-sm text-slate-500">Ceza No</div>
                    <div id="quickPayPenaltyNo" class="mt-1 text-lg font-bold text-slate-900">-</div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl bg-emerald-50 p-4">
                        <div class="text-sm text-emerald-600">Uygulanacak Tutar</div>
                        <div id="quickPayAmount" class="mt-1 text-xl font-extrabold text-emerald-700">0,00 ₺</div>
                    </div>

                    <div class="rounded-2xl bg-amber-50 p-4">
                        <div class="text-sm text-amber-600">İndirim Durumu</div>
                        <div id="quickPayDiscountInfo" class="mt-1 text-sm font-bold text-amber-700">-</div>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Ödeme Tarihi</label>
                    <input type="date"
                           name="payment_date"
                           value="{{ now()->format('Y-m-d') }}"
                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                           required>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Ödeme Dekontu</label>
                    <input type="file"
                           name="payment_receipt"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm">
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button"
                            onclick="closeQuickPayModal()"
                            class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Vazgeç
                    </button>

                    <button type="submit"
                            class="rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow transition hover:bg-emerald-700">
                        Ödemeyi Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openQuickPayModal(id, penaltyNo, amount, remainingDays) {
            const modal = document.getElementById('quickPayModal');
            const form = document.getElementById('quickPayForm');

            form.action = `/traffic-penalties/${id}/quick-pay`;
            document.getElementById('quickPayPenaltyNo').textContent = penaltyNo;
            document.getElementById('quickPayAmount').textContent = amount;

            if (parseInt(remainingDays) > 0) {
                document.getElementById('quickPayDiscountInfo').textContent = `%25 indirim aktif • ${remainingDays} gün kaldı`;
            } else {
                document.getElementById('quickPayDiscountInfo').textContent = 'İndirim süresi dolmuş';
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeQuickPayModal() {
            const modal = document.getElementById('quickPayModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function toggleFilters() {
            const box = document.getElementById('filterBox');
            const icon = document.getElementById('toggleFilterIcon');

            box.classList.toggle('hidden');
            icon.textContent = box.classList.contains('hidden') ? '⌄' : '⌃';
        }

        document.addEventListener('click', function (e) {
            const modal = document.getElementById('quickPayModal');
            if (e.target === modal) {
                closeQuickPayModal();
            }
        });
    </script>
@endsection
