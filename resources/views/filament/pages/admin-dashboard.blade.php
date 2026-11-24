<x-filament-panels::page>
    <div class="tk-stack">
        <div class="tk-panel">
            <div class="tk-hero-bar">
                <div>
                    <h2 class="tk-title">Pusat Kendali {{ $store->store_name }}</h2>
                    <p class="tk-subtitle">
                        Ringkasan pekerjaan bengkel, stok barang menipis, dan simulasi rakitan masuk yang memerlukan tindak lanjut hari ini.
                    </p>
                </div>

                <div class="tk-btn-row">
                    <a href="{{ url('/admin/products/create') }}" class="tk-btn tk-btn-primary">
                        + Tambah Barang
                    </a>
                    <a href="{{ url('/admin/service-tickets/create') }}" class="tk-btn tk-btn-soft">
                        + Tiket Servis Walk-in
                    </a>
                    <a href="{{ url('/admin/papan-bengkel') }}" class="tk-btn">
                        Buka Papan Bengkel
                    </a>
                </div>
            </div>

            @if($store->is_placeholder_data)
                <div class="tk-panel-subtle" style="margin-top: 1rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
                    <span style="font-size: 0.84rem; color: var(--tk-ink);">
                        <strong>Identitas toko masih menggunakan penanda contoh.</strong> Lengkapi nomor WhatsApp dan alamat workshop agar tautan pesanan langsung menuju toko Anda.
                    </span>
                    <a href="{{ url('/admin/manage-store-settings') }}" class="tk-btn tk-btn-sm tk-btn-soft">
                        Atur Profil Toko
                    </a>
                </div>
            @endif
        </div>

        <section aria-label="Ringkasan Data Operasional Nyata" class="tk-metrics-grid">
            <a href="{{ url('/admin/papan-bengkel') }}" class="tk-metric-card">
                <span class="tk-metric-label">Antrean Bengkel Dikerjakan</span>
                <span class="tk-metric-value">{{ $activeQueueCount }} Unit</span>
                <span class="tk-metric-note">{{ $readyPickupCount }} unit sudah lulus QC &amp; siap diambil</span>
            </a>

            <a href="{{ url('/admin/service-tickets') }}" class="tk-metric-card">
                <span class="tk-metric-label">Menunggu Konfirmasi Biaya</span>
                <span class="tk-metric-value" style="{{ $waitingApprovalCount > 0 ? 'color: var(--tk-err-fg);' : '' }}">{{ $waitingApprovalCount }} Tiket</span>
                <span class="tk-metric-note">Perlu persetujuan estimasi pelanggan via WhatsApp</span>
            </a>

            <a href="{{ url('/admin/products') }}" class="tk-metric-card">
                <span class="tk-metric-label">Stok Etalase Siap Jual</span>
                <span class="tk-metric-value">{{ $availableProductsCount }} Judul</span>
                <span class="tk-metric-note">Nilai stok tersedia: Rp {{ number_format($inventoryValuation, 0, ',', '.') }}</span>
            </a>

            <a href="{{ url('/admin/pc-catalog-parts') }}" class="tk-metric-card">
                <span class="tk-metric-label">Database Part API Rakit PC</span>
                <span class="tk-metric-value">{{ number_format($catalogPartsCount, 0, ',', '.') }} Part</span>
                <span class="tk-metric-note">
                    Kurs acuan Rp {{ number_format($store->usd_to_idr_rate ?: 16500, 0, ',', '.') }}/USD
                    @if($store->last_parts_sync_at)
                        &middot; Sinkron {{ $store->last_parts_sync_at->diffForHumans() }}
                    @endif
                </span>
            </a>
        </section>

        <div class="tk-admin-grid">
            <section aria-label="Daftar Antrean Servis Aktif" class="tk-panel" style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
                    <div>
                        <h3 class="tk-section-title">Antrean Servis &amp; Follow-Up Cepat</h3>
                        <p class="tk-subtitle">Klik Lanjut Tahap untuk memajukan progres atau hubungi pelanggan lewat WhatsApp.</p>
                    </div>
                    <div class="tk-btn-row">
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
                </div>

                @if($tickets->isEmpty())
                    <div class="tk-empty">
                        Tidak ada tiket servis pada saringan ini. Klik tombol "+ Tiket Servis Walk-in" di atas untuk menerima perangkat baru.
                    </div>
                @else
                    <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                        @foreach($tickets as $ticket)
                            <div class="tk-row-item">
                                <div style="flex: 1 1 16rem; min-width: 0;">
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
                                    <div style="font-size: 0.9rem; font-weight: 700; color: var(--tk-ink); margin-top: 0.2rem;">
                                        {{ $ticket->device_brand_model }}
                                    </div>
                                    <div style="font-size: 0.78rem; color: var(--tk-muted); margin-top: 0.1rem;">
                                        {{ $ticket->customer_name }} &middot; Teknisi: {{ $ticket->technician?->name ?? 'Belum ditugaskan' }} &middot; Total: <strong style="color: var(--tk-ink);">Rp {{ number_format($ticket->grand_total, 0, ',', '.') }}</strong>
                                    </div>
                                </div>

                                <div class="tk-btn-row">
                                    <a href="{{ $ticket->customerWhatsappUrl() }}" target="_blank" rel="noopener noreferrer" class="tk-btn tk-btn-sm">
                                        WA
                                    </a>
                                    @if($ticket->status !== 'completed' && $ticket->status !== 'cancelled')
                                        <button type="button" wire:click="advanceTicketStep({{ $ticket->id }})" class="tk-btn tk-btn-sm tk-btn-primary">
                                            Lanjut Tahap
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
            </section>

            <div class="tk-stack">
                <section aria-label="Kontrol Cepat Stok Etalase" class="tk-panel" style="display: flex; flex-direction: column; gap: 0.9rem;">
                    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem;">
                        <div>
                            <h3 class="tk-section-title">Kontrol Cepat Stok Etalase</h3>
                            <p class="tk-subtitle">Ubah jumlah stok fisik langsung tanpa membuka halaman edit.</p>
                        </div>
                        <div class="tk-btn-row">
                            <button type="button" wire:click="setStockFilter('low')" class="tk-btn tk-btn-sm {{ $stockFilter === 'low' ? 'tk-btn-soft' : '' }}">
                                Menipis (&le;2)
                            </button>
                            <button type="button" wire:click="setStockFilter('all')" class="tk-btn tk-btn-sm {{ $stockFilter === 'all' ? 'tk-btn-soft' : '' }}">
                                Semua Ready
                            </button>
                        </div>
                    </div>

                    @if($products->isEmpty())
                        <div class="tk-empty">
                            Seluruh barang tersedia memiliki stok di atas 2 unit.
                        </div>
                    @else
                        <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                            @foreach($products as $product)
                                <div class="tk-row-item">
                                    <div style="flex: 1 1 11rem; min-width: 0;">
                                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--tk-ink); line-height: 1.3;">
                                            <a href="{{ url('/admin/products/' . $product->id . '/edit') }}" style="color: inherit; text-decoration: none;">
                                                {{ $product->name }}
                                            </a>
                                        </div>
                                        <div style="font-size: 0.76rem; color: var(--tk-muted); margin-top: 0.15rem;">
                                            {{ $product->conditionShortLabel() }} &middot; {{ $product->formattedPrice() }}
                                        </div>
                                    </div>

                                    <div class="tk-btn-row">
                                        <div class="tk-stepper" aria-label="Ubah stok {{ $product->name }}">
                                            <button type="button" wire:click="adjustProductStock({{ $product->id }}, -1)" class="tk-step-btn" aria-label="Kurangi stok">-</button>
                                            <span class="tk-step-val">{{ $product->stock }}</span>
                                            <button type="button" wire:click="adjustProductStock({{ $product->id }}, 1)" class="tk-step-btn" aria-label="Tambah stok">+</button>
                                        </div>
                                        <button type="button" wire:click="markProductSold({{ $product->id }})" class="tk-btn tk-btn-sm">
                                            Terjual
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section aria-label="Simulasi Rakit PC Terbaru" class="tk-panel" style="display: flex; flex-direction: column; gap: 0.85rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                        <h3 class="tk-section-title">Simulasi Rakit PC Terbaru</h3>
                        <a href="{{ url('/admin/pc-builds') }}" class="tk-btn tk-btn-sm">Lihat Semua</a>
                    </div>

                    @if($recentBuilds->isEmpty())
                        <div class="tk-empty">
                            Belum ada simulasi rakitan yang disimpan oleh pengunjung.
                        </div>
                    @else
                        <div style="display: flex; flex-direction: column; gap: 0.55rem;">
                            @foreach($recentBuilds as $build)
                                <div class="tk-row-item">
                                    <div style="flex: 1 1 10rem; min-width: 0;">
                                        <span class="tk-mono" style="font-size: 0.76rem; font-weight: 700; color: var(--tk-accent);">{{ $build->share_code }}</span>
                                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--tk-ink);">{{ $build->build_name }}</div>
                                        <div style="font-size: 0.76rem; color: var(--tk-muted);">
                                            {{ $build->formattedGrandTotal() }} &middot; Daya {{ $build->estimated_wattage }}W
                                        </div>
                                    </div>
                                    <a href="{{ url('/rakit-pc?kode=' . $build->share_code) }}" target="_blank" rel="noopener noreferrer" class="tk-btn tk-btn-sm">
                                        Buka
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-filament-panels::page>
