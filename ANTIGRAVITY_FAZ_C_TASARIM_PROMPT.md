# ANTIGRAVITY PROMPT — FAZ C: MOBİL UYGULAMA TASARIM REDIZAYNI

## AMAÇ
ServisPilot mobile-app'ın tüm ekranlarını **ultra-pro, modern, KPI-öncelikli, renk-hiyerarşik** bir tasarım diline geçir. Mevcut CRUD/yetki/iş mantığı %100 korunacak — sadece UI/UX katmanı değişecek.

## KRİTİK SINIRLAR
- **`VehiclesScreen.js` ve `VehicleDetailScreen.js` ASLA DOKUNULMAYACAK.** Bu ekranlar iş kritik özelliklere sahip ve tasarımları korunacak.
- Mevcut `hasPermission()` kontrolleri, axios çağrıları, form validation kuralları, navigation logic — HİÇBİRİ değişmeyecek.
- Faz B.3'te eklediğimiz CRUD modallarının (CustomersScreen, TripsScreen, PersonnelScreen vb.) işlevselliği KIRILMAYACAK — sadece görsel olarak yenilenecek.

## ÇALIŞMA KURALLARI
- Hiçbir aşamada onay sorma. Her fazı tamamla, otomatik olarak sonrakine geç.
- Her faz sonrasında küçük bir sanity check yap (uygulama açılıyor mu, ekrana navigate edilebiliyor mu — `npx expo start` ile gerek yok, sadece import hatalarını kontrol et).
- En son tek bir kapsamlı rapor ver.

---

## FAZ C.0 — DESIGN SYSTEM (Theme & Tokens)

### Dosya: `mobile-app/src/theme/index.js`
Aşağıdaki design token'ları tek dosyada export et:

```javascript
export const colors = {
  // Module colors
  customers:   { 50: '#EFF6FF', 100: '#DBEAFE', 500: '#3B82F6', 600: '#2563EB', 700: '#1D4ED8' },
  trips:       { 50: '#F0FDFA', 100: '#CCFBF1', 500: '#14B8A6', 600: '#0D9488', 700: '#0F766E' },
  fuel:        { 50: '#FFFBEB', 100: '#FEF3C7', 500: '#F59E0B', 600: '#D97706', 700: '#B45309' },
  maintenance: { 50: '#FAF5FF', 100: '#F3E8FF', 500: '#A855F7', 600: '#9333EA', 700: '#7E22CE' },
  personnel:   { 50: '#FDF2F8', 100: '#FCE7F3', 500: '#EC4899', 600: '#DB2777', 700: '#BE185D' },
  penalty:     { 50: '#FEF2F2', 100: '#FEE2E2', 500: '#EF4444', 600: '#DC2626', 700: '#B91C1C' },
  document:    { 50: '#F0F9FF', 100: '#E0F2FE', 500: '#0EA5E9', 600: '#0284C7', 700: '#0369A1' },
  payroll:     { 50: '#ECFDF5', 100: '#D1FAE5', 500: '#10B981', 600: '#059669', 700: '#047857' },
  contract:    { 50: '#FFF7ED', 100: '#FFEDD5', 500: '#F97316', 600: '#EA580C', 700: '#C2410C' },
  route:       { 50: '#EEF2FF', 100: '#E0E7FF', 500: '#6366F1', 600: '#4F46E5', 700: '#4338CA' },

  // Neutrals
  bg:          '#F8FAFC',
  surface:     '#FFFFFF',
  surfaceAlt:  '#F1F5F9',
  border:      '#E2E8F0',
  textPrimary: '#0F172A',
  textSecondary: '#475569',
  textMuted:   '#94A3B8',

  // Semantic
  success:     '#10B981',
  warning:     '#F59E0B',
  danger:      '#EF4444',
  info:        '#3B82F6',
};

export const spacing = { xs: 4, sm: 8, md: 12, lg: 16, xl: 24, xxl: 32, xxxl: 48 };

export const radius = { sm: 6, md: 10, lg: 14, xl: 20, full: 999 };

export const typography = {
  display:   { fontSize: 28, fontWeight: '700', letterSpacing: -0.5 },
  title:     { fontSize: 20, fontWeight: '700' },
  heading:   { fontSize: 17, fontWeight: '600' },
  body:      { fontSize: 15, fontWeight: '400' },
  caption:   { fontSize: 13, fontWeight: '400' },
  label:     { fontSize: 12, fontWeight: '600', letterSpacing: 0.4, textTransform: 'uppercase' },
};

export const shadow = {
  sm: { shadowColor: '#0F172A', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.06, shadowRadius: 2, elevation: 1 },
  md: { shadowColor: '#0F172A', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.08, shadowRadius: 8, elevation: 3 },
  lg: { shadowColor: '#0F172A', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.12, shadowRadius: 16, elevation: 6 },
};
```

---

## FAZ C.1 — REUSABLE COMPONENTS

`mobile-app/src/components/` altında şu dosyaları oluştur:

### `KpiCard.js`
- Props: `label`, `value`, `delta` (opsiyonel: `+18%`), `deltaDirection` (`up`/`down`/`neutral`), `icon` (Ionicons name), `accent` (renk anahtarı: `customers`, `trips` vs.)
- Yapı: Üstte küçük renk dolgulu pill (icon + label), altında büyük value (display typography), en altta delta (yeşil/kırmızı/gri)
- White surface, radius.lg, shadow.sm, padding lg

### `ListItemCard.js`
- Props: `avatarText` veya `avatarIcon`, `accent`, `title`, `subtitle`, `badges` (array: `{label, color}`), `right` (opsiyonel custom node), `onPress`, `onLongPress`
- Yapı: Sol tarafta dairesel avatar (accent.100 arka plan, accent.700 metin), ortada title + subtitle, sağ tarafta rozetler veya chevron-forward icon
- Dokunma feedback: Pressable + opacity 0.7

### `Fab.js`
- Props: `onPress`, `icon` (default: 'add'), `accent`, `visible` (default: true) — `hasPermission()` döndürdüğü değere göre `visible` set edilecek
- Sağ alt sabit pozisyon, daire, gölge lg, accent.500 arka plan

### `BottomSheetModal.js`
- Props: `visible`, `onClose`, `title`, `children`, `footer` (opsiyonel)
- Animasyonu: KeyboardAvoidingView + slideInUp. iOS/Android her ikisinde de düzgün çalışacak.
- Üstte tutucu (handle) bar, altta footer (genelde İptal + Kaydet butonu)

### `FormField.js`
- Props: `label`, `value`, `onChangeText`, `error`, `placeholder`, `secureTextEntry`, `keyboardType`, `multiline`
- Hata varsa kenar rengi `colors.danger`, altında error text
- Focused state: kenar rengi accent.500

### `FilterChipRow.js`
- Props: `chips` (array: `{label, value, count}`), `selected`, `onSelect`, `accent`
- Yatay scrollable row, seçili chip accent.500 dolgu, diğerleri surfaceAlt arka plan

### `EmptyState.js`
- Props: `icon`, `title`, `description`, `actionLabel`, `onAction`, `accent`
- Liste boşken gösterilecek. Büyük dairesel icon + başlık + açıklama + opsiyonel CTA button

### `Skeleton.js`
- Liste yüklenirken gösterilecek shimmer placeholder (3-5 fake row)

### `Header.js`
- Props: `title`, `subtitle`, `right` (action buttons), `accent` (opsiyonel hafif arka plan)
- 56px sabit yükseklik, padding lg

### `SectionHeader.js`
- Props: `title`, `count`, `action` (opsiyonel "Tümünü gör" linki)
- Liste içinde alt başlıklar için

### `Badge.js`
- Props: `label`, `tone` (`neutral`, `success`, `warning`, `danger`, `info`)
- Küçük yuvarlatılmış pill, label uppercase letter-spacing

### `EmptyIcon3D.js` (3D ikon yerine ışıklı circular icon)
- Props: `icon` (Ionicons name), `accent`, `size` (default 64)
- İki katmanlı dairesel arka plan: dış accent.50, iç accent.100, ortada Ionicons accent.700 — 3D efekt yerine gradient-like layered visual (gerçek 3D PNG kullanmaktan kaçın çünkü asset eklemek build sürecini bozar; bu yaklaşım Lottie'siz ve dependency'siz)

Tüm component'lerden `mobile-app/src/components/index.js` üzerinden re-export et:
```javascript
export { default as KpiCard } from './KpiCard';
export { default as ListItemCard } from './ListItemCard';
// ... vs
```

---

## FAZ C.2 — HomeScreen (Dashboard) Redizaynı

**Dosya:** `mobile-app/src/screens/HomeScreen.js`

Mevcut iş mantığını koru, UI'ı baştan yaz.

### Yapı (yukarıdan aşağıya):

1. **Greeting Section** (padding xl)
   - Üstte küçük: "Pazartesi, 25 Nisan" (caption, textMuted)
   - Altında: "Merhaba, {user.name}" (display, textPrimary)
   - Sağ üstte: Profile avatar (dairesel, baş harf)

2. **KPI Grid** — 2x2 (veya 2x3) responsive grid, gap md
   - Aktif Araç (vehicles modülünden — accent: customers/blue)
   - Bu Ay Sefer (trips — teal, delta: önceki aya göre %)
   - Bu Ay Yakıt (fuel — amber, ₺)
   - Yaklaşan Bakım (maintenance — purple, sayı)
   - Yaklaşan Muayene (document — sky, sayı)
   - Açık Cezalar (penalty — red, ₺)

   Her KPI için API endpoint çağrısı yap (mevcut `/api/me` veya yeni mini endpoint — eğer yoksa `/api/v1/dashboard/summary` adında basit bir endpoint oluştur).

3. **Quick Actions** — yatay scrollable row, accent renkli icon button'lar
   - Yeni Sefer, Yeni Müşteri, Yakıt Kayıt, Bakım Kayıt
   - `hasPermission()` ile filtrele

4. **Son Aktiviteler** — son 5 işlem (multi-modül feed)
   - SectionHeader: "Son Aktiviteler" + count
   - ListItemCard kullan, accent her satıra göre değişir (örn. yakıt = amber, sefer = teal)

5. **Yaklaşanlar** — son 3 satır
   - Yaklaşan muayeneler/bakımlar
   - ListItemCard ile

Background: `colors.bg`. Tüm spacing token'ları kullan.

---

## FAZ C.3 — LIST SCREEN PATTERN (Tek pattern, 9 ekrana uygulanacak)

Aşağıdaki 9 ekran AYNI pattern'le redizayn edilecek:
- `CustomersScreen.js` (accent: customers)
- `TripsScreen.js` (accent: trips)
- `PersonnelScreen.js` (accent: personnel)
- `VehicleFuelsScreen.js` (accent: fuel)
- `VehicleMaintenancesScreen.js` (accent: maintenance)
- `VehiclePenaltiesScreen.js` (accent: penalty)
- `PayrollScreen.js` (accent: payroll)
- (FuelStations ekranı varsa veya yarat — accent: fuel)
- (Contracts modülü varsa — accent: contract)

### Standart Yapı:

1. **Header** (white surface, accent.50 alt sınır)
   - Title (modül adı, örn. "Müşteriler")
   - Subtitle: "{count} kayıt"
   - Right: filter icon + search icon (search açınca search bar'a dönüşür)

2. **Search Bar** (collapsible, açıkken full width)
   - SearchIcon + TextInput + clear button

3. **FilterChipRow** (modüle göre değişir)
   - Müşteriler: Tümü / Aktif / Pasif / Ticari / Bireysel
   - Trips: Tümü / Bugün / Bu Hafta / Bu Ay / Tamamlanan / İptal
   - Fuels: Tümü / Bu Ay / Geçen Ay / Bu Yıl
   - Maintenances: Tümü / Yaklaşan / Tamamlanan / Geciken
   - Penalties: Tümü / Ödenmemiş / Ödenmiş
   - Payroll: Tümü / {Ay yıl listesi}

4. **List** (FlatList, contentContainerStyle padding)
   - Skeleton (loading)
   - EmptyState (boş)
   - ListItemCard (her kayıt için)

5. **FAB**
   - Sağ alt sabit
   - `visible={hasPermission(`{modül}.create`)}`
   - onPress: BottomSheetModal aç

6. **BottomSheetModal** (Add/Edit form — Faz B.3'te yazılan logic'i KORU, sadece UI'ı FormField + tema ile güncelle)

7. **ActionSheet** (uzun basınca veya kart sağındaki menü iconuna basınca)
   - Düzenle (`hasPermission('{modül}.edit')` ise göster)
   - Sil (`hasPermission('{modül}.delete')` ise göster)
   - Detay görüntüle

### ÖNEMLİ:
- Mevcut ekrandaki state, axios çağrıları, validation kuralları AYNEN korunacak.
- Her ekran için `accent` prop'unu `colors.{modül}` ile bağla.
- 9 ekranı SIRAYLA bir-bir redizayn et, her birinden sonra import hatası kontrolü yap, geç.

---

## FAZ C.4 — DETAIL SCREEN PATTERN

Aşağıdaki ekranları redizayn et:
- `CustomerDetailScreen.js` (accent: customers, tab'lar: Bilgi / Sözleşmeler / Rotalar / Ekstre)
- (DriverDetailScreen varsa — accent: personnel)
- (TripDetailScreen varsa — accent: trips)

### Yapı:
1. **Hero Header** (accent.50 arka plan, padding xl)
   - Sol üst: back arrow
   - Ortada büyük avatar (initials veya icon)
   - Altında title + subtitle + badge'ler
   - Sağ üst: edit icon (yetki varsa)

2. **Tab Bar** (sticky)
   - 3-4 tab, accent.500 selected indicator, accent.500 selected text

3. **Tab İçerikleri**
   - Her tab kendi içinde ListItemCard veya KpiCard ile
   - Mevcut data fetch logic AYNEN korunacak

4. **FAB** (eğer aktif tab CRUD destekliyorsa, contextual: aktif tab'a göre değişir)

---

## FAZ C.5 — AUTH / PROFILE / SETTINGS

### `LoginScreen.js`
- Üstte logo (mevcut asset varsa kullan, yoksa `EmptyIcon3D` ile büyük "S" harf)
- Tagline: "Filo Yönetiminde Yeni Standart"
- Form: e-posta + şifre, FormField bileşenleri
- Primary button: tüm genişlik, accent.customers.500 (mavi marka rengi)
- Loading state: button içinde spinner
- Hata: ekran üstünde toast

### `ProfileScreen.js` (varsa)
- Hero avatar + isim + rol badge
- Liste: Ayarlar, Şifre Değiştir, Bildirimler, Çıkış (her satır ListItemCard)

### `SettingsScreen.js` (varsa)
- Section'lar: Görünüm (dark mode toggle — opsiyonel, şimdilik dummy), Bildirimler, Hesap, Hakkında

---

## FAZ C.6 — NAVİGASYON GÖRSELLEŞTİRMESİ

`mobile-app/App.js` veya navigation root'ta:
- Tab bar style: white surface, top border `colors.border`, padding md
- Tab icon size: 24, tab label size: 11
- Active tint: `colors.customers[500]` (marka mavi)
- Inactive tint: `colors.textMuted`
- Header style: white surface, shadow.sm

---

## FAZ C.7 — REGRESSION KONTROL

Tüm ekranlar redizayn edildikten sonra:

1. **Import temizliği**
   - Her ekranda eski stylesheet referansları (StyleSheet.create() içindeki kullanılmayan key'ler) temizle
   - Component import'larını `from '../components'` üzerinden yeniden düzenle

2. **Smoke test (manuel olmayacak — sadece kod düzeyinde)**
   - `mobile-app` dizininde `npx expo start --no-dev` çalıştırılabiliyor mu kontrol et (sadece `expo doctor` ile syntax kontrolü, gerçek başlatma yapma)
   - Her ekranın import'unun doğru çözümlendiğini grep ile doğrula:
     ```
     grep -r "import.*from.*'../components'" mobile-app/src/screens/
     grep -r "import.*colors.*from.*'../theme'" mobile-app/src/screens/
     ```

3. **API parite kontrolü**
   - `mobile-app/src/api/axios.js` içindeki tüm endpoint'lerin `routes/api.php`'de karşılığının olduğunu grep ile teyit et.

4. **VehiclesScreen ve VehicleDetailScreen DOKUNULMADI mı?**
   - `git diff mobile-app/src/screens/VehiclesScreen.js` boş olmalı
   - `git diff mobile-app/src/screens/VehicleDetailScreen.js` boş olmalı (eğer Faz B'de gate eklendiyse o değişiklik kalsın, başka değişiklik olmasın)

---

## RAPORLAMA

İşin sonunda tek bir markdown raporu üret: `FAZ_C_TASARIM_RAPORU.md`

İçeriği:
- Yeni eklenen dosyalar (theme, components, vs.)
- Redizayn edilen ekran listesi (✅/❌ durum)
- Toplam değişen satır sayısı
- Atlanan/dokunulmayan ekranlar (VehiclesScreen, VehicleDetailScreen)
- Bilinen kalanlar (3D ikon Lottie, gerçek device test gereksinimi)
- Kullanıcının bir sonraki adımı için önerilen aksiyonlar (örn. "Expo Go ile telefonda aç ve her ekrana tıkla")

---

## SON HATIRLATMA
- Faz C.0 → C.7 sıralı, ara onay yok, tek seferde tamamla.
- VehiclesScreen ve VehicleDetailScreen ASLA değiştirilmeyecek.
- Mevcut CRUD logic'i, axios çağrıları, hasPermission gateleri AYNEN korunacak.
- Sadece UI/UX katmanı yenilenecek.
- Tüm renk/spacing/typography değerleri `theme/index.js`'den gelmeli — hardcoded değer kullanma.
- Component'ler `components/index.js` üzerinden export edilmeli.

Hazır olduğunda raporla. Başla.
