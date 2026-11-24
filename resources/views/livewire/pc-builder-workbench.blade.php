@php
    $requiredKeys = collect($slots)->filter(fn ($m) => $m['required'])->keys();
    $filledRequiredCount = $requiredKeys->filter(fn ($k) => !empty($selectedProducts[$k]))->count();
    $totalRequiredCount = $requiredKeys->count();
    $powerPercent = min(100, max(12, (int) round(($evaluation['estimated_wattage'] / max(1, $evaluation['recommended_psu_wattage'])) * 100)));
@endphp
<div
    x-data
    @keydown.escape.window="$wire.closeSlotPicker()"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6"
>
    <div class="surface-card rounded-xl p-5 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div class="space-y-1.5">
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Meja Simulasi Rakit PC</h1>
            <p class="text-sm text-muted-ink max-w-2xl">
                Pilih komponen satu per satu dari atas ke bawah. Anda bebas memadukan part baru bersegel dengan komponen second toko, dan sistem otomatis menjaga agar soket serta dayanya saling cocok.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0 no-print">
            <button
                type="button"
                wire:click="loadPreset('RKT-CONTOH')"
                class="btn-ghost-active min-h-11 px-4 rounded-lg text-sm cursor-pointer"
            >
                Isi Contoh Paket 1080p
            </button>
            <button
                type="button"
                wire:click="clearBuild"
                class="btn-secondary min-h-11 px-4 rounded-lg text-sm cursor-pointer"
            >
                Mulai dari Kosong
            </button>
        </div>
    </div>

    @if($loadErrorMessage)
        <div class="badge-err rounded-xl p-4 text-sm font-medium" role="alert">
            {{ $loadErrorMessage }}
        </div>
    @endif

    <div wire:loading.delay class="surface-subtle rounded-xl p-3.5 text-sm font-medium no-print" role="status" aria-live="polite">
        Memperbarui perhitungan kecocokan dan daya...
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        <section aria-label="Urutan Pemilihan Komponen PC" class="lg:col-span-8 space-y-3.5">
            <div class="surface-subtle rounded-xl px-4 py-3 flex items-center justify-between gap-4 text-sm">
                <span class="font-medium">Kelengkapan Komponen Utama</span>
                <span class="font-semibold tabular-nums">{{ $filledRequiredCount }} dari {{ $totalRequiredCount }} terisi</span>
            </div>

            @foreach($slots as $slotKey => $meta)
                @php
                    /** @var \App\Models\Product|null $chosen */
                    $chosen = $selectedProducts[$slotKey] ?? null;
                @endphp
                <div class="surface-card rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5 flex-1 min-w-0">
                        <span class="w-8 h-8 rounded-lg {{ $chosen ? 'btn-accent' : 'surface-subtle text-muted-ink' }} inline-flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">
                            {{ $loop->iteration }}
                        </span>

                        @if($chosen && $chosen->primaryImageUrl())
                            <img src="{{ $chosen->primaryImageUrl() }}" alt="" class="hidden sm:block w-16 h-12 rounded-lg object-cover shrink-0 border border-[var(--border-default)]">
                        @endif

                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-semibold text-sm sm:text-base">{{ $meta['label'] }}</h2>
                                @if(!$meta['required'])
                                    <span class="text-xs text-muted-ink">(Opsional)</span>
                                @endif
                            </div>

                            @if($chosen)
                                <p class="font-medium text-sm sm:text-base text-[var(--text-primary)]">
                                    <a href="{{ route('product.show', $chosen->slug) }}" class="hover:underline">
                                        {{ $chosen->name }}
                                    </a>
                                </p>
                                <div class="flex flex-wrap items-center gap-2 pt-0.5 text-xs">
                                    <span class="px-2 py-0.5 rounded-md font-semibold {{ $chosen->condition === 'new' ? 'badge-ok' : 'badge-warn' }}">
                                        {{ $chosen->conditionShortLabel() }}
                                    </span>
                                    @foreach(array_slice($chosen->specSummaryBadges(), 0, 2) as $b)
                                        <span class="surface-subtle px-2 py-0.5 rounded-md text-muted-ink">{{ $b }}</span>
                                    @endforeach
                                    <span class="font-bold text-sm tabular-nums text-[var(--text-primary)]">
                                        {{ $chosen->formattedPrice() }}
                                    </span>
                                </div>
                            @else
                                <p class="text-xs sm:text-sm text-muted-ink">{{ $meta['hint'] }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 sm:pl-2 no-print">
                        @if($chosen)
                            <button
                                type="button"
                                wire:click="openSlotPicker('{{ $slotKey }}')"
                                class="btn-secondary min-h-11 px-3.5 rounded-lg text-sm cursor-pointer"
                            >
                                Ganti
                            </button>
                            <button
                                type="button"
                                wire:click="removeComponent('{{ $slotKey }}')"
                                class="btn-secondary min-h-11 px-3 rounded-lg text-sm cursor-pointer"
                                aria-label="Lepas {{ $meta['label'] }}"
                            >
                                Lepas
                            </button>
                        @else
                            <button
                                type="button"
                                wire:click="openSlotPicker('{{ $slotKey }}')"
                                class="btn-accent min-h-11 px-4 rounded-lg text-sm cursor-pointer"
                            >
                                Pilih {{ explode(' ', $meta['label'])[0] }}
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </section>

        <aside aria-label="Ringkasan Kecocokan dan Total Biaya" class="lg:col-span-4 space-y-4 lg:sticky lg:top-24">
            <div class="surface-card rounded-xl p-5 sm:p-6 space-y-5">
                <h2 class="font-bold text-lg">Ringkasan Rakitan Anda</h2>

                @if($evaluation['passed'] && empty($evaluation['warnings']))
                    <div class="badge-ok rounded-xl p-3.5 text-sm space-y-0.5">
                        <p class="font-semibold">Komponen Saling Cocok</p>
                        <p class="text-xs opacity-90">Soket, tipe memori, dan ukuran casing sudah sesuai.</p>
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
                        <p class="font-semibold">Catatan Tambahan:</p>
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach($evaluation['warnings'] as $warn)
                                <li>{{ $warn }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="surface-subtle rounded-xl p-4 space-y-2">
                    <div class="flex items-center justify-between text-xs sm:text-sm">
                        <span class="text-muted-ink">Kebutuhan Daya</span>
                        <span class="font-semibold tabular-nums">{{ $evaluation['estimated_wattage'] }} Watt</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-[var(--border-default)] overflow-hidden" aria-hidden="true">
                        <div class="h-full bg-[var(--accent)]" style="width: {{ $powerPercent }}%"></div>
                    </div>
                    <p class="text-xs text-muted-ink">
                        Disarankan memakai PSU minimal <strong class="text-[var(--text-primary)]">{{ $evaluation['recommended_psu_wattage'] }} Watt</strong>.
                    </p>
                </div>

                <div class="border-t border-[var(--border-default)] pt-4 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-ink">Total Harga Komponen</span>
                        <span class="font-semibold tabular-nums">Rp {{ number_format($evaluation['parts_subtotal'], 0, ',', '.') }}</span>
                    </div>

                    <label class="flex items-start gap-2.5 py-1 cursor-pointer no-print">
                        <input type="checkbox" wire:model.live="includeAssembly" class="mt-1 w-4 h-4 rounded accent-[#1D4ED8]">
                        <span class="text-xs sm:text-sm">
                            Sekalian dirakitkan rapi + uji suhu di toko
                            <span class="block font-semibold tabular-nums">+Rp {{ number_format($store->default_assembly_fee, 0, ',', '.') }}</span>
                        </span>
                    </label>

                    <div class="border-t border-[var(--border-default)] pt-3 flex justify-between items-baseline">
                        <span class="font-bold">Total Estimasi</span>
                        <span class="text-xl sm:text-2xl font-bold tabular-nums">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="space-y-2.5 pt-1 no-print">
                    <a
                        href="{{ $whatsappBuildUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn-accent w-full min-h-11 px-4 rounded-lg text-sm inline-flex items-center justify-center text-center"
                    >
                        Konsultasi &amp; Pesan via WhatsApp
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
                <h2 class="font-semibold text-sm">Simpan &amp; Bagikan Rakitan Ini</h2>
                <form wire:submit="saveBuild" class="space-y-3">
                    <div>
                        <label for="nama-rakitan" class="block text-xs font-medium text-muted-ink mb-1">Nama Rakitan</label>
                        <input
                            id="nama-rakitan"
                            type="text"
                            wire:model="buildName"
                            placeholder="Contoh: PC Kerja & Gaming Rumah"
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

                    <button type="submit" class="btn-secondary w-full min-h-11 px-4 rounded-lg text-sm cursor-pointer">
                        Buat Link Rakitan
                    </button>
                </form>

                @if($savedShareUrl)
                    <div class="badge-ok rounded-xl p-3.5 text-xs space-y-1">
                        <p class="font-semibold">Tersimpan dengan kode <span class="font-mono">{{ $shareCode }}</span></p>
                        <p class="break-all">{{ $savedShareUrl }}</p>
                    </div>
                @endif
            </div>
        </aside>
    </div>

    @if($activePickerSlot)
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="judul-dialog-komponen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 no-print"
        >
            <div class="surface-card rounded-2xl w-full max-w-3xl max-h-[88dvh] flex flex-col overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-[var(--border-default)] flex items-center justify-between gap-3">
                    <div>
                        <h2 id="judul-dialog-komponen" class="font-bold text-base sm:text-lg">
                            Pilih {{ $slots[$activePickerSlot]['label'] ?? 'Komponen' }}
                        </h2>
                        <p class="text-xs text-muted-ink">Pilih dari stok yang sedang tersedia di toko kami.</p>
                    </div>
                    <button
                        type="button"
                        wire:click="closeSlotPicker"
                        class="btn-secondary min-h-11 px-4 rounded-lg text-sm cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>

                <div class="p-4 border-b border-[var(--border-default)] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex-1">
                        <label for="cari-part-dialog" class="sr-only">Cari komponen</label>
                        <input
                            id="cari-part-dialog"
                            type="search"
                            wire:model.live.debounce.250ms="pickerSearch"
                            placeholder="Cari merek atau tipe komponen..."
                            class="input-field w-full min-h-11 px-3.5 rounded-lg text-sm"
                        >
                    </div>
                    <label class="inline-flex items-center gap-2 min-h-11 text-xs sm:text-sm cursor-pointer">
                        <input type="checkbox" wire:model.live="onlyCompatible" class="w-4 h-4 rounded accent-[#1D4ED8]">
                        <span>Hanya tampilkan yang cocok</span>
                    </label>
                </div>

                <div class="p-4 overflow-y-auto space-y-3 flex-1">
                    @if($pickerCandidates->isEmpty())
                        <div class="surface-subtle rounded-xl p-6 text-center space-y-2">
                            <p class="font-semibold text-sm">Belum ada komponen yang cocok dengan saringan ini.</p>
                            <p class="text-xs text-muted-ink">
                                Matikan centang "Hanya tampilkan yang cocok" bila Anda ingin melihat semua stok beserta alasan ketidakcocokannya.
                            </p>
                        </div>
                    @else
                        @foreach($pickerCandidates as $entry)
                            @php
                                /** @var \App\Models\Product $cand */
                                $cand = $entry['product'];
                                $isCompatible = $entry['compatible'];
                            @endphp
                            <div class="surface-subtle rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-start gap-3.5">
                                    @if($cand->primaryImageUrl())
                                        <img src="{{ $cand->primaryImageUrl() }}" alt="" class="hidden sm:block w-16 h-12 rounded-lg object-cover shrink-0 border border-[var(--border-default)]">
                                    @endif
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="px-2 py-0.5 rounded-md text-xs font-semibold {{ $cand->condition === 'new' ? 'badge-ok' : 'badge-warn' }}">
                                                {{ $cand->conditionShortLabel() }}
                                            </span>
                                            @foreach(array_slice($cand->specSummaryBadges(), 0, 3) as $b)
                                                <span class="surface-card px-2 py-0.5 rounded-md text-xs text-muted-ink">{{ $b }}</span>
                                            @endforeach
                                        </div>
                                        <p class="font-semibold text-sm sm:text-base">{{ $cand->name }}</p>
                                        <p class="text-xs text-muted-ink">{{ $cand->warranty_info }} &middot; Stok {{ $cand->stock }}</p>
                                        @if(!$isCompatible && $entry['reason'])
                                            <p class="text-xs font-semibold text-[var(--status-err-fg)]">
                                                Kurang cocok: {{ $entry['reason'] }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex sm:flex-col items-center sm:items-end justify-between gap-2 shrink-0">
                                    <span class="font-bold text-base tabular-nums">{{ $cand->formattedPrice() }}</span>
                                    <button
                                        type="button"
                                        wire:click="chooseComponent('{{ $activePickerSlot }}', {{ $cand->id }})"
                                        class="{{ $isCompatible ? 'btn-accent' : 'btn-secondary' }} min-h-11 px-4 rounded-lg text-sm cursor-pointer"
                                    >
                                        {{ $isCompatible ? 'Pilih' : 'Tetap Pilih' }}
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
