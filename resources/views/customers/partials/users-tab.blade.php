@php
    $customerUsers = $customerUsers ?? collect();
@endphp

<div class="grid grid-cols-1 gap-6 2xl:grid-cols-12">

    <div class="2xl:col-span-4 overflow-hidden rounded-[30px] border border-slate-200/70 bg-white shadow-lg shadow-slate-200/40">
        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-lg font-bold text-slate-900">Yeni Müşteri Kullanıcısı</h3>
            <p class="mt-1 text-sm text-slate-500">
                Bu firmaya özel giriş yapabilecek kullanıcı oluşturun
            </p>
        </div>

        <form action="{{ route('customers.portal-users.store', $customer) }}" method="POST" class="space-y-5 px-6 py-6">
            @csrf

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                    Ad Soyad
                </label>
                <input type="text"
                       name="name"
                       value="{{ old('name') }}"
                       placeholder="Kullanıcı adı soyadı"
                       class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                @error('name')
                    <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                    Kullanıcı Adı
                </label>
                <input type="text"
                       name="username"
                       value="{{ old('username') }}"
                       placeholder="koraks.portal"
                       class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                @error('username')
                    <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                    E-Posta
                </label>
                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="kullanici@firma.com"
                       class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                @error('email')
                    <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                    Şifre
                </label>
                <input type="password"
                       name="password"
                       placeholder="••••••••"
                       class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                @error('password')
                    <div class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="inline-flex items-center gap-3">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-semibold text-slate-700">Kullanıcı aktif olsun</span>
                </label>
            </div>

            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4">
                <div class="text-sm font-semibold text-slate-700">Bilgilendirme</div>
                <div class="mt-1 text-xs leading-6 text-slate-500">
                    Bu kullanıcı giriş yaptığında sadece kendi firmasına ait müşteri portalını görecek.
                </div>
            </div>

            <button type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:bg-slate-800">
                <span>👤</span>
                <span>Kullanıcı Kaydet</span>
            </button>
        </form>
    </div>

    <div class="2xl:col-span-8 overflow-hidden rounded-[30px] border border-slate-200/70 bg-white shadow-lg shadow-slate-200/40">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Portal Kullanıcıları</h3>
                <p class="mt-1 text-sm text-slate-500">Bu firmaya giriş yetkisi olan kullanıcılar</p>
            </div>

            <div class="text-sm font-medium text-slate-400">
                Toplam {{ $customerUsers->count() }} kayıt
            </div>
        </div>

        <div class="p-6">
            @if($customerUsers->count())
                <div class="space-y-4">
                    @foreach($customerUsers as $portalUser)
                        <details class="group rounded-[26px] border border-slate-200 bg-slate-50/70 p-5 shadow-sm">
                            <summary class="list-none cursor-pointer">
                                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                                    <div class="flex items-start gap-4">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-500 text-xl font-bold text-white shadow">
                                            {{ strtoupper(substr($portalUser->name ?? 'U', 0, 1)) }}
                                        </div>

                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <div class="text-lg font-bold text-slate-900">
                                                    {{ $portalUser->name }}
                                                </div>

                                                @if($portalUser->is_active)
                                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                                        Aktif
                                                    </span>
                                                @else
                                                    <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">
                                                        Pasif
                                                    </span>
                                                @endif

                                                <span class="inline-flex rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">
                                                    Portal Kullanıcısı
                                                </span>
                                            </div>

                                            <div class="mt-3 grid grid-cols-1 gap-3 text-sm text-slate-600 md:grid-cols-3">
                                                <div>
                                                    <span class="font-semibold text-slate-800">Kullanıcı Adı:</span>
                                                    {{ $portalUser->username ?: '-' }}
                                                </div>

                                                <div>
                                                    <span class="font-semibold text-slate-800">E-Posta:</span>
                                                    {{ $portalUser->email ?: '-' }}
                                                </div>

                                                <div>
                                                    <span class="font-semibold text-slate-800">Son Durum:</span>
                                                    {{ $portalUser->is_active ? 'Giriş yapabilir' : 'Pasif durumda' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-2 rounded-2xl bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700">
                                            <span>✏️</span>
                                            <span>Düzenle</span>
                                        </span>

                                        <form action="{{ route('customers.portal-users.toggle-status', [$customer, $portalUser]) }}" method="POST" onclick="event.stopPropagation();" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-2 rounded-2xl bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">
                                                <span>{{ $portalUser->is_active ? '🔒' : '✅' }}</span>
                                                <span>{{ $portalUser->is_active ? 'Pasif Yap' : 'Aktif Et' }}</span>
                                            </button>
                                        </form>

                                        <form action="{{ route('customers.portal-users.destroy', [$customer, $portalUser]) }}" method="POST" onclick="event.stopPropagation();" class="inline" onsubmit="return confirm('Bu portal kullanıcısını silmek istediğine emin misin?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-2 rounded-2xl bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                                                <span>🗑️</span>
                                                <span>Sil</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </summary>

                            <div class="mt-5 border-t border-slate-200 pt-5">
                                <div class="mb-4">
                                    <h4 class="text-sm font-bold text-slate-900">Kullanıcıyı Düzenle</h4>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Kullanıcı bilgilerini güncelleyin. Şifreyi boş bırakırsanız mevcut şifre korunur.
                                    </p>
                                </div>

                                <form action="{{ route('customers.portal-users.update', [$customer, $portalUser]) }}" method="POST" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    @csrf
                                    @method('PUT')

                                    <div>
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                            Ad Soyad
                                        </label>
                                        <input type="text"
                                               name="name"
                                               value="{{ old('name', $portalUser->name) }}"
                                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                            Kullanıcı Adı
                                        </label>
                                        <input type="text"
                                               name="username"
                                               value="{{ old('username', $portalUser->username) }}"
                                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                            E-Posta
                                        </label>
                                        <input type="email"
                                               name="email"
                                               value="{{ old('email', $portalUser->email) }}"
                                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                                            Yeni Şifre
                                        </label>
                                        <input type="password"
                                               name="password"
                                               placeholder="Değiştirmek istemiyorsanız boş bırakın"
                                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="inline-flex items-center gap-3">
                                            <input type="checkbox"
                                                   name="is_active"
                                                   value="1"
                                                   {{ old('is_active', $portalUser->is_active) ? 'checked' : '' }}
                                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm font-semibold text-slate-700">Kullanıcı aktif olsun</span>
                                        </label>
                                    </div>

                                    <div class="md:col-span-2 flex justify-end">
                                        <button type="submit"
                                                class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:bg-slate-800">
                                            <span>💾</span>
                                            <span>Güncelle</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </details>
                    @endforeach
                </div>
            @else
                <div class="px-6 py-14 text-center">
                    <div class="mx-auto max-w-md">
                        <div class="mb-3 text-5xl">👥</div>
                        <div class="text-base font-semibold text-slate-700">Henüz portal kullanıcısı yok</div>
                        <div class="mt-1 text-sm text-slate-500">
                            İlk kullanıcıyı oluşturduğunuzda müşteri kendi portalına giriş yapabilecek.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>