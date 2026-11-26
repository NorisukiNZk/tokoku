# Tokoku

Aplikasi web **toko komponen PC, simulasi rakit PC interaktif, dan bengkel servis komputer** berbasis Laravel 12, Filament v3, Livewire 3, dan Tailwind CSS v4.

## Tentang Proyek

Tokoku menyatukan seluruh alur kerja toko komputer modern dalam satu platform: pelanggan dapat mencari dan membandingkan stok part baru maupun *second* lalu langsung bertransaksi via WhatsApp/COD, menyusun spesifikasi PC dengan validasi kecocokan otomatis, serta mendaftar dan memantau progres servis secara transparan tanpa perlu membuat akun.

## Fitur Utama

- **Etalase & Bandingkan Barang (`/`)**: Sorotan unit pilihan, strip kategori cepat, filter kondisi (`Baru Segel`, `Second Mulus`, `Second Normal`, `Unit Minus`), daftar simpan favorit, perbandingan hingga 3 produk berdampingan, serta pesan otomatis ke WhatsApp toko.
- **Simulasi Rakit PC & Diagram Casing (`/rakit-pc`)**: Katalog referensi terpisah (`docyx/pc-part-dataset`, ribuan part dengan kurs USD ke IDR otomatis), diagram casing SVG interaktif, penilai kelas performa otomatis, cek kecocokan soket/RAM/VGA/PSU, paket cepat (*Hemat*, *Menengah*, *High-End*), simpan tautan (`RKT-...`), dan cetak PDF.
- **Booking & Lacak Servis (`/servis`)**: Pendaftaran jadwal antar atau jemput unit dengan nomor tiket otomatis (`SRV-...`) serta pelacakan progres 7 tahap berbasis tiket + 4 digit akhir WhatsApp (lengkap dengan laporan suhu awal/akhir `°C`, rincian biaya, dan log teknisi).
- **Multi-Panel Staf (`/admin` & `/teknisi`)**: Pusat Kendali Admin (kelola produk, stok cepat, katalog part, tiket servis, staf, dan profil toko) serta Papan Antrean Bengkel 4 kolom untuk teknisi.
- **Siap Dipasang di HP (PWA) & Mode Terang/Gelap**: Mendukung *Add to Home Screen* (`manifest.webmanifest`, *service worker*, halaman offline), *Bottom Navigation Bar* seluler, dan standar kontras WCAG AA.

## Teknologi

`PHP 8.2+` · `Laravel 12` · `Filament v3` · `Livewire 3` · `Alpine.js` · `Tailwind CSS v4` · `Vite` · `SQLite / MySQL` · `PHPUnit`

## Instalasi Cepat

Prasyarat: **PHP >= 8.2** (ekstensi `intl`, `zip`, `mbstring`, `curl`, `fileinfo`, `openssl`, `pdo_sqlite`/`pdo_mysql`), **Composer**, dan **Node.js / NPM**.

```bash
git clone https://github.com/NorisukiNZk/tokoku.git
cd tokoku
composer install && npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan tokoku:sync-parts
php artisan storage:link
npm run build
php artisan serve
```

> Secara bawaan proyek menggunakan SQLite sehingga langsung siap dijalankan di `http://127.0.0.1:8000`. Untuk MySQL, sesuaikan konfigurasi `DB_*` pada file `.env`.

## Akun & Data Uji Bawaan

| Akses / Fitur | URL | Kredensial / Kode Uji |
| :--- | :--- | :--- |
| **Panel Admin** | `/admin` | `admin@tokoku.local` / `admin123` (PIN Reset: `112233`) |
| **Panel Teknisi** | `/teknisi` | `teknisi@tokoku.local` / `teknisi123` (PIN Reset: `445566`) |
| **Cek Resi Servis** | `/servis` | Tiket `SRV-202611-0001` · PIN WA `1234` |
| **Contoh Rakitan PC** | `/rakit-pc?kode=RKT-CONTOH` | Kode `RKT-CONTOH` |

## Pengujian Otomatis

```bash
php artisan test
```
