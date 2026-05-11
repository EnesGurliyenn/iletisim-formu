<?php
// ============================================================
// form_isle.php — Form İşleme Backend'i (AJAX destekli)
// ============================================================

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit('Geçersiz istek yöntemi.');
}

// JSON yanıt göndereceğiz
header('Content-Type: application/json; charset=utf-8');

// Veritabanı bağlantısını dahil et
require_once 'includes/baglan.php';

// ============================================================
// 1. CSRF Token Doğrulama (Güvenlik katmanı)
// ============================================================
session_start();

if (
    empty($_POST['csrf_token']) ||
    !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    echo json_encode(['basarili' => false, 'mesaj' => 'Güvenlik doğrulaması başarısız.']);
    exit;
}

// ============================================================
// 2. Veri Alma & Temizleme
// ============================================================
// filter_input: Hem varlık kontrolü hem de tip dönüşümü yapar
$ad_soyad = trim(filter_input(INPUT_POST, 'ad_soyad', FILTER_SANITIZE_SPECIAL_CHARS));
$email    = trim(filter_input(INPUT_POST, 'email',    FILTER_SANITIZE_EMAIL));
$telefon  = trim(filter_input(INPUT_POST, 'telefon',  FILTER_SANITIZE_SPECIAL_CHARS));
$konu     = trim(filter_input(INPUT_POST, 'konu',     FILTER_SANITIZE_SPECIAL_CHARS));
$mesaj    = trim(filter_input(INPUT_POST, 'mesaj',    FILTER_SANITIZE_SPECIAL_CHARS));
$ip       = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR']; // Gerçek IP adresi

// ============================================================
// 3. Doğrulama (Validation)
// ============================================================
$hatalar = [];

// Ad Soyad: 2-100 karakter, sadece harf ve boşluk
if (empty($ad_soyad)) {
    $hatalar['ad_soyad'] = 'Ad Soyad alanı zorunludur.';
} elseif (mb_strlen($ad_soyad) < 2 || mb_strlen($ad_soyad) > 100) {
    $hatalar['ad_soyad'] = 'Ad Soyad 2-100 karakter olmalıdır.';
} elseif (!preg_match('/^[\p{L}\s\-\.]+$/u', $ad_soyad)) {
    // \p{L} = Unicode harf (Türkçe karakterler dahil), /u = Unicode modu
    $hatalar['ad_soyad'] = 'Ad Soyad yalnızca harf ve boşluk içerebilir.';
}

// E-posta: Geçerli format
if (empty($email)) {
    $hatalar['email'] = 'E-posta alanı zorunludur.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $hatalar['email'] = 'Geçerli bir e-posta adresi girin.';
} elseif (mb_strlen($email) > 150) {
    $hatalar['email'] = 'E-posta adresi çok uzun.';
}

// Telefon: Opsiyonel ama girildiyse Türkçe format kontrolü
if (!empty($telefon)) {
    $sadece_rakam = preg_replace('/\D/', '', $telefon); // Sadece rakam bırak
    if (strlen($sadece_rakam) < 10 || strlen($sadece_rakam) > 15) {
        $hatalar['telefon'] = 'Geçerli bir telefon numarası girin (10-15 rakam).';
    }
}

// Konu: 3-100 karakter
if (empty($konu)) {
    $hatalar['konu'] = 'Konu alanı zorunludur.';
} elseif (mb_strlen($konu) < 3 || mb_strlen($konu) > 100) {
    $hatalar['konu'] = 'Konu 3-100 karakter olmalıdır.';
}

// Mesaj: 10-2000 karakter
if (empty($mesaj)) {
    $hatalar['mesaj'] = 'Mesaj alanı zorunludur.';
} elseif (mb_strlen($mesaj) < 10) {
    $hatalar['mesaj'] = 'Mesaj en az 10 karakter olmalıdır.';
} elseif (mb_strlen($mesaj) > 2000) {
    $hatalar['mesaj'] = 'Mesaj en fazla 2000 karakter olabilir.';
}

// Hata varsa döndür
if (!empty($hatalar)) {
    echo json_encode(['basarili' => false, 'hatalar' => $hatalar]);
    exit;
}

// ============================================================
// 4. Spam Kontrolü (Kara liste + Rate limiting)
// ============================================================

// 4a. Kara liste kontrolü (e-posta ve IP)
$stmt = $pdo->prepare(
    'SELECT COUNT(*) FROM spam_listesi WHERE deger = ? OR deger = ?'
);
$stmt->execute([$email, $ip]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['basarili' => false, 'mesaj' => 'Mesajınız gönderilemedi. Lütfen iletişime geçin.']);
    exit;
}

// 4b. Rate limiting: Aynı IP'den son 10 dakikada 3'ten fazla mesaj
$stmt = $pdo->prepare(
    'SELECT COUNT(*) FROM mesajlar
     WHERE ip_adresi = ? AND tarih > DATE_SUB(NOW(), INTERVAL 10 MINUTE)'
);
$stmt->execute([$ip]);
if ($stmt->fetchColumn() >= 3) {
    echo json_encode(['basarili' => false, 'mesaj' => 'Çok fazla mesaj gönderdiniz. Lütfen 10 dakika bekleyin.']);
    exit;
}

// ============================================================
// 5. Dosya Yükleme (Opsiyonel)
// ============================================================
$dosya_adi = null;

if (isset($_FILES['dosya']) && $_FILES['dosya']['error'] !== UPLOAD_ERR_NO_FILE) {
    $dosya   = $_FILES['dosya'];
    $izinli  = ['pdf','doc','docx','jpg','jpeg','png']; // İzin verilen uzantılar
    $max_boy = 5 * 1024 * 1024;                         // Maksimum 5 MB

    // Uzantıyı güvenli şekilde al (pathinfo kullan, $_FILES'daki isme güvenme)
    $uzanti = strtolower(pathinfo($dosya['name'], PATHINFO_EXTENSION));

    if ($dosya['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['basarili' => false, 'mesaj' => 'Dosya yüklenirken hata oluştu.']);
        exit;
    }
    if (!in_array($uzanti, $izinli)) {
        echo json_encode(['basarili' => false, 'mesaj' => 'İzin verilmeyen dosya türü.']);
        exit;
    }
    if ($dosya['size'] > $max_boy) {
        echo json_encode(['basarili' => false, 'mesaj' => 'Dosya boyutu 5 MB\'ı geçemez.']);
        exit;
    }

    // Güvenli ve benzersiz dosya adı oluştur
    $klasor    = 'yuklemeler/';
    if (!is_dir($klasor)) mkdir($klasor, 0755, true); // Klasörü oluştur
    $dosya_adi = uniqid('dosya_', true) . '.' . $uzanti;

    if (!move_uploaded_file($dosya['tmp_name'], $klasor . $dosya_adi)) {
        echo json_encode(['basarili' => false, 'mesaj' => 'Dosya kaydedilemedi.']);
        exit;
    }
}

// ============================================================
// 6. Veritabanına Kaydet (Prepared Statement — SQL Injection yok)
// ============================================================
try {
    $sql = '
        INSERT INTO mesajlar
            (ad_soyad, email, telefon, konu, mesaj, dosya_adi, ip_adresi, durum)
        VALUES
            (:ad_soyad, :email, :telefon, :konu, :mesaj, :dosya_adi, :ip_adresi, "yeni")
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':ad_soyad'  => $ad_soyad,
        ':email'     => $email,
        ':telefon'   => $telefon ?: null, // Boşsa NULL kaydet
        ':konu'      => $konu,
        ':mesaj'     => $mesaj,
        ':dosya_adi' => $dosya_adi,
        ':ip_adresi' => $ip,
    ]);

    $yeni_id = $pdo->lastInsertId(); // Eklenen kaydın ID'si

    // CSRF token'ı yenile (her başarılı gönderimden sonra değiştir)
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    echo json_encode([
        'basarili'  => true,
        'mesaj'     => 'Mesajınız başarıyla gönderildi! En kısa sürede size dönüş yapacağız.',
        'mesaj_id'  => $yeni_id,
        'yeni_csrf' => $_SESSION['csrf_token'],
    ]);

} catch (PDOException $e) {
    // Hata logla ama kullanıcıya teknik detay gösterme
    error_log('Mesaj kayıt hatası: ' . $e->getMessage());
    echo json_encode(['basarili' => false, 'mesaj' => 'Mesaj kaydedilirken bir hata oluştu.']);
}
