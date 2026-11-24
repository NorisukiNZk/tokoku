@php
    $requiredKeys = collect($slots)->filter(fn ($m) => $m['required'])->keys();
    $filledRequiredCount = $requiredKeys->filter(fn ($k) => !empty($selectedProducts[$k]))->count();
    $totalRequiredCount = $requiredKeys->count();
    $powerPercent = min(100, max(12, (int) round(($evaluation['estimated_wattage'] / max(1, $evaluation['recommended_psu_wattage'])) * 100)));
@endphp
<div
    x-data
    @keydown.escape.window="$wire.closeSlotPicker()"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 pb-24 lg:pb-8 space-y-6"
>
    <div class="surface-card rounded-xl p-5 sm:p-6 space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Simulasi Rakit PC &amp; Cek Harga Part</h1>
                    <span class="badge-accent-soft px-2.5 py-0.5 rounded-md text-xs font-semibold tabular-nums">
                        {{ number_format($totalCatalogCount, 0, ',', '.') }} Part Tersedia
                    </span>
                </div>
                <p class="text-sm text-muted-ink max-w-3xl">
                    Cari komponen mulai dari seri paling terjangkau hingga kelas tertinggi berdasarkan database referensi pasar global yang dikonversi ke Rupiah (kurs acuan Rp {{ number_format($store->usd_to_idr_rate ?: 16500, 0, ',', '.') }}/USD). Sistem otomatis mencocokkan soket CPU, tipe RAM, ukuran casing, dan daya PSU.
                </p>
            </div>
        </div>

        <div class="pt-2 border-t border-[var(--border-default)] flex flex-wrap items-center justify-between gap-3 no-print">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-muted-ink mr-1">Susun Cepat Otomatis:</span>
                <button
                    type="button"
                    wire:click="applySmartTierPreset('budget')"
                    @click="$store.tokoku.notify('Menyusun paket kelas hemat...')"
                    class="btn-secondary min-h-11 px-3.5 rounded-lg text-xs sm:text-sm cursor-pointer"
                >
                    Paket Hemat
                </button>
                <button
                    type="button"
                    wire:click="applySmartTierPreset('mid')"
                    @click="$store.tokoku.notify('Menyusun paket kelas menengah...')"
                    class="btn-secondary min-h-11 px-3.5 rounded-lg text-xs sm:text-sm cursor-pointer"
                >
                    Paket Menengah
                </button>
                <button
                    type="button"
                    wire:click="applySmartTierPreset('high')"
                    @click="$store.tokoku.notify('Menyusun paket kelas high-end...')"
                    class="btn-secondary min-h-11 px-3.5 rounded-lg text-xs sm:text-sm cursor-pointer"
                >
                    Paket High-End
                </button>
            </div>

            <button
                type="button"
                wire:click="clearBuild"
                @click="$store.tokoku.notify('Semua slot rakitan dikosongkan')"
                class="btn-secondary min-h-11 px-3.5 rounded-lg text-xs sm:text-sm cursor-pointer"
            >
                Kosongkan Semua
            </button>
        </div>
    </div>

    @if($loadErrorMessage)
        <div class="badge-err rounded-xl p-4 text-sm font-medium" role="alert">
            {{ $loadErrorMessage }}
        </div>
    @endif

    <div wire:loading.delay class="surface-subtle rounded-xl p-3.5 text-sm font-medium no-print" role="status" aria-live="polite">
        Memproses daftar komponen dan kecocokan spesifikasi...
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        <section aria-label="Urutan Pemilihan Komponen PC" class="lg:col-span-8 space-y-3.5">
            <div class="surface-subtle rounded-xl px-4 py-3 flex items-center justify-between gap-4 text-sm">
                <span class="font-medium">Kelengkapan Komponen Utama</span>
                <span class="font-semibold tabular-nums">{{ $filledRequiredCount }} dari {{ $totalRequiredCount }} terisi</span>
            </div>

            @foreach($slots as $slotKey => $meta)
                @php
                    /** @var \App\Models\PcCatalogPart|null $chosen */
                    $chosen = $selectedProducts[$slotKey] ?? null;
                @endphp
                <div class="surface-card rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors">
                    <div class="flex items-start gap-3.5 flex-1 min-w-0">
                        <span class="w-8 h-8 rounded-lg {{ $chosen ? 'btn-accent' : 'surface-subtle text-muted-ink' }} inline-flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">
                            {{ $loop->iteration }}
                        </span>

                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-semibold text-sm sm:text-base">{{ $meta['label'] }}</h2>
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
                                @click="$store.tokoku.notify('{{ $meta['label'] }} dilepas')"
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

        <aside id="ringkasan-rakitan" aria-label="Ringkasan Kecocokan dan Total Anggaran" class="lg:col-span-4 space-y-4 lg:sticky lg:top-24 scroll-mt-20">
            <div class="surface-card rounded-xl p-5 sm:p-6 space-y-5">
                <h2 class="font-bold text-lg">Ringkasan Estimasi Rakitan</h2>

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
                        <span class="font-semibold tabular-nums">{{ $evaluation['estimated_wattage'] }} Watt</span>
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
                        <input type="checkbox" wire:model.live="includeAssembly" class="mt-1 w-4 h-4 rounded accent-[#1D4ED8]">
                        <span class="text-xs sm:text-sm">
                            Tambah Jasa Rakit, Manajemen Kabel, &amp; Uji Suhu
                            <span class="block font-semibold tabular-nums">+Rp {{ number_format($store->default_assembly_fee, 0, ',', '.') }}</span>
                        </span>
                    </label>

                    <div class="border-t border-[var(--border-default)] pt-3 flex justify-between items-baseline">
                        <span class="font-bold">Total Estimasi</span>
                        <span class="text-xl sm:text-2xl font-bold tabular-nums">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
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
                <h2 class="font-semibold text-sm">Simpan &amp; Bagikan Rakitan Ini</h2>
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

                    <button type="submit" class="btn-secondary w-full min-h-11 px-4 rounded-lg text-sm cursor-pointer">
                        Buat Link Rakitan
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
                <span class="font-bold text-base tabular-nums">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded {{ $evaluation['passed'] ? 'badge-ok' : 'badge-err' }}">
                    {{ $evaluation['passed'] ? 'Cocok' : 'Cek Bentrok' }}
                </span>
            </div>
            <p class="text-[11px] text-muted-ink tabular-nums">{{ $filledRequiredCount }}/{{ $totalRequiredCount }} part utama &middot; {{ $evaluation['estimated_wattage'] }}W</p>
        </div>
        <a href="#ringkasan-rakitan" class="btn-accent min-h-11 px-4 rounded-lg text-xs inline-flex items-center shrink-0">
            Lihat Ringkasan
        </a>
    </div>

    @if($activePickerSlot)
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="judul-dialog-komponen"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/60 no-print"
            @click.self="$wire.closeSlotPicker()"
        >
            <div class="surface-card rounded-2xl w-full max-w-4xl max-h-[90dvh] flex flex-col overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-[var(--border-default)] flex items-center justify-between gap-3">
                    <div>
                        <h2 id="judul-dialog-komponen" class="font-bold text-base sm:text-lg">
                            Pilih {{ $slots[$activePickerSlot]['label'] ?? 'Komponen' }}
                        </h2>
                        <p class="text-xs text-muted-ink tabular-nums">
                            Ditemukan {{ number_format($pickerTotalMatching, 0, ',', '.') }} pilihan komponen yang sesuai filter.
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="closeSlotPicker"
                        class="btn-secondary min-h-11 px-4 rounded-lg text-sm cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>

                <div class="p-4 border-b border-[var(--border-default)] space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                        <div class="sm:col-span-7">
                            <label for="cari-part-dialog" class="sr-only">Cari merek atau seri part</label>
                            <input
                                id="cari-part-dialog"
                                type="search"
                                wire:model.live.debounce.250ms="pickerSearch"
                                placeholder="Ketik seri part (misal: Ryzen 5 5600, i5-12400, RTX 4060, 750W)..."
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

                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex flex-wrap items-center gap-1.5">
                            @foreach([
                                'all' => 'Semua Kelas',
                                'budget' => 'Kelas Hemat',
                                'mid' => 'Menengah',
                                'high' => 'High-End',
                            ] as $tierKey => $tierText)
                                <button
                                    type="button"
                                    wire:click="$set('pickerTier', '{{ $tierKey }}')"
                                    class="min-h-11 px-3 rounded-lg text-xs sm:text-sm cursor-pointer {{ $pickerTier === $tierKey ? 'btn-ghost-active' : 'btn-secondary' }}"
                                >
                                    {{ $tierText }}
                                </button>
                            @endforeach
                        </div>

                        <label class="inline-flex items-center gap-2 min-h-11 text-xs sm:text-sm cursor-pointer">
                            <input type="checkbox" wire:model.live="onlyCompatible" class="w-4 h-4 rounded accent-[#1D4ED8]">
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
                                        <span class="block font-bold text-base tabular-nums">{{ $cand->formattedPrice() }}</span>
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
