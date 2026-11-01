# Deploy Smart Lab ke Server Kampus

Panduan ini mengasumsikan server **Linux (Ubuntu/Debian) + Nginx + PHP-FPM + MySQL/MariaDB**,
konfigurasi paling umum di server kampus. Catatan untuk server Windows ada di bagian akhir.

---

## 0. Yang perlu ditanyakan ke tim IT kampus

| Pertanyaan | Kenapa penting |
|---|---|
| OS server & akses yang diberikan (SSH? panel?) | Menentukan cara instalasi |
| **Nama domain** (mis. `smartlab.kampus.ac.id`) | QR di meja berisi URL ini — **cetak QR setelah domain final** |
| **Sertifikat HTTPS** | Scanner QR di dalam aplikasi butuh kamera → browser hanya mengizinkan di HTTPS |
| Apakah PC lab bisa menjangkau server (port 80/443) | Klien lock PC memanggil `/api/pc-status/{id}` tiap 5 detik |
| Database MySQL: dibuatkan, atau install sendiri? | Butuh 1 database + 1 user khusus |
| Kebijakan backup | Data booking & user harus dibackup rutin |

## 1. Kebutuhan server

- PHP **8.2+** dengan ekstensi: `bcmath ctype curl dom fileinfo gd intl mbstring openssl pdo_mysql tokenizer xml zip`
  (`gd`, `zip`, `xml` dipakai untuk import Excel). **Tanpa `zip`, import file `.xlsx` selalu gagal**
  (file `.csv` tetap bisa). Cek dengan `php -m | grep -E "zip|gd"`.
- Composer 2
- MySQL 8 / MariaDB 10.4+
- Node.js 20+ **hanya untuk build aset** (boleh build di laptop lalu upload folder `public/build`)
- Nginx (atau Apache) + PHP-FPM

## 2. Instalasi pertama

```bash
# 1. Ambil kode
cd /var/www
git clone <url-repo> smartlab
cd smartlab

# 2. Dependency PHP (tanpa paket development)
composer install --no-dev --optimize-autoloader

# 3. Aset frontend
npm ci && npm run build          # atau upload public/build hasil build di laptop
rm -f public/hot                 # file ini TIDAK boleh ada di produksi

# 4. Konfigurasi
cp .env.example .env
nano .env                        # isi APP_URL, DB_*, lihat bagian 3
php artisan key:generate

# 5. Database
php artisan migrate --force
php artisan db:seed --force      # membuat role admin/student/lecturer (aman diulang)
php artisan app:create-admin     # membuat akun admin pertama (interaktif)

# 6. File upload (gambar denah) & izin folder
php artisan storage:link
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 7. Cache untuk performa
php artisan optimize
```

## 3. Isi `.env` produksi

```env
APP_NAME="Smart Lab"
APP_ENV=production
APP_DEBUG=false                      # WAJIB false: kalau true, error menampilkan password DB
APP_URL=https://smartlab.kampus.ac.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartlab
DB_USERNAME=smartlab
DB_PASSWORD=<password-kuat>

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync                # aplikasi tidak memakai antrean, tidak perlu worker
LOG_STACK=daily
LOG_LEVEL=warning
```

Membuat database & user MySQL:
```sql
CREATE DATABASE smartlab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'smartlab'@'localhost' IDENTIFIED BY '<password-kuat>';
GRANT ALL PRIVILEGES ON smartlab.* TO 'smartlab'@'localhost';
FLUSH PRIVILEGES;
```

## 4. Nginx

`/etc/nginx/sites-available/smartlab`:
```nginx
server {
    listen 80;
    server_name smartlab.kampus.ac.id;
    root /var/www/smartlab/public;
    index index.php;
    client_max_body_size 10M;            # upload Excel & gambar denah

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;   # sesuaikan versi PHP
    }

    location ~ /\.(?!well-known) { deny all; }
}
```
```bash
sudo ln -s /etc/nginx/sites-available/smartlab /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

**HTTPS:** jika kampus tidak menyediakan sertifikat dan server bisa diakses internet:
`sudo certbot --nginx -d smartlab.kampus.ac.id`. Jika hanya intranet, minta sertifikat ke IT kampus.

## 5. Scheduler (wajib)

Membatalkan booking yang tidak check-in & menyelesaikan sesi yang habis, tiap menit:
```bash
sudo crontab -u www-data -e
# tambahkan:
* * * * * cd /var/www/smartlab && php artisan schedule:run >> /dev/null 2>&1
```
Cek: `php artisan schedule:list` harus menampilkan `booking:update-status`.

## 6. Backup harian

```bash
# /etc/cron.d/smartlab-backup
0 2 * * * root mysqldump smartlab | gzip > /var/backups/smartlab-$(date +\%F).sql.gz && find /var/backups -name 'smartlab-*.sql.gz' -mtime +14 -delete
```
Backup juga folder `storage/app/public` (gambar denah).

## 7. Mengisi data awal (lewat web, login sebagai admin)

1. **Cluster / Lab** → Admin → Cluster: buat lab, atur durasi min/maks, toleransi telat, upload denah.
2. **Unit PC** → Admin → Inventory: tambahkan PC per lab. **Nama unit harus unik.**
   Catat **ID** tiap PC → dipakai `pc_id` di klien lock PC.
3. **User** → Admin → Manajemen User → Import Excel (kolom: `nama_lengkap`, `nomor_induk`, `email`, `role`, `ambil_ta`).
   Password awal user hasil import = **nomor induknya** → minta mahasiswa menggantinya di menu Pengaturan Akun.
4. **QR meja** → Admin → Inventory → tombol QR tiap PC → cetak & tempel.
   QR berisi URL domain saat QR dibuat, jadi **cetak setelah domain & HTTPS final**.
   (Gambar QR dibuat oleh `api.qrserver.com`, jadi browser admin perlu internet saat membuka QR.)

## 8. Klien lock PC di lab

Ikuti `SETUP.md` di folder klien. Di `config.json`:
```json
"server_ip": "smartlab.kampus.ac.id",
"server_port": "80"
```
Jika server mengalihkan HTTP → HTTPS, klien otomatis mengikuti pengalihan.
Tes dari PC lab: buka `http://smartlab.kampus.ac.id/api/pc-status/<id>` → harus muncul JSON.

## 9. Update aplikasi di kemudian hari

```bash
cd /var/www/smartlab
php artisan down
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize
php artisan up
```

## 10. Checklist sebelum dipakai

- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] `https://domain/up` → halaman "Application up"
- [ ] Login admin berhasil, mahasiswa contoh bisa booking
- [ ] Scan QR di meja → check-in berhasil → PC terbuka (klien lock)
- [ ] `php artisan schedule:list` menampilkan `booking:update-status` dan cron aktif
- [ ] Gambar denah tampil (berarti `storage:link` benar)
- [ ] Halaman `/display` (layar publik) tampil
- [ ] Backup harian berjalan

---

### Jika server kampus Windows (XAMPP/Laragon)

- Arahkan DocumentRoot Apache ke folder `public`, aktifkan `mod_rewrite`.
- Di `C:\xampp\php\php.ini` hapus tanda `;` pada `extension=zip` dan `extension=gd`, lalu restart Apache.
- Langkah 2–3 sama (jalankan di Command Prompt). `storage:link` butuh CMD "Run as administrator".
- Scheduler: Task Scheduler → tiap 1 menit menjalankan
  `C:\xampp\php\php.exe C:\path\smartlab\artisan schedule:run`.
- Jangan pakai `php artisan serve` untuk produksi.
