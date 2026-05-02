# ServisPilot — Antigravity Kapsamlı Proje Analiz & Eksiklik Raporu Promptu

> Bu promptu Antigravity'de yeni bir konuşma açıp **olduğu gibi** yapıştır. Antigravity tüm projeyi (Laravel web panel + React Native mobil uygulama) tarayıp, web↔mobil paritesini çıkaracak ve eksikleri madde madde raporlayacak.

---

## 📋 ANTIGRAVITY'E YAPIŞTIRACAĞIN PROMPT (BURADAN AŞAĞISI)

Selam. Bu projede **ServisPilot** adında Laravel tabanlı çok kiracılı (multi-tenant) bir SaaS filo/servis yönetim sistemi var. Aynı zamanda bu sistemin React Native (Expo) ile yazılmış Android + iOS mobil uygulaması var. Web panel ile mobil uygulamanın **birebir bağlantılı** olması gerekiyor ama hangi modülün mobile aktarıldığı, hangi alanın eksik kaldığı, hangi web özelliğinin mobil karşılığının olmadığı belirsiz hale geldi. Senden istediğim şey net: **projeyi baştan sona röntgenle, detaylı bir Web↔Mobil Eşleşme & Eksiklik Raporu üret.**

İşi şu sırayla yapmanı istiyorum. Her adımı tamamlamadan bir sonrakine geçme. Her adımın sonunda bana özet ver, sonra devam et.

---

### ADIM 1 — Proje keşfi ve mimari haritalama
1. `composer.json`, `package.json` (root), ve `mobile-app/package.json` dosyalarını oku. Laravel sürümü, PHP sürümü, kullanılan major paketler (Sanctum, Spatie Permission, Excel, DOMPDF, vb.) ve mobil tarafta kullanılan kütüphaneleri (React Native sürümü, Expo, navigation, axios, async-storage, redux/context, vb.) listele.
2. Klasör mimarisini çıkar: 
   - `app/Http/Controllers/` (web ve API ayrı), 
   - `app/Models/`, 
   - `app/Services/`, 
   - `routes/web.php`, `routes/api.php`, `routes/auth.php`,
   - `resources/views/` (Blade görünümleri),
   - `database/migrations/` (tablo şemaları),
   - `mobile-app/src/screens/`, `mobile-app/src/api/`, `mobile-app/src/components/`, `mobile-app/src/context/`.
3. Veritabanı şemasını migrations'lardan çıkar. Her tablo için: alanları, foreign key'leri, multi-tenant alanı (`company_id` var mı?), soft delete kullanımı.
4. **Çıktı:** Bir mimari özet tablosu — "Modül adı | Web Controller | Model | Migration tablosu | API V1 Controller | Mobil Screen | Mobil API çağrısı".

---

### ADIM 2 — Web panel modüllerinin tam envanteri
`routes/web.php` dosyasını satır satır oku. Her route'u şu kategorilere ayır:
- **CRUD modülü** (örn. vehicles, customers, drivers, trips, fuels, maintenances, payrolls, traffic-penalties, route-stops, service-routes, customer-contracts, fuel-stations, documents, company-users)
- **Raporlama modülü** (reports, exports, PDF çıktıları)
- **Ayarlar modülü** (company-settings, profile, vehicle-tracking, vehicle-maintenance-settings, izinler/permissions)
- **Süper Admin modülü** (super-admin altındaki her şey, modül yönetimi, lisans, paket)
- **Müşteri Portalı** (customer-portal/* — son müşterinin gördüğü ayrı yüzey)
- **İletişim/Operasyonel** (chat, activity-logs, support)
- **Auth & profil** (auth.php + profile)

Her modül için: 
- Hangi action'lar var (index/create/store/show/edit/update/destroy + özel action'lar mı?), 
- Hangi Blade view'ları render ediyor, 
- Yetki middleware'i ne (rol/permission), 
- Multi-tenant scope düzgün uygulanmış mı (`company_id` filtresi `Concerns/` veya global scope'larla mı geliyor?).

**Çıktı:** Web modüllerinin tam listesi, her birinin "olgunluk seviyesi" (Tamam / Eksik / Bozuk / Boş kabuk).

---

### ADIM 3 — Web panelinde **yarım kalmış / eksik / bozuk** noktaları tespit et
Aşağıdaki 8 sinyali kullanarak eksiklikleri çıkar:
1. **TODO/FIXME/HACK/XXX** yorumlarını tüm projede grep'le. Liste çıkar (dosya + satır + içerik).
2. Boş method gövdeleri (`return view(...)` yapıp hiç data göndermeyen, `// TODO` ile başlayan veya `dd()`/`dump()` kalmış controller action'ları).
3. Route var ama controller method'u yok (route:list ile karşılaştır).
4. Blade view dosyaları var ama controller'dan render edilmiyor (orphan view), veya tam tersi (controller render ediyor ama view yok).
5. Form var ama validation yok (Request sınıfı kullanılmıyor, `$request->all()` ile direkt kayıt).
6. N+1 query riski olan listeler (foreach içinde relation çağrısı, `with()` eksik).
7. Yetki kontrolü atlanmış endpoint'ler (`auth` middleware var ama policy/gate yok, multi-tenant izolasyon eksik).
8. Frontend'de buton/menü var ama bağlandığı route yok — bunu Blade dosyalarındaki `route('...')` çağrılarını route listesi ile karşılaştırarak bul.

**Çıktı:** "Web Panel — Eksik/Bozuk Noktalar" başlığı altında kategorize liste. Her madde için: dosya yolu, satır numarası, sorun, önerilen düzeltme.

---

### ADIM 4 — Mobil uygulamanın tam envanteri  
`mobile-app/src/screens/` altındaki her ekranı incele. Her ekran için:
- Hangi API endpoint'lerini çağırıyor (`mobile-app/src/api/axios.js` ve ekran içindeki çağrılar)?
- Hangi web modülüne tekabül ediyor?
- Sadece listeleme mi yapıyor, yoksa create/update/delete de var mı?
- Form'larda validation var mı, error handling nasıl?
- Offline desteği, cache, async-storage kullanımı var mı?
- Push notification entegrasyonu (Expo Notifications, FCM, APNS) var mı?
- Auth token yönetimi (Sanctum personal access token saklama, refresh) düzgün mü?

`routes/api.php` dosyasındaki tüm endpoint'leri (legacy + `/api/v1/*`) listele. Mobil tarafta hangi endpoint'in çağrıldığını grep ile bul. Çağrılmayan API endpoint'leri ve mobilde çağrılan ama backend'de olmayan endpoint'leri ayrı listele.

**Çıktı:** "Mobil Ekran ↔ API Endpoint ↔ Web Modülü" eşleşme tablosu.

---

### ADIM 5 — Web ↔ Mobil PARITY MATRİSİ (kritik adım)
Bir tablo üret. Sütunlar:

| Modül | Web'de var mı? | API V1 endpoint'i var mı? | Mobil ekranı var mı? | Mobilde CRUD seviyesi (Read/Create/Update/Delete) | Eksik aksiyonlar | Öncelik (Yüksek/Orta/Düşük) |

Bu matrisi en az şu modüller için doldur (projede daha fazlası varsa hepsini ekle):
- Araçlar (Vehicles)
- Araç Galerisi / Resim Yükleme
- Araç Belgeleri (Documents)
- Bakımlar (Maintenances)
- Yakıtlar (Fuels)
- Yakıt İstasyonları (Fuel Stations)
- Trafik Cezaları (Traffic Penalties)
- Yaklaşan Muayene/Sigorta (Upcoming Inspections/Insurances)
- Personel / Sürücüler (Personnel/Drivers)
- Müşteriler (Customers)
- Müşteri Sözleşmeleri (Customer Contracts)
- Müşteri Servis Rotaları (Customer Service Routes)
- Servis Rotaları (Service Routes)
- Rota Durakları (Route Stops)
- Seferler (Trips)
- Bordrolar (Payrolls)
- Finans Özeti (Finance Summary)
- Raporlar (Reports — PDF/Excel exportları dahil)
- Aktivite Logları (Activity Logs)
- Pilot/Sohbet (Chat)
- Şirket Ayarları (Company Settings)
- Şirket Kullanıcıları & Yetkileri (Company Users / Permissions)
- Araç Takip (Vehicle Tracking)
- Süper Admin paneli (mobilde olmaması normal — sadece "kapsam dışı" olarak işaretle)
- Müşteri Portalı (mobilde ayrı bir yüzey gerekiyor mu?)
- Bildirimler (Notifications — yaklaşan muayene/sigorta/bakım hatırlatmaları)
- Auth (login, şifre sıfırlama, profil güncelleme, 2FA varsa)

**Çıktı:** Eksiksiz parity matrisi + her satıra kısa not.

---

### ADIM 6 — Mobil tarafa özgü eksiklik kontrolü
Mobil uygulamada şunları **özellikle** kontrol et ve raporla:
1. **Token yönetimi:** Sanctum token nerede saklanıyor (AsyncStorage/SecureStore)? Logout'ta backend `/logout` çağrılıyor mu? Token expire olduğunda 401 yakalanıp login'e yönlendiriliyor mu?
2. **Push notification:** Expo Notifications veya Firebase entegrasyonu var mı? Backend'den device token kayıt endpoint'i var mı? Yaklaşan muayene/sigorta/bakım için backend'den notification gönderiliyor mu?
3. **Resim/dosya yükleme:** Araç galerisi, belge yükleme, profil fotoğrafı — Expo ImagePicker entegrasyonu var mı, multipart/form-data ile düzgün gönderiliyor mu?
4. **PDF görüntüleme/indirme:** Web'de PDF export var (bakım raporu, vb.) — mobilde bunu görüntüleme/paylaşma var mı (Expo FileSystem + Sharing)?
5. **Pull-to-refresh, infinite scroll, pagination** her listede uygulanmış mı?
6. **Form validation** (ön yüz tarafında) — yoksa sadece backend hatasını mı gösteriyor?
7. **Çok dilli destek (i18n)** — web Türkçe ise mobil de tutarlı mı?
8. **Tema / dark mode**, **offline ekran**, **loading skeleton'lar**, **error boundary**.
9. **Deep linking** (ör. notification'a tıklayınca ilgili araç detayına gitme).
10. **Build & dağıtım hazırlığı:** `app.json` içindeki bundle identifier, versiyon, ikon, splash, izinler (camera, location, notifications) tam mı? EAS Build yapılandırması var mı?

**Çıktı:** "Mobil Uygulama — Eksik/İyileştirilecek Noktalar" listesi.

---

### ADIM 7 — Güvenlik & Multi-tenant izolasyon kontrolü
SaaS olduğuna göre bu KRİTİK:
1. Tüm Eloquent sorguları `company_id` ile scope'lanıyor mu? Global scope mu, manuel `where` mı? Bypass edilebilen yerler var mı?
2. API V1 controller'larında `auth()->user()->company_id` ile filtreleme tutarlı mı?
3. Route model binding'lerde başka şirketin kaydına erişim mümkün mü (ör. `/vehicles/{vehicle}` — vehicle başka şirketin olabilir mi)?
4. Sanctum token scope/ability kullanımı var mı? Mobil token ile süper admin endpoint'lerine erişilebilir mi?
5. Dosya yükleme yollarında path traversal / public erişim açığı var mı (`storage/app/public` symlink ve Policy doğrulaması)?
6. Hassas alanlar (`api_token`, `password`, `remember_token`) `$hidden` array'inde mi?
7. Rate limiting `routes/api.php` üzerinde uygulanmış mı?
8. CORS yapılandırması mobil için doğru mu (`config/cors.php`)?

**Çıktı:** "Güvenlik & Tenant İzolasyonu — Riskler" raporu, her madde için CVSS benzeri bir önem derecesi (Kritik/Yüksek/Orta/Düşük).

---

### ADIM 8 — Final rapor: Yol Haritası
Tüm bulguları birleştirip 3 bölümlü bir yol haritası yaz:

**A) HEMEN YAPILMASI GEREKENLER (1-2 hafta):**
Üretimi etkileyen bug'lar, güvenlik açıkları, kritik eksik API endpoint'leri.

**B) KISA VADELİ (2-6 hafta):**
Web'de var olup mobilde olmayan modüllerin mobile aktarılması, eksik CRUD aksiyonları, push notification altyapısı.

**C) ORTA VADELİ (6+ hafta):**
İyileştirmeler — offline desteği, dark mode, i18n, performans optimizasyonları, test coverage, CI/CD.

Her madde için: **dosya yolları**, **etkilenen modül**, **tahmini efor (S/M/L)**, **bağımlılıklar (önce şunu bitir, sonra bu)**.

---

### KURALLAR
- Tahmin etme, **dosyaları gerçekten oku**. Her iddia için dosya yolu + satır numarası ver.
- Türkçe raporla.
- Markdown başlıklar, tablolar ve kod blokları kullan.
- "Muhtemelen", "sanırım" gibi belirsiz ifadeler yerine "şu dosyada şu var/yok" diye somut konuş.
- Bulguları abartma da, küçümseme de — projeyi olduğu gibi ortaya koy.
- Final raporu tek bir markdown dosyası olarak `PROJE_ANALIZ_RAPORU.md` adıyla projenin köküne kaydet.

Hazırsan **ADIM 1**'den başla.

## 📋 PROMPT BURADA BİTİYOR

---

## Bu promptu nasıl kullanacaksın

1. Antigravity'i aç, ServisPilot projesini workspace olarak seç.
2. Yeni bir Cascade/agent konuşması başlat.
3. Yukarıdaki "BURADAN AŞAĞISI" işaretinden "PROMPT BURADA BİTİYOR" işaretine kadar olan bölümü kopyala.
4. Yapıştır ve gönder.
5. Antigravity her adımda sana ara özet verecek; "devam" diyerek ilerlet. İstersen bir adımda durup detaya inebilirsin.

## İpuçları

- Antigravity bazen ilk adımda yüzeysel kalabilir. Eğer bir adımın çıktısı yetersizse şunu de: *"Bu adımda yüzeysel kaldın. {modül adı} için dosyaları satır satır okuyup tekrar yap."*
- Final raporu (`PROJE_ANALIZ_RAPORU.md`) elinde olduğunda bana getir; oradaki maddeleri öncelik sırasına göre tek tek çözmeye başlayalım.
- "Yetkilendirme/multi-tenant" ve "API parity matrisi" en değerli iki çıktı olacak. Onları gözden kaçırma.
