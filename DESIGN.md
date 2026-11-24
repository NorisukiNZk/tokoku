# DESIGN.md: Panduan Identitas Visual Tokoku (Edisi Ramah Pengguna)

## Identitas & Arah Pengalaman Pengguna (UX)
- **Nama Produk**: Tokoku (penanda merek teks bersih hingga pemilik mengunggah logo resmi di Panel Admin).
- **Tujuan Rombak UX**: Menghilangkan kesan kaku dan melelahkan mata akibat kode teknis berlebih. Antarmuka dirancang akrab bagi pembeli umum layaknya menjelajah Facebook Marketplace: bersih, ruang kosong lega, istilah mudah dimengerti orang awam, dan tidak menumpuk terlalu banyak kotak dalam satu pandangan.

## Tipografi Ramah Mata (Tanpa Monospace Berlebih)
- **Huruf Tunggal Utama (`Inter`)**: Dipilih khusus karena bukaan huruf lebar, tinggi huruf kecil (*x-height*) proporsional, dan paling nyaman dibaca lama di layar ponsel maupun laptop tanpa membuat mata cepat lelah.
- **Aturan Keterbacaan**:
  - Gunakan penulisan kalimat wajar (*Sentence case* atau *Title Case*), hindari huruf kapital semua (*ALL CAPS*) pada label dan tombol.
  - Jarak antarbaris (`line-height`) longgar (`1.6` untuk paragraf) dan angka harga sejajar (`tabular-nums`).
  - Huruf kode (`monospace`) tidak lagi dipakai pada label kondisi atau spesifikasi barang; hanya dipakai tipis bila menampilkan nomor tiket resi yang perlu disalin.

## Sistem Warna Tenang & Ramah (2 Netral Bersih + 1 Aksen Biru Marketplace)
Seluruh pasangan warna diuji memenuhi kontras WCAG AA di Mode Terang maupun Mode Gelap.

### Mode Terang (Default)
- **Latar Halaman**: `#F4F6F9` (abu-biru sangat lembut agar kartu putih tampak bersih tanpa garis tepi kasar)
- **Permukaan Kartu**: `#FFFFFF` dengan garis tepi halus `#DCE3EC`
- **Permukaan Lembut**: `#EEF2F7`
- **Teks Utama**: `#0F172A` (kontras > 16:1)
- **Teks Sekunder**: `#475569` (kontras > 7.5:1 di atas putih)
- **Aksen Utama (Biru Marketplace)**: `#1D4ED8` dengan teks `#FFFFFF` (kontras 6.70:1, nyaman di mata dan menumbuhkan rasa percaya)
- **Aksen Lembut (Tab/Pilihan Aktif)**: `#EFF6FF` dengan teks `#1E40AF` dan tepi `#93C5FD`

### Mode Gelap
- **Latar Halaman**: `#0F172A` (biru-abu malam yang teduh, bukan hitam pekat yang menyilaukan teks)
- **Permukaan Kartu**: `#1E293B` dengan garis tepi `#334155`
- **Permukaan Lembut**: `#273549`
- **Teks Utama**: `#F8FAFC` (kontras > 13:1)
- **Teks Sekunder**: `#CBD5E1` (kontras > 8.5:1)
- **Aksen Utama**: `#60A5FA` dengan teks gelap `#0F172A` (kontras > 8.1:1)
- **Aksen Lembut**: `#1E3A5F` dengan teks `#BFDBFE` dan tepi `#3B82F6`

## Tata Letak & Dials
- `Dial: ENERGY 2 / RHYTHM 2 / MOTION 2`
- **Alasan Gerak (`MOTION 2`)**: Seluruh gerak bersifat fungsional (bukan hiasan berulang): perpindahan antarhalaman menggunakan `wire:navigate` untuk menghilangkan kedip layar putih; dialog, laci barang tersimpan, dan *bottom sheet* seluler menggunakan transisi buka-tutup halus 180ms untuk menjaga arah spasial; *skeleton card* muncul hanya saat Livewire sedang menyaring katalog; dan seluruh transisi menghormati `prefers-reduced-motion: reduce`.
- **Etalase (`/`)**: Tata letak dua kolom ala Facebook Marketplace (Sidebar filter di kiri pada layar lebar, panel filter lipat di ponsel agar foto barang langsung terlihat di atas) dan kartu produk bersih berfokus pada foto, harga, serta 1 baris info inti.
- **Rakit PC (`/rakit-pc`)**: Alur bertahap bernomor dengan bilah kelengkapan komponen dan pilihan paket awal sekali klik bagi pemula.
- **Servis (`/servis`)**: Terbagi rapi menjadi 2 tab fokus (*Booking & Kisaran Biaya* serta *Lacak Status Servis*) sehingga halaman tidak memanjang berlebihan.
- **Motif Identitas**: Kartu putih bersudut lembut (`rounded-xl` pada kartu utama, `rounded-lg` pada tombol & input, `rounded-md` pada label kondisi) dengan penanda kondisi warna tenang dalam bahasa sehari-hari (`Baru Segel`, `Second Mulus`, `Second Normal`, `Unit Minus`).
