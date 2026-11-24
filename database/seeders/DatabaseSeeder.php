<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\PcBuild;
use App\Models\Product;
use App\Models\ServicePackage;
use App\Models\ServiceTicket;
use App\Models\ServiceTicketLog;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\PcCompatibilityService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@tokoku.local'],
            [
                'name' => 'Admin Tokoku',
                'phone' => '081200000001',
                'role' => 'admin',
                'password' => Hash::make('admin123'),
            ]
        );

        $tech = User::query()->updateOrCreate(
            ['email' => 'teknisi@tokoku.local'],
            [
                'name' => 'Teknisi Bengkel',
                'phone' => '081200000002',
                'role' => 'technician',
                'password' => Hash::make('teknisi123'),
            ]
        );

        StoreSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'store_name' => 'Tokoku',
                'tagline' => 'Etalase Part PC Baru & Bekas, Servis Terukur, dan Rakit PC',
                'whatsapp_number' => '6280000000000',
                'workshop_address' => '[ALAMAT WORKSHOP: Silakan isi alamat toko di Panel Admin]',
                'cod_areas' => 'Ambil langsung di meja workshop atau COD area dalam kota',
                'operating_hours' => 'Senin s.d. Sabtu, 09:00 - 18:00 WITA',
                'default_assembly_fee' => 150000,
                'is_placeholder_data' => true,
            ]
        );

        $categoriesData = [
            ['name' => 'Prosesor (CPU)', 'slug' => 'prosesor-cpu', 'component_slot' => 'cpu', 'sort_order' => 1],
            ['name' => 'Motherboard', 'slug' => 'motherboard', 'component_slot' => 'motherboard', 'sort_order' => 2],
            ['name' => 'Memori (RAM)', 'slug' => 'memori-ram', 'component_slot' => 'ram', 'sort_order' => 3],
            ['name' => 'Kartu Grafis (VGA)', 'slug' => 'kartu-grafis-vga', 'component_slot' => 'gpu', 'sort_order' => 4],
            ['name' => 'Penyimpanan (SSD & HDD)', 'slug' => 'penyimpanan-ssd-hdd', 'component_slot' => 'storage', 'sort_order' => 5],
            ['name' => 'Pendingin CPU', 'slug' => 'pendingin-cpu', 'component_slot' => 'cooler', 'sort_order' => 6],
            ['name' => 'Casing PC', 'slug' => 'casing-pc', 'component_slot' => 'case', 'sort_order' => 7],
            ['name' => 'Power Supply (PSU)', 'slug' => 'power-supply-psu', 'component_slot' => 'psu', 'sort_order' => 8],
            ['name' => 'Monitor & Layar', 'slug' => 'monitor-layar', 'component_slot' => 'monitor', 'sort_order' => 9],
            ['name' => 'Laptop & Mini PC Bekas', 'slug' => 'laptop-mini-pc', 'component_slot' => 'laptop', 'sort_order' => 10],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['component_slot']] = Category::query()->updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }

        $items = [
            [
                'slot' => 'cpu',
                'sku' => 'CPU-R5-5600',
                'brand' => 'AMD',
                'name' => 'AMD Ryzen 5 5600 6-Core 12-Thread Box',
                'condition' => 'new',
                'warranty_info' => 'Garansi Resmi 3 Tahun',
                'condition_notes' => 'Unit segel resmi lengkap dengan kipas Wraith Stealth di dalam dus.',
                'description' => 'Prosesor 6-core 12-thread arsitektur Zen 3 dengan cache L3 32MB. Tanpa grafis terintegrasi, wajib dipasang bersama VGA diskrit.',
                'price' => 1890000,
                'is_negotiable' => false,
                'stock' => 4,
                'show_in_pc_builder' => true,
                'specs' => ['socket' => 'AM4', 'tdp_watt' => 65, 'has_igpu' => false],
            ],
            [
                'slot' => 'cpu',
                'sku' => 'CPU-R5-7500F',
                'brand' => 'AMD',
                'name' => 'AMD Ryzen 5 7500F Tray + Fan',
                'condition' => 'new',
                'warranty_info' => 'Garansi Distributor 3 Tahun',
                'condition_notes' => 'Kondisi baru tray, sudah termasuk kipas standar.',
                'description' => 'Prosesor soket generasi AM5 6-core 12-thread hingga 5.0 GHz untuk platform DDR5. Wajib menggunakan kartu grafis terpisah.',
                'price' => 2540000,
                'is_negotiable' => false,
                'stock' => 3,
                'show_in_pc_builder' => true,
                'specs' => ['socket' => 'AM5', 'tdp_watt' => 65, 'has_igpu' => false],
            ],
            [
                'slot' => 'cpu',
                'sku' => 'CPU-I5-12400-USED',
                'brand' => 'Intel',
                'name' => 'Intel Core i5-12400 (Ada iGPU UHD 730) Fullset',
                'condition' => 'like_new',
                'warranty_info' => 'Sisa Garansi Resmi s.d. Agustus 2027 + Garansi Toko 14 Hari',
                'condition_notes' => 'Copotan upgrade PC kerja kantor, pin dan IHS bersih, kipas bawaan belum pernah dipakai.',
                'description' => 'Prosesor LGA1700 6-core 12-thread dengan grafis bawaan Intel UHD 730 sehingga PC dapat langsung tampil ke monitor tanpa VGA tambahan.',
                'price' => 1950000,
                'is_negotiable' => true,
                'stock' => 1,
                'show_in_pc_builder' => true,
                'specs' => ['socket' => 'LGA1700', 'tdp_watt' => 65, 'has_igpu' => true],
            ],
            [
                'slot' => 'motherboard',
                'sku' => 'MB-B550M-VDH',
                'brand' => 'MSI',
                'name' => 'MSI B550M PRO-VDH WIFI (AM4, DDR4, 4 Slot RAM)',
                'condition' => 'new',
                'warranty_info' => 'Garansi Resmi AAA 3 Tahun',
                'condition_notes' => 'Segel dus utuh, sudah mendukung Ryzen seri 5000 langsung tanpa update BIOS.',
                'description' => 'Motherboard Micro-ATX soket AM4 dilengkapi Wi-Fi & Bluetooth bawaan, heatsink VRM, dan 2 slot M.2 NVMe.',
                'price' => 1765000,
                'is_negotiable' => false,
                'stock' => 3,
                'show_in_pc_builder' => true,
                'specs' => ['socket' => 'AM4', 'ram_type' => 'DDR4', 'ram_slots' => 4, 'form_factor' => 'MATX'],
            ],
            [
                'slot' => 'motherboard',
                'sku' => 'MB-B650M-HDV',
                'brand' => 'ASRock',
                'name' => 'ASRock B650M-HDV/M.2 (AM5, DDR5)',
                'condition' => 'new',
                'warranty_info' => 'Garansi Resmi Astrindo 3 Tahun',
                'condition_notes' => 'Unit baru bersegel resmi.',
                'description' => 'Motherboard Micro-ATX soket AM5 dengan VRM Dr.MOS tangguh, 2 slot RAM DDR5, dan dukungan PCIe Gen5 M.2.',
                'price' => 2120000,
                'is_negotiable' => false,
                'stock' => 2,
                'show_in_pc_builder' => true,
                'specs' => ['socket' => 'AM5', 'ram_type' => 'DDR5', 'ram_slots' => 2, 'form_factor' => 'MATX'],
            ],
            [
                'slot' => 'motherboard',
                'sku' => 'MB-B660-TOMAHAWK-USED',
                'brand' => 'MSI',
                'name' => 'MSI MAG B660 TOMAHAWK WIFI DDR4 (ATX, LGA1700)',
                'condition' => 'good_used',
                'warranty_info' => 'Garansi Toko 30 Hari',
                'condition_notes' => 'Kelengkapan dus, antena Wi-Fi, dan kabel SATA. Semua port USB, LAN 2.5G, dan 4 slot RAM lulus uji.',
                'description' => 'Motherboard ukuran penuh ATX untuk prosesor Intel Gen 12/13/14 dengan sistem pendingin VRM tebal.',
                'price' => 1850000,
                'is_negotiable' => true,
                'stock' => 1,
                'show_in_pc_builder' => true,
                'specs' => ['socket' => 'LGA1700', 'ram_type' => 'DDR4', 'ram_slots' => 4, 'form_factor' => 'ATX'],
            ],
            [
                'slot' => 'motherboard',
                'sku' => 'MB-H81-MINUS',
                'brand' => 'Gigabyte',
                'name' => 'Motherboard Gigabyte GA-H81M-DS2 LGA1150 (Slot RAM B Mati)',
                'condition' => 'minus_parts',
                'warranty_info' => 'Garansi Tes Nyala 3 Hari sesuai deskripsi minus',
                'condition_notes' => 'MINUS JELAS: Slot RAM sebelah kanan tidak terbaca, audio belakang kresek (bonus USB soundcard). Slot RAM kiri, PCIe VGA, dan LAN normal.',
                'description' => 'Cocok untuk PC kasir ringan dengan 1 keping RAM 8GB DDR3 atau bahan kanibalan komponen teknisi.',
                'price' => 190000,
                'is_negotiable' => true,
                'stock' => 1,
                'show_in_pc_builder' => false,
                'specs' => ['socket' => 'LGA1150', 'ram_type' => 'DDR3', 'ram_slots' => 1, 'form_factor' => 'MATX'],
            ],
            [
                'slot' => 'ram',
                'sku' => 'RAM-KF-16D4',
                'brand' => 'Kingston',
                'name' => 'Kingston Fury Beast 16GB (2x8GB) DDR4 3200MHz CL16',
                'condition' => 'new',
                'warranty_info' => 'Garansi Resmi Lifetime',
                'condition_notes' => 'Kemasan blister segel utuh.',
                'description' => 'Kit memori dual-channel 2 keping masing-masing 8GB DDR4 3200MHz dengan profil XMP 2.0.',
                'price' => 640000,
                'is_negotiable' => false,
                'stock' => 6,
                'show_in_pc_builder' => true,
                'specs' => ['ram_type' => 'DDR4', 'modules_count' => 2, 'capacity_gb' => 16],
            ],
            [
                'slot' => 'ram',
                'sku' => 'RAM-TF-32D5',
                'brand' => 'TeamGroup',
                'name' => 'T-Force Vulcan 32GB (2x16GB) DDR5 5600MHz EXPO/XMP',
                'condition' => 'new',
                'warranty_info' => 'Garansi Resmi Lifetime',
                'condition_notes' => 'Baru segel resmi.',
                'description' => 'Memori DDR5 low-profile 2x16GB mendukung AMD EXPO dan Intel XMP 3.0, aman dipasang bersama heatsink besar.',
                'price' => 1590000,
                'is_negotiable' => false,
                'stock' => 4,
                'show_in_pc_builder' => true,
                'specs' => ['ram_type' => 'DDR5', 'modules_count' => 2, 'capacity_gb' => 32],
            ],
            [
                'slot' => 'gpu',
                'sku' => 'VGA-RX6600-USED',
                'brand' => 'PowerColor',
                'name' => 'PowerColor Fighter Radeon RX 6600 8GB GDDR6',
                'condition' => 'good_used',
                'warranty_info' => 'Garansi Toko 14 Hari',
                'condition_notes' => 'Segel baut utuh belum pernah bongkar, suhu FurMark stabil maks 68 derajat Celcius, dus asli ada.',
                'description' => 'Kartu grafis 1080p hemat daya (hanya 1x 8-pin PCIe, TDP 132W) dengan dimensi ringkas 200 mm.',
                'price' => 2150000,
                'is_negotiable' => true,
                'stock' => 2,
                'show_in_pc_builder' => true,
                'specs' => ['gpu_length_mm' => 200, 'tdp_watt' => 132],
            ],
            [
                'slot' => 'gpu',
                'sku' => 'VGA-RTX4060-NEW',
                'brand' => 'ASUS',
                'name' => 'ASUS Dual GeForce RTX 4060 OC Edition 8GB GDDR6',
                'condition' => 'new',
                'warranty_info' => 'Garansi Resmi ASUS Indonesia 3 Tahun',
                'condition_notes' => 'Barang baru bersegel distributor resmi.',
                'description' => 'Kartu grafis efisien berdaya 115W dengan fitur DLSS 3 dan encoder NVENC AV1 untuk gaming maupun editing.',
                'price' => 4890000,
                'is_negotiable' => false,
                'stock' => 2,
                'show_in_pc_builder' => true,
                'specs' => ['gpu_length_mm' => 227, 'tdp_watt' => 115],
            ],
            [
                'slot' => 'gpu',
                'sku' => 'VGA-RTX3080-TRIO-USED',
                'brand' => 'MSI',
                'name' => 'MSI GeForce RTX 3080 Gaming Z Trio 10GB (323 mm)',
                'condition' => 'like_new',
                'warranty_info' => 'Garansi Toko 30 Hari (Sudah Repaste PTM7950 di Bengkel Kami)',
                'condition_notes' => 'Sudah perawatan thermal pad & pasta di bengkel Tokoku, suhu beban penuh 69C, membutuhkan casing lega minimal 330 mm.',
                'description' => 'Kartu grafis 3 kipas berdaya 340W untuk resolusi 1440p. Perhatikan panjang fisik 323 mm dan kecukupan PSU sebelum membeli.',
                'price' => 5450000,
                'is_negotiable' => true,
                'stock' => 1,
                'show_in_pc_builder' => true,
                'specs' => ['gpu_length_mm' => 323, 'tdp_watt' => 340],
            ],
            [
                'slot' => 'storage',
                'sku' => 'SSD-NM620-512',
                'brand' => 'Lexar',
                'name' => 'SSD Lexar NM620 512GB M.2 NVMe PCIe Gen3x4',
                'condition' => 'new',
                'warranty_info' => 'Garansi Resmi 5 Tahun',
                'condition_notes' => 'Segel kemasan resmi.',
                'description' => 'Kecepatan baca hingga 3500 MB/s untuk drive sistem operasi utama.',
                'price' => 565000,
                'is_negotiable' => false,
                'stock' => 5,
                'show_in_pc_builder' => true,
                'specs' => ['capacity_gb' => 512, 'tdp_watt' => 5],
            ],
            [
                'slot' => 'storage',
                'sku' => 'SSD-SN580-1TB',
                'brand' => 'WD Blue',
                'name' => 'WD Blue SN580 1TB M.2 NVMe PCIe Gen4x4',
                'condition' => 'new',
                'warranty_info' => 'Garansi Resmi 5 Tahun',
                'condition_notes' => 'Segel resmi distributor.',
                'description' => 'SSD NVMe Gen4 1TB kecepatan baca hingga 4150 MB/s untuk penyimpanan game dan proyek video.',
                'price' => 1040000,
                'is_negotiable' => false,
                'stock' => 4,
                'show_in_pc_builder' => true,
                'specs' => ['capacity_gb' => 1000, 'tdp_watt' => 6],
            ],
            [
                'slot' => 'cooler',
                'sku' => 'CLR-AK400',
                'brand' => 'DeepCool',
                'name' => 'DeepCool AK400 Tower CPU Cooler 4 Heatpipe',
                'condition' => 'new',
                'warranty_info' => 'Garansi Resmi 1 Tahun',
                'condition_notes' => 'Lengkap braket Intel LGA1700/1200 dan AMD AM4/AM5.',
                'description' => 'Pendingin udara 120mm senyap untuk menjaga suhu kerja prosesor jauh lebih adem dari kipas bawaan.',
                'price' => 365000,
                'is_negotiable' => false,
                'stock' => 4,
                'show_in_pc_builder' => true,
                'specs' => ['supported_sockets' => ['AM4', 'AM5', 'LGA1700', 'LGA1200'], 'tdp_watt' => 5],
            ],
            [
                'slot' => 'case',
                'sku' => 'CASE-FLATLINE',
                'brand' => 'Tecware',
                'name' => 'Tecware Flatline mATX Mesh (Termasuk 4 Fan, Max VGA 340mm)',
                'condition' => 'new',
                'warranty_info' => 'Garansi Toko 7 Hari Cacat Fisik',
                'condition_notes' => 'Unit baru dalam dus gabus aman, panel samping kaca engsel.',
                'description' => 'Casing khusus motherboard Micro-ATX dan Mini-ITX dengan aliran udara depan jaring dan ruang kartu grafis hingga 340 mm.',
                'price' => 580000,
                'is_negotiable' => false,
                'stock' => 3,
                'show_in_pc_builder' => true,
                'specs' => ['supported_form_factors' => ['MATX', 'ITX'], 'max_gpu_mm' => 340],
            ],
            [
                'slot' => 'case',
                'sku' => 'CASE-AERO-MINI-USED',
                'brand' => 'Cube Gaming',
                'name' => 'Casing Office Ringkas mATX Slim (Max VGA 250mm)',
                'condition' => 'good_used',
                'warranty_info' => 'Tanpa garansi (Cek fisik langsung di toko)',
                'condition_notes' => 'Fisik 90% rapi, tombol power dan USB 3.0 depan normal. Hanya muat motherboard mATX/ITX dan VGA pendek maks 250 mm.',
                'description' => 'Casing hemat ruang untuk rakitan PC kerja/admin atau gaming ringan kartu grafis dua kipas pendek.',
                'price' => 235000,
                'is_negotiable' => true,
                'stock' => 2,
                'show_in_pc_builder' => true,
                'specs' => ['supported_form_factors' => ['MATX', 'ITX'], 'max_gpu_mm' => 250],
            ],
            [
                'slot' => 'case',
                'sku' => 'CASE-LANCOOL-ATX',
                'brand' => 'Lian Li',
                'name' => 'Lian Li Lancool 216 Mesh Full ATX (Max VGA 392mm)',
                'condition' => 'new',
                'warranty_info' => 'Garansi Resmi 1 Tahun',
                'condition_notes' => 'Unit baru segel dus.',
                'description' => 'Casing mid-tower luas mendukung motherboard ATX, Micro-ATX, hingga Mini-ITX dengan kipas depan 160mm.',
                'price' => 1450000,
                'is_negotiable' => false,
                'stock' => 2,
                'show_in_pc_builder' => true,
                'specs' => ['supported_form_factors' => ['ATX', 'MATX', 'ITX'], 'max_gpu_mm' => 392],
            ],
            [
                'slot' => 'psu',
                'sku' => 'PSU-FSP-550',
                'brand' => 'FSP',
                'name' => 'FSP HV PRO 550W 85+ Bronze (DC-to-DC)',
                'condition' => 'new',
                'warranty_info' => 'Garansi Resmi 3 Tahun',
                'condition_notes' => 'Segel resmi, kabel pipih hitam mudah dirapikan.',
                'description' => 'Power supply murni 550 Watt topologi DC-to-DC untuk rakitan kelas menengah satu VGA.',
                'price' => 695000,
                'is_negotiable' => false,
                'stock' => 4,
                'show_in_pc_builder' => true,
                'specs' => ['psu_watt' => 550],
            ],
            [
                'slot' => 'psu',
                'sku' => 'PSU-SEASONIC-750',
                'brand' => 'Seasonic',
                'name' => 'Seasonic Focus GX-750 750W 80+ Gold Full Modular',
                'condition' => 'new',
                'warranty_info' => 'Garansi Resmi 10 Tahun',
                'condition_notes' => 'Baru segel resmi.',
                'description' => 'Power supply 750 Watt 80+ Gold full modular untuk kartu grafis berdaya tinggi seperti RTX 3080 atau RX 7800 XT.',
                'price' => 1680000,
                'is_negotiable' => false,
                'stock' => 2,
                'show_in_pc_builder' => true,
                'specs' => ['psu_watt' => 750],
            ],
            [
                'slot' => 'laptop',
                'sku' => 'LPT-X280-USED',
                'brand' => 'Lenovo',
                'name' => 'Lenovo ThinkPad X280 Core i5-8350U RAM 8GB SSD 256GB 12.5"',
                'condition' => 'good_used',
                'warranty_info' => 'Garansi Toko 30 Hari Mesin & Keyboard',
                'condition_notes' => 'Kesehatan baterai 84% (tahan sekitar 3.5 jam kerja dokumen), engsel kokoh, charger USB-C original disertakan.',
                'description' => 'Laptop bisnis ringan 1.1 kg sudah di-repaste dan lulus pengecekan menyeluruh di bengkel Tokoku.',
                'price' => 2950000,
                'is_negotiable' => true,
                'stock' => 2,
                'show_in_pc_builder' => false,
                'specs' => [],
            ],
            [
                'slot' => 'monitor',
                'sku' => 'MON-LG-24MR400',
                'brand' => 'LG',
                'name' => 'Monitor LG 24MR400-B 24 Inci IPS 100Hz FreeSync',
                'condition' => 'new',
                'warranty_info' => 'Garansi Resmi LG 3 Tahun',
                'condition_notes' => 'Unit baru segel, bebas dead pixel (bisa tes nyala di meja toko).',
                'description' => 'Layar panel IPS 23.8 inci Full HD dengan refresh rate 100Hz nyaman untuk kerja harian dan game kasual.',
                'price' => 1325000,
                'is_negotiable' => false,
                'stock' => 3,
                'show_in_pc_builder' => false,
                'specs' => [],
            ],
        ];

        $createdProducts = [];
        foreach ($items as $item) {
            $svgPath = 'products/' . strtolower($item['sku']) . '.svg';
            Storage::disk('public')->put($svgPath, $this->renderHonestSpecPlaceholderSvg($item['sku'], $item['brand'], $item['slot']));

            $createdProducts[$item['sku']] = Product::query()->updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'category_id' => $categories[$item['slot']]->id,
                    'name' => $item['name'],
                    'slug' => Str::slug($item['name'] . '-' . $item['sku']),
                    'brand' => $item['brand'],
                    'condition' => $item['condition'],
                    'warranty_info' => $item['warranty_info'],
                    'condition_notes' => $item['condition_notes'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'is_negotiable' => $item['is_negotiable'],
                    'stock' => $item['stock'],
                    'availability_status' => 'available',
                    'location_area' => 'Workshop Tokoku',
                    'cod_available' => true,
                    'show_in_marketplace' => true,
                    'show_in_pc_builder' => $item['show_in_pc_builder'],
                    'primary_image' => $svgPath,
                    'compatibility_specs' => $item['specs'],
                    'is_sample_data' => true,
                ]
            );
        }

        $packages = [
            [
                'name' => 'Deep Cleaning & Ganti Thermal Paste PC / Laptop',
                'slug' => 'deep-cleaning-repaste',
                'category_label' => 'Perawatan Suhu',
                'description' => 'Pembersihan debu heatsink, kipas casing/radiator, penggantian pasta termal baru, serta pengujian suhu beban kerja sebelum dan sesudah pengerjaan.',
                'estimated_duration' => '2 - 4 Jam',
                'base_price' => 125000,
                'sort_order' => 1,
            ],
            [
                'name' => 'Diagnosa Mati Total, No Display, atau Restart Sendiri',
                'slug' => 'diagnosa-troubleshooting-hardware',
                'category_label' => 'Perbaikan Hardware',
                'description' => 'Pengecekan menyeluruh jalur daya PSU, RAM, VGA, dan motherboard menggunakan part penguji bengkel. Biaya pengecekan digratiskan bila lanjut perbaikan.',
                'estimated_duration' => '1 - 2 Hari Kerja',
                'base_price' => 75000,
                'sort_order' => 2,
            ],
            [
                'name' => 'Instalasi Ulang OS, Driver Resmi, & Optimasi Penyimpanan',
                'slug' => 'instalasi-os-driver',
                'category_label' => 'Sistem & Software',
                'description' => 'Pemasangan ulang Windows/Linux, pembaruan driver chipset & kartu grafis, partisi rapi, dan pengecekan kesehatan SSD/HDD.',
                'estimated_duration' => '2 - 3 Jam',
                'base_price' => 100000,
                'sort_order' => 3,
            ],
            [
                'name' => 'Jasa Rakit PC Kustom, Manajemen Kabel, & Stress Test',
                'slug' => 'jasa-rakit-manajemen-kabel',
                'category_label' => 'Perakitan PC',
                'description' => 'Perakitan komponen dari nol atau pindah casing, perapian jalur kabel belakang, update BIOS + aktifasi XMP/EXPO, dan uji kestabilan.',
                'estimated_duration' => '4 - 6 Jam',
                'base_price' => 150000,
                'sort_order' => 4,
            ],
        ];

        $savedPackages = [];
        foreach ($packages as $pkg) {
            $savedPackages[$pkg['slug']] = ServicePackage::query()->updateOrCreate(
                ['slug' => $pkg['slug']],
                $pkg + ['is_active' => true]
            );
        }

        $sampleTicket = ServiceTicket::query()->updateOrCreate(
            ['ticket_number' => 'SRV-202611-0001'],
            [
                'customer_name' => '[SIMULASI] Pelanggan Uji Coba',
                'customer_phone' => '081200001234',
                'customer_address' => 'Data simulasi untuk mencoba fitur lacak resi',
                'device_type' => 'PC Rakitan Desktop',
                'device_brand_model' => 'Ryzen 5 3600 + GTX 1660 Super',
                'service_package_id' => $savedPackages['deep-cleaning-repaste']->id,
                'complaint_summary' => 'Suhu prosesor menyentuh 92C saat render dan kipas berbunyi kasar.',
                'visit_type' => 'walk_in',
                'preferred_date' => now()->toDateString(),
                'technician_id' => $tech->id,
                'status' => 'testing_qc',
                'diagnosis_notes' => 'Pasta lama mengering keras dan jaring depan casing tertutup debu tebal. Sudah dibersihkan dan diganti pasta baru.',
                'temp_before_c' => 92,
                'temp_after_c' => 67,
                'service_fee' => 125000,
                'parts_total' => 0,
                'grand_total' => 125000,
                'warranty_until' => now()->addDays(14)->toDateString(),
                'is_sample_data' => true,
            ]
        );

        if ($sampleTicket->logs()->count() === 0) {
            ServiceTicketLog::query()->create([
                'service_ticket_id' => $sampleTicket->id,
                'user_id' => $admin->id,
                'status' => 'booked',
                'note' => 'Unit diterima di meja workshop dan dicatat suhu awal 92C.',
                'is_public' => true,
            ]);
            ServiceTicketLog::query()->create([
                'service_ticket_id' => $sampleTicket->id,
                'user_id' => $tech->id,
                'status' => 'in_progress',
                'note' => 'Pembersihan menyeluruh sirip heatsink, kipas VGA, dan penggantian pasta termal.',
                'is_public' => true,
            ]);
            ServiceTicketLog::query()->create([
                'service_ticket_id' => $sampleTicket->id,
                'user_id' => $tech->id,
                'status' => 'testing_qc',
                'note' => 'Pengujian Cinebench 20 menit: suhu puncak turun ke 67C stabil.',
                'is_public' => true,
            ]);
        }

        $compat = new PcCompatibilityService();
        $presetSelected = [
            'cpu' => $createdProducts['CPU-R5-5600'],
            'motherboard' => $createdProducts['MB-B550M-VDH'],
            'ram' => $createdProducts['RAM-KF-16D4'],
            'gpu' => $createdProducts['VGA-RX6600-USED'],
            'storage' => $createdProducts['SSD-NM620-512'],
            'cooler' => $createdProducts['CLR-AK400'],
            'case' => $createdProducts['CASE-FLATLINE'],
            'psu' => $createdProducts['PSU-FSP-550'],
        ];
        $eval = $compat->evaluate($presetSelected);

        PcBuild::query()->updateOrCreate(
            ['share_code' => 'RKT-CONTOH'],
            [
                'build_name' => 'Rakitan Hemat 1080p (Part Baru + VGA Bekas Bergaransi)',
                'customer_name' => 'Contoh Rakitan Toko',
                'purpose_label' => 'Gaming 1080p & Editing',
                'selected_components' => collect($presetSelected)->mapWithKeys(fn ($p, $slot) => [$slot => $p->id])->all(),
                'estimated_wattage' => $eval['estimated_wattage'],
                'recommended_psu_wattage' => $eval['recommended_psu_wattage'],
                'compatibility_passed' => $eval['passed'],
                'compatibility_notes' => $eval['warnings'],
                'include_assembly_service' => true,
                'assembly_fee' => 150000,
                'parts_subtotal' => $eval['parts_subtotal'],
                'grand_total' => $eval['parts_subtotal'] + 150000,
            ]
        );
    }

    private function renderHonestSpecPlaceholderSvg(string $sku, string $brand, string $slot): string
    {
        $slotNames = [
            'cpu' => 'Prosesor Desktop',
            'motherboard' => 'Motherboard PC',
            'ram' => 'Memori RAM',
            'gpu' => 'Kartu Grafis (VGA)',
            'storage' => 'Penyimpanan SSD',
            'cooler' => 'Pendingin CPU',
            'case' => 'Casing Komputer',
            'psu' => 'Power Supply',
            'laptop' => 'Laptop Kerja',
            'monitor' => 'Layar Monitor',
        ];
        $slotLabel = $slotNames[$slot] ?? 'Perangkat PC';
        $safeSku = htmlspecialchars($sku, ENT_QUOTES, 'UTF-8');
        $safeBrand = htmlspecialchars($brand, ENT_QUOTES, 'UTF-8');

        $iconShape = match ($slot) {
            'cpu' => '<rect x="250" y="95" width="140" height="140" rx="18" fill="#DBEAFE" stroke="#2563EB" stroke-width="3"/><rect x="282" y="127" width="76" height="76" rx="10" fill="#EFF6FF" stroke="#1D4ED8" stroke-width="2.5"/>',
            'gpu' => '<rect x="190" y="110" width="260" height="115" rx="16" fill="#DBEAFE" stroke="#2563EB" stroke-width="3"/><circle cx="265" cy="167" r="36" fill="#EFF6FF" stroke="#1D4ED8" stroke-width="2.5"/><circle cx="375" cy="167" r="36" fill="#EFF6FF" stroke="#1D4ED8" stroke-width="2.5"/>',
            'laptop', 'monitor' => '<rect x="205" y="95" width="230" height="135" rx="12" fill="#DBEAFE" stroke="#2563EB" stroke-width="3"/><line x1="180" y1="242" x2="460" y2="242" stroke="#1D4ED8" stroke-width="5" stroke-linecap="round"/>',
            default => '<rect x="220" y="100" width="200" height="130" rx="16" fill="#DBEAFE" stroke="#2563EB" stroke-width="3"/><line x1="255" y1="140" x2="385" y2="140" stroke="#1D4ED8" stroke-width="3" stroke-linecap="round"/><line x1="255" y1="170" x2="355" y2="170" stroke="#3B82F6" stroke-width="3" stroke-linecap="round"/><line x1="255" y1="200" x2="330" y2="200" stroke="#60A5FA" stroke-width="3" stroke-linecap="round"/>',
        };

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 480" width="640" height="480">
  <rect width="640" height="480" fill="#EEF2F7"/>
  <rect x="28" y="28" width="584" height="424" rx="20" fill="#F8FAFC" stroke="#DCE3EC" stroke-width="2"/>
  {$iconShape}
  <text x="320" y="305" text-anchor="middle" fill="#0F172A" font-family="Inter, Segoe UI, sans-serif" font-size="28" font-weight="700">{$safeBrand}</text>
  <text x="320" y="340" text-anchor="middle" fill="#475569" font-family="Inter, Segoe UI, sans-serif" font-size="18" font-weight="500">{$slotLabel} ({$safeSku})</text>
  <rect x="215" y="378" width="210" height="32" rx="8" fill="#E2E8F0"/>
  <text x="320" y="399" text-anchor="middle" fill="#334155" font-family="Inter, Segoe UI, sans-serif" font-size="13" font-weight="600">[Ilustrasi Contoh Stok]</text>
</svg>
SVG;
    }
}
