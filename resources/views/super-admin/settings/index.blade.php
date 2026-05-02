@extends('layouts.super-admin')

@section('title', 'Sistem Ayarları')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold tracking-tight">Sistem Ayarları</h1>
        </div>
    </div>

    <!-- Content -->
    <div class="bg-white dark:bg-slate-800 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] rounded-[32px] mb-8 border border-slate-200 dark:border-slate-700/60 overflow-hidden">
        <div class="flex flex-col md:flex-row md:-mr-px">
            
            <!-- Sidebar -->
            <div class="flex-nowrap overflow-x-scroll no-scrollbar md:block md:overflow-auto px-3 py-6 border-b md:border-b-0 md:border-r border-slate-200 dark:border-slate-700/60 min-w-60 md:space-y-3">
                
                <div>
                    <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase pt-1 pb-2 px-3">Ayarlar</div>
                    <ul class="flex flex-nowrap md:block mr-3 md:mr-0">
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                            <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap bg-indigo-50 dark:bg-indigo-500/10 text-indigo-500 dark:text-indigo-400" href="#general">
                                <svg class="w-4 h-4 shrink-0 fill-current mr-2" viewBox="0 0 16 16">
                                    <path d="M8 1.928A6.072 6.072 0 1014.072 8 6.072 6.072 0 008 1.928zM.328 8A7.672 7.672 0 1115.672 8 7.672 7.672 0 01.328 8z" />
                                </svg>
                                <span class="text-sm font-medium">Genel Platform</span>
                            </a>
                        </li>
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                            <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/20" href="#smtp">
                                <svg class="w-4 h-4 shrink-0 fill-current mr-2" viewBox="0 0 16 16">
                                    <path d="M14.682 2.318A4.485 4.485 0 0011.5 1 4.377 4.377 0 008 2.28 4.377 4.377 0 004.5 1a4.485 4.485 0 00-3.182 1.318M8 15.353A2.88 2.88 0 009.615 14L15 8V4.5A2.5 2.5 0 0012.5 2h-9A2.5 2.5 0 001 4.5V8l5.385 6A2.88 2.88 0 008 15.353z" />
                                </svg>
                                <span class="text-sm font-medium">Email (SMTP)</span>
                            </a>
                        </li>
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                            <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/20" href="#bank">
                                <svg class="w-4 h-4 shrink-0 fill-current mr-2" viewBox="0 0 16 16">
                                    <path d="M8 1.928A6.072 6.072 0 1014.072 8 6.072 6.072 0 008 1.928zM.328 8A7.672 7.672 0 1115.672 8 7.672 7.672 0 01.328 8z" />
                                </svg>
                                <span class="text-sm font-medium">Banka Bilgileri</span>
                            </a>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Panel -->
            <div class="grow">
                <form action="{{ route('super-admin.settings.update') }}" method="POST">
                    @csrf
                    
                    <!-- Panel body -->
                    <div class="p-6 space-y-6">
                        
                        <!-- General -->
                        <section id="general">
                            <h2 class="text-xl leading-snug text-slate-800 dark:text-slate-100 font-bold mb-4">Genel Platform Ayarları</h2>
                            <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                <div class="sm:w-1/3">
                                    <label class="block text-sm font-medium mb-1" for="platform_name">Platform Adı</label>
                                    <input id="platform_name" name="platform_name" class="form-input w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700/60 rounded-xl" type="text" value="{{ $settings['general']->where('key', 'platform_name')->first()->value ?? 'FiloMERKEZ' }}" />
                                </div>
                                <div class="sm:w-1/3">
                                    <label class="block text-sm font-medium mb-1" for="support_email">Destek Email</label>
                                    <input id="support_email" name="support_email" class="form-input w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700/60 rounded-xl" type="email" value="{{ $settings['general']->where('key', 'support_email')->first()->value ?? 'destek@FiloMERKEZ.com' }}" />
                                </div>
                            </div>
                        </section>

                        <hr class="border-slate-200 dark:border-slate-700/60">

                        <!-- SMTP -->
                        <section id="smtp">
                            <h2 class="text-xl leading-snug text-slate-800 dark:text-slate-100 font-bold mb-4">Email (SMTP) Ayarları</h2>
                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="smtp_host">SMTP Host</label>
                                    <input id="smtp_host" name="smtp_host" class="form-input w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700/60 rounded-xl" type="text" value="{{ $settings['smtp']->where('key', 'smtp_host')->first()->value ?? '' }}" placeholder="mail.siteadi.com" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="smtp_port">SMTP Port</label>
                                    <input id="smtp_port" name="smtp_port" class="form-input w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700/60 rounded-xl" type="text" value="{{ $settings['smtp']->where('key', 'smtp_port')->first()->value ?? '587' }}" placeholder="587 veya 465" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="smtp_user">SMTP Kullanıcı</label>
                                    <input id="smtp_user" name="smtp_user" class="form-input w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700/60 rounded-xl" type="text" value="{{ $settings['smtp']->where('key', 'smtp_user')->first()->value ?? '' }}" placeholder="info@siteadi.com" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="smtp_pass">SMTP Şifre</label>
                                    <input id="smtp_pass" name="smtp_pass" class="form-input w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700/60 rounded-xl" type="password" value="{{ $settings['smtp']->where('key', 'smtp_pass')->first()->value ?? '' }}" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="smtp_encryption">Şifreleme (Encryption)</label>
                                    <select id="smtp_encryption" name="smtp_encryption" class="form-select w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700/60 rounded-xl">
                                        <option value="tls" {{ ($settings['smtp']->where('key', 'smtp_encryption')->first()->value ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS (Önerilen - 587)</option>
                                        <option value="ssl" {{ ($settings['smtp']->where('key', 'smtp_encryption')->first()->value ?? '') == 'ssl' ? 'selected' : '' }}>SSL (465)</option>
                                        <option value="null" {{ ($settings['smtp']->where('key', 'smtp_encryption')->first()->value ?? '') == 'null' ? 'selected' : '' }}>Yok</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="smtp_from_address">Gönderen E-Posta</label>
                                    <input id="smtp_from_address" name="smtp_from_address" class="form-input w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700/60 rounded-xl" type="email" value="{{ $settings['smtp']->where('key', 'smtp_from_address')->first()->value ?? '' }}" placeholder="info@filormerkez.com" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium mb-1" for="smtp_from_name">Gönderen Adı</label>
                                    <input id="smtp_from_name" name="smtp_from_name" class="form-input w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700/60 rounded-xl" type="text" value="{{ $settings['smtp']->where('key', 'smtp_from_name')->first()->value ?? 'FiloMERKEZ' }}" />
                                </div>
                            </div>
                        </section>
                        <hr class="border-slate-200 dark:border-slate-700/60">

                        <!-- Bank Settings -->
                        <section id="bank">
                            <h2 class="text-xl leading-snug text-slate-800 dark:text-slate-100 font-bold mb-4">Banka Hesap Bilgileri (Havale/EFT)</h2>
                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="bank_name">Banka Adı</label>
                                    <input id="bank_name" name="bank_name" class="form-input w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700/60 rounded-xl" type="text" value="{{ $settings['bank']->where('key', 'bank_name')->first()->value ?? '' }}" placeholder="Örn: Garanti BBVA" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="bank_account_holder">Hesap Sahibi (Alıcı Adı)</label>
                                    <input id="bank_account_holder" name="bank_account_holder" class="form-input w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700/60 rounded-xl" type="text" value="{{ $settings['bank']->where('key', 'bank_account_holder')->first()->value ?? '' }}" placeholder="Örn: FiloMERKEZ Yazılım A.Ş." />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium mb-1" for="bank_iban">IBAN Numarası</label>
                                    <input id="bank_iban" name="bank_iban" class="form-input w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700/60 rounded-xl" type="text" value="{{ $settings['bank']->where('key', 'bank_iban')->first()->value ?? '' }}" placeholder="Örn: TR00 0000 0000 0000 0000 0000 00" />
                                </div>
                            </div>
                        </section>

                    </div>

                    <!-- Panel footer -->
                    <footer>
                        <div class="flex flex-col px-6 py-5 border-t border-slate-200 dark:border-slate-700/60">
                            <div class="flex self-end">
                                <button type="submit" class="btn bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl shadow-lg shadow-indigo-500/30 transition-all duration-300 hover:shadow-indigo-500/50 hover:-translate-y-0.5">Ayarları Kaydet</button>
                            </div>
                        </div>
                    </footer>
                </form>
            </div>
            
        </div>
    </div>

</div>
@endsection
