@php
    $activeCategory = $categorySlug !== '' ? $categories->firstWhere('slug', $categorySlug) : null;
    $conditionLabels = [
        'new' => 'Baru Segel (BNIB)',
        'used_normal' => 'Second Mulus & Normal',
        'minus_parts' => 'Unit Minus / Bahan Part',
    ];
    $hasActiveFilter = $search !== '' || $categorySlug !== '' || $conditionFilter !== 'all' || $onlyNegotiable || $hideSold;
    $featuredComparePayload = $featuredProduct ? [
        'id' => $featuredProduct->id,
        'name' => $featuredProduct->name,
        'price' => $featuredProduct->formattedPrice(),
        'priceNum' => $featuredProduct->price,
        'condition' => $featuredProduct->conditionShortLabel(),
        'negotiable' => (bool) $featuredProduct->is_negotiable,
        'warranty' => $featuredProduct->warranty_info ?: 'Cek sepuasnya di toko',
        'specs' => implode(' · ', $featuredProduct->specSummaryBadges()),
        'image' => $featuredProduct->primaryImageUrl(),
        'url' => route('product.show', $featuredProduct->slug),
        'waUrl' => $store->whatsappUrl($featuredProduct->whatsappInquiryMessage(route('product.show', $featuredProduct->slug))),
    ] : null;
@endphp
<div x-data="{ mobileFilterOpen: false }" class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-4 sm:py-7 space-y-4 sm:space-y-7">
    {{-- MOBILE TOP SEARCH & FILTER BAR (Langsung terlihat begitu buka di HP!) --}}
    <div class="lg:hidden flex items-center gap-2">
        <div class="relative flex-1 min-w-0">
            <label for="input-cari-hp" class="sr-only">Cari Barang di Etalase</label>
            <input
                id="input-cari-hp"
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari VGA, Ryzen, Laptop..."
                class="input-field w-full min-h-11 pl-9 pr-3 rounded-xl text-sm"
            >
            <svg class="w-4 h-4 text-muted-ink absolute left-3 top-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
            </svg>
        </div>
        <button
            type="button"
            @click="mobileFilterOpen = !mobileFilterOpen"
            :aria-expanded="mobileFilterOpen.toString()"
            class="min-h-11 px-3.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 shrink-0 cursor-pointer {{ $hasActiveFilter ? 'btn-accent' : 'btn-secondary' }}"
        >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18m-15 5h12m-9 5h6" />
            </svg>
            <span>Filter</span>
        </button>
    </div>

    {{-- MOBILE COLLAPSIBLE FILTER PANEL --}}
    <div
        x-show="mobileFilterOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="lg:hidden surface-card rounded-xl p-4 space-y-3.5"
    >
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="pilih-kondisi-hp" class="block text-xs font-semibold text-muted-ink mb-1">Kondisi Barang</label>
                <select
                    id="pilih-kondisi-hp"
                    wire:model.live="conditionFilter"
                    class="input-field w-full min-h-11 px-3 rounded-lg text-sm"
                >
                    <option value="all">Semua Kondisi</option>
                    <option value="new">Baru Segel (BNIB)</option>
                    <option value="used_normal">Second Mulus &amp; Normal</option>
                    <option value="minus_parts">Unit Minus / Bahan Part</option>
                </select>
            </div>
            <div>
                <label for="urutkan-produk-hp" class="block text-xs font-semibold text-muted-ink mb-1">Urutan Harga</label>
                <select id="urutkan-produk-hp" wire:model.live="sortBy" class="input-field w-full min-h-11 px-3 rounded-lg text-sm">
                    <option value="terbaru">Paling Baru</option>
                    <option value="termurah">Harga Terendah</option>
                    <option value="termahal">Harga Tertinggi</option>
                </select>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 pt-1 border-t border-[var(--border-default)]">
            <label class="inline-flex items-center gap-2 min-h-10 cursor-pointer text-xs font-medium">
                <input type="checkbox" wire:model.live="onlyNegotiable" class="w-4 h-4 rounded accent-[#C2410C]">
                <span>Bisa nego</span>
            </label>
            <label class="inline-flex items-center gap-2 min-h-10 cursor-pointer text-xs font-medium">
                <input type="checkbox" wire:model.live="hideSold" class="w-4 h-4 rounded accent-[#C2410C]">
                <span>Hanya ready</span>
            </label>
            <button type="button" @click="mobileFilterOpen = false" class="btn-accent min-h-10 px-4 rounded-lg text-xs cursor-pointer">
                Terapkan
            </button>
        </div>
    </div>

    {{-- DARK SHOWCASE BANNER (Compact & punchy on mobile, full 7:5 editorial on desktop) --}}
    @if(!$hasActiveFilter && $featuredProduct)
        <section aria-label="Sorotan Unit Pilihan dan Akses Cepat" class="surface-ink rounded-2xl p-3.5 sm:p-6 lg:p-7">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-6 items-stretch">
                <div class="lg:col-span-7 surface-ink-card rounded-xl p-3.5 sm:p-5 flex flex-col sm:flex-row gap-3.5 sm:gap-5 justify-between">
                    <a
                        href="{{ route('product.show', $featuredProduct->slug) }}"
                        wire:navigate
                        class="block w-full sm:w-56 md:w-64 shrink-0 aspect-[16/9] sm:aspect-auto rounded-lg overflow-hidden bg-[#0B0F19] border border-[var(--ink-panel-border)]"
                    >
                        @if($featuredProduct->primaryImageUrl())
                            <img
                                src="{{ $featuredProduct->primaryImageUrl() }}"
                                alt="{{ $featuredProduct->name }}"
                                class="w-full h-full object-cover"
                            >
                        @endif
                    </a>

                    <div class="flex-1 flex flex-col justify-between gap-3 min-w-0">
                        <div class="space-y-1.5 sm:space-y-2.5">
                            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-[#FB923C] text-[#0F172A] font-display font-bold text-[11px] sm:text-xs">
                                    Sorotan Unit Pilihan
                                </span>
                                <span class="px-2 py-0.5 rounded-md bg-[#0F172A] text-[#FDBA74] border border-[#EA580C] text-[11px] sm:text-xs font-semibold">
                                    {{ $featuredProduct->conditionShortLabel() }}
                                </span>
                            </div>

                            <h2 class="font-display font-extrabold text-base sm:text-xl leading-snug line-clamp-2">
                                <a href="{{ route('product.show', $featuredProduct->slug) }}" wire:navigate class="hover:underline">
                                    {{ $featuredProduct->name }}
                                </a>
                            </h2>

                            <div class="flex flex-wrap items-baseline gap-2">
                                <p class="font-display font-extrabold text-xl sm:text-[26px] text-[#FDBA74] tabular-nums">
                                    {{ $featuredProduct->formattedPrice() }}
                                </p>
                                @if($featuredProduct->is_negotiable)
                                    <span class="text-xs ink-muted">Bisa nego</span>
                                @endif
                            </div>

                            @if($featuredProduct->condition_notes)
                                <p class="hidden sm:line-clamp-2 text-xs sm:text-sm ink-muted leading-relaxed">
                                    {{ $featuredProduct->condition_notes }}
                                </p>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 pt-1">
                            <a
                                href="{{ route('product.show', $featuredProduct->slug) }}"
                                wire:navigate
                                class="btn-ink-accent min-h-11 px-3.5 sm:px-4 rounded-lg text-xs sm:text-sm inline-flex items-center justify-center text-center"
                            >
                                Lihat Unit
                            </a>
                            <a
                                href="{{ $store->whatsappUrl($featuredProduct->whatsappInquiryMessage(route('product.show', $featuredProduct->slug))) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn-ink-outline min-h-11 px-3.5 sm:px-4 rounded-lg text-xs sm:text-sm inline-flex items-center justify-center text-center"
                            >
                                Tanya WA
                            </a>
                            <button
                                type="button"
                                @click="$store.tokoku.toggleCompare(@js($featuredComparePayload))"
                                :aria-pressed="$store.tokoku.isCompared({{ $featuredProduct->id }}).toString()"
                                class="hidden sm:inline-flex btn-ink-outline min-h-11 px-3.5 rounded-lg text-xs items-center gap-1.5 cursor-pointer"
                            >
                                <span x-text="$store.tokoku.isCompared({{ $featuredProduct->id }}) ? 'Dibandingkan' : 'Bandingkan'">Bandingkan</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Compact 2-pill shortcut on mobile (<640px), full cards on sm+ --}}
                <div class="grid grid-cols-2 gap-2 sm:hidden">
                    <a href="{{ route('pc-builder') }}" wire:navigate class="btn-ink-outline min-h-11 px-3 rounded-xl text-xs inline-flex items-center justify-center gap-1.5 text-center">
                        <span>Rakit PC ({{ number_format($catalogPartsCount, 0, ',', '.') }})</span>
                    </a>
                    <a href="{{ route('service.index', ['tab' => 'tracking']) }}" wire:navigate class="btn-ink-outline min-h-11 px-3 rounded-xl text-xs inline-flex items-center justify-center gap-1.5 text-center">
                        <span>Cek Resi Servis</span>
                    </a>
                </div>

                <div class="hidden sm:grid lg:col-span-5 grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4">
                    <div class="surface-ink-card rounded-xl p-4 sm:p-5 flex flex-col justify-between gap-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <span class="text-xs font-semibold text-[#FDBA74] uppercase tracking-wide">Simulasi Rakit PC</span>
                                <p class="font-display font-bold text-base leading-snug">
                                    Cek kecocokan dari {{ number_format($catalogPartsCount, 0, ',', '.') }} part &amp; diagram casing
                                </p>
                                <p class="text-xs ink-muted">
                                    Pilih CPU, lihat soket cocok otomatis, estimasi daya PSU, hingga kelas performanya.
                                </p>
                            </div>
                            <span class="w-10 h-10 rounded-xl bg-[#0F172A] border border-[#EA580C] text-[#FB923C] inline-flex items-center justify-center shrink-0" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                </svg>
                            </span>
                        </div>
                        <div>
                            <a
                                href="{{ route('pc-builder') }}"
                                wire:navigate
                                class="btn-ink-outline min-h-11 px-4 rounded-lg text-xs sm:text-sm inline-flex items-center gap-2"
                            >
                                <span>Buka Simulasi Rakit PC</span>
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>

                    <div class="surface-ink-card rounded-xl p-4 sm:p-5 space-y-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-display font-bold text-sm">Lacak Tiket Servis Instan</p>
                            <a href="{{ route('service.index') }}" wire:navigate class="text-xs font-semibold text-[#FDBA74] hover:underline py-1">
                                Booking Servis
                            </a>
                        </div>
                        <form wire:submit="quickCheckTicket" class="grid grid-cols-12 gap-2">
                            <div class="col-span-12 sm:col-span-6">
                                <label for="hero-tiket-cepat" class="sr-only">Nomor Tiket Servis</label>
                                <input
                                    id="hero-tiket-cepat"
                                    type="text"
                                    wire:model="quickTicketNumber"
                                    placeholder="SRV-202611-0001"
                                    required
                                    class="input-ink w-full min-h-11 px-3 rounded-lg text-xs sm:text-sm font-mono"
                                >
                            </div>
                            <div class="col-span-5 sm:col-span-3">
                                <label for="hero-pin-cepat" class="sr-only">PIN 4 Digit Akhir WA</label>
                                <input
                                    id="hero-pin-cepat"
                                    type="text"
                                    maxlength="4"
                                    wire:model="quickTicketPin"
                                    placeholder="PIN 4"
                                    required
                                    class="input-ink w-full min-h-11 px-3 rounded-lg text-xs sm:text-sm font-mono"
                                >
                            </div>
                            <div class="col-span-7 sm:col-span-3">
                                <button
                                    type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="quickCheckTicket"
                                    class="btn-ink-outline w-full min-h-11 px-3 rounded-lg text-xs sm:text-sm cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                                >
                                    <span wire:loading.remove wire:target="quickCheckTicket">Cek</span>
                                    <span wire:loading wire:target="quickCheckTicket">...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- QUICK HORIZONTAL SCROLLABLE CATEGORY STRIP --}}
    <section aria-label="Pilih Kategori Cepat" class="-mx-3.5 px-3.5 sm:mx-0 sm:px-0">
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
            <button
                type="button"
                wire:click="$set('categorySlug', '')"
                class="min-h-11 px-3.5 rounded-xl text-xs sm:text-sm inline-flex items-center gap-2 shrink-0 cursor-pointer transition-colors {{ $categorySlug === '' ? 'btn-accent' : 'surface-card hover:border-[var(--accent-soft-border)] font-semibold' }}"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span>Semua</span>
            </button>

            @foreach($categories as $cat)
                @php $isSelectedCat = $categorySlug === $cat->slug; @endphp
                <button
                    type="button"
                    wire:click="$set('categorySlug', '{{ $cat->slug }}')"
                    class="min-h-11 px-3 rounded-xl text-xs sm:text-sm inline-flex items-center gap-2 shrink-0 cursor-pointer transition-colors {{ $isSelectedCat ? 'btn-ghost-active' : 'surface-card hover:border-[var(--accent-soft-border)] font-medium' }}"
                >
                    <span class="whitespace-nowrap">{{ $cat->name }}</span>
                    <span class="text-[11px] tabular-nums {{ $isSelectedCat ? 'font-bold' : 'text-muted-ink' }}">{{ $cat->products_count }}</span>
                </button>
            @endforeach
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        {{-- DESKTOP SIDEBAR ONLY (Hidden on mobile so mobile goes straight to 2-column product grid!) --}}
        <aside aria-label="Saringan Barang dan Pintasan Layanan" class="hidden lg:block lg:col-span-3 space-y-5 lg:sticky lg:top-24">
            <div class="surface-card rounded-xl p-5 space-y-4">
                <div>
                    <label for="input-cari-barang" class="block text-sm font-semibold mb-1.5">Cari Barang</label>
                    <div class="relative">
                        <input
                            id="input-cari-barang"
                            type="search"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Ketik VGA, Ryzen, Laptop..."
                            class="input-field w-full min-h-11 pl-10 pr-3.5 rounded-lg text-sm"
                        >
                        <svg class="w-4 h-4 text-muted-ink absolute left-3.5 top-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                        </svg>
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="space-y-2">
                        <label for="pilih-kondisi" class="block text-xs font-semibold text-muted-ink">Kondisi Barang</label>
                        <select
                            id="pilih-kondisi"
                            wire:model.live="conditionFilter"
                            class="input-field w-full min-h-11 px-3 rounded-lg text-sm"
                        >
                            <option value="all">Semua Kondisi</option>
                            <option value="new">Baru Segel (BNIB)</option>
                            <option value="used_normal">Second Mulus &amp; Normal</option>
                            <option value="minus_parts">Unit Minus / Bahan Part</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <p class="text-xs font-semibold text-muted-ink">Kategori Etalase</p>
                        <div class="space-y-1">
                            <button
                                type="button"
                                wire:click="$set('categorySlug', '')"
                                class="w-full min-h-11 px-3 rounded-lg text-sm flex items-center justify-between text-left cursor-pointer transition-colors {{ $categorySlug === '' ? 'btn-ghost-active' : 'hover:bg-[var(--bg-subtle)]' }}"
                            >
                                <span>Semua Barang</span>
                            </button>
                            @foreach($categories as $cat)
                                <button
                                    type="button"
                                    wire:click="$set('categorySlug', '{{ $cat->slug }}')"
                                    class="w-full min-h-11 px-3 rounded-lg text-sm flex items-center justify-between text-left cursor-pointer transition-colors {{ $categorySlug === $cat->slug ? 'btn-ghost-active' : 'hover:bg-[var(--bg-subtle)]' }}"
                                >
                                    <span class="truncate pr-2">{{ $cat->name }}</span>
                                    <span class="text-xs text-muted-ink tabular-nums">{{ $cat->products_count }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-3 border-t border-[var(--border-default)] space-y-2">
                        <label class="flex items-center gap-2.5 min-h-11 cursor-pointer text-sm">
                            <input type="checkbox" wire:model.live="onlyNegotiable" class="w-4 h-4 rounded accent-[#C2410C]">
                            <span>Bisa nego harga</span>
                        </label>
                        <label class="flex items-center gap-2.5 min-h-11 cursor-pointer text-sm">
                            <input type="checkbox" wire:model.live="hideSold" class="w-4 h-4 rounded accent-[#C2410C]">
                            <span>Hanya yang masih ready</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="surface-card rounded-xl p-5 space-y-3">
                <div>
                    <h2 class="font-display font-bold text-sm">Lacak Tiket Servis</h2>
                    <p class="text-xs text-muted-ink mt-0.5">Cek progres PC atau laptop yang sedang diservis di bengkel kami.</p>
                </div>
                <form wire:submit="quickCheckTicket" class="space-y-2.5">
                    <div>
                        <label for="tiket-cepat" class="sr-only">Nomor Tiket Servis</label>
                        <input
                            id="tiket-cepat"
                            type="text"
                            wire:model="quickTicketNumber"
                            placeholder="No. Tiket (SRV-202611-0001)"
                            required
                            class="input-field w-full min-h-11 px-3 rounded-lg text-sm font-mono"
                        >
                    </div>
                    <div class="grid grid-cols-12 gap-2">
                        <div class="col-span-5">
                            <label for="pin-cepat" class="sr-only">4 Digit Akhir WA</label>
                            <input
                                id="pin-cepat"
                                type="text"
                                maxlength="4"
                                wire:model="quickTicketPin"
                                placeholder="PIN 4"
                                required
                                class="input-field w-full min-h-11 px-3 rounded-lg text-sm font-mono"
                            >
                        </div>
                        <div class="col-span-7">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="quickCheckTicket"
                                class="btn-secondary w-full min-h-11 px-3 rounded-lg text-sm cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                <span wire:loading.remove wire:target="quickCheckTicket">Cek Status</span>
                                <span wire:loading wire:target="quickCheckTicket">Memuat...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </aside>

        <div class="lg:col-span-9 space-y-4 sm:space-y-5">
            <div class="surface-card rounded-xl p-3.5 sm:p-5 space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h1 class="font-display text-lg sm:text-2xl font-extrabold tracking-tight">Pilihan Barang Hari Ini</h1>
                        <p class="text-xs sm:text-sm text-muted-ink">
                            <strong class="text-[var(--text-primary)] tabular-nums">{{ $products->total() }}</strong> barang siap pantau dari stok workshop.
                        </p>
                    </div>

                    <div class="hidden sm:flex items-center gap-2 shrink-0">
                        <label for="urutkan-produk" class="text-xs font-medium text-muted-ink">Urutkan:</label>
                        <select id="urutkan-produk" wire:model.live="sortBy" class="input-field min-h-11 px-3 rounded-lg text-sm">
                            <option value="terbaru">Paling Baru</option>
                            <option value="termurah">Harga Terendah</option>
                            <option value="termahal">Harga Tertinggi</option>
                        </select>
                    </div>
                </div>

                @if($hasActiveFilter)
                    <div class="pt-2.5 border-t border-[var(--border-default)] flex flex-wrap items-center gap-1.5" aria-label="Saringan Aktif">
                        @if($search !== '')
                            <button type="button" wire:click="$set('search', '')" class="btn-ghost-active min-h-9 px-2.5 rounded-lg text-xs inline-flex items-center gap-1.5 cursor-pointer">
                                <span>"{{ $search }}"</span>
                                <span aria-hidden="true">&times;</span>
                            </button>
                        @endif
                        @if($activeCategory)
                            <button type="button" wire:click="$set('categorySlug', '')" class="btn-ghost-active min-h-9 px-2.5 rounded-lg text-xs inline-flex items-center gap-1.5 cursor-pointer">
                                <span>{{ $activeCategory->name }}</span>
                                <span aria-hidden="true">&times;</span>
                            </button>
                        @endif
                        @if($conditionFilter !== 'all')
                            <button type="button" wire:click="$set('conditionFilter', 'all')" class="btn-ghost-active min-h-9 px-2.5 rounded-lg text-xs inline-flex items-center gap-1.5 cursor-pointer">
                                <span>{{ $conditionLabels[$conditionFilter] ?? $conditionFilter }}</span>
                                <span aria-hidden="true">&times;</span>
                            </button>
                        @endif
                        @if($onlyNegotiable)
                            <button type="button" wire:click="$set('onlyNegotiable', false)" class="btn-ghost-active min-h-9 px-2.5 rounded-lg text-xs inline-flex items-center gap-1.5 cursor-pointer">
                                <span>Bisa Nego</span>
                                <span aria-hidden="true">&times;</span>
                            </button>
                        @endif
                        @if($hideSold)
                            <button type="button" wire:click="$set('hideSold', false)" class="btn-ghost-active min-h-9 px-2.5 rounded-lg text-xs inline-flex items-center gap-1.5 cursor-pointer">
                                <span>Ready</span>
                                <span aria-hidden="true">&times;</span>
                            </button>
                        @endif
                        <button type="button" wire:click="resetFilters" class="btn-secondary min-h-9 px-2.5 rounded-lg text-xs cursor-pointer">
                            Reset Filter
                        </button>
                    </div>
                @endif
            </div>

            <div
                wire:loading.grid
                wire:target="search,categorySlug,conditionFilter,onlyNegotiable,hideSold,sortBy,resetFilters"
                class="hidden grid-cols-2 sm:grid-cols-2 xl:grid-cols-3 gap-2.5 sm:gap-5"
                role="status"
                aria-label="Memuat daftar barang"
            >
                @for($i = 0; $i < 6; $i++)
                    <div class="surface-card rounded-xl overflow-hidden">
                        <div class="skeleton-block aspect-[4/3] w-full"></div>
                        <div class="p-3 sm:p-4 space-y-2.5">
                            <div class="skeleton-block h-4 sm:h-5 w-2/3 rounded"></div>
                            <div class="skeleton-block h-3.5 sm:h-4 w-full rounded"></div>
                            <div class="skeleton-block h-10 rounded-lg"></div>
                        </div>
                    </div>
                @endfor
            </div>

            <div wire:loading.remove wire:target="search,categorySlug,conditionFilter,onlyNegotiable,hideSold,sortBy,resetFilters">
                @if($products->isEmpty())
                    <div class="surface-card rounded-xl p-8 sm:p-10 text-center space-y-3">
                        <h2 class="font-display text-base sm:text-lg font-bold">Belum ada barang yang sesuai pencarian Anda</h2>
                        <p class="text-xs sm:text-sm text-muted-ink max-w-md mx-auto">
                            Coba gunakan kata kunci yang lebih umum atau tampilkan kembali seluruh kategori barang.
                        </p>
                        <div class="pt-1">
                            <button type="button" wire:click="resetFilters" class="btn-accent min-h-11 px-5 rounded-lg text-sm cursor-pointer">
                                Tampilkan Semua Stok Etalase
                            </button>
                        </div>
                    </div>
                @else
                    {{-- NATIVE MARKETPLACE 2-COLUMN GRID ON MOBILE (grid-cols-2), 3-COLUMN ON XL --}}
                    <div class="grid grid-cols-2 sm:grid-cols-2 xl:grid-cols-3 gap-2.5 sm:gap-5">
                        @foreach($products as $product)
                            @php
                                $productUrl = route('product.show', $product->slug);
                                $waUrl = $store->whatsappUrl($product->whatsappInquiryMessage($productUrl));
                                $savedPayload = [
                                    'id' => $product->id,
                                    'slug' => $product->slug,
                                    'name' => $product->name,
                                    'price' => $product->formattedPrice(),
                                    'condition' => $product->conditionShortLabel(),
                                    'image' => $product->primaryImageUrl(),
                                    'url' => $productUrl,
                                ];
                                $comparePayload = [
                                    'id' => $product->id,
                                    'name' => $product->name,
                                    'price' => $product->formattedPrice(),
                                    'priceNum' => $product->price,
                                    'condition' => $product->conditionShortLabel(),
                                    'negotiable' => (bool) $product->is_negotiable,
                                    'warranty' => $product->warranty_info ?: 'Cek sepuasnya di toko',
                                    'specs' => implode(' · ', $product->specSummaryBadges()),
                                    'image' => $product->primaryImageUrl(),
                                    'url' => $productUrl,
                                    'waUrl' => $waUrl,
                                ];
                            @endphp
                            <article class="product-card rounded-xl overflow-hidden flex flex-col justify-between">
                                <div>
                                    <div class="relative bg-[#0F172A] aspect-[4/3] overflow-hidden">
                                        <a href="{{ $productUrl }}" wire:navigate class="block w-full h-full">
                                            @if($product->primaryImageUrl())
                                                <img
                                                    src="{{ $product->primaryImageUrl() }}"
                                                    alt="{{ $product->name }}"
                                                    loading="lazy"
                                                    class="w-full h-full object-cover"
                                                >
                                            @else
                                                <div class="w-full h-full flex items-center justify-center p-3 text-center text-[11px] ink-muted">
                                                    [Foto belum tersedia: {{ $product->sku }}]
                                                </div>
                                            @endif
                                        </a>

                                        <div class="absolute top-2 left-2 sm:top-3 sm:left-3 flex flex-col sm:flex-row flex-wrap gap-1 pointer-events-none max-w-[70%]">
                                            <span class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md text-[10px] sm:text-xs font-semibold leading-tight {{ match($product->condition) {
                                                'new' => 'badge-ok',
                                                'like_new' => 'badge-accent-soft',
                                                'good_used' => 'badge-warn',
                                                default => 'badge-err',
                                            } }}">
                                                {{ $product->conditionShortLabel() }}
                                            </span>

                                            @if($product->availability_status !== 'available')
                                                <span class="badge-err px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md text-[10px] sm:text-xs font-semibold leading-tight">
                                                    {{ $product->availabilityLabel() }}
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Floating Bookmark + Compare Icon Buttons (44px tap target on mobile too!) --}}
                                        <div class="absolute top-1.5 right-1.5 sm:top-2.5 sm:right-2.5 flex flex-col gap-1.5">
                                            <button
                                                type="button"
                                                @click.prevent="$store.tokoku.toggleSaved(@js($savedPayload))"
                                                :aria-label="$store.tokoku.isSaved({{ $product->id }}) ? 'Hapus dari tersimpan' : 'Simpan barang ini'"
                                                :aria-pressed="$store.tokoku.isSaved({{ $product->id }}).toString()"
                                                class="min-h-11 min-w-11 rounded-lg inline-flex items-center justify-center cursor-pointer transition-colors"
                                                :class="$store.tokoku.isSaved({{ $product->id }}) ? 'btn-accent' : 'btn-secondary'"
                                            >
                                                <svg class="w-4 h-4" :fill="$store.tokoku.isSaved({{ $product->id }}) ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                                </svg>
                                            </button>

                                            <button
                                                type="button"
                                                @click.prevent="$store.tokoku.toggleCompare(@js($comparePayload))"
                                                :aria-label="$store.tokoku.isCompared({{ $product->id }}) ? 'Hapus dari perbandingan' : 'Bandingkan barang ini'"
                                                :aria-pressed="$store.tokoku.isCompared({{ $product->id }}).toString()"
                                                class="sm:hidden min-h-11 min-w-11 rounded-lg inline-flex items-center justify-center cursor-pointer transition-colors"
                                                :class="$store.tokoku.isCompared({{ $product->id }}) ? 'btn-ghost-active' : 'btn-secondary'"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="p-2.5 sm:p-4 space-y-1 sm:space-y-2">
                                        <div class="flex flex-wrap items-baseline justify-between gap-1">
                                            <p class="font-display text-sm sm:text-lg font-extrabold tabular-nums leading-tight">{{ $product->formattedPrice() }}</p>
                                            @if($product->is_negotiable)
                                                <span class="text-[10px] sm:text-xs font-semibold text-[var(--accent-soft-fg)]">Nego</span>
                                            @endif
                                        </div>

                                        <h2 class="font-semibold text-xs sm:text-[15px] leading-snug line-clamp-2 min-h-[2.1rem] sm:min-h-0">
                                            <a href="{{ $productUrl }}" wire:navigate class="hover:underline">
                                                {{ $product->name }}
                                            </a>
                                        </h2>

                                        <p class="text-[11px] sm:text-xs text-muted-ink truncate">
                                            Stok {{ $product->stock }} &middot; {{ $product->location_area }}
                                        </p>

                                        @php $badges = array_slice($product->specSummaryBadges(), 0, 2); @endphp
                                        @if(count($badges) > 0)
                                            <div class="hidden sm:flex flex-wrap gap-1.5 pt-1">
                                                @foreach($badges as $specBadge)
                                                    <span class="surface-subtle px-2 py-0.5 rounded-md text-xs text-muted-ink">
                                                        {{ $specBadge }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="px-2.5 pb-2.5 sm:px-4 sm:pb-4 pt-1 sm:pt-2 space-y-2">
                                    <button
                                        type="button"
                                        @click="$store.tokoku.toggleCompare(@js($comparePayload))"
                                        :aria-pressed="$store.tokoku.isCompared({{ $product->id }}).toString()"
                                        class="hidden sm:inline-flex w-full min-h-11 px-3 rounded-lg text-xs items-center justify-center gap-1.5 cursor-pointer"
                                        :class="$store.tokoku.isCompared({{ $product->id }}) ? 'btn-ghost-active' : 'btn-secondary'"
                                    >
                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                        <span x-text="$store.tokoku.isCompared({{ $product->id }}) ? 'Terpilih untuk Bandingkan' : 'Bandingkan'">Bandingkan</span>
                                    </button>

                                    <div class="flex items-center gap-1.5 sm:gap-2">
                                        <a
                                            href="{{ $productUrl }}"
                                            wire:navigate
                                            class="hidden sm:inline-flex btn-secondary flex-1 min-h-11 px-3 rounded-lg text-sm items-center justify-center"
                                        >
                                            Detail
                                        </a>
                                        <a
                                            href="{{ $waUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="btn-accent flex-1 min-h-11 px-2.5 sm:px-3 rounded-lg text-xs sm:text-sm inline-flex items-center justify-center text-center"
                                        >
                                            Chat WA
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="pt-4">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>

            <section aria-label="Layanan Perakitan dan Servis" class="grid grid-cols-1 md:grid-cols-12 gap-4 sm:gap-5 pt-2 sm:pt-4">
                <div class="md:col-span-7 surface-ink rounded-2xl p-4 sm:p-6 flex flex-col justify-between gap-4">
                    <div class="space-y-2">
                        <span class="inline-block px-2.5 py-0.5 rounded-md bg-[#FB923C] text-[#0F172A] font-display font-bold text-xs">
                            Simulasi Interaktif &middot; {{ number_format($catalogPartsCount, 0, ',', '.') }} Part
                        </span>
                        <h2 class="font-display text-lg sm:text-xl font-extrabold">Susun PC di Diagram Casing Interaktif</h2>
                        <p class="text-xs sm:text-sm ink-muted leading-relaxed">
                            Pilih prosesor, motherboard, VGA, hingga PSU langsung dari diagram casing dengan pengecekan soket, RAM, dan daya otomatis.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
                        <a href="{{ route('pc-builder') }}" wire:navigate class="btn-ink-accent min-h-11 px-3.5 sm:px-4 rounded-lg text-xs sm:text-sm inline-flex items-center justify-center text-center">
                            Mulai Rakit PC
                        </a>
                        <a href="{{ route('pc-builder', ['kode' => 'RKT-CONTOH']) }}" wire:navigate class="btn-ink-outline min-h-11 px-3.5 sm:px-4 rounded-lg text-xs sm:text-sm inline-flex items-center justify-center text-center">
                            Contoh Rakitan
                        </a>
                    </div>
                </div>

                <div class="md:col-span-5 surface-card rounded-2xl p-4 sm:p-6 flex flex-col justify-between gap-4">
                    <div class="space-y-2">
                        <span class="badge-accent-soft inline-block px-2.5 py-0.5 rounded-md text-xs font-semibold">
                            Bengkel &amp; Perawatan Suhu
                        </span>
                        <h2 class="font-display text-lg font-extrabold">Servis Transparan Tanpa Tebak Biaya</h2>
                        <p class="text-xs sm:text-sm text-muted-ink leading-relaxed">
                            Daftar jadwal kedatangan atau penjemputan, lalu pantau riwayat pengerjaan dan perubahan suhu perangkat Anda lewat kode resi.
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('service.index') }}" wire:navigate class="btn-secondary w-full sm:w-auto min-h-11 px-4 rounded-lg text-sm inline-flex items-center justify-center">
                            Buka Layanan Servis
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
