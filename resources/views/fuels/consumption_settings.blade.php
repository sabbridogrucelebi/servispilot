@extends('layouts.app')

@section('title', 'Yakıt Oran Belirleme')
@section('subtitle', 'Araç bazlı minimum ve maksimum yakıt tüketim limitleri')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between bg-white p-6 rounded-[24px] border border-slate-200/70 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-blue-50 text-indigo-600 shadow-inner">
                <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Objects/Gear.png" alt="Ayarlar" class="w-8 h-8 drop-shadow-sm" />
            </div>
            <div>
                <h2 class="text-xl font-black tracking-tight text-slate-800">Yakıt Oran Belirleme (KM / Litre)</h2>
                <p class="text-sm font-medium text-slate-500">Her aracın 1 litre yakıtla gidebileceği minimum ve maksimum KM limitlerini belirleyin.</p>
            </div>
        </div>
        <div>
            <a href="{{ route('fuels.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Geri Dön
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm">
            <div class="mb-2 font-semibold">Lütfen aşağıdaki hataları düzeltin:</div>
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-[24px] border border-slate-200/70 bg-white shadow-sm overflow-hidden">
        <form action="{{ route('fuels.consumption-settings.save') }}" method="POST">
            @csrf
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4 rounded-tl-[24px]">Araç Plakası / Bilgisi</th>
                            <th class="px-6 py-4 w-48">MİN. KM / LİTRE <br><span class="text-[10px] font-medium normal-case text-slate-400">(Bunun altına düşerse uyar)</span></th>
                            <th class="px-6 py-4 w-48 rounded-tr-[24px]">MAKS. KM / LİTRE <br><span class="text-[10px] font-medium normal-case text-slate-400">(Bunu geçerse uyar)</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($vehicles as $vehicle)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 font-bold border border-slate-200 shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                    </div>
                                    <div>
                                        <div class="font-black text-slate-800 text-base">{{ $vehicle->plate }}</div>
                                        <div class="text-xs font-medium text-slate-500">{{ $vehicle->brand }} {{ $vehicle->model }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="relative">
                                    <input type="number" step="0.01" min="0" name="vehicles[{{ $vehicle->id }}][min_km_per_liter]" value="{{ old('vehicles.'.$vehicle->id.'.min_km_per_liter', $vehicle->min_km_per_liter) }}" placeholder="Örn: 8.00" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 placeholder-slate-300 outline-none focus:border-rose-400 focus:ring-4 focus:ring-rose-50 transition-all">
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="relative">
                                    <input type="number" step="0.01" min="0" name="vehicles[{{ $vehicle->id }}][max_km_per_liter]" value="{{ old('vehicles.'.$vehicle->id.'.max_km_per_liter', $vehicle->max_km_per_liter) }}" placeholder="Örn: 11.00" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 placeholder-slate-300 outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-50 transition-all">
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-slate-500">
                                Kayıtlı araç bulunamadı.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-6 bg-slate-50 border-t border-slate-200 flex items-center justify-end">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 px-8 py-3.5 text-sm font-black text-white shadow-lg shadow-indigo-200 transition-all hover:scale-[1.02] hover:shadow-xl hover:shadow-indigo-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                    Tüm Ayarları Kaydet
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
