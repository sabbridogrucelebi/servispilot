# SERVİSPİLOT PROJE ANALİZ VE YOL HARİTASI RAPORU

## 1. YÖNETİCİ ÖZETİ

Bu rapor, ServisPilot SaaS platformunun ve entegre mobil uygulamasının (React Native) mimari, güvenlik, tenant izolasyonu ve kullanıcı deneyimi açısından uçtan uca denetlenmesi sonucunda hazırlanmıştır. Yapılan kapsamlı analizler, projenin web tarafında olgun bir yapıya ulaştığını, ancak mobil uygulamanın yetki ve güvenlik açısından "sadece bir görüntüleme kabuğu" seviyesinde kaldığını ortaya koymuştur. 

Bu durum, sistemi ciddi veri sızıntısı ve yetkisiz müdahale risklerine açık hale getirmektedir. Bu raporda yer alan yol haritası, platformun teknik borçlarını kapatmayı, mobil uygulamayı %100 yetki uyumlu tam bir CRUD istemcisine dönüştürmeyi ve tasarım sistemini "Ultra-Pro" seviyesine taşımayı hedefleyen **3 Fazlı (Hemen, Kısa Vade, Orta Vade)** bir uygulama planıdır.

### En Kritik 5 Bulgu
1. **V0 Legacy API Güvenlik Açığı:** Mobilin kullandığı eski API uçlarında rol/yetki (permission) kontrolü yoktur; yetkisiz (Viewer) bir kullanıcı araç silebilir.
2. **Maaş Kilidi (Payroll Lock) Sızıntısı:** `payroll_locks` tablosunda `company_id` bulunmamaktadır. Bir şirketin maaşları kilitlediğinde tüm sistemdeki şirketlerin kilitlenmesi (Denial of Service) riski vardır.
3. **Mobil Yetki Körlüğü:** `/me` endpoint'i mobilde kullanıcıya `permissions` dizisini iletmemektedir; bu nedenle mobil uygulama yetkiye dayalı hiçbir butonu gizleyememektedir (Sıfır Gate).
4. **Tenant İzolasyon Eksikliği:** Arvento entegrasyonu ve mesajlaşma gibi kritik modellerde global `BelongsToCompany` tenant scope'u uygulanmamıştır.
5. **Kopuk Modüller:** Servis Rotaları, Sözleşmeler, İstasyonlar ve Canlı Araç Takip gibi kritik modüller mobilde hiç bulunmamakta, mevcut olanlar ise eksik çalışmaktadır.

### 🎯 Çekirdek Vizyon (Proje Anayasası)
Aşağıdaki 3 kural, projenin bundan sonraki her satır kodunda merkeze alınacaktır:
- **KURAL 1 — Yetki Senkronu:** Web panelde geçerli olan her bir rol ve permission kuralı, mobilde de birebir geçerlidir.
- **KURAL 2 — Mobil = Tam CRUD İstemcisi:** Mobil uygulama sadece veri okumaz; yetkisi olan kullanıcı mobilden tam kapasite Ekleme, Düzenleme ve Silme yapabilir.
- **KURAL 3 — Mobil Tasarım Yenilemesi:** `VehiclesScreen` dışındaki tüm ekranlar Ultra-Pro standartlarında (3D ikon, Glassmorphism, Gradient KPI) baştan tasarlanacaktır.

### Efor ve Takım Tahmini
*   **Toplam Tahmini Efor:** 45 - 60 Kişi-Gün
*   **Önerilen Takım:** 1 Backend Developer (Laravel), 1 Mobile Developer (React Native), 1 UI/UX Designer.

---

## 2. PROJENİN ŞU ANKİ DURUMU

*   **Web Paneli Olgunluk Oranı:** %90 (Çekirdek modüller aktif, stabil çalışıyor).
*   **Mobil Uygulama Olgunluk Oranı:** %35 (Sadece okuma ağırlıklı, eksik modül sayısı yüksek, tasarım sistemi oturmamış).
*   **Web ↔ Mobil Parity (Eşleşme) Oranı:** %40 (Web özelliklerinin yarısından azı mobilde mevcut).
*   **Güvenlik Skoru (Açıklar):**
    *   **Kritik:** 3 (V0 Yetki Bypass, Payroll Lock İzolasyonu, Mobil Yetki Körlüğü)
    *   **Yüksek:** 2 (Arvento Global Modeli, Token Scope Eksikliği)
    *   **Orta:** 3 (Chat İzolasyonu eksikliği, Hata yakalama zafiyetleri, Ölü Rotalar)
    *   **Düşük:** 2 (Ölü Blade linkleri, Placeholder eksikleri)
*   **Tasarım Borç Skoru:** Yüksek (Mobilde 4-8-12-16 kuralına uyulmamış, empty state'ler eksik, hiyerarşi düz).

---

## 3. ÇEKİRDEK VİZYON (PROJE ANAYASASI)

Bu yol haritası aşağıdaki üç temel kural etrafında şekillendirilmiştir ve her sprint bu kurallara göre denetlenecektir:

**Kural 1 — Yetki Senkronu:** Süper admin'in (ve şirket admin'inin) `permissions` tablosunda tanımladığı her yetki kuralı (`vehicles.view`, `vehicles.create`, `vehicles.update`, `vehicles.delete`, `customers.*`, `trips.*`, `payrolls.*`, vb.) hem web panelde hem mobil uygulamada birebir aynı şekilde uygulanır. Bir viewer kullanıcısı her iki yüzeyde de düzenle/sil butonlarını görmez. Bir accounting kullanıcısı her iki yüzeyde de bordro işlemlerine erişebilir, ancak sefer silemez. Yetki tek bir kaynaktan (`permissions` tablosu) gelir, iki istemci de buna harfiyen uyar.

**Kural 2 — Mobil = Tam CRUD İstemcisi:** Mobil uygulama salt-görüntüleme aracı DEĞİLDİR. Süper admin bir kullanıcıya `vehicles.create`, `customers.update`, `trips.delete` yetkisi verdiğinde, o kullanıcı mobilden de:
*   Yeni araç ekleyebilmeli (form + foto + belge yükleme)
*   Mevcut müşteriyi düzenleyebilmeli (tüm alanlar)
*   Sefer silebilmeli (onay diyaloğu + soft delete + activity log)
*   Yakıt fişi/bakım kaydı ekleyebilmeli
*   Bordro hesaplaması başlatabilmeli (yetkisi varsa)
*   Cezayı kaydedebilmeli, evrak yükleyebilmeli.
Yani her web modülünün her aksiyonu mobilde de — kullanıcının yetkisi varsa — çalışıyor olmalı. Read, Create, Update, Delete operasyonlarının tamamı iki platformda da senkron olmalıdır.

**Kural 3 — Mobil Tasarım Yenilemesi:** `mobile-app/src/screens/VehiclesScreen.js` (ve ona doğrudan bağlı olan `VehicleDetailScreen`, `VehicleDocuments`, `VehicleFuels`, `VehicleMaintenances`, `VehiclePenalties`, `VehicleReports`, `VehicleGallery`) MEVCUT TASARIMIYLA KORUNUR — DOKUNULMAZ. Diğer tüm ekranlar (`Home`, `Dashboard`, `Menu`, `Personnel`, `PersonnelDetail`, `Payroll`, `PayrollDetail`, `Customers`, `CustomerDetail`, `Trips`, `TripDetail`, `Reports`, `Activity`, `Finance`, `PilotChat`, `Profile`, `Login`, `UpcomingInspections`, `UpcomingInsurances`) `MOBIL_DESIGN_SYSTEM.md` doğrultusunda tamamen yeniden tasarlanır: 3D ikonlar, gradient KPI kartları, glassmorphism, 4-8-12-16-24-32 pt spacing sistemi, smooth reanimated geçişler, dark mode tutarlılığı, empty/loading/error state'leri eksiksiz uygulanır.

---

## 4. FAZ A — HEMEN (Sprint 0, 1-2 hafta) 🚨

Üretimi ve güvenliği doğrudan etkileyen, derhal çözülmesi gereken sorunlar paketi.

| Task ID | Sorumlu | Sorun / Tanım | Çözüm ve Teknik Adımlar | Efor | Doğrulama (Definition of Done) |
| :--- | :--- | :--- | :--- | :---: | :--- |
| **A1** | Backend | `payroll_locks` tablosunda Tenant İzolasyonu yok. Tüm firmalar kilitleniyor. | 1. Migration oluştur: `table->foreignId('company_id')`. 2. Mevcut datayı backfill et (default company veya truncate). 3. Unique constraint ekle: `[company_id, period]`. 4. Model'e `BelongsToCompany` ekle. | S | Unit test yazılacak: A firması kilitlediğinde B firması etkilenmeyecek. Veritabanı constraint'i devrede olacak. |
| **A2** | Backend | Kapsamsız Modeller (`VehicleTrackingSetting`, `ChatGroup`, `Message`, `VehicleImage`). | Modellerin içine `use BelongsToCompany;` eklenecek. Controller içindeki fazladan `where('company_id')` kodları temizlenip global scope'a güvenilecek. | M | Rota model bağlama (Route Binding) ile id'si bilinen başka firmanın verisine erişim test edilip 404/403 alındığı kanıtlanacak. |
| **A3** | Backend | V0 API Endpoint'lerinde (`apiResource('/vehicles')`) yetki zafiyeti. | Geçici tampon çözümü: V0 Controller fonksiyonlarına (örn: `destroy`, `update`) `abort_unless($this->userHasPermission(...), 403)` kontrolü acilen eklenecek. | S | Postman ile 'Viewer' rolündeki token üzerinden DELETE /api/vehicles/1 isteği atıldığında 403 Forbidden alınacak. |
| **A4** | Backend | `/me` endpoint'i `permissions` listesini mobillere dönmüyor. | `AuthController@me` güncellenerek `user->permissions->pluck('key')` dizi halinde JSON cevabına eklenecek. | S | Postman testinde `/me` dönen objesinde `["vehicles.view", "fuels.create"...]` listesi açıkça görülecek. |
| **A5** | Mobile | Token güvenliği zafiyeti. `AsyncStorage` plaintext tutuyor. | `expo-secure-store` kütüphanesi kurularak `userToken` buraya taşınacak. | S | Kodda `AsyncStorage.getItem('userToken')` kalmayacak. iOS Keychain ve Android Keystore kullanımı sağlanacak. |
| **A6** | Backend | Ölü rotalar ve kırık Blade linkleri (Sistem 500 hataları). | `fuels/import`, `documents/{document}` rotaları temizlenecek. `reset-password`, `delete-user-form` ve `fuels.blade.php` içindeki `route()` çağrıları düzeltilecek. | S | `php artisan route:list` hatasız çalışacak, Blade dosyalarında regex taraması sıfır kırık link dönecek. |
| **A7** | Mobile | 401 Interceptor sert çıkış yapıyor. | `axios.js` güncellenecek. 401 düştüğünde direkt silmek yerine sessizce login sayfasına yönlendirip state sıfırlayan akış yazılacak (Soft logout). | S | Süresi dolmuş token ile istek atıldığında uygulama çökmeyecek, login ekranına smooth geçiş yapacak. |

---

## 5. FAZ B — KISA VADE (Sprint 1-3, 2-6 hafta) 🔧

Bu faz, Kural 1 (Yetki Senkronu) ve Kural 2'nin (Mobil Tam CRUD) hayata geçirilmesini kapsar.

### B.1 — Backend Yetki Birleştirme (Kural 1)

*   **V0 -> V1 Migration:** `VehicleController`, `PersonnelController`, `CustomerController` ve `TripController` içindeki tüm eski uçlar iptal edilecek, sadece `V1/*ApiController` kullanılacak.
*   **Route Koruması:** `routes/api.php` içerisindeki tüm V1 endpoint grupları `middleware('permission:...')` ile sarmalanacak (Böylece Controller içi if'lere gerek kalmadan gate sağlanacak).
*   **Token Ability:** Mobil login esnasında `createToken('mobile', ['mobile-client'])` yetkisi verilecek. `/super-admin` rotaları ise API üzerinden `tokenCan('super-admin')` kontrolü ile dışarıya kapatılacak.
*   **X-Permissions-Updated-At:** `User` modeline timestamp eklenecek. Yetki değiştiğinde güncellenecek. Her V1 API yanıt header'ında bu tarih dönülecek.

### B.2 — Mobil Yetki Altyapısı (Kural 1)

*   **`AuthContext` Helper'ları:**
    ```javascript
    const hasPermission = (perm) => userInfo?.is_company_admin || userInfo?.permissions?.includes(perm);
    const hasRole = (role) => userInfo?.role === role;
    ```
*   **Gate Uygulaması:** Uygulamadaki tüm Floating Action Button'lar (FAB), Context Menu'ler ve Swipe Action'lar `{hasPermission('x.create') && <FAB />}` mantığıyla sarılacak.
*   **UI/UX Geribildirimi:** Manuel API çağrılarında (istisnai) 403 hatası dönerse, Alert ile "Bu işlemi yapmak için yetkiniz bulunmamaktadır." mesajı gösterilecek.

### B.3 — Mobil CRUD Tamamlama Matrisi (Kural 2)

Mobilde sadece izlenebilen modüller tam operasyonel hale getirilecektir.

| Modül | Mevcut Mobil Aksiyon | Eklenmesi Gereken | Form Alanları ve Girdiler | Efor | DoD (Kabul Kriteri) |
| :--- | :--- | :--- | :--- | :---: | :--- |
| **Customers** | Sadece Okuma | Create, Update, Delete | Firma Adı, İletişim Kişisi, Tel (Maskeli), Vergi No, Adres. | M | Müşteri başarıyla eklenip web panele yansıyacak. Delete işlemi için 'Emin misiniz?' diyaloğu çalışacak. |
| **Trips** | Sadece Okuma | Create, Update, Delete, Status | Araç seçimi, Sürücü Seçimi, Müşteri, Başlangıç/Bitiş lokasyonu, Tarih, Saat picker. | L | Validasyonlar web ile aynı olacak. Sefer başlat/bitir aksiyonları log tablosuna düşecek. |
| **Fuels** | Sadece Okuma | Update, Delete | Fiş yükleme, Litre, Tutar, İstasyon seçimi (Select box). | M | Yakıt kaydı silindiğinde istasyon bakiyesi otomatik güncellenecek. Foto yükleme başarılı olacak. |
| **Personnel** | Sadece Okuma | Create, Update, Delete | Ad, Soyad, TC No, Ehliyet Sınıfı, Maaş Tipi. | M | Personel fotoğrafı kırpma aracıyla eklenecek. |
| **Payrolls** | Sadece Okuma | Generate (Hesapla), Update | Dönem seçimi, Ek kesinti/Avans girişleri. | L | Yalnızca yetkili rol (Accounting) bu butonları görecek. Net maaş anlık formülü çalışacak. |

### B.4 — Mobilde Eksik Modüller (Sıfırdan)

1.  **Service Routes & Route Stops:** Harita destekli durak listeleme ve rota belirleme ekranları.
2.  **Fuel Stations (İstasyonlar):** İstasyon bazlı bakiye ve ödeme (Tahsilat) girme ekranları.
3.  **Canlı Araç Takip:** Arvento API'sini (V1 üzerinden) tüketerek `react-native-maps` ile araçların son konumlarının haritada gösterilmesi.
4.  **Activity Log:** Sisteme giriş yapan, veri silen veya güncelleyen herkesin anlık takip akışı (Infinite scroll destekli).
5.  **Push Notifications:** `expo-notifications` ile FCM (Android) ve APNs (iOS) entegrasyonu. (Yaklaşan bakım ve sigorta tebligatları).

---

## 6. FAZ C — ORTA VADE (Sprint 4-8, 6-12 hafta) 🎨

Kural 3 (Mobil UI/UX Yenilemesi) doğrultusunda uygulamanın `MOBIL_DESIGN_SYSTEM.md`'ye göre baştan yaratılması aşaması.

**ÖNEMLİ KURAL TEKRARI:** `VehiclesScreen` ve detay/alt ekranları mevcut tasarımıyla **KORUNACAKTIR**.

### Redesign Görev (Task) Listesi Örneği

*   `[ ]` **Task C-01:** `HomeScreen.js` Redesign
    *   **Kapsam:** VehiclesScreen hariç kuralına uygun. Web'deki `dashboard.blade.php` karşılığı.
    *   **Yeni Bileşenler:** Gradient header, Avatar, `react-native-animated-numbers` ile sıfırdan yukarı sayan ana KPI metrikleri.
    *   **3D İkonlar:** `assets/icons/3d/chart.png` vb. kullanımı (Lottie yerine PNG stratejisi).
    *   **Efor:** L
    *   **DoD:** Cihazlarda (iOS/Android) pixel-perfect kontrolü, Dark/Light Mode switch uyumluluğu testi yeşil verecek.

*   `[ ]` **Task C-02:** `CustomersScreen.js` ve `CustomerDetail.js` Redesign
    *   **Kapsam:** Liste ve Detay ekranları.
    *   **Yeni Bileşenler:** Kartların üstünde `Glassmorphism` efektli etiketler, yatay `FilterChip` bileşenleri.
    *   **Empty State:** "Müşteri Yok" durumu için 120x120px 3D ikonlu, yönlendirici butonlu tasarım.
    *   **Gate:** B.2 adımından gelen `hasPermission` kuralı uygulanmış "Yeni Müşteri" FAB butonu.
    *   **Efor:** M
    *   **DoD:** Liste çekilirken skeleton loading gösterilecek, pull-to-refresh sorunsuz çalışacak.

### Altyapı ve Hazırlıklar
*   **Dark Mode:** `react-native-appearance` veya Expo kancaları ile sistem teması dinlenerek renk paletleri (Zemin: `#0F172A`, Kart: `#1E293B`) dinamik hale getirilecek.
*   **i18n:** `i18next` kurularak tüm metinler (stringler) dil dosyalarına taşınacak (TR/EN altyapısı kurulacak).
*   **Offline-First:** `AsyncStorage` ile son çekilen listeler cache'lenecek. İnternet kesildiğinde kırmızı bir barda "Çevrimdışısınız, önbellek verisi gösteriliyor" mesajı verilecek.
*   **Erişilebilirlik (A11y):** Font boyutlandırması kullanıcı cihaz ayarlarına saygı duyacak, minimum dokunma alanı her touchable element için 44x44pt olacak.

---

## 7. SPRINT PLANI VE GANTT ÇİZELGESİ

Aşağıdaki takvim, 16 haftalık (8 Sprint x 2 Hafta) önerilen iş dağılımını gösterir.

```mermaid
gantt
    title ServisPilot Geliştirme Yol Haritası
    dateFormat  YYYY-MM-DD
    
    section Faz A (Sprint 0)
    A1: Payroll Tenant İzolasyonu          :a1, 2026-05-01, 3d
    A2: Model Scope Revizyonu              :a2, after a1, 2d
    A3: V0 Yetki Tamponlaması              :a3, after a2, 2d
    A4 & A5: Token, /me İzinleri ve Store  :a4, after a3, 3d
    A6 & A7: Ölü Linkler & Soft Logout     :a5, after a4, 2d
    
    section Faz B (Sprint 1-3)
    B1: V0 -> V1 Migration                 :b1, 2026-05-15, 14d
    B2: Mobil Yetki (hasPermission) Gate'leri:b2, 2026-05-15, 7d
    B3: Mobil CRUD (Trips, Customers vb.)  :b3, after b2, 14d
    B4: Yeni Eksik Modüllerin Eklenmesi    :b4, after b3, 14d
    
    section Faz C (Sprint 4-8)
    C1: UI/UX Component Library Üretimi    :c1, 2026-06-25, 10d
    C2: Dashboard & Reports Redesign       :c2, after c1, 14d
    C3: Diğer Modüllerin Redesign Süreci   :c3, after c2, 20d
    C4: Dark Mode, i18n & Push Release     :c4, after c3, 10d
```

---

## 8. BAŞARI METRİKLERİ (KPI)

Projenin başarısı, aşağıdaki somut metriklerle ölçülecektir:

1.  **Web ↔ Mobil API Parity Oranı:** Mevcut %40 ➔ **Hedef %100** (Web'de olan her data, mobilde de okunur/yazılır olacak).
2.  **Mobilde Permission Gate Kapsamı:** Mevcut %0 ➔ **Hedef %100** (Tüm aksiyonlu butonlar yetki zırhı ardında olacak).
3.  **API V1 Migration:** Mevcut %X ➔ **Hedef %100** (V0 endpoint'leri tarihe karışacak).
4.  **Tenant İzolasyon Kapsamı:** Mevcut eksik (5 Model) ➔ **Hedef %100** (Her veri sadece kendi şirketine ait olacak).
5.  **Test Coverage (PHPUnit):** Kritik API uçları ve permission testleri için **Minimum %70** kapsam hedeflenmektedir.
6.  **Performans (Mobil):** React Native FlashList geçişleri ile liste render süreleri %40 iyileştirilecektir.

---

## 9. RİSK KAYDI VE MİTİGASYON

| Risk Tipi | Olasılık | Etki | Mitigasyon (Önlem) Stratejisi |
| :--- | :---: | :---: | :--- |
| **V0 -> V1 Geçişinde Canlıda (Prod) Regresyon** | Orta | Yüksek | API geçişleri Feature Flag ile kademeli açılacak, eski V0 uçları yeni V1 devreye girene kadar paralel çalıştırılıp loglanacak. |
| **Mobil UI/UX Redesign Sırasında Kullanıcı Kafa Karışıklığı** | Düşük | Orta | Yeni tasarım devrede olduğunda kullanıcılara "Onboarding Tour" gösterilecek, eski alışkanlıkları için "Yeni Tasarım" ipuçları sunulacak. |
| **Push Notification Gecikmesi (FCM/APNs)** | Orta | Orta | Apple Developer hesabı ve Firebase Console ayarları Sprint 0 bitmeden onaylatılıp sertifika hazırlıkları erkenden yapılacak. |
| **Mobil Güncelleme Onay (Review) Gecikmesi** | Yüksek | Düşük | Apple ve Google Play mağaza inceleme süreçleri için Expo OTA (Over-the-Air) güncellemeleri veya EAS Update aktif edilecek. |

---

## 10. EKLER (Referans Belgeler)

Denetim aşamasında üretilen tüm destekleyici ve açıklayıcı belgeler proje kök dizininde bulunmaktadır:

*   [ACIL_DUZELTME_LISTESI.md](file:///c:/xampp/htdocs/servispilot/ACIL_DUZELTME_LISTESI.md) - *Kritik Güvenlik ve Mimari Eksikler Özeti*
*   [WEB_MOBIL_PARITY_MATRISI.md](file:///c:/xampp/htdocs/servispilot/WEB_MOBIL_PARITY_MATRISI.md) - *Hangi modül ne durumda? Kapsamlı CRUD haritası.*
*   [MOBIL_TEKNIK_DENETIM.md](file:///c:/xampp/htdocs/servispilot/MOBIL_TEKNIK_DENETIM.md) - *Expo ve React Native teknik borç dökümü.*
*   [MOBIL_YETKI_DENETIMI.md](file:///c:/xampp/htdocs/servispilot/MOBIL_YETKI_DENETIMI.md) - *Ekran bazlı permission gate eksiklikleri.*
*   [MOBIL_UI_UX_DENETIMI.md](file:///c:/xampp/htdocs/servispilot/MOBIL_UI_UX_DENETIMI.md) - *Tasarım sistemi analizi ve boşluk değerlendirmesi.*
*   [MOBIL_DESIGN_SYSTEM.md](file:///c:/xampp/htdocs/servispilot/MOBIL_DESIGN_SYSTEM.md) - *Gelecek redesign vizyonunun token ve tipografi haritası.*
*   [GUVENLIK_RAPORU.md](file:///c:/xampp/htdocs/servispilot/GUVENLIK_RAPORU.md) - *Süper Admin, Sanctum Scope ve Tenant izolasyon analiz detayları.*

> **Çekirdek Vizyon Hatırlatması:** 
> *1. Yetkiler web ve mobilde birebir aynıdır.* 
> *2. Mobil tam bir okuma/yazma/silme istemcisidir.* 
> *3. VehiclesScreen hariç tüm app Ultra-Pro kalitesinde yeniden tasarlanacaktır.*
