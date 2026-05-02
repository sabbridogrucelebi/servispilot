# ServisPilot Web ve Mobil Senkronizasyon Kuralları

**Altın Kural:** ServisPilot çift platformlu bir üründür. Web panel (Laravel + Blade) ve mobile-app (React Native/Expo) **fonksiyonel olarak eşit (parite)** olmak ZORUNDADIR. Web'de yapılan her değişiklik mobilde de karşılığını almalıdır; aksi halde değişiklik **EKSİK** kabul edilir ve PR/commit reddedilir.

## Tetikleyiciler
Aşağıdaki değişikliklerden herhangi biri yapıldığında mobil uygulamanın da güncellenmesi zorunludur:
- Yeni route/endpoint ekleme veya değiştirme (`routes/api.php`, `routes/web.php`)
- Controller'da yeni method/validation/iş mantığı
- Migration (yeni tablo, yeni kolon, kolon adı/tipi değişikliği)
- Model'e yeni `$fillable`, `$casts`, scope, relation ekleme
- Permission slug ekleme/değiştirme/silme (`permissions` seeder, `PermissionSlug` enum)
- Form Request validation kuralı ekleme/değiştirme
- API response shape değişikliği (alan ekleme/silme/yeniden adlandırma)
- Yeni tenant-scoped model
- Yeni dosya/asset upload akışı

## Senkronizasyon Eşleşme Tablosu
| Web (Laravel) | Mobil (React Native) |
| --- | --- |
| `app/Http/Controllers/Api/V1/*ApiController.php` | `mobile-app/src/screens/*Screen.js` + `mobile-app/src/api/*.js` |
| `app/Models/*.php` | `mobile-app/src/types/*.js` (varsa) ve ekran formları |
| `app/Http/Middleware/Permission.php` slugları | `mobile-app/src/context/AuthContext.js` `hasPermission()` çağrıları |
| `routes/api.php` | `mobile-app/src/api/axios.js` endpoint URL'leri |
| Migration kolonları | Mobil form alanları + state |

## İstisnalar
Aşağıdaki özellikler web'e özeldir ve mobil senkronu gerektirmez:
- Admin-only super admin paneli özellikleri (örn. `/admin-panel/companies`)
- Web özel reporting/export ekranları
- E-posta template'leri

## Checklist (PR Öncesi)
- [ ] Endpoint web'de eklendi mi?
- [ ] Mobilde aynı endpoint çağrılıyor mu?
- [ ] Permission slug eklendiyse mobilde `hasPermission` ile gizlendi/açıldı mı?
- [ ] Validation kuralları mobilde de uygulandı mı?
- [ ] Tenant scope korunuyor mu?

## Yaptırım
Eğer tek tarafı değiştirip diğerini bırakırsan, commit'i yarım bırakmış olursun. Çift platform değişikliği tamamlanmadan TodoList kapanmaz.

Referans: [WEB_MOBIL_PARITY_MATRISI.md](WEB_MOBIL_PARITY_MATRISI.md)
