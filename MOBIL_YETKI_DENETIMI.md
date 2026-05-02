# MOBİL YETKİ (PERMISSION) DENETİMİ (ADIM 6.B)

Bu belge, mobil uygulamadaki ekranların ve içerisindeki aksiyon butonlarının (Ekle, Sil, Düzenle vb.) yetki kısıtlamalarını incelemektedir. Web tarafında **Süper Admin** ve **Şirket Admini** tarafından atanan Roller ve Permission kuralları, mobil uygulamada **birebir** uygulanmalıdır. Aksi halde sistem bir güvenlik açığı oluşturur.

## 1. Yetki Haritası ve Eksiklik Matrisi

| Ekran | Aksiyon / Buton | Web Tarafı İzin Karşılığı | Mobilde Gate (hasPermission) Var mı? | Ne Yapılmalı? (Acil Çözüm Önerisi) |
| :--- | :--- | :--- | :---: | :--- |
| **Araçlar Listesi** (`VehiclesScreen`) | "İlk Aracı Ekle" / FAB Ekle Butonu | `vehicles.create` | ❌ YOK (Herkes görebilir) | `hasPermission('vehicles.create')` kontrolü ile sarılmalı, yetkisi olmayanlarda FAB gizlenmeli. |
| **Araçlar Listesi** (`VehiclesScreen`) | "Aracı Düzenle" (Bottom Sheet) | `vehicles.edit` | ❌ YOK | Sadece `vehicles.edit` yetkisi olana ActionSheet'te gösterilmeli. |
| **Araçlar Listesi** (`VehiclesScreen`) | "Aracı Sil" (Bottom Sheet) | `vehicles.delete` | ❌ YOK | Sadece `vehicles.delete` yetkisi olana gösterilmeli + Backend V1 API'ye taşınmalı. |
| **Araç Detay** (`VehicleDetailScreen`) | Düzenle İkonu (Sağ üst vb.) | `vehicles.edit` | ❌ YOK | Yetkisi olmayanlarda ikon render edilmemeli. |
| **Araç Galerisi** (`VehicleGalleryScreen`) | "Resim Yükle" Butonu | `vehicles.edit` veya `vehicles.create` | ❌ YOK | Sadece yükleme yetkisi olanlarda yükleme butonu görünmeli. |
| **Araç Galerisi** (`VehicleGalleryScreen`) | "Resmi Sil" Butonu | `vehicles.delete` | ❌ YOK | Sil butonu ve Swipe-to-delete özelliği yetki ile kısıtlanmalı. |
| **Araç Belgeleri** (`VehicleDocumentsScreen`)| "Yeni Belge Ekle" (Modal) | `documents.create` | ❌ YOK | Belge yükleme formunu sadece yetkili kullanıcılar açabilmeli. |
| **Araç Yakıtları** (`VehicleFuelsScreen`) | "Yeni Yakıt Ekle" | `fuels.create` | ❌ YOK | Yetki yoksa ekleme FAB'ı veya butonu render edilmemeli. |
| **Yaklaşan Muayeneler** (`UpcomingInspectionsScreen`) | "Yeni Kayıt Ekle" FAB | `vehicles.edit` | ❌ YOK | Ekleme/Güncelleme yetkisi yoksa FAB gizlenmeli. |
| **Yaklaşan Sigortalar** (`UpcomingInsurancesScreen`) | "Yeni Kayıt Ekle" FAB | `vehicles.edit` | ❌ YOK | Ekleme/Güncelleme yetkisi yoksa FAB gizlenmeli. |
| **Seferler** (`TripsScreen`) | Durum Değiştir (Başlat/Bitir) | `trips.edit` | ❌ YOK (Aksiyon yok zaten) | Gelecekte eklenecek olan sefer başlatma aksiyonları sadece ilgili şoför veya operasyon yetkilisine açılmalı. |
| **Pilot Chat** (`PilotChatScreen`) | Yeni Grup Oluştur | Yok (Manuel Backend Kontrolü) | ❌ YOK | Rol/yetki bazlı yeni konuşma başlatma kısıtları arayüzde gizlenmeli. |

## 2. Sistemsel Çözüm Önerisi (Nasıl Yapılmalı?)

1. **Backend /me Güncellemesi:** Laravel `AuthController@me` fonksiyonu güncellenerek `permissions` array'i frontend'e gönderilmelidir.
2. **AuthContext Güncellemesi:** Mobilde `AuthContext.js` içerisine şu fonksiyon eklenmelidir:
   ```javascript
   const hasPermission = (permissionKey) => {
       if (userInfo?.is_company_admin) return true; // Admin her şeyi yapar
       return userInfo?.permissions?.includes(permissionKey);
   };
   ```
3. **UI Elementlerini Sarmalama:** Ekranlardaki tüm kritik butonlar bu fonksiyon ile koşullu render edilmelidir:
   ```jsx
   {hasPermission('vehicles.create') && (
       <TouchableOpacity onPress={showAddModal}>
           <Text>Araç Ekle</Text>
       </TouchableOpacity>
   )}
   ```
