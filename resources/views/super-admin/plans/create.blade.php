@extends('layouts.super-admin')

@section('title', 'Yeni Paket Ekle')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-4xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('super-admin.plans.index') }}" class="flex items-center text-sm font-bold text-slate-500 hover:text-indigo-500 transition-colors mb-4">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Geri Dön
        </a>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Yeni Paket Ekle</h1>
    </div>

    <div class="bg-white/70 backdrop-blur-md rounded-[32px] p-8 border border-slate-200/60 shadow-xl">
        <form action="{{ route('super-admin.plans.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Paket Adı</label>
                    <input type="text" name="name" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Sıralama (Opsiyonel)</label>
                    <input type="number" name="sort_order" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200" value="0">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Açıklama</label>
                <textarea name="description" rows="3" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Aylık Fiyat (TL)</label>
                    <input type="number" step="0.01" name="price" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Yıllık Fiyat (TL)</label>
                    <input type="number" step="0.01" name="yearly_price" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Maksimum Araç</label>
                    <input type="number" name="max_vehicles" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Maksimum Kullanıcı</label>
                    <input type="number" name="max_users" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200" required>
                </div>
            </div>

            <div class="flex gap-6 mb-8">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded text-indigo-600 focus:ring-indigo-500" checked>
                    <span class="text-sm font-bold text-slate-700">Aktif Mi?</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_popular" value="0">
                    <input type="checkbox" name="is_popular" value="1" class="rounded text-amber-500 focus:ring-amber-500">
                    <span class="text-sm font-bold text-slate-700">Popüler Etiketi?</span>
                </label>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-black rounded-xl shadow-lg hover:-translate-y-1 transition-all active:scale-95">
                    PAKETİ KAYDET
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
