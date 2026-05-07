<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FiloMERKEZ - Premium Filo Yönetim Platformu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #030712; /* Slate 950 */
            color: #f8fafc;
            overflow-x: hidden;
        }

        /* Animated Dark Mesh Background */
        .premium-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -2;
            background: #030712;
            overflow: hidden;
        }
        .bg-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.5;
            animation: float 20s infinite ease-in-out;
        }
        .blob-1 { top: -10%; left: -10%; width: 50vw; height: 50vw; background: #4f46e5; animation-delay: 0s; }
        .blob-2 { bottom: -20%; right: -10%; width: 60vw; height: 60vw; background: #2563eb; animation-delay: -5s; }
        .blob-3 { top: 40%; left: 60%; width: 40vw; height: 40vw; background: #9333ea; animation-delay: -10s; }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }

        /* Glassmorphism Utilities */
        .glass-nav {
            background: rgba(3, 7, 18, 0.6);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* Typography Gradients */
        .text-gradient-primary {
            background: linear-gradient(135deg, #a5b4fc 0%, #818cf8 50%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .text-gradient-secondary {
            background: linear-gradient(135deg, #93c5fd 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Premium Button */
        .btn-premium {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }
        .btn-premium::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            border-radius: inherit;
            background: linear-gradient(135deg, #6366f1 0%, #60a5fa 100%);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .btn-premium:hover::before { opacity: 1; }
        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px -10px rgba(79, 70, 229, 0.5);
        }

        .btn-glass {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Reveal Animation */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            animation: revealAnim 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes revealAnim {
            to { opacity: 1; transform: translateY(0); }
        }
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .delay-300 { animation-delay: 300ms; }
        .delay-400 { animation-delay: 400ms; }
        .delay-500 { animation-delay: 500ms; }

        /* Grid Pattern Overlay */
        .grid-pattern {
            position: absolute;
            inset: 0;
            background-image: 
                linear-gradient(to right, rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 50px 50px;
            mask-image: radial-gradient(circle at center, black, transparent 80%);
            -webkit-mask-image: radial-gradient(circle at center, black, transparent 80%);
            z-index: -1;
        }
    </style>
</head>
<body class="antialiased">

    <!-- Background Elements -->
    <div class="premium-bg">
        <div class="bg-blob blob-1"></div>
        <div class="bg-blob blob-2"></div>
        <div class="bg-blob blob-3"></div>
    </div>
    <div class="grid-pattern"></div>

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass-nav transition-all duration-300">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center gap-3 cursor-pointer group">
                <div class="relative flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <div class="absolute inset-0 rounded-2xl border border-white/20"></div>
                </div>
                <span class="text-2xl font-black tracking-tight text-white">Filo<span class="text-gradient-secondary">MERKEZ</span></span>
            </div>

            <!-- Links (Desktop) -->
            <div class="hidden md:flex items-center gap-10">
                <a href="#" class="text-sm font-semibold text-slate-300 hover:text-white transition-colors">Platform</a>
                <a href="#" class="text-sm font-semibold text-slate-300 hover:text-white transition-colors">Çözümler</a>
                <a href="#" class="text-sm font-semibold text-slate-300 hover:text-white transition-colors">Özellikler</a>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-premium px-6 py-2.5 rounded-xl text-white text-sm font-bold shadow-lg">Paneli Aç</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:block text-sm font-bold text-slate-300 hover:text-white px-4 transition-colors">Giriş Yap</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-premium px-6 py-2.5 rounded-xl text-white text-sm font-bold shadow-lg flex items-center gap-2">
                                Kayıt Ol
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <main class="relative min-h-screen flex items-center pt-24 pb-12 px-6">
        <div class="max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            
            <!-- Left Content -->
            <div class="lg:col-span-6 space-y-8 relative z-10">
                <div class="reveal">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass-card border border-indigo-500/30 text-xs font-bold uppercase tracking-widest text-indigo-300 shadow-[0_0_15px_rgba(79,70,229,0.2)]">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-500"></span>
                        </span>
                        Geleceğin Yönetim Sistemi
                    </div>
                </div>

                <h1 class="reveal delay-100 text-5xl sm:text-6xl lg:text-7xl font-black leading-[1.05] tracking-tight">
                    Filonuzun <br />
                    <span class="text-gradient-primary">Kontrolü</span> Artık <br />
                    Sizin Elinizde.
                </h1>

                <p class="reveal delay-200 text-lg sm:text-xl text-slate-400 font-medium leading-relaxed max-w-xl">
                    FiloMERKEZ; araç takibi, yakıt optimizasyonu, personel rotasyonu ve akıllı raporlamayı tek bir premium ekranda birleştirir. Operasyonlarınızı geleceğe taşıyın.
                </p>

                <div class="reveal delay-300 flex flex-col sm:flex-row items-center gap-4 pt-4">
                    <a href="{{ route('login') }}" class="w-full sm:w-auto btn-premium px-8 py-4 rounded-2xl text-white font-bold text-lg flex items-center justify-center gap-3 group">
                        Sisteme Giriş Yap
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="#" class="w-full sm:w-auto btn-glass px-8 py-4 rounded-2xl text-white font-bold text-lg flex items-center justify-center gap-3">
                        <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm">
                            <div class="w-2 h-2 rounded-full bg-white"></div>
                        </div>
                        Canlı Demo İzle
                    </a>
                </div>

                <div class="reveal delay-400 flex items-center gap-6 pt-6 border-t border-white/10 mt-6">
                    <div class="flex -space-x-3">
                        <img src="https://ui-avatars.com/api/?name=Ali+Y&background=4f46e5&color=fff&size=40" class="w-10 h-10 rounded-full border-2 border-slate-900" alt="User">
                        <img src="https://ui-avatars.com/api/?name=Ayşe+K&background=2563eb&color=fff&size=40" class="w-10 h-10 rounded-full border-2 border-slate-900" alt="User">
                        <img src="https://ui-avatars.com/api/?name=Mehmet+C&background=9333ea&color=fff&size=40" class="w-10 h-10 rounded-full border-2 border-slate-900" alt="User">
                    </div>
                    <div>
                        <div class="flex items-center gap-1 text-yellow-500 text-sm">
                            ★ ★ ★ ★ ★
                        </div>
                        <div class="text-sm font-semibold text-slate-400 mt-1">
                            <span class="text-white">500+</span> seçkin şirket kullanıyor
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Content (Dashboard Mockup) -->
            <div class="lg:col-span-6 relative reveal delay-500 mt-12 lg:mt-0">
                <!-- Decorative Elements -->
                <div class="absolute -top-12 -right-12 w-64 h-64 bg-indigo-500/30 rounded-full blur-[80px]"></div>
                <div class="absolute -bottom-12 -left-12 w-64 h-64 bg-blue-500/20 rounded-full blur-[80px]"></div>
                
                <div class="relative glass-card rounded-[32px] p-2 sm:p-4 transform hover:-translate-y-2 transition-transform duration-500 border border-white/10">
                    <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/10 to-transparent rounded-[32px] pointer-events-none"></div>
                    
                    <!-- Dashboard Image -->
                    <img src="{{ asset('assets/images/hero_fleet.png') }}" alt="FiloMERKEZ Dashboard" class="w-full rounded-[24px] shadow-2xl relative z-10" onerror="this.src='https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2000&auto=format&fit=crop';">
                    
                    <!-- Floating Widget 1 -->
                    <div class="absolute top-8 -right-6 sm:-right-12 glass-card p-4 sm:p-5 rounded-2xl shadow-2xl z-20 border border-white/20 animate-[float_4s_ease-in-out_infinite]">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Tasarruf Oranı</span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black text-white">+%18.4</div>
                        <div class="text-xs text-emerald-400 font-medium mt-1">Geçen aya göre ↑</div>
                    </div>

                    <!-- Floating Widget 2 -->
                    <div class="absolute -bottom-6 -left-6 sm:-left-10 glass-card p-4 rounded-2xl shadow-2xl z-20 border border-white/20 animate-[float_5s_ease-in-out_infinite_reverse]">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <div class="text-[10px] font-black text-indigo-300 uppercase tracking-widest mb-0.5">Sistem Durumu</div>
                                <div class="text-sm font-bold text-white flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    Aktif & Stabil
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
        </div>
    </main>

    <!-- Logo Cloud / Stats -->
    <section class="relative z-10 border-y border-white/5 bg-white/[0.02] backdrop-blur-md py-12">
        <div class="max-w-7xl mx-auto px-6">
            <p class="text-center text-sm font-bold text-slate-400 uppercase tracking-widest mb-8">Rakamlarla FiloMERKEZ</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-white/5">
                <div class="space-y-1">
                    <div class="text-3xl sm:text-4xl font-black text-white">2.5M+</div>
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-widest">Tamamlanan Sefer</div>
                </div>
                <div class="space-y-1">
                    <div class="text-3xl sm:text-4xl font-black text-white">25K+</div>
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-widest">Aktif Araç</div>
                </div>
                <div class="space-y-1">
                    <div class="text-3xl sm:text-4xl font-black text-white">%99.9</div>
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-widest">Uptime Süresi</div>
                </div>
                <div class="space-y-1">
                    <div class="text-3xl sm:text-4xl font-black text-white">7/24</div>
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-widest">Operasyon Desteği</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="relative z-10 py-8 text-center">
        <div class="text-sm font-semibold text-slate-500">
            &copy; 2026 <span class="text-white font-bold">FiloMERKEZ Platformu</span>. Tüm hakları saklıdır.
        </div>
    </footer>

</body>
</html>
