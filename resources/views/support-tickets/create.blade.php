@extends('layouts.app')

@section('title', 'Yeni Destek Talebi')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-3xl mx-auto">

    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('support-tickets.index') }}" class="flex items-center justify-center w-10 h-10 bg-white border border-slate-200/60 rounded-xl hover:bg-slate-50 transition-all shadow-sm group">
                    <svg class="w-5 h-5 text-slate-500 group-hover:-translate-x-0.5 transition-transform" viewBox="0 0 16 16"><path fill="currentColor" d="M6.6 13.4L5.2 12l4-4-4-4 1.4-1.4L12 8z" transform="scale(-1 1) translate(-16 0)"></path></svg>
                </a>
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-[0_8px_16px_-6px_rgba(99,102,241,0.5)] border border-white/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">Yeni Destek Talebi</h1>
                    <p class="text-sm font-medium text-slate-500 mt-1">Yaşadığınız problemi detaylıca anlatın, destek ekibimiz en kısa sürede size dönüş yapacaktır.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white/70 backdrop-blur-md shadow-[0_20px_40px_-15px_rgba(0,0,0,0.05)] rounded-[32px] border border-slate-200/60 overflow-hidden relative">
        <form action="{{ route('support-tickets.store') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-10">
            @csrf

            <div class="space-y-8">
                <!-- Konu -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2" for="subject">Konu Başlığı <span class="text-rose-500">*</span></label>
                    <input id="subject" name="subject" class="form-input w-full bg-slate-50/50 border-slate-200/60 rounded-xl p-4 font-medium focus:ring-2 focus:ring-indigo-500/30 transition-all shadow-inner" type="text" placeholder="Örn: Araç eklerken hata alıyorum" required value="{{ old('subject') }}" />
                </div>

                <!-- Öncelik -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2" for="priority">Öncelik Durumu <span class="text-rose-500">*</span></label>
                    <select id="priority" name="priority" class="form-select w-full bg-slate-50/50 border-slate-200/60 rounded-xl p-4 font-medium focus:ring-2 focus:ring-indigo-500/30 transition-all shadow-sm" required>
                        <option value="low">Düşük (Sistemi engellemeyen basit hatalar/sorular)</option>
                        <option value="normal" selected>Normal (Standart destek talebi)</option>
                        <option value="high">Yüksek (Bazı modülleri kullanamıyorum)</option>
                        <option value="urgent">Acil (Sistem tamamen durdu, işlem yapılamıyor)</option>
                    </select>
                </div>

                <!-- Mesaj -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2" for="message">Mesajınız <span class="text-rose-500">*</span></label>
                    <textarea id="message" name="message" class="form-textarea w-full bg-slate-50/50 border-slate-200/60 rounded-2xl p-4 font-medium focus:ring-2 focus:ring-indigo-500/30 transition-all shadow-inner" rows="6" placeholder="Lütfen karşılaştığınız sorunu adımlarıyla birlikte anlatın..." required>{{ old('message') }}</textarea>
                </div>

                <!-- Dosya Yükleme -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2" for="file">Ekran Görüntüsü / Dosya (İsteğe Bağlı)</label>
                    <input id="file" name="file" class="block w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition cursor-pointer" type="file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" />
                    <div class="text-xs font-medium text-slate-400 mt-3">Maksimum 10MB. İzin verilen formatlar: JPG, PNG, PDF, DOC.</div>
                </div>
            </div>

            <div class="mt-10 pt-8 border-t border-slate-200/60 flex justify-end">
                <button type="submit" class="group relative px-8 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold rounded-xl shadow-[0_6px_0_rgb(67,56,202)] active:shadow-none active:translate-y-[6px] transition-all hover:brightness-110 flex items-center text-lg">
                    <span>Talebi Gönder</span>
                    <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
