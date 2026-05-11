<?php
// ============================================================
// includes/baglan.php  — Veritabanı Bağlantısı
// ============================================================

// --- Bağlantı Ayarları ---
define('DB_HOST',     'localhost');   // XAMPP varsayılan sunucu
define('DB_KULLANICI','root');        // XAMPP varsayılan kullanıcı
define('DB_SIFRE',    '');           // XAMPP varsayılan şifre (boş)
define('DB_ADI',      'iletisim_db');// Veritabanı adı
define('DB_PORT',     3306);         // MySQL port

// --- PDO ile Bağlantı Kur ---
// PDO kullanmak prepared statements ile SQL Injection'ı tamamen önler
function baglantiKur(): PDO {
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        DB_HOST, DB_PORT, DB_ADI
    );

    $ayarlar = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Hataları exception olarak fırlat
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Veriyi ilişkisel dizi olarak getir
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Gerçek prepared statement kullan
    ];

    try {
        $pdo = new PDO($dsn, DB_KULLANICI, DB_SIFRE, $ayarlar);
        return $pdo;
    } catch (PDOException $e) {
        // Üretim ortamında hata detayını kullanıcıya gösterme!
        error_log('DB Bağlantı Hatası: ' . $e->getMessage());
        die(json_encode([
            'basarili' => false,
            'mesaj'    => 'Veritabanına bağlanılamadı. Lütfen daha sonra tekrar deneyin.'
        ]));
    }
}

// Global bağlantı nesnesi — tüm dosyalar bu değişkeni kullanır
$pdo = baglantiKur();
