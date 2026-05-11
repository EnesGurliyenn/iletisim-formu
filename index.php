<?php
// ============================================================
// index.php — İleri Seviye İletişim Formu (Ana Sayfa)
// ============================================================

session_start(); // PHP oturumunu başlat

// CSRF Token Oluştur (yoksa) — Her oturumda bir kez üretilir
// bin2hex(random_bytes(32)) = kriptografik olarak güvenli 64 karakter token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token']; // HTML'de kullanmak için değişkene al
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>İletişim Formu — İleri Seviye</title>

  <!-- Google Fonts: Playfair Display + DM Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

  <style>
    /* ============================================================
       CSS Değişkenleri — Renk Paleti & Tema
       ============================================================ */
    :root {
      --bg:         #0a0f1e;        /* Sayfa arka planı */
      --card:       #111827;        /* Form kartı arka planı */
      --border:     #1f2937;        /* Kenar rengi */
      --border-h:   #3b82f6;        /* Hover/focus kenar rengi */
      --text:       #f1f5f9;        /* Ana metin */
      --muted:      #6b7280;        /* Soluk metin */
      --accent:     #3b82f6;        /* Vurgu (mavi) */
      --accent2:    #8b5cf6;        /* İkinci vurgu (mor) */
      --success:    #10b981;        /* Başarı rengi */
      --error:      #ef4444;        /* Hata rengi */
      --warn:       #f59e0b;        /* Uyarı rengi */
      --input-bg:   #1a2234;        /* Input arka planı */
      --radius:     14px;           /* Genel köşe yuvarlama */
    }

    /* ============================================================
       Reset & Temel Stiller
       ============================================================ */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
      /* Arka plan ızgara deseni */
      background-image:
        radial-gradient(circle at 20% 20%, rgba(59,130,246,.12) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(139,92,246,.10) 0%, transparent 50%),
        linear-gradient(rgba(59,130,246,.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(59,130,246,.03) 1px, transparent 1px);
      background-size: auto, auto, 60px 60px, 60px 60px;
    }

    /* ============================================================
       Form Kartı
       ============================================================ */
    .kart {
      width: 100%;
      max-width: 680px;
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 25px 80px rgba(0,0,0,.5), 0 0 0 1px rgba(255,255,255,.04);
      animation: kartAc .6s cubic-bezier(.22,1,.36,1);
    }

    @keyframes kartAc {
      from { opacity: 0; transform: translateY(30px) scale(.97); }
      to   { opacity: 1; transform: translateY(0)   scale(1);    }
    }

    /* ============================================================
       Form Başlığı
       ============================================================ */
    .baslik {
      padding: 2.5rem 2.5rem 2rem;
      border-bottom: 1px solid var(--border);
      position: relative;
      overflow: hidden;
    }
    .baslik::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(59,130,246,.08) 0%, rgba(139,92,246,.06) 100%);
      pointer-events: none;
    }
    .baslik-ust { display: flex; align-items: center; gap: 1rem; margin-bottom: .75rem; }
    .ikon {
      width: 52px; height: 52px; border-radius: 14px;
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
      box-shadow: 0 8px 24px rgba(59,130,246,.35);
    }
    .baslik h1 {
      font-family: 'Playfair Display', serif;
      font-size: 1.9rem; font-weight: 700;
      background: linear-gradient(135deg, #e2e8f0, #94a3b8);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .baslik p { color: var(--muted); font-size: .93rem; line-height: 1.5; }

    /* ============================================================
       Form Alanları
       ============================================================ */
    .form-govde { padding: 2rem 2.5rem 2.5rem; }

    .alan-grup {   /* İki alanı yan yana koymak için flex wrapper */
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.2rem;
    }
    @media (max-width: 520px) {
      .alan-grup { grid-template-columns: 1fr; }
      .baslik, .form-govde { padding: 1.5rem; }
    }

    .alan { margin-bottom: 1.3rem; }

    label {
      display: block;
      font-size: .83rem; font-weight: 600;
      color: #94a3b8; letter-spacing: .04em; text-transform: uppercase;
      margin-bottom: .5rem;
    }
    label .zorunlu { color: var(--accent); margin-left: 2px; }

    /* Input & Textarea ortak stil */
    input[type="text"],
    input[type="email"],
    input[type="tel"],
    textarea,
    input[type="file"],
    select {
      width: 100%;
      background: var(--input-bg);
      border: 1.5px solid var(--border);
      border-radius: var(--radius);
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      font-size: .95rem;
      padding: .85rem 1.1rem;
      transition: border-color .25s, box-shadow .25s, background .25s;
      outline: none;
      -webkit-appearance: none;
    }
    input:focus, textarea:focus, select:focus {
      border-color: var(--border-h);
      box-shadow: 0 0 0 4px rgba(59,130,246,.15);
      background: #1e2a40;
    }
    /* Geçersiz alan vurgusu */
    input.hatali, textarea.hatali { border-color: var(--error) !important; }
    .hata-mesaji {
      color: var(--error); font-size: .8rem; margin-top: .4rem;
      display: none; /* JS ile gösterilir */
      animation: salla .35s ease;
    }
    @keyframes salla {
      0%,100% { transform: translateX(0); }
      25%      { transform: translateX(-6px); }
      75%      { transform: translateX(6px); }
    }
    .hata-mesaji.goster { display: block; }

    textarea { resize: vertical; min-height: 140px; line-height: 1.6; }

    /* Karakter sayacı */
    .sayac { text-align: right; font-size: .75rem; color: var(--muted); margin-top: .3rem; }
    .sayac.uyari { color: var(--warn); }
    .sayac.doldu  { color: var(--error); }

    /* Dosya yükleme özel tasarım */
    .dosya-alan {
      border: 2px dashed var(--border);
      border-radius: var(--radius);
      padding: 1.5rem;
      text-align: center;
      cursor: pointer;
      transition: border-color .25s, background .25s;
      position: relative;
    }
    .dosya-alan:hover { border-color: var(--accent); background: rgba(59,130,246,.05); }
    .dosya-alan input[type="file"] {
      position: absolute; inset: 0; opacity: 0; cursor: pointer; border: none;
    }
    .dosya-alan .dosya-ikon { font-size: 2rem; margin-bottom: .5rem; }
    .dosya-alan p { color: var(--muted); font-size: .85rem; }
    .dosya-adi-goster { font-size: .85rem; color: var(--accent); margin-top: .5rem; font-weight: 500; }

    /* ============================================================
       Gönder Butonu
       ============================================================ */
    .gonder-btn {
      width: 100%;
      padding: 1rem 2rem;
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      color: #fff;
      font-family: 'DM Sans', sans-serif;
      font-size: 1rem; font-weight: 600;
      border: none; border-radius: var(--radius);
      cursor: pointer;
      transition: transform .2s, box-shadow .2s, opacity .2s;
      position: relative; overflow: hidden;
      box-shadow: 0 8px 32px rgba(59,130,246,.35);
    }
    .gonder-btn:hover  { transform: translateY(-2px); box-shadow: 0 12px 40px rgba(59,130,246,.45); }
    .gonder-btn:active { transform: translateY(0); }
    .gonder-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }

    /* Yükleniyor animasyonu */
    .gonder-btn .spinner {
      display: none;
      width: 18px; height: 18px;
      border: 2px solid rgba(255,255,255,.3);
      border-top-color: #fff;
      border-radius: 50%;
      animation: dondur .8s linear infinite;
      margin: 0 auto;
    }
    @keyframes dondur { to { transform: rotate(360deg); } }

    /* ============================================================
       Bildirim Alanı (Başarı / Hata)
       ============================================================ */
    .bildirim {
      display: none;
      border-radius: var(--radius);
      padding: 1.1rem 1.3rem;
      margin-bottom: 1.5rem;
      font-size: .92rem; font-weight: 500;
      border-left: 4px solid;
      animation: kayan .4s ease;
    }
    @keyframes kayan {
      from { opacity: 0; transform: translateY(-10px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .bildirim.basari {
      background: rgba(16,185,129,.1);
      border-color: var(--success);
      color: #6ee7b7;
      display: flex; align-items: center; gap: .7rem;
    }
    .bildirim.hata {
      background: rgba(239,68,68,.1);
      border-color: var(--error);
      color: #fca5a5;
      display: flex; align-items: center; gap: .7rem;
    }

    /* ============================================================
       Admin Linki
       ============================================================ */
    .admin-link {
      text-align: center; margin-top: 1.5rem; font-size: .82rem; color: var(--muted);
    }
    .admin-link a { color: var(--accent); text-decoration: none; }
    .admin-link a:hover { text-decoration: underline; }
  </style>
</head>

<body>
<div class="kart">

  <!-- BAŞLIK -->
  <div class="baslik">
    <div class="baslik-ust">
      <div class="ikon">✉️</div>
      <h1>Bize Yazın</h1>
    </div>
    <p>Aşağıdaki formu eksiksiz doldurun. Mesajınızı en kısa sürede değerlendireceğiz.</p>
  </div>

  <!-- FORM GÖVDESİ -->
  <div class="form-govde">

    <!-- Bildirim Alanı (başlangıçta gizli, JS ile açılır) -->
    <div class="bildirim" id="bildirim"></div>

    <form id="iletisimFormu" novalidate>

      <!--
        CSRF Token: Gizli bir alan olarak forma eklenir.
        PHP session'dan alınan token, form gönderildiğinde doğrulanır.
        Bu, başka sitelerden sahte form gönderimlerini (CSRF saldırısı) engeller.
      -->
      <input type="hidden" name="csrf_token" id="csrfToken" value="<?= htmlspecialchars($csrf) ?>">

      <!-- Ad Soyad + E-posta (yan yana) -->
      <div class="alan-grup">
        <div class="alan">
          <label for="ad_soyad">Ad Soyad <span class="zorunlu">*</span></label>
          <input type="text" id="ad_soyad" name="ad_soyad"
                 placeholder="Ahmet Yılmaz" autocomplete="name">
          <div class="hata-mesaji" id="hata_ad_soyad"></div>
        </div>
        <div class="alan">
          <label for="email">E-posta <span class="zorunlu">*</span></label>
          <input type="email" id="email" name="email"
                 placeholder="ornek@email.com" autocomplete="email">
          <div class="hata-mesaji" id="hata_email"></div>
        </div>
      </div>

      <!-- Telefon + Konu (yan yana) -->
      <div class="alan-grup">
        <div class="alan">
          <label for="telefon">Telefon</label>
          <input type="tel" id="telefon" name="telefon"
                 placeholder="0555 123 45 67" autocomplete="tel">
          <div class="hata-mesaji" id="hata_telefon"></div>
        </div>
        <div class="alan">
          <label for="konu">Konu <span class="zorunlu">*</span></label>
          <input type="text" id="konu" name="konu"
                 placeholder="Konunuzu yazın">
          <div class="hata-mesaji" id="hata_konu"></div>
        </div>
      </div>

      <!-- Mesaj (tam genişlik + karakter sayacı) -->
      <div class="alan">
        <label for="mesaj">Mesajınız <span class="zorunlu">*</span></label>
        <textarea id="mesaj" name="mesaj" maxlength="2000"
                  placeholder="Mesajınızı buraya yazın..."></textarea>
        <div class="sayac" id="sayac">0 / 2000</div>
        <div class="hata-mesaji" id="hata_mesaj"></div>
      </div>

      <!-- Dosya Yükleme (opsiyonel) -->
      <div class="alan">
        <label>Dosya Ekle <span style="font-weight:400;text-transform:none;color:var(--muted)">(opsiyonel, maks. 5 MB)</span></label>
        <div class="dosya-alan">
          <div class="dosya-ikon">📎</div>
          <p>PDF, Word, JPG veya PNG yükleyin</p>
          <input type="file" name="dosya" id="dosya"
                 accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
          <div class="dosya-adi-goster" id="dosyaAdi"></div>
        </div>
      </div>

      <!-- Gönder Butonu -->
      <button type="submit" class="gonder-btn" id="gonderBtn">
        <span id="btnMetin">Mesajı Gönder →</span>
        <div class="spinner" id="spinner"></div>
      </button>

    </form>
  </div><!-- /form-govde -->
</div><!-- /kart -->

<div class="admin-link">
  <a href="admin.php" target="_blank">🔧 Admin Paneli</a> &nbsp;·&nbsp;
  <a href="veritabani_kur.sql" download>📥 SQL Dosyasını İndir</a>
</div>

<!-- ============================================================
     JavaScript — AJAX Form Gönderimi + Client-Side Doğrulama
     ============================================================ -->
<script>
// ---- Elemanları Seç ----
const form       = document.getElementById('iletisimFormu');
const bildirim   = document.getElementById('bildirim');
const gonderBtn  = document.getElementById('gonderBtn');
const btnMetin   = document.getElementById('btnMetin');
const spinner    = document.getElementById('spinner');
const mesajArea  = document.getElementById('mesaj');
const sayacEl    = document.getElementById('sayac');
const dosyaInput = document.getElementById('dosya');
const dosyaAdi   = document.getElementById('dosyaAdi');

// ---- Karakter Sayacı ----
// mesaj textarea'sına her tuş basıldığında çalışır
mesajArea.addEventListener('input', () => {
  const uzunluk = mesajArea.value.length;
  const max     = parseInt(mesajArea.getAttribute('maxlength'));
  sayacEl.textContent = `${uzunluk} / ${max}`;

  // Renkle uyarı ver
  sayacEl.className = 'sayac';
  if (uzunluk > max * .9)       sayacEl.classList.add('uyari');  // %90'ı geçince sarı
  if (uzunluk >= max)            sayacEl.classList.add('doldu'); // dolunca kırmızı
});

// ---- Dosya seçildiğinde adı göster ----
dosyaInput.addEventListener('change', () => {
  dosyaAdi.textContent = dosyaInput.files[0]?.name || '';
});

// ---- Hata Göster / Temizle Yardımcı Fonksiyonları ----
function hataGoster(id, mesaj) {
  const el = document.getElementById('hata_' + id);
  const input = document.getElementById(id);
  if (el) { el.textContent = mesaj; el.classList.add('goster'); }
  if (input) input.classList.add('hatali');
}

function hataCoz(id) {
  const el = document.getElementById('hata_' + id);
  const input = document.getElementById(id);
  if (el) { el.textContent = ''; el.classList.remove('goster'); }
  if (input) input.classList.remove('hatali');
}

function tumHatalariTemizle() {
  ['ad_soyad','email','telefon','konu','mesaj'].forEach(hataCoz);
}

// ---- Client-Side Doğrulama (ön kontrol) ----
// Sunucuya gitmeden önce temel kontroller yapılır (UX için)
function dogrula() {
  let gecerli = true;
  tumHatalariTemizle();

  const adSoyad = document.getElementById('ad_soyad').value.trim();
  const email   = document.getElementById('email').value.trim();
  const konu    = document.getElementById('konu').value.trim();
  const mesaj   = document.getElementById('mesaj').value.trim();
  const telefon = document.getElementById('telefon').value.trim();

  if (adSoyad.length < 2) { hataGoster('ad_soyad', 'En az 2 karakter girin.'); gecerli = false; }
  if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) { hataGoster('email', 'Geçerli e-posta girin.'); gecerli = false; }
  if (telefon && telefon.replace(/\D/g,'').length < 10) { hataGoster('telefon', 'En az 10 rakam girin.'); gecerli = false; }
  if (konu.length < 3)  { hataGoster('konu', 'En az 3 karakter girin.'); gecerli = false; }
  if (mesaj.length < 10){ hataGoster('mesaj', 'En az 10 karakter girin.'); gecerli = false; }

  return gecerli;
}

// ---- Form Gönderimi (AJAX ile) ----
form.addEventListener('submit', async (e) => {
  e.preventDefault(); // Sayfanın yenilenmesini engelle

  if (!dogrula()) return; // Client-side doğrulama başarısızsa dur

  // Butonu devre dışı bırak ve spinner göster (çift gönderimi önle)
  gonderBtn.disabled = true;
  btnMetin.style.display = 'none';
  spinner.style.display  = 'block';
  bildirim.style.display = 'none';

  // FormData: hem normal alanları hem dosyayı taşır
  const veri = new FormData(form);

  try {
    // AJAX isteği: fetch API ile asenkron gönderim
    const yanit = await fetch('form_isle.php', {
      method: 'POST',
      body: veri // Content-Type otomatik multipart/form-data olur
    });

    // Yanıtı JSON'a çevir
    const json = await yanit.json();

    if (json.basarili) {
      // ✅ Başarı
      bildirim.className   = 'bildirim basari';
      bildirim.innerHTML   = `✅ ${json.mesaj}`;
      bildirim.style.display = 'flex';
      form.reset();               // Formu temizle
      sayacEl.textContent  = '0 / 2000';
      dosyaAdi.textContent = '';

      // CSRF token'ı güncelle (sunucu yeni token döndürür)
      if (json.yeni_csrf) {
        document.getElementById('csrfToken').value = json.yeni_csrf;
      }
    } else if (json.hatalar) {
      // ⚠️ Alan bazında hatalar
      Object.entries(json.hatalar).forEach(([alan, mesaj]) => {
        hataGoster(alan, mesaj);
      });
    } else {
      // ❌ Genel hata mesajı
      bildirim.className   = 'bildirim hata';
      bildirim.innerHTML   = `❌ ${json.mesaj}`;
      bildirim.style.display = 'flex';
    }

  } catch (hata) {
    // Ağ hatası veya geçersiz JSON
    bildirim.className   = 'bildirim hata';
    bildirim.innerHTML   = '❌ Bağlantı hatası oluştu. Lütfen tekrar deneyin.';
    bildirim.style.display = 'flex';
  } finally {
    // Her durumda butonu tekrar aktif et
    gonderBtn.disabled    = false;
    btnMetin.style.display = 'inline';
    spinner.style.display  = 'none';

    // Bildirimi ekrana kaydır
    bildirim.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
});

// ---- Gerçek zamanlı tek alan doğrulama (blur olayında) ----
// Kullanıcı alandan çıkınca o alanı kontrol et
['ad_soyad','email','konu'].forEach(id => {
  document.getElementById(id)?.addEventListener('blur', () => {
    const val = document.getElementById(id).value.trim();
    hataCoz(id); // Önce hatayı temizle
    if (id === 'ad_soyad' && val.length < 2) hataGoster(id, 'En az 2 karakter girin.');
    if (id === 'email' && val && !val.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) hataGoster(id, 'Geçerli e-posta girin.');
    if (id === 'konu' && val.length < 3) hataGoster(id, 'En az 3 karakter girin.');
  });
});
</script>
</body>
</html>
