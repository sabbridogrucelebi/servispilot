@extends('layouts.app')

@section('title', 'Müşteriler')
@section('subtitle', 'Tüm müşteri kayıtlarını yönetin')

@section('content')

@php
    $totalCustomers = $customers->count();
    $activeCustomers = $customers->where('is_active', true)->count();
    $passiveCustomers = $customers->where('is_active', false)->count();

    $customerTypeStats = [
        [
            'label' => 'Fabrika Müşteri Sayısı',
            'count' => $customers->where('customer_type', 'Fabrika')->count(),
            'note' => 'Fabrika türündeki müşteriler',
            'gradient' => 'from-orange-500 to-amber-500',
            'icon' => '🏭',
        ],
        [
            'label' => 'Okul Müşteri Sayısı',
            'count' => $customers->where('customer_type', 'Okul')->count(),
            'note' => 'Okul türündeki müşteriler',
            'gradient' => 'from-blue-500 to-cyan-500',
            'icon' => '🏫',
        ],
        [
            'label' => 'Resmi Daire Sayısı',
            'count' => $customers->where('customer_type', 'Resmi Daire')->count(),
            'note' => 'Resmi kurum müşterileri',
            'gradient' => 'from-violet-500 to-fuchsia-500',
            'icon' => '🏛️',
        ],
        [
            'label' => 'Diğer Servisler',
            'count' => $customers->where('customer_type', 'Diğer Servisler')->count(),
            'note' => 'Diğer hizmet müşterileri',
            'gradient' => 'from-emerald-500 to-teal-500',
            'icon' => '🧾',
        ],
    ];

    $expiringSoonCount = $customers->filter(function ($customer) {
        return $customer->contract_end_date && $customer->contract_end_date->between(now(), now()->copy()->addDays(30));
    })->count();
@endphp

<div class="space-y-6">

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">Müşteri Yönetimi</h2>
            <p class="mt-2 text-sm font-medium text-slate-500">
                Müşterilerinizi görüntüleyin, detaylarını inceleyin ve yönetin.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('customers.create') }}"
               class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 hover:scale-[1.02] transition">
                <span class="text-base">+</span>
                <span>Yeni Müşteri Ekle</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-blue-500 to-indigo-600 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Toplam Müşteri</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ $totalCustomers }}</div>
                <div class="mt-2 text-xs text-white/75">Sistemde kayıtlı tüm müşteriler</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-emerald-500 to-teal-500 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Aktif Müşteri</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ $activeCustomers }}</div>
                <div class="mt-2 text-xs text-white/75">Aktif sözleşme veya aktif kayıt durumu</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-rose-500 to-pink-500 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Pasif Müşteri</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ $passiveCustomers }}</div>
                <div class="mt-2 text-xs text-white/75">Pasif duruma alınmış müşteri kayıtları</div>
            </div>
        </div>

        <div id="customer-type-kpi-card"
             class="relative overflow-hidden rounded-[28px] bg-gradient-to-br {{ $customerTypeStats[0]['gradient'] }} p-5 text-white shadow-xl transition-all duration-700">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>

            <div class="relative flex items-start justify-between gap-3">
                <div>
                    <div id="customer-type-kpi-label" class="text-sm font-medium text-white/80">
                        {{ $customerTypeStats[0]['label'] }}
                    </div>
                    <div id="customer-type-kpi-count" class="mt-3 text-3xl font-extrabold tracking-tight">
                        {{ $customerTypeStats[0]['count'] }}
                    </div>
                    <div id="customer-type-kpi-note" class="mt-2 text-xs text-white/75">
                        {{ $customerTypeStats[0]['note'] }}
                    </div>
                </div>

                <div id="customer-type-kpi-icon"
                     class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-2xl shadow-sm">
                    {{ $customerTypeStats[0]['icon'] }}
                </div>
            </div>
        </div>

    </div>

    <div class="rounded-[30px] border border-slate-200/60 bg-white/90 p-5 shadow-xl backdrop-blur">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="xl:col-span-2">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                    Ara
                </label>
                <input type="text"
                       placeholder="Firma adı, yetkili kişi veya telefon ile ara..."
                       class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                    Durum
                </label>
                <select class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    <option>Tüm Durumlar</option>
                    <option>Aktif</option>
                    <option>Pasif</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                    Müşteri Türü
                </label>
                <select class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    <option>Tüm Türler</option>
                    <option>Fabrika</option>
                    <option>Okul</option>
                    <option>Resmi Daire</option>
                    <option>Diğer Servisler</option>
                </select>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-[30px] border border-slate-200/60 bg-white/90 shadow-xl backdrop-blur">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Müşteri Listesi</h3>
                <p class="mt-1 text-sm text-slate-500">Tüm müşteri kayıtlarını detaylı görüntüleyin</p>
            </div>

            <div class="text-sm font-medium text-slate-400">
                Toplam {{ $totalCustomers }} kayıt
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[1200px] w-full">
                <thead class="border-b border-slate-100 bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Müşteri</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Tür / Ünvan</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Yetkili</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Vergi Bilgisi</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Sözleşme</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Durum</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-[0.14em] text-slate-500">İşlemler</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                        <tr class="transition duration-200 hover:bg-indigo-50/40">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-500 text-lg text-white shadow">
                                        🏢
                                    </div>

                                    <div>
                                        <div class="text-sm font-extrabold tracking-wide text-slate-900">
                                            {{ $customer->company_name }}
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ $customer->address ? \Illuminate\Support\Str::limit($customer->address, 45) : 'Adres bilgisi yok' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="text-sm font-semibold text-slate-800">
                                    {{ $customer->customer_type ?: '-' }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $customer->company_title ?: 'Firma ünvanı belirtilmedi' }}
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="text-sm font-semibold text-slate-800">
                                    {{ $customer->authorized_person ?: '-' }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $customer->authorized_phone ?: 'Telefon bilgisi yok' }}
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="text-sm font-semibold text-slate-800">
                                    KDV: %{{ $customer->vat_rate ? rtrim(rtrim((string) $customer->vat_rate, '0'), '.') : '0' }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    Tevkifat: {{ $customer->withholding_rate ?: 'Yok' }}
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="text-sm font-semibold text-slate-800">
                                    {{ $customer->contract_start_date ? $customer->contract_start_date->format('d.m.Y') : '-' }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    Bitiş: {{ $customer->contract_end_date ? $customer->contract_end_date->format('d.m.Y') : 'Belirtilmedi' }}
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                @if($customer->is_active)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        ● Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">
                                        ● Pasif
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-5">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('customers.show', $customer) }}"
                                       class="rounded-xl bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                        Detay
                                    </a>

                                    <a href="{{ route('customers.edit', $customer) }}"
                                       class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">
                                        Düzenle
                                    </a>

                                    <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Bu müşteriyi silmek istediğine emin misin?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                            Sil
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">
                                <div class="mx-auto max-w-md">
                                    <div class="mb-3 text-4xl">🏢</div>
                                    <div class="text-base font-semibold text-slate-700">Henüz müşteri kaydı yok</div>
                                    <div class="mt-1 text-sm text-slate-500">
                                        İlk müşteri kaydını oluşturarak müşteri yönetimine başlayın.
                                    </div>
                                    <div class="mt-5">
                                        <a href="{{ route('customers.create') }}"
                                           class="inline-flex items-center rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg transition hover:scale-[1.02]">
                                            Yeni Müşteri Ekle
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const customerTypeStats = @json($customerTypeStats);

        if (!customerTypeStats || customerTypeStats.length === 0) return;

        const card = document.getElementById('customer-type-kpi-card');
        const label = document.getElementById('customer-type-kpi-label');
        const count = document.getElementById('customer-type-kpi-count');
        const note = document.getElementById('customer-type-kpi-note');
        const icon = document.getElementById('customer-type-kpi-icon');

        const gradients = [
            'from-orange-500', 'to-amber-500',
            'from-blue-500', 'to-cyan-500',
            'from-violet-500', 'to-fuchsia-500',
            'from-emerald-500', 'to-teal-500'
        ];

        let currentIndex = 0;

        function renderCard(index) {
            const item = customerTypeStats[index];

            card.classList.remove(...gradients);
            const itemGradients = item.gradient.split(' ');
            card.classList.add(...itemGradients);

            label.textContent = item.label;
            count.textContent = item.count;
            note.textContent = item.note;
            icon.textContent = item.icon;
        }

        renderCard(currentIndex);

        setInterval(() => {
            currentIndex = (currentIndex + 1) % customerTypeStats.length;
            renderCard(currentIndex);
        }, 5000);
    });
</script>

@endsection