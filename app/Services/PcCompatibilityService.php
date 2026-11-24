<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PcCompatibilityService
{
    public const BUILDER_SLOTS = [
        'cpu' => [
            'label' => 'Prosesor (CPU)',
            'required' => true,
            'hint' => 'Menentukan soket motherboard yang dapat dipilih (dari kelas hemat hingga flagship).',
        ],
        'motherboard' => [
            'label' => 'Motherboard',
            'required' => true,
            'hint' => 'Menyesuaikan soket prosesor, generasi memori DDR, dan ukuran casing.',
        ],
        'ram' => [
            'label' => 'Memori (RAM)',
            'required' => true,
            'hint' => 'Otomatis disaring sesuai generasi DDR3/DDR4/DDR5 dan jumlah slot motherboard.',
        ],
        'gpu' => [
            'label' => 'Kartu Grafis (VGA / GPU)',
            'required' => false,
            'hint' => 'Opsional bila CPU memiliki grafis bawaan (iGPU). Pilih sesuai target resolusi & anggaran.',
        ],
        'storage' => [
            'label' => 'Penyimpanan (SSD / HDD)',
            'required' => true,
            'hint' => 'Pilih SSD NVMe atau SATA untuk sistem operasi dan penyimpanan data.',
        ],
        'cooler' => [
            'label' => 'Pendingin CPU (Cooler)',
            'required' => false,
            'hint' => 'Pendingin udara atau Liquid AIO untuk menjaga suhu kerja prosesor.',
        ],
        'case' => [
            'label' => 'Casing Komputer',
            'required' => true,
            'hint' => 'Dicek terhadap ukuran motherboard (ATX/mATX/ITX) dan panjang fisik kartu grafis.',
        ],
        'psu' => [
            'label' => 'Power Supply (PSU)',
            'required' => true,
            'hint' => 'Kapasitas daya dihitung otomatis dari total komponen ditambah ruang aman 30%.',
        ],
    ];

    /**
     * @param array<string, Model|null> $selected
     * @return array{
     *     passed: bool,
     *     errors: list<string>,
     *     warnings: list<string>,
     *     estimated_wattage: int,
     *     recommended_psu_wattage: int,
     *     parts_subtotal: int
     * }
     */
    public function evaluate(array $selected): array
    {
        $errors = [];
        $warnings = [];

        $cpu = $selected['cpu'] ?? null;
        $mobo = $selected['motherboard'] ?? null;
        $ram = $selected['ram'] ?? null;
        $gpu = $selected['gpu'] ?? null;
        $storage = $selected['storage'] ?? null;
        $cooler = $selected['cooler'] ?? null;
        $case = $selected['case'] ?? null;
        $psu = $selected['psu'] ?? null;

        $cpuSpecs = $cpu?->compatibility_specs ?? [];
        $moboSpecs = $mobo?->compatibility_specs ?? [];
        $ramSpecs = $ram?->compatibility_specs ?? [];
        $gpuSpecs = $gpu?->compatibility_specs ?? [];
        $coolerSpecs = $cooler?->compatibility_specs ?? [];
        $caseSpecs = $case?->compatibility_specs ?? [];
        $psuSpecs = $psu?->compatibility_specs ?? [];

        if ($cpu && $mobo) {
            $cpuSocket = strtoupper((string) ($cpuSpecs['socket'] ?? ''));
            $moboSocket = strtoupper((string) ($moboSpecs['socket'] ?? ''));

            if ($cpuSocket !== '' && $moboSocket !== '' && $cpuSocket !== $moboSocket) {
                $errors[] = "Soket tidak cocok: {$cpu->name} memakai soket {$cpuSocket}, sedangkan {$mobo->name} memakai soket {$moboSocket}.";
            }
        }

        if ($mobo && $ram) {
            $moboRam = strtoupper((string) ($moboSpecs['ram_type'] ?? ''));
            $ramType = strtoupper((string) ($ramSpecs['ram_type'] ?? ''));

            if ($moboRam !== '' && $ramType !== '' && $moboRam !== $ramType) {
                $errors[] = "Tipe RAM tidak cocok: {$mobo->name} membutuhkan {$moboRam}, sedangkan {$ram->name} bertipe {$ramType}.";
            }

            $moboSlots = (int) ($moboSpecs['ram_slots'] ?? 0);
            $ramModules = (int) ($ramSpecs['modules_count'] ?? 0);
            if ($moboSlots > 0 && $ramModules > 0 && $ramModules > $moboSlots) {
                $errors[] = "Jumlah keping RAM ({$ramModules} keping) melebihi slot memori pada {$mobo->name} ({$moboSlots} slot).";
            }
        }

        if ($mobo && $case) {
            $moboForm = strtoupper((string) ($moboSpecs['form_factor'] ?? ''));
            $supportedForms = array_map('strtoupper', (array) ($caseSpecs['supported_form_factors'] ?? []));

            if ($moboForm !== '' && count($supportedForms) > 0 && ! in_array($moboForm, $supportedForms, true)) {
                $supportedText = implode(', ', $supportedForms);
                $errors[] = "Ukuran motherboard {$moboForm} tidak muat di {$case->name} (hanya mendukung: {$supportedText}).";
            }
        }

        if ($gpu && $case) {
            $gpuLen = (int) ($gpuSpecs['gpu_length_mm'] ?? 0);
            $maxGpuLen = (int) ($caseSpecs['max_gpu_mm'] ?? 0);

            if ($gpuLen > 0 && $maxGpuLen > 0 && $gpuLen > $maxGpuLen) {
                $errors[] = "Panjang fisik {$gpu->name} ({$gpuLen} mm) melebihi ruang maksimal VGA pada {$case->name} ({$maxGpuLen} mm).";
            }
        }

        if ($cpu && $cooler) {
            $cpuSocket = strtoupper((string) ($cpuSpecs['socket'] ?? ''));
            $coolerSockets = array_map('strtoupper', (array) ($coolerSpecs['supported_sockets'] ?? []));

            if ($cpuSocket !== '' && count($coolerSockets) > 0 && ! in_array($cpuSocket, $coolerSockets, true)) {
                $errors[] = "Braket pendingin {$cooler->name} tidak mendukung soket {$cpuSocket}.";
            }
        }

        if ($cpu && ! $gpu) {
            $hasIgpu = (bool) ($cpuSpecs['has_igpu'] ?? true);
            if (! $hasIgpu) {
                $warnings[] = "{$cpu->name} tidak memiliki grafis bawaan (tanpa iGPU). Tambahkan Kartu Grafis (VGA) agar layar dapat tampil.";
            }
        }

        $wattage = 45;
        foreach ([$cpu, $gpu, $cooler] as $part) {
            if ($part) {
                $wattage += (int) ($part->compatibility_specs['tdp_watt'] ?? 0);
            }
        }
        if ($ram) {
            $wattage += 10;
        }
        if ($storage) {
            $wattage += 8;
        }

        $recommendedPsu = max(450, (int) (ceil(($wattage * 1.30) / 50) * 50));

        if ($psu) {
            $psuWatt = (int) ($psuSpecs['psu_watt'] ?? 0);
            if ($psuWatt > 0 && $psuWatt < ($wattage + 40)) {
                $errors[] = "Daya {$psu->name} ({$psuWatt}W) kurang untuk beban komponen sekitar {$wattage}W. Pilih minimal {$recommendedPsu}W.";
            } elseif ($psuWatt > 0 && $psuWatt < $recommendedPsu) {
                $warnings[] = "Daya {$psu->name} ({$psuWatt}W) cukup mepet untuk beban {$wattage}W; disarankan {$recommendedPsu}W agar lebih aman.";
            }
        }

        $subtotal = 0;
        foreach ($selected as $part) {
            if ($part instanceof Model) {
                $subtotal += (int) ($part->estimated_price_idr ?? $part->price ?? 0);
            }
        }

        return [
            'passed' => count($errors) === 0,
            'errors' => $errors,
            'warnings' => $warnings,
            'estimated_wattage' => $wattage,
            'recommended_psu_wattage' => $recommendedPsu,
            'parts_subtotal' => $subtotal,
        ];
    }

    /**
     * Push compatibility constraints down into SQL so filtering 10,000+ catalog parts stays fast.
     *
     * @param array<string, Model|null> $selected
     */
    public function applySqlCompatibilityConstraints(Builder $query, string $slot, array $selected, int $currentWattage): void
    {
        $cpu = $selected['cpu'] ?? null;
        $mobo = $selected['motherboard'] ?? null;
        $ram = $selected['ram'] ?? null;
        $gpu = $selected['gpu'] ?? null;
        $case = $selected['case'] ?? null;

        if ($slot === 'cpu' && $mobo && ! empty($mobo->compatibility_specs['socket'])) {
            $query->where('compatibility_specs->socket', $mobo->compatibility_specs['socket']);
        }

        if ($slot === 'motherboard') {
            if ($cpu && ! empty($cpu->compatibility_specs['socket'])) {
                $query->where('compatibility_specs->socket', $cpu->compatibility_specs['socket']);
            }
            if ($ram && ! empty($ram->compatibility_specs['ram_type'])) {
                $query->where('compatibility_specs->ram_type', $ram->compatibility_specs['ram_type']);
            }
            if ($case && ! empty($case->compatibility_specs['supported_form_factors'])) {
                $query->whereIn('compatibility_specs->form_factor', (array) $case->compatibility_specs['supported_form_factors']);
            }
        }

        if ($slot === 'ram' && $mobo) {
            if (! empty($mobo->compatibility_specs['ram_type'])) {
                $query->where('compatibility_specs->ram_type', $mobo->compatibility_specs['ram_type']);
            }
            if (! empty($mobo->compatibility_specs['ram_slots'])) {
                $query->where('compatibility_specs->modules_count', '<=', (int) $mobo->compatibility_specs['ram_slots']);
            }
        }

        if ($slot === 'gpu' && $case && ! empty($case->compatibility_specs['max_gpu_mm'])) {
            $maxMm = (int) $case->compatibility_specs['max_gpu_mm'];
            $query->where(function (Builder $sub) use ($maxMm) {
                $sub->whereNull('compatibility_specs->gpu_length_mm')
                    ->orWhere('compatibility_specs->gpu_length_mm', '<=', $maxMm);
            });
        }

        if ($slot === 'case') {
            if ($mobo && ! empty($mobo->compatibility_specs['form_factor'])) {
                $query->whereJsonContains('compatibility_specs->supported_form_factors', $mobo->compatibility_specs['form_factor']);
            }
            if ($gpu && ! empty($gpu->compatibility_specs['gpu_length_mm'])) {
                $query->where('compatibility_specs->max_gpu_mm', '>=', (int) $gpu->compatibility_specs['gpu_length_mm']);
            }
        }

        if ($slot === 'psu') {
            $minSafeWatt = max(350, $currentWattage + 40);
            $query->where('compatibility_specs->psu_watt', '>=', $minSafeWatt);
        }
    }

    /**
     * @param Collection<int, Model> $candidates
     * @param array<string, Model|null> $selected
     * @return Collection<int, array{product: Model, compatible: bool, reason: string|null}>
     */
    public function annotateCandidatesForSlot(string $slot, Collection $candidates, array $selected): Collection
    {
        return $candidates->map(function (Model $candidate) use ($slot, $selected) {
            $hypothetical = $selected;
            $hypothetical[$slot] = $candidate;

            $result = $this->evaluate($hypothetical);

            return [
                'product' => $candidate,
                'compatible' => $result['passed'],
                'reason' => $result['errors'][0] ?? null,
            ];
        });
    }

    /**
     * Deterministically evaluate practical capability tiers strictly from selected component specs.
     *
     * @param array<string, Model|null> $selected
     * @return array{
     *     ready: bool,
     *     tier_label: string,
     *     tier_tone: string,
     *     summary: string,
     *     office_score: int,
     *     gaming_score: int,
     *     creator_score: int,
     *     notes: list<string>
     * }
     */
    public function evaluatePerformanceProfile(array $selected): array
    {
        $cpu = $selected['cpu'] ?? null;
        $gpu = $selected['gpu'] ?? null;
        $ram = $selected['ram'] ?? null;
        $storage = $selected['storage'] ?? null;

        if (! $cpu && ! $gpu) {
            return [
                'ready' => false,
                'tier_label' => 'Menunggu Pilihan Part',
                'tier_tone' => 'idle',
                'summary' => 'Pilih Prosesor (CPU) atau Kartu Grafis (VGA) lewat diagram casing atau daftar slot di bawah untuk menilai kelas rakitan.',
                'office_score' => 0,
                'gaming_score' => 0,
                'creator_score' => 0,
                'notes' => [
                    'Skor dihitung langsung dari kelas prosesor, GPU, kapasitas RAM, dan penyimpanan yang dipilih.',
                ],
            ];
        }

        $cpuScore = 0;
        $hasIgpu = true;
        if ($cpu) {
            $cpuName = strtoupper((string) $cpu->name);
            $cpuSpecs = $cpu->compatibility_specs ?? [];
            $hasIgpu = (bool) ($cpuSpecs['has_igpu'] ?? true);
            $cpuTier = (string) ($cpu->price_tier ?? 'mid');

            $cpuScore = match (true) {
                (bool) preg_match('/(RYZEN 9|CORE I9|ULTRA 9|THREADRIPPER|XEON)/', $cpuName) => 94,
                (bool) preg_match('/(RYZEN 7|CORE I7|ULTRA 7)/', $cpuName) => 82,
                (bool) preg_match('/(RYZEN 5|CORE I5|ULTRA 5)/', $cpuName) => 68,
                (bool) preg_match('/(RYZEN 3|CORE I3|PENTIUM|ATHLON|CELERON)/', $cpuName) => 46,
                $cpuTier === 'high' => 86,
                $cpuTier === 'mid' => 66,
                default => 48,
            };

            $socket = strtoupper((string) ($cpuSpecs['socket'] ?? ''));
            if (in_array($socket, ['AM5', 'LGA1700', 'LGA1851'], true)) {
                $cpuScore = min(100, $cpuScore + 5);
            }
        }

        $gpuScore = 0;
        if ($gpu) {
            $gpuName = strtoupper((string) $gpu->name);
            $gpuSpecs = $gpu->compatibility_specs ?? [];
            $gpuTdp = (int) ($gpuSpecs['tdp_watt'] ?? 0);
            $gpuTier = (string) ($gpu->price_tier ?? 'mid');

            $gpuScore = match (true) {
                (bool) preg_match('/(5090|4090|5080|4080|7900 XTX|9070 XT)/', $gpuName) => 98,
                (bool) preg_match('/(5070|4070|3090|3080|7900 XT|7900 GRE|7800 XT|6950|6900)/', $gpuName) => 87,
                (bool) preg_match('/(4060 TI|3070|6800|7700|6750|6700|B580)/', $gpuName) => 76,
                (bool) preg_match('/(4060|3060|6650|6600|7600|2070|A770|A750)/', $gpuName) => 64,
                (bool) preg_match('/(3050|2060|1660|5600|6500|1070|1080)/', $gpuName) => 50,
                $gpuTier === 'high' => 86,
                $gpuTier === 'mid' => 64,
                $gpuTdp >= 220 => 80,
                $gpuTdp >= 130 => 62,
                default => 42,
            };
        } elseif ($cpu && $hasIgpu) {
            $gpuScore = 26;
        }

        $ramGb = (int) ($ram?->compatibility_specs['capacity_gb'] ?? 0);
        $ramType = strtoupper((string) ($ram?->compatibility_specs['ram_type'] ?? ''));
        $ramScore = 0;
        if ($ram) {
            $ramScore = match (true) {
                $ramGb >= 64 => 98,
                $ramGb >= 32 => 90,
                $ramGb >= 16 => 76,
                $ramGb >= 8 => 52,
                default => 38,
            };
            if ($ramType === 'DDR5') {
                $ramScore = min(100, $ramScore + 5);
            }
        }

        $storageText = strtoupper((string) (($storage?->name ?? '') . ' ' . ($storage?->spec_summary ?? '')));
        $storageScore = $storage ? (str_contains($storageText, 'NVME') || str_contains($storageText, 'SSD') ? 90 : 55) : 45;

        $effectiveRam = $ram ? $ramScore : 45;
        $effectiveCpu = $cpu ? $cpuScore : 40;

        $officeScore = (int) min(100, max(15, round($effectiveCpu * 0.52 + $effectiveRam * 0.34 + $storageScore * 0.14)));

        if (! $gpu && $cpu && ! $hasIgpu) {
            $gamingScore = 0;
        } elseif (! $gpu) {
            $gamingScore = (int) min(38, max(12, round($effectiveCpu * 0.35 + $gpuScore * 0.65)));
        } else {
            $gamingScore = (int) min(100, max(18, round($effectiveCpu * 0.32 + $gpuScore * 0.56 + $effectiveRam * 0.12)));
        }

        $creatorScore = (int) min(100, max(15, round($effectiveCpu * 0.44 + max($gpuScore, 20) * 0.36 + $effectiveRam * 0.20)));

        $notes = [];
        if (! $gpu && $cpu && ! $hasIgpu) {
            $tierLabel = 'Perlu VGA Terpisah';
            $tierTone = 'warn';
            $summary = 'Prosesor terpilih tidak memiliki grafis bawaan (tanpa iGPU). Tambahkan Kartu Grafis agar layar bisa tampil.';
            $notes[] = 'Pilih slot Kartu Grafis (VGA) untuk membuka kemampuan grafis dan tampilan layar.';
        } elseif ($gamingScore >= 82 && $creatorScore >= 78) {
            $tierLabel = 'Workstation & Gaming 1440p/4K';
            $tierTone = 'flagship';
            $summary = 'Kombinasi prosesor, kartu grafis, dan memori siap untuk render 3D berat, editing 4K, dan game resolusi tinggi.';
        } elseif ($gamingScore >= 62) {
            $tierLabel = 'Gaming 1080p/1440p & Desain';
            $tierTone = 'high';
            $summary = 'Seimbang untuk game modern di setelan menengah hingga tinggi, desain grafis, serta editing video Full HD.';
        } elseif ($gamingScore >= 42) {
            $tierLabel = 'Multitasking & Gaming Ringan';
            $tierTone = 'mid';
            $summary = 'Nyaman untuk pekerjaan multitasking, pemrograman, desain 2D, dan game e-sports di resolusi 1080p.';
        } else {
            $tierLabel = 'Harian, Kantor & Belajar';
            $tierTone = 'entry';
            $summary = 'Cocok untuk pengolahan dokumen, browsing banyak tab, akuntansi, dan penggunaan harian hemat daya.';
        }

        if (! $ram) {
            $notes[] = 'Belum memilih RAM: disarankan minimal 16 GB untuk kenyamanan multitasking modern.';
        } elseif ($ramGb >= 32) {
            $notes[] = "Kapasitas RAM {$ramGb} GB sangat leluasa untuk editing video, virtualisasi, dan multitasking berat.";
        }

        if (! $storage) {
            $notes[] = 'Tambahkan SSD NVMe/SATA agar waktu booting sistem dan buka aplikasi tetap responsif.';
        }

        return [
            'ready' => true,
            'tier_label' => $tierLabel,
            'tier_tone' => $tierTone,
            'summary' => $summary,
            'office_score' => $officeScore,
            'gaming_score' => $gamingScore,
            'creator_score' => $creatorScore,
            'notes' => $notes,
        ];
    }
}
