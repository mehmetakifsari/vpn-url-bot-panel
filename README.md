# VPN URL Bot Paneli

PHP tabanlı **kontrol paneli** ve Python tabanlı **VPN destekli bot** sistemidir.  
Amaç: Belirli URL adreslerini farklı VPN lokasyonları üzerinden otomatik ziyaret etmek ve YouTube gibi sitelerde buton tıklama işlemlerini yönetmektir.

---

## 🚀 Özellikler

- 🌍 **VPN Desteği**  
  - Proxy yerine **OpenVPN** profilleri ile çalışır.  
  - Amerika, İngiltere vb. ülke lokasyonları seçilebilir.
- 🖥️ **Web Panel (PHP)**  
  - URL listesi ekleme/düzenleme  
  - VPN ülkelerini seçme (checkbox + “Tümünü Seç” özelliği)  
  - Zaman (dakika) ayarlama  
  - Başlat/Durdur kontrolü  
  - Canlı log görüntüleme (bot / YouTube / VPN logları)  
  - **Tamamlanan URL’ler** tablosu
- 🤖 **Python Bot**  
  - `yt_clicker.py` ile YouTube sayfalarında **play butonunu otomatik tıklar**  
  - Seçili VPN üzerinden URL listesi sırayla çalıştırılır  
  - Her VPN bitince sıradaki VPN ile devam eder
- 🎨 **Responsive Arayüz**  
  - Mobil, tablet ve PC uyumlu  
  - **Karanlık / Açık mod** desteği  
- 📝 **Loglama**  
  - `completed_urls.txt` → Hangi URL hangi VPN’de tamamlandı  
  - `log_view` alanı ile panelde izlenebilir

---

## 📂 Proje Yapısı

```
proje8/
│
├── assets/
│   ├── ui.css          # Ortak stil dosyası (dark/light tema + responsive)
│   └── theme.js        # Tema değiştirici script
│
├── login.php           # Giriş ekranı
├── logout.php          # Çıkış
├── bot_panel.php       # Ana kontrol paneli
├── run_bot.php         # Bot başlat/durdur/status AJAX endpoint
├── yt_clicker.py       # YouTube clicker botu
├── vpn_job.py          # VPN + URL yürütme botu
├── vpnctl.sh           # OpenVPN bağlantı yönetici (başlat/durdur)
│
├── url.txt             # Kullanıcı tarafından girilen URL listesi
├── vpn_map.json        # Ülke → OVPN dosya yolu eşlemesi
├── selected_countries.json # Panelden seçilen ülkeler
├── completed_urls.txt  # Tamamlanan URL kayıtları
└── db.php / auth.php   # Kullanıcı doğrulama
```

---

## ⚙️ Kurulum

### 1. Gereksinimler
- Ubuntu 20.04+  
- PHP 8.1+ + Apache/Nginx  
- Python 3.10+  
- [Playwright](https://playwright.dev/python/) (tarayıcı otomasyonu için)  
- `openvpn` paketi

### 2. OpenVPN Profilleri
`vpn_map.json` içine ülkelerin `.ovpn` dosyalarının yolunu tanımlayın.  
Her ülke için ayrıca `auth.txt` (username/password) dosyası hazırlanmalı.

Örnek:
```json
{
  "Amerika": "/etc/openvpn/ovpn/usa.ovpn",
  "İngiltere": "/etc/openvpn/ovpn/uk.ovpn"
}
```

### 3. Panel
```bash
cd /var/www/html/proje8
composer install   # (gerekirse bağımlılıklar)
```
Ardından `login.php` üzerinden giriş yapın.  

---

## ▶️ Kullanım

1. **URL listesi** girin (`url.txt` içine yazılır).  
2. **VPN ülkelerini** seçin (panel üzerinden).  
3. **Süreyi** belirleyin (her URL için dakika).  
4. **Başlat** butonuna tıklayın.  
5. Logları ve tamamlanan URL’leri panelden takip edin.

---

## 📸 Ekran Görüntüsü

> Buraya proje ekran görüntülerini ekleyebilirsin (`/screenshots` klasörü açıp resimleri koyabilirsin).

---

## 🛠️ Geliştirme

- `ui.css` → tema ve responsive görünüm  
- `bot_panel.php` → panel düzeni  
- `vpn_job.py` → VPN sıralı URL çalıştırma  
- `yt_clicker.py` → YouTube play click

---

## 📜 Lisans

MIT License.  
Bu proje yalnızca **eğitim ve deneme** amaçlıdır.  
Kötüye kullanım sorumluluğu tamamen kullanıcıya aittir.
