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

## 2. Bir kez yapılacak kurulum (terminal gerekmez)

### Adım 1 — Dosyaları yükleyin
`lisans.guvenyolnakliyat.com` alt alan adının **kök klasörünü** bulun. cPanel → **Alan Adları / Subdomains** sayfasında "Belge Kökü" (Document Root) sütununda yazar; genelde `public_html/lisans` veya `/lisans.guvenyolnakliyat.com` olur.

1. Dosya Yöneticisi ile o klasöre girin. İçinde eski lisans dosyaları varsa silin (`cgi-bin` ve `.well-known` klasörlerine dokunmayın).
2. `lisans-sunucusu-yukle.zip` dosyasını yükleyip **orada çıkartın**.
3. Klasörde doğrudan şunlar görünmeli: `index.php`, `install.php`, `admin.php`, `api.php`, `data/`, `keys/`, `tema-kaynak/` …

> Zip'i çıkartınca fazladan bir alt klasör oluştuysa (ör. `lisans/lisans-sunucusu-yukle/`), içindekileri bir üst klasöre taşıyın.
> Klasörde hosting firmasının koyduğu `index.html` / `default.html` varsa silin, yoksa sihirbaz yerine o sayfa açılır.

### Adım 2 — Kurulum sihirbazını çalıştırın
Tarayıcıda adresinizi açın: `https://lisans.guvenyolnakliyat.com/`
1. Sihirbaz sunucuyu kontrol eder (PHP sürümü, OpenSSL, SQLite, yazma izni). Kırmızı satır varsa cPanel → **"PHP Sürümü Seç"** bölümünden düzeltin.
2. Bir **panel parolası** belirleyin → **Kurulumu tamamla**.
3. Sihirbaz anahtar çiftini üretir, veritabanını kurar ve kendini kilitler.

### Adım 3 — Tema zip'ini panelden indirin
Panelde **"📦 Müşteriye verilecek tema paketi"** kutusu:
- *Lisans sunucusu adresi* otomatik dolu gelir (kontrol edin: `https://lisans.guvenyolnakliyat.com`)
- **⬇ Tema zip'ini oluştur ve indir** → `nakliye-pro-1.0.0.zip`

Bu zip; açık anahtarınızı ve sunucu adresinizi içerir, dosyaları imzalıdır. **Müşteriye verilecek dosya budur.**
> Temada değişiklik yaptığınızda yeni `tema-kaynak/nakliye-pro` klasörünü yükleyip zip'i yeniden indirin.

### Adım 4 — Yedek alın
`keys/.ht-private.pem` (özel anahtar) ve `data/.ht-licenses.sqlite` (lisans kayıtları) dosyalarını bilgisayarınıza yedekleyin. **Özel anahtar kaybolursa verdiğiniz tüm lisanslar geçersiz olur.**

### Güvenlik kontrolü
Panel, özel anahtarın internetten indirilebilir olup olmadığını kendisi test eder. Kırmızı **"GÜVENLİK"** uyarısı görürseniz sunucunuz `.htaccess` uygulamıyor demektir (genelde Nginx); hosting firmanızdan `keys/` ve `data/` klasörlerini dış erişime kapatmasını isteyin.

### Sorun giderme
| Belirti | Çözüm |
|---|---|
| **500 Internal Server Error** | Klasördeki `.htaccess` dosyasını geçici olarak silip deneyin. Açılırsa hosting firmanıza bildirin; gizli dosyalar `.ht-` önekli olduğu için Apache/LiteSpeed yine korur. |
| **403 / boş sayfa** (klasör adresinde) | `index.php` yüklenmemiş; zip'i yeniden çıkartın. Ya da doğrudan `https://lisans.guvenyolnakliyat.com/install.php` açın. Klasörde `index.html` varsa silin. |
| **"RSA anahtarı üretilemedi"** | Hosting'de OpenSSL kısıtlı. Firmanızdan `openssl_pkey_new` desteği isteyin. |
| **"data/ klasörüne yazılamadı"** | Dosya Yöneticisinde `data` ve `keys` klasörlerinin iznini **755** yapın. |
| **Müşteri: "Lisans sunucusuna bağlanılamadı"** | Adresi tarayıcıda deneyin: `https://lisans.guvenyolnakliyat.com/api.php` → `{"success":false,"message":"Yalnızca POST."}` görmelisiniz. SSL sertifikasının geçerli olduğundan emin olun. |

> Terminal kullanabiliyorsanız aynı işleri komutla da yapabilirsiniz:
> `php license-server/tools/generate-keys.php` ve `php tools/build-theme.php --server=https://lisans.guvenyolnakliyat.com`

---

## 3. Lisans satmak (her satışta)

**Yöntem A — Panelden:** `https://lisans.guvenyolnakliyat.com/admin.php` → *Yeni lisans oluştur*
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

## 6b. Lisanssız kuranları görmek

Temayı kuran **her site** (anahtar girmemiş olsa bile) günde bir sunucunuza kurulum bilgisini gönderir. Panelde **"🌐 Tema kurulu siteler"** tablosunda hepsini görürsünüz:

- **Lisanslı** (yeşil) — geçerli anahtarla kullanılan siteler.
- **Lisanssız** (kırmızı) — anahtar girmeden temayı kullanan siteler. Başlıkta toplam sayı da görünür.

Her satırda alan adı, tema/WordPress/PHP sürümü, ilk ve son görülme tarihi yer alır. Böylece korsan kullanımı tespit edip o siteyle iletişime geçebilir veya yasal işlem başlatabilirsiniz.

> Geliştirme adreslerinde (`localhost`, `.local`, `.test`) ping gönderilmez.

## 6c. Otomatik Türkçe

Tema etkinleştirildiğinde site dilini otomatik **Türkçe (tr_TR)** yapar ve WordPress ile Elementor'un Türkçe dil paketlerini indirir. Yönetim menüleri, blog, yorum formu gibi alanların İngilizce görünmesinin sebebi site dilinin İngilizce olmasıdır; bu ayarla düzelir.
Elle değiştirmek için: **Ayarlar → Genel → Site Dili → Türkçe**.

---

## 7. Sık sorulan sorular

**Özel anahtarı kaybedersem?**
Yeni anahtar üretmeniz gerekir; bu durumda tüm müşterilere yeni zip göndermelisiniz. → **`keys/.ht-private.pem` dosyasını mutlaka yedekleyin.**

**Lisans sunucum kapanırsa müşteri siteleri çöker mi?**
Hayır. Son başarılı doğrulamadan sonra **15 gün** (1 gün + 14 gün tolerans) çalışmaya devam eder.

**Müşteri temayı başkasına verirse?**
Zip başka alan adında anahtarsız kilitli açılır. Aynı anahtarla başka sitede etkinleştirmeye çalışırsa site limitine takılır. Panelin *Son API olayları* bölümünde hangi alan adlarının denediğini görürsünüz.

**Kodu tam olarak şifreleyebilir misiniz? Kırılmasın istiyorum.**
Önemli bir gerçeği açıkça söyleyeyim: PHP kodunu "geri döndürülemez" biçimde şifrelemenin **tek güvenilir yolu ticari bir araçtır** — **ionCube** veya **SourceGuardian**. Bunlar kodu makine okunur hâle getirir ve müşteri sunucusunda bir "loader" (yükleyici) gerektirir.

Temaya `eval(base64_decode(...))` gibi kendi kendini çözen sahte şifreleme **koymuyorum** — çünkü bu yöntem:
- Wordfence, Sucuri gibi güvenlik eklentileri ve birçok hosting tarafından **virüs sanılıp engellenir** (müşteri sitesi kara listeye düşebilir),
- 5 dakikada geri çözülür, yani gerçek koruma sağlamaz.

Bu paketin sağladığı gerçek korumalar: **imzalı lisans** (sahte onay üretilemez), **imzalı dosya bütünlüğü** (dosya değişirse kilitlenir), **dağıtık kontroller** ve **lisanssız kullanım takibi**. Testte sahte veritabanı kaydı, jeton düzenleme, dosya yamalama, yabancı dosya ekleme ve manifest silme engellendi. Paneldeki **"Kodu sıkıştır"** kutusu da yorum/boşlukları silip okumayı zorlaştırır.

**Profesyonel seviye için ionCube adımları (önerilen):**
1. [ioncube.com/encoder](https://www.ioncube.com/encoder.php) üzerinden Encoder lisansı alın (ücretli).
2. Yalnızca `inc/` klasörünü şifreleyin (CSS/JS/şablonlar açık kalabilir):
   `ioncube_encoder5 inc/ -o inc-sifreli/ --replace-target`
3. Şifreli `inc/` ile temayı yeniden paketleyin ve **bütünlük manifestini şifrelemeden _sonra_ oluşturun** (panelden zip indirmek bunu zaten sırayla yapar; şifrelenmiş kaynağı `tema-kaynak/` olarak koyun).
4. Müşterilerinize "sunucunuzda ionCube Loader açık olmalı" notu verin (çoğu hostingde açıktır / tek tıkla açılır).

**Lisans sunucusu verileri nerede?**
`data/.ht-licenses.sqlite` dosyasında. Bu dosyayı düzenli yedekleyin.
