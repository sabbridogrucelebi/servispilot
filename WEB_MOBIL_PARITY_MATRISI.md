# WEB ↔ MOBİL PARİTY (EŞLEŞME) MATRİSİ

**ÖNEMLİ NOT:** Süper Admin ve Şirket Admini tarafından atanan yetkiler (Roller ve İzinler) Web panelde eksiksiz çalışırken, **Mobil uygulamada tamamen devre dışıdır.** Mobil uygulama hiçbir butonu gizlememekte ve "Legacy API" uçlarında backend kontrolü olmadığı için "Viewer" (Sadece İzleyici) rolündeki bir personel bile mobil uygulama üzerinden araç silebilir!

| Modül | Web'de Var mı? | API V1 Endpoint Var mı? | Mobil Ekranı Var mı? | Mobilde CRUD Seviyesi | Web'de Permission Gate | Mobilde Permission Gate | Eksik Aksiyonlar | Öncelik |
| :--- | :---: | :---: | :---: | :---: | :--- | :--- | :--- | :---: |
| **Araçlar (Vehicles)** | ✅ | ✅ (V1 Read, V0 CRUD) | ✅ (`VehiclesScreen`) | R / C / U / D | `permission:vehicles.view` vb. | ❌ (YOK - Butonlar herkese açık) | Mobilde yetki kontrolü sıfır. V0 endpoint'te yetki backend'de de yok! | 🚨 Kritik |
| **Araç Galerisi** | ✅ | ✅ (V1 Read, V0 CRUD) | ✅ (`VehicleGalleryScreen`) | R / C / D | `permission:vehicles.view` | ❌ (YOK) | V1 API üzerinden resim yükleme desteklenmiyor, V0 kullanılıyor. | 🚨 Kritik |
| **Araç Belgeleri** | ✅ | ✅ (V1 Read, V0 CRUD) | ✅ (`VehicleDocumentsScreen`) | R / C / U | `permission:documents.view` | ❌ (YOK) | Arşivleme veya belge indirme (PDF gösterimi) eksik. | Yüksek |
| **Bakımlar** | ✅ | ✅ (V1 Sadece Okuma) | ✅ (`VehicleMaintenancesScreen`) | R (Sadece Okuma) | `permission:maintenances.view`| ❌ (YOK) | Mobilde bakım ekleme/düzenleme/silme ekranı yok. | Orta |
| **Yakıtlar** | ✅ | ✅ (V1 Read, V0 CRUD) | ✅ (`VehicleFuelsScreen`) | R / C | `permission:fuels.view` | ❌ (YOK) | Yakıt silme veya düzenleme mobilde yok. Sadece eklenebiliyor. | Orta |
| **Yakıt İstasyonları** | ✅ | ❌ | ❌ | Yok | `permission:fuels.view` | N/A | İstasyonları mobilden yönetme API'si ve ekranı yok. | Düşük |
| **Trafik Cezaları** | ✅ | ✅ (V1 Sadece Okuma) | ✅ (`VehiclePenaltiesScreen`) | R (Sadece Okuma) | `permission:penalties.view` | ❌ (YOK) | Ceza ödeme (Quick Pay) veya belge yükleme mobilde yok. | Orta |
| **Sürücüler / Personel**| ✅ | ✅ (V1 Sadece Okuma) | ✅ (`PersonnelScreen`) | R (Sadece Okuma) | `permission:drivers.view` | ❌ (YOK) | Personel ekleme/düzenleme/silme mobilde yok. | Yüksek |
| **Müşteriler** | ✅ | ✅ (V1 Sadece Okuma) | ✅ (`CustomersScreen`) | R (Sadece Okuma) | `permission:customers.view` | ❌ (YOK) | Müşteri ekleme/düzenleme/silme mobilde yok. | Yüksek |
| **Müşteri Sözleşmeleri**| ✅ | ❌ | ❌ | Yok | `permission:customers.view` | N/A | Sözleşme yükleme/görme mobilde yok. | Düşük |
| **Müşteri Servis Rotaları**|✅| ❌ | ❌ | Yok | `permission:customers.view` | N/A | Rotaları görme/düzenleme mobilde yok. | Orta |
| **Servis Rotaları** | ✅ | ❌ | ❌ | Yok | `permission:service_routes.view`| N/A | Ana rota modülü mobilde tamamen eksik. | Yüksek |
| **Rota Durakları** | ✅ | ❌ | ❌ | Yok | `permission:route_stops.view` | N/A | Duraklar mobilde yok. | Orta |
| **Seferler (Trips)** | ✅ | ✅ (V1 Sadece Okuma) | ✅ (`TripsScreen`) | R (Sadece Okuma) | `permission:trips.view` | ❌ (YOK) | Seferleri başlatma/bitirme (aktif operasyon) butonları eksik. | 🚨 Kritik |
| **Bordrolar (Payrolls)**| ✅ | ✅ (V1 Sadece Okuma) | ✅ (`PayrollScreen`) | R (Sadece Okuma) | `permission:payrolls.view` | ❌ (YOK) | Bordro kilitleme/yazdırma mobilde yok. | Orta |
| **Finans Özeti** | ✅ | ✅ | ✅ (`FinanceScreen`) | R (Sadece Okuma) | `permission:reports.view` | ❌ (YOK) | Finansal detay dökümleri eksik. | Orta |
| **Raporlar** | ✅ | ❌ (V1 Araç raporu hariç)| ✅ (`ReportsScreen`) | R (Sadece Okuma) | `permission:reports.view` | ❌ (YOK) | Excel/CSV exportları mobilde yok. | Düşük |
| **Aktivite Logları** | ✅ | ✅ | ✅ (`ActivityScreen`) | R (Sadece Okuma) | `permission:logs.view` | ❌ (YOK) | Kapsamlı filtreleme yok. | Düşük |
| **Pilot/Sohbet (Chat)** | ✅ | ❌ (V1'de yok, Custom var) | ✅ (`PilotChatScreen`) | R / C | N/A (Manuel Controller) | ❌ (YOK) | Multi-tenant isolation eksik. | 🚨 Kritik |
| **Şirket Ayarları** | ✅ | ❌ | ❌ | Yok | `company_admin` rolü | N/A | Mobilde şirket profili düzenleme yok. | Düşük |
| **Kullanıcılar & Yetkiler**| ✅| ❌ | ❌ | Yok | `permission:company_users.view`| N/A | Mobilden kullanıcı yetkilendirme yapılamıyor. | Orta |
| **Araç Takip (Arvento)**| ✅ | ❌ | ❌ | Yok | `permission:vehicles.view` | N/A | Araç harita canlı izleme mobilde yok. | Yüksek |
| **Bildirimler (Push)** | ❌ (Web'de sadece UI)| ❌ | ❌ | Yok | N/A | N/A | Expo/FCM entegrasyonu tamamen eksik. | 🚨 Kritik |

---

### Mobilde Hiç Olmayan Web Modülleri
1. Yakıt İstasyonları ve Ödemeler
2. Müşteri Sözleşmeleri
3. Şirket Ayarları & Yetki Yönetimi
4. Müşteri Servis Rotaları ve Duraklar
5. Canlı Araç Takip Haritası (Arvento)
6. Excel / PDF Export Altyapısı
7. Yaklaşan Muayene / Sigorta Bildirimleri (Push Notification)

### Mobilde Olup Web'le Uyumsuz Olan (Sorunlu) Modüller
1. **Araçlar (Vehicles):** Web panelde gelişmiş permission'lar varken, mobilde V0 (Legacy) API üzerinden "Sil" ve "Düzenle" butonları herkese açıktır.
2. **Chat Sistemi:** Web'de tenant yapısı oturmadığı için mobilde atılan mesajların şirket izolasyonu kırıktır.
3. **Yakıtlar:** Web'de istasyon bakiyesi düşme mantığı varken mobilde hızlı ekleme yapıldığında finansal bakiye senkronizasyonu tam yansımamaktadır.
