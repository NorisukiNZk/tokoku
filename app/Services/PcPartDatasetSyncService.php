<?php

namespace App\Services;

use App\Models\PcCatalogPart;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PcPartDatasetSyncService
{
    private const BASE_URL = 'https://raw.githubusercontent.com/docyx/pc-part-dataset/main/data/json';
    private const RATE_URL = 'https://open.er-api.com/v6/latest/USD';

    private const SLOT_FILES = [
        'cpu' => 'cpu.json',
        'motherboard' => 'motherboard.json',
        'ram' => 'memory.json',
        'gpu' => 'video-card.json',
        'storage' => 'internal-hard-drive.json',
        'cooler' => 'cpu-cooler.json',
        'case' => 'case.json',
        'psu' => 'power-supply.json',
    ];

    /**
     * @return array{total_synced: int, usd_rate: int, counts_by_slot: array<string, int>}
     */
    public function syncAll(?int $overrideRate = null): array
    {
        $store = StoreSetting::current();
        $usdRate = $overrideRate ?? $this->fetchLiveUsdToIdrRate((int) ($store->usd_to_idr_rate ?: 16500));

        $countsBySlot = [];
        $totalSynced = 0;
        $now = now();

        foreach (self::SLOT_FILES as $slot => $filename) {
            $response = Http::timeout(45)->get(self::BASE_URL . '/' . $filename);
            if (! $response->successful()) {
                throw new \RuntimeException("Gagal mengambil data API {$filename} (HTTP {$response->status()}).");
            }

            $rawItems = $response->json();
            if (! is_array($rawItems)) {
                continue;
            }

            $rows = [];
            $seenKeys = [];

            foreach ($rawItems as $item) {
                $priceUsd = isset($item['price']) ? (float) $item['price'] : 0.0;
                $name = trim((string) ($item['name'] ?? ''));

                if ($name === '' || $priceUsd <= 1.0) {
                    continue;
                }

                $mapped = $this->normalizeItem($slot, $item, $usdRate);
                $extKey = $mapped['external_key'];

                if (isset($seenKeys[$extKey])) {
                    continue;
                }
                $seenKeys[$extKey] = true;

                $rows[] = array_merge($mapped, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::transaction(function () use ($slot, $rows) {
                PcCatalogPart::query()->where('slot', $slot)->delete();
                foreach (array_chunk($rows, 400) as $chunk) {
                    PcCatalogPart::query()->insert($chunk);
                }
            });

            $countsBySlot[$slot] = count($rows);
            $totalSynced += count($rows);
        }

        $store->update([
            'usd_to_idr_rate' => $usdRate,
            'last_parts_sync_at' => $now,
        ]);

        return [
            'total_synced' => $totalSynced,
            'usd_rate' => $usdRate,
            'counts_by_slot' => $countsBySlot,
        ];
    }

    public function fetchLiveUsdToIdrRate(int $fallback = 16500): int
    {
        try {
            $resp = Http::timeout(10)->get(self::RATE_URL);
            if ($resp->successful()) {
                $idr = (float) ($resp->json('rates.IDR') ?? 0);
                if ($idr >= 10000 && $idr <= 30000) {
                    return (int) round($idr);
                }
            }
        } catch (\Throwable) {
            // Keep fallback rate when rate service is unreachable.
        }

        return $fallback;
    }

    private function normalizeItem(string $slot, array $item, int $usdRate): array
    {
        $name = trim((string) $item['name']);
        $priceUsd = round((float) $item['price'], 2);
        $priceIdr = max(25000, (int) (round(($priceUsd * $usdRate) / 5000) * 5000));
        $brand = strtok($name, ' ') ?: 'Generic';

        [$specs, $summaryParts] = match ($slot) {
            'cpu' => $this->mapCpu($name, $item),
            'motherboard' => $this->mapMotherboard($name, $item),
            'ram' => $this->mapMemory($item),
            'gpu' => $this->mapGpu($name, $item),
            'storage' => $this->mapStorage($item),
            'cooler' => $this->mapCooler($item),
            'case' => $this->mapCase($item),
            'psu' => $this->mapPsu($item),
            default => [[], []],
        };

        $keySeed = $slot . ':' . strtolower($name) . ':' . ($summaryParts[0] ?? '');
        $externalKey = $slot . '-' . substr(md5($keySeed), 0, 16);

        return [
            'external_key' => $externalKey,
            'slot' => $slot,
            'name' => $name,
            'brand' => substr($brand, 0, 80),
            'price_usd' => $priceUsd,
            'estimated_price_idr' => $priceIdr,
            'price_tier' => $this->classifyPriceTier($slot, $priceUsd),
            'spec_summary' => implode(' | ', array_filter($summaryParts)),
            'compatibility_specs' => json_encode($specs),
        ];
    }

    private function classifyPriceTier(string $slot, float $usd): string
    {
        [$budgetMax, $midMax] = match ($slot) {
            'cpu' => [135, 310],
            'motherboard' => [115, 230],
            'ram' => [55, 130],
            'gpu' => [230, 550],
            'storage' => [55, 135],
            'cooler' => [38, 95],
            'case' => [68, 135],
            'psu' => [70, 140],
            default => [80, 200],
        };

        if ($usd <= $budgetMax) {
            return 'budget';
        }
        if ($usd <= $midMax) {
            return 'mid';
        }

        return 'high';
    }

    private function mapCpu(string $name, array $item): array
    {
        $arch = (string) ($item['microarchitecture'] ?? '');
        $socket = match ($arch) {
            'Zen 5', 'Zen 4' => 'AM5',
            'Zen 3', 'Zen 2', 'Zen+', 'Zen' => 'AM4',
            'Arrow Lake' => 'LGA1851',
            'Raptor Lake Refresh', 'Raptor Lake', 'Alder Lake' => 'LGA1700',
            'Rocket Lake', 'Comet Lake' => 'LGA1200',
            'Coffee Lake Refresh', 'Coffee Lake', 'Kaby Lake', 'Skylake' => 'LGA1151',
            'Broadwell', 'Haswell Refresh', 'Haswell' => 'LGA1150',
            'Ivy Bridge', 'Sandy Bridge' => 'LGA1155',
            'Cascade Lake' => 'LGA2066',
            'Piledriver', 'Bulldozer' => 'AM3+',
            'Steamroller', 'Lynx' => 'FM2',
            'Wolfdale', 'Yorkfield', 'Core' => 'LGA775',
            default => '',
        };

        if (str_contains($name, 'Threadripper 39')) {
            $socket = 'sTRX4';
        } elseif (str_contains($name, 'Threadripper')) {
            $socket = 'sTR4';
        } elseif ($socket === '' && preg_match('/\b(3000G|200GE)\b/i', $name)) {
            $socket = 'AM4';
        }

        $tdp = max(15, (int) ($item['tdp'] ?? 65));
        $gfx = trim((string) ($item['graphics'] ?? ''));
        $hasIgpu = $gfx !== '' && strtolower($gfx) !== 'none';
        $cores = (int) ($item['core_count'] ?? 0);
        $boost = $item['boost_clock'] ?? $item['core_clock'] ?? null;

        $summary = [];
        if ($socket !== '') {
            $summary[] = 'Soket ' . $socket;
        }
        if ($cores > 0) {
            $summary[] = $cores . ' Core';
        }
        if ($boost) {
            $summary[] = $boost . ' GHz';
        }
        $summary[] = $hasIgpu ? ('iGPU ' . $gfx) : 'Tanpa iGPU';

        return [[
            'socket' => $socket,
            'tdp_watt' => $tdp,
            'has_igpu' => $hasIgpu,
        ], $summary];
    }

    private function mapMotherboard(string $name, array $item): array
    {
        $rawSocket = trim((string) ($item['socket'] ?? ''));
        $socket = match ($rawSocket) {
            'AM3+/AM3' => 'AM3+',
            default => $rawSocket,
        };

        $ramType = match ($socket) {
            'AM5', 'LGA1851' => 'DDR5',
            'LGA1700' => preg_match('/\b(DDR4|D4)\b/i', $name) ? 'DDR4' : 'DDR5',
            'AM4', 'LGA1200', 'sTRX4', 'sTR4', 'LGA2066', 'LGA2011-3' => 'DDR4',
            'LGA1151' => preg_match('/\bDDR3\b/i', $name) ? 'DDR3' : 'DDR4',
            'LGA1150', 'LGA1155', 'LGA1156', 'LGA2011', 'AM3+', 'FM2', 'LGA1366' => 'DDR3',
            'LGA775' => 'DDR3',
            default => '',
        };

        $rawForm = trim((string) ($item['form_factor'] ?? 'ATX'));
        $formFactor = match ($rawForm) {
            'Micro ATX' => 'MATX',
            'Mini ITX', 'Thin Mini ITX', 'Mini DTX' => 'ITX',
            'EATX', 'XL ATX', 'SSI CEB' => 'EATX',
            default => 'ATX',
        };

        $slots = max(1, (int) ($item['memory_slots'] ?? 4));

        return [[
            'socket' => $socket,
            'ram_type' => $ramType,
            'ram_slots' => $slots,
            'form_factor' => $formFactor,
        ], [
            $socket !== '' ? ('Soket ' . $socket) : null,
            $ramType !== '' ? $ramType : null,
            $rawForm,
            $slots . ' Slot RAM',
        ]];
    }

    private function mapMemory(array $item): array
    {
        $speedArr = is_array($item['speed'] ?? null) ? $item['speed'] : [4, 3200];
        $ddrGen = (int) ($speedArr[0] ?? 4);
        $mhz = (int) ($speedArr[1] ?? 3200);
        $ramType = 'DDR' . $ddrGen;

        $modulesArr = is_array($item['modules'] ?? null) ? $item['modules'] : [2, 8];
        $modulesCount = max(1, (int) ($modulesArr[0] ?? 1));
        $perStick = max(1, (int) ($modulesArr[1] ?? 8));
        $totalGb = $modulesCount * $perStick;

        return [[
            'ram_type' => $ramType,
            'modules_count' => $modulesCount,
            'capacity_gb' => $totalGb,
        ], [
            "{$totalGb} GB ({$modulesCount}x{$perStick}GB)",
            "{$ramType} {$mhz} MHz",
            ! empty($item['cas_latency']) ? ('CL' . $item['cas_latency']) : null,
        ]];
    }

    private function mapGpu(string $name, array $item): array
    {
        $chipset = trim((string) ($item['chipset'] ?? $name));
        $vram = (float) ($item['memory'] ?? 0);
        $length = (int) ($item['length'] ?? 0);
        $tdp = $this->estimateGpuTdp($chipset);

        return [[
            'gpu_length_mm' => $length > 0 ? $length : null,
            'tdp_watt' => $tdp,
        ], [
            $chipset,
            $vram > 0 ? ($vram . ' GB VRAM') : null,
            $length > 0 ? ($length . ' mm') : null,
            "~{$tdp}W",
        ]];
    }

    private function estimateGpuTdp(string $chipset): int
    {
        $c = strtoupper($chipset);
        if (str_contains($c, '4090') || str_contains($c, '5090')) {
            return 450;
        }
        if (str_contains($c, '4080') || str_contains($c, '5080') || str_contains($c, '7900 XTX') || str_contains($c, '3090') || str_contains($c, '6950')) {
            return 335;
        }
        if (str_contains($c, '3080') || str_contains($c, '7900') || str_contains($c, '6900') || str_contains($c, '6800 XT') || str_contains($c, '4070 TI') || str_contains($c, '7800 XT')) {
            return 275;
        }
        if (str_contains($c, '4070') || str_contains($c, '3070') || str_contains($c, '6700 XT') || str_contains($c, '6750') || str_contains($c, '7700 XT') || str_contains($c, '2080')) {
            return 215;
        }
        if (str_contains($c, '4060 TI') || str_contains($c, '3060 TI') || str_contains($c, '7600') || str_contains($c, '6650') || str_contains($c, '2070') || str_contains($c, '5700')) {
            return 175;
        }
        if (str_contains($c, '4060') || str_contains($c, '3060') || str_contains($c, '6600') || str_contains($c, '2060') || str_contains($c, '1660') || str_contains($c, '580') || str_contains($c, '5600')) {
            return 135;
        }
        if (str_contains($c, '1650') || str_contains($c, '1050') || str_contains($c, '6500') || str_contains($c, '6400') || str_contains($c, '1030') || str_contains($c, '730') || str_contains($c, '710') || str_contains($c, '550') || str_contains($c, '3050')) {
            return 75;
        }

        return 160;
    }

    private function mapStorage(array $item): array
    {
        $cap = (int) ($item['capacity'] ?? 0);
        $capLabel = $cap >= 1000
            ? (round($cap / 1000, 1) . ' TB')
            : ($cap . ' GB');

        $type = (string) ($item['type'] ?? 'SSD');
        $typeLabel = $type === 'SSD' ? 'SSD' : ('HDD ' . $type . ' RPM');
        $iface = (string) ($item['interface'] ?? '');

        return [[
            'capacity_gb' => $cap,
            'tdp_watt' => $type === 'SSD' ? 6 : 10,
        ], [
            $cap > 0 ? $capLabel : null,
            $typeLabel,
            $iface !== '' ? $iface : null,
        ]];
    }

    private function mapCooler(array $item): array
    {
        $size = $item['size'] ?? null;
        $noise = $item['noise_level'] ?? null;
        $kind = $size ? "Liquid AIO {$size}mm" : 'Pendingin Udara';

        return [[
            'tdp_watt' => $size ? 12 : 5,
        ], [
            $kind,
            is_numeric($noise) ? ($noise . ' dBA') : null,
        ]];
    }

    private function mapCase(array $item): array
    {
        $type = trim((string) ($item['type'] ?? 'ATX Mid Tower'));

        [$supportedForms, $maxGpu] = match ($type) {
            'ATX Full Tower' => [['EATX', 'ATX', 'MATX', 'ITX'], 420],
            'ATX Mid Tower', 'ATX Desktop', 'ATX Test Bench' => [['ATX', 'MATX', 'ITX'], 350],
            'MicroATX Mid Tower', 'MicroATX Mini Tower' => [['MATX', 'ITX'], 320],
            'MicroATX Desktop', 'MicroATX Slim', 'HTPC' => [['MATX', 'ITX'], 260],
            'Mini ITX Tower', 'Mini ITX Desktop', 'Mini ITX Test Bench' => [['ITX'], 290],
            default => [['ATX', 'MATX', 'ITX'], 330],
        };

        return [[
            'supported_form_factors' => $supportedForms,
            'max_gpu_mm' => $maxGpu,
        ], [
            $type,
            ! empty($item['side_panel']) ? (string) $item['side_panel'] : null,
            "Maks VGA ~{$maxGpu}mm",
        ]];
    }

    private function mapPsu(array $item): array
    {
        $watt = max(250, (int) ($item['wattage'] ?? 500));
        $eff = ! empty($item['efficiency']) ? ('80+ ' . ucfirst((string) $item['efficiency'])) : 'Standar';
        $mod = (string) ($item['modular'] ?? 'No');
        $modLabel = match (strtolower($mod)) {
            'full' => 'Full Modular',
            'semi' => 'Semi Modular',
            default => 'Non-Modular',
        };

        return [[
            'psu_watt' => $watt,
        ], [
            $watt . ' Watt',
            $eff,
            $modLabel,
        ]];
    }
}
