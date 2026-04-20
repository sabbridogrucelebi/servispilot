@extends('layouts.app')

@section('title', 'Yeni Müşteri Ekle')
@section('subtitle', 'Yeni müşteri kaydı oluşturun')

@section('content')

<div class="space-y-6">

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-rose-700 shadow-sm">
            <div class="text-sm font-bold">Formda eksik veya hatalı alanlar var:</div>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                <span>🏢</span>
                <span>Yeni Müşteri Kaydı</span>
            </div>

            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">
                Yeni Müşteri Ekle
            </h2>

            <p class="mt-2 text-sm font-medium text-slate-500">
                Firma, sözleşme ve vergi bilgilerini eksiksiz girerek yeni müşteri kartı oluşturun.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('customers.index') }}"
               class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <span>←</span>
                <span>Listeye Dön</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-blue-500 to-indigo-600 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Müşteri Kaydı</div>
                <div class="mt-3 text-2xl font-extrabold tracking-tight">Yeni Kayıt</div>
                <div class="mt-2 text-xs text-white/75">Sisteme yeni firma kartı ekleniyor</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-emerald-500 to-teal-500 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">KDV Bilgisi</div>
                <div class="mt-3 text-2xl font-extrabold tracking-tight">Fatura Bazlı</div>
                <div class="mt-2 text-xs text-white/75">Fatura işleminde otomatik esas alınır</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-violet-500 to-fuchsia-500 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Tevkifat</div>
                <div class="mt-3 text-2xl font-extrabold tracking-tight">Hazır Seçim</div>
                <div class="mt-2 text-xs text-white/75">Muhasebe süreci için ön tanım yapılır</div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-amber-500 to-orange-500 p-5 text-white shadow-xl">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 right-8 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="text-sm font-medium text-white/80">Sözleşme Takibi</div>
                <div class="mt-3 text-2xl font-extrabold tracking-tight">Başlangıç / Bitiş</div>
                <div class="mt-2 text-xs text-white/75">Takip ve yenileme için kayıt altına alınır</div>
            </div>
        </div>

    </div>

    <form action="{{ route('customers.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

            <div class="xl:col-span-2 overflow-hidden rounded-[30px] border border-slate-200/60 bg-white/90 shadow-xl backdrop-blur">
                <div class="border-b border-slate-100 px-6 py-5">
                    <h3 class="text-lg font-bold text-slate-800">Firma Bilgileri</h3>
                    <p class="mt-1 text-sm text-slate-500">Müşteriye ait temel firma ve iletişim bilgilerini girin</p>
                </div>

                <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            Müşteri Türü
                        </label>
                        <select name="customer_type"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                            <option value="">Seçiniz</option>
                            <option value="Fabrika" {{ old('customer_type') == 'Fabrika' ? 'selected' : '' }}>Fabrika</option>
                            <option value="Okul" {{ old('customer_type') == 'Okul' ? 'selected' : '' }}>Okul</option>
                            <option value="Resmi Daire" {{ old('customer_type') == 'Resmi Daire' ? 'selected' : '' }}>Resmi Daire</option>
                            <option value="Diğer Servisler" {{ old('customer_type') == 'Diğer Servisler' ? 'selected' : '' }}>Diğer Servisler</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            Firma Adı
                        </label>
                        <input type="text"
                               name="company_name"
                               value="{{ old('company_name') }}"
                               placeholder="Örn: ABC Tekstil"
                               class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            Firma Ünvanı
                        </label>
                        <input type="text"
                               name="company_title"
                               value="{{ old('company_title') }}"
                               placeholder="Örn: ABC Tekstil San. ve Tic. Ltd. Şti."
                               class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            Yetkili Kişi
                        </label>
                        <input type="text"
                               name="authorized_person"
                               value="{{ old('authorized_person') }}"
                               placeholder="Ad Soyad"
                               class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            Yetkili Telefon
                        </label>
                        <input type="text"
                               name="authorized_phone"
                               value="{{ old('authorized_phone') }}"
                               placeholder="05xx xxx xx xx"
                               class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            E-Posta Adresi
                        </label>
                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="ornek@firma.com"
                               class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            Firma Adresi
                        </label>
                        <textarea name="address"
                                  rows="4"
                                  placeholder="Firma açık adresi"
                                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">{{ old('address') }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                            Notlar
                        </label>
                        <textarea name="notes"
                                  rows="4"
                                  placeholder="Müşteri ile ilgili özel notlar..."
                                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="space-y-6">

                <div class="overflow-hidden rounded-[30px] border border-slate-200/60 bg-white/90 shadow-xl backdrop-blur">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h3 class="text-lg font-bold text-slate-800">Sözleşme Bilgileri</h3>
                        <p class="mt-1 text-sm text-slate-500">Sözleşme takibini buradan başlatın</p>
                    </div>

                    <div class="space-y-5 px-6 py-5">
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                Başlangıç Tarihi
                            </label>
                            <input type="date"
                                   name="contract_start_date"
                                   value="{{ old('contract_start_date') }}"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                Bitiş Tarihi
                            </label>
                            <input type="date"
                                   name="contract_end_date"
                                   value="{{ old('contract_end_date') }}"
                                   class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4">
                            <div class="text-sm font-semibold text-slate-700">Bilgilendirme</div>
                            <div class="mt-1 text-xs leading-6 text-slate-500">
                                Sözleşme bitiş tarihi girildiğinde bu müşteri için ileride yenileme takibi yapılabilir.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[30px] border border-slate-200/60 bg-white/90 shadow-xl backdrop-blur">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h3 class="text-lg font-bold text-slate-800">Vergi Bilgileri</h3>
                        <p class="mt-1 text-sm text-slate-500">Faturalandırmada kullanılacak oranlar</p>
                    </div>

                    <div class="space-y-5 px-6 py-5">
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                KDV Oranı
                            </label>
                            <select name="vat_rate"
                                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                <option value="0" {{ old('vat_rate') == '0' ? 'selected' : '' }}>%0</option>
                                <option value="1" {{ old('vat_rate') == '1' ? 'selected' : '' }}>%1</option>
                                <option value="10" {{ old('vat_rate') == '10' ? 'selected' : '' }}>%10</option>
                                <option value="20" {{ old('vat_rate', '20') == '20' ? 'selected' : '' }}>%20</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                Tevkifat Oranı
                            </label>
                            <select name="withholding_rate"
                                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                <option value="">Tevkifat Yok</option>
                                <option value="2/10" {{ old('withholding_rate') == '2/10' ? 'selected' : '' }}>2/10</option>
                                <option value="3/10" {{ old('withholding_rate') == '3/10' ? 'selected' : '' }}>3/10</option>
                                <option value="4/10" {{ old('withholding_rate') == '4/10' ? 'selected' : '' }}>4/10</option>
                                <option value="5/10" {{ old('withholding_rate') == '5/10' ? 'selected' : '' }}>5/10</option>
                                <option value="7/10" {{ old('withholding_rate') == '7/10' ? 'selected' : '' }}>7/10</option>
                                <option value="9/10" {{ old('withholding_rate') == '9/10' ? 'selected' : '' }}>9/10</option>
                            </select>
                        </div>

                        <div>
                            <label class="inline-flex items-center gap-3">
                                <input type="checkbox"
                                       name="is_active"
                                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <span class="text-sm font-semibold text-slate-700">Müşteri aktif olarak kaydedilsin</span>
                            </label>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('customers.index') }}"
               class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <span>İptal</span>
            </a>

            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:scale-[1.02]">
                <span>💾</span>
                <span>Kaydet</span>
            </button>
        </div>
    </form>

</div>

@endsection