@php
    $requiredKeys = collect($slots)->filter(fn ($m) => $m['required'])->keys();
    $filledRequiredCount = $requiredKeys->filter(fn ($k) => !empty($selectedProducts[$k]))->count();
    $totalRequiredCount = $requiredKeys->count();
    $filledTotalCount = collect($selectedProducts)->filter()->count();
    $powerPercent = min(100, max(12, (int) round(($evaluation['estimated_wattage'] / max(1, $evaluation['recommended_psu_wattage'])) * 100)));

    $shortPart = static function ($part, int $max = 20): string {
        if (! $part) {
            return 'Kosong (klik pilih)';
        }
        $name = trim((string) $part->name);
        return mb_strlen($name) > $max ? mb_substr($name, 0, $max - 1) . '…' : $name;
    };

    $hasCase = ! empty($selectedProducts['case']);
    $hasMobo = ! empty($selectedProducts['motherboard']);
    $hasCpu = ! empty($selectedProducts['cpu']);
    $hasCooler = ! empty($selectedProducts['cooler']);
    $hasRam = ! empty($selectedProducts['ram']);
    $hasStorage = ! empty($selectedProducts['storage']);
    $hasGpu = ! empty($selectedProducts['gpu']);
    $hasPsu = ! empty($selectedProducts['psu']);
@endphp
<div
    x-data="{ mobileStudioOpen: false }"
    @keydown.escape.window="$wire.closeSlotPicker()"
    class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-4 sm:py-8 pb-28 lg:pb-8 space-y-4 sm:space-y-6"
>
    <section aria-label="Studio Diagram Casing PC Interaktif dan Penilai Performa" class="surface-ink rounded-2xl p-3.5 sm:p-6 space-y-4 sm:space-y-5 no-print">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3.5 sm:gap-4 border-b border-[var(--ink-panel-border)] pb-3.5 sm:pb-4">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="font-display text-xl sm:text-3xl font-extrabold tracking-tight">Simulasi Rakit PC &amp; Diagram Casing</h1>
                    <span class="px-2.5 py-0.5 rounded-md bg-[#FB923C] text-[#0F172A] font-display font-bold text-xs tabular-nums">
                        {{ number_format($totalCatalogCount, 0, ',', '.') }} Part Tersedia
                    </span>
                </div>
                <p class="text-xs sm:text-sm ink-muted max-w-3xl">
                    Pilih komponen per slot atau pakai paket cepat di bawah. Kurs acuan Rp {{ number_format($store->usd_to_idr_rate ?: 16500, 0, ',', '.') }}/USD dengan cek soket, RAM, ukuran VGA, dan daya PSU otomatis.
                </p>
            </div>

            <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 shrink-0 no-print">
                <button
                    type="button"
                    wire:click="applySmartTierPreset('budget')"
                    @click="$store.tokoku.notify('Menyusun paket kelas hemat...')"
                    class="btn-ink-outline min-h-11 px-3 rounded-lg text-xs cursor-pointer text-center"
                >
                    Paket Hemat
                </button>
                <button
                    type="button"
                    wire:click="applySmartTierPreset('mid')"
                    @click="$store.tokoku.notify('Menyusun paket kelas menengah...')"
                    class="btn-ink-outline min-h-11 px-3 rounded-lg text-xs cursor-pointer text-center"
                >
                    Paket Menengah
                </button>
                <button
                    type="button"
                    wire:click="applySmartTierPreset('high')"
                    @click="$store.tokoku.notify('Menyusun paket kelas high-end...')"
                    class="btn-ink-accent min-h-11 px-3 rounded-lg text-xs cursor-pointer text-center"
                >
                    Paket High-End
                </button>
                <button
                    type="button"
                    wire:click="clearBuild"
                    @click="$store.tokoku.notify('Semua slot rakitan dikosongkan')"
                    class="btn-ink-outline min-h-11 px-3 rounded-lg text-xs cursor-pointer text-center"
                >
                    Reset
                </button>
            </div>
        </div>

        {{-- Mobile Expand/Collapse Toggle so 8 Slots appear immediately on phone screens --}}
        <div class="lg:hidden flex items-center justify-between gap-2 surface-ink-card rounded-xl px-3.5 py-2.5">
            <div class="min-w-0">
                <p class="font-display font-bold text-xs text-[#FDBA74] truncate">{{ $performanceProfile['tier_label'] }}</p>
                <p class="text-[11px] ink-muted tabular-nums">{{ $filledTotalCount }}/8 slot terpasang &middot; {{ $evaluation['estimated_wattage'] }}W</p>
            </div>
            <button
                type="button"
                @click="mobileStudioOpen = !mobileStudioOpen"
                :aria-expanded="mobileStudioOpen.toString()"
                class="btn-ink-outline min-h-10 px-3 rounded-lg text-xs shrink-0 cursor-pointer"
            >
                <span x-text="mobileStudioOpen ? 'Tutup Diagram' : 'Buka Diagram'">Buka Diagram</span>
            </button>
        </div>

        <div :class="mobileStudioOpen ? 'grid' : 'hidden lg:grid'" class="grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5 items-stretch">
            <div class="lg:col-span-7 surface-ink-card rounded-xl p-4 flex flex-col justify-between gap-4">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <h2 class="font-display font-bold text-sm sm:text-base">Diagram Casing PC Interaktif</h2>
                        <p class="text-xs ink-muted">Zona menyala otomatis saat komponen dipasang. Klik zona mana saja untuk memilih atau mengganti part.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-md bg-[#0F172A] border border-[var(--ink-panel-control-border)] text-xs font-semibold tabular-nums shrink-0">
                        {{ $filledTotalCount }}/8 Terpasang
                    </span>
                </div>

                <div class="w-full rounded-xl bg-[#0B1220] border border-[var(--ink-panel-border)] p-2 sm:p-3 overflow-hidden">
                    <svg viewBox="0 0 560 336" class="w-full h-auto block select-none" role="img" aria-label="Diagram interaktif susunan komponen di dalam casing PC">
                        <defs>
                            <pattern id="tk-blueprint-grid" width="20" height="20" patternUnits="userSpaceOnUse">
                                <path d="M 20 0 L 0 0 0 20" fill="none" stroke="#1E293B" stroke-width="0.7" />
                            </pattern>
                        </defs>
                        <rect x="0" y="0" width="560" height="336" rx="10" fill="#0B1220" />
                        <rect x="0" y="0" width="560" height="336" rx="10" fill="url(#tk-blueprint-grid)" />

                        {{-- CASE OUTER SHELL --}}
                        <g
                            class="chassis-zone"
                            wire:click="openSlotPicker('case')"
                            @keydown.enter.prevent="$wire.openSlotPicker('case')"
                            @keydown.space.prevent="$wire.openSlotPicker('case')"
                            role="button"
                            tabindex="0"
                            aria-label="Pilih Casing Komputer: {{ $selectedProducts['case']?->name ?? 'Belum dipilih' }}"
                        >
                            <rect x="28" y="16" width="504" height="304" rx="16" fill="{{ $hasCase ? '#131F35' : '#0F172A' }}" stroke="{{ $hasCase ? '#34D399' : '#64748B' }}" stroke-width="{{ $hasCase ? '3' : '2' }}" stroke-dasharray="{{ $hasCase ? 'none' : '8 5' }}" />
                            {{-- Feet --}}
                            <rect x="64" y="320" width="46" height="10" rx="3" fill="{{ $hasCase ? '#34D399' : '#475569' }}" />
                            <rect x="450" y="320" width="46" height="10" rx="3" fill="{{ $hasCase ? '#34D399' : '#475569' }}" />
                            {{-- Front Mesh Intake Fans --}}
                            <rect x="456" y="42" width="56" height="252" rx="8" fill="#0F172A" stroke="#334155" stroke-width="1.5" />
                            <circle cx="484" cy="88" r="21" fill="none" stroke="{{ $hasCase ? '#FB923C' : '#475569' }}" stroke-width="2" />
                            <circle cx="484" cy="88" r="7" fill="{{ $hasCase ? '#FB923C' : '#475569' }}" />
                            <circle cx="484" cy="148" r="21" fill="none" stroke="{{ $hasCase ? '#FB923C' : '#475569' }}" stroke-width="2" />
                            <circle cx="484" cy="148" r="7" fill="{{ $hasCase ? '#FB923C' : '#475569' }}" />
                            <circle cx="484" cy="208" r="21" fill="none" stroke="{{ $hasCase ? '#FB923C' : '#475569' }}" stroke-width="2" />
                            <circle cx="484" cy="208" r="7" fill="{{ $hasCase ? '#FB923C' : '#475569' }}" />
                            <text x="434" y="34" text-anchor="end" font-family="Inter, sans-serif" font-size="10" font-weight="700" fill="{{ $hasCase ? '#A7F3D0' : '#CBD5E1' }}">
                                CASING: {{ $shortPart($selectedProducts['case'], 24) }}
                            </text>
                        </g>

                        {{-- TOP COOLER / RADIATOR ZONE --}}
                        <g
                            class="chassis-zone"
                            wire:click="openSlotPicker('cooler')"
                            @keydown.enter.prevent="$wire.openSlotPicker('cooler')"
                            @keydown.space.prevent="$wire.openSlotPicker('cooler')"
                            role="button"
                            tabindex="0"
                            aria-label="Pilih Pendingin CPU: {{ $selectedProducts['cooler']?->name ?? 'Belum dipilih' }}"
                        >
                            <rect x="78" y="28" width="224" height="28" rx="6" fill="{{ $hasCooler ? '#2C190E' : '#1E293B' }}" stroke="{{ $hasCooler ? '#FB923C' : '#64748B' }}" stroke-width="2" stroke-dasharray="{{ $hasCooler ? 'none' : '5 4' }}" />
                            <text x="90" y="46" font-family="Inter, sans-serif" font-size="10" font-weight="700" fill="{{ $hasCooler ? '#FDBA74' : '#CBD5E1' }}">
                                COOLER: {{ $shortPart($selectedProducts['cooler'], 24) }}
                            </text>
                        </g>

                        {{-- MOTHERBOARD TRAY ZONE --}}
                        <g
                            class="chassis-zone"
                            wire:click="openSlotPicker('motherboard')"
                            @keydown.enter.prevent="$wire.openSlotPicker('motherboard')"
                            @keydown.space.prevent="$wire.openSlotPicker('motherboard')"
                            role="button"
                            tabindex="0"
                            aria-label="Pilih Motherboard: {{ $selectedProducts['motherboard']?->name ?? 'Belum dipilih' }}"
                        >
                            <rect x="54" y="64" width="382" height="178" rx="10" fill="{{ $hasMobo ? '#17253D' : '#111B2E' }}" stroke="{{ $hasMobo ? '#38BDF8' : '#64748B' }}" stroke-width="2" stroke-dasharray="{{ $hasMobo ? 'none' : '6 4' }}" />
                            {{-- Rear I/O block --}}
                            <rect x="64" y="76" width="22" height="72" rx="4" fill="#0F172A" stroke="{{ $hasMobo ? '#38BDF8' : '#475569' }}" stroke-width="1.2" />
                            <text x="64" y="233" font-family="Inter, sans-serif" font-size="10" font-weight="700" fill="{{ $hasMobo ? '#BAE6FD' : '#CBD5E1' }}">
                                MOTHERBOARD: {{ $shortPart($selectedProducts['motherboard'], 38) }}
                            </text>
                        </g>

                        {{-- CPU SOCKET ZONE --}}
                        <g
                            class="chassis-zone"
                            wire:click="openSlotPicker('cpu')"
                            @keydown.enter.prevent="$wire.openSlotPicker('cpu')"
                            @keydown.space.prevent="$wire.openSlotPicker('cpu')"
                            role="button"
                            tabindex="0"
                            aria-label="Pilih Prosesor CPU: {{ $selectedProducts['cpu']?->name ?? 'Belum dipilih' }}"
                        >
                            <rect x="116" y="76" width="124" height="68" rx="8" fill="{{ $hasCpu ? '#2C190E' : '#1E293B' }}" stroke="{{ $hasCpu ? '#FB923C' : '#8FA0B8' }}" stroke-width="2.5" stroke-dasharray="{{ $hasCpu ? 'none' : '5 4' }}" />
                            <rect x="128" y="88" width="24" height="24" rx="4" fill="{{ $hasCpu ? '#FB923C' : '#334155' }}" />
                            <text x="160" y="99" font-family="Inter, sans-serif" font-size="10" font-weight="800" fill="{{ $hasCpu ? '#FDBA74' : '#F8FAFC' }}">CPU</text>
                            <text x="160" y="113" font-family="Inter, sans-serif" font-size="9.5" fill="{{ $hasCpu ? '#F8FAFC' : '#CBD5E1' }}">
                                {{ $selectedProducts['cpu']?->compatibility_specs['socket'] ?? 'Soket' }}
                            </text>
                            <text x="124" y="134" font-family="Inter, sans-serif" font-size="9.5" font-weight="600" fill="{{ $hasCpu ? '#F8FAFC' : '#CBD5E1' }}">
                                {{ $shortPart($selectedProducts['cpu'], 18) }}
                            </text>
                        </g>

                        {{-- RAM DIMM SLOTS ZONE --}}
                        <g
                            class="chassis-zone"
                            wire:click="openSlotPicker('ram')"
                            @keydown.enter.prevent="$wire.openSlotPicker('ram')"
                            @keydown.space.prevent="$wire.openSlotPicker('ram')"
                            role="button"
                            tabindex="0"
                            aria-label="Pilih Memori RAM: {{ $selectedProducts['ram']?->name ?? 'Belum dipilih' }}"
                        >
                            <rect x="256" y="74" width="166" height="72" rx="8" fill="{{ $hasRam ? '#132B24' : '#1E293B' }}" stroke="{{ $hasRam ? '#34D399' : '#8FA0B8' }}" stroke-width="2" stroke-dasharray="{{ $hasRam ? 'none' : '5 4' }}" />
                            <rect x="268" y="84" width="10" height="52" rx="2" fill="{{ $hasRam ? '#34D399' : '#475569' }}" />
                            <rect x="284" y="84" width="10" height="52" rx="2" fill="{{ $hasRam ? '#10B981' : '#334155' }}" />
                            <text x="304" y="100" font-family="Inter, sans-serif" font-size="10" font-weight="800" fill="{{ $hasRam ? '#A7F3D0' : '#F8FAFC' }}">MEMORI RAM</text>
                            <text x="304" y="115" font-family="Inter, sans-serif" font-size="9.5" fill="#F8FAFC">
                                {{ $hasRam ? (($selectedProducts['ram']->compatibility_specs['ram_type'] ?? 'DDR') . ' · ' . ($selectedProducts['ram']->compatibility_specs['capacity_gb'] ?? '?') . ' GB') : 'Klik pilih RAM' }}
                            </text>
                            <text x="304" y="131" font-family="Inter, sans-serif" font-size="9" fill="#CBD5E1">
                                {{ $shortPart($selectedProducts['ram'], 18) }}
                            </text>
                        </g>

                        {{-- STORAGE NVME / SSD ZONE --}}
                        <g
                            class="chassis-zone"
                            wire:click="openSlotPicker('storage')"
                            @keydown.enter.prevent="$wire.openSlotPicker('storage')"
                            @keydown.space.prevent="$wire.openSlotPicker('storage')"
                            role="button"
                            tabindex="0"
                            aria-label="Pilih Penyimpanan SSD HDD: {{ $selectedProducts['storage']?->name ?? 'Belum dipilih' }}"
                        >
                            <rect x="268" y="256" width="168" height="50" rx="8" fill="{{ $hasStorage ? '#132B24' : '#1E293B' }}" stroke="{{ $hasStorage ? '#34D399' : '#8FA0B8' }}" stroke-width="2" stroke-dasharray="{{ $hasStorage ? 'none' : '5 4' }}" />
                            <text x="280" y="276" font-family="Inter, sans-serif" font-size="10" font-weight="800" fill="{{ $hasStorage ? '#A7F3D0' : '#F8FAFC' }}">SSD / STORAGE</text>
                            <text x="280" y="294" font-family="Inter, sans-serif" font-size="9.5" fill="#CBD5E1">
                                {{ $shortPart($selectedProducts['storage'], 24) }}
                            </text>
                        </g>

                        {{-- GPU / VGA PCIE BAY ZONE --}}
                        <g
                            class="chassis-zone"
                            wire:click="openSlotPicker('gpu')"
                            @keydown.enter.prevent="$wire.openSlotPicker('gpu')"
                            @keydown.space.prevent="$wire.openSlotPicker('gpu')"
                            role="button"
                            tabindex="0"
                            aria-label="Pilih Kartu Grafis VGA: {{ $selectedProducts['gpu']?->name ?? 'Belum dipilih' }}"
                        >
                            <rect x="74" y="158" width="348" height="58" rx="9" fill="{{ $hasGpu ? '#2C190E' : '#1E293B' }}" stroke="{{ $hasGpu ? '#FB923C' : '#8FA0B8' }}" stroke-width="2.5" stroke-dasharray="{{ $hasGpu ? 'none' : '6 4' }}" />
                            <circle cx="110" cy="187" r="17" fill="#0F172A" stroke="{{ $hasGpu ? '#FB923C' : '#64748B' }}" stroke-width="2" />
                            <circle cx="110" cy="187" r="5" fill="{{ $hasGpu ? '#FB923C' : '#64748B' }}" />
                            <circle cx="154" cy="187" r="17" fill="#0F172A" stroke="{{ $hasGpu ? '#FB923C' : '#64748B' }}" stroke-width="2" />
                            <circle cx="154" cy="187" r="5" fill="{{ $hasGpu ? '#FB923C' : '#64748B' }}" />
                            <text x="184" y="180" font-family="Inter, sans-serif" font-size="10.5" font-weight="800" fill="{{ $hasGpu ? '#FDBA74' : '#F8FAFC' }}">KARTU GRAFIS (VGA / GPU)</text>
                            <text x="184" y="198" font-family="Inter, sans-serif" font-size="10" font-weight="600" fill="#F8FAFC">
                                {{ $shortPart($selectedProducts['gpu'], 32) }}
                            </text>
                        </g>

                        {{-- PSU SHROUD ZONE --}}
                        <g
                            class="chassis-zone"
                            wire:click="openSlotPicker('psu')"
                            @keydown.enter.prevent="$wire.openSlotPicker('psu')"
                            @keydown.space.prevent="$wire.openSlotPicker('psu')"
                            role="button"
                            tabindex="0"
                            aria-label="Pilih Power Supply PSU: {{ $selectedProducts['psu']?->name ?? 'Belum dipilih' }}"
                        >
                            <rect x="54" y="256" width="198" height="50" rx="8" fill="{{ $hasPsu ? '#2C190E' : '#1E293B' }}" stroke="{{ $hasPsu ? '#FB923C' : '#8FA0B8' }}" stroke-width="2" stroke-dasharray="{{ $hasPsu ? 'none' : '5 4' }}" />
                            <text x="68" y="276" font-family="Inter, sans-serif" font-size="10" font-weight="800" fill="{{ $hasPsu ? '#FDBA74' : '#F8FAFC' }}">
                                PSU {{ $hasPsu && !empty($selectedProducts['psu']->compatibility_specs['psu_watt']) ? '(' . $selectedProducts['psu']->compatibility_specs['psu_watt'] . 'W)' : '' }}
                            </text>
                            <text x="68" y="294" font-family="Inter, sans-serif" font-size="9.5" fill="#CBD5E1">
                                {{ $shortPart($selectedProducts['psu'], 26) }}
                            </text>
                        </g>
                    </svg>
                </div>

                <div class="flex flex-wrap gap-1.5 no-print" aria-label="Pintasan Slot Casing">
                    @foreach($slots as $sKey => $sMeta)
                        @php $isFilled = !empty($selectedProducts[$sKey]); @endphp
                        <button
                            type="button"
                            wire:click="openSlotPicker('{{ $sKey }}')"
                            class="min-h-10 px-2.5 rounded-lg text-xs inline-flex items-center gap-1.5 cursor-pointer transition-colors {{ $isFilled ? 'bg-[#2C190E] text-[#FDBA74] border border-[#FB923C] font-semibold' : 'btn-ink-outline' }}"
                        >
                            <span class="w-2 h-2 rounded-full {{ $isFilled ? 'bg-[#34D399]' : 'bg-[#64748B]' }}" aria-hidden="true"></span>
                            <span>{{ explode(' ', $sMeta['label'])[0] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="lg:col-span-5 surface-ink-card rounded-xl p-4 sm:p-5 flex flex-col justify-between gap-5">
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-[#FDBA74] uppercase tracking-wide">Penilai Kelas Performa</p>
                            <h2 class="font-display font-extrabold text-lg sm:text-xl mt-0.5">{{ $performanceProfile['tier_label'] }}</h2>
                        </div>
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold shrink-0 {{ $evaluation['passed'] ? 'bg-[#064E3B] text-[#A7F3D0] border border-[#10B981]' : 'bg-[#450A0A] text-[#FECACA] border border-[#EF4444]' }}">
                            {{ $evaluation['passed'] ? 'Cocok' : 'Ada Bentrok' }}
                        </span>
                    </div>

                    <p class="text-xs sm:text-sm ink-muted leading-relaxed">
                        {{ $performanceProfile['summary'] }}
                    </p>

                    <div class="space-y-3 pt-1">
                        @foreach([
                            ['label' => 'Kerja & Multitasking', 'score' => $performanceProfile['office_score'], 'color' => '#38BDF8'],
                            ['label' => 'Gaming Modern', 'score' => $performanceProfile['gaming_score'], 'color' => '#FB923C'],
                            ['label' => 'Editing & Render', 'score' => $performanceProfile['creator_score'], 'color' => '#34D399'],
                        ] as $meter)
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-medium">{{ $meter['label'] }}</span>
                                    <span class="font-display font-bold tabular-nums">{{ $meter['score'] }} / 100</span>
                                </div>
                                <div class="w-full h-2.5 rounded-full bg-[#0F172A] border border-[#334155] overflow-hidden" aria-hidden="true">
                                    <div class="h-full rounded-full transition-all duration-300" style="width: {{ $meter['score'] }}%; background-color: {{ $meter['color'] }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(!empty($performanceProfile['notes']))
                        <ul class="space-y-1.5 text-xs ink-muted pt-1 border-t border-[var(--ink-panel-border)]">
                            @foreach($performanceProfile['notes'] as $note)
                                <li class="pt-1">&bull; {{ $note }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="rounded-xl bg-[#0F172A] border border-[var(--ink-panel-border)] p-3.5 flex items-center justify-between gap-3">
                    <div>
                        <span class="block text-[11px] ink-muted">Total Estimasi Rakitan</span>
                        <span class="font-display font-extrabold text-xl text-[#FDBA74] tabular-nums">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="text-right">
                        <span class="block text-[11px] ink-muted">Beban / Saran PSU</span>
                        <span class="font-display font-bold text-sm tabular-nums">{{ $evaluation['estimated_wattage'] }}W / {{ $evaluation['recommended_psu_wattage'] }}W</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($loadErrorMessage)
        <div class="badge-err rounded-xl p-4 text-sm font-medium" role="alert">
            {{ $loadErrorMessage }}
        </div>
    @endif

    <div wire:loading.delay class="surface-subtle rounded-xl p-3.5 text-sm font-medium no-print" role="status" aria-live="polite">
        Memproses daftar komponen dan kecocokan spesifikasi...
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start no-print">
        <section aria-label="Urutan Pemilihan Komponen PC" class="lg:col-span-8 space-y-3.5">
            <div class="surface-subtle rounded-xl px-4 py-3 flex items-center justify-between gap-4 text-sm">
                <span class="font-semibold">Kelengkapan Komponen Utama</span>
                <span class="font-display font-bold tabular-nums">{{ $filledRequiredCount }} dari {{ $totalRequiredCount }} terisi</span>
            </div>

            @foreach($slots as $slotKey => $meta)
                @php
                    /** @var \App\Models\PcCatalogPart|null $chosen */
                    $chosen = $selectedProducts[$slotKey] ?? null;
                    /** @var \App\Models\Product|null $matchedStoreItem */
                    $matchedStoreItem = $chosen ? ($storeMatchesByPartId[$chosen->id] ?? null) : null;
                @endphp
                <div class="surface-card rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors">
                    <div class="flex items-start gap-3.5 flex-1 min-w-0">
                        <span class="w-8 h-8 rounded-lg {{ $chosen ? 'btn-accent' : 'surface-subtle text-muted-ink' }} inline-flex items-center justify-center font-display text-xs font-bold shrink-0 mt-0.5">
                            {{ $loop->iteration }}
                        </span>

                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-display font-bold text-sm sm:text-base">{{ $meta['label'] }}</h2>
                                @if(!$meta['required'])
                                    <span class="text-xs text-muted-ink">(Opsional)</span>
                                @endif
                            </div>

                            @if($chosen)
                                <p class="font-medium text-sm sm:text-base text-[var(--text-primary)]">
                                    {{ $chosen->name }}
                                </p>
                                <div class="flex flex-wrap items-center gap-2 pt-0.5 text-xs">
                                    <span class="px-2 py-0.5 rounded-md font-semibold {{ match($chosen->price_tier) {
                                        'budget' => 'badge-ok',
                                        'mid' => 'badge-accent-soft',
                                        default => 'badge-warn',
                                    } }}">
                                        {{ $chosen->tierLabel() }}
                                    </span>
                                    @foreach($chosen->specBadges() as $b)
                                        <span class="surface-subtle px-2 py-0.5 rounded-md text-muted-ink">{{ $b }}</span>
                                    @endforeach
                                    <span class="font-display font-bold text-sm tabular-nums text-[var(--text-primary)]">
                                        {{ $chosen->formattedPrice() }}
                                    </span>
                                    @if($matchedStoreItem)
                                        <a
                                            href="{{ route('product.show', $matchedStoreItem->slug) }}"
                                            wire:navigate
                                            class="badge-ok px-2.5 py-0.5 rounded-md font-semibold hover:underline"
                                        >
                                            Ready di Stok Toko ({{ $matchedStoreItem->formattedPrice() }})
                                        </a>
                                    @endif
                                </div>
                            @else
                                <p class="text-xs sm:text-sm text-muted-ink">{{ $meta['hint'] }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto shrink-0 sm:pl-2 no-print">
                        @if($chosen)
                            <button
                                type="button"
                                wire:click="openSlotPicker('{{ $slotKey }}')"
                                class="btn-secondary flex-1 sm:flex-none min-h-11 px-3.5 rounded-lg text-sm cursor-pointer"
                            >
                                Ganti
                            </button>
                            <button
                                type="button"
                                wire:click="removeComponent('{{ $slotKey }}')"
                                @click="$store.tokoku.notify('{{ $meta['label'] }} dilepas')"
                                class="btn-secondary min-h-11 px-3.5 rounded-lg text-sm cursor-pointer"
                                aria-label="Lepas {{ $meta['label'] }}"
                            >
                                Lepas
                            </button>
                        @else
                            <button
                                type="button"
                                wire:click="openSlotPicker('{{ $slotKey }}')"
                                class="btn-accent w-full sm:w-auto min-h-11 px-4 rounded-lg text-sm cursor-pointer"
                            >
                                Pilih {{ explode(' ', $meta['label'])[0] }}
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </section>

        <aside id="ringkasan-rakitan" aria-label="Ringkasan Kecocokan dan Total Anggaran" class="lg:col-span-4 space-y-4 lg:sticky lg:top-24 scroll-mt-20">
            <div class="surface-card rounded-xl p-5 sm:p-6 space-y-5">
                <h2 class="font-display font-extrabold text-lg">Ringkasan Estimasi Rakitan</h2>

                @if($evaluation['passed'] && empty($evaluation['warnings']))
                    <div class="badge-ok rounded-xl p-3.5 text-sm space-y-0.5">
                        <p class="font-semibold">Komponen Saling Cocok</p>
                        <p class="text-xs opacity-90">Soket, generasi RAM, ukuran motherboard, dan daya aman.</p>
                    </div>
                @endif

                @if(!empty($evaluation['errors']))
                    <div class="badge-err rounded-xl p-3.5 text-xs sm:text-sm space-y-1.5" role="alert">
                        <p class="font-semibold">Ada Komponen yang Belum Cocok:</p>
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach($evaluation['errors'] as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($evaluation['warnings']))
                    <div class="badge-warn rounded-xl p-3.5 text-xs sm:text-sm space-y-1.5">
                        <p class="font-semibold">Catatan Perhatian:</p>
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach($evaluation['warnings'] as $warn)
                                <li>{{ $warn }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="surface-subtle rounded-xl p-4 space-y-2">
                    <div class="flex items-center justify-between text-xs sm:text-sm">
                        <span class="text-muted-ink">Estimasi Beban Daya</span>
                        <span class="font-display font-bold tabular-nums">{{ $evaluation['estimated_wattage'] }} Watt</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-[var(--border-default)] overflow-hidden" aria-hidden="true">
                        <div class="h-full bg-[var(--accent)] transition-all duration-300" style="width: {{ $powerPercent }}%"></div>
                    </div>
                    <p class="text-xs text-muted-ink">
                        Saran kapasitas PSU minimal <strong class="text-[var(--text-primary)]">{{ $evaluation['recommended_psu_wattage'] }} Watt</strong>.
                    </p>
                </div>

                <div class="border-t border-[var(--border-default)] pt-4 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-ink">Estimasi Harga Part</span>
                        <span class="font-semibold tabular-nums">Rp {{ number_format($evaluation['parts_subtotal'], 0, ',', '.') }}</span>
                    </div>

                    <label class="flex items-start gap-2.5 py-1 cursor-pointer no-print">
                        <input type="checkbox" wire:model.live="includeAssembly" class="mt-1 w-4 h-4 rounded accent-[#C2410C]">
                        <span class="text-xs sm:text-sm">
                            Tambah Jasa Rakit, Manajemen Kabel, &amp; Uji Suhu
                            <span class="block font-semibold tabular-nums">+Rp {{ number_format($store->default_assembly_fee, 0, ',', '.') }}</span>
                        </span>
                    </label>

                    <div class="border-t border-[var(--border-default)] pt-3 flex justify-between items-baseline">
                        <span class="font-bold">Total Estimasi</span>
                        <span class="font-display text-xl sm:text-2xl font-extrabold tabular-nums">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                    </div>
                    <p class="text-xs text-muted-ink">
                        Harga merupakan estimasi referensi pasar. Klik tombol WhatsApp untuk cek stok ready toko atau pemesanan part.
                    </p>
                </div>

                <div class="space-y-2.5 pt-1 no-print">
                    <a
                        href="{{ $whatsappBuildUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn-accent w-full min-h-11 px-4 rounded-lg text-sm inline-flex items-center justify-center text-center"
                    >
                        Tanya Ketersediaan &amp; Pesan via WA
                    </a>

                    <button
                        type="button"
                        onclick="window.print()"
                        class="btn-secondary w-full min-h-11 px-4 rounded-lg text-sm cursor-pointer"
                    >
                        Cetak / Simpan PDF
                    </button>
                </div>
            </div>

            <div class="surface-card rounded-xl p-5 space-y-3 no-print">
                <h2 class="font-display font-bold text-sm">Simpan &amp; Bagikan Rakitan Ini</h2>
                <form wire:submit="saveBuild" class="space-y-3">
                    <div>
                        <label for="nama-rakitan" class="block text-xs font-medium text-muted-ink mb-1">Nama Rakitan</label>
                        <input
                            id="nama-rakitan"
                            type="text"
                            wire:model="buildName"
                            placeholder="Contoh: PC Desain & Gaming 1440p"
                            class="input-field w-full min-h-11 px-3.5 rounded-lg text-sm"
                        >
                        @error('buildName')
                            <p class="text-xs text-[var(--status-err-fg)] font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nama-perakit" class="block text-xs font-medium text-muted-ink mb-1">Nama Anda (Opsional)</label>
                        <input
                            id="nama-perakit"
                            type="text"
                            wire:model="customerName"
                            placeholder="Nama pemesan"
                            class="input-field w-full min-h-11 px-3.5 rounded-lg text-sm"
                        >
                    </div>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="saveBuild"
                        class="btn-secondary w-full min-h-11 px-4 rounded-lg text-sm cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="saveBuild">Buat Link Rakitan</span>
                        <span wire:loading wire:target="saveBuild">Menyimpan Link...</span>
                    </button>
                </form>

                @if($savedShareUrl)
                    <div class="badge-ok rounded-xl p-3.5 text-xs space-y-2.5">
                        <div>
                            <p class="font-semibold">Tersimpan dengan kode <span class="font-mono">{{ $shareCode }}</span></p>
                            <p class="break-all opacity-90 mt-0.5">{{ $savedShareUrl }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                @click="$store.tokoku.copyText(@js($savedShareUrl), 'Tautan rakitan ' + @js($shareCode) + ' disalin')"
                                class="btn-secondary min-h-10 px-3 rounded-lg text-xs cursor-pointer"
                            >
                                Salin Link
                            </button>
                            <button
                                type="button"
                                @click="$store.tokoku.shareLink(@js($buildName ?: 'Simulasi Rakit PC'), 'Lihat simulasi rakitan PC saya (' + @js($shareCode) + ')', @js($savedShareUrl))"
                                class="btn-secondary min-h-10 px-3 rounded-lg text-xs cursor-pointer"
                            >
                                Bagikan
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </aside>
    </div>

    <div class="mobile-builder-bar lg:hidden fixed inset-x-0 z-30 px-4 py-2.5 flex items-center justify-between gap-3 no-print">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <span class="font-display font-extrabold text-base text-[#FDBA74] tabular-nums">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded {{ $evaluation['passed'] ? 'bg-[#064E3B] text-[#A7F3D0]' : 'bg-[#450A0A] text-[#FECACA]' }}">
                    {{ $evaluation['passed'] ? 'Cocok' : 'Cek Bentrok' }}
                </span>
            </div>
            <p class="text-[11px] ink-muted tabular-nums">{{ $filledRequiredCount }}/{{ $totalRequiredCount }} part utama &middot; {{ $evaluation['estimated_wattage'] }}W</p>
        </div>
        <a href="#ringkasan-rakitan" class="btn-ink-accent min-h-11 px-4 rounded-lg text-xs inline-flex items-center shrink-0">
            Lihat Ringkasan
        </a>
    </div>

    <section aria-label="Lembar Cetak Penawaran Rakitan PC" class="print-only space-y-5">
        <div class="border-b-2 border-slate-800 pb-3 flex justify-between items-start gap-4">
            <div>
                <h1 class="text-xl font-extrabold">{{ $store->store_name }} - Lembar Estimasi Rakitan PC</h1>
                <p class="text-xs text-slate-600">{{ $store->workshop_address }} | WA: {{ $store->whatsapp_number }}</p>
                <p class="text-xs mt-1">
                    <strong>Tanggal Cetak:</strong> {{ now()->format('d M Y, H:i') }}
                    @if($shareCode !== '') · <span class="font-mono font-bold">Kode: {{ $shareCode }}</span> @endif
                    @if($buildName !== '') · {{ $buildName }} @endif
                    @if($customerName !== '') · Pemesan: {{ $customerName }} @endif
                </p>
            </div>
            @if(!empty($buildQrSvg))
                <div class="text-center shrink-0">
                    <div class="inline-block border border-slate-800 p-1 bg-white">{!! $buildQrSvg !!}</div>
                    <p class="text-[10px] font-semibold mt-0.5">Pindai Buka Rakitan</p>
                </div>
            @endif
        </div>

        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 24%;">Slot Komponen</th>
                    <th style="width: 48%;">Nama Komponen &amp; Spesifikasi</th>
                    <th style="width: 28%; text-align: right;">Estimasi Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach($slots as $slotKey => $meta)
                    @php
                        $p = $selectedProducts[$slotKey] ?? null;
                        $matchedStore = $p ? ($storeMatchesByPartId[$p->id] ?? null) : null;
                    @endphp
                    <tr>
                        <td class="font-semibold">{{ $meta['label'] }}</td>
                        <td>
                            @if($p)
                                <div class="font-semibold">{{ $p->name }}</div>
                                @if($matchedStore)
                                    <div class="text-[11px]">Tersedia di stok fisik toko ({{ $matchedStore->formattedPrice() }})</div>
                                @endif
                            @else
                                <span class="text-slate-500">Belum dipilih</span>
                            @endif
                        </td>
                        <td style="text-align: right;" class="tabular-nums font-semibold">
                            {{ $p ? $p->formattedPrice() : '-' }}
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2" class="font-semibold">Subtotal Komponen</td>
                    <td style="text-align: right;" class="tabular-nums font-semibold">Rp {{ number_format($evaluation['parts_subtotal'], 0, ',', '.') }}</td>
                </tr>
                @if($includeAssembly)
                    <tr>
                        <td colspan="2">Jasa Perakitan, Manajemen Kabel, &amp; Uji Suhu</td>
                        <td style="text-align: right;" class="tabular-nums">Rp {{ number_format($assemblyFee, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr>
                    <td colspan="2" class="font-extrabold">TOTAL ESTIMASI ANGGARAN</td>
                    <td style="text-align: right;" class="tabular-nums font-extrabold">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="text-xs space-y-1 border-t border-slate-300 pt-3">
            <p><strong>Ringkasan Teknis:</strong> Kelas {{ $performanceProfile['tier_label'] }} | Estimasi beban daya {{ $evaluation['estimated_wattage'] }}W (Saran PSU minimal {{ $evaluation['recommended_psu_wattage'] }}W) | Status kecocokan: {{ $evaluation['passed'] ? 'Komponen Saling Cocok' : 'Perlu Pemeriksaan Ulang' }}.</p>
            <p class="text-slate-600">Catatan: Harga part referensi menyesuaikan kurs dan ketersediaan stok harian saat pemesanan di {{ $store->store_name }}.</p>
        </div>
    </section>

    @if($activePickerSlot)
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="judul-dialog-komponen"
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-950/75 no-print"
            @click.self="$wire.closeSlotPicker()"
        >
            <div class="surface-card rounded-t-2xl sm:rounded-2xl w-full max-w-4xl max-h-[92dvh] sm:max-h-[90dvh] flex flex-col overflow-hidden">
                <div class="p-3.5 sm:p-5 border-b border-[var(--border-default)] flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h2 id="judul-dialog-komponen" class="font-display font-bold text-sm sm:text-lg truncate">
                            Pilih {{ $slots[$activePickerSlot]['label'] ?? 'Komponen' }}
                        </h2>
                        <p class="text-[11px] sm:text-xs text-muted-ink tabular-nums">
                            {{ number_format($pickerTotalMatching, 0, ',', '.') }} komponen sesuai filter.
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="closeSlotPicker"
                        class="btn-secondary min-h-11 px-3.5 rounded-lg text-xs sm:text-sm shrink-0 cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>

                <div class="p-3.5 sm:p-4 border-b border-[var(--border-default)] space-y-2.5 sm:space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                        <div class="sm:col-span-7">
                            <label for="cari-part-dialog" class="sr-only">Cari merek atau seri part</label>
                            <input
                                id="cari-part-dialog"
                                type="search"
                                wire:model.live.debounce.250ms="pickerSearch"
                                placeholder="Ketik seri (Ryzen 5 5600, RTX 4060, 750W)..."
                                class="input-field w-full min-h-11 px-3.5 rounded-lg text-sm"
                            >
                        </div>
                        <div class="sm:col-span-5">
                            <label for="urutkan-part-dialog" class="sr-only">Urutkan harga</label>
                            <select
                                id="urutkan-part-dialog"
                                wire:model.live="pickerSort"
                                class="input-field w-full min-h-11 px-3 rounded-lg text-sm"
                            >
                                <option value="price_asc">Harga: Termurah ke Termahal</option>
                                <option value="price_desc">Harga: Termahal ke Termurah</option>
                                <option value="name_asc">Nama: A ke Z</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-0.5">
                            @foreach([
                                'all' => 'Semua',
                                'budget' => 'Hemat',
                                'mid' => 'Menengah',
                                'high' => 'High-End',
                            ] as $tierKey => $tierText)
                                <button
                                    type="button"
                                    wire:click="$set('pickerTier', '{{ $tierKey }}')"
                                    class="min-h-10 px-3 rounded-lg text-xs shrink-0 cursor-pointer {{ $pickerTier === $tierKey ? 'btn-ghost-active' : 'btn-secondary' }}"
                                >
                                    {{ $tierText }}
                                </button>
                            @endforeach
                        </div>

                        <label class="inline-flex items-center gap-2 min-h-10 text-xs sm:text-sm cursor-pointer">
                            <input type="checkbox" wire:model.live="onlyCompatible" class="w-4 h-4 rounded accent-[#C2410C]">
                            <span>Hanya tampilkan yang cocok</span>
                        </label>
                    </div>
                </div>

                <div class="p-4 overflow-y-auto space-y-2.5 flex-1">
                    @if($pickerCandidates->isEmpty())
                        <div class="surface-subtle rounded-xl p-8 text-center space-y-2">
                            <p class="font-semibold text-sm">Tidak ada komponen yang sesuai saringan ini.</p>
                            <p class="text-xs text-muted-ink max-w-md mx-auto">
                                Coba ubah kata kunci pencarian, pilih "Semua Kelas", atau matikan centang "Hanya tampilkan yang cocok" bila ingin melihat soket berbeda.
                            </p>
                        </div>
                    @else
                        @foreach($pickerCandidates as $entry)
                            @php
                                /** @var \App\Models\PcCatalogPart $cand */
                                $cand = $entry['product'];
                                $isCompatible = $entry['compatible'];
                                /** @var \App\Models\Product|null $candStoreMatch */
                                $candStoreMatch = $storeMatchesByPartId[$cand->id] ?? null;
                            @endphp
                            <div class="surface-subtle rounded-xl p-3.5 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="px-2 py-0.5 rounded-md text-xs font-semibold {{ match($cand->price_tier) {
                                            'budget' => 'badge-ok',
                                            'mid' => 'badge-accent-soft',
                                            default => 'badge-warn',
                                        } }}">
                                            {{ $cand->tierLabel() }}
                                        </span>
                                        @if($candStoreMatch)
                                            <span class="badge-ok px-2 py-0.5 rounded-md text-xs font-semibold">
                                                Ready di Stok Toko ({{ $candStoreMatch->formattedPrice() }})
                                            </span>
                                        @endif
                                        @foreach($cand->specBadges() as $b)
                                            <span class="surface-card px-2 py-0.5 rounded-md text-xs text-muted-ink">{{ $b }}</span>
                                        @endforeach
                                    </div>
                                    <p class="font-semibold text-sm sm:text-base">{{ $cand->name }}</p>
                                    @if(!$isCompatible && $entry['reason'])
                                        <p class="text-xs font-semibold text-[var(--status-err-fg)]">
                                            Kurang cocok: {{ $entry['reason'] }}
                                        </p>
                                    @endif
                                </div>

                                <div class="flex sm:flex-col items-center sm:items-end justify-between gap-2 shrink-0">
                                    <div class="text-left sm:text-right">
                                        <span class="block font-display font-bold text-base tabular-nums">{{ $cand->formattedPrice() }}</span>
                                        <span class="block text-[11px] text-muted-ink tabular-nums">Ref. ${{ number_format($cand->price_usd, 2) }}</span>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="chooseComponent('{{ $activePickerSlot }}', {{ $cand->id }})"
                                        @click="$store.tokoku.notify('Komponen terpasang ke simulasi')"
                                        class="{{ $isCompatible ? 'btn-accent' : 'btn-secondary' }} min-h-11 px-4 rounded-lg text-sm cursor-pointer"
                                    >
                                        {{ $isCompatible ? 'Pilih' : 'Tetap Pilih' }}
                                    </button>
                                </div>
                            </div>
                        @endforeach

                        @if($pickerTotalMatching > $pickerCandidates->count())
                            <div class="pt-3 text-center">
                                <button
                                    type="button"
                                    wire:click="loadMoreCandidates"
                                    class="btn-secondary min-h-11 px-5 rounded-lg text-sm cursor-pointer"
                                >
                                    Tampilkan Lebih Banyak ({{ number_format($pickerTotalMatching - $pickerCandidates->count(), 0, ',', '.') }} lagi)
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
