<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Personel Kayıt Formu - {{ $company->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col items-center justify-center p-4 sm:p-8">

    <div class="w-full max-w-2xl">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600 text-white text-3xl shadow-lg mb-4">
                👋
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $company->name }}</h1>
            <p class="text-slate-500 mt-2 font-medium">Lütfen aşağıdaki bilgileri eksiksiz doldurarak personel kaydınızı tamamlayın.</p>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-6 py-6 rounded-3xl mb-8 text-center shadow-sm">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Başvurunuz Alındı!</h3>
                <p class="font-medium text-emerald-600/80">{{ session('success') }}</p>
            </div>
        @else

        <form action="{{ route('invite.driver.store', $token) }}" method="POST" class="space-y-6">
            @csrf

            <div class="rounded-[28px] border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-4 mb-6">Kişisel Bilgileriniz</h3>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Adınız Soyadınız <span class="text-rose-500">*</span></label>
                        <input type="text"
                               name="full_name"
                               required
                               placeholder="Örn: Ali Yılmaz"
                               value="{{ old('full_name') }}"
                               class="w-full rounded-2xl border border-slate-200 px-4 py-3.5 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition-shadow">
                        @error('full_name')
                            <div class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">TC Kimlik No</label>
                        <input type="text"
                               name="tc_no"
                               maxlength="11"
                               placeholder="11 Haneli TC Kimlik Numaranız"
                               value="{{ old('tc_no') }}"
                               class="w-full rounded-2xl border border-slate-200 px-4 py-3.5 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition-shadow">
                        @error('tc_no')
                            <div class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Telefon Numaranız <span class="text-rose-500">*</span></label>
                        <input type="tel"
                               name="phone"
                               required
                               placeholder="Örn: 0532 123 45 67"
                               value="{{ old('phone') }}"
                               class="w-full rounded-2xl border border-slate-200 px-4 py-3.5 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition-shadow">
                        @error('phone')
                            <div class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">E-posta Adresiniz</label>
                        <input type="email"
                               name="email"
                               placeholder="Opsiyonel"
                               value="{{ old('email') }}"
                               class="w-full rounded-2xl border border-slate-200 px-4 py-3.5 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition-shadow">
                        @error('email')
                            <div class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Doğum Tarihiniz</label>
                        <input type="date"
                               name="birth_date"
                               value="{{ old('birth_date') }}"
                               class="w-full rounded-2xl border border-slate-200 px-4 py-3.5 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition-shadow">
                        @error('birth_date')
                            <div class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Ehliyet Sınıfı</label>
                        <select name="license_class"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3.5 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition-shadow bg-white">
                            <option value="">Seçiniz</option>
                            <option value="D1 - Minibüs" @selected(old('license_class') == 'D1 - Minibüs')>D1 - Minibüs</option>
                            <option value="D - Otobüs" @selected(old('license_class') == 'D - Otobüs')>D - Otobüs</option>
                        </select>
                        @error('license_class')
                            <div class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-5">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Ev Adresiniz</label>
                    <textarea name="address"
                              rows="3"
                              placeholder="Mahalle, Sokak, İlçe, İl..."
                              class="w-full rounded-2xl border border-slate-200 px-4 py-3.5 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition-shadow">{{ old('address') }}</textarea>
                    @error('address')
                        <div class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <button type="submit"
                    class="w-full rounded-[24px] bg-indigo-600 px-6 py-5 text-center text-sm font-black text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 hover:shadow-indigo-600/40 transition-all active:scale-[0.98]">
                KAYDIMI OLUŞTUR VE FİRMAYA GÖNDER
            </button>
        </form>
        
        @endif

        <div class="text-center mt-12 mb-6 opacity-60">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Altyapı: FiloMERKEZ</p>
        </div>
    </div>
</body>
</html>
