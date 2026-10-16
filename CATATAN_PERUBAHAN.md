# Catatan Perubahan Mekanisme Booking dan PC

Catatan ini merangkum perubahan Step 1–5 untuk bahan pembaruan DPPL/SKPL.
Dokumen `.docx` tidak diubah.

## Step 1 — Sumber keputusan lock/unlock PC

- Keputusan `LOCK`/`UNLOCK` dipusatkan di `BookingService::getPcCommand`; booking aktif untuk satu PC maupun satu ruangan penuh diperhitungkan berdasarkan waktu server.
- Endpoint status PC mendelegasikan keputusan ke service, menyertakan `server_time`, dan mengembalikan `IGNORE` dengan HTTP 404 untuk PC yang tidak dikenal.
- Route `pc-specs/{id}` yang menunjuk method tidak tersedia dihapus.
- File: `app/Services/BookingService.php`, `app/Http/Controllers/Api/PcStatusController.php`, `routes/api.php`, `tests/Feature/PcStatusTest.php`.
- Perubahan skema: tidak ada.

## Step 2 — Check-in dan booking tanpa kehadiran

- Ditambahkan `clusters.early_checkin_minutes` (unsigned small integer, default 10) sebagai jendela check-in sebelum waktu mulai. `delay_time` tetap menjadi toleransi keterlambatan sebelum auto-cancel.
- Auto-cancel mengisi `cancel_reason` dengan `Terlambat Check-in`; pesan check-in menjelaskan kapan PC akan terbuka; penyelesaian manual hanya berlaku untuk booking aktif milik pengguna.
- Endpoint `POST /api/check-in` beserta method-nya dihapus setelah tidak ditemukan pemakaian di aplikasi/klien yang ditinjau.
- File: `app/Http/Controllers/Api/LabController.php`, `app/Http/Controllers/CheckInController.php`, `app/Models/Cluster.php`, `app/Services/BookingService.php`, `database/migrations/2026_10_17_000000_add_early_checkin_minutes_to_clusters_table.php`, `routes/api.php`, `tests/Feature/BookingExpiryTest.php`, `tests/Feature/CheckInTest.php`.

## Step 3 — Bentrok slot dan booking atomik

- Bentrok slot memakai interval setengah terbuka (`existing.start_time < requested.end_time` dan `existing.end_time > requested.start_time`), sehingga booking yang berurutan tepat pada batas waktu dapat dilakukan.
- Pemeriksaan ketersediaan dan pembuatan booking berjalan dalam transaksi dengan penguncian baris klaster serta, untuk booking satu PC, baris inventory. Booking satu ruangan tetap bentrok dengan booking PC di klaster yang sama, dan sebaliknya.
- Alur booking Livewire menggunakan `BookingService` yang sama.
- File: `app/Livewire/BookingPage.php`, `app/Services/BookingService.php`, `tests/Feature/BookingAvailabilityTest.php`.
- Perubahan skema: tidak ada.

## Step 4 — Klien Windows

- Klien menyelaraskan timer dengan `server_time`, menghitung mundur dari waktu server terakhir yang diketahui saat offline, lalu mengunci PC ketika sesi berakhir atau saat tidak memiliki status UNLOCK yang valid.
- Pemanggilan lock dilakukan saat transisi status; overlay menampilkan kondisi offline dan peringatan untuk menyimpan pekerjaan ketika sisa waktu paling banyak lima menit.
- Logika pemilihan aksi dipisahkan ke `decide_action` dan diuji dengan lima skenario.
- File eksternal (di luar repository): `C:\Users\M S I\Documents\Kuliah\Kerja Praktik\SmartLab_Client\SmartLab_Client\smart_lab_client.py`, `C:\Users\M S I\Documents\Kuliah\Kerja Praktik\SmartLab_Client\SmartLab_Client\test_smart_lab_client.py`.
- Perubahan skema: tidak ada. Berkas `.exe` tidak dibangun; untuk membangunnya ulang jalankan `pyinstaller --clean --noconfirm smart_lab_client.spec` dari folder klien.

## Step 5 — Heartbeat dan pantauan admin

- Ditambahkan `inventory.last_seen_at` (timestamp nullable). Endpoint status memperbarui heartbeat hanya jika belum pernah tercatat atau catatan terakhir lebih lama dari 30 detik; pembaruan bersyarat di database juga mencegah polling serentak menulis ulang timestamp yang masih baru.
- Daftar PC pada halaman kelola lab menampilkan lencana Online/Offline dan waktu terakhir terlihat; daftar diperbarui otomatis setiap 30 detik.
- File: `app/Http/Controllers/Api/PcStatusController.php`, `app/Models/Inventory.php`, `database/migrations/2026_10_17_000001_add_last_seen_at_to_inventory_table.php`, `resources/views/livewire/admin/manage-lab.blade.php`, `tests/Feature/PcStatusTest.php`, `CATATAN_PERUBAHAN.md`.

## Baseline suite penuh yang sudah ada

Suite penuh tetap menunjukkan 23 kegagalan yang sama seperti baseline sebelum Step 5; tidak ada kegagalan baru dari perubahan Step 1–5. Akar kegagalan yang tercatat adalah skema tabel `users` pada SQLite pengujian tidak memiliki kolom `email_verified_at`, sedangkan beberapa tes auth/profil dan factory menggunakannya. Satu tes halaman reset password juga mengharapkan halaman yang saat ini merespons 404.

Rincian baseline:

- `AuthenticationTest`: 3 kegagalan.
- `EmailVerificationTest`: 3 kegagalan.
- `PasswordConfirmationTest`: 3 kegagalan.
- `PasswordResetTest`: 4 kegagalan.
- `PasswordUpdateTest`: 2 kegagalan.
- `RegistrationTest`: 2 kegagalan.
- `ExampleTest`: 1 kegagalan.
- `ProfileTest`: 5 kegagalan.

Skema `users` sengaja tidak diubah sebagai bagian dari pekerjaan ini.
