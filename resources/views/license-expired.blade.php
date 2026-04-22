<x-guest-layout>
    <div class="text-center py-6">
        <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-red-50 border-4 border-red-100 shadow-inner">
            <svg class="h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>
        
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Erişim Kilitlendi</h1>
        <div class="mt-2 text-lg font-bold text-red-600 bg-red-50 inline-block px-4 py-1.5 rounded-full border border-red-100">
            Lisans Süreniz Dolmuştur
        </div>
        
        <p class="mt-6 text-slate-500 leading-relaxed max-w-sm mx-auto">
            Platformu kullanmaya devam edebilmek için lisansınızı yenilemeniz gerekmektedir. Lütfen sistem yöneticisi ile iletişime geçin.
        </p>

        <div class="mt-8 rounded-[24px] border border-slate-100 bg-slate-50 p-6 text-center shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Destek Hattı</p>
            <p class="text-lg font-bold text-slate-800 flex items-center justify-center gap-2">
                <span>💬</span> info@servispilot.com
            </p>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-8">
            @csrf
            <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-slate-800 to-slate-900 px-8 py-4 text-sm font-bold text-white shadow-lg shadow-slate-900/20 hover:shadow-slate-900/40 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2 group">
                <svg class="w-5 h-5 text-slate-300 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Güvenli Çıkış Yap
            </button>
        </form>
    </div>
</x-guest-layout>
