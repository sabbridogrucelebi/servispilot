<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ServisPilot') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 antialiased">
    <div class="relative min-h-screen overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.25),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(168,85,247,0.2),_transparent_25%)]"></div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl items-center px-6 py-10 lg:px-8">
            <div class="grid w-full grid-cols-1 gap-10 lg:grid-cols-2 lg:items-center">
                <div class="hidden lg:block">
                    <div class="max-w-xl">
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-blue-200">
                            ServisPilot
                        </div>

                        <h1 class="mt-6 text-5xl font-black leading-tight text-white">
                            Taşımacılık operasyonunu
                            <span class="bg-gradient-to-r from-blue-400 to-cyan-300 bg-clip-text text-transparent">
                                tek merkezden
                            </span>
                            yönetin.
                        </h1>

                        <p class="mt-5 text-base leading-8 text-slate-300">
                            Araç, personel, müşteri, sözleşme ve fatura süreçlerini tek panelden yönetin.
                            Yönetici ve müşteri girişleri için ayrı deneyim sunun.
                        </p>

                        <div class="mt-8 grid grid-cols-2 gap-4">
                            <div class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                                <div class="text-2xl">🏢</div>
                                <div class="mt-3 text-sm font-bold text-white">Müşteri Portalı</div>
                                <div class="mt-1 text-xs leading-6 text-slate-400">Müşteriler sadece kendilerine ait ekranı görür.</div>
                            </div>

                            <div class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                                <div class="text-2xl">🚌</div>
                                <div class="mt-3 text-sm font-bold text-white">Operasyon Paneli</div>
                                <div class="mt-1 text-xs leading-6 text-slate-400">Yönetim ve operasyon ekibi için güçlü kontrol alanı.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full">
                    <div class="mx-auto w-full max-w-xl rounded-[32px] border border-white/10 bg-white/95 p-6 shadow-2xl shadow-black/30 backdrop-blur md:p-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>