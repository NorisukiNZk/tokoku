<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\PcCompatibilityService;
use PHPUnit\Framework\TestCase;

class PcCompatibilityServiceTest extends TestCase
{
    public function test_detects_cpu_and_motherboard_socket_mismatch(): void
    {
        $service = new PcCompatibilityService();

        $cpu = new Product([
            'name' => 'Ryzen 5 7500F',
            'price' => 2500000,
            'compatibility_specs' => ['socket' => 'AM5', 'tdp_watt' => 65, 'has_igpu' => false],
        ]);
        $mobo = new Product([
            'name' => 'MSI B550M',
            'price' => 1700000,
            'compatibility_specs' => ['socket' => 'AM4', 'ram_type' => 'DDR4', 'ram_slots' => 4, 'form_factor' => 'MATX'],
        ]);

        $result = $service->evaluate([
            'cpu' => $cpu,
            'motherboard' => $mobo,
        ]);

        $this->assertFalse($result['passed']);
        $this->assertStringContainsString('Soket tidak cocok', $result['errors'][0]);
    }

    public function test_detects_gpu_length_and_psu_shortage(): void
    {
        $service = new PcCompatibilityService();

        $cpu = new Product([
            'name' => 'Core i5-12400',
            'price' => 1950000,
            'compatibility_specs' => ['socket' => 'LGA1700', 'tdp_watt' => 65, 'has_igpu' => true],
        ]);
        $gpu = new Product([
            'name' => 'RTX 3080 Trio',
            'price' => 5450000,
            'compatibility_specs' => ['gpu_length_mm' => 323, 'tdp_watt' => 340],
        ]);
        $case = new Product([
            'name' => 'Slim Case',
            'price' => 235000,
            'compatibility_specs' => ['supported_form_factors' => ['MATX'], 'max_gpu_mm' => 250],
        ]);
        $psu = new Product([
            'name' => 'PSU 400W',
            'price' => 400000,
            'compatibility_specs' => ['psu_watt' => 400],
        ]);

        $result = $service->evaluate([
            'cpu' => $cpu,
            'gpu' => $gpu,
            'case' => $case,
            'psu' => $psu,
        ]);

        $this->assertFalse($result['passed']);
        $this->assertCount(2, $result['errors']);
    }

    public function test_valid_build_passes_and_calculates_subtotal(): void
    {
        $service = new PcCompatibilityService();

        $result = $service->evaluate([
            'cpu' => new Product([
                'name' => 'Ryzen 5 5600',
                'price' => 1890000,
                'compatibility_specs' => ['socket' => 'AM4', 'tdp_watt' => 65, 'has_igpu' => false],
            ]),
            'motherboard' => new Product([
                'name' => 'B550M PRO-VDH',
                'price' => 1765000,
                'compatibility_specs' => ['socket' => 'AM4', 'ram_type' => 'DDR4', 'ram_slots' => 4, 'form_factor' => 'MATX'],
            ]),
            'ram' => new Product([
                'name' => '16GB DDR4',
                'price' => 640000,
                'compatibility_specs' => ['ram_type' => 'DDR4', 'modules_count' => 2],
            ]),
            'gpu' => new Product([
                'name' => 'RX 6600',
                'price' => 2150000,
                'compatibility_specs' => ['gpu_length_mm' => 200, 'tdp_watt' => 132],
            ]),
            'case' => new Product([
                'name' => 'Flatline mATX',
                'price' => 580000,
                'compatibility_specs' => ['supported_form_factors' => ['MATX', 'ITX'], 'max_gpu_mm' => 340],
            ]),
            'psu' => new Product([
                'name' => 'FSP 550W',
                'price' => 695000,
                'compatibility_specs' => ['psu_watt' => 550],
            ]),
        ]);

        $this->assertTrue($result['passed']);
        $this->assertSame(7720000, $result['parts_subtotal']);
    }
}
