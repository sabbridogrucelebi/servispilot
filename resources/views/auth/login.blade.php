<x-guest-layout>
    <div x-data="{ loginType: 'staff' }" class="space-y-6">
        <div class="text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-gradient-to-br from-blue-600 to-indigo-600 text-3xl text-white shadow-lg">
                🚌
            </div>
            <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-900">ServisPilot Giriş</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">
                Yönetici paneli veya müşteri portalı için giriş yapın.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-3 rounded-3xl bg-slate-100 p-2">
            <button type="button"
                    @click="loginType = 'staff'"
                    :class="loginType === 'staff'
                        ? 'bg-white text-slate-900 shadow'
                        : 'text-slate-500'"
                    class="rounded-2xl px-4 py-3 text-sm font-semibold transition">
                Yönetici Girişi
            </button>

            <button type="button"
                    @click="loginType = 'customer'"
                    :class="loginType === 'customer'
                        ? 'bg-white text-slate-900 shadow'
                        : 'text-slate-500'"
                    class="rounded-2xl px-4 py-3 text-sm font-semibold transition">
                Müşteri Girişi
            </button>
        </div>

        <div x-show="loginType === 'staff'" x-transition class="rounded-3xl border border-blue-100 bg-blue-50 px-4 py-4 text-sm text-blue-700">
            Yönetici girişi için <strong>e-posta adresi</strong> ve şifre kullanın.
        </div>

        <div x-show="loginType === 'customer'" x-transition class="rounded-3xl border border-emerald-100 bg-emerald-50 px-4 py-4 text-sm text-emerald-700">
            Müşteri girişi için size tanımlanan <strong>kullanıcı adı</strong> ve şifreyi kullanın.
        </div>

        <x-auth-session-status class="mb-2" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <input type="hidden" name="login_type" x-model="loginType">

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700" x-text="loginType === 'customer' ? 'Kullanıcı Adı' : 'E-Posta Adresi'"></label>

                <input id="login"
                       name="login"
                       type="text"
                       value="{{ old('login') }}"
                       :placeholder="loginType === 'customer' ? 'Kullanıcı adınızı girin' : 'E-posta adresinizi girin'"
                       required
                       autofocus
                       class="block w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">

                <x-input-error :messages="$errors->get('login')" class="mt-2" />
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Şifre</label>

                <input id="password"
                       name="password"
                       type="password"
                       required
                       autocomplete="current-password"
                       class="block w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between gap-4">
                <label for="remember" class="inline-flex items-center gap-2 text-sm text-slate-600">
                    <input id="remember"
                           type="checkbox"
                           name="remember"
                           class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span>Beni hatırla</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-slate-500 hover:text-slate-700" href="{{ route('password.request') }}">
                        Şifremi unuttum
                    </a>
                @endif
            </div>

            <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:scale-[1.01]">
                Giriş Yap
            </button>
        </form>
    </div>
</x-guest-layout>