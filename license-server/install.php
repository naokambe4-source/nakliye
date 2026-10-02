<?php
/**
 * Lisans sunucusu kurulum sihirbazı (tarayıcıdan çalışır, terminal gerekmez).
 *
 * 1. Sunucu gereksinimlerini kontrol eder.
 * 2. Panel parolasını kaydeder.
 * 3. RSA anahtar çiftini üretir, veritabanını oluşturur.
 * Kurulum bittikten sonra kendini kilitler.
 */

define( 'NKLS', true );
require __DIR__ . '/lib.php';

header( 'X-Frame-Options: DENY' );
header( 'Cache-Control: no-store' );

$config = nkls_config();
$errors = array();
$done   = false;

if ( nkls_installed() ) {
	header( 'Location: admin.php' );
	exit;
}

$reqs   = nkls_requirements();
$req_ok = true;
foreach ( $reqs as $r ) {
	if ( $r[3] && ! $r[2] ) {
		$req_ok = false;
	}
}

if ( 'POST' === ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) && $req_ok ) {
	$pass  = isset( $_POST['password'] ) ? (string) $_POST['password'] : '';
	$pass2 = isset( $_POST['password2'] ) ? (string) $_POST['password2'] : '';

	if ( strlen( $pass ) < 8 ) {
		$errors[] = 'Parola en az 8 karakter olmalı.';
	} elseif ( $pass !== $pass2 ) {
		$errors[] = 'Parolalar eşleşmiyor.';
	}

	if ( ! $errors ) {
		// Anahtar: daha önce üretildiyse koru (lisanslar geçersiz olmasın).
		if ( ! is_readable( $config['private_key_path'] ) ) {
			$pem = nkls_generate_private_key();
			if ( ! $pem ) {
				$errors[] = 'RSA anahtarı üretilemedi (OpenSSL yapılandırması). Hosting firmanızdan "openssl_pkey_new" desteği isteyin. Hata: ' . (string) openssl_error_string();
			} elseif ( false === @file_put_contents( $config['private_key_path'], $pem, LOCK_EX ) ) {
				$errors[] = 'keys/ klasörüne yazılamadı. Klasör iznini 755 yapın.';
			} else {
				@chmod( $config['private_key_path'], 0600 );
			}
		}
	}

	if ( ! $errors ) {
		try {
			nkls_db(); // Tabloları oluşturur.
			@chmod( $config['db_path'], 0600 );
		} catch ( Throwable $e ) {
			$errors[] = 'Veritabanı oluşturulamadı: ' . $e->getMessage();
		}
	}

	if ( ! $errors ) {
		$saved = nkls_save_settings(
			array(
				'admin_password_hash' => password_hash( $pass, PASSWORD_DEFAULT ),
				'installed_at'        => time(),
				'base_url'            => nkls_base_url(),
			)
		);
		if ( ! $saved ) {
			$errors[] = 'data/ klasörüne yazılamadı. Klasör iznini 755 yapın.';
		} else {
			$done = true;
		}
	}
}
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Nakliye Pro — Lisans Sunucusu Kurulumu</title>
<style>
	*{box-sizing:border-box}body{margin:0;min-height:100vh;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;background:radial-gradient(circle at 80% 0,#1e3a8a,#0f2a4a 45%,#020617);color:#0f172a;padding:40px 16px}
	.box{max-width:640px;margin:0 auto;background:#fff;border-radius:18px;padding:36px;box-shadow:0 40px 80px -30px rgba(0,0,0,.6)}
	h1{margin:0 0 6px;font-size:24px}p{color:#475569;line-height:1.6}
	table{width:100%;border-collapse:collapse;margin:16px 0 24px}td{padding:9px 6px;border-bottom:1px solid #f1f5f9;font-size:14px}
	.ok{color:#16a34a;font-weight:700}.bad{color:#dc2626;font-weight:700}.warn{color:#d97706;font-weight:700}
	label{display:block;font-weight:600;font-size:14px;margin:14px 0 6px}input{width:100%;padding:12px 14px;border:1.5px solid #cbd5e1;border-radius:10px;font:inherit}
	button,.btn{display:inline-block;margin-top:22px;padding:13px 26px;border:0;border-radius:10px;background:#f97316;color:#fff;font-weight:700;font-size:15px;cursor:pointer;text-decoration:none}
	.err{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:12px 16px;border-radius:10px;margin:10px 0}
	.success{text-align:center}.success .icon{font-size:56px}
	code{background:#f1f5f9;padding:2px 6px;border-radius:5px;font-size:13px;word-break:break-all}
	.logo{color:#fff;text-align:center;font-weight:800;font-size:22px;margin-bottom:22px}
</style>
</head>
<body>
<div class="logo">🚚 Nakliye Pro · Lisans Sunucusu</div>
<div class="box">
<?php if ( $done ) : ?>
	<div class="success">
		<div class="icon">✅</div>
		<h1>Kurulum tamamlandı</h1>
		<p>Anahtar çifti üretildi, veritabanı oluşturuldu ve panel parolanız kaydedildi.</p>
		<p>Lisans sunucusu adresiniz:<br><code><?php echo nkls_e( nkls_base_url() ); ?></code></p>
		<a class="btn" href="admin.php">Panele giriş yap →</a>
	</div>
<?php else : ?>
	<h1>Kurulum sihirbazı</h1>
	<p>Bu sihirbaz lisans sunucunuzu birkaç saniyede hazırlar. Terminal veya dosya düzenleme gerekmez.</p>

	<table>
		<?php foreach ( $reqs as $r ) : ?>
			<tr><td><?php echo nkls_e( $r[0] ); ?></td><td><?php echo nkls_e( $r[1] ); ?></td>
			<td class="<?php echo $r[2] ? 'ok' : ( $r[3] ? 'bad' : 'warn' ); ?>"><?php echo $r[2] ? '✓' : ( $r[3] ? '✗ Gerekli' : '! Önerilir' ); ?></td></tr>
		<?php endforeach; ?>
	</table>

	<?php foreach ( $errors as $e ) : ?><div class="err"><?php echo nkls_e( $e ); ?></div><?php endforeach; ?>

	<?php if ( ! $req_ok ) : ?>
		<div class="err">Kırmızı işaretli gereksinimleri hosting panelinizden (cPanel → "PHP Sürümü Seç" / "PHP Eklentileri") düzeltip sayfayı yenileyin.</div>
	<?php else : ?>
		<form method="post" autocomplete="off">
			<label for="p1">Panel parolası (en az 8 karakter)</label>
			<input id="p1" type="password" name="password" required minlength="8" autofocus>
			<label for="p2">Parola tekrar</label>
			<input id="p2" type="password" name="password2" required minlength="8">
			<button type="submit">Kurulumu tamamla</button>
		</form>
	<?php endif; ?>
<?php endif; ?>
</div>
</body>
</html>
