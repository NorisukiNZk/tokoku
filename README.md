# Tokoku: Marketplace Part PC, Simulasi Rakit, & Bengkel Servis

Aplikasi web terpadu untuk toko perangkat keras komputer yang menggabungkan etalase barang baru maupun *second* bergaya marketplace harian, meja simulasi perakitan PC dengan validasi kecocokan otomatis, serta layanan pendaftaran dan pelacakan progres servis PC/laptop secara transparan.

## Fitur Utama

1. **Etalase Barang Baru & Second (`/`)**
   - Pencarian instan serta penyaringan berdasarkan kategori, kondisi fisik (`Baru Segel`, `Second Mulus`, `Second Normal`, `Unit Minus`), ketersediaan stok, dan status negosiasi harga.
   - Halaman detail barang dilengkapi keterangan garansi, catatan kondisi fisik, spesifikasi teknis, dan tombol pesan langsung ke WhatsApp toko dengan teks produk terisi otomatis.

2. **Simulasi Rakit PC & Mesin Kompatibilitas (`/rakit-pc`)**
   - Pemilihan komponen bertahap dari stok toko (mendukung kombinasi komponen baru dan *second*).
   - Validasi otomatis kesesuaian soket prosesor dan motherboard (`AM4`, `AM5`, `LGA1700`), generasi memori (`DDR4`/`DDR5`), ukuran motherboard terhadap casing (`ATX`, `mATX`, `ITX`), batas panjang kartu grafis (`mm`), serta perhitungan daya dan rekomendasi kapasitas PSU (`Watt`).
   - Fitur simpan kode tautan rakitan (`RKT-...`), cetak estimasi PDF, dan kirim rincian spesifikasi ke WhatsApp.

3. **Booking & Lacak Tiket Servis (`/servis`)**
   - Formulir pendaftaran jadwal servis (*antar ke toko* atau *jemput unit*) yang langsung menerbitkan nomor tiket resmi (`SRV-...`).
   - Pelacakan status pengerjaan 7 tahap menggunakan nomor tiket dan 4 digit akhir nomor WhatsApp pelanggan tanpa perlu registrasi akun, lengkap dengan laporan suhu awal/akhir (`°C`), rincian biaya sparepart, dan log catatan teknisi.

4. **Multi-Panel Staf dengan Filament (`/admin` & `/teknisi`)**
   - **Panel Admin (`/admin`)**: Kelola stok produk & komponen rakitan, kategori, daftar paket servis, simulasi rakitan tersimpan, akun staf, serta pengaturan kontak & identitas toko.
   - **Panel Teknisi (`/teknisi`)**: Ruang kerja khusus teknisi untuk memproses antrean servis, mencatat diagnosa dan suhu pengujian, menambahkan pemakaian sparepart, serta memperbarui log progres pelanggan.

5. **Antarmuka Responsif (Mode Terang & Gelap)**
   - Menggunakan tipografi `Inter` dan sistem warna berstandar kontras aksesibilitas WCAG AA dengan pengalih tema Terang dan Gelap.

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

4. **Jalankan migrasi database, data contoh awal, dan tautan penyimpanan gambar**
   ```bash
   php artisan migrate --seed
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
