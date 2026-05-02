<x-guest-layout>
<div class="min-h-[50vh] flex items-center justify-center p-4">
    <div class="w-full text-center relative overflow-hidden">
        
        <div class="relative z-10">
            <div class="flex justify-center mb-8">
                <div class="relative">
                    <div class="absolute -inset-4 rounded-full bg-rose-500/10 animate-ping"></div>
                    <div class="relative w-24 h-24 bg-gradient-to-br from-rose-500 to-orange-500 rounded-3xl shadow-xl flex items-center justify-center text-4xl border border-rose-400">
                        🔒
                    </div>
                </div>
            </div>

            <h1 class="text-4xl font-black text-white mb-4 tracking-tight">Erişim Süreniz Doldu</h1>
            <p class="text-slate-400 font-medium text-lg mb-10 mx-auto leading-relaxed">
                Platformu kullanmaya devam edebilmek için aboneliğinizi yenilemeniz gerekmektedir. Ödeme yaptıysanız lütfen onaylanmasını bekleyin.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10 text-left">
                <div class="p-4 bg-white/5 backdrop-blur-md rounded-2xl border border-white/10">
                    <div class="text-[10px] font-bold text-slate-500 uppercase mb-1">Mevcut Durum</div>
                    <div class="font-bold text-rose-500">Abonelik Pasif</div>
                </div>
                <div class="p-4 bg-white/5 backdrop-blur-md rounded-2xl border border-white/10">
                    <div class="text-[10px] font-bold text-slate-500 uppercase mb-1">Firma Adı</div>
                    <div class="font-bold text-slate-300">{{ auth()->user()->company->name ?? 'Firma' }}</div>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <a href="{{ route('billing.index') }}" class="w-full text-center px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-black rounded-2xl shadow-lg shadow-indigo-500/30 hover:shadow-xl transition-all hover:-translate-y-1 active:scale-95">
                    ABONELİK SEÇENEKLERİNİ GÖR
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-center px-8 py-4 bg-white/5 text-slate-300 font-black rounded-2xl border border-white/10 hover:bg-white/10 transition-all active:scale-95">
                        ÇIKIŞ YAP
                    </button>
                </form>
            </div>

            <div class="mt-8 pt-8 border-t border-white/10">
                <p class="text-xs text-slate-500 font-bold">
                    Yardıma mı ihtiyacınız var? <a href="mailto:destek@FiloMERKEZ.com" class="text-indigo-400">Bize ulaşın.</a>
                </p>
            </div>
        </div>
    </div>
</div>
</x-guest-layout>

