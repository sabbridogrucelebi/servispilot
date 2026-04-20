<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $vehicle->plate }} • Araç Fotoğraf Yükleme</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6">
        <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-xl">
            <div class="border-b border-slate-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-2xl">📷</div>
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900">Araç Fotoğraf Yükleme</h1>
                        <p class="mt-1 text-sm text-slate-500">
                            Araç: <span class="font-semibold text-slate-700">{{ $vehicle->plate }}</span>
                            @if($vehicle->brand || $vehicle->model)
                                • {{ trim(($vehicle->brand ?? '') . ' ' . ($vehicle->model ?? '')) }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="mx-6 mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mx-6 mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <div class="font-semibold">Lütfen alanları kontrol et.</div>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="p-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach($imageTypeOptions as $key => $label)
                        <form action="{{ route('vehicles.public-images.store', ['vehicle' => $vehicle->id, 'token' => $token]) }}"
                              method="POST"
                              enctype="multipart/form-data"
                              class="rounded-[24px] border border-slate-200 bg-slate-50 p-4">
                            @csrf

                            <input type="hidden" name="image_type" value="{{ $key }}">

                            <div class="text-base font-bold text-slate-900">{{ $label }}</div>
                            <div class="mt-1 text-sm text-slate-500">{{ $label }} çekiniz ve yükleyiniz.</div>

                            <div class="mt-4">
                                <label class="mb-2 block text-sm font-semibold text-slate-700">Fotoğraf Seç / Kamera Aç</label>
                                <input type="file"
                                       name="image"
                                       accept="image/*"
                                       capture="environment"
                                       required
                                       class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none">
                            </div>

                            <button type="submit"
                                    class="mt-4 w-full rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-3 text-sm font-semibold text-white shadow">
                                Yükle
                            </button>
                        </form>
                    @endforeach
                </div>

                <div class="mt-6 rounded-2xl bg-amber-50 p-4 text-sm text-amber-700">
                    Fotoğrafı çektikten sonra <span class="font-semibold">Yükle</span> butonuna basın. Başarıyla yüklenince sistemde araca otomatik işlenir.
                </div>
            </div>
        </div>
    </div>
</body>
</html>