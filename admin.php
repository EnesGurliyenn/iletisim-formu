<?php
// ============================================================
// admin.php — Mesaj Yönetim Paneli (Basit Admin Ekranı)
// ============================================================
// NOT: Gerçek projede bu sayfayı şifre ile koruyun!

session_start();
require_once 'includes/baglan.php';

// Sayfalama ayarları
$sayfa_basi = 10;                                        // Sayfada gösterilecek kayıt sayısı
$sayfa      = max(1, (int)($_GET['sayfa'] ?? 1));       // Mevcut sayfa (minimum 1)
$offset     = ($sayfa - 1) * $sayfa_basi;               // SQL OFFSET hesabı

// Filtre parametresi (URL'den gelir: ?durum=yeni)
$filtre  = $_GET['durum'] ?? 'hepsi';
$izinli  = ['hepsi','yeni','okundu','cevaplandi','spam'];
if (!in_array($filtre, $izinli)) $filtre = 'hepsi';

// SQL sorgusunu filtre durumuna göre oluştur
$where = $filtre !== 'hepsi' ? 'WHERE durum = :durum' : '';
$params = $filtre !== 'hepsi' ? [':durum' => $filtre] : [];

// Toplam kayıt sayısı (sayfalama için)
$toplam_stmt = $pdo->prepare("SELECT COUNT(*) FROM mesajlar $where");
$toplam_stmt->execute($params);
$toplam_kayit = $toplam_stmt->fetchColumn();
$toplam_sayfa = (int)ceil($toplam_kayit / $sayfa_basi);

// Kayıtları çek
$sql  = "SELECT * FROM mesajlar $where ORDER BY tarih DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $sayfa_basi, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,     PDO::PARAM_INT);
$stmt->execute();
$mesajlar = $stmt->fetchAll();

// Durum güncelleme (POST ile)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['yeni_durum'])) {
    $izinli_durum = ['okundu','cevaplandi','spam','yeni'];
    if (in_array($_POST['yeni_durum'], $izinli_durum)) {
        $up = $pdo->prepare('UPDATE mesajlar SET durum = ? WHERE id = ?');
        $up->execute([$_POST['yeni_durum'], (int)$_POST['id']]);
    }
    header('Location: admin.php?durum=' . $filtre . '&sayfa=' . $sayfa);
    exit;
}

// İstatistikler
$istat = $pdo->query("
    SELECT durum, COUNT(*) AS adet FROM mesajlar GROUP BY durum
")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Mesaj Yönetim Paneli</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
  .header { background: #1e293b; padding: 1.5rem 2rem; border-bottom: 2px solid #334155; display: flex; align-items: center; gap: 1rem; }
  .header h1 { font-size: 1.4rem; color: #38bdf8; }
  .badge { background: #ef4444; color: #fff; border-radius: 9999px; padding: 2px 10px; font-size: .75rem; font-weight: 700; }
  .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
  .istat-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; margin-bottom: 2rem; }
  .istat-kart { background: #1e293b; border-radius: 12px; padding: 1.2rem; text-align: center; border: 1px solid #334155; }
  .istat-kart .sayi { font-size: 2rem; font-weight: 800; color: #38bdf8; }
  .istat-kart .etiket { font-size: .8rem; color: #94a3b8; margin-top: .3rem; text-transform: uppercase; letter-spacing: .05em; }
  .filtreler { display: flex; gap: .7rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
  .filtreler a { padding: .5rem 1.2rem; border-radius: 9999px; border: 1px solid #334155; color: #94a3b8; text-decoration: none; font-size: .9rem; transition: all .2s; }
  .filtreler a.aktif, .filtreler a:hover { background: #38bdf8; color: #0f172a; border-color: #38bdf8; font-weight: 600; }
  table { width: 100%; border-collapse: collapse; background: #1e293b; border-radius: 12px; overflow: hidden; }
  th { background: #0f172a; color: #38bdf8; padding: .9rem 1rem; text-align: left; font-size: .8rem; text-transform: uppercase; letter-spacing: .07em; }
  td { padding: .9rem 1rem; border-top: 1px solid #334155; font-size: .88rem; vertical-align: top; }
  tr:hover td { background: #263248; }
  .durum { display: inline-block; padding: .25rem .75rem; border-radius: 9999px; font-size: .75rem; font-weight: 700; }
  .durum.yeni       { background: #1d4ed8; color: #bfdbfe; }
  .durum.okundu     { background: #166534; color: #bbf7d0; }
  .durum.cevaplandi { background: #7c3aed; color: #ede9fe; }
  .durum.spam       { background: #9f1239; color: #fecdd3; }
  select.guncelle { background: #334155; border: 1px solid #475569; color: #e2e8f0; border-radius: 6px; padding: .3rem .5rem; font-size: .8rem; cursor: pointer; }
  .sayfalama { display: flex; gap: .5rem; justify-content: center; margin-top: 2rem; }
  .sayfalama a { padding: .5rem 1rem; background: #1e293b; border: 1px solid #334155; color: #94a3b8; border-radius: 8px; text-decoration: none; }
  .sayfalama a.aktif { background: #38bdf8; color: #0f172a; border-color: #38bdf8; font-weight: 700; }
  .mesaj-text { max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #94a3b8; }
  form.inline { display:inline; }
</style>
</head>
<body>
<div class="header">
  <h1>📬 Mesaj Yönetim Paneli</h1>
  <span class="badge"><?= $istat['yeni'] ?? 0 ?> yeni</span>
</div>
<div class="container">

  <!-- İstatistik Kartları -->
  <div class="istat-grid">
    <div class="istat-kart"><div class="sayi"><?= $toplam_kayit ?></div><div class="etiket">Toplam Mesaj</div></div>
    <div class="istat-kart"><div class="sayi" style="color:#60a5fa"><?= $istat['yeni'] ?? 0 ?></div><div class="etiket">Yeni</div></div>
    <div class="istat-kart"><div class="sayi" style="color:#34d399"><?= $istat['cevaplandi'] ?? 0 ?></div><div class="etiket">Cevaplanan</div></div>
    <div class="istat-kart"><div class="sayi" style="color:#f87171"><?= $istat['spam'] ?? 0 ?></div><div class="etiket">Spam</div></div>
  </div>

  <!-- Filtreler -->
  <div class="filtreler">
    <?php foreach (['hepsi'=>'Tümü','yeni'=>'Yeni','okundu'=>'Okundu','cevaplandi'=>'Cevaplanan','spam'=>'Spam'] as $d => $l): ?>
      <a href="?durum=<?= $d ?>" class="<?= $filtre===$d?'aktif':'' ?>"><?= $l ?></a>
    <?php endforeach; ?>
  </div>

  <!-- Mesaj Tablosu -->
  <table>
    <thead>
      <tr>
        <th>#ID</th><th>Ad Soyad</th><th>E-posta</th><th>Konu</th>
        <th>Mesaj</th><th>Durum</th><th>Tarih</th><th>İşlem</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($mesajlar)): ?>
      <tr><td colspan="8" style="text-align:center;color:#64748b;padding:3rem">Kayıt bulunamadı.</td></tr>
    <?php else: foreach ($mesajlar as $m): ?>
      <tr>
        <td><?= $m['id'] ?></td>
        <td><?= htmlspecialchars($m['ad_soyad']) ?></td>
        <td><?= htmlspecialchars($m['email']) ?></td>
        <td><?= htmlspecialchars($m['konu']) ?></td>
        <td><div class="mesaj-text" title="<?= htmlspecialchars($m['mesaj']) ?>"><?= htmlspecialchars($m['mesaj']) ?></div></td>
        <td><span class="durum <?= $m['durum'] ?>"><?= ucfirst($m['durum']) ?></span></td>
        <td><?= date('d.m.Y H:i', strtotime($m['tarih'])) ?></td>
        <td>
          <form class="inline" method="POST">
            <input type="hidden" name="id" value="<?= $m['id'] ?>">
            <select name="yeni_durum" class="guncelle" onchange="this.form.submit()">
              <?php foreach (['yeni','okundu','cevaplandi','spam'] as $d): ?>
                <option value="<?= $d ?>" <?= $m['durum']===$d?'selected':'' ?>><?= ucfirst($d) ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>

  <!-- Sayfalama -->
  <?php if ($toplam_sayfa > 1): ?>
  <div class="sayfalama">
    <?php for ($s = 1; $s <= $toplam_sayfa; $s++): ?>
      <a href="?durum=<?= $filtre ?>&sayfa=<?= $s ?>" class="<?= $sayfa===$s?'aktif':'' ?>"><?= $s ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>

</div>
</body>
</html>
