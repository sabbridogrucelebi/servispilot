@extends('layouts.app')

@section('title', 'Puantaj / Sefer')
@section('subtitle', 'Günlük operasyon kayıtlarını yönetin')

@section('content')
@php
    $tripStats = $tripStats ?? [
        'toplam_sefer' => 124,
        'sabah_sefer' => 62,
        'aksam_sefer' => 62,
        'sorunlu_kayit' => 5,
        'tanimli_arac' => 255,
        'tanimsiz_arac' => 14,
        'ucretli_sefer' => 98,
        'toplam_tutar' => 133900,
    ];

    $customers = $customers ?? collect();
    $routes = $routes ?? collect();
    $drivers = $drivers ?? collect();

    $tripRows = $tripRows ?? collect([
        (object) [
            'date' => '01.04.2026',
            'customer' => 'KORAKS ALİMİNYUM',
            'customer_note' => 'Personel taşıma hizmeti · 2026',
            'route' => 'Karaman Yolu',
            'route_note' => 'Sabah personel hattı',
            'type' => 'Sabah',
            'planned_vehicle' => '42 ABC 123',
            'actual_vehicle' => '42 ABC 123',
            'driver' => 'Ahmet Yılmaz',
            'status' => 'Tamamlandı',
            'status_class' => 'bg-emerald-100 text-emerald-700',
            'fee_rule' => 'Tanımlı Sabah Ücreti',
            'amount' => 1250,
        ],
        (object) [
            'date' => '01.04.2026',
            'customer' => 'KORAKS ALİMİNYUM',
            'customer_note' => 'Personel taşıma hizmeti · 2026',
            'route' => 'Karaman Yolu',
            'route_note' => 'Akşam dönüş hattı',
            'type' => 'Akşam',
            'planned_vehicle' => '42 XYZ 987',
            'actual_vehicle' => '42 XYZ 987',
            'driver' => 'Ahmet Yılmaz',
            'status' => 'Ücretsiz',
            'status_class' => 'bg-blue-100 text-blue-700',
            'fee_rule' => 'Hafta Sonu Ücret Yok',
            'amount' => 1000,
        ],
        (object) [
            'date' => '01.04.2026',
            'customer' => 'KORAKS ALİMİNYUM',
            'customer_note' => 'Personel taşıma hizmeti · 2026',
            'route' => 'OSB Güney Hattı',
            'route_note' => 'Seçilen sabah hattı',
            'type' => 'Sabah',
            'planned_vehicle' => '—',
            'actual_vehicle' => '42 TTR 909',
            'driver' => 'Ahmet Yılmaz',
            'status' => 'Tanımsız Araç',
            'status_class' => 'bg-amber-100 text-amber-700',
            'fee_rule' => 'Fallback Sabah',
            'amount' => 1000,
        ],
    ]);

    $summaryFooter = $summaryFooter ?? [
        'tanimli_arac_tutari' => 155500,
        'tanimsiz_arac_tutari' => 12600,
        'ucretli_toplam' => 27690,
        'aksam_toplam' => 16400,
        'sabah_toplam' => 11400,
        'toplam_hizmet' => 41640.03,
        'genel_toplam' => 44640,
    ];
@endphp

<div x-data="tripIndexPage()" class="space-y-6">

    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">Puantaj / Sefer Yönetimi</h2>
            <p class="mt-2 text-sm font-medium text-slate-500">Günlük operasyon kayıtlarını yönetin, araç ve ücretleri yönetin.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" @click="openCreateModal = true"
                    class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:scale-[1.02]">
                <span>＋</span>
                <span>Yeni Sefer Kaydı</span>
            </button>

            <button type="button" @click="openBulkModal = true"
                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <span>◆</span>
                <span>Toplu Günlük Puantaj</span>
            </button>

            <button type="button"
                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <span>📊</span>
                <span>Excel Al</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-blue-500 to-indigo-600 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Toplam Sefer Kaydı</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($tripStats['toplam_sefer']) }}</div>
                <div class="mt-2 text-xs text-white/75">Toplam sefer ve işlem kaydı</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-emerald-500 to-teal-500 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Sabah Seferi</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($tripStats['sabah_sefer']) }}</div>
                <div class="mt-2 text-xs text-white/75">Sabah operasyon kayıtları</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-pink-500 to-rose-500 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Akşam Seferi</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($tripStats['aksam_sefer']) }}</div>
                <div class="mt-2 text-xs text-white/75">Akşam dağıtım kayıtları</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-amber-500 to-orange-500 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Sorunlu Kayıt</div>
                <div class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($tripStats['sorunlu_kayit']) }}</div>
                <div class="mt-2 text-xs text-white/75">Kontrol edilmesi gereken kayıtlar</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[28px] border border-slate-200/60 bg-white p-5 shadow-lg shadow-slate-200/40">
            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Tanımlı Araç</div>
            <div class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($tripStats['tanimli_arac']) }}</div>
            <div class="mt-2 text-xs text-slate-500">Rotasında tanımlı araçla yapılan kayıtlar</div>
        </div>

        <div class="rounded-[28px] border border-slate-200/60 bg-white p-5 shadow-lg shadow-slate-200/40">
            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Tanımsız Araç</div>
            <div class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($tripStats['tanimsiz_arac']) }}</div>
            <div class="mt-2 text-xs text-slate-500">Fallback kuralıyla işlenen araçlar</div>
        </div>

        <div class="rounded-[28px] border border-slate-200/60 bg-white p-5 shadow-lg shadow-slate-200/40">
            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Ücretli Sefer</div>
            <div class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($tripStats['ucretli_sefer']) }}</div>
            <div class="mt-2 text-xs text-slate-500">Ücret üreten kayıt sayısı</div>
        </div>

        <div class="rounded-[28px] border border-slate-200/60 bg-white p-5 shadow-lg shadow-slate-200/40">
            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Toplam Tutar</div>
            <div class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">₺{{ number_format($tripStats['toplam_tutar'], 2, ',', '.') }}</div>
            <div class="mt-2 text-xs text-slate-500">Dönemsel toplam operasyon tutarı</div>
        </div>
    </div>

    <form method="GET" action="{{ route('trips.index') }}" class="rounded-[30px] border border-slate-200/60 bg-white/90 p-5 shadow-xl backdrop-blur">
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <div class="xl:col-span-3">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Tarih Aralığı</label>
                <input type="text" name="date_range" value="{{ request('date_range', '01.04.2026 - 30.04.2026') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="xl:col-span-2">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Müşteri</label>
                <select name="customer_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tüm Müşteriler</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->company_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="xl:col-span-2">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Güzergah</label>
                <select name="route_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tüm Güzergahlar</option>
                    @foreach($routes as $route)
                        <option value="{{ $route->id }}">{{ $route->route_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="xl:col-span-2">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Servis Tipi</label>
                <select name="service_type" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tüm Tipler</option>
                    <option value="Sabah">Sabah</option>
                    <option value="Akşam">Akşam</option>
                </select>
            </div>

            <div class="xl:col-span-1">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Durum</label>
                <select name="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tümü</option>
                    <option value="Tamamlandı">Tamamlandı</option>
                    <option value="Ücretsiz">Ücretsiz</option>
                    <option value="Tanımsız Araç">Tanımsız Araç</option>
                </select>
            </div>

            <div class="xl:col-span-2 flex items-end">
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:scale-[1.01]">
                    <span>🔎</span>
                    <span>Göster</span>
                </button>
            </div>
        </div>
    </form>

    <div class="overflow-hidden rounded-[30px] border border-slate-200/60 bg-white/90 shadow-xl backdrop-blur">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Puantaj Listesi</h3>
                <p class="mt-1 text-sm text-slate-500">Günlük puantaj kayıtlarını detaylı görüntüleyin</p>
            </div>
            <div class="text-sm font-medium text-slate-400">Toplam {{ $tripRows->count() }} kayıt</div>
        </div>

        <div class="border-b border-slate-100 px-6 py-4">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white">Tümü</button>
                <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600">255</button>
                <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600">Müşteri</button>
                <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600">Güzergah</button>
                <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600">Şoför</button>
                <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600">Excel</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[1650px] w-full">
                <thead class="border-b border-slate-100 bg-slate-50">
                    <tr>
                        <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Tarih</th>
                        <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Müşteri</th>
                        <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Güzergah</th>
                        <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Tip</th>
                        <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Planlanan Araç</th>
                        <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Gerçek Araç</th>
                        <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Şoför</th>
                        <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Durum</th>
                        <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Ücret Kuralı</th>
                        <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Tutar</th>
                        <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-[0.14em] text-slate-500">İşlemler</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @foreach($tripRows as $row)
                        <tr class="transition duration-200 hover:bg-indigo-50/40">
                            <td class="px-5 py-5 text-sm font-extrabold text-slate-900">{{ $row->date }}</td>
                            <td class="px-5 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-500 text-lg text-white shadow">🏢</div>
                                    <div>
                                        <div class="text-sm font-extrabold tracking-wide text-slate-900">{{ $row->customer }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $row->customer_note }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-5">
                                <div class="text-sm font-semibold text-slate-800">{{ $row->route }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $row->route_note }}</div>
                            </td>
                            <td class="px-5 py-5 text-sm font-semibold text-slate-800">{{ $row->type }}</td>
                            <td class="px-5 py-5">
                                <div class="inline-flex min-w-[130px] items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm">
                                    <span>{{ $row->planned_vehicle }}</span>
                                    <span class="text-slate-400">▾</span>
                                </div>
                            </td>
                            <td class="px-5 py-5">
                                <div class="text-sm font-semibold text-slate-800">{{ $row->actual_vehicle }}</div>
                            </td>
                            <td class="px-5 py-5">
                                <div class="text-sm font-semibold text-slate-800">{{ $row->driver }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $row->driver }}</div>
                            </td>
                            <td class="px-5 py-5">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $row->status_class }}">{{ $row->status }}</span>
                            </td>
                            <td class="px-5 py-5 text-sm font-medium text-slate-600">{{ $row->fee_rule }}</td>
                            <td class="px-5 py-5">
                                <span class="inline-flex rounded-2xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-800">
                                    {{ $row->amount > 0 ? '₺' . number_format($row->amount, 2, ',', '.') : '0 TL' }}
                                </span>
                            </td>
                            <td class="px-5 py-5">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">Detay</button>
                                    <button type="button" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">Düzenle</button>
                                    <button type="button" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-100">Sil</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot class="border-t border-slate-200 bg-slate-50/80">
                    <tr>
                        <td colspan="2" class="px-5 py-4 text-left text-base font-semibold text-slate-700">Durum</td>
                        <td class="px-5 py-4">
                            <div class="rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm">Tanımlı Araç Tutarı</div>
                        </td>
                        <td class="px-5 py-4 text-lg font-bold text-slate-900">₺{{ number_format($summaryFooter['tanimli_arac_tutari'], 2, ',', '.') }}</td>
                        <td class="px-5 py-4">
                            <div class="rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm">Tanımsız Araç</div>
                        </td>
                        <td class="px-5 py-4 text-lg font-bold text-slate-900">₺{{ number_format($summaryFooter['tanimsiz_arac_tutari'], 2, ',', '.') }}</td>
                        <td class="px-5 py-4">
                            <div class="rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm">Ücretli Toplam</div>
                        </td>
                        <td class="px-5 py-4 text-lg font-bold text-slate-900">₺{{ number_format($summaryFooter['ucretli_toplam'], 2, ',', '.') }}</td>
                        <td class="px-5 py-4 text-lg font-bold text-slate-900">₺{{ number_format($summaryFooter['toplam_hizmet'], 2, ',', '.') }}</td>
                        <td colspan="2" class="px-5 py-4 text-right text-xl font-black text-slate-900">₺{{ number_format($summaryFooter['genel_toplam'], 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="border-t border-slate-100 px-6 py-4 text-center text-sm text-slate-500">
            1 Nisan 2026 - 10 Nisan 2026 tarihleri arasında toplam 44 kayıt bulundu.
        </div>
    </div>

    <div x-show="openCreateModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-6" style="display:none;">
        <div @click.away="openCreateModal = false" class="w-full max-w-3xl overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-2xl shadow-slate-900/20">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Yeni Sefer Kaydı</h3>
                    <p class="mt-1 text-sm text-slate-500">Yeni operasyon kaydı ekleyin</p>
                </div>
                <button type="button" @click="openCreateModal = false" class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 transition hover:bg-slate-200">✕</button>
            </div>
            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Tarih</label>
                    <input type="date" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Müşteri</label>
                    <select class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800">
                        <option>KORAKS ALİMİNYUM</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Güzergah</label>
                    <select class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800">
                        <option>Karaman Yolu</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Sefer Tipi</label>
                    <select class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800">
                        <option>Sabah</option>
                        <option>Akşam</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Planlanan Araç</label>
                    <input type="text" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800" value="42 ABC 123">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Gerçek Araç</label>
                    <select class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800">
                        <option>42 XYZ 999</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Şoför</label>
                    <select class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800">
                        <option>Ahmet Yılmaz</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Tutar</label>
                    <input type="number" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800" placeholder="1250">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Not</label>
                    <textarea rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-5">
                <button type="button" @click="openCreateModal = false" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700">Vazgeç</button>
                <button type="button" class="rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white">Kaydet</button>
            </div>
        </div>
    </div>

    <div x-show="openBulkModal" x-transition.opacity class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/50 px-4 py-6" style="display:none;">
        <div @click.away="openBulkModal = false" class="w-full max-w-4xl overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-2xl shadow-slate-900/20">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Toplu Günlük Puantaj</h3>
                    <p class="mt-1 text-sm text-slate-500">Seçili gün için birden fazla güzergahı tek ekranda işleyin</p>
                </div>
                <button type="button" @click="openBulkModal = false" class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 transition hover:bg-slate-200">✕</button>
            </div>
            <div class="space-y-5 p-6">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Tarih</label>
                        <input type="date" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Müşteri</label>
                        <select class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800">
                            <option>KORAKS ALİMİNYUM</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[26px] border border-slate-200 bg-slate-50/70">
                    <table class="w-full min-w-[700px]">
                        <thead class="border-b border-slate-200 bg-white">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Güzergah</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Sabah Araç</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Akşam Araç</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr>
                                <td class="px-4 py-3 text-sm font-semibold text-slate-800">Karaman Yolu</td>
                                <td class="px-4 py-3"><select class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-800"><option>42 ABC 123</option></select></td>
                                <td class="px-4 py-3"><select class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-800"><option>42 XYZ 999</option></select></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm font-semibold text-slate-800">Merkez Hat</td>
                                <td class="px-4 py-3"><select class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-800"><option>42 DEF 456</option></select></td>
                                <td class="px-4 py-3"><select class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-800"><option>42 DEF 456</option></select></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm font-semibold text-slate-800">Organize Hat</td>
                                <td class="px-4 py-3"><select class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-800"><option>—</option></select></td>
                                <td class="px-4 py-3"><select class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-800"><option>42 AAA 111</option></select></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-5">
                <button type="button" @click="openBulkModal = false" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700">Vazgeç</button>
                <button type="button" class="rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white">Hepsini Kaydet</button>
            </div>
        </div>
    </div>
</div>

<script>
    function tripIndexPage() {
        return {
            openCreateModal: false,
            openBulkModal: false,
        }
    }
</script>
@endsection
