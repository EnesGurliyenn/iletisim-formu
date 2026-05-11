-- ============================================================
-- İleri Seviye İletişim Formu - Veritabanı Kurulum Dosyası
-- XAMPP'te phpMyAdmin üzerinden çalıştırın
-- ============================================================

-- Veritabanını oluştur (yoksa)
CREATE DATABASE IF NOT EXISTS iletisim_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Veritabanını seç
USE iletisim_db;

-- ============================================================
-- Mesajlar tablosu
-- ============================================================
CREATE TABLE IF NOT EXISTS mesajlar (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad      VARCHAR(100)  NOT NULL,
    email         VARCHAR(150)  NOT NULL,
    telefon       VARCHAR(20)   DEFAULT NULL,
    konu          VARCHAR(100)  NOT NULL,
    mesaj         TEXT          NOT NULL,
    dosya_adi     VARCHAR(255)  DEFAULT NULL,  -- Opsiyonel ek dosya
    ip_adresi     VARCHAR(45)   DEFAULT NULL,  -- IPv6 için 45 karakter
    durum         ENUM('yeni','okundu','cevaplandi','spam') DEFAULT 'yeni',
    tarih         DATETIME      DEFAULT CURRENT_TIMESTAMP,
    guncelleme    DATETIME      ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Spam kara listesi tablosu (ileri seviye özellik)
-- ============================================================
CREATE TABLE IF NOT EXISTS spam_listesi (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    deger    VARCHAR(150) NOT NULL,   -- E-posta veya IP
    tip      ENUM('email','ip') NOT NULL,
    tarih    DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_deger (deger)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Örnek spam girişleri (test amaçlı)
-- ============================================================
INSERT INTO spam_listesi (deger, tip) VALUES
('spammer@example.com', 'email'),
('192.168.1.999',       'ip');

-- ============================================================
-- Test mesajı (isteğe bağlı)
-- ============================================================
INSERT INTO mesajlar (ad_soyad, email, telefon, konu, mesaj, ip_adresi, durum)
VALUES (
  'Test Kullanıcı',
  'test@example.com',
  '0555 000 00 00',
  'Test Mesajı',
  'Bu bir test mesajıdır. Sistem düzgün çalışıyor!',
  '127.0.0.1',
  'okundu'
);

SELECT 'Veritabanı başarıyla kuruldu!' AS Sonuc;
