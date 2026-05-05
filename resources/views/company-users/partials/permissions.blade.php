{{-- Premium Modül Bazlı Yetki Kartları --}}
@php
    // Modül grupları tanımı: Her modül kendi ikonuna, rengine ve alt yetkilerine sahip
    $modules = [
        [
            'title' => 'Araçlar',
            'icon' => '🚗',
            'gradient' => 'from-blue-500 to-indigo-600',
            'shadow' => 'shadow-blue-500/25',
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-200',
            'text' => 'text-blue-600',
            'keys' => ['vehicles.view', 'vehicles.create', 'vehicles.edit', 'vehicles.delete'],
            'labels' => ['Araçları Görüntüleme', 'Araç Ekleme', 'Araç Düzenleme', 'Araç Silme'],
        ],
        [
            'title' => 'Personeller',
            'icon' => '👤',
            'gradient' => 'from-violet-500 to-purple-600',
            'shadow' => 'shadow-violet-500/25',
            'bg' => 'bg-violet-50',
            'border' => 'border-violet-200',
            'text' => 'text-violet-600',
            'keys' => ['drivers.view', 'drivers.create', 'drivers.edit', 'drivers.delete'],
            'labels' => ['Personelleri Görüntüleme', 'Personel Ekleme', 'Personel Düzenleme', 'Personel Silme'],
        ],
        [
            'title' => 'Bakım / Tamir',
            'icon' => '🔧',
            'gradient' => 'from-emerald-500 to-teal-600',
            'shadow' => 'shadow-emerald-500/25',
            'bg' => 'bg-emerald-50',
            'border' => 'border-emerald-200',
            'text' => 'text-emerald-600',
            'keys' => ['maintenances.view', 'maintenances.create', 'maintenances.edit', 'maintenances.delete'],
            'labels' => ['Bakımları Görüntüleme', 'Bakım Ekleme', 'Bakım Düzenleme', 'Bakım Silme'],
        ],
        [
            'title' => 'Yakıt',
            'icon' => '⛽',
            'gradient' => 'from-amber-500 to-orange-600',
            'shadow' => 'shadow-amber-500/25',
            'bg' => 'bg-amber-50',
            'border' => 'border-amber-200',
            'text' => 'text-amber-600',
            'keys' => ['fuels.view', 'fuels.create', 'fuels.edit', 'fuels.delete', 'fuel_stations.view', 'fuel_stations.create', 'fuel_stations.edit', 'fuel_stations.delete'],
            'labels' => ['Yakıtları Görüntüleme', 'Yakıt Ekleme', 'Yakıt Düzenleme', 'Yakıt Silme', 'İstasyonları Görüntüleme', 'İstasyon Ekleme', 'İstasyon Düzenleme', 'İstasyon Silme'],
        ],
        [
            'title' => 'Trafik Cezaları',
            'icon' => '⚠️',
            'gradient' => 'from-rose-500 to-pink-600',
            'shadow' => 'shadow-rose-500/25',
            'bg' => 'bg-rose-50',
            'border' => 'border-rose-200',
            'text' => 'text-rose-600',
            'keys' => ['penalties.view', 'penalties.create', 'penalties.edit', 'penalties.delete'],
            'labels' => ['Cezaları Görüntüleme', 'Ceza Ekleme', 'Ceza Düzenleme', 'Ceza Silme'],
        ],
        [
            'title' => 'Puantaj / Sefer',
            'icon' => '📅',
            'gradient' => 'from-sky-500 to-cyan-600',
            'shadow' => 'shadow-sky-500/25',
            'bg' => 'bg-sky-50',
            'border' => 'border-sky-200',
            'text' => 'text-sky-600',
            'keys' => ['trips.view', 'trips.create', 'trips.edit', 'trips.delete'],
            'labels' => ['Seferleri Görüntüleme', 'Sefer Ekleme', 'Sefer Düzenleme', 'Sefer Silme'],
        ],
        [
            'title' => 'Maaşlar',
            'icon' => '💰',
            'gradient' => 'from-lime-500 to-green-600',
            'shadow' => 'shadow-lime-500/25',
            'bg' => 'bg-lime-50',
            'border' => 'border-lime-200',
            'text' => 'text-lime-600',
            'keys' => ['payrolls.view', 'payrolls.create', 'payrolls.edit', 'payrolls.delete'],
            'labels' => ['Maaşları Görüntüleme', 'Maaş Ekleme', 'Maaş Düzenleme', 'Maaş Silme'],
        ],
        [
            'title' => 'Müşteriler',
            'icon' => '🏢',
            'gradient' => 'from-teal-500 to-emerald-600',
            'shadow' => 'shadow-teal-500/25',
            'bg' => 'bg-teal-50',
            'border' => 'border-teal-200',
            'text' => 'text-teal-600',
            'keys' => ['customers.view', 'customers.create', 'customers.edit', 'customers.delete'],
            'labels' => ['Müşterileri Görüntüleme', 'Müşteri Ekleme', 'Müşteri Düzenleme', 'Müşteri Silme'],
        ],
        [
            'title' => 'Belgeler',
            'icon' => '📄',
            'gradient' => 'from-slate-500 to-slate-700',
            'shadow' => 'shadow-slate-500/25',
            'bg' => 'bg-slate-50',
            'border' => 'border-slate-200',
            'text' => 'text-slate-600',
            'keys' => ['documents.view', 'documents.create', 'documents.edit', 'documents.delete'],
            'labels' => ['Belgeleri Görüntüleme', 'Belge Ekleme', 'Belge Düzenleme', 'Belge Silme'],
        ],
        [
            'title' => 'Raporlar & Finans',
            'icon' => '📊',
            'gradient' => 'from-indigo-500 to-blue-600',
            'shadow' => 'shadow-indigo-500/25',
            'bg' => 'bg-indigo-50',
            'border' => 'border-indigo-200',
            'text' => 'text-indigo-600',
            'keys' => ['reports.view', 'financials.view', 'dashboard.view'],
            'labels' => ['Raporları Görüntüleme', 'Finansal Özet Görüntüleme', 'Gösterge Panelini Görüntüleme'],
        ],
    ];

    // Yetki key → id eşleştirmesi
    $permKeyToId = $permissions->pluck('id', 'key')->toArray();
    $selectedPerms = $selectedPermissions ?? old('permissions', []);
@endphp

<div class="rounded-[32px] border border-slate-200 bg-white p-8 shadow-sm">
    <div class="mb-8 flex items-center gap-4">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white shadow-lg shadow-purple-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04a11.357 11.357 0 00-1.018 4.772c0 4.113 2.193 7.713 5.5 9.69a11.354 11.354 0 0011.001 0c3.307-1.977 5.5-5.577 5.5-9.69a11.357 11.357 0 00-1.018-4.772z"></path></svg>
        </div>
        <div>
            <h2 class="text-xl font-black text-slate-900 tracking-tight">Menü Erişimi</h2>
            <p class="text-sm font-medium text-slate-400">Modüle tıklayarak alt yetkileri açın veya kapatın</p>
        </div>
        {{-- Tümünü Seç / Kaldır --}}
        <div class="ml-auto flex gap-2">
            <button type="button" onclick="selectAllPerms()" class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-emerald-600 hover:bg-emerald-100 transition-all">
                Tümünü Seç
            </button>
            <button type="button" onclick="deselectAllPerms()" class="rounded-xl bg-rose-50 border border-rose-200 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-rose-600 hover:bg-rose-100 transition-all">
                Tümünü Kaldır
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4" id="permission-modules">
        @foreach($modules as $mi => $mod)
            @php
                $modPerms = [];
                foreach ($mod['keys'] as $ki => $key) {
                    if (isset($permKeyToId[$key])) {
                        $modPerms[] = [
                            'id' => $permKeyToId[$key],
                            'key' => $key,
                            'label' => $mod['labels'][$ki] ?? $key,
                        ];
                    }
                }
                if (empty($modPerms)) continue;
                $activeCount = 0;
                foreach ($modPerms as $mp) {
                    if (in_array($mp['id'], $selectedPerms)) $activeCount++;
                }
            @endphp

            <div class="perm-module-card group relative" data-module="{{ $mi }}">
                {{-- Modül Başlık Kartı (Tıklanabilir) --}}
                <button type="button"
                        onclick="toggleModule({{ $mi }})"
                        class="module-trigger w-full rounded-[24px] border-2 transition-all duration-500 p-5 text-center hover:-translate-y-1 hover:shadow-2xl {{ $activeCount > 0 ? $mod['border'] . ' ' . $mod['bg'] . ' shadow-lg ' . $mod['shadow'] : 'border-slate-100 bg-slate-50/50 shadow-sm' }}">
                    
                    {{-- 3D İkon --}}
                    <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-[20px] bg-gradient-to-br {{ $mod['gradient'] }} text-3xl shadow-xl {{ $mod['shadow'] }} transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3">
                        {{ $mod['icon'] }}
                    </div>

                    {{-- Modül Adı --}}
                    <h3 class="text-sm font-black text-slate-800 tracking-tight leading-tight">{{ $mod['title'] }}</h3>

                    {{-- Aktif Yetki Sayacı --}}
                    <div class="mt-2 flex items-center justify-center gap-1.5" id="module-counter-{{ $mi }}">
                        <span class="inline-flex h-5 min-w-[20px] items-center justify-center rounded-full text-[10px] font-black {{ $activeCount > 0 ? 'bg-gradient-to-r ' . $mod['gradient'] . ' text-white' : 'bg-slate-200 text-slate-500' }}" id="module-count-{{ $mi }}">
                            {{ $activeCount }}
                        </span>
                        <span class="text-[10px] font-bold text-slate-400">/{{ count($modPerms) }}</span>
                    </div>

                    {{-- Açılma İndikatörü --}}
                    <div class="mt-2">
                        <svg class="w-4 h-4 mx-auto text-slate-300 transition-transform duration-300" id="module-chevron-{{ $mi }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </button>

                {{-- Alt Yetki Detay Paneli (Gizli – tıklayınca açılır) --}}
                <div class="perm-detail-panel hidden absolute left-1/2 -translate-x-1/2 top-full mt-3 z-50 w-72 rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_25px_60px_rgba(0,0,0,0.15)] animate-in fade-in slide-in-from-top-2 duration-300"
                     id="module-panel-{{ $mi }}">
                    
                    {{-- Panel Başlık --}}
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br {{ $mod['gradient'] }} text-xl shadow-md">
                                {{ $mod['icon'] }}
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-900">{{ $mod['title'] }}</h4>
                                <p class="text-[10px] font-bold text-slate-400">Alt yetkileri yönetin</p>
                            </div>
                        </div>
                        <button type="button" onclick="toggleModule({{ $mi }})" class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center hover:bg-slate-200 transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    {{-- Hepsini Seç Toggle --}}
                    <button type="button" onclick="toggleAllInModule({{ $mi }})" class="w-full mb-3 rounded-xl bg-slate-50 border border-slate-200 py-2.5 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                        Hepsini Seç / Kaldır
                    </button>

                    {{-- Alt Yetkiler --}}
                    <div class="space-y-2">
                        @foreach($modPerms as $mp)
                            <label class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3 transition-all hover:bg-white hover:border-slate-200 hover:shadow-sm cursor-pointer group/item">
                                <input type="checkbox" name="permissions[]" value="{{ $mp['id'] }}"
                                       data-module="{{ $mi }}"
                                       onchange="updateModuleCount({{ $mi }})"
                                       {{ in_array($mp['id'], $selectedPerms) ? 'checked' : '' }}
                                       class="perm-cb h-5 w-5 rounded-lg border-slate-300 {{ $mod['text'] }} focus:ring-2 focus:ring-offset-1 transition-all">
                                <span class="text-xs font-bold text-slate-600 group-hover/item:text-slate-900 transition-colors">{{ $mp['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Overlay (panel açıkken arka plana tıklama) --}}
<div id="perm-overlay" class="fixed inset-0 z-40 hidden" onclick="closeAllPanels()"></div>

<style>
    .perm-detail-panel { z-index: 50; }
    .perm-module-card { position: relative; }
    @keyframes shake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-2px)} 75%{transform:translateX(2px)} }
</style>

<script>
    let openModuleId = null;

    function toggleModule(id) {
        const panel = document.getElementById('module-panel-' + id);
        const chevron = document.getElementById('module-chevron-' + id);
        const overlay = document.getElementById('perm-overlay');

        // Eğer zaten açıksa kapat
        if (openModuleId === id) {
            closeAllPanels();
            return;
        }

        // Önceki açık paneli kapat
        closeAllPanels();

        // Yeni paneli aç
        panel.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
        overlay.classList.remove('hidden');
        openModuleId = id;
    }

    function closeAllPanels() {
        document.querySelectorAll('.perm-detail-panel').forEach(p => p.classList.add('hidden'));
        document.querySelectorAll('[id^="module-chevron-"]').forEach(c => c.style.transform = '');
        document.getElementById('perm-overlay').classList.add('hidden');
        openModuleId = null;
    }

    function updateModuleCount(moduleId) {
        const cbs = document.querySelectorAll(`input.perm-cb[data-module="${moduleId}"]`);
        const total = cbs.length;
        const checked = [...cbs].filter(c => c.checked).length;
        
        const countEl = document.getElementById('module-count-' + moduleId);
        countEl.textContent = checked;

        // Kart stilini güncelle
        const trigger = document.querySelector(`[data-module="${moduleId}"] .module-trigger`);
        if (checked > 0) {
            countEl.className = countEl.className.replace('bg-slate-200 text-slate-500', '').replace(/bg-gradient-to-r\s+\S+\s+text-white/, '');
            countEl.classList.add('bg-gradient-to-r', 'text-white');
            // Gradient sınıfını geri koy
            const gradientMatch = trigger.className.match(/from-\S+/);
        } else {
            countEl.className = 'inline-flex h-5 min-w-[20px] items-center justify-center rounded-full text-[10px] font-black bg-slate-200 text-slate-500';
        }
    }

    function toggleAllInModule(moduleId) {
        const cbs = document.querySelectorAll(`input.perm-cb[data-module="${moduleId}"]`);
        const allChecked = [...cbs].every(c => c.checked);
        cbs.forEach(c => c.checked = !allChecked);
        updateModuleCount(moduleId);
    }

    function selectAllPerms() {
        document.querySelectorAll('input.perm-cb').forEach(c => c.checked = true);
        // Her modülün sayacını güncelle
        document.querySelectorAll('.perm-module-card').forEach(card => {
            const id = card.dataset.module;
            if (id !== undefined) updateModuleCount(id);
        });
    }

    function deselectAllPerms() {
        document.querySelectorAll('input.perm-cb').forEach(c => c.checked = false);
        document.querySelectorAll('.perm-module-card').forEach(card => {
            const id = card.dataset.module;
            if (id !== undefined) updateModuleCount(id);
        });
    }
</script>
