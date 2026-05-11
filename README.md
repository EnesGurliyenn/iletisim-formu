# 📬 İleri Seviye PHP İletişim Formu
 
> Enes Gürliyen & Abdulsamet Dişlioğlu

---

## 🚀 Proje Hakkında

Bu proje, PHP ve MySQL kullanılarak geliştirilmiş **ileri seviye bir iletişim formu** web uygulamasıdır. XAMPP ortamında çalışmak üzere tasarlanmıştır.

---

## ✨ Özellikler

- 🛡️ **CSRF Koruması** — Sahte form gönderimlerini engeller
- 🔒 **SQL Injection Önleme** — PDO Prepared Statements kullanılır
- 🚫 **Rate Limiting** — Aynı IP'den 10 dk'da max 3 mesaj
- 📋 **Spam Kara Listesi** — E-posta ve IP bazlı engelleme
- 📎 **Dosya Yükleme** — PDF, DOC, JPG, PNG (max 5 MB)
- ⚡ **AJAX Gönderim** — Sayfa yenilemeden asenkron işlem
- ✅ **Çift Katmanlı Doğrulama** — Hem JS hem PHP tarafında
- 📊 **Admin Paneli** — Mesaj listeleme, filtreleme, durum güncelleme

---

## 📁 Dosya Yapısı

```
iletisim_formu/
├── index.php              # Ana form sayfası
├── form_isle.php          # Form işleme backend
├── admin.php              # Yönetim paneli
├── veritabani_kur.sql     # Veritabanı kurulum dosyası
└── includes/
    └── baglan.php         # PDO veritabanı bağlantısı
```

---

## 🛠️ Kullanılan Teknolojiler

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=flat&logo=javascript&logoColor=black)
![XAMPP](https://img.shields.io/badge/XAMPP-FB7A24?style=flat&logo=xampp&logoColor=white)

---

## ⚙️ Kurulum

### Gereksinimler
- XAMPP (Apache + MySQL)
- PHP 7.4 veya üzeri

### Adımlar

**1. Repoyu klonla:**
```bash
git clone https://github.com/KULLANICI_ADIN/iletisim-formu.git
```

**2. Klasörü XAMPP'e taşı:**
```
C:\xampp\htdocs\iletisim_formu\
```

**3. XAMPP'te Apache ve MySQL'i başlat**

**4. Veritabanını kur:**
- `http://localhost/phpmyadmin` adresine git
- **SQL** sekmesine tıkla
- `veritabani_kur.sql` içeriğini yapıştır → **Git**

**5. Uygulamayı aç:**
```
http://localhost/iletisim_formu/
```

---

## 📸 Ekran Görüntüleri

| Form Sayfası | Admin Paneli |
|---|---|
| `index.php` | `admin.php` |

---

## 🔐 Güvenlik Katmanları

| Katman | Yöntem |
|---|---|
| CSRF | `bin2hex(random_bytes(32))` ile token üretimi |
| SQL Injection | PDO `prepare()` + `execute()` |
| XSS | `htmlspecialchars()` ile çıktı temizleme |
| Rate Limit | IP başına 10 dk'da 3 istek sınırı |
| Dosya Güvenliği | `pathinfo()` ile uzantı kontrolü, `uniqid()` ile yeniden adlandırma |

---

## 👥 Geliştiriciler

- **Enes Gürliyen**
- **Abdulsamet Dişlioğlu**

---

## 📄 Lisans

Bu proje eğitim amaçlı geliştirilmiştir.