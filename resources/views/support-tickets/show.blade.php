@extends('layouts.app')

@section('title', 'Destek Talebi Detayı')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-5xl mx-auto">

    <div class="mb-8">
        <div class="flex items-center mb-6">
            <a href="{{ route('support-tickets.index') }}" class="w-10 h-10 flex items-center justify-center bg-white border border-slate-200/60 rounded-xl hover:bg-slate-50 transition shadow-sm mr-4 group">
                <svg class="w-5 h-5 fill-slate-500 group-hover:-translate-x-0.5 transition-transform" viewBox="0 0 16 16"><path d="M6.6 13.4L5.2 12l4-4-4-4 1.4-1.4L12 8z" transform="scale(-1 1) translate(-16 0)"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl md:text-3xl text-slate-800 font-extrabold tracking-tight">Talep #{{ str_pad($supportTicket->id, 5, '0', STR_PAD_LEFT) }}</h1>
                <div class="flex flex-wrap items-center gap-3 mt-2">
                    @if($supportTicket->status === 'open')
                        <span class="bg-amber-100 text-amber-600 px-3 py-1 rounded-full text-xs font-bold border border-amber-200">Açık (Yanıt Bekliyor)</span>
                    @elseif($supportTicket->status === 'answered')
                        <span class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-full text-xs font-bold border border-emerald-200">Yanıtlandı</span>
                    @else
                        <span class="bg-slate-100 text-slate-500 px-3 py-1 rounded-full text-xs font-bold border border-slate-200">Kapatıldı</span>
                    @endif

                    <span class="text-slate-300 text-sm font-bold">•</span>
                    <span class="text-slate-600 text-sm font-bold">{{ $supportTicket->subject }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages Container -->
    <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="p-6 space-y-6 max-h-[600px] overflow-y-auto">
            @foreach($supportTicket->messages->sortBy('created_at') as $msg)
                
                @if($msg->is_super_admin)
                    <!-- Admin Message (Left) -->
                    <div class="flex w-full max-w-3xl">
                        <div class="flex-shrink-0 mr-4">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-md">
                                S
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-slate-800 mb-1">Sistem Destek Ekibi</div>
                            <div class="bg-slate-100 text-slate-800 p-4 rounded-2xl rounded-tl-none shadow-sm">
                                {!! nl2br(e($msg->message)) !!}
                                
                                @if($msg->file_path)
                                    <div class="mt-3">
                                        <a href="{{ Storage::url($msg->file_path) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm text-indigo-600 hover:text-indigo-700 transition shadow-sm">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                            Eki Görüntüle
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="text-xs text-slate-400 mt-1">{{ $msg->created_at->format('d.m.Y H:i') }}</div>
                        </div>
                    </div>
                @else
                    <!-- Tenant Message (Right) -->
                    <div class="flex w-full max-w-3xl ml-auto justify-end">
                        <div class="text-right">
                            <div class="text-sm font-medium text-slate-800 mb-1">{{ $msg->user->name ?? 'Siz' }}</div>
                            <div class="bg-indigo-500 text-white p-4 rounded-2xl rounded-tr-none shadow-sm text-left">
                                {!! nl2br(e($msg->message)) !!}

                                @if($msg->file_path)
                                    <div class="mt-3">
                                        <a href="{{ Storage::url($msg->file_path) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-indigo-600/50 border border-indigo-400/30 rounded-lg text-sm text-white hover:bg-indigo-600 transition">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                            Yüklediğiniz Ek
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="text-xs text-slate-400 mt-1">{{ $msg->created_at->format('d.m.Y H:i') }}</div>
                        </div>
                        <div class="flex-shrink-0 ml-4">
                            <div class="w-10 h-10 rounded-full bg-slate-200 border border-slate-300 flex items-center justify-center text-slate-600 font-bold shadow-sm">
                                {{ strtoupper(substr($msg->user->name ?? 'S', 0, 1)) }}
                            </div>
                        </div>
                    </div>
                @endif

            @endforeach
        </div>
    </div>

    <!-- Reply Form -->
    @if($supportTicket->status !== 'closed')
        <div class="bg-white/70 backdrop-blur-md shadow-[0_20px_40px_-15px_rgba(0,0,0,0.05)] rounded-3xl border border-slate-200/60 p-6 sm:p-8">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 text-white mr-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-xl font-extrabold text-slate-800">Yanıt Yaz</h3>
            </div>
            <form action="{{ route('support-tickets.reply', $supportTicket) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-6">
                    <textarea name="message" class="form-textarea w-full bg-slate-50/50 border-slate-200/60 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500/30 transition-all shadow-inner" rows="4" placeholder="Mesajınızı buraya yazın..." required></textarea>
                </div>
                
                <div class="sm:flex sm:items-center sm:justify-between pt-4 border-t border-slate-200/60">
                    <div>
                        <input id="file" name="file" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition cursor-pointer" type="file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" />
                    </div>
                    <div class="mt-4 sm:mt-0">
                        <button type="submit" class="group relative px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold rounded-xl shadow-[0_6px_0_rgb(67,56,202)] active:shadow-none active:translate-y-[6px] transition-all hover:brightness-110 flex items-center">
                            <span>Gönder</span>
                            <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @else
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-6 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 mb-4">
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">Bu Talep Kapatılmış</h3>
            <p class="text-slate-500 text-sm">Bu destek talebi çözüme ulaştığı için kapatılmıştır. Yeni bir sorununuz varsa lütfen yeni bir talep oluşturun.</p>
            <div class="mt-4">
                <a href="{{ route('support-tickets.create') }}" class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-600">Yeni Talep Aç</a>
            </div>
        </div>
    @endif

</div>
@endsection
