@extends('layouts.super-admin')

@section('title', 'Global Sistem Logları')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold tracking-tight">Platform Denetim Logları ✨</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Tüm firmalardaki aktiviteleri anlık olarak izleyin.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-slate-800 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] rounded-[24px] mb-8 border border-slate-200 dark:border-slate-700/60 p-5">
        <form action="{{ route('super-admin.logs.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Firma</label>
                <select name="company_id" class="form-select w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700/60 rounded-xl">
                    <option value="">Tümü</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Modül</label>
                <select name="module" class="form-select w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700/60 rounded-xl">
                    <option value="">Tümü</option>
                    <option value="vehicles" {{ request('module') == 'vehicles' ? 'selected' : '' }}>Araçlar</option>
                    <option value="users" {{ request('module') == 'users' ? 'selected' : '' }}>Kullanıcılar</option>
                    <option value="auth" {{ request('module') == 'auth' ? 'selected' : '' }}>Giriş/Çıkış</option>
                    <!-- Eklenebilir -->
                </select>
            </div>
            <div class="md:col-span-2 flex items-end justify-end space-x-2">
                <a href="{{ route('super-admin.logs.index') }}" class="btn border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 text-slate-600 dark:text-slate-300 rounded-xl">Sıfırla</a>
                <button type="submit" class="btn bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl shadow-lg shadow-indigo-500/30 transition-all duration-300 hover:shadow-indigo-500/50 hover:-translate-y-0.5">Filtrele</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-800 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] rounded-[32px] border border-slate-200 dark:border-slate-700/60 overflow-hidden relative">
        <div class="overflow-x-auto">
            <table class="table-auto w-full dark:text-slate-300">
                <thead class="text-xs uppercase text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-700/20 border-b border-slate-200 dark:border-slate-700/60">
                    <tr>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Zaman</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Firma</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Kullanıcı</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Modül</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">Aktivite</div></th>
                        <th class="px-5 py-4 whitespace-nowrap"><div class="font-semibold text-left">IP</div></th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200 dark:divide-slate-700/60">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap text-slate-500">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-5 py-4 whitespace-nowrap font-medium text-slate-800 dark:text-slate-100">
                                {{ $log->company->name ?? 'Sistem' }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                {{ $log->user->name ?? 'Bilinmeyen' }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2.5 py-1 rounded-full text-xs font-semibold">{{ $log->module }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300 max-w-md truncate" title="{{ $log->description }}">
                                <span class="font-medium">{{ $log->title }}</span>
                                <span class="text-slate-400 block text-xs truncate mt-0.5">{{ $log->description }}</span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-500">
                                {{ $log->ip_address }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-500 dark:text-slate-400">
                                Henüz hiç kayıt bulunmuyor.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $logs->links() }}
    </div>

</div>
@endsection
