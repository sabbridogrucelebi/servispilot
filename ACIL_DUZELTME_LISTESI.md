# ACİL DÜZELTME LİSTESİ (Kırmızı Bayraklar)

Bu belge, ServisPilot SaaS platformundaki kritik mimari hataları ve hemen düzeltilmesi gereken güvenlik/izolasyon açıklarını listeler. Bu maddeler, projenin final "Yol Haritası" raporunun (A) bölümünde detaylandırılacaktır.

## A1. V0 (Legacy) API Endpoint'leri Yetkisiz Erişim Zafiyeti (Kritik)
`routes/api.php` içerisindeki `Route::apiResource('/vehicles', VehicleController::class)` gibi eski (V0) endpoint'lerde hiçbir `permission:` middleware'i veya `hasPermission()` kontrolü bulunmamaktadır. Sisteme giriş yapmış "Viewer" (sadece okuma yetkisi olan) bir kullanıcı, API'ye doğrudan `DELETE /api/vehicles/1` isteği göndererek araç silebilir. Bu durum acil bir güvenlik açığıdır.

## A2. `/me` Endpoint'inde İzin (Permissions) Eksikliği (Kritik)
Mobil uygulamanın rol ve yetkilerini aldığı `/me` endpoint'i (`AuthController@me`), kullanıcının `role` bilgisini dönmekte ancak `permissions` dizisini göndermemektedir. Bu nedenle mobil uygulama kullanıcının eylem (örn. "vehicles.delete") bazlı yetkilerini bilemez ve rollere özgü güvenli bir state oluşturamaz.

## A3. Mobil Uygulamada "Sıfır" Yetki Gate'i (Kritik)
Mobil tarafta (`mobile-app/src/screens/*`) hiçbir buton veya aksiyon (`hasPermission` gibi bir yardımcı fonksiyon ile) yetki kontrolüne tabi tutulmamaktadır. Uygulama sadece "görüntüleme kabuğu" olarak çalışmakta; ekleme, düzenleme ve silme butonları her kullanıcıya açık şekilde gösterilmektedir.

## 1. Multi-Tenant İzolasyon Açıkları (Kritik)

Sistemin çok kiracılı (multi-tenant) doğası gereği, verilerin `company_id` veya `BelongsToCompany` global scope'u ile izole edilmesi gerekir. Aşağıdaki tablolarda/modellerde bu izolasyon **eksiktir veya tamamen yoktur**:

*   **`payroll_locks` Tablosu:**
    *   **Sorun:** `company_id` sütunu içermiyor. Bir firma belirli bir maaş dönemini kilitlediğinde, sistemdeki diğer tüm firmaların da o dönemi kilitlenmektedir.
    *   **Çözüm:** Migration ile `company_id` eklenmeli, model `BelongsToCompany` trait'ini kullanmalı ve kontrollerde `where('company_id', ...)` filtrelemesi yapılmalıdır.
*   **`VehicleTrackingSetting` Modeli:**
    *   **Sorun:** `BelongsToCompany` trait'ini kullanmıyor. Web panelde sadece `provider = arvento` sorgusu yapılıyor. Bu, SaaS sistemindeki her firmanın tek bir Arvento API kimlik bilgisini paylaştığı anlamına gelir.
    *   **Çözüm:** Model scope'lanmalı, ayarlar firmaya özgü hale getirilmelidir.
*   **`ChatGroup` ve `Message` Modelleri:**
    *   **Sorun:** Her ikisi de `BelongsToCompany` kullanmıyor.
    *   **Çözüm:** Mesajlaşma altyapısının firmalar arasında izolasyonu sağlanmalıdır (Firma A'nın şoförü Firma B'yi görememeli).

## 2. Ölü Route'lar (Tanımlı ama Olmayan Controller Metotları)

`routes/web.php` dosyasında tanımlı olmalarına rağmen karşılık gelen sınıflarda metotları bulunmayan rotalar (sisteme 500 veya 404 hatası döndürür):

*   `GET fuels/import` ➔ `App\Http\Controllers\FuelController@import` (Eksik)
*   `GET documents/{document}` ➔ `App\Http\Controllers\DocumentController@show` (Eksik)

## 3. Ölü Linkler (Blade View'larda Kırık Butonlar)

Frontend dosyalarında `route(...)` fonksiyonu ile çağrılan ancak rotalarda tanımlı olmayan (404/500 verdiren) çağrılar:

*   `resources/views/auth/reset-password.blade.php`: `route('token')` çağrılmış.
*   `resources/views/profile/partials/delete-user-form.blade.php`: `route('profile.destroy')` çağrılmış (sadece `edit` ve `update` mevcut).
*   `resources/views/vehicles/partials/tabs/fuels.blade.php`: `route('vehicle')` isimli eksik/yanlış parametreli bir çağrı var.
