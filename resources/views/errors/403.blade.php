<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yetkisiz Erişim</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-2xl p-10 max-w-lg w-full text-center">
        <h1 class="text-4xl font-bold text-red-600 mb-4">403</h1>
        <h2 class="text-2xl font-semibold mb-3">Yetkisiz Erişim</h2>
        <p class="text-gray-600 mb-6">
            Bu sayfayı görüntüleme yetkin bulunmuyor.
        </p>

        <a href="{{ route('dashboard') }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-3 rounded-lg shadow">
            Dashboard'a Dön
        </a>
    </div>
</body>
</html>