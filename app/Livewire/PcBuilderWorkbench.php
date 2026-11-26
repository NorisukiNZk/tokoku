<?php

namespace App\Livewire;

use App\Models\PcBuild;
use App\Models\PcCatalogPart;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Services\PcCompatibilityService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Url;
use Livewire\Component;

class PcBuilderWorkbench extends Component
{
    #[Url(as: 'kode', except: '')]
    public string $shareCode = '';

    #[Url(as: 'pasang', except: 0)]
    public int $preselectProductId = 0;

    /** @var array<string, int|null> */
    public array $selectedIds = [
        'cpu' => null,
        'motherboard' => null,
        'ram' => null,
        'gpu' => null,
        'storage' => null,
        'cooler' => null,
        'case' => null,
        'psu' => null,
    ];

    public ?string $activePickerSlot = null;
    public string $pickerSearch = '';
    public string $pickerTier = 'all';
    public string $pickerSort = 'price_asc';
    public int $pickerLimit = 30;
    public bool $onlyCompatible = true;
    public bool $includeAssembly = true;

    public string $buildName = '';
    public string $customerName = '';
    public ?string $savedShareUrl = null;
    public ?string $loadErrorMessage = null;

    public function mount(): void
    {
        if ($this->shareCode !== '') {
            $this->loadByShareCode($this->shareCode);
        }

        if ($this->preselectProductId > 0) {
            $storeProduct = Product::query()->with('category')->find($this->preselectProductId);
            $slot = $storeProduct?->category?->component_slot;
            if ($storeProduct && $slot && array_key_exists($slot, $this->selectedIds)) {
                $words = array_slice(preg_split('/\s+/', $storeProduct->name) ?: [], 0, 3);
                $keyword = implode(' ', $words);
                $matchedCatalogPart = PcCatalogPart::query()
                    ->where('slot', $slot)
                    ->where('name', 'like', '%' . $keyword . '%')
                    ->orderBy('estimated_price_idr')
                    ->first();

                if ($matchedCatalogPart) {
                    $this->selectedIds[$slot] = $matchedCatalogPart->id;
                } else {
                    $this->activePickerSlot = $slot;
                    $this->pickerSearch = $keyword;
                }
            }
        }
    }

    public function updatedPickerSearch(): void
    {
        $this->pickerLimit = 30;
    }

    public function updatedPickerTier(): void
    {
        $this->pickerLimit = 30;
    }

    public function updatedPickerSort(): void
    {
        $this->pickerLimit = 30;
    }

    public function updatedOnlyCompatible(): void
    {
        $this->pickerLimit = 30;
    }

    public function loadMoreCandidates(): void
    {
        $this->pickerLimit += 30;
    }

    public function loadPreset(string $code): void
    {
        $this->loadErrorMessage = null;
        $this->savedShareUrl = null;
        $this->shareCode = $code;
        $this->loadByShareCode($code);
    }

    public function applySmartTierPreset(string $tier): void
    {
        $this->loadErrorMessage = null;
        $this->savedShareUrl = null;
        $this->shareCode = '';

        $targetSocket = $tier === 'budget' ? 'AM4' : 'AM5';
        $targetRam = $tier === 'budget' ? 'DDR4' : 'DDR5';

        $cpuQuery = PcCatalogPart::query()
            ->where('slot', 'cpu')
            ->where('compatibility_specs->socket', $targetSocket)
            ->where('price_tier', $tier);

        $cpu = (clone $cpuQuery)->orderBy('estimated_price_idr', $tier === 'high' ? 'desc' : 'asc')->first()
            ?? PcCatalogPart::query()->where('slot', 'cpu')->where('compatibility_specs->socket', $targetSocket)->orderBy('estimated_price_idr')->first()
            ?? PcCatalogPart::query()->where('slot', 'cpu')->orderBy('estimated_price_idr')->first();

        $socket = $cpu?->compatibility_specs['socket'] ?? $targetSocket;

        $mobo = PcCatalogPart::query()
            ->where('slot', 'motherboard')
            ->where('compatibility_specs->socket', $socket)
            ->where('compatibility_specs->ram_type', $targetRam)
            ->where('price_tier', $tier)
            ->orderBy('estimated_price_idr')
            ->first()
            ?? PcCatalogPart::query()
                ->where('slot', 'motherboard')
                ->where('compatibility_specs->socket', $socket)
                ->orderBy('estimated_price_idr')
                ->first();

        $ramType = $mobo?->compatibility_specs['ram_type'] ?? $targetRam;

        $ram = PcCatalogPart::query()
            ->where('slot', 'ram')
            ->where('compatibility_specs->ram_type', $ramType)
            ->where('compatibility_specs->capacity_gb', '>=', $tier === 'budget' ? 16 : 32)
            ->orderBy('estimated_price_idr')
            ->first()
            ?? PcCatalogPart::query()->where('slot', 'ram')->where('compatibility_specs->ram_type', $ramType)->orderBy('estimated_price_idr')->first();

        $gpu = PcCatalogPart::query()
            ->where('slot', 'gpu')
            ->where('price_tier', $tier)
            ->orderBy('estimated_price_idr', $tier === 'high' ? 'desc' : 'asc')
            ->first()
            ?? PcCatalogPart::query()->where('slot', 'gpu')->orderBy('estimated_price_idr')->first();

        $storage = PcCatalogPart::query()
            ->where('slot', 'storage')
            ->where('compatibility_specs->capacity_gb', '>=', $tier === 'budget' ? 500 : 1000)
            ->where('spec_summary', 'like', '%SSD%')
            ->orderBy('estimated_price_idr')
            ->first()
            ?? PcCatalogPart::query()->where('slot', 'storage')->orderBy('estimated_price_idr')->first();

        $cooler = PcCatalogPart::query()
            ->where('slot', 'cooler')
            ->where('price_tier', $tier === 'high' ? 'mid' : 'budget')
            ->orderBy('estimated_price_idr')
            ->first()
            ?? PcCatalogPart::query()->where('slot', 'cooler')->orderBy('estimated_price_idr')->first();

        $moboForm = $mobo?->compatibility_specs['form_factor'] ?? 'ATX';
        $gpuLen = (int) ($gpu?->compatibility_specs['gpu_length_mm'] ?? 240);

        $case = PcCatalogPart::query()
            ->where('slot', 'case')
            ->whereJsonContains('compatibility_specs->supported_form_factors', $moboForm)
            ->where('compatibility_specs->max_gpu_mm', '>=', max(280, $gpuLen))
            ->where('price_tier', $tier === 'high' ? 'mid' : 'budget')
            ->orderBy('estimated_price_idr')
            ->first()
            ?? PcCatalogPart::query()->where('slot', 'case')->orderBy('estimated_price_idr')->first();

        $minPsuWatt = $tier === 'high' ? 850 : ($tier === 'mid' ? 650 : 550);
        $psu = PcCatalogPart::query()
            ->where('slot', 'psu')
            ->where('compatibility_specs->psu_watt', '>=', $minPsuWatt)
            ->orderBy('estimated_price_idr')
            ->first()
            ?? PcCatalogPart::query()->where('slot', 'psu')->orderByDesc('estimated_price_idr')->first();

        $this->selectedIds = [
            'cpu' => $cpu?->id,
            'motherboard' => $mobo?->id,
            'ram' => $ram?->id,
            'gpu' => $gpu?->id,
            'storage' => $storage?->id,
            'cooler' => $cooler?->id,
            'case' => $case?->id,
            'psu' => $psu?->id,
        ];
    }

    private function loadByShareCode(string $code): void
    {
        $build = PcBuild::query()->where('share_code', strtoupper(trim($code)))->first();
        if (! $build) {
            $this->loadErrorMessage = "Kode rakitan '{$code}' tidak ditemukan di database. Anda tetap dapat merakit dari awal di bawah ini.";

            return;
        }

        foreach (array_keys($this->selectedIds) as $slot) {
            $this->selectedIds[$slot] = isset($build->selected_components[$slot])
                ? (int) $build->selected_components[$slot]
                : null;
        }

        $this->includeAssembly = (bool) $build->include_assembly_service;
        $this->buildName = $build->build_name;
        $this->customerName = (string) ($build->customer_name ?? '');
    }

    public function clearBuild(): void
    {
        foreach (array_keys($this->selectedIds) as $slot) {
            $this->selectedIds[$slot] = null;
        }
        $this->shareCode = '';
        $this->preselectProductId = 0;
        $this->savedShareUrl = null;
        $this->loadErrorMessage = null;
    }

    public function openSlotPicker(string $slot): void
    {
        if (array_key_exists($slot, $this->selectedIds)) {
            $this->activePickerSlot = $slot;
            $this->pickerSearch = '';
            $this->pickerTier = 'all';
            $this->pickerLimit = 30;
        }
    }

    public function closeSlotPicker(): void
    {
        $this->activePickerSlot = null;
        $this->pickerSearch = '';
    }

    public function chooseComponent(string $slot, int $partId): void
    {
        if (array_key_exists($slot, $this->selectedIds)) {
            $this->selectedIds[$slot] = $partId;
            $this->activePickerSlot = null;
            $this->pickerSearch = '';
            $this->savedShareUrl = null;
        }
    }

    public function removeComponent(string $slot): void
    {
        if (array_key_exists($slot, $this->selectedIds)) {
            $this->selectedIds[$slot] = null;
            $this->savedShareUrl = null;
        }
    }

    public function saveBuild(PcCompatibilityService $compat): void
    {
        $rateKey = 'tokoku:save-build:' . request()->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 10)) {
            $seconds = RateLimiter::availableIn($rateKey);
            $this->addError('buildName', "Terlalu sering menyimpan tautan rakitan. Silakan tunggu {$seconds} detik sebelum membuat link baru.");

            return;
        }

        $this->validate([
            'buildName' => ['required', 'string', 'min:3', 'max:120'],
            'customerName' => ['nullable', 'string', 'max:100'],
        ], [
            'buildName.required' => 'Tuliskan nama rakitan terlebih dahulu (misal: PC Kerja & Gaming 1080p).',
            'buildName.min' => 'Nama rakitan minimal 3 karakter.',
        ]);

        $selectedParts = $this->resolveSelectedParts();
        $hasAnyPart = collect($selectedParts)->filter()->isNotEmpty();

        if (! $hasAnyPart) {
            $this->addError('buildName', 'Pilih minimal satu komponen sebelum menyimpan rakitan.');

            return;
        }

        $store = StoreSetting::current();
        $evaluation = $compat->evaluate($selectedParts);
        $assemblyFee = $this->includeAssembly ? (int) $store->default_assembly_fee : 0;

        $saved = PcBuild::query()->create([
            'build_name' => trim($this->buildName),
            'customer_name' => trim($this->customerName) ?: null,
            'selected_components' => array_filter($this->selectedIds),
            'estimated_wattage' => $evaluation['estimated_wattage'],
            'recommended_psu_wattage' => $evaluation['recommended_psu_wattage'],
            'compatibility_passed' => $evaluation['passed'],
            'compatibility_notes' => array_merge($evaluation['errors'], $evaluation['warnings']),
            'include_assembly_service' => $this->includeAssembly,
            'assembly_fee' => $assemblyFee,
            'parts_subtotal' => $evaluation['parts_subtotal'],
            'grand_total' => $evaluation['parts_subtotal'] + $assemblyFee,
        ]);

        RateLimiter::hit($rateKey, 600);

        $this->shareCode = $saved->share_code;
        $this->savedShareUrl = route('pc-builder', ['kode' => $saved->share_code]);
    }

    /**
     * @return array<string, PcCatalogPart|null>
     */
    private function resolveSelectedParts(): array
    {
        $ids = array_values(array_filter($this->selectedIds));
        $partsById = count($ids) > 0
            ? PcCatalogPart::query()->whereIn('id', $ids)->get()->keyBy('id')
            : collect();

        $resolved = [];
        foreach ($this->selectedIds as $slot => $id) {
            $resolved[$slot] = $id ? ($partsById->get($id) ?? null) : null;
        }

        return $resolved;
    }

    public function render(PcCompatibilityService $compat)
    {
        $store = StoreSetting::current();
        $selectedParts = $this->resolveSelectedParts();
        $evaluation = $compat->evaluate($selectedParts);
        $performanceProfile = $compat->evaluatePerformanceProfile($selectedParts);

        $assemblyFee = $this->includeAssembly ? (int) $store->default_assembly_fee : 0;
        $grandTotal = $evaluation['parts_subtotal'] + $assemblyFee;

        $totalCatalogCount = PcCatalogPart::query()->count();
        $pickerCandidates = collect();
        $pickerTotalMatching = 0;

        if ($this->activePickerSlot) {
            $query = PcCatalogPart::query()->where('slot', $this->activePickerSlot);

            if ($this->pickerSearch !== '') {
                $words = array_filter(preg_split('/\s+/', trim($this->pickerSearch)) ?: []);
                foreach ($words as $w) {
                    $term = '%' . $w . '%';
                    $query->where(function ($sub) use ($term) {
                        $sub->where('name', 'like', $term)
                            ->orWhere('spec_summary', 'like', $term)
                            ->orWhere('brand', 'like', $term);
                    });
                }
            }

            if ($this->pickerTier !== 'all') {
                $query->where('price_tier', $this->pickerTier);
            }

            if ($this->onlyCompatible) {
                $withoutCurrentSlot = $selectedParts;
                $withoutCurrentSlot[$this->activePickerSlot] = null;
                $baseEval = $compat->evaluate($withoutCurrentSlot);
                $compat->applySqlCompatibilityConstraints(
                    $query,
                    $this->activePickerSlot,
                    $selectedParts,
                    $baseEval['estimated_wattage']
                );
            }

            $pickerTotalMatching = (clone $query)->count();

            match ($this->pickerSort) {
                'price_desc' => $query->orderByDesc('estimated_price_idr'),
                'name_asc' => $query->orderBy('name'),
                default => $query->orderBy('estimated_price_idr'),
            };

            $fetched = $query->limit($this->pickerLimit)->get();
            $pickerCandidates = $compat->annotateCandidatesForSlot(
                $this->activePickerSlot,
                $fetched,
                $selectedParts
            );
        }

        $storeInventoryBySlot = Cache::remember('tokoku.builder.store_products', 120, function () {
            return Product::query()
                ->with('category')
                ->where('show_in_marketplace', true)
                ->where('availability_status', 'available')
                ->where('stock', '>', 0)
                ->get()
                ->groupBy(fn (Product $p) => (string) ($p->category?->component_slot ?? ''));
        });

        $storeMatchesByPartId = [];
        foreach ($selectedParts as $slotKey => $partModel) {
            if ($partModel) {
                $slotStoreItems = $storeInventoryBySlot->get($slotKey, collect());
                $matched = $compat->matchStoreInventoryForSlot($slotStoreItems, $partModel);
                if ($matched) {
                    $storeMatchesByPartId[$partModel->id] = $matched;
                }
            }
        }

        if ($this->activePickerSlot) {
            $slotStoreItems = $storeInventoryBySlot->get($this->activePickerSlot, collect());
            foreach ($pickerCandidates as $entry) {
                $candPart = $entry['product'];
                if (! isset($storeMatchesByPartId[$candPart->id])) {
                    $matched = $compat->matchStoreInventoryForSlot($slotStoreItems, $candPart);
                    if ($matched) {
                        $storeMatchesByPartId[$candPart->id] = $matched;
                    }
                }
            }
        }

        $waLines = ["Halo Admin {$store->store_name}, saya ingin konsultasi / pesan rakitan PC dengan rincian berikut:"];
        if ($this->shareCode !== '') {
            $waLines[] = "Kode Simulasi: {$this->shareCode}";
        }
        foreach (PcCompatibilityService::BUILDER_SLOTS as $slotKey => $meta) {
            $p = $selectedParts[$slotKey] ?? null;
            if ($p) {
                $matchedStore = $storeMatchesByPartId[$p->id] ?? null;
                $stockSuffix = $matchedStore ? " [Ready Stok Toko: {$matchedStore->formattedPrice()}]" : '';
                $waLines[] = "- {$meta['label']}: {$p->name} (Est. {$p->formattedPrice()}){$stockSuffix}";
            }
        }
        if ($this->includeAssembly) {
            $waLines[] = "- Jasa Rakit & Manajemen Kabel: Rp " . number_format($assemblyFee, 0, ',', '.');
        }
        $waLines[] = "Estimasi Beban Daya: {$evaluation['estimated_wattage']}W (Saran PSU {$evaluation['recommended_psu_wattage']}W)";
        $waLines[] = "Total Estimasi Anggaran: Rp " . number_format($grandTotal, 0, ',', '.');
        $waLines[] = "";
        $waLines[] = "Mohon info ketersediaan stok toko atau estimasi pemesanan komponennya.";

        $pageTitle = ($this->shareCode !== '' && $this->buildName !== '')
            ? "{$this->buildName} ({$this->shareCode}) - Simulasi Rakit PC {$store->store_name}"
            : 'Simulasi Rakit PC & Harga Part Lengkap - ' . $store->store_name;

        $chosenSummaryNames = collect($selectedParts)->filter()->map(fn ($p) => $p->name)->take(3)->implode(', ');
        $metaDescription = $chosenSummaryNames !== ''
            ? 'Rakitan PC (' . $chosenSummaryNames . ') dengan total estimasi Rp ' . number_format($grandTotal, 0, ',', '.') . ' dan daya ' . $evaluation['estimated_wattage'] . 'W di ' . $store->store_name . '.'
            : "Susun rakitan PC dari {$totalCatalogCount} part referensi dengan diagram casing interaktif, cek kecocokan soket/RAM/PSU otomatis, dan penilai performa di {$store->store_name}.";

        return view('livewire.pc-builder-workbench', [
            'store' => $store,
            'slots' => PcCompatibilityService::BUILDER_SLOTS,
            'selectedProducts' => $selectedParts,
            'storeMatchesByPartId' => $storeMatchesByPartId,
            'evaluation' => $evaluation,
            'performanceProfile' => $performanceProfile,
            'assemblyFee' => $assemblyFee,
            'grandTotal' => $grandTotal,
            'totalCatalogCount' => $totalCatalogCount,
            'pickerCandidates' => $pickerCandidates,
            'pickerTotalMatching' => $pickerTotalMatching,
            'whatsappBuildUrl' => $store->whatsappUrl(implode("\n", $waLines)),
        ])
            ->title($pageTitle)
            ->layoutData([
                'metaDescription' => $metaDescription,
                'canonicalUrl' => $this->shareCode !== ''
                    ? route('pc-builder', ['kode' => $this->shareCode])
                    : route('pc-builder'),
            ]);
    }
}
