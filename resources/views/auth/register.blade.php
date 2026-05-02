<x-guest-layout>
<div class="space-y-8">
    <div class="text-center lg:text-left">
        <h2 class="text-3xl font-black tracking-tight text-white">Yeni Kayıt</h2>
        <p class="mt-3 text-sm font-medium text-slate-400">
            FiloMERKEZ ailesine katılmak için formu doldurun.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- Name -->
        <div class="space-y-2">
            <label for="name" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Ad Soyad</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus placeholder="Adınızı girin..." 
                   class="block w-full rounded-[20px] bg-white/5 border border-white/10 px-6 py-4 text-sm text-white placeholder:text-slate-600 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="space-y-2">
            <label for="email" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">E-Posta Adresi</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required placeholder="e-posta@ornek.com" 
                   class="block w-full rounded-[20px] bg-white/5 border border-white/10 px-6 py-4 text-sm text-white placeholder:text-slate-600 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <label for="password" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Şifre</label>
            <input id="password" name="password" type="password" required placeholder="••••••••" 
                   class="block w-full rounded-[20px] bg-white/5 border border-white/10 px-6 py-4 text-sm text-white placeholder:text-slate-600 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="space-y-2">
            <label for="password_confirmation" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Şifre Tekrar</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required placeholder="••••••••" 
                   class="block w-full rounded-[20px] bg-white/5 border border-white/10 px-6 py-4 text-sm text-white placeholder:text-slate-600 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-4 pt-2">
            <a class="text-xs font-bold text-slate-500 hover:text-white transition-colors" href="{{ route('login') }}">
                Zaten hesabınız var mı?
            </a>

            <button type="submit"
                    class="group relative inline-flex items-center justify-center rounded-[20px] bg-indigo-600 px-8 py-4 text-sm font-black text-white shadow-2xl shadow-indigo-500/20 transition-all hover:bg-indigo-500 hover:-translate-y-0.5 active:scale-[0.98] overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-400 to-purple-400 opacity-0 group-hover:opacity-20 transition-opacity"></div>
                KAYIT OL
            </button>
        </div>
    </form>
</div>
</x-guest-layout>
