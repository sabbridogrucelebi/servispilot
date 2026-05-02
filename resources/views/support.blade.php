@extends('layouts.app')

@section('title', 'Yazılım Mimari & Destek')
@section('subtitle', 'Teknik destek ve geliştirici iletişimi')

@section('content')
<div class="flex items-center justify-center min-h-[60vh]">
    <div class="relative w-full max-w-2xl">
        <!-- Background Glow -->
        <div class="absolute -inset-4 rounded-[40px] bg-gradient-to-tr from-indigo-500/20 to-purple-600/20 blur-2xl opacity-50"></div>
        
        <div class="relative rounded-[40px] border border-white bg-white/80 p-12 shadow-2xl backdrop-blur-2xl">
            <div class="text-center">
                <!-- Avatar / Logo Area -->
                <div class="relative mx-auto mb-8 h-32 w-32">
                    <div class="absolute -inset-2 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 opacity-20 blur animate-pulse"></div>
                    <div class="relative flex h-full w-full items-center justify-center rounded-full bg-slate-900 border-4 border-white shadow-2xl text-4xl font-black text-indigo-400">
                        SD
                    </div>
                    <div class="absolute bottom-1 right-1 h-8 w-8 rounded-full bg-emerald-500 border-4 border-white shadow-lg flex items-center justify-center">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <div class="relative h-3 w-3 rounded-full bg-white"></div>
                    </div>
                </div>

                <div class="mb-2 text-[12px] font-black uppercase tracking-[0.4em] text-indigo-600">Yazılım Mimarı</div>
                <h2 class="mb-8 text-5xl font-black tracking-tighter text-slate-900">Sabri DOĞRU</h2>
                
                <p class="mx-auto mb-12 max-w-md text-lg font-medium leading-relaxed text-slate-500">
                    Sistemle ilgili teknik sorularınız, yeni özellik talepleriniz veya hata bildirimleriniz için doğrudan benimle iletişime geçebilirsiniz.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                    <a href="https://wa.me/905305283669" target="_blank" 
                       class="group relative inline-flex items-center justify-center gap-4 overflow-hidden rounded-3xl bg-emerald-500 px-10 py-5 text-lg font-black text-white shadow-xl shadow-emerald-200 transition-all hover:bg-emerald-600 hover:scale-105 active:scale-95">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        <span>WhatsApp Destek</span>
                    </a>
                    
                    <a href="mailto:destek@FiloMERKEZ.pro" 
                       class="inline-flex items-center justify-center gap-3 rounded-3xl bg-slate-100 px-10 py-5 text-lg font-bold text-slate-700 transition-all hover:bg-slate-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        E-posta Gönder
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
