<x-filament-panels::page>
    <div
        x-data="{ activeWorkspace: 'servis' }"
        class="tk-stack"
    >
        {{-- 1. UNIFIED DARK COMMAND BANNER (No double header!) --}}
        <header class="tk-cmd-banner tk-anim-enter">
            <div class="tk-hero-bar">
                <div style="max-width: 38rem;">
                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.45rem;">
                        <span class="tk-auth-badge">Beranda Operasional</span>
                        @if($store->is_placeholder_data)
                            <a href="{{ url('/admin/manage-store-settings') }}" style="font-size: 0.75rem; font-weight: 600; color: #FDBA74; text-decoration: underline;">
                                Lengkapi Nomor WA &amp; Alamat Toko &rarr;
                            </a>
                        @endif
                    </div>
                    <h1 class="tk-cmd-title">Pusat Kendali {{ $store->store_name }}</h1>
                    <p class="tk-cmd-sub">
                        Pantau antrean servis bengkel, kendalikan stok barang etalase, dan tindak lanjuti simulasi rakitan pelanggan dari satu layar fokus.
                    </p>
                </div>

                <div class="tk-cmd-actions-grid">
                    <a href="{{ url('/admin/products/create') }}" class="tk-cmd-btn-accent tk-cmd-span-mobile">
                        <svg class="tk-ico-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Tambah Barang</span>
                    </a>
                    <a href="{{ url('/admin/service-tickets/create') }}" class="tk-cmd-btn-outline">
                        <span>+ Terima Servis</span>
                    </a>
                    <a href="{{ url('/admin/papan-bengkel') }}" class="tk-cmd-btn-outline">
                        <span>Papan Bengkel</span>
                    </a>
                </div>
            </div>
        </header>

        {{-- 2. HIGH-CLARITY KPI STRIP (Staggered GPU fade-up) --}}
        <section aria-label="Ringkasan Data Operasional Nyata" class="tk-metrics-grid">
            <a href="{{ url('/admin/papan-bengkel') }}" class="tk-kpi-card tk-anim-enter tk-delay-1">
                <span class="tk-kpi-bar" style="background-color: #38BDF8;" aria-hidden="true"></span>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                    <span class="tk-metric-label">Antrean Bengkel Aktif</span>
                    <span class="tk-badge tk-badge-ok">{{ $readyPickupCount }} Siap Ambil</span>
                </div>
                <span class="tk-kpi-value">{{ $activeQueueCount }} Unit</span>
                <span class="tk-metric-note">Klik untuk membuka papan 4 lajur teknisi</span>
            </a>

            <a href="{{ url('/admin/service-tickets?activeTab=tunggu_biaya') }}" class="tk-kpi-card tk-anim-enter tk-delay-2">
                <span class="tk-kpi-bar" style="background-color: {{ $waitingApprovalCount > 0 ? '#EF4444' : '#10B981' }};" aria-hidden="true"></span>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                    <span class="tk-metric-label">Tunggu Persetujuan Biaya</span>
                    @if($waitingApprovalCount > 0)
                        <span class="tk-live-dot" aria-hidden="true"></span>
                    @endif
                </div>
                <span class="tk-kpi-value" style="{{ $waitingApprovalCount > 0 ? 'color: var(--tk-err-fg);' : '' }}">
                    {{ $waitingApprovalCount }} Tiket
                </span>
                <span class="tk-metric-note">Konfirmasi estimasi ke WhatsApp pelanggan</span>
            </a>

            <a href="{{ url('/admin/products') }}" class="tk-kpi-card tk-anim-enter tk-delay-3">
                <span class="tk-kpi-bar" style="background-color: #FB923C;" aria-hidden="true"></span>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                    <span class="tk-metric-label">Stok Etalase Siap Jual</span>
                </div>
                <span class="tk-kpi-value">{{ $availableProductsCount }} Produk</span>
                <span class="tk-metric-note">Nilai stok: Rp {{ number_format($inventoryValuation, 0, ',', '.') }}</span>
            </a>

            <a href="{{ url('/admin/pc-catalog-parts') }}" class="tk-kpi-card tk-anim-enter tk-delay-4">
                <span class="tk-kpi-bar" style="background-color: #10B981;" aria-hidden="true"></span>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                    <span class="tk-metric-label">Katalog API Rakit PC</span>
                </div>
                <span class="tk-kpi-value">{{ number_format($catalogPartsCount, 0, ',', '.') }}</span>
                <span class="tk-metric-note">Kurs Rp {{ number_format($store->usd_to_idr_rate ?: 16500, 0, ',', '.') }}/USD</span>
            </a>
        </section>

        {{-- 3. SINGLE-FOCUS WORKSPACE WITH INSTANT 0MS TAB SWITCHING --}}
        <section aria-label="Meja Kerja Utama Admin" class="tk-panel tk-anim-enter tk-delay-3" style="display: flex; flex-direction: column; gap: 1.15rem;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.85rem;">
                <div role="tablist" aria-label="Pilih Fokus Pekerjaan" class="tk-seg-tabs">
                    <button
                        type="button"
                        role="tab"
                        :aria-selected="(activeWorkspace === 'servis').toString()"
                        @click="activeWorkspace = 'servis'"
                        class="tk-seg-btn"
                        :class="activeWorkspace === 'servis' ? 'tk-seg-btn-active' : ''"
                    >
                        <span>Antrean Servis</span>
                        <span class="tk-count-pill">{{ $tickets->count() }}</span>
                    </button>

                    <button
                        type="button"
                        role="tab"
                        :aria-selected="(activeWorkspace === 'stok').toString()"
                        @click="activeWorkspace = 'stok'"
                        class="tk-seg-btn"
                        :class="activeWorkspace === 'stok' ? 'tk-seg-btn-active' : ''"
                    >
                        <span>Kontrol Cepat Stok</span>
                        <span class="tk-count-pill">{{ $products->count() }}</span>
                    </button>

                    <button
                        type="button"
                        role="tab"
                        :aria-selected="(activeWorkspace === 'rakitan').toString()"
                        @click="activeWorkspace = 'rakitan'"
                        class="tk-seg-btn"
                        :class="activeWorkspace === 'rakitan' ? 'tk-seg-btn-active' : ''"
                    >
                        <span>Simulasi Rakitan Masuk</span>
                        <span class="tk-count-pill">{{ $recentBuilds->count() }}</span>
                    </button>
                </div>

                {{-- Contextual Sub-Filter per Active Tab --}}
                <div x-show="activeWorkspace === 'servis'" class="tk-btn-row">
                    <button type="button" wire:click="setTicketFilter('active')" class="tk-btn tk-btn-sm {{ $ticketFilter === 'active' ? 'tk-btn-soft' : '' }}">
                        Semua Aktif
                    </button>
                    <button type="button" wire:click="setTicketFilter('approval')" class="tk-btn tk-btn-sm {{ $ticketFilter === 'approval' ? 'tk-btn-soft' : '' }}">
                        Tunggu Biaya
                    </button>
                    <button type="button" wire:click="setTicketFilter('ready')" class="tk-btn tk-btn-sm {{ $ticketFilter === 'ready' ? 'tk-btn-soft' : '' }}">
                        Siap Diambil
                    </button>
                </div>

                <div x-show="activeWorkspace === 'stok'" x-cloak class="tk-btn-row">
                    <button type="button" wire:click="setStockFilter('low')" class="tk-btn tk-btn-sm {{ $stockFilter === 'low' ? 'tk-btn-soft' : '' }}">
                        Stok Menipis (&le;2)
                    </button>
                    <button type="button" wire:click="setStockFilter('all')" class="tk-btn tk-btn-sm {{ $stockFilter === 'all' ? 'tk-btn-soft' : '' }}">
                        Semua Ready
                    </button>
                    <a href="{{ url('/admin/products') }}" class="tk-btn tk-btn-sm">Tabel Penuh &rarr;</a>
                </div>

                <div x-show="activeWorkspace === 'rakitan'" x-cloak class="tk-btn-row">
                    <a href="{{ url('/admin/pc-builds') }}" class="tk-btn tk-btn-sm">Kelola Semua Rakitan &rarr;</a>
                </div>
            </div>

            {{-- TAB 1: ANTREAN SERVIS (Full-width clean rows, zero squishing) --}}
            <div
                x-show="activeWorkspace === 'servis'"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
            >
                @if($tickets->isEmpty())
                    <div class="tk-empty">
                        Tidak ada tiket servis pada saringan ini. Klik tombol "+ Terima Servis" di atas untuk mendaftarkan unit baru.
                    </div>
                @else
                    <div class="tk-clean-table">
                        @foreach($tickets as $ticket)
                            <div class="tk-clean-row" wire:key="adm-ticket-{{ $ticket->id }}">
                                <div style="flex: 2 1 18rem; min-width: 0;">
                                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.45rem;">
                                        <span class="tk-mono" style="font-size: 0.8rem; font-weight: 700; color: var(--tk-accent);">
                                            {{ $ticket->ticket_number }}
                                        </span>
                                        <span class="tk-badge {{ match($ticket->status) {
                                            'waiting_approval' => 'tk-badge-err',
                                            'ready' => 'tk-badge-ok',
                                            'in_progress', 'testing_qc' => 'tk-badge-warn',
                                            default => 'tk-badge-blue',
                                        } }}">
                                            {{ $ticket->statusLabel() }}
                                        </span>
                                        @if($ticket->temp_before_c && $ticket->temp_after_c)
                                            <span class="tk-badge tk-badge-muted tk-mono">{{ $ticket->temp_before_c }}°C &rarr; {{ $ticket->temp_after_c }}°C</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 0.94rem; font-weight: 700; color: var(--tk-ink); margin-top: 0.22rem;">
                                        {{ $ticket->device_brand_model }}
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--tk-muted); margin-top: 0.1rem;">
                                        Pelanggan: <strong style="color: var(--tk-ink);">{{ $ticket->customer_name }}</strong>
                                        &middot; Teknisi: {{ $ticket->technician?->name ?? 'Belum diambil' }}
                                    </div>
                                </div>

                                <div class="tk-clean-row-price">
                                    <div class="tk-mono" style="font-size: 0.92rem; font-weight: 700; color: var(--tk-ink);">
                                        Rp {{ number_format($ticket->grand_total, 0, ',', '.') }}
                                    </div>
                                    <div style="font-size: 0.74rem; color: var(--tk-muted);">
                                        {{ $ticket->visit_type === 'home_pickup' ? 'Jemput Lokasi' : 'Antar ke Toko' }}
                                    </div>
                                </div>

                                <div class="tk-clean-row-actions">
                                    <a href="{{ $ticket->customerWhatsappUrl() }}" target="_blank" rel="noopener noreferrer" class="tk-btn tk-btn-sm">
                                        Chat WA
                                    </a>
                                    @if($ticket->status !== 'completed' && $ticket->status !== 'cancelled')
                                        <button type="button" wire:click="advanceTicketStep({{ $ticket->id }})" class="tk-btn tk-btn-sm tk-btn-primary">
                                            Lanjut Tahap &rarr;
                                        </button>
                                    @endif
                                    <a href="{{ url('/admin/service-tickets/' . $ticket->id . '/edit') }}" class="tk-btn tk-btn-sm">
                                        Detail
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- TAB 2: KONTROL CEPAT STOK ETALASE --}}
            <div
                x-show="activeWorkspace === 'stok'"
                x-cloak
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
            >
                @if($products->isEmpty())
                    <div class="tk-empty">
                        Seluruh produk aktif memiliki stok aman di atas 2 unit. Klik "Semua Ready" untuk melihat seluruh barang.
                    </div>
                @else
                    <div class="tk-clean-table">
                        @foreach($products as $product)
                            <div class="tk-clean-row" wire:key="adm-prod-{{ $product->id }}">
                                <div style="display: flex; align-items: center; gap: 0.85rem; flex: 2 1 16rem; min-width: 0;">
                                    @if($product->primaryImageUrl())
                                        <img src="{{ $product->primaryImageUrl() }}" alt="" style="width: 3rem; height: 2.4rem; object-fit: cover; border-radius: 0.45rem; border: 1px solid var(--tk-border); flex-shrink: 0;">
                                    @endif
                                    <div style="min-width: 0;">
                                        <a href="{{ url('/admin/products/' . $product->id . '/edit') }}" style="font-size: 0.9rem; font-weight: 700; color: var(--tk-ink); text-decoration: none; display: block;">
                                            {{ $product->name }}
                                        </a>
                                        <div style="font-size: 0.78rem; color: var(--tk-muted); margin-top: 0.12rem;">
                                            <span class="tk-mono">{{ $product->sku }}</span> &middot; {{ $product->conditionShortLabel() }} &middot; <strong style="color: var(--tk-ink);">{{ $product->formattedPrice() }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="tk-btn-row">
                                    <div class="tk-stepper" aria-label="Ubah stok {{ $product->name }}">
                                        <button type="button" wire:click="adjustProductStock({{ $product->id }}, -1)" class="tk-step-btn" aria-label="Kurangi stok">-</button>
                                        <span class="tk-step-val">{{ $product->stock }}</span>
                                        <button type="button" wire:click="adjustProductStock({{ $product->id }}, 1)" class="tk-step-btn" aria-label="Tambah stok">+</button>
                                    </div>
                                    <button type="button" wire:click="markProductSold({{ $product->id }})" class="tk-btn tk-btn-sm">
                                        Tandai Terjual
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- TAB 3: SIMULASI RAKITAN MASUK --}}
            <div
                x-show="activeWorkspace === 'rakitan'"
                x-cloak
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
            >
                @if($recentBuilds->isEmpty())
                    <div class="tk-empty">
                        Belum ada simulasi rakitan PC yang disimpan oleh pengunjung.
                    </div>
                @else
                    <div class="tk-clean-table">
                        @foreach($recentBuilds as $build)
                            <div class="tk-clean-row" wire:key="adm-build-{{ $build->id }}">
                                <div style="flex: 1 1 16rem; min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span class="tk-mono tk-badge tk-badge-blue">{{ $build->share_code }}</span>
                                        @if($build->customer_name)
                                            <span style="font-size: 0.78rem; color: var(--tk-muted);">Pemesan: {{ $build->customer_name }}</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 0.92rem; font-weight: 700; color: var(--tk-ink); margin-top: 0.2rem;">
                                        {{ $build->build_name }}
                                    </div>
                                    <div style="font-size: 0.78rem; color: var(--tk-muted);">
                                        Total Estimasi: <strong style="color: var(--tk-ink);">{{ $build->formattedGrandTotal() }}</strong> &middot; Beban Daya {{ $build->estimated_wattage }}W (Saran PSU {{ $build->recommended_psu_wattage }}W)
                                    </div>
                                </div>

                                <a href="{{ url('/rakit-pc?kode=' . $build->share_code) }}" target="_blank" rel="noopener noreferrer" class="tk-btn tk-btn-sm tk-btn-soft">
                                    Buka di Rakit PC &rarr;
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-filament-panels::page>
