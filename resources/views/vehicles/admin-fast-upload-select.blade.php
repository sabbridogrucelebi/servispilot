@extends('layouts.app')

@section('title', 'Admin Hızlı Resim Yükleme')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white/90 backdrop-blur rounded-[32px] p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-200/60 relative overflow-hidden">
        
        <!-- Decoration -->
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>
        <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-blue-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

        <div class="relative z-10 text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 text-indigo-600 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-800">Admin Araç Seçimi</h2>
            <p class="mt-2 text-sm text-slate-500">Resim yüklemek istediğiniz aracı seçin ve hızlı yükleme ekranına gidin.</p>
        </div>

        <form action="#" onsubmit="event.preventDefault(); goToVehicle();" class="relative z-10">
            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Araç Plakası</label>
                <select id="vehicle_select" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-4 text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 outline-none text-lg transition-all">
                    <option value="">Araç Seçiniz...</option>
                    @foreach($vehicles as $v)
                        <option value="{{ route('vehicles.public-images.form', ['vehicle' => $v->id, 'token' => $v->public_image_upload_token]) }}">
                            {{ $v->plate }} @if($v->brand) ({{ $v->brand }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-4 py-4 text-lg font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:scale-[1.02] transition-all duration-300">
                <span>Yükleme Ekranına Git</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </form>
    </div>
</div>

<script>
function goToVehicle() {
    var sel = document.getElementById('vehicle_select');
    if(sel.value) {
        window.location.href = sel.value;
    } else {
        alert('Lütfen araç seçiniz');
    }
}
</script>
@endsection
