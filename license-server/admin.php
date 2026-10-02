<?php
/**
 * Lisans yönetim paneli: lisans oluşturma, iptal, aktivasyon yönetimi.
 */

define( 'NKLS', true );
require __DIR__ . '/lib.php';

session_name( 'nkls_admin' );
session_set_cookie_params( array( 'httponly' => true, 'samesite' => 'Strict', 'secure' => ! empty( $_SERVER['HTTPS'] ) ) );
session_start();
header( 'X-Frame-Options: DENY' );
header( 'Referrer-Policy: no-referrer' );

$config = nkls_config();
if ( empty( $config['admin_password_hash'] ) ) {
	http_response_code( 403 );
	exit( 'Panel kapalı: config.local.php içinde admin_password_hash tanımlayın.' );
}

if ( empty( $_SESSION['csrf'] ) ) {
	$_SESSION['csrf'] = bin2hex( random_bytes( 16 ) );
}
$csrf = $_SESSION['csrf'];
$msg  = '';

$post_ok = 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['csrf'] ) && hash_equals( $csrf, (string) $_POST['csrf'] );

// Giriş / çıkış.
if ( isset( $_GET['logout'] ) ) {
	session_destroy();
	header( 'Location: admin.php' );
	exit;
}
if ( empty( $_SESSION['auth'] ) ) {
	if ( $post_ok && isset( $_POST['password'] ) ) {
		usleep( 400000 ); // Kaba kuvvete karşı yavaşlatma.
		if ( password_verify( (string) $_POST['password'], $config['admin_password_hash'] ) ) {
			session_regenerate_id( true );
			$_SESSION['auth'] = true;
			header( 'Location: admin.php' );
			exit;
		}
		$msg = 'Parola hatalı.';
	}
	?>
	<!doctype html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Lisans Paneli</title>
	<style>body{font-family:system-ui,sans-serif;background:#0f2a4a;display:grid;place-items:center;min-height:100vh;margin:0}form{background:#fff;padding:32px;border-radius:14px;width:320px}input,button{width:100%;padding:12px;margin-top:10px;border-radius:8px;border:1px solid #cbd5e1;box-sizing:border-box;font:inherit}button{background:#f97316;color:#fff;border:0;font-weight:700;cursor:pointer}.e{color:#b91c1c}</style></head>
	<body><form method="post"><h2>🚚 Nakliye Pro Lisans</h2><?php echo $msg ? '<p class="e">' . nkls_e( $msg ) . '</p>' : ''; ?>
	<input type="hidden" name="csrf" value="<?php echo nkls_e( $csrf ); ?>"><input type="password" name="password" placeholder="Parola" autofocus required><button>Giriş</button></form></body></html>
	<?php
	exit;
}

$db = nkls_db();

// İşlemler.
if ( $post_ok && isset( $_POST['do'] ) ) {
	switch ( $_POST['do'] ) {
		case 'create':
			$key  = nkls_generate_key();
			$days = (int) $_POST['days'];
			$db->prepare( 'INSERT INTO licenses (license_key, product, customer, email, max_sites, expires_at, note, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)' )
				->execute( array( $key, $config['product'], trim( (string) $_POST['customer'] ), trim( (string) $_POST['email'] ), max( 1, (int) $_POST['max_sites'] ), $days > 0 ? time() + $days * 86400 : 0, trim( (string) $_POST['note'] ), time() ) );
			$msg = 'Lisans oluşturuldu: ' . $key;
			break;
		case 'toggle':
			$db->prepare( "UPDATE licenses SET status = CASE status WHEN 'active' THEN 'revoked' ELSE 'active' END WHERE id = ?" )->execute( array( (int) $_POST['id'] ) );
			$msg = 'Lisans durumu güncellendi.';
			break;
		case 'extend':
			$db->prepare( 'UPDATE licenses SET expires_at = CASE WHEN expires_at > ? THEN expires_at ELSE ? END + ? WHERE id = ? AND expires_at > 0' )
				->execute( array( time(), time(), 365 * 86400, (int) $_POST['id'] ) );
			$msg = 'Lisans 1 yıl uzatıldı.';
			break;
		case 'remove_activation':
			$db->prepare( 'DELETE FROM activations WHERE id = ?' )->execute( array( (int) $_POST['id'] ) );
			$msg = 'Aktivasyon kaldırıldı.';
			break;
		case 'delete':
			$db->prepare( 'DELETE FROM licenses WHERE id = ?' )->execute( array( (int) $_POST['id'] ) );
			$msg = 'Lisans silindi.';
			break;
	}
}

$q        = isset( $_GET['q'] ) ? trim( (string) $_GET['q'] ) : '';
$stmt     = $db->prepare( 'SELECT l.*, (SELECT COUNT(*) FROM activations a WHERE a.license_id = l.id) AS used FROM licenses l WHERE (? = "" OR l.license_key LIKE ? OR l.customer LIKE ? OR l.email LIKE ?) ORDER BY l.id DESC LIMIT 200' );
$like     = '%' . $q . '%';
$stmt->execute( array( $q, $like, $like, $like ) );
$licenses = $stmt->fetchAll();

$acts = array();
foreach ( $db->query( 'SELECT * FROM activations ORDER BY last_seen DESC' ) as $row ) {
	$acts[ $row['license_id'] ][] = $row;
}
$events = $db->query( 'SELECT e.*, l.license_key FROM events e LEFT JOIN licenses l ON l.id = e.license_id ORDER BY e.id DESC LIMIT 30' )->fetchAll();
$stats  = $db->query( "SELECT (SELECT COUNT(*) FROM licenses) t, (SELECT COUNT(*) FROM licenses WHERE status='active') a, (SELECT COUNT(*) FROM activations) s" )->fetch();

function nkls_csrf_field() {
	return '<input type="hidden" name="csrf" value="' . nkls_e( $_SESSION['csrf'] ) . '">';
}
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Nakliye Pro — Lisans Yönetimi</title>
<style>
	*{box-sizing:border-box}body{margin:0;font-family:system-ui,sans-serif;background:#f1f5f9;color:#0f172a;font-size:14px}
	header{background:#0f2a4a;color:#fff;padding:16px 28px;display:flex;justify-content:space-between;align-items:center}header a{color:#fdba74}
	main{max-width:1280px;margin:0 auto;padding:24px}
	.card{background:#fff;border-radius:12px;padding:20px;margin-bottom:20px;border:1px solid #e2e8f0}
	.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.stats strong{font-size:28px;display:block}
	form.inline{display:inline}input,select,button{font:inherit;padding:8px 10px;border-radius:8px;border:1px solid #cbd5e1}
	button{cursor:pointer;background:#fff}button.p{background:#f97316;border-color:#f97316;color:#fff;font-weight:700}button.d{color:#b91c1c;border-color:#fecaca}
	.grid{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;align-items:end}.grid label{display:flex;flex-direction:column;gap:4px;font-weight:600;font-size:12px}
	table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:10px 8px;border-bottom:1px solid #f1f5f9;vertical-align:top}th{font-size:12px;color:#64748b;text-transform:uppercase}
	code{background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:13px}
	.b{display:inline-block;padding:2px 8px;border-radius:99px;font-size:12px;font-weight:700}.b-active{background:#dcfce7;color:#166534}.b-revoked{background:#fee2e2;color:#991b1b}
	.msg{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-weight:600}
	.acts{font-size:12px;color:#475569}.acts div{margin:2px 0}
	@media(max-width:900px){.grid{grid-template-columns:1fr 1fr}.stats{grid-template-columns:1fr}}
</style>
</head>
<body>
<header><strong>🚚 Nakliye Pro — Lisans Yönetimi</strong><a href="?logout=1">Çıkış</a></header>
<main>
	<?php if ( $msg ) : ?><div class="msg"><?php echo nkls_e( $msg ); ?></div><?php endif; ?>

	<div class="stats">
		<div class="card"><span>Toplam lisans</span><strong><?php echo (int) $stats['t']; ?></strong></div>
		<div class="card"><span>Aktif lisans</span><strong><?php echo (int) $stats['a']; ?></strong></div>
		<div class="card"><span>Aktif site</span><strong><?php echo (int) $stats['s']; ?></strong></div>
	</div>

	<div class="card">
		<h3>Yeni lisans oluştur</h3>
		<form method="post" class="grid">
			<?php echo nkls_csrf_field(); ?><input type="hidden" name="do" value="create">
			<label>Müşteri<input name="customer" required></label>
			<label>E-posta<input type="email" name="email"></label>
			<label>Site limiti<input type="number" name="max_sites" value="1" min="1"></label>
			<label>Süre<select name="days"><option value="0">Süresiz</option><option value="365">1 yıl</option><option value="30">30 gün (deneme)</option></select></label>
			<label>Not<input name="note"></label>
			<button class="p">Oluştur</button>
		</form>
	</div>

	<div class="card">
		<form method="get" style="margin-bottom:12px"><input name="q" value="<?php echo nkls_e( $q ); ?>" placeholder="Anahtar, müşteri veya e-posta ara"> <button>Ara</button></form>
		<table>
			<thead><tr><th>Anahtar</th><th>Müşteri</th><th>Durum</th><th>Siteler</th><th>Bitiş</th><th>İşlemler</th></tr></thead>
			<tbody>
			<?php foreach ( $licenses as $l ) : ?>
				<tr>
					<td><code><?php echo nkls_e( $l['license_key'] ); ?></code><br><small><?php echo nkls_e( $l['note'] ); ?></small></td>
					<td><?php echo nkls_e( $l['customer'] ); ?><br><small><?php echo nkls_e( $l['email'] ); ?></small></td>
					<td><span class="b b-<?php echo nkls_e( $l['status'] ); ?>"><?php echo 'active' === $l['status'] ? 'Aktif' : 'İptal'; ?></span></td>
					<td>
						<?php echo (int) $l['used']; ?> / <?php echo (int) $l['max_sites']; ?>
						<div class="acts">
							<?php foreach ( isset( $acts[ $l['id'] ] ) ? $acts[ $l['id'] ] : array() as $a ) : ?>
								<div>
									<?php echo nkls_e( $a['domain'] ); ?> <small>(v<?php echo nkls_e( $a['version'] ); ?>, <?php echo date( 'd.m.Y', (int) $a['last_seen'] ); ?>)</small>
									<form method="post" class="inline"><?php echo nkls_csrf_field(); ?><input type="hidden" name="do" value="remove_activation"><input type="hidden" name="id" value="<?php echo (int) $a['id']; ?>"><button class="d" title="Kaldır">×</button></form>
								</div>
							<?php endforeach; ?>
						</div>
					</td>
					<td><?php echo $l['expires_at'] ? date( 'd.m.Y', (int) $l['expires_at'] ) : 'Süresiz'; ?></td>
					<td>
						<form method="post" class="inline"><?php echo nkls_csrf_field(); ?><input type="hidden" name="do" value="toggle"><input type="hidden" name="id" value="<?php echo (int) $l['id']; ?>"><button><?php echo 'active' === $l['status'] ? 'İptal et' : 'Etkinleştir'; ?></button></form>
						<?php if ( $l['expires_at'] ) : ?>
							<form method="post" class="inline"><?php echo nkls_csrf_field(); ?><input type="hidden" name="do" value="extend"><input type="hidden" name="id" value="<?php echo (int) $l['id']; ?>"><button>+1 yıl</button></form>
						<?php endif; ?>
						<form method="post" class="inline" onsubmit="return confirm('Lisans kalıcı olarak silinsin mi?')"><?php echo nkls_csrf_field(); ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?php echo (int) $l['id']; ?>"><button class="d">Sil</button></form>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if ( ! $licenses ) : ?><tr><td colspan="6">Kayıt yok.</td></tr><?php endif; ?>
			</tbody>
		</table>
	</div>

	<div class="card">
		<h3>Son API olayları</h3>
		<table>
			<thead><tr><th>Zaman</th><th>İşlem</th><th>Anahtar</th><th>Alan adı</th><th>IP</th><th>Sonuç</th></tr></thead>
			<tbody>
			<?php foreach ( $events as $ev ) : ?>
				<tr><td><?php echo date( 'd.m.Y H:i', (int) $ev['created_at'] ); ?></td><td><?php echo nkls_e( $ev['action'] ); ?></td><td><code><?php echo nkls_e( $ev['license_key'] ); ?></code></td><td><?php echo nkls_e( $ev['domain'] ); ?></td><td><?php echo nkls_e( $ev['ip'] ); ?></td><td><?php echo nkls_e( $ev['result'] ); ?></td></tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</main>
</body>
</html>
