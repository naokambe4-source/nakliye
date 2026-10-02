# Nakliye Pro — Lisanslı WordPress Nakliyat Teması

Evden eve nakliyat, şehirler arası taşımacılık ve lojistik firmaları için **Elementor ile tamamen düzenlenebilir**, **lisans korumalı**, **özel yönetim panelli** ve **kurulum sihirbazlı** premium WordPress teması.

```
nakliye/
├── nakliye-pro/          → WordPress teması (wp-content/themes/ içine)
├── nakliye-pro-child/    → Alt tema (özelleştirmeler için)
├── license-server/       → Lisans sunucusu (API + yönetim paneli, PHP + SQLite)
└── tools/build-theme.php → İmzalı dağıtım paketi (.zip) oluşturucu
```

---

## Özellikler

### Elementor ile düzenlenebilir
- **12 özel Elementor bileşeni** ("Nakliye Pro" kategorisi): Manşet (Hero + hızlı teklif formu), Hizmetler, Teklif Formu, Fiyat Hesaplayıcı, Taşıma Takibi, Süreç Adımları, Sayaçlar, Müşteri Yorumları (kaydırıcı), Araç Filosu, Paket Fiyatları, SSS (FAQPage şemalı), CTA Şeridi.
- Her bileşende içerik + stil sekmeleri (renk, tipografi, iç boşluk, köşe, sütun sayısı — responsive).
- Demo içerikteki **7 sayfa doğrudan Elementor verisiyle** oluşturulur; her bölüm sürükle-bırak düzenlenir.
- Elementor Pro Tema Oluşturucu desteği (header/footer/single/archive konumları).
- Sayfa şablonları: **Tam Genişlik** ve **Boş Tuval** (açılış sayfaları için).

### Nakliyat sistemleri
- **Teklif formu**: AJAX, bal küpü + IP hız sınırı, yöneticiye ve müşteriye e-posta, her talebe benzersiz takip no (`NK-2610-AB5V5`).
- **Teklif yönetimi**: durum akışı (Yeni → Ekspertiz → Teklif → Onay → Paketleniyor → Yolda → Teslim / İptal), müşteriye görünen notlar, durum geçmişi, liste sütunları, menüde yeni talep sayacı.
- **Online taşıma takibi**: takip no + telefonun son 4 hanesi ile (gizlilik), ilerleme çubuğu ve zaman çizelgesi.
- **Fiyat hesaplayıcı**: ev tipi, şehir içi/arası km, kat/asansör, dış cephe asansörü, paketleme %, sigorta %; canlı döküm. Sonuç tek tıkla teklif formuna aktarılır.
- İçerik türleri: Hizmetler (+kategori), Araç Filosu, Müşteri Yorumları, Teklif Talepleri.
- Yüzen WhatsApp / arama / yukarı çık butonları, MovingCompany yapısal verisi, bakım modu, markalı giriş ekranı.

### Özel yönetim paneli (Nakliye Pro menüsü)
- **Pano**: talep istatistikleri, son talepler, durum dağılımı, lisans/bütünlük/Elementor durumu, hızlı bağlantılar.
- **Tema Ayarları** (8 sekme): Genel, Renkler & Yazı (Google Fonts), İletişim, Üst Alan, Alt Alan, Sosyal Medya, Fiyat Hesaplama, Gelişmiş (head kodu, özel CSS). Ayarları JSON olarak dışa/içe aktarma ve sıfırlama.
- **Lisans**, **Kurulum Sihirbazı**, **Sistem Durumu** (PHP, bellek, OpenSSL, cron, Elementor vb.).

### Kurulum sihirbazı
Tema etkinleşince otomatik açılır: **Hoş geldiniz → Lisans → Eklentiler (Elementor, WP Mail SMTP, Yoast tek tıkla kurulum) → Firma bilgileri (logo, renk, telefon) → Demo içerik → Hazır**.

---

## Lisans ve kırılma koruması

| Katman | Ne yapar |
|---|---|
| **RSA-2048 imzalı jeton** | Lisans sunucusu, alan adına bağlı bir jetonu **özel anahtarla** imzalar. Tema yalnızca **açık anahtarı** taşır. Veritabanındaki lisans kaydını elle "aktif" yapmak işe yaramaz — imza tutmaz. |
| **Alan adı bağlama + site limiti** | Jeton tek bir alan adına geçerlidir. Sunucu, lisans başına site sayısını sınırlar. |
| **Tekrar oynatma koruması** | Her istekte tema rastgele nonce gönderir; sunucu bunu imzalı yanıta ekler. Eski "aktif" yanıtlar yeniden kullanılamaz. |
| **Günlük uzak doğrulama** | WP-Cron ile her gün kontrol. Sunucuda iptal edilen lisans ertesi kontrolde kapanır. Sunucuya ulaşılamazsa 14 gün tolerans. |
| **İmzalı dosya bütünlüğü** | Derleme sırasında `functions.php` ve `inc/**/*.php` dosyalarının SHA-256 özetleri imzalanır. Bir dosya değiştirilir, silinir veya çekirdek klasörlere yabancı PHP eklenirse tema kilitli moda geçer. |
| **Dağıtık kontroller** | Lisans kontrolü tek bir yardımcı fonksiyonda değil; bileşenler, form AJAX'ı, demo yükleyici ve ayar kaydı doğrudan lisans sınıfını sorgular. |
| **Kilitli mod** | Premium bileşenler ziyaretçiye hiçbir şey göstermez (editörde kilit uyarısı), teklif formu kapanır, demo içerik ve fiyat ayarları kilitlenir, sitede "lisanssız" şeridi çıkar. |
| **Geliştirme ortamı** | `localhost`, `*.local`, `*.test`, yerel IP'lerde lisans gerekmez — müşteriniz siteyi yerelde rahatça kurar. |

> ⚠️ **Dürüst not:** Açık kaynak koduyla dağıtılan hiçbir PHP teması %100 kırılamaz değildir; kodu okuyabilen kararlı biri tüm kontrolleri tek tek yamalayabilir. Bu sistem sıradan "nulled" paylaşımları ve basit yamaları engeller. Daha güçlü koruma için paketi "Kodu sıkıştır" seçeneğiyle üretin ve **`inc/core/` klasörünü ionCube veya SourceGuardian ile şifreleyin** (bütünlük manifestini şifrelemeden *sonra* yeniden oluşturun).

---

## Kurulum (satıcı tarafı — bir kez, terminal gerekmez)

> Ayrıntılı anlatım ve sorun giderme: **[LISANS-REHBERI.md](LISANS-REHBERI.md)**

1. `license-server/` klasörünün içeriğini ve `tema-kaynak/nakliye-pro/` klasörünü lisans adresinize yükleyin
   (hazır paket: `lisans-sunucusu-yukle.zip`), ör. `https://lisans.guvenyolnakliyat.com/buryaa/`.
2. Tarayıcıda o adresi açın → **kurulum sihirbazı** gereksinimleri kontrol eder, panel parolasını alır, RSA anahtarını üretir.
3. Panelde **"Tema zip'ini oluştur ve indir"** → müşteriye verilecek, imzalı ve sunucu adresiniz yazılı `nakliye-pro-1.0.0.zip`.
4. `keys/.ht-private.pem` ve `data/.ht-licenses.sqlite` dosyalarını yedekleyin.
5. Her satışta panelden lisans oluşturun; müşteriye **zip + anahtar** gönderin.

Komut satırı tercih edenler için:
```bash
php license-server/tools/generate-keys.php
php license-server/tools/create-license.php "Müşteri" musteri@mail.com 1 365
php tools/build-theme.php --server=https://lisans.guvenyolnakliyat.com/buryaa [--obfuscate]
```

---

## Kurulum (müşteri tarafı)

1. **Görünüm → Temalar → Yeni Ekle → Tema Yükle** → `nakliye-pro-1.0.0.zip`.
2. Etkinleştirince **Kurulum Sihirbazı** açılır: lisans anahtarı → Elementor kurulumu → firma bilgileri → demo içerik.
3. Sayfaları **Elementor ile Düzenle** butonuyla değiştirin; renk/telefon/logo gibi genel ayarlar **Nakliye Pro → Tema Ayarları**'nda.
4. Özelleştirmeler için `nakliye-pro-child` alt temasını kullanın (ana tema dosyalarını düzenlemek bütünlük korumasını tetikler).
5. Siteyi başka alan adına taşırken: **Lisans → Bu siteden kaldır**, sonra yeni alan adında yeniden etkinleştirin.

### Kısa kodlar (Elementor kullanmayan sayfalar için)
- `[nakliye_teklif_formu]` — `[nakliye_teklif_formu kompakt="evet"]`
- `[nakliye_takip]`

---

## Gereksinimler
- WordPress 6.0+, PHP 7.4+ (8.1+ önerilir), OpenSSL eklentisi
- Elementor (ücretsiz) — Elementor Pro isteğe bağlı
- Lisans sunucusu: PHP 7.4+, pdo_sqlite, openssl

## Test edildi
WordPress 7.1.2 + Elementor 4.4.0 + PHP 8.3 üzerinde: tüm sayfalar ve yönetim ekranları hatasız; teklif/takip AJAX akışı; lisans etkinleştirme, sahte DB kaydı, jeton düzenleme, dosya yamalama, yabancı dosya ekleme, manifest silme, site limiti, sunucuda iptal ve kaldırma senaryoları.
