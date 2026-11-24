<?php

namespace App\Livewire;

use App\Models\PcBuild;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Services\PcCompatibilityService;
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
            $product = Product::query()->with('category')->find($this->preselectProductId);
            $slot = $product?->category?->component_slot;
            if ($product && $slot && array_key_exists($slot, $this->selectedIds)) {
                $this->selectedIds[$slot] = $product->id;
            }
        }
    }

    public function loadPreset(string $code): void
    {
        $this->loadErrorMessage = null;
        $this->savedShareUrl = null;
        $this->shareCode = $code;
        $this->loadByShareCode($code);
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
        }
    }

    public function closeSlotPicker(): void
    {
        $this->activePickerSlot = null;
        $this->pickerSearch = '';
    }

    public function chooseComponent(string $slot, int $productId): void
    {
        if (array_key_exists($slot, $this->selectedIds)) {
            $this->selectedIds[$slot] = $productId;
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
        $this->validate([
            'buildName' => ['required', 'string', 'min:3', 'max:120'],
            'customerName' => ['nullable', 'string', 'max:100'],
        ], [
            'buildName.required' => 'Tuliskan nama rakitan terlebih dahulu (misal: PC Kerja & Gaming 1080p).',
            'buildName.min' => 'Nama rakitan minimal 3 karakter.',
        ]);

        $selectedProducts = $this->resolveSelectedProducts();
        $hasAnyPart = collect($selectedProducts)->filter()->isNotEmpty();

        if (! $hasAnyPart) {
            $this->addError('buildName', 'Pilih minimal satu komponen sebelum menyimpan rakitan.');

            return;
        }

        $store = StoreSetting::current();
        $evaluation = $compat->evaluate($selectedProducts);
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

        $this->shareCode = $saved->share_code;
        $this->savedShareUrl = route('pc-builder', ['kode' => $saved->share_code]);
    }

    /**
     * @return array<string, Product|null>
     */
    private function resolveSelectedProducts(): array
    {
        $ids = array_values(array_filter($this->selectedIds));
        $productsById = count($ids) > 0
            ? Product::query()->with('category')->whereIn('id', $ids)->get()->keyBy('id')
            : collect();

        $resolved = [];
        foreach ($this->selectedIds as $slot => $id) {
            $resolved[$slot] = $id ? ($productsById->get($id) ?? null) : null;
        }

        return $resolved;
    }

    public function render(PcCompatibilityService $compat)
    {
        $store = StoreSetting::current();
        $selectedProducts = $this->resolveSelectedProducts();
        $evaluation = $compat->evaluate($selectedProducts);

        $assemblyFee = $this->includeAssembly ? (int) $store->default_assembly_fee : 0;
        $grandTotal = $evaluation['parts_subtotal'] + $assemblyFee;

        $pickerCandidates = collect();
        if ($this->activePickerSlot) {
            $query = Product::query()
                ->with('category')
                ->where('show_in_pc_builder', true)
                ->where('availability_status', 'available')
                ->where('stock', '>', 0)
                ->whereHas('category', fn ($q) => $q->where('component_slot', $this->activePickerSlot));

            if ($this->pickerSearch !== '') {
                $term = '%' . trim($this->pickerSearch) . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('sku', 'like', $term)
                        ->orWhere('brand', 'like', $term);
                });
            }

            $annotated = $compat->annotateCandidatesForSlot(
                $this->activePickerSlot,
                $query->orderBy('price')->get(),
                $selectedProducts
            );

            $pickerCandidates = $this->onlyCompatible
                ? $annotated->where('compatible', true)->values()
                : $annotated->values();
        }

        $waLines = ["Halo Admin {$store->store_name}, saya ingin konsultasi / memesan rakitan PC berikut:"];
        if ($this->shareCode !== '') {
            $waLines[] = "Kode Simulasi: {$this->shareCode}";
        }
        foreach (PcCompatibilityService::BUILDER_SLOTS as $slotKey => $meta) {
            $p = $selectedProducts[$slotKey] ?? null;
            if ($p) {
                $waLines[] = "- {$meta['label']}: {$p->name} ({$p->conditionShortLabel()}) = {$p->formattedPrice()}";
            }
        }
        if ($this->includeAssembly) {
            $waLines[] = "- Jasa Rakit & Manajemen Kabel: Rp " . number_format($assemblyFee, 0, ',', '.');
        }
        $waLines[] = "Estimasi Beban Daya: {$evaluation['estimated_wattage']}W (Rekomendasi PSU {$evaluation['recommended_psu_wattage']}W)";
        $waLines[] = "Total Estimasi: Rp " . number_format($grandTotal, 0, ',', '.');
        $waLines[] = "";
        $waLines[] = "Mohon cek ketersediaan stok fisik unitnya.";

        return view('livewire.pc-builder-workbench', [
            'store' => $store,
            'slots' => PcCompatibilityService::BUILDER_SLOTS,
            'selectedProducts' => $selectedProducts,
            'evaluation' => $evaluation,
            'assemblyFee' => $assemblyFee,
            'grandTotal' => $grandTotal,
            'pickerCandidates' => $pickerCandidates,
            'whatsappBuildUrl' => $store->whatsappUrl(implode("\n", $waLines)),
        ])->title('Simulasi Rakit PC & Cek Kompatibilitas - ' . $store->store_name);
    }
}
