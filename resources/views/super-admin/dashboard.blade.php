@extends('layouts.app')

@section('content')
<meta name="api-token" content="{{ $apiToken }}">
<div class="ml-64 p-8 space-y-8 bg-gray-50 min-h-screen" x-data="superAdmin()">

    {{-- 2. HEADER --}}
    <div class="bg-white rounded-2xl shadow-sm p-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Süper Admin Paneli</h1>
            <p class="text-sm text-gray-500">Platform genel istatistikleri ve yönetimi</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right">
                <div class="text-sm font-semibold text-gray-700">
                    {{ now()->format('d.m.Y H:i') }}
                </div>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-sm font-bold text-white shadow-lg">
                {{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}
            </div>
        </div>
    </div>

    {{-- 3. KPI KARTLARI (5'li Grid) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6">
        <!-- KPI 1 -->
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-2xl shadow-md p-6 text-white relative overflow-hidden transform transition duration-300 hover:scale-105">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
            <p class="text-indigo-100 text-sm font-medium">Toplam Şirket</p>
            <p class="text-3xl font-bold mt-2">{{ $totalCompanies }}</p>
        </div>
        <!-- KPI 2 -->
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl shadow-md p-6 text-white relative overflow-hidden transform transition duration-300 hover:scale-105">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
            <p class="text-emerald-100 text-sm font-medium">Güncel MRR</p>
            <p class="text-3xl font-bold mt-2">{{ number_format($totalMrr, 0, ',', '.') }} ₺</p>
        </div>
        <!-- KPI 3 -->
        <div class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl shadow-md p-6 text-white relative overflow-hidden transform transition duration-300 hover:scale-105">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
            <p class="text-blue-100 text-sm font-medium">Toplam Tahsilat</p>
            <p class="text-3xl font-bold mt-2">{{ number_format($totalRevenue, 0, ',', '.') }} ₺</p>
        </div>
        <!-- KPI 4 -->
        <div class="bg-gradient-to-br from-red-500 to-rose-700 rounded-2xl shadow-md p-6 text-white relative overflow-hidden transform transition duration-300 hover:scale-105">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
            <p class="text-red-100 text-sm font-medium">Askıya Alınmış</p>
            <p class="text-3xl font-bold mt-2">{{ $suspendedCompanies }}</p>
        </div>
        <!-- KPI 5 -->
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl shadow-md p-6 text-white relative overflow-hidden transform transition duration-300 hover:scale-105">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
            <p class="text-amber-100 text-sm font-medium">Aktif Duyurular</p>
            <p class="text-3xl font-bold mt-2">{{ $activeAnnouncements }}</p>
        </div>
    </div>

    {{-- 4. ALT İÇERİK: Tablo ve Duyuru Formu --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        {{-- SOL TARAF: Şirketler Tablosu (Geniş alan) --}}
        <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm p-6 overflow-hidden">
            <h2 class="text-lg font-bold mb-4 text-gray-800">Firma & Lisans Yönetimi</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="px-4 py-3 font-semibold rounded-tl-lg">Şirket</th>
                            <th class="px-4 py-3 font-semibold">Paket / Status</th>
                            <th class="px-4 py-3 font-semibold">Bitiş Tarihi</th>
                            <th class="px-4 py-3 font-semibold">Kota</th>
                            <th class="px-4 py-3 font-semibold text-right rounded-tr-lg">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($companies as $company)
                        <tr class="hover:bg-gray-50 transition-colors group">
                            <td class="px-4 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-lg shadow-sm border border-gray-200">
                                        🏢
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800">{{ $company->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $company->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col space-y-1 items-start">
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $company->license_type ?? 'STANDART' }}</span>
                                    
                                    @php
                                        $statusClass = match($company->status->value ?? 'active') {
                                            'active' => 'bg-emerald-100 text-emerald-700',
                                            'suspended' => 'bg-red-100 text-red-700',
                                            'trial' => 'bg-blue-100 text-blue-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                        {{ $company->status?->label() ?? 'Aktif' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-sm text-gray-700">{{ $company->license_expires_at ? $company->license_expires_at->format('d.m.Y') : 'Süresiz' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col space-y-1">
                                    <div class="flex items-center space-x-1 text-xs text-gray-600">
                                        <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                        <span>{{ $company->users_count }} / {{ $company->max_users }}</span>
                                    </div>
                                    <div class="flex items-center space-x-1 text-xs text-gray-600">
                                        <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                        <span>{{ $company->vehicles_count }} / {{ $company->max_vehicles }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-right space-x-2">
                                <!-- Impersonate Btn -->
                                <button @click="impersonate({{ $company->id }})" class="p-2 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm focus:outline-none" title="Giriş Yap (Impersonate)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                                <!-- Settings Btn -->
                                <button @click="openLicenseModal({{ $company->id }}, '{{ $company->license_type }}', '{{ $company->status?->value ?? 'active' }}', '{{ $company->license_expires_at ? $company->license_expires_at->format('Y-m-d') : '' }}', {{ $company->max_vehicles }}, {{ $company->max_users }})" class="p-2 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-100 transition-all shadow-sm focus:outline-none" title="Lisans/Status Ayarları">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SAĞ TARAF: Duyuru Formu (Dar alan) --}}
        <div class="xl:col-span-1 bg-white rounded-2xl shadow-sm p-6 flex flex-col">
            <h2 class="text-lg font-bold mb-4 text-gray-800">Global Sistem Duyuruları</h2>
            
            <form @submit.prevent="submitAnnouncement" class="space-y-4 mb-8">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duyuru Başlığı</label>
                    <input type="text" x-model="annForm.title" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">İçerik</label>
                    <textarea x-model="annForm.content" required rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-colors"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tip</label>
                        <select x-model="annForm.type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                            <option value="info">Bilgi</option>
                            <option value="warning">Uyarı</option>
                            <option value="danger">Acil</option>
                            <option value="success">Başarı</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Durum</label>
                        <select x-model="annForm.is_active" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                            <option value="1">Yayında</option>
                            <option value="0">Taslak</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="w-full py-2 px-4 bg-gray-900 text-white rounded-lg shadow hover:bg-gray-800 transition-colors font-medium">
                    Duyuruyu Yayınla
                </button>
            </form>

            <div class="border-t border-gray-100 pt-4 flex-1 overflow-y-auto">
                <h4 class="text-sm font-bold text-gray-500 uppercase mb-3">Mevcut Duyurular</h4>
                <div class="space-y-3">
                    @foreach($announcements as $ann)
                    <div class="p-3 rounded-xl border {{ $ann->type === 'danger' ? 'border-red-200 bg-red-50' : ($ann->type === 'warning' ? 'border-amber-200 bg-amber-50' : 'border-indigo-100 bg-indigo-50') }}">
                        <div class="flex justify-between items-start">
                            <p class="font-bold text-sm text-gray-800">{{ $ann->title }}</p>
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full {{ $ann->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700' }}">{{ $ann->is_active ? 'Yayında' : 'Pasif' }}</span>
                        </div>
                        <p class="text-xs text-gray-600 mt-1">{{ Str::limit($ann->content, 60) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    <!-- MODAL: Lisans ve Status Ayarları -->
    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm"></div>
        
        <div x-show="isModalOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-white rounded-2xl shadow-2xl overflow-hidden w-full max-w-lg z-10 transform transition-all">
            
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Lisans ve Firma Durumu</h3>
                <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Firma Durumu</label>
                    <select x-model="modalForm.status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                        <option value="active">Aktif</option>
                        <option value="trial">Deneme Sürümü</option>
                        <option value="passive">Pasif (Ödeme Bekliyor)</option>
                        <option value="suspended">Askıya Alındı</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lisans Paketi</label>
                        <input type="text" x-model="modalForm.license_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bitiş Tarihi</label>
                        <input type="date" x-model="modalForm.license_expires_at" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Maksimum Araç</label>
                        <input type="number" x-model="modalForm.max_vehicles" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Maksimum Kullanıcı</label>
                        <input type="number" x-model="modalForm.max_users" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end space-x-3">
                <button @click="isModalOpen = false" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg shadow-sm hover:bg-gray-50 font-medium">İptal</button>
                <button @click="saveLicenseAndStatus" class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 font-medium transition-colors">Kaydet</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('superAdmin', () => ({
            isModalOpen: false,
            currentCompanyId: null,
            modalForm: {
                status: 'active',
                license_type: 'standart',
                license_expires_at: '',
                max_vehicles: 10,
                max_users: 5
            },
            annForm: {
                title: '',
                content: '',
                type: 'info',
                is_active: '1'
            },

            openLicenseModal(id, type, status, expires, max_v, max_u) {
                this.currentCompanyId = id;
                this.modalForm.license_type = type;
                this.modalForm.status = status;
                this.modalForm.license_expires_at = expires;
                this.modalForm.max_vehicles = max_v;
                this.modalForm.max_users = max_u;
                this.isModalOpen = true;
            },

            async saveLicenseAndStatus() {
                try {
                    const token = document.querySelector('meta[name="api-token"]').getAttribute('content');
                    const headers = { 
                        'Content-Type': 'application/json', 
                        'Accept': 'application/json', 
                        'Authorization': 'Bearer ' + token 
                    };

                    const resStatus = await fetch(`/api/v1/super-admin/companies/${this.currentCompanyId}/status`, {
                        method: 'PUT',
                        headers: headers,
                        body: JSON.stringify({ status: this.modalForm.status })
                    });
                    if(resStatus.status === 401 || resStatus.status === 403) {
                        return alert('Oturum süreniz dolmuş veya bu işleme yetkiniz yok. Lütfen sayfayı yenileyip tekrar giriş yapın.');
                    }

                    const resLicense = await fetch(`/api/v1/super-admin/companies/${this.currentCompanyId}/license`, {
                        method: 'PUT',
                        headers: headers,
                        body: JSON.stringify({
                            license_type: this.modalForm.license_type,
                            license_expires_at: this.modalForm.license_expires_at || null,
                            max_vehicles: this.modalForm.max_vehicles,
                            max_users: this.modalForm.max_users
                        })
                    });

                    window.location.reload();
                } catch (e) {
                    alert('Güncelleme sırasında bir hata oluştu.');
                }
            },

            async impersonate(companyId) {
                if(!confirm('Bu şirkete admin olarak giriş yapmak istiyor musunuz?')) return;
                
                try {
                    const res = await fetch(`/super-admin/companies/${companyId}/impersonate`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 
                            'Content-Type': 'application/json', 
                            'Accept': 'application/json', 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    
                    if(res.status === 401 || res.status === 403) {
                        return alert('Oturum süreniz dolmuş veya bu işleme yetkiniz yok. Lütfen sayfayı yenileyip tekrar giriş yapın.');
                    }
                    const data = await res.json();
                    
                    if(data.success && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        alert(data.message || 'Hata oluştu.');
                    }
                } catch (e) {
                    alert('Hata oluştu.');
                }
            },

            async submitAnnouncement() {
                try {
                    const token = document.querySelector('meta[name="api-token"]').getAttribute('content');
                    const res = await fetch('/api/v1/super-admin/announcements', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json', 
                            'Accept': 'application/json', 
                            'Authorization': 'Bearer ' + token 
                        },
                        body: JSON.stringify({
                            title: this.annForm.title,
                            content: this.annForm.content,
                            type: this.annForm.type,
                            is_active: this.annForm.is_active === '1'
                        })
                    });
                    window.location.reload();
                } catch(e) {
                    alert('Hata');
                }
            }
        }));
    });
</script>
@endsection
