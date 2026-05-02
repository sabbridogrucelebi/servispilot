# GÜVENLİK VE TENANT İZOLASYONU RAPORU (ADIM 7)

Bu rapor, ServisPilot SaaS platformundaki Super Admin yetkilendirme mantığı ve çok kiracılı (multi-tenant) izolasyon zafiyetlerinin analizini içermektedir.

## 1. Yetki Kapsamı Boşlukları (Permission Mismatches)

Süper Admin'in arayüzden atayabildiği "Roller" ve "Permission" kuralları sistemde mevcuttur ancak uygulama katmanında **tam olarak sarılmamış** rotalar bulunmaktadır.

*   **V0 (Legacy) Rotaları: 🚨 KRİTİK**
    *   `Route::apiResource('/vehicles', VehicleController::class)` gibi `api.php` içerisindeki eski endpoint'ler **hiçbir middleware korumasına** sahip değildir.
    *   `app/Http/Controllers/Api/VehicleController.php` içinde sadece `$vehicle->company_id !== $request->user()->company_id` (Firma İzolasyonu) kontrolü yapılmaktadır. Ancak `$user->hasPermission('vehicles.delete')` kontrolü **YOKTUR**.
    *   **Sonuç:** Bir firmadaki "Viewer" (Sadece görüntüleme yetkisine sahip kişi), Postman veya Mobil App üzerinden DELETE isteği atarsa aracı/personeli silebilir. Rolleri yetki olarak korumaz, sadece firma bariyeri sağlar.
    *   **Öneri:** Bu legacy V0 endpoint'ler acilen iptal edilip tamamen V1'e taşınmalı veya V0 controller'larına acilen `$this->userHasPermission(...)` if blokları eklenmelidir. (Tavsiye edilen: Hızlı yama için if bloklarını eklemek, uzun vadede V1'e tamamen geçmek).

## 2. Super Admin ve Sanctum Token Scope Zafiyeti

*   **Mevcut Durum:** `/super-admin/*` prefix'ine sahip rotalar `auth` ve `super_admin` middleware'i ile korunmaktadır. `SuperAdminMiddleware`, kullanıcının `isSuperAdmin()` olup olmadığına bakar.
*   **Mobil Uygulama Etkisi:** Sanctum, mobile login olan kullanıcıya `createToken($device_name)` ile standart bir token verir. Token üzerinde bir `ability` (scope) tanımı yoktur (örneğin `['role:mobile']`).
*   **Sonuç:** Eğer Super Admin, mobil uygulamaya giriş yaparsa, mobil uygulama için verilen token teorik olarak "Super Admin" yetkisini de içinde barındırır. Eğer saldırgan bu token'ı cihazdan çalarsa (XSS, Man-in-the-middle vb.), web üzerindeki `/super-admin` endpoint'lerine API üzerinden istek atabilir.
*   **Öneri:** Mobil cihazlara verilen token'lar `createToken($device_name, ['mobile-client'])` şeklinde oluşturulmalı ve `SuperAdminMiddleware` veya route tanımlarında token'ın web tabanlı yetkiye sahip olup olmadığı da kontrol edilmelidir.

## 3. BelongsToCompany Model İzolasyon Zafiyetleri

`BelongsToCompany` global scope trait'i kullanmayan 5 kritik model tespit edilmişti. Bunların sızıntı potansiyelleri aşağıdadır:

1.  **`PayrollLock`:** (🚨 KRİTİK Sızıntı)
    *   `PayrollController.php` içindeki kilit açma/kapama fonksiyonu `PayrollLock::where('period', $period)->first();` sorgusunu çalıştırır.
    *   Bu tabloda `company_id` yoktur. Eğer A firması Nisan ayını kilitlerse, B firması da kilitlenmiş olur (Denial of Service - Veri blokajı).
2.  **`ChatGroup` & `Message`:** (⚠️ Orta Risk)
    *   Modellerde scope yok ancak Controller içinde manuel `$companyId` parametresi ile `where()` sorgusu yazılmış. Kod bağlamında sızıntı yok. Ancak Route Model Binding kullanılırsa (ileride), firma sızıntısına çok açıktır.
3.  **`VehicleImage`:** (✅ Düşük Risk)
    *   Silme işleminde `$image->vehicle_id !== $vehicle->id` kontrolü yapılarak araç üstünden firmaya bağlanmış. Güvenli.
4.  **`VehicleTrackingSetting` (Arvento):** (🚨 KRİTİK Sızıntı)
    *   Model globale atılmış. Sadece `where('provider', 'arvento')` sorgusu yapılıyor.
    *   Sistemde tek bir Arvento credential paylaşıyor demektir. Eğer her firma kendi Arvento bilgilerini girmeliyse, veriler birbirine karışacaktır.

## 4. Mobil Önbellek (Cache TTL) ve Permission Senkronizasyonu

*   **Sorun:** Super Admin veya Şirket Admini, web üzerinden bir personelin yetkisini (örn: silme yetkisini) aldığında, mobil cihazda bu durum **asla anlık olarak yansımaz.** Mobil uygulama, giriş yaptığı andaki `userInfo` bilgisini `AsyncStorage` üzerinde süresiz tutar. Sadece uygulama kapatılıp açıldığında (Splash Screen aşamasında) `/me` endpoint'inden veriyi çeker. Uygulama arkada çalışmaya devam ediyorsa yetki güncellemesi alınmaz.
*   **Strateji Önerisi:**
    1.  `users` tablosuna `permissions_updated_at` (Timestamp) sütunu eklenmelidir.
    2.  Her API yanıtı (tüm V1 endpointleri) header'ında `X-Permissions-Updated-At` değerini dönmelidir.
    3.  Mobil tarafta `axios.interceptors.response` içerisinde bu header okunmalı; eğer AsyncStorage'daki tarihten daha yeniyse arka planda otomatik bir `/me` isteği atılarak kullanıcının hakları (permissions array'i) sessizce güncellenmelidir. Böylece admin yetkiyi aldığı an, kullanıcının bir sonraki sayfaya geçişinde butonlar anında kaybolur.
