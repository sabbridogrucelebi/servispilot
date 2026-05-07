<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'FiloMERKEZ') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="min-h-screen bg-[#020617] text-white antialiased overflow-x-hidden">
    {{-- Mesh Gradient Background --}}
    <div class="fixed inset-0 opacity-40 z-0">
        <div class="absolute inset-0 bg-[radial-gradient(at_0%_0%,_hsla(253,16%,7%,1)_0,_transparent_50%),_radial-gradient(at_50%_0%,_hsla(225,39%,30%,1)_0,_transparent_50%),_radial-gradient(at_100%_0%,_hsla(339,49%,30%,1)_0,_transparent_50%)]"></div>
    </div>

    <div class="relative min-h-screen z-10 flex items-center">
        <div class="relative mx-auto w-full max-w-7xl px-6 py-12 lg:px-8">
            <div class="grid w-full grid-cols-1 gap-16 lg:grid-cols-2 lg:items-center">
                {{-- Left Side: Branding & Info --}}
                <div class="hidden lg:block space-y-12">
                    <div class="inline-flex items-center gap-4">
                        <img src="{{ asset('assets/images/filomerkez_logo.png') }}" alt="FiloMERKEZ" class="h-14 w-14 rounded-2xl shadow-2xl shadow-indigo-500/20 object-cover border border-white/10" />
                        <span class="text-3xl font-black tracking-tighter uppercase">Filo<span class="text-indigo-400">MERKEZ</span></span>
                    </div>

                    <div class="space-y-6">
                        <h1 class="text-7xl font-black leading-[1.1] tracking-tighter">
                            Filonuzu <br>
                            <span class="bg-gradient-to-r from-indigo-400 via-purple-400 to-rose-400 bg-clip-text text-transparent">
                                Tek Merkezden
                            </span><br>
                            Yönetin.
                        </h1>
                        <p class="text-xl leading-relaxed text-slate-400 font-medium max-w-md">
                            Taşımacılık operasyonunuzu modernize edin. Araç takibi, yakıt analizi ve personel yönetimini yapay zeka ile güçlendirin.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-6 max-w-md">
                        <div class="glass-card rounded-[32px] p-6 hover:bg-white/5 transition-all group">
                            <div class="text-3xl group-hover:scale-110 transition-transform">🏢</div>
                            <div class="mt-4 text-[10px] font-black text-white uppercase tracking-[0.2em]">Müşteri Portalı</div>
                            <div class="mt-2 text-xs leading-relaxed text-slate-500 font-bold">Özel erişim alanı.</div>
                        </div>

                        <div class="glass-card rounded-[32px] p-6 hover:bg-white/5 transition-all group">
                            <div class="text-3xl group-hover:scale-110 transition-transform">🚌</div>
                            <div class="mt-4 text-[10px] font-black text-white uppercase tracking-[0.2em]">Yönetim Paneli</div>
                            <div class="mt-2 text-xs leading-relaxed text-slate-500 font-bold">Tam operasyonel kontrol.</div>
                        </div>
                    </div>
                </div>

                {{-- Right Side: Login Form --}}
                <div class="w-full flex justify-center lg:justify-end">
                    <div class="w-full max-w-lg rounded-[48px] glass-card p-10 shadow-2xl relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-rose-500 opacity-30"></div>
                        
                        {{-- Mobile Logo --}}
                        <div class="lg:hidden flex items-center gap-3 mb-10">
                            <img src="{{ asset('assets/images/filomerkez_logo.png') }}" alt="FiloMERKEZ" class="h-10 w-10 rounded-xl object-cover border border-white/10" />
                            <span class="text-xl font-black tracking-tighter uppercase">FiloMERKEZ</span>
                        </div>

                        <div class="text-white">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
