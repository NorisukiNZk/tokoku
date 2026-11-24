<div x-data="{ mobileFilterOpen: false }" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        <aside aria-label="Saringan Barang dan Pintasan Layanan" class="lg:col-span-3 space-y-5 lg:sticky lg:top-24">
            <div class="surface-card rounded-xl p-4 sm:p-5 space-y-4">
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

                <div class="lg:hidden">
                    <button
                        type="button"
                        @click="mobileFilterOpen = !mobileFilterOpen"
                        :aria-expanded="mobileFilterOpen.toString()"
                        class="btn-secondary w-full min-h-11 px-4 rounded-lg text-sm flex items-center justify-between cursor-pointer"
                    >
                        <span>Filter Kategori &amp; Kondisi</span>
                        <span class="text-xs text-muted-ink" x-text="mobileFilterOpen ? 'Tutup' : 'Buka'">Buka</span>
                    </button>
                </div>

                <div :class="mobileFilterOpen ? 'block' : 'hidden lg:block'" class="space-y-5 pt-1 border-t border-[var(--border-default)] lg:border-t-0 lg:pt-0">
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
                            <input type="checkbox" wire:model.live="onlyNegotiable" class="w-4 h-4 rounded accent-[#1D4ED8]">
                            <span>Bisa nego harga</span>
                        </label>
                        <label class="flex items-center gap-2.5 min-h-11 cursor-pointer text-sm">
                            <input type="checkbox" wire:model.live="hideSold" class="w-4 h-4 rounded accent-[#1D4ED8]">
                            <span>Hanya yang masih ready</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="surface-card rounded-xl p-4 sm:p-5 space-y-3">
                <div>
                    <h2 class="font-semibold text-sm">Lacak Tiket Servis</h2>
                    <p class="text-xs text-muted-ink mt-0.5">Cek progres PC atau laptop yang sedang diservis di toko kami.</p>
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
                            class="input-field w-full min-h-11 px-3 rounded-lg text-sm"
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
                                placeholder="PIN 4 digit"
                                required
                                class="input-field w-full min-h-11 px-3 rounded-lg text-sm"
                            >
                        </div>
                        <div class="col-span-7">
                            <button type="submit" class="btn-secondary w-full min-h-11 px-3 rounded-lg text-sm cursor-pointer">
                                Cek Status
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </aside>

        <div class="lg:col-span-9 space-y-5">
            <div class="surface-card rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight">Pilihan Barang Hari Ini</h1>
                    <p class="text-sm text-muted-ink">
                        Stok komponen baru bersegel &amp; unit second pilihan yang sudah diuji teknisi kami.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <label for="urutkan-produk" class="text-xs font-medium text-muted-ink">Urutkan:</label>
                    <select id="urutkan-produk" wire:model.live="sortBy" class="input-field min-h-11 px-3 rounded-lg text-sm">
                        <option value="terbaru">Paling Baru</option>
                        <option value="termurah">Harga Terendah</option>
                        <option value="termahal">Harga Tertinggi</option>
                    </select>

                    @if($search !== '' || $categorySlug !== '' || $conditionFilter !== 'all' || $onlyNegotiable || $hideSold)
                        <button type="button" wire:click="resetFilters" class="btn-secondary min-h-11 px-3.5 rounded-lg text-xs sm:text-sm cursor-pointer">
                            Hapus Filter
                        </button>
                    @endif
                </div>
            </div>

            <div wire:loading.delay class="surface-subtle rounded-xl p-4 text-sm font-medium" role="status" aria-live="polite">
                Memuat daftar barang...
            </div>

            @if($products->isEmpty())
                <div class="surface-card rounded-xl p-10 text-center space-y-3">
                    <h2 class="text-lg font-semibold">Belum ada barang yang sesuai pencarian Anda</h2>
                    <p class="text-sm text-muted-ink max-w-md mx-auto">
                        Coba gunakan kata kunci yang lebih umum atau tampilkan kembali seluruh kategori barang untuk melihat stok lainnya.
                    </p>
                    <div class="pt-2">
                        <button type="button" wire:click="resetFilters" class="btn-accent min-h-11 px-5 rounded-lg text-sm cursor-pointer">
                            Tampilkan Semua Stok Etalase
                        </button>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach($products as $product)
                        <article class="product-card rounded-xl overflow-hidden flex flex-col justify-between">
                            <div>
                                <a href="{{ route('product.show', $product->slug) }}" class="block relative bg-[var(--bg-subtle)] aspect-[4/3] overflow-hidden">
                                    @if($product->primaryImageUrl())
                                        <img
                                            src="{{ $product->primaryImageUrl() }}"
                                            alt="{{ $product->name }}"
                                            loading="lazy"
                                            class="w-full h-full object-cover"
                                        >
                                    @else
                                        <div class="w-full h-full flex items-center justify-center p-4 text-center text-xs text-muted-ink">
                                            [Foto belum tersedia: {{ $product->sku }}]
                                        </div>
                                    @endif

                                    <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                                        <span class="px-2.5 py-1 rounded-md text-xs font-semibold {{ match($product->condition) {
                                            'new' => 'badge-ok',
                                            'like_new' => 'badge-accent-soft',
                                            'good_used' => 'badge-warn',
                                            default => 'badge-err',
                                        } }}">
                                            {{ $product->conditionShortLabel() }}
                                        </span>

                                        @if($product->availability_status !== 'available')
                                            <span class="badge-err px-2.5 py-1 rounded-md text-xs font-semibold">
                                                {{ $product->availabilityLabel() }}
                                            </span>
                                        @endif
                                    </div>
                                </a>

                                <div class="p-4 space-y-2">
                                    <div class="flex items-baseline justify-between gap-2">
                                        <p class="text-lg font-bold tabular-nums">{{ $product->formattedPrice() }}</p>
                                        @if($product->is_negotiable)
                                            <span class="text-xs font-medium text-[var(--accent-soft-fg)]">Bisa nego</span>
                                        @endif
                                    </div>

                                    <h2 class="font-medium text-[15px] leading-snug line-clamp-2">
                                        <a href="{{ route('product.show', $product->slug) }}" class="hover:underline">
                                            {{ $product->name }}
                                        </a>
                                    </h2>

                                    <p class="text-xs text-muted-ink">
                                        {{ $product->location_area }} &middot; Stok {{ $product->stock }}
                                    </p>

                                    @php $badges = array_slice($product->specSummaryBadges(), 0, 2); @endphp
                                    @if(count($badges) > 0)
                                        <div class="flex flex-wrap gap-1.5 pt-1">
                                            @foreach($badges as $specBadge)
                                                <span class="surface-subtle px-2 py-0.5 rounded-md text-xs text-muted-ink">
                                                    {{ $specBadge }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="px-4 pb-4 pt-2 flex items-center gap-2">
                                <a
                                    href="{{ route('product.show', $product->slug) }}"
                                    class="btn-secondary flex-1 min-h-11 px-3 rounded-lg text-sm inline-flex items-center justify-center"
                                >
                                    Detail
                                </a>
                                <a
                                    href="{{ $store->whatsappUrl($product->whatsappInquiryMessage(route('product.show', $product->slug))) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn-accent flex-1 min-h-11 px-3 rounded-lg text-sm inline-flex items-center justify-center"
                                >
                                    Chat WA
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="pt-4">
                    {{ $products->links() }}
                </div>
            @endif

            <section aria-label="Layanan Perakitan dan Servis" class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4">
                <div class="surface-card rounded-xl p-5 sm:p-6 flex flex-col justify-between gap-4">
                    <div class="space-y-1.5">
                        <h2 class="text-lg font-bold">Bingung Memilih Part yang Saling Cocok?</h2>
                        <p class="text-sm text-muted-ink">
                            Gunakan halaman Simulasi Rakit PC kami. Pilih prosesor lebih dulu, lalu sistem otomatis menyaring motherboard, RAM, dan ukuran PSU yang sesuai.
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('pc-builder') }}" class="btn-accent min-h-11 px-4 rounded-lg text-sm inline-flex items-center">
                            Mulai Simulasi Rakit PC
                        </a>
                    </div>
                </div>

                <div class="surface-card rounded-xl p-5 sm:p-6 flex flex-col justify-between gap-4">
                    <div class="space-y-1.5">
                        <h2 class="text-lg font-bold">Butuh Perawatan Suhu atau Servis Laptop?</h2>
                        <p class="text-sm text-muted-ink">
                            Lihat daftar kisaran biaya perawatan, buat jadwal kedatangan tanpa antre, dan pantau perubahan suhu unit Anda secara langsung.
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('service.index') }}" class="btn-secondary min-h-11 px-4 rounded-lg text-sm inline-flex items-center">
                            Lihat Layanan Servis
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
