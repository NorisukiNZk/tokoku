# DESIGN.md: Panduan Identitas Visual Tokoku (Edisi Hardware Storefront Berkarakter)

## Identitas & Arah Visual
- **Nama Produk**: Tokoku (toko komponen PC baru & second bergaransi, simulasi rakit PC interaktif, dan bengkel servis terukur).
- **Kepribadian Visual**: Memecah kesan datar serba putih melalui kontras terarah antara **Blok Sorotan Arang Gelap (`#0F172A`)** dengan **Katalog Putih Bersih (`#FFFFFF`)** dan **Aksen Oranye Teknis (`#C2410C` terang / `#FB923C` gelap)** yang identik dengan bengkel perangkat keras profesional.

## Tipografi Berpasangan (Tegas di Judul, Ringan di Isi)
- **Display & Harga (`Plus Jakarta Sans` 700/800)**: Memberikan karakter modern dan ketegasan pada judul utama, sorotan unit, dan angka harga sejajar (`tabular-nums`).
- **Teks Bacaan & Label (`Inter` 400/500/600)**: Menjaga keterbacaan panjang yang nyaman di mata pada deskripsi barang, filter, dan formulir.

## Sistem Warna (2 Netral Kontras + 1 Aksen Oranye Teknis)
Seluruh pasangan teks dan batas komponen lulus kontras minimum WCAG AA di Mode Terang dan Mode Gelap.

### Mode Terang (Default)
- **Kanvas**: `#F5F6F8`
- **Permukaan Kartu**: `#FFFFFF` (tepi `#DDE2EA`)
- **Blok Kontras Editorial (`surface-ink`)**: `#0F172A` dengan teks `#F8FAFC` dan kartu dalam `#1E293B` (memecah monotoni halaman katalog)
- **Teks Utama**: `#0F172A` | **Teks Sekunder**: `#475569`
- **Aksen Utama (Oranye Teknis)**: `#C2410C` dengan teks `#FFFFFF` (kontras `5.18:1`)
- **Aksen Lembut**: `#FFF7ED` dengan teks `#9A3412` (kontras `6.88:1`) dan tepi `#EA580C`

### Mode Gelap
- **Kanvas**: `#0B0F19`
- **Permukaan Kartu**: `#151B2B` (tepi `#283248`)
- **Blok Kontras Editorial**: `#1B2336` dengan garis aksen atas `#EA580C`
- **Teks Utama**: `#F8FAFC` | **Teks Sekunder**: `#CBD5E1`
- **Aksen Utama**: `#FB923C` dengan teks gelap `#0B0F19` (kontras `8.72:1`)
- **Aksen Lembut**: `#2C190E` dengan teks `#FDBA74` (kontras `9.40:1`)

## Dials & Motif Identitas
- `Dial: ENERGY 3 / RHYTHM 3 / MOTION 2`
- **ENERGY 3 (Bold Focal Anchor)**: Blok sorotan arang gelap di beranda, ilustrasi hardware berwarna khas per kategori/merek, dan diagram PC menyala interaktif di `/rakit-pc`.
- **RHYTHM 3 (Varied Composition)**: Komposisi berubah jelas antarbagian: blok sorotan gelap asimetris -> strip kategori cepat -> tata letak 2 kolom katalog + fitur bandingkan barang -> blok bengkel kontras.
- **MOTION 2 (Functional Motion)**: Transisi SPA `wire:navigate`, buka-tutup halus modal/laci/lightbox, *skeleton* saat Livewire memfilter, dan nyala komponen di diagram casing PC, seluruhnya patuh pada `prefers-reduced-motion: reduce`.
