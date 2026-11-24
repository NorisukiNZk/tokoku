<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class PcCompatibilityService
{
    public const BUILDER_SLOTS = [
        'cpu' => [
            'label' => 'Prosesor (CPU)',
            'required' => true,
            'hint' => 'Menentukan soket motherboard yang dapat dipilih.',
        ],
        'motherboard' => [
            'label' => 'Motherboard',
            'required' => true,
            'hint' => 'Menentukan tipe memori DDR dan ukuran form factor casing.',
        ],
        'ram' => [
            'label' => 'Memori (RAM)',
            'required' => true,
            'hint' => 'Harus sesuai generasi DDR dan kapasitas slot motherboard.',
        ],
        'gpu' => [
            'label' => 'Kartu Grafis (VGA / GPU)',
            'required' => false,
            'hint' => 'Opsional bila CPU memiliki grafis terintegrasi (non-F / seri G).',
        ],
        'storage' => [
            'label' => 'Penyimpanan (SSD / HDD)',
            'required' => true,
            'hint' => 'Media instalasi sistem operasi dan data kerja.',
        ],
        'cooler' => [
            'label' => 'Pendingin Prosesor (CPU Cooler)',
            'required' => false,
            'hint' => 'Disarankan bila prosesor tidak menyertakan kipas bawaan.',
        ],
        'case' => [
            'label' => 'Casing PC',
            'required' => true,
            'hint' => 'Harus mendukung ukuran motherboard dan panjang fisik VGA.',
        ],
        'psu' => [
            'label' => 'Power Supply (PSU)',
            'required' => true,
            'hint' => 'Kapasitas daya murni disarankan memiliki ruang aman minimal 25%.',
        ],
    ];

    /**
     * @param array<string, Product|null> $selected
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
                $warnings[] = "{$cpu->name} tidak memiliki grafis terintegrasi (tanpa iGPU). Anda wajib menambahkan Kartu Grafis (VGA) agar layar tampil.";
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
                $errors[] = "Daya {$psu->name} ({$psuWatt}W) terlalu mepet/kurang untuk beban komponen sekitar {$wattage}W. Pilih minimal {$recommendedPsu}W.";
            } elseif ($psuWatt > 0 && $psuWatt < $recommendedPsu) {
                $warnings[] = "Daya {$psu->name} ({$psuWatt}W) masih bisa menyala untuk beban {$wattage}W, namun disarankan {$recommendedPsu}W agar ruang upgrade lebih aman.";
            }
        }

        $subtotal = 0;
        foreach ($selected as $part) {
            if ($part instanceof Product) {
                $subtotal += (int) $part->price;
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
     * @param Collection<int, Product> $candidates
     * @param array<string, Product|null> $selected
     * @return Collection<int, array{product: Product, compatible: bool, reason: string|null}>
     */
    public function annotateCandidatesForSlot(string $slot, Collection $candidates, array $selected): Collection
    {
        return $candidates->map(function (Product $candidate) use ($slot, $selected) {
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
