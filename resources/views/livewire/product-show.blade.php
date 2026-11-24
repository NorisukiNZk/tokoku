@php
    $savedPayload = [
        'id' => $product->id,
        'slug' => $product->slug,
        'name' => $product->name,
        'price' => $product->formattedPrice(),
        'condition' => $product->conditionShortLabel(),
        'image' => $product->primaryImageUrl(),
        'url' => route('product.show', $product->slug),
    ];
@endphp
<div
    x-data="{ zoomOpen: false }"
    @keydown.escape.window="zoomOpen = false"
    class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-8"
>
    <nav aria-label="Jalur Halaman" class="text-sm text-muted-ink flex flex-wrap items-center gap-2">
        <a href="{{ route('home') }}" wire:navigate class="hover:underline py-1">Belanja Barang</a>
        <span>/</span>
        @if($product->category)
            <a href="{{ route('home', ['kategori' => $product->category->slug]) }}" wire:navigate class="hover:underline py-1">
                {{ $product->category->name }}
            </a>
            <span>/</span>
        @endif
        <span class="text-[var(--text-primary)] font-medium truncate max-w-xs">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <div class="lg:col-span-6 space-y-3">
            <div class="surface-card rounded-xl overflow-hidden bg-[var(--bg-subtle)] aspect-[4/3] relative flex items-center justify-center">
                @if($activeImage)
                    <button
                        type="button"
                        @click="zoomOpen = true"
                        aria-label="Perbesar foto produk"
                        class="w-full h-full cursor-zoom-in block"
                    >
                        <img src="{{ $activeImage }}" alt="{{ $product->name }}" class="w-full h-full object-contain">
                    </button>
                    <button
                        type="button"
                        @click="zoomOpen = true"
                        class="btn-secondary min-h-10 px-3 rounded-lg text-xs absolute bottom-3 right-3 cursor-pointer"
                    >
                        Perbesar Foto
                    </button>
                @else
                    <span class="text-sm text-muted-ink">[Foto belum diunggah: {{ $product->sku }}]</span>
                @endif
            </div>

            @if($galleryUrls->count() > 1)
                <div class="flex flex-wrap gap-2.5">
                    @foreach($galleryUrls as $index => $imgUrl)
                        <button
                            type="button"
                            wire:click="selectImage('{{ $imgUrl }}')"
                            class="w-20 h-16 rounded-lg overflow-hidden border-2 cursor-pointer transition-colors {{ $activeImage === $imgUrl ? 'border-[var(--accent)]' : 'border-[var(--border-default)]' }}"
                            aria-label="Lihat foto {{ $index + 1 }}"
                        >
                            <img src="{{ $imgUrl }}" alt="" class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="lg:col-span-6 surface-card rounded-xl p-5 sm:p-7 space-y-6">
            <div class="space-y-2.5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-3 py-1 rounded-md text-xs font-semibold {{ match($product->condition) {
                            'new' => 'badge-ok',
                            'like_new' => 'badge-accent-soft',
                            'good_used' => 'badge-warn',
                            default => 'badge-err',
                        } }}">
                            {{ $product->conditionLabel() }}
                        </span>
                        <span class="surface-subtle px-3 py-1 rounded-md text-xs text-muted-ink">
                            {{ $product->availabilityLabel() }} &middot; Stok {{ $product->stock }}
                        </span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            @click="$store.tokoku.toggleSaved(@js($savedPayload))"
                            class="min-h-11 px-3 rounded-lg text-xs font-semibold inline-flex items-center gap-1.5 cursor-pointer"
                            :class="$store.tokoku.isSaved({{ $product->id }}) ? 'btn-ghost-active' : 'btn-secondary'"
                        >
                            <svg class="w-4 h-4" :fill="$store.tokoku.isSaved({{ $product->id }}) ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                            </svg>
                            <span x-text="$store.tokoku.isSaved({{ $product->id }}) ? 'Tersimpan' : 'Simpan'">Simpan</span>
                        </button>

                        <button
                            type="button"
                            @click="$store.tokoku.shareLink(@js($product->name), @js($product->name . ' - ' . $product->formattedPrice()), window.location.href)"
                            class="btn-secondary min-h-11 px-3 rounded-lg text-xs inline-flex items-center gap-1.5 cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            <span>Bagikan</span>
                        </button>
                    </div>
                </div>

                <h1 class="text-xl sm:text-2xl font-bold leading-snug">{{ $product->name }}</h1>

                <div class="flex flex-wrap items-baseline gap-3 pt-1">
                    <p class="text-2xl sm:text-3xl font-bold tabular-nums">{{ $product->formattedPrice() }}</p>
                    <span class="text-sm font-medium {{ $product->is_negotiable ? 'text-[var(--accent-soft-fg)]' : 'text-muted-ink' }}">
                        {{ $product->is_negotiable ? 'Bisa nego wajar' : 'Harga pas (nett)' }}
                    </span>
                </div>
            </div>

            <div class="surface-subtle rounded-xl p-4 space-y-3 text-sm">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1">
                    <span class="font-semibold">Garansi</span>
                    <span class="sm:col-span-2 text-muted-ink">{{ $product->warranty_info ?: 'Cek sepuasnya langsung di toko' }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 border-t border-[var(--border-default)] pt-2.5">
                    <span class="font-semibold">Lokasi &amp; COD</span>
                    <span class="sm:col-span-2 text-muted-ink">
                        {{ $product->location_area }} ({{ $product->cod_available ? 'Bisa cek di toko / COD' : 'Ambil di toko / kirim kurir' }})
                    </span>
                </div>
                @if($product->condition_notes)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 border-t border-[var(--border-default)] pt-2.5">
                        <span class="font-semibold">Kondisi Fisik</span>
                        <span class="sm:col-span-2">{{ $product->condition_notes }}</span>
                    </div>
                @endif
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <a
                    href="{{ $store->whatsappUrl($product->whatsappInquiryMessage(url()->current())) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn-accent flex-1 min-h-11 px-5 rounded-lg text-sm inline-flex items-center justify-center"
                >
                    {{ $product->is_negotiable ? 'Chat & Nego di WhatsApp' : 'Pesan via WhatsApp' }}
                </a>

                @if($product->show_in_pc_builder && $product->availability_status === 'available' && $product->stock > 0)
                    <a
                        href="{{ route('pc-builder', ['pasang' => $product->id]) }}"
                        wire:navigate
                        class="btn-secondary min-h-11 px-4 rounded-lg text-sm inline-flex items-center justify-center"
                    >
                        Pakai di Simulasi Rakit PC
                    </a>
                @endif
            </div>

            @if($product->description)
                <div class="space-y-1.5 pt-2 border-t border-[var(--border-default)]">
                    <h2 class="text-sm font-semibold">Deskripsi Barang</h2>
                    <p class="text-sm text-muted-ink leading-relaxed">{{ $product->description }}</p>
                </div>
            @endif

            @if(!empty($product->compatibility_specs))
                @php
                    $friendlyLabels = [
                        'socket' => 'Soket Prosesor',
                        'ram_type' => 'Tipe Memori',
                        'ram_slots' => 'Jumlah Slot RAM',
                        'modules_count' => 'Jumlah Keping',
                        'capacity_gb' => 'Kapasitas (GB)',
                        'form_factor' => 'Ukuran Motherboard',
                        'supported_form_factors' => 'Ukuran Mobo Didukung',
                        'supported_sockets' => 'Soket Didukung',
                        'gpu_length_mm' => 'Panjang VGA (mm)',
                        'max_gpu_mm' => 'Ruang Maksimal VGA (mm)',
                        'tdp_watt' => 'Konsumsi Daya (Watt)',
                        'psu_watt' => 'Daya Murni PSU (Watt)',
                        'has_igpu' => 'Grafis Bawaan (iGPU)',
                    ];
                @endphp
                <div class="space-y-2 pt-2 border-t border-[var(--border-default)]">
                    <h2 class="text-sm font-semibold">Informasi Teknis</h2>
                    <div class="grid grid-cols-2 gap-2.5 text-sm">
                        @foreach($product->compatibility_specs as $key => $val)
                            @if($val !== null && $val !== '' && $val !== [])
                                <div class="surface-subtle rounded-lg px-3 py-2">
                                    <span class="block text-xs text-muted-ink">{{ $friendlyLabels[$key] ?? ucfirst(str_replace('_', ' ', $key)) }}</span>
                                    <span class="font-semibold">
                                        @if(is_bool($val))
                                            {{ $val ? 'Ada' : 'Tidak ada' }}
                                        @elseif(is_array($val))
                                            {{ implode(', ', $val) }}
                                        @else
                                            {{ $val }}
                                        @endif
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($related->isNotEmpty())
        <section aria-labelledby="judul-barang-sejenis" class="space-y-4 pt-6 border-t border-[var(--border-default)]">
            <h2 id="judul-barang-sejenis" class="text-lg font-bold">Barang Sejenis Lainnya</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($related as $item)
                    <a href="{{ route('product.show', $item->slug) }}" wire:navigate class="product-card rounded-xl p-4 space-y-2 block">
                        <span class="inline-block text-xs font-semibold px-2.5 py-0.5 rounded-md {{ $item->condition === 'new' ? 'badge-ok' : 'badge-warn' }}">
                            {{ $item->conditionShortLabel() }}
                        </span>
                        <h3 class="font-medium text-sm line-clamp-2">{{ $item->name }}</h3>
                        <p class="font-bold text-base tabular-nums">{{ $item->formattedPrice() }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if($activeImage)
        <div
            x-show="zoomOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            role="dialog"
            aria-modal="true"
            aria-label="Pratinjau foto ukuran penuh"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 no-print"
            @click.self="zoomOpen = false"
        >
            <div class="relative max-w-4xl w-full flex flex-col items-end gap-3">
                <button type="button" @click="zoomOpen = false" class="btn-secondary min-h-11 px-4 rounded-lg text-sm cursor-pointer">
                    Tutup (Esc)
                </button>
                <div class="surface-card rounded-xl overflow-hidden w-full max-h-[80dvh] flex items-center justify-center p-2">
                    <img src="{{ $activeImage }}" alt="{{ $product->name }}" class="max-h-[76dvh] w-auto object-contain rounded-lg">
                </div>
            </div>
        </div>
    @endif
</div>
