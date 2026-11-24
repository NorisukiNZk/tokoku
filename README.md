# Tokoku: Marketplace Part PC, Simulasi Rakit, & Bengkel Servis

Aplikasi web terpadu untuk toko perangkat keras komputer yang menggabungkan etalase barang baru maupun *second* bergaya marketplace harian, meja simulasi perakitan PC dengan validasi kecocokan otomatis, serta layanan pendaftaran dan pelacakan progres servis PC/laptop secara transparan.

## Fitur Utama

1. **Etalase Barang Baru & Second (`/`)**
   - Pencarian instan serta penyaringan berdasarkan kategori, kondisi fisik (`Baru Segel`, `Second Mulus`, `Second Normal`, `Unit Minus`), ketersediaan stok, dan status negosiasi harga.
   - Halaman detail barang dilengkapi keterangan garansi, catatan kondisi fisik, spesifikasi teknis, dan tombol pesan langsung ke WhatsApp toko dengan teks produk terisi otomatis.

2. **Simulasi Rakit PC Berbasis API Ribuan Part (`/rakit-pc`)**
   - Menggunakan database katalog referensi tersendiri (`pc_catalog_parts`) yang disinkronkan dari dataset terbuka `docyx/pc-part-dataset` (10.000+ part mulai kelas *entry-level* paling murah hingga *flagship* beserta konversi kurs USD ke Rupiah otomatis), terpisah dari stok fisik etalase.
   - Validasi otomatis kesesuaian soket prosesor dan motherboard (`AM4`, `AM5`, `LGA1700`, `LGA1851`), generasi memori (`DDR3`/`DDR4`/`DDR5`), ukuran motherboard terhadap casing, batas panjang kartu grafis (`mm`), serta perhitungan daya PSU (`Watt`).
   - Dilengkapi penyusunan paket otomatis (*Paket Hemat*, *Menengah*, *High-End*), filter kelas harga, simpan tautan rakitan (`RKT-...`), cetak PDF, dan konsultasi pemesanan ke WhatsApp.

3. **Booking & Lacak Tiket Servis (`/servis`)**
   - Formulir pendaftaran jadwal servis (*antar ke toko* atau *jemput unit*) yang langsung menerbitkan nomor tiket resmi (`SRV-...`).
   - Pelacakan status pengerjaan 7 tahap menggunakan nomor tiket dan 4 digit akhir nomor WhatsApp pelanggan tanpa perlu registrasi akun, lengkap dengan laporan suhu awal/akhir (`°C`), rincian biaya sparepart, dan log catatan teknisi.

4. **Multi-Panel Staf dengan Filament (`/admin` & `/teknisi`)**
   - **Panel Admin (`/admin`)**: Kelola stok produk & komponen rakitan, kategori, daftar paket servis, simulasi rakitan tersimpan, akun staf, serta pengaturan kontak & identitas toko.
   - **Panel Teknisi (`/teknisi`)**: Ruang kerja khusus teknisi untuk memproses antrean servis, mencatat diagnosa dan suhu pengujian, menambahkan pemakaian sparepart, serta memperbarui log progres pelanggan.

5. **Siap Dipasang di HP (PWA + Navigasi Bawah Seluler) & Mode Terang/Gelap**
   - Dilengkapi `manifest.webmanifest`, *service worker* (`/sw.js`), halaman cadangan offline (`/offline.html`), ikon aplikasi Android & iPhone, tombol **Pasang di HP**, serta *Bottom Navigation Bar* khusus layar seluler dengan tipografi `Inter` dan kontras aksesibilitas WCAG AA.

## Teknologi yang Digunakan

- **Backend**: PHP 8.2+, Laravel 12
- **Panel Operasional**: Filament v3 (Multi-Panel)
- **Antarmuka Publik**: Livewire 3, Alpine.js, Tailwind CSS v4, Vite
- **Database**: SQLite (bawaan) atau MySQL / MariaDB
- **Pengujian**: PHPUnit (Unit & Feature Test)

## Isi & Struktur Direktori Inti

```text
tokoku/
├── app/
│   ├── Filament/
│   │   ├── Pages/              # Halaman pengaturan identitas & kontak toko
│   │   ├── Resources/          # CRUD Produk, Kategori, Tiket Servis, Paket, & Staf
│   │   └── Widgets/            # Ringkasan metrik operasional bengkel & stok
│   ├── Livewire/               # Komponen halaman publik (Etalase, Detail, Rakit PC, Servis)
│   ├── Models/                 # Model Eloquent & relasi database
│   ├── Providers/Filament/     # Konfigurasi panel /admin dan /teknisi
│   └── Services/               # PcCompatibilityService (logika validasi kecocokan part)
├── database/
│   ├── migrations/             # Skema tabel pengguna, katalog, servis, & rakitan PC
│   └── seeders/                # Data contoh awal komponen, paket servis, & akun demo
├── resources/
│   ├── css/app.css             # Variabel tema Terang/Gelap & konfigurasi Tailwind v4
│   └── views/                  # Layout utama & tampilan Blade Livewire
├── routes/web.php              # Rute halaman publik pengunjung
└── tests/                      # Pengujian otomatis mesin kompatibilitas & alur fitur
```

## Cara Instalasi

### Prasyarat
- PHP >= 8.2 (ekstensi `intl`, `zip`, `mbstring`, `curl`, `fileinfo`, `openssl`, dan `pdo_sqlite` atau `pdo_mysql` aktif)
- Composer
- Node.js & NPM

### Langkah Pemasangan

1. **Salin repositori dan masuk ke direktori proyek**
   ```bash
   git clone https://github.com/NorisukiNZk/tokoku.git
   cd tokoku
   ```

2. **Instal paket PHP dan JavaScript**
   ```bash
   composer install
   npm install
   ```

3. **Siapkan file konfigurasi dan kunci aplikasi**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *(Secara bawaan proyek menggunakan SQLite sehingga siap berjalan langsung. Jika ingin memakai MySQL, sesuaikan `DB_CONNECTION` dan nama database di dalam `.env`).*

4. **Jalankan migrasi database, data awal, sinkronisasi ribuan part PC dari API, dan tautan gambar**
   ```bash
   php artisan migrate --seed
   php artisan tokoku:sync-parts
   php artisan storage:link
   ```

5. **Kompilasi aset tampilan dan jalankan server lokal**
   ```bash
   npm run build
   php artisan serve
   ```
   Buka `http://127.0.0.1:8000` di browser Anda.

## Akun & Data Uji Bawaan (Seeder)

| Akses / Fitur | Alamat URL | Kredensial / Kode Uji |
| :--- | :--- | :--- |
| **Panel Admin** | `/admin` | `admin@tokoku.local` / `admin123` |
| **Panel Teknisi** | `/teknisi` | `teknisi@tokoku.local` / `teknisi123` |
| **Cek Resi Servis** | `/servis` | Tiket: `SRV-202611-0001` / 4 Digit WA: `1234` |
| **Contoh Rakitan PC** | `/rakit-pc?kode=RKT-CONTOH` | Kode: `RKT-CONTOH` |

## Menjalankan Pengujian Otomatis

```bash
php artisan test
```
