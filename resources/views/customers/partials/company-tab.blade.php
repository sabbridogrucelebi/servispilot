<div class="grid grid-cols-1 gap-6 2xl:grid-cols-12">

    <div class="2xl:col-span-8 overflow-hidden rounded-[30px] border border-slate-200/70 bg-white shadow-lg shadow-slate-200/40">
        <div class="flex items-center justify-between border-b border-slate-100 px-7 py-6">
            <div>
                <h3 class="text-xl font-bold text-slate-900">Firma Bilgileri</h3>
                <p class="mt-1 text-sm text-slate-500">Müşteriye ait temel iletişim ve kayıt bilgileri</p>
            </div>

            <div class="hidden md:flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-lg">
                {{ $customerTypeIcon }}
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2">
            <div class="border-b border-r border-slate-100 px-7 py-6">
                <div class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Firma Adı</div>
                <div class="mt-3 text-base font-bold text-slate-900">{{ $customer->company_name ?: '-' }}</div>
            </div>

            <div class="border-b border-slate-100 px-7 py-6">
                <div class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Firma Ünvanı</div>
                <div class="mt-3 text-base font-semibold text-slate-800">{{ $customer->company_title ?: '-' }}</div>
            </div>

            <div class="border-b border-r border-slate-100 px-7 py-6">
                <div class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Yetkili Kişi</div>
                <div class="mt-3 text-base font-semibold text-slate-800">{{ $customer->authorized_person ?: '-' }}</div>
            </div>

            <div class="border-b border-slate-100 px-7 py-6">
                <div class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Yetkili Telefon</div>
                <div class="mt-3 text-base font-semibold text-slate-800">{{ $customer->authorized_phone ?: '-' }}</div>
            </div>

            <div class="border-b border-r border-slate-100 px-7 py-6">
                <div class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">E-Posta</div>
                <div class="mt-3 text-base font-semibold text-slate-800 break-all">{{ $customer->email ?: '-' }}</div>
            </div>

            <div class="border-b border-slate-100 px-7 py-6">
                <div class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Müşteri Türü</div>
                <div class="mt-3 text-base font-semibold text-slate-800">{{ $customer->customer_type ?: '-' }}</div>
            </div>

            <div class="border-b border-slate-100 px-7 py-6 md:col-span-2">
                <div class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Firma Adresi</div>
                <div class="mt-3 text-sm leading-7 text-slate-700">
                    {{ $customer->address ?: 'Adres bilgisi girilmemiş.' }}
                </div>
            </div>

            <div class="px-7 py-6 md:col-span-2">
                <div class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Notlar</div>
                <div class="mt-3 text-sm leading-7 text-slate-700">
                    {{ $customer->notes ?: 'Herhangi bir not eklenmemiş.' }}
                </div>
            </div>
        </div>
    </div>

    <div class="2xl:col-span-4 space-y-6">

        <div class="overflow-hidden rounded-[30px] border border-slate-200/70 bg-white shadow-lg shadow-slate-200/40">
            <div class="border-b border-slate-100 px-6 py-5">
                <h3 class="text-lg font-bold text-slate-900">Yönetici Özeti</h3>
                <p class="mt-1 text-sm text-slate-500">Kısa durum görünümü</p>
            </div>

            <div class="space-y-4 px-6 py-6">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Durum</div>
                    <div class="mt-2 text-sm font-bold text-slate-900">{{ $statusText }}</div>
                </div>

                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Yetkili</div>
                    <div class="mt-2 text-sm font-bold text-slate-900">{{ $customer->authorized_person ?: '-' }}</div>
                </div>

                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">İletişim</div>
                    <div class="mt-2 text-sm font-bold text-slate-900">{{ $customer->authorized_phone ?: '-' }}</div>
                    <div class="mt-1 text-xs text-slate-500">{{ $customer->email ?: '-' }}</div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-[30px] border border-slate-200/70 bg-white shadow-lg shadow-slate-200/40">
            <div class="border-b border-slate-100 px-6 py-5">
                <h3 class="text-lg font-bold text-slate-900">Sözleşme Özeti</h3>
                <p class="mt-1 text-sm text-slate-500">Mevcut sözleşme durum bilgisi</p>
            </div>

            <div class="space-y-4 px-6 py-6">
                @if(!$customer->contract_end_date)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <div class="text-sm font-semibold text-slate-700">Bitiş tarihi girilmemiş</div>
                        <div class="mt-1 text-xs text-slate-500">Sözleşme takibi için bitiş tarihi eklenmesi önerilir.</div>
                    </div>
                @elseif($isContractExpired)
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4">
                        <div class="text-sm font-semibold text-rose-700">Sözleşme süresi dolmuş</div>
                        <div class="mt-1 text-xs text-rose-600">{{ abs($daysRemaining) }} gün önce sona erdi.</div>
                    </div>
                @elseif($isContractEndingSoon)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4">
                        <div class="text-sm font-semibold text-amber-700">Sözleşme bitişi yaklaşıyor</div>
                        <div class="mt-1 text-xs text-amber-600">{{ $daysRemaining }} gün sonra sona erecek.</div>
                    </div>
                @else
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4">
                        <div class="text-sm font-semibold text-emerald-700">Sözleşme aktif görünüyor</div>
                        <div class="mt-1 text-xs text-emerald-600">{{ $daysRemaining }} gün kaldı.</div>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-3">
                    <div class="rounded-2xl bg-slate-50 px-4 py-4">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Başlangıç Tarihi</div>
                        <div class="mt-2 text-sm font-bold text-slate-900">
                            {{ $customer->contract_start_date ? $customer->contract_start_date->format('d.m.Y') : '-' }}
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-50 px-4 py-4">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Bitiş Tarihi</div>
                        <div class="mt-2 text-sm font-bold text-slate-900">
                            {{ $customer->contract_end_date ? $customer->contract_end_date->format('d.m.Y') : '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-[30px] border border-slate-200/70 bg-white shadow-lg shadow-slate-200/40">
            <div class="border-b border-slate-100 px-6 py-5">
                <h3 class="text-lg font-bold text-slate-900">Vergi Özeti</h3>
                <p class="mt-1 text-sm text-slate-500">Fatura için kayıtlı oranlar</p>
            </div>

            <div class="space-y-4 px-6 py-6">
                <div class="rounded-2xl bg-emerald-50 px-4 py-4">
                    <div class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-500">KDV Oranı</div>
                    <div class="mt-2 text-lg font-bold text-emerald-700">
                        %{{ $customer->vat_rate ? rtrim(rtrim((string) $customer->vat_rate, '0'), '.') : '0' }}
                    </div>
                </div>

                <div class="rounded-2xl bg-violet-50 px-4 py-4">
                    <div class="text-xs font-bold uppercase tracking-[0.14em] text-violet-500">Tevkifat Oranı</div>
                    <div class="mt-2 text-lg font-bold text-violet-700">
                        {{ $customer->withholding_rate ?: 'Yok' }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
