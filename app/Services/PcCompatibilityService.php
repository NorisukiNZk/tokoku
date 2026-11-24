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
}
