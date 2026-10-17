# AUDIT UI SmartLab (Fase 1)

## Ringkasan verifikasi

| Lokasi | Viewport | h-screen / 100vh | Fixed / sticky | Tabel / modal | Status |
| --- | --- | --- | --- | --- | --- |
| `resources/views/layouts/booking.blade.php` | `viewport-fit=cover` aktif; body tag rusak (`x-data` tertelan ke class) | `h-screen w-screen` di body | Tidak ada | Tidak ada modal di layout | Terverifikasi benar; perlu diperbaiki |
| `resources/views/layouts/app.blade.php` | Standar `width=device-width` | `h-screen` pada wrapper | Tidak ada | Tidak ada | Aman, tetapi perlu konsistensi tinggi layar |
| `resources/views/layouts/admin.blade.php` | Standar `width=device-width` | `h-screen` di inner shell | Sidebar mobile fixed/translate | Modal dan overlay ada di halaman child | Diperlukan pemeriksaan mobile di Fase 2 |
| `resources/views/components/layouts/app.blade.php` | `width=device-width` | Tanpa `h-screen` utama | Tidak ada | Tidak ada | Menggunakan CDN Tailwind, bukan konfigurasi proyek |
| `resources/views/components/layouts/admin.blade.php` | Standar `width=device-width` | `h-screen` di layout admin | Sidebar mobile fixed | Overlay sidebar | Butuh konsolidasi dengan head partial |
| `resources/css/app.css` | N/A | N/A | N/A | Global `table` + `th:nth-child(1)` mematikan kolom pertama di mobile | Terverifikasi benar; dihapus |
| `tailwind.config.js` | N/A | N/A | N/A | Konfigurasi tailwind | Terbaca dengan benar; tidak perlu upgrade/downgrade |

## Verifikasi temuan awal

1. Header booking bertabrakan dengan status bar iPhone: benar ada `viewport-fit=cover` dan `pt-[calc(env(safe-area-inset-top,0px)+0.75rem)]` pada header booking (di markup Livewire/booking/header). Karena di layout juga ada `viewport-fit=cover`, potensi overlap memang ada.
2. Body booking memakai `h-screen w-screen overflow-hidden`: benar ada di `resources/views/layouts/booking.blade.php`.
3. Tag body di layout booking cacat: benar, atribut `class` tertutup sebelum `x-data` karena quote yang hilang.
4. CSS global berbahaya di `resources/css/app.css`: benar ada `table { display: block; ... }` dan `th:nth-child(1), td:nth-child(1) { display:none; }`.
5. Meta viewport tidak konsisten antar layout: benar; beberapa layout memakai `width=device-width, initial-scale=1`, beberapa masih memakai `viewport-fit=cover` atau apapun.
6. Versi/konfigurasi Tailwind: `npm ls tailwindcss --depth=0` menghasilkan `tailwindcss@3.4.19`. `tailwind.config.js` terbaca saat build, jadi tidak perlu merubah versi. `@tailwindcss/vite` ada di `package.json`, tetapi Vite config aktif saat ini tidak menggunakannya.

## Keputusan fondasi Fase 1

- Gunakan head partial bersama untuk meta + font + Vite.
- Hapus kebijakan global `viewport-fit=cover` dari layout default agar konsisten dan aman di semua perangkat.
- Tambahkan utilitas `pt-safe`, `pb-safe`, dan `h-screen-dvh` dengan fallback `height: 100vh` untuk kompatibilitas.
- Hapus aturan global tabel yang secara otomatis menyembunyikan kolom pertama.
- Tetap pada Tailwind 3.4.19 karena konfigurasi dan build aktif sudah valid.
