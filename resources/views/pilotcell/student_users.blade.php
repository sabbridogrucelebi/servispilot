@extends('layouts.app')

@section('title', 'Öğrenci Kullanıcıları - ' . $student->name)

@section('content')
<div class="min-h-[calc(100vh-64px)] bg-slate-50 flex flex-col" x-data="userManager()">
    <!-- Üst Başlık -->
    <div class="bg-white border-b border-gray-100 p-6 flex items-center justify-between shadow-sm z-10">
        <div class="flex items-center gap-4">
            <a href="{{ route('pilotcell.school.routes.students.show', ['school_id' => $school->id, 'route_id' => $route->id, 'student_id' => $student->id]) }}" class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-100 flex shrink-0 items-center justify-center text-emerald-600 font-bold text-lg">
                        {{ mb_substr($student->name, 0, 1) }}
                    </div>
                    {{ $student->name }} - Veli Kullanıcıları
                </h1>
                <p class="text-sm text-slate-500 mt-1 ml-14">Bu öğrenciyi takip edebilecek mobil uygulama kullanıcıları.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showUserModal = true" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Kullanıcı Aç
            </button>
        </div>
    </div>

    <!-- İçerik -->
    <div class="flex-1 p-6 lg:p-8 max-w-5xl mx-auto w-full">
        @if(session('success'))
            <div class="mb-6 rounded-xl bg-emerald-50 p-4 border border-emerald-100 flex items-center gap-3 text-emerald-800">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-xl bg-red-50 p-4 border border-red-100 flex items-center gap-3 text-red-800">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <ul class="text-sm font-medium list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $users = collect([
                ['type' => '1. Veli', 'user' => $student->parent],
                ['type' => '2. Veli', 'user' => $student->parent2]
            ])->filter(fn($item) => $item['user'] !== null);
        @endphp

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            @if($users->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Veli Tipi</th>
                                <th class="px-6 py-4 font-semibold">Ad Soyad</th>
                                <th class="px-6 py-4 font-semibold">Kullanıcı Adı (Telefon)</th>
                                <th class="px-6 py-4 font-semibold text-center">Durum</th>
                                <th class="px-6 py-4 font-semibold text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($users as $item)
                                @php $user = $item['user']; @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-800">
                                        {{ $item['type'] }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-800">
                                        {{ $user->name }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-slate-600">
                                        {{ $user->username }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($user->is_active)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700 ring-1 ring-inset ring-red-600/20">
                                                Pasif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openEdit({{ $user->toJson() }})" class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors" title="Düzenle">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            </button>
                                            <form action="{{ route('pilotcell.school.routes.students.users.destroy', ['school_id' => $school->id, 'route_id' => $route->id, 'student_id' => $student->id, 'user_id' => $user->id]) }}" method="POST" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz? Kullanıcının sisteme girişi engellenecektir.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Sil">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center text-slate-500">
                    <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <p class="font-medium text-lg text-slate-700">Henüz kullanıcı hesabı oluşturulmamış.</p>
                    <p class="text-sm mt-1">Öğrencinin velileri için mobil uygulama erişimi sağlamak üzere sağ üstteki "Kullanıcı Aç" butonunu kullanın.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Kullanıcı Ekle Modal -->
    <template x-teleport="body">
        <div x-show="showUserModal" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showUserModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" @click="showUserModal = false" aria-hidden="true">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
                </div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div x-show="showUserModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block transform overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md sm:align-middle border border-slate-100">
                    <form action="{{ route('pilotcell.school.routes.students.users.store', ['school_id' => $school->id, 'route_id' => $route->id, 'student_id' => $student->id]) }}" method="POST">
                        @csrf
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-xl font-bold text-slate-800">Yeni Kullanıcı Aç</h3>
                            <button type="button" @click="showUserModal = false" class="text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 rounded-lg p-2 transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <div class="p-6 space-y-5 bg-slate-50/50">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Veli Seçimi (Ad Soyad)</label>
                                <select name="parent_selection" required x-model="selectedParent" @change="updateUsername()" class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                    <option value="">-- Veli Seçin --</option>
                                    @if($student->parent1_name)
                                        @if(!$student->parent)
                                            <option value="1">1. Veli - {{ $student->parent1_name }}</option>
                                        @endif
                                    @endif
                                    @if($student->parent2_name)
                                        @if(!$student->parent2)
                                            <option value="2">2. Veli - {{ $student->parent2_name }}</option>
                                        @endif
                                    @endif
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kullanıcı Adı</label>
                                <input type="text" name="username" required x-model="username" class="w-full rounded-xl border-slate-200 bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm cursor-not-allowed" readonly placeholder="Veli seçildiğinde otomatik dolar">
                                <p class="text-[10px] text-slate-500 mt-1">Kullanıcı adı velinin cep telefonu numarası olarak belirlenir.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Şifre Belirleyin</label>
                                <input type="text" name="password" required minlength="6" class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="En az 6 karakter">
                            </div>
                        </div>
                        <div class="bg-white px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100">
                            <button type="button" @click="showUserModal = false" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-200 transition-colors">İptal</button>
                            <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-colors">Kaydet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Kullanıcı Düzenle Modal -->
    <template x-teleport="body">
        <div x-show="editUser" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editUser" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" @click="editUser = null" aria-hidden="true">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
                </div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div x-show="editUser" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block transform overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md sm:align-middle border border-slate-100">
                    <form :action="'/pilotcell/school/{{ $school->id }}/routes/{{ $route->id }}/students/{{ $student->id }}/users/' + (editUser ? editUser.id : '')" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-xl font-bold text-slate-800">Kullanıcı Düzenle</h3>
                            <button type="button" @click="editUser = null" class="text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 rounded-lg p-2 transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <div class="p-6 space-y-5 bg-slate-50/50">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Ad Soyad</label>
                                <input type="text" disabled :value="editUser?.name" class="w-full rounded-xl border-slate-200 bg-slate-100 px-4 py-2.5 text-sm text-slate-600 shadow-sm cursor-not-allowed">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kullanıcı Adı</label>
                                <input type="text" disabled :value="editUser?.username" class="w-full rounded-xl border-slate-200 bg-slate-100 px-4 py-2.5 text-sm text-slate-600 shadow-sm cursor-not-allowed">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Yeni Şifre (İsteğe Bağlı)</label>
                                <input type="text" name="password" minlength="6" class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Değiştirmek istemiyorsanız boş bırakın">
                            </div>

                            <div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" :checked="editUser?.is_active" class="w-5 h-5 text-indigo-600 bg-white border-slate-300 rounded focus:ring-indigo-600 focus:ring-2">
                                    <span class="text-sm font-bold text-slate-700">Kullanıcı Aktif (Giriş yapabilir)</span>
                                </label>
                            </div>
                        </div>
                        <div class="bg-white px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100">
                            <button type="button" @click="editUser = null" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-200 transition-colors">İptal</button>
                            <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-colors">Güncelle</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('userManager', () => ({
            showUserModal: false,
            editUser: null,
            selectedParent: '',
            username: '',
            
            parent1Phone: '{{ $student->parent1_phone ?? "" }}',
            parent2Phone: '{{ $student->parent2_phone ?? "" }}',

            updateUsername() {
                if (this.selectedParent === '1') {
                    this.username = this.parent1Phone;
                } else if (this.selectedParent === '2') {
                    this.username = this.parent2Phone;
                } else {
                    this.username = '';
                }
            },

            openEdit(user) {
                this.editUser = user;
            }
        }));
    });
</script>
@endsection
