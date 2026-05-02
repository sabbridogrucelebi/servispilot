@extends('layouts.app')

@section('title', 'Abonelik ve Ödeme')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <!-- Page header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-[0_8px_16px_-6px_rgba(99,102,241,0.5)] border border-white/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">Abonelik ve Ödeme</h1>
                    <p class="text-sm font-medium text-slate-500 mt-1">Paketinizi seçin, ödemenizi yapın ve sınırsız özelliklerin tadını çıkarın.</p>
                </div>
            </div>
        </div>
    </div>

    @if(!$activeSubscription)
        <div class="bg-rose-50 border border-rose-200 rounded-[24px] p-6 mb-8 flex items-start gap-4">
            <div class="w-10 h-10 bg-rose-100 rounded-full flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <h3 class="text-rose-800 font-bold">Aboneliğiniz Aktif Değil!</h3>
                <p class="text-rose-700 text-sm">Sistemi kullanmaya devam etmek için lütfen aşağıdan bir paket seçip ödemenizi yapın.</p>
            </div>
        </div>
    @else
        <div class="bg-indigo-600 rounded-[32px] p-8 mb-12 text-white shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-10">
                <svg class="w-48 h-48" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L1 21h22L12 2z"></path></svg>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-8">
                <div>
                    <div class="text-indigo-200 text-xs font-bold uppercase tracking-wider mb-2">Mevcut Paketiniz</div>
                    <h2 class="text-4xl font-black mb-2">{{ $activeSubscription->plan->name }}</h2>
                    <div class="flex items-center gap-4 text-indigo-100 font-medium">
                        <span class="flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> Yenileme: {{ $activeSubscription->ends_at ? $activeSubscription->ends_at->format('d.m.Y') : 'Süresiz' }}</span>
                        <span class="flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Aktif</span>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20">
                    <div class="text-xs font-bold uppercase mb-3 text-indigo-100">Kota Kullanımı</div>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-xs font-bold mb-1">
                                <span>Araç Kotası</span>
                                <span>{{ $company->vehicles()->count() }} / {{ $activeSubscription->plan->max_vehicles }}</span>
                            </div>
                            <div class="w-48 h-1.5 bg-white/20 rounded-full">
                                <div class="h-full bg-white rounded-full" style="width: {{ ($company->vehicles()->count() / $activeSubscription->plan->max_vehicles) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Pricing Cards -->
    <div class="mb-16">
        <h2 class="text-2xl font-black text-slate-800 mb-8 flex items-center gap-2">
            <span class="w-2 h-8 bg-indigo-500 rounded-full"></span>
            Paketler
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($plans as $plan)
                <div class="bg-white/70 backdrop-blur-md p-8 rounded-[40px] border {{ $plan->is_popular ? 'border-indigo-500 ring-4 ring-indigo-500/10' : 'border-slate-200/60' }} shadow-xl relative transition-all hover:-translate-y-2 group">
                    @if($plan->is_popular)
                        <div class="absolute top-0 right-8 -translate-y-1/2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-4 py-1 rounded-full text-xs font-black shadow-lg">EN POPÜLER</div>
                    @endif
                    <div class="mb-6">
                        <h3 class="text-2xl font-black text-slate-800 mb-2">{{ $plan->name }}</h3>
                        <p class="text-slate-500 text-sm h-12 overflow-hidden">{{ $plan->description }}</p>
                    </div>
                    <div class="mb-8">
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black text-slate-800">{{ number_format($plan->price, 0) }}</span>
                            <span class="text-lg font-bold text-slate-400">{{ $plan->currency }} / ay</span>
                        </div>
                        <div class="text-xs font-bold text-indigo-500 mt-1">Yıllık {{ number_format($plan->yearly_price, 0) }} {{ $plan->currency }}</div>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center gap-3 text-sm font-bold text-slate-600">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            {{ $plan->max_vehicles }} Araç Kapasitesi
                        </li>
                        <li class="flex items-center gap-3 text-sm font-bold text-slate-600">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            {{ $plan->max_users }} Kullanıcı
                        </li>
                        @foreach($plan->features ?? [] as $feature)
                            <li class="flex items-center gap-3 text-sm font-bold text-slate-600">
                                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                    <form action="{{ route('billing.plans.select', $plan) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-4 rounded-[20px] font-black transition-all shadow-lg {{ $plan->is_popular ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-indigo-500/30' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} hover:shadow-xl active:scale-95">
                            Paketi Seç
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Past Invoices -->
    <div class="bg-white/70 backdrop-blur-md shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] rounded-[32px] border border-slate-200/60 overflow-hidden relative">
        <div class="p-6 border-b border-slate-200/60 flex items-center justify-between">
            <h2 class="text-xl font-black text-slate-800">Geçmiş Faturalar ve Ödemeler</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead class="text-xs uppercase text-slate-400 bg-slate-50/50">
                    <tr>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Fatura No</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Tutar</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Tarih</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Durum</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-right">İşlem</div></th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200/60">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap font-bold text-slate-800">{{ $invoice->invoice_no }}</td>
                            <td class="px-5 py-4 whitespace-nowrap font-bold text-indigo-600">{{ number_format($invoice->amount, 2) }} {{ $invoice->currency }}</td>
                            <td class="px-5 py-4 whitespace-nowrap font-medium text-slate-500">{{ $invoice->created_at->format('d.m.Y') }}</td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($invoice->status === 'paid')
                                    <span class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-full text-[11px] font-bold">Ödendi</span>
                                @elseif($invoice->status === 'pending_approval')
                                    <span class="bg-amber-100 text-amber-600 px-3 py-1 rounded-full text-[11px] font-bold">Onay Bekliyor</span>
                                @elseif($invoice->status === 'rejected')
                                    <span class="bg-rose-100 text-rose-600 px-3 py-1 rounded-full text-[11px] font-bold">Reddedildi</span>
                                @else
                                    <span class="bg-slate-100 text-slate-400 px-3 py-1 rounded-full text-[11px] font-bold">Ödenmedi</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right font-medium">
                                <a href="{{ route('billing.invoice', $invoice) }}" class="text-indigo-500 hover:text-indigo-600 font-bold">Detay ve Ödeme</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-500">Geçmiş ödemeniz bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
