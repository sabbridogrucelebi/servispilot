# MOBİL TASARIM SİSTEMİ (DESIGN SYSTEM) & YOL HARİTASI (ADIM 6.D & 6.E)

"Ultra-Pro" SaaS hissini mobil cihaza taşımak için kullanılacak kesin tasarım kuralları ve stil sözlüğüdür. (⚠️ Not: `VehiclesScreen` tasarımı mevcut haliyle korunacak, diğer tüm ekranlar bu sisteme göre yeniden yazılacaktır.)

## 1. Renk Paleti (Light Mode)
*   **Primary (Marka):**
    *   `primary-500`: `#3B82F6` (Ana aksiyonlar)
    *   `primary-900`: `#1E3A8A` (Header arka planı, Gradient start)
*   **Secondary:** `#6366F1` (İkincil butonlar, destekleyici ikonlar)
*   **Success (Başarı/Kâr):** `#10B981` (Background: `#D1FAE5`)
*   **Warning (Bekleyen/Uyarı):** `#F59E0B` (Background: `#FEF3C7`)
*   **Danger (Zarar/Sil/Hata):** `#EF4444` (Background: `#FEE2E2`)
*   **Neutral (Zemin ve Metin):**
    *   `Background`: `#F8FAFC` (Tüm app zemini)
    *   `Card`: `#FFFFFF`
    *   `Text-Main`: `#0F172A` (Başlıklar)
    *   `Text-Muted`: `#64748B` (Alt başlıklar, caption)
    *   `Border`: `#E2E8F0`

## 2. Tipografi Ölçeği (Sistem Fontları: Inter veya Roboto)
*   `Display / Büyük KPI`: **32px / 36px**, Bold, LineHeight: 40px
*   `H1 / Sayfa Başlığı`: **24px**, Bold, LineHeight: 32px
*   `H2 / Kart Başlığı`: **18px**, SemiBold, LineHeight: 24px
*   `Body / Ana Metin`: **14px**, Regular, LineHeight: 20px
*   `Caption / Alt Metin`: **12px**, Medium, LineHeight: 16px, Renk: `Text-Muted`

## 3. Spacing (Boşluk) & Radius Token'ları
*   **Padding/Margin:** `xs(4px)`, `sm(8px)`, `md(12px)`, `base(16px)`, `lg(24px)`, `xl(32px)`
*   **Border Radius:** Kartlar için `md(16px)` veya `lg(24px)` (Glassmorphism hissi için yumuşak köşeler). Butonlar için tam yuvarlak `full(9999px)` veya `md(12px)`.

## 4. Shadow (Gölge) Yükseklikleri
*   `elevation-sm`: Y-offset 2, Blur 4, Opacity 0.05 (Liste kartları)
*   `elevation-md`: Y-offset 4, Blur 12, Opacity 0.08 (Header, Floating elementler)
*   `elevation-lg`: Y-offset 10, Blur 20, Opacity 0.12 (Bottom Sheet, FAB Buton)

## 5. KPI Kart Anatomisi & 3D İkon Stratejisi
*   **Kart Anatomisi:** Kartın üstünde 3D/Glass bir ikon. Sağ tarafında kalın `Display` fontu ile metrik (Örn: `1,250 ₺`). Altında % trend ok'u (Yeşil veya Kırmızı). Arkasında yumuşak bir radial gradient parlaması (`rgba(59,130,246,0.1)`).
*   **3D İkon Stratejisi Karşılaştırması:**
    *   *(a) Lottie Animasyonları:* JSON tabanlıdır, animasyonludur fakat renk değiştirmek veya gerçekçi ışık/cam efekti (glassmorphism) yakalamak zordur. Performans harcar.
    *   *(b) 3D PNG Assetleri (ÖNERİLEN):* Blender veya Spline'dan şeffaf arka planlı yüksek çözünürlüklü (256x256) render alınmış PNG'ler. **Neden?** Ultra-pro, "premium SaaS" hissiyatını statik PNG'lerin sağladığı mükemmel ışık ve cam dokusu verir. Uygulamanın boyutunu çok artırmaz, performansı (özellikle listede) Lottie'den 10 kat daha hızlıdır.

## 6. Animasyon ve Micro-interaction (react-native-reanimated)
*   Sayfa geçişleri ve Bottom Sheet açılışları `easeInOutCubic` (yaklaşık 250ms).
*   KPI rakamlarında `react-native-animated-numbers` kullanılarak ekrana ilk girişte sıfırdan yukarı sayma animasyonu.
*   Butonlarda basılma anında (onPressIn) `scale(0.95)` ile hafif bir içe çökme efekti (Spring).

---

## Redesign Yol Haritası (Web ↔ Mobil Görsel Parite)

*   **Dashboard:** Web'deki grafiklerin (Chart.js vb.) React Native karşılığı (`react-native-gifted-charts`) kullanılacak. En tepede "Hoşgeldin, X" ve avatar. Altında 3'lü kaydırılabilir KPI kartları (Gelir, Gider, Net). Altında yaklaşan evraklar (kırmızı highlight ile).
*   **Finance & Reports:** Sadece liste değil, üstte sabit (Sticky Header) büyük bir Özet KPI (Aylık toplam ciro). Altında yatay filtre çipleri (`chip`).
*   **Trips (Seferler):** Liste kartlarında Google Maps minik önizlemesi (veya başlangıç-bitiş nokta ikonları). Sola kaydırınca "Tamamlandı" işaretleme swipe action'u.
*   **Personnel & Customers:** Web'deki veri tablosu görünümü yerine, her biri için detaylı "Profil" kartı görünümü. Baş harften oluşan dairesel renkli avatar, altında görev/pozisyon badge'leri.
