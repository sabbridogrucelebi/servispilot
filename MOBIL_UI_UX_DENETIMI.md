# MOBİL UI/UX TASARIM DENETİMİ (ADIM 6.C)

Bu belge, ServisPilot mobil uygulamasının UI/UX standartlarını, görsel hiyerarşiyi ve kullanıcı deneyimi eksikliklerini bir ürün tasarımcısı (Product Designer) gözüyle denetler.

## Genel Ekran Analizleri

### `HomeScreen.js` (Dashboard)
- ❌ **KRİTİK:** Sayfa başlıkları ve KPI rakamları (`styles.bigNumber`) yeterince vurgulu değil. 32px veya 40px bold olması gereken büyük rakamlar, sıradan bir metin bloğu gibi hissettiriyor.
- ⚠️ **ORTA:** Yatay kaydırılabilir kartlardaki (Horizontal Scroll) padding değerleri `paddingLeft: 20, paddingRight: 8` kullanılmış. Dengesiz boşluklar kaydırma hissiyatını (scroll snap) bozuyor. 16px tutarlı boşluk kullanılmalı.
- ✅ **İYİ:** LinearGradient kullanımı başarılı, marka kimliğini yansıtmak için koyu mavi tonlar tercih edilmiş.
- ❌ **KRİTİK:** "Son İşlemler" listesi hard-coded (sabit) veriler içeriyor (`"Yeni Araç Eklendi"` vb.). Dinamik veri bağlandığında skeleton loading (yükleniyor iskeleti) düşünülmemiş.

### `VehiclesScreen.js`
- ❌ **KRİTİK:** Empty state (Boş liste) ekranı çok zayıf. Sadece düz bir metin (`"Henüz filonuza araç eklenmemiş."`) kullanılmış. Ortada 120x120px bir illustrasyon (SVG veya 3D) olmalı.
- ⚠️ **ORTA:** Buton dokunma alanları (Touch targets) sınırda (yaklaşık 36px-40px). Apple HIG ve Google Material Design gereği minimum 44x44pt olmalı. Özellikle Bottom Sheet içerisindeki "Aracı Düzenle" satırları çok bitişik.
- ⚠️ **ORTA:** "İlk Aracı Ekle" butonu (satır 340) ekranın ortasında havada duruyor, ana FAB (Floating Action Button) mantığı yerine inline kullanılmış, erişilebilirliği düşük.

### `TripsScreen.js`
- ❌ **KRİTİK:** Durum belirten metinler (Beklemede, Tamamlandı) sadece renkli background (`#FEF3C7` vb.) içine alınmış ama metin kontrastı (WCAG AA) testini geçemiyor. Sarı arka plana beyaz/açık gri metin okunamaz.
- ⚠️ **ORTA:** Liste elemanları (Cards) arasındaki boşluklar `marginVertical: 4` kadar dar. En az `8px` veya `12px` olmalı ki kartlar birbirine girmesin.

### `VehicleDetailScreen.js` / Alt Tablar (Fuels, Documents vb.)
- ❌ **KRİTİK:** Belge listelerinde veya Yakıt kartlarında tipografi hiyerarşisi çok düz (Flat). Tarih, Miktar ve Tutar aynı font size (14px) ile verilmiş. Tutar bold ve vurgulu (brand color), tarih ise caption (12px, text-gray-500) olmalı.
- ⚠️ **ORTA:** Çekmeceler veya Tab geçişlerinde animasyon (micro-interaction) eksik. Tıklama hissiyatı (ripple effect veya opacity fade) çok sert.

## Genel UI/UX Kuralları (Ne Düzeltilmeli?)

1. **Tipografik Hiyerarşi:** `12px` (Caption), `14px` (Body), `16px` (Body Lg), `20px` (H3), `24px` (H2), `32px+` (Display) standart ölçeğine geçilmeli.
2. **Boşluk (Spacing) Sistemi:** `4-8-12-16-24-32` pt (katları) kuralı her `margin` ve `padding` tanımında standart olmalı. Ad-hoc (rastgele) 5px, 15px gibi değerler kaldırılmalı.
3. **Skeleton Loaders:** Uygulama veri beklerken (API fetching) ortada dönen çirkin bir `ActivityIndicator` (Spinner) yerine pürüzsüz animasyonlu skeleton'lar kullanılmalı.
4. **Hata State'leri:** API'den 404 veya 500 geldiğinde, Alert göstermek yerine ekranda "Veri alınamadı, tekrar dene" butonu barındıran şık Error State UI'ları olmalı.
