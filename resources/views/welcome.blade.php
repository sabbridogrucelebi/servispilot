<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ServisPilot - Ultra Pro Filo Yönetim Sistemi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #020617;
            color: white;
            overflow-x: hidden;
        }
        .mesh-gradient {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            z-index: -1;
            opacity: 0.6;
        }
        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .text-gradient {
            background: linear-gradient(to right, #818cf8, #c084fc, #fb7185);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
            z-index: -1;
        }
    </style>
</head>
<body class="antialiased">
    <div class="mesh-gradient"></div>
    
    {{-- Navbar --}}
    <nav class="fixed top-0 left-0 right-0 z-50 px-6 py-6">
        <div class="max-w-7xl mx-auto flex items-center justify-between glass rounded-[32px] px-8 py-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 bg-indigo-600 rounded-2xl flex items-center justify-center text-xl shadow-lg shadow-indigo-500/20">🚀</div>
                <span class="text-xl font-black tracking-tighter uppercase">Servis<span class="text-indigo-400">Pilot</span></span>
            </div>
            
            <div class="hidden md:flex items-center gap-8 text-sm font-bold text-slate-400">
                <a href="#" class="hover:text-white transition-colors">Özellikler</a>
                <a href="#" class="hover:text-white transition-colors">Hizmetler</a>
                <a href="#" class="hover:text-white transition-colors">Fiyatlandırma</a>
                <a href="#" class="hover:text-white transition-colors">İletişim</a>
            </div>

            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-6 py-2.5 rounded-2xl bg-white text-slate-900 text-sm font-black hover:bg-indigo-50 transition-all shadow-xl">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-bold text-slate-300 hover:text-white px-4">Giriş Yap</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-6 py-2.5 rounded-2xl bg-indigo-600 text-white text-sm font-black hover:bg-indigo-500 transition-all shadow-xl shadow-indigo-500/20">Hemen Katıl</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <main class="relative pt-40 pb-24 px-6 overflow-hidden">
        <div class="hero-glow"></div>
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="space-y-10 relative z-10">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border-indigo-500/20 text-[10px] font-black uppercase tracking-[0.2em] text-indigo-400">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                    </span>
                    Yeni Nesil Filo Yönetimi
                </div>

                <h1 class="text-6xl md:text-8xl font-black leading-[1.1] tracking-tighter">
                    Filonuzu <br>
                    <span class="text-gradient">Zekayla</span> Yönetin.
                </h1>

                <p class="text-xl text-slate-400 font-medium leading-relaxed max-w-lg">
                    ServisPilot ile araç takibi, yakıt analizi, personel yönetimi ve yapay zeka destekli raporlama artık tek bir çatıda. Operasyonel verimliliğinizi %40 artırın.
                </p>

                <div class="flex flex-wrap gap-4 pt-4">
                    <a href="{{ route('login') }}" class="group relative px-10 py-5 rounded-[24px] bg-white text-slate-900 font-black text-lg transition-all hover:-translate-y-1 hover:shadow-2xl overflow-hidden">
                        <div class="absolute inset-0 bg-indigo-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                        Hemen Başla
                    </a>
                    <button class="px-10 py-5 rounded-[24px] glass text-white font-black text-lg hover:bg-white/10 transition-all flex items-center gap-3">
                        <span>▶</span> Demo İzle
                    </button>
                </div>

                <div class="flex items-center gap-8 pt-8">
                    <div class="flex -space-x-4">
                        <div class="h-12 w-12 rounded-full border-2 border-slate-900 bg-slate-800 flex items-center justify-center font-bold">JD</div>
                        <div class="h-12 w-12 rounded-full border-2 border-slate-900 bg-indigo-600 flex items-center justify-center font-bold">AS</div>
                        <div class="h-12 w-12 rounded-full border-2 border-slate-900 bg-slate-700 flex items-center justify-center font-bold">MT</div>
                    </div>
                    <div class="text-sm font-bold text-slate-400">
                        <span class="text-white">500+</span> Şirket ServisPilot ile Büyüyor
                    </div>
                </div>
            </div>

            <div class="relative lg:mt-0 mt-12">
                <div class="absolute inset-0 bg-indigo-500/20 blur-[100px] rounded-full"></div>
                <div class="relative glass rounded-[48px] p-4 shadow-2xl border-white/10 transform hover:scale-[1.02] transition-transform duration-700">
                    <img src="/assets/images/hero_fleet.png" alt="ServisPilot Dashboard" class="rounded-[40px] shadow-2xl">
                    
                    {{-- Floating Metric 1 --}}
                    <div class="absolute -top-10 -right-10 glass p-6 rounded-3xl shadow-2xl animate-bounce duration-[3s]">
                        <div class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Aylık Tasarruf</div>
                        <div class="text-2xl font-black">+%12.4</div>
                    </div>

                    {{-- Floating Metric 2 --}}
                    <div class="absolute -bottom-6 -left-10 glass p-6 rounded-3xl shadow-2xl">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 bg-emerald-500/20 text-emerald-400 rounded-xl flex items-center justify-center text-xl">✓</div>
                            <div>
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sistem Durumu</div>
                                <div class="text-sm font-black">Operasyonel</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- Stats Section --}}
    <section class="max-w-7xl mx-auto px-6 py-24 border-t border-white/5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-12">
            <div class="space-y-2">
                <div class="text-4xl font-black">1.2M+</div>
                <div class="text-sm font-bold text-slate-500 uppercase tracking-widest">Tamamlanan Sefer</div>
            </div>
            <div class="space-y-2">
                <div class="text-4xl font-black">15K+</div>
                <div class="text-sm font-bold text-slate-500 uppercase tracking-widest">Kayıtlı Araç</div>
            </div>
            <div class="space-y-2">
                <div class="text-4xl font-black">%98</div>
                <div class="text-sm font-bold text-slate-500 uppercase tracking-widest">Müşteri Memnuniyeti</div>
            </div>
            <div class="space-y-2">
                <div class="text-4xl font-black">7/24</div>
                <div class="text-sm font-bold text-slate-500 uppercase tracking-widest">Canlı Destek</div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="py-12 px-6 border-t border-white/5 text-center">
        <div class="text-sm font-bold text-slate-500">
            © 2026 <span class="text-white">ServisPilot Pro</span>. Tüm hakları saklıdır.
        </div>
    </footer>
</body>
</html>
