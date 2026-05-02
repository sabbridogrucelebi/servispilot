## Description
<!-- Ne değişti? -->

## Web/Mobile Parity Checklist (REQUIRED)
- [ ] Bu değişiklik sadece web özel mi? (kabul edilen istisna mı?)
- [ ] Web tarafındaki endpoint/route eklendi mi?
- [ ] Mobil tarafındaki ekran/api çağrısı güncellendi mi?
- [ ] Permission slug değiştiyse, mobilde `hasPermission()` kontrolü güncellendi mi?
- [ ] Validation kuralları her iki tarafta tutarlı mı?
- [ ] API response shape değiştiyse mobil parsing güncellendi mi?
- [ ] Migration eklendiyse mobil form alanları senkron mu?

## Test Edildi
- [ ] Web'de manuel test
- [ ] Mobilde manuel test
- [ ] Smoke test geçti (`php test_batch*.php`)
