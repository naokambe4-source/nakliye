# Nakliye Pro — Lisans Sistemi Rehberi

Bu rehber lisans sisteminin **ne olduğunu**, **nasıl kurulduğunu** ve **günlük olarak nasıl kullanıldığını** sade bir dille anlatır.

---

## 1. Büyük resim: 3 parça var

```
 ┌──────────────────────┐        ┌──────────────────────────┐
 │  SİZ (temayı satan)  │        │  MÜŞTERİ (temayı alan)   │
 │                      │        │                          │
 │  license-server/     │◄──────►│  WordPress + nakliye-pro │
 │  lisans.siteniz.com  │ HTTPS  │  musterifirma.com        │
 │  • lisans üretir     │        │  • anahtarı girer        │
 │  • iptal / uzatır    │        │  • tema sunucuya sorar   │
 │  • ÖZEL anahtar      │        │  • AÇIK anahtar          │
 └──────────────────────┘        └──────────────────────────┘
```

| Parça | Nerede durur | Görevi |
|---|---|---|
| **Tema** (`nakliye-pro`) | Müşterinin WordPress sitesinde | Lisans anahtarını sorar, sunucudan gelen cevabı doğrular |
| **Lisans sunucusu** (`license-server`) | **Sizin** sunucunuzda (ör. `lisans.siteniz.com`) | Lisans üretir, kaydını tutar, cevapları imzalar |
| **Anahtar çifti** | Özel anahtar sizde, açık anahtar temada | Sahte "lisans geçerli" cevabı üretilmesini imkânsız kılar |

### İmza mantığı (neden kırılması zor?)
- **Özel anahtar** (`private.pem`) yalnızca sizin sunucunuzda durur. Sadece o, "bu lisans geçerli" cevabını **imzalayabilir**.
- **Açık anahtar** temanın içindedir. Sadece imzanın **doğru olup olmadığını kontrol edebilir**, imza üretemez.
- Yani biri veritabanına "lisans aktif" yazsa, sahte bir lisans sunucusu kursa ya da cevabı kopyalasa bile imza tutmaz → tema kilitli kalır.

> Benzetme: Özel anahtar noterdeki mühür, açık anahtar mühürün fotoğrafı. Fotoğrafla mühürü kontrol edebilirsiniz ama yeni mühür basamazsınız.

---

## 2. Bir kez yapılacak kurulum (sizin tarafınız)

### Adım 1 — Anahtar çiftini üretin
Projenin ana klasöründe:
```bash
php license-server/tools/generate-keys.php
```
Çıktı:
- `license-server/keys/private.pem` → **ÇOK GİZLİ.** Yedekleyin, kimseyle paylaşmayın. Kaybederseniz verdiğiniz tüm lisanslar geçersiz olur ve temayı yeniden derlemeniz gerekir.
- `nakliye-pro/inc/core/public-key.php` → açık anahtar temaya otomatik yazılır.

### Adım 2 — Lisans sunucusunu yayınlayın
1. Bir alt alan adı açın: ör. `lisans.siteniz.com` (SSL'li olsun).
2. `license-server/` klasörünün **içeriğini** bu alan adının köküne yükleyin (`keys/private.pem` dahil).
3. Sunucuda PHP 7.4+ ve `pdo_sqlite`, `openssl` eklentileri açık olmalı (çoğu hostingde açıktır).
4. Panel parolası belirleyin. Bilgisayarınızda şunu çalıştırın:
   ```bash
   php -r "echo password_hash('BuradaGüçlüBirParola', PASSWORD_DEFAULT);"
   ```
   Çıkan `$2y$10$...` metniyle sunucuda `config.local.php` dosyası oluşturun:
   ```php
   <?php return array( 'admin_password_hash' => '$2y$10$...' );
   ```
5. Kontrol: `https://lisans.siteniz.com/admin.php` açılıp parola sormalı.
   `https://lisans.siteniz.com/data/` ve `/keys/` adresleri **403 / erişim yok** vermeli.

> Nginx kullanıyorsanız `.htaccess` çalışmaz; `data/`, `keys/`, `tools/` klasörlerini ve `config*.php`, `lib.php` dosyalarını sunucu ayarından erişime kapatın.

### Adım 3 — Temaya sunucu adresini yazın
`nakliye-pro/inc/core/config.php`:
```php
define( 'NAKLIYE_LICENSE_SERVER', 'https://lisans.siteniz.com' );
define( 'NAKLIYE_PURCHASE_URL',  'https://siteniz.com/nakliye-pro' ); // "Lisans satın al" butonu
```

### Adım 4 — Satış paketini derleyin
```bash
php tools/build-theme.php
```
Çıktı: `dist/nakliye-pro-1.0.0.zip` → **müşteriye verilecek dosya budur.**

Bu komut tema dosyalarının parmak izini çıkarıp özel anahtarla imzalar (`manifest.json`). Müşteri bir dosyayı değiştirirse tema bunu fark eder.

> ⚠️ Derlenmemiş `nakliye-pro` klasörünü müşteriye **vermeyin**: imzalı manifest olmadığı için canlı alan adında kilitli açılır.
> Temada her değişiklik yaptığınızda 4. adımı tekrarlayın.

---

## 3. Lisans satmak (her satışta)

**Yöntem A — Panelden:** `admin.php` → *Yeni lisans oluştur*
- **Müşteri / E-posta:** kayıt için
- **Site limiti:** kaç alan adında kullanılabilir (genelde 1)
- **Süre:** Süresiz, 1 yıl veya 30 gün (deneme)

**Yöntem B — Komut satırından:**
```bash
php license-server/tools/create-license.php "Yıldız Nakliyat" info@yildiz.com 1 365
# NKP-7H4K-Q2MX-9PLR-VB3T
```

Müşteriye iki şey gönderin: **zip dosyası** + **lisans anahtarı** (`NKP-XXXX-XXXX-XXXX-XXXX`).

---

## 4. Müşteri ne yapar?

1. WordPress → **Görünüm → Temalar → Yeni Ekle → Tema Yükle** → zip'i yükler, etkinleştirir.
2. **Kurulum Sihirbazı** açılır → *Lisans* adımında anahtarı yapıştırır → **Etkinleştir**.
3. Tema sunucunuza bağlanır; sunucu "geçerli" derse tüm özellikler açılır.
4. Sonra lisansı her zaman **Nakliye Pro → Lisans** sayfasından görebilir: durum, alan adı, bitiş tarihi, son doğrulama, kaç sitede kullanıldığı.

### Yerel bilgisayarda kurulum
`localhost`, `*.local`, `*.test` ve `192.168.x.x` gibi yerel adreslerde **lisans gerekmez**, her şey açıktır. Müşteri siteyi önce bilgisayarında hazırlayabilir; canlıya taşıyınca anahtarı girer.

---

## 5. Lisansın yaşam döngüsü

| Durum | Ne olur | Nasıl çözülür |
|---|---|---|
| **Aktif** | Tüm özellikler açık | — |
| **Etkinleştirilmemiş** | Bileşenler ziyaretçiye görünmez, teklif formu kapalı, sitede kırmızı "lisanssız" şeridi | Anahtarı girin |
| **Süresi dolmuş** | Aynı kilitli mod | Panelde **+1 yıl** → müşteri *Şimdi doğrula*'ya basar |
| **İptal edilmiş** | Ertesi günlük kontrolde kilitlenir | Panelde **Etkinleştir** |
| **Başka alan adı** | Anahtar o alan adına ait değil | Eski siteden kaldırıp yenisinde etkinleştirin |
| **Site limiti dolu** | "En fazla 1 sitede kullanılabilir" hatası | Eski siteden kaldırın veya panelde aktivasyonu silin |
| **Sunucuya ulaşılamıyor** | **14 gün** boyunca çalışmaya devam eder | Sunucunuzu kontrol edin |
| **Dosyalar değiştirilmiş** | Lisans geçerli olsa bile kilitlenir | Temayı orijinal zip'ten yeniden yükleyin; özelleştirme için alt tema kullanın |

### Günlük doğrulama
Tema her gün (WP-Cron ile) sunucunuza "bu lisans hâlâ geçerli mi?" diye sorar. Her soruda rastgele bir kod gönderir; sunucu bu kodu imzalı cevaba ekler. Böylece eski bir "geçerli" cevabı kaydedip tekrar kullanmak işe yaramaz.

### Müşteri alan adı değiştirirse
1. Eski sitede **Lisans → Bu siteden kaldır**
2. Yeni sitede anahtarı tekrar girer.

Eski site kapandıysa ve kaldıramıyorsa: panelde o lisansın yanındaki alan adının **×** butonuna basın.

---

## 6. Kilitli modda tam olarak ne kapanır?

- 12 Elementor bileşeni ziyaretçiye **hiçbir şey göstermez** (yöneticiye "lisans gerekli" kutusu görünür)
- Teklif formu talep kabul etmez
- `[nakliye_teklif_formu]` ve `[nakliye_takip]` kısa kodları çalışmaz
- Demo içerik yükleme kapanır
- Fiyat hesaplama ayarları değiştirilemez
- Sitenin altında kırmızı **"Bu sitede lisanssız Nakliye Pro teması kullanılmaktadır"** şeridi çıkar

Açık kalanlar: temel tema (üst/alt alan, blog, sayfalar), yönetim paneli ve lisans sayfası — müşteri anahtarını girebilsin diye.

---

## 7. Sık sorulan sorular

**Özel anahtarı kaybedersem?**
Yeni anahtar üretmeniz gerekir (`generate-keys.php --force`). Bu durumda temayı yeniden derleyip tüm müşterilere yeni zip göndermelisiniz. → **`private.pem` dosyasını mutlaka yedekleyin.**

**Lisans sunucum kapanırsa müşteri siteleri çöker mi?**
Hayır. Son başarılı doğrulamadan sonra **15 gün** (1 gün + 14 gün tolerans) çalışmaya devam eder.

**Müşteri temayı başkasına verirse?**
Zip başka alan adında anahtarsız kilitli açılır. Aynı anahtarla başka sitede etkinleştirmeye çalışırsa site limitine takılır. Panelin *Son API olayları* bölümünde hangi alan adlarının denediğini görürsünüz.

**Tamamen kırılamaz mı?**
Hayır — kodu açık dağıtılan hiçbir PHP teması %100 kırılamaz değildir. Kodu okuyabilen kararlı biri kontrolleri tek tek yamalayabilir. Bu sistem "nulled" paylaşımları ve basit yamaları engeller (testte: sahte veritabanı kaydı, jeton düzenleme, dosya yamalama, yabancı dosya ekleme ve manifest silme engellendi). Daha güçlü koruma için:
```bash
php tools/build-theme.php --obfuscate
```
ile yorumları silin; profesyonel seviye için `inc/core/` klasörünü **ionCube** veya **SourceGuardian** ile şifreleyin.

**Lisans sunucusu verileri nerede?**
`license-server/data/licenses.sqlite` dosyasında. Bu dosyayı düzenli yedekleyin.
