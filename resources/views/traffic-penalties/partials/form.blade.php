@php
    $isEdit = isset($trafficPenalty) && $trafficPenalty;
    $penaltyAmountValue = old('penalty_amount', $trafficPenalty->penalty_amount ?? '');
    $discountedValue = old('discounted_amount', $trafficPenalty->discounted_amount ?? '');
@endphp

<div class="grid gap-6 xl:grid-cols-3">
    <div class="xl:col-span-2 space-y-6">
        <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900">Ceza Bilgileri</h3>
            <p class="mt-1 text-sm text-slate-500">Trafik cezasının temel detaylarını girin.</p>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Araç</label>
                    <select name="vehicle_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        <option value="">Araç seçiniz</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', $trafficPenalty->vehicle_id ?? '') == $vehicle->id)>
                                {{ $vehicle->plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}
                            </option>
                        @endforeach
                    </select>
                    @error('vehicle_id') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Ceza No</label>
                    <input type="text" name="penalty_no" value="{{ old('penalty_no', $trafficPenalty->penalty_no ?? '') }}"
                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    @error('penalty_no') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Ceza Tarihi</label>
                    <input type="date" name="penalty_date" id="penalty_date"
                           value="{{ old('penalty_date', isset($trafficPenalty->penalty_date) ? $trafficPenalty->penalty_date->format('Y-m-d') : '') }}"
                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    @error('penalty_date') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Ceza Saati</label>
                    <input type="time" name="penalty_time"
                           value="{{ old('penalty_time', $trafficPenalty->penalty_time ?? '') }}"
                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    @error('penalty_time') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Ceza Maddesi</label>
                    <input type="text" name="penalty_article"
                           value="{{ old('penalty_article', $trafficPenalty->penalty_article ?? '') }}"
                           placeholder="Örn: 47/1-b"
                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    @error('penalty_article') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Ceza Yeri</label>
                    <input type="text" name="penalty_location"
                           value="{{ old('penalty_location', $trafficPenalty->penalty_location ?? '') }}"
                           placeholder="Örn: Konya Yolu / Ankara"
                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    @error('penalty_location') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Ceza Tutarı</label>
                    <input type="number" step="0.01" min="0" name="penalty_amount" id="penalty_amount"
                           value="{{ $penaltyAmountValue }}"
                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    @error('penalty_amount') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">%25 İndirimli Tutar</label>
                    <input type="text" id="discounted_amount_preview"
                           value="{{ $discountedValue }}"
                           readonly
                           class="w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
                    <div class="mt-1 text-xs text-slate-500">Bu tutar otomatik hesaplanır.</div>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Şoför Ad Soyad</label>
                    <input type="text" name="driver_name"
                           value="{{ old('driver_name', $trafficPenalty->driver_name ?? '') }}"
                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    @error('driver_name') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Notlar</label>
                    <textarea name="notes" rows="4"
                              class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">{{ old('notes', $trafficPenalty->notes ?? '') }}</textarea>
                    @error('notes') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900">Trafik Cezası Belgesi ve Ödeme Dekontu</h3>
            <p class="mt-1 text-sm text-slate-500">
                Ceza belgesini ve ödeme dekontunu yükleyebilirsiniz. Ödeme tarihi girildiğinde sistem indirimi otomatik uygular.
            </p>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Trafik Cezası Belgesi</label>
                    <input type="file" name="traffic_penalty_document"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm">
                    @error('traffic_penalty_document') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror

                    @if($isEdit && $trafficPenalty->traffic_penalty_document)
                        <a href="{{ asset('storage/' . $trafficPenalty->traffic_penalty_document) }}"
                           target="_blank"
                           class="mt-2 inline-block text-xs font-semibold text-indigo-600">
                            Mevcut belgeyi görüntüle
                        </a>
                    @endif
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Ödeme Dekontu</label>
                    <input type="file" name="payment_receipt"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm">
                    @error('payment_receipt') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror

                    @if($isEdit && $trafficPenalty->payment_receipt)
                        <a href="{{ asset('storage/' . $trafficPenalty->payment_receipt) }}"
                           target="_blank"
                           class="mt-2 inline-block text-xs font-semibold text-emerald-600">
                            Mevcut dekontu görüntüle
                        </a>
                    @endif
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Ceza Ödeme Tarihi</label>
                    <input type="date" name="payment_date" id="payment_date"
                           value="{{ old('payment_date', isset($trafficPenalty->payment_date) && $trafficPenalty->payment_date ? $trafficPenalty->payment_date->format('Y-m-d') : '') }}"
                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    @error('payment_date') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Ödeme Durumu Önizleme</label>
                    <div id="payment_status_preview"
                         class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                        Ödeme tarihi girildiğinde sistem hesaplayacak
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900">Akıllı Tutar Kartı</h3>

            <div class="mt-5 space-y-4">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Normal Ceza</div>
                    <div id="normal_amount_card" class="mt-2 text-2xl font-extrabold text-slate-900">0,00 ₺</div>
                </div>

                <div class="rounded-2xl bg-emerald-50 p-4">
                    <div class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-600">%25 İndirimli Tutar</div>
                    <div id="discount_amount_card" class="mt-2 text-2xl font-extrabold text-emerald-600">0,00 ₺</div>
                </div>

                <div class="rounded-2xl bg-indigo-50 p-4">
                    <div class="text-xs font-bold uppercase tracking-[0.14em] text-indigo-600">Uygulanacak Tutar</div>
                    <div id="final_amount_card" class="mt-2 text-2xl font-extrabold text-indigo-700">0,00 ₺</div>
                    <div id="final_amount_note" class="mt-2 text-xs font-medium text-slate-500">
                        Ceza ve ödeme tarihine göre otomatik belirlenir.
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3">
                <button type="submit"
                        class="w-full rounded-2xl bg-gradient-to-r from-rose-600 to-pink-600 px-5 py-4 text-sm font-semibold text-white shadow transition hover:scale-[1.01]">
                    {{ $buttonText }}
                </button>

                <a href="{{ route('traffic-penalties.index') }}"
                   class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Vazgeç
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function formatMoney(value) {
        const number = parseFloat(value || 0);
        return number.toLocaleString('tr-TR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + ' ₺';
    }

    function updatePenaltyCalculations() {
        const penaltyAmountInput = document.getElementById('penalty_amount');
        const penaltyDateInput = document.getElementById('penalty_date');
        const paymentDateInput = document.getElementById('payment_date');
        const discountedPreview = document.getElementById('discounted_amount_preview');
        const paymentStatusPreview = document.getElementById('payment_status_preview');
        const normalAmountCard = document.getElementById('normal_amount_card');
        const discountAmountCard = document.getElementById('discount_amount_card');
        const finalAmountCard = document.getElementById('final_amount_card');
        const finalAmountNote = document.getElementById('final_amount_note');

        const penaltyAmount = parseFloat(penaltyAmountInput.value || 0);
        const discountedAmount = penaltyAmount * 0.75;

        discountedPreview.value = discountedAmount.toFixed(2);
        normalAmountCard.textContent = formatMoney(penaltyAmount);
        discountAmountCard.textContent = formatMoney(discountedAmount);

        const penaltyDate = penaltyDateInput.value ? new Date(penaltyDateInput.value) : null;
        const paymentDate = paymentDateInput.value ? new Date(paymentDateInput.value) : null;

        if (!penaltyDate) {
            paymentStatusPreview.textContent = 'Önce ceza tarihini giriniz.';
            finalAmountCard.textContent = formatMoney(0);
            finalAmountNote.textContent = 'Ceza tarihi olmadan ödeme kuralı hesaplanamaz.';
            return;
        }

        const discountDeadline = new Date(penaltyDate);
        discountDeadline.setMonth(discountDeadline.getMonth() + 1);

        if (paymentDate) {
            if (paymentDate <= discountDeadline) {
                paymentStatusPreview.innerHTML = '<span class="text-emerald-600">%25 indirim uygulanır. 1 ay içinde ödeme yapılmış.</span>';
                finalAmountCard.textContent = formatMoney(discountedAmount);
                finalAmountNote.textContent = 'Ödeme süresi uygundur, indirimli tutar geçerlidir.';
            } else {
                paymentStatusPreview.innerHTML = '<span class="text-rose-600">1 aylık indirim süresi geçti. İndirimsiz tutar uygulanır.</span>';
                finalAmountCard.textContent = formatMoney(penaltyAmount);
                finalAmountNote.textContent = 'Geç ödeme olduğu için normal ceza tutarı uygulanır.';
            }
        } else {
            const today = new Date();
            if (today <= discountDeadline) {
                paymentStatusPreview.innerHTML = '<span class="text-emerald-600">Şu anda indirim süresi aktif. Ödenirse %25 indirim uygulanır.</span>';
                finalAmountCard.textContent = formatMoney(discountedAmount);
                finalAmountNote.textContent = 'Henüz ödeme yapılmadı, indirimli ödeme hakkı devam ediyor.';
            } else {
                paymentStatusPreview.innerHTML = '<span class="text-rose-600">İndirim süresi dolmuş durumda.</span>';
                finalAmountCard.textContent = formatMoney(penaltyAmount);
                finalAmountNote.textContent = 'Ödeme yapılırsa indirimsiz tutar uygulanır.';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('penalty_amount')?.addEventListener('input', updatePenaltyCalculations);
        document.getElementById('penalty_date')?.addEventListener('change', updatePenaltyCalculations);
        document.getElementById('payment_date')?.addEventListener('change', updatePenaltyCalculations);

        updatePenaltyCalculations();
    });
</script>