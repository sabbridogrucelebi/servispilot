# MOBİL TEKNİK DENETİM (ADIM 6.A)

Bu belge, ServisPilot mobil uygulamasının teknik altyapısının (React Native / Expo) güncel durumunu, eksikliklerini ve endüstri standartlarına göre yapılması gerekenleri listeler.

## 1. Token ve Oturum Yönetimi
- **Mevcut Durum:** `AsyncStorage` kullanılıyor. Token güvenli bir alanda (Keychain/Keystore) tutulmuyor. `axios.js` içinde 401 hatası yakalandığında token silinip kullanıcı login'e atılıyor.
- **Eksik:** Expo'nun `expo-secure-store` kütüphanesi kullanılmalı (Şifrelenmiş depolama için). Token yenileme (Refresh Token) mekanizması backend ve mobil tarafta yok, token expire olunca kullanıcı aniden session drop yaşıyor.

## 2. Push Notification Altyapısı
- **Mevcut Durum:** Tamamen **EKSİK**. `package.json` dosyasında `expo-notifications` veya Firebase/APNs entegrasyonu yok.
- **Ne Yapılmalı:** `expo-notifications` kurulmalı. `device_token` almak ve backend'e iletmek için `App.js` veya `AuthContext` içerisinde bir servis yazılmalı. Yaklaşan bakım, sigorta ve vize gibi uyarılar için kritik.

## 3. Resim Yükleme ve PDF Görüntüleme
- **Mevcut Durum:** `expo-image-picker` var (çalışıyor), multipart upload `VehicleGalleryScreen` vb. ekranlarda manuel fetch ile yapılıyor. PDF için belge linkine tıklanınca sadece URL var, uygulama içinde natif bir Viewer yok.
- **Ne Yapılmalı:** Belge ve PDF göstermek için `expo-file-system` üzerinden indirme ve `expo-sharing` ile paylaşma veya `react-native-pdf` ile in-app render eklenmeli.

## 4. UI/UX Teknikleri (Pull-to-refresh, Infinite Scroll)
- **Mevcut Durum:** Çoğu Listeleme ekranında (örneğin Araçlar) basit RefreshControl var, ancak sayfalama (pagination / infinite scroll) tam anlamıyla entegre edilmemiş. API'den gelen `per_page` datası UI'da scroll-to-bottom tetiklenince çalışmıyor, sadece ilk 20 veya 50 kayıt geliyor.
- **Ne Yapılmalı:** `FlatList` üzerinde `onEndReached` event'i ile backend'in sunduğu pagination tam entegre edilmeli.

## 5. Uygulama Yapılandırması (app.json) ve Derleme
- **Mevcut Durum:** `app.json` dosyası tamamen çıplak (barebone). EAS (Expo Application Services) config `projectId` yok, Android paketi (`package`) veya iOS Bundle Identifier (`bundleIdentifier`) belirtilmemiş.
- **Ne Yapılmalı:** Store yüklemeleri ve bağımsız derleme (Standalone Build) için `app.json` doldurulmalı, kamera/dosya okuma yetkileri (permissions) iOS/Android için açıkça belirtilmelidir.

## 6. Eksik Modern Altyapılar
- **i18n (Çoklu Dil):** Yok. Tüm metinler hard-code (sabit string).
- **Dark Mode:** Yok. Sadece `userInterfaceStyle: "light"` olarak kilitlenmiş.
- **Error Boundary:** Merkezi hata yakalama ekranı yok, çökmeler direkt "kırmızı ekran" veya sessiz çökme (crash) ile sonuçlanır.
- **Deep Linking:** Yok. Push notification tıklandığında ilgili araca gitme gibi senaryolar çalışmaz.
