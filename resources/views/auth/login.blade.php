<x-guest-layout>
    <div x-data="{ loginType: 'staff' }" class="space-y-8">
        <div class="text-center lg:text-left">
            <h2 class="text-3xl font-black tracking-tight text-white">Hoş Geldiniz</h2>
            <p class="mt-3 text-sm font-medium text-slate-400">
                Sisteme erişmek için lütfen kimlik bilgilerinizi girin.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-3 rounded-2xl bg-white/5 p-1.5 border border-white/5">
            <button type="button"
                    @click="loginType = 'staff'"
                    :class="loginType === 'staff'
                        ? 'bg-white/10 text-white border-white/10 shadow-xl'
                        : 'text-slate-500 border-transparent'"
                    class="rounded-xl px-4 py-3 text-xs font-black uppercase tracking-widest border transition-all duration-300">
                PERSONEL
            </button>

            <button type="button"
                    @click="loginType = 'customer'"
                    :class="loginType === 'customer'
                        ? 'bg-white/10 text-white border-white/10 shadow-xl'
                        : 'text-slate-500 border-transparent'"
                    class="rounded-xl px-4 py-3 text-xs font-black uppercase tracking-widest border transition-all duration-300">
                MÜŞTERİ
            </button>
        </div>

        <x-auth-session-status class="mb-2" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="login_type" x-model="loginType">

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1" x-text="loginType === 'customer' ? 'Kullanıcı Adı' : 'E-Posta Adresi'"></label>
                <input id="login"
                       name="login"
                       type="text"
                       value="{{ old('login') }}"
                       :placeholder="loginType === 'customer' ? 'Kullanıcı adınız...' : 'E-posta adresiniz...'"
                       required
                       autofocus
                       class="block w-full rounded-[20px] bg-white/5 border border-white/10 px-6 py-4 text-sm text-white placeholder:text-slate-600 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                <x-input-error :messages="$errors->get('login')" class="mt-2" />
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between px-1">
                    <label for="password" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Şifre</label>
                    @if (Route::has('password.request'))
                        <a class="text-[10px] font-bold text-indigo-400 hover:text-indigo-300 uppercase tracking-widest" href="{{ route('password.request') }}">
                            Şifremi Unuttum
                        </a>
                    @endif
                </div>
                <input id="password"
                       name="password"
                       type="password"
                       required
                       autocomplete="current-password"
                       placeholder="••••••••"
                       class="block w-full rounded-[20px] bg-white/5 border border-white/10 px-6 py-4 text-sm text-white placeholder:text-slate-600 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center gap-3 px-1">
                <input id="remember"
                       type="checkbox"
                       name="remember"
                       class="h-5 w-5 rounded border-white/10 bg-white/5 text-indigo-600 focus:ring-offset-0 focus:ring-indigo-500">
                <label for="remember" class="text-xs font-bold text-slate-400">Beni hatırla</label>
            </div>

            <button type="submit"
                    class="group relative inline-flex w-full items-center justify-center rounded-[24px] bg-indigo-600 py-4 text-sm font-black text-white shadow-2xl shadow-indigo-500/20 transition-all hover:bg-indigo-500 hover:-translate-y-0.5 active:scale-[0.98] overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-400 to-purple-400 opacity-0 group-hover:opacity-20 transition-opacity"></div>
                GİRİŞ YAP
            </button>
        </form>
    </div>
</x-guest-layout>