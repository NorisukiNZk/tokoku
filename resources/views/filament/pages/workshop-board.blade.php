<x-filament-panels::page>
    <div
        x-data
        @keydown.escape.window="$wire.closeQuickWorkbench()"
        class="tk-stack"
    >
        <div class="tk-panel">
            <div class="tk-hero-bar">
                <div>
                    <h2 class="tk-title">Papan Antrean Bengkel</h2>
                    <p class="tk-subtitle">
                        Kelola unit servis aktif secara langsung: ambil tugas, catat suhu uji, pindahkan tahap pengerjaan, dan kirim pembaruan ke WhatsApp pelanggan.
                    </p>
                </div>

                <div class="tk-btn-row">
                    <a href="{{ url('/' . $panelPath . '/service-tickets/create') }}" class="tk-btn tk-btn-primary">
                        + Terima Unit Baru
                    </a>
                    <a href="{{ url('/' . $panelPath . '/service-tickets') }}" class="tk-btn">
                        Tampilan Tabel &amp; Arsip
                    </a>
                </div>
            </div>

            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--tk-border); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
                <div style="flex: 1 1 16rem; max-width: 24rem;">
                    <label for="tk-board-search" class="sr-only">Cari tiket servis</label>
                    <input
                        id="tk-board-search"
                        type="search"
                        wire:model.live.debounce.250ms="search"
                        placeholder="Cari SRV-..., nama pelanggan, atau perangkat..."
                        class="tk-input"
                    >
                </div>

                <div class="tk-btn-row">
                    @foreach([
                        'all' => 'Semua Aktif (' . $totalActive . ')',
                        'mine' => 'Tugas Saya',
                        'unassigned' => 'Belum Diambil',
                        'pickup' => 'Jemput Unit',
                    ] as $scopeKey => $scopeLabel)
                        <button
                            type="button"
                            wire:click="setScope('{{ $scopeKey }}')"
                            class="tk-btn tk-btn-sm {{ $scope === $scopeKey ? 'tk-btn-soft' : '' }}"
                        >
                            {{ $scopeLabel }}
                        </button>
                    @endforeach
                    <span class="tk-badge tk-badge-ok">Selesai Hari Ini: {{ $completedToday }}</span>
                </div>
            </div>
        </div>

        <div wire:loading.delay class="tk-panel-subtle" role="status" aria-live="polite" style="font-size: 0.85rem; font-weight: 600;">
            Memperbarui papan antrean bengkel...
        </div>

        <div class="tk-board-grid">
            @foreach($lanes as $laneKey => $lane)
                <section aria-label="{{ $lane['title'] }}" class="tk-lane">
                    <div class="tk-lane-head">
                        <h3 class="tk-lane-title">{{ $lane['title'] }}</h3>
                        <span class="tk-count-pill">{{ $lane['items']->count() }}</span>
                    </div>

                    @if($lane['items']->isEmpty())
                        <div class="tk-empty">
                            {{ $lane['empty'] }}
                        </div>
                    @else
                        @foreach($lane['items'] as $ticket)
                            <article class="tk-job-card">
                                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem;">
                                    <div>
                                        <span class="tk-mono" style="font-size: 0.8rem; font-weight: 700; color: var(--tk-accent);">
                                            {{ $ticket->ticket_number }}
                                        </span>
                                        <h4 style="font-size: 0.92rem; font-weight: 700; color: var(--tk-ink); margin: 0.15rem 0 0 0; line-height: 1.3;">
                                            {{ $ticket->device_brand_model }}
                                        </h4>
                                    </div>
                                    <span class="tk-badge {{ $ticket->visit_type === 'home_pickup' ? 'tk-badge-warn' : 'tk-badge-muted' }}">
                                        {{ $ticket->visit_type === 'home_pickup' ? 'Jemput' : 'Toko' }}
                                    </span>
                                </div>

                                <p style="font-size: 0.8rem; color: var(--tk-muted); margin: 0; line-height: 1.45;">
                                    <strong style="color: var(--tk-ink);">{{ $ticket->customer_name }}</strong>
                                    &middot; {{ \Illuminate\Support\Str::limit($ticket->complaint_summary, 95) }}
                                </p>

                                <div style="display: flex; flex-wrap: wrap; gap: 0.35rem; align-items: center;">
                                    <span class="tk-badge {{ match($ticket->status) {
                                        'waiting_approval' => 'tk-badge-err',
                                        'ready' => 'tk-badge-ok',
                                        'in_progress', 'testing_qc' => 'tk-badge-warn',
                                        default => 'tk-badge-blue',
                                    } }}">
                                        {{ $ticket->statusLabel() }}
                                    </span>

                                    @if($ticket->temp_before_c || $ticket->temp_after_c)
                                        <span class="tk-badge tk-badge-muted tk-mono">
                                            {{ $ticket->temp_before_c ? $ticket->temp_before_c . '°C' : '-' }}
                                            &rarr;
                                            {{ $ticket->temp_after_c ? $ticket->temp_after_c . '°C' : '-' }}
                                        </span>
                                    @endif

                                    @if($ticket->grand_total > 0)
                                        <span class="tk-badge tk-badge-muted tk-mono">
                                            Rp {{ number_format($ticket->grand_total, 0, ',', '.') }}
                                        </span>
                                    @endif
                                </div>

                                <div style="font-size: 0.75rem; color: var(--tk-muted); display: flex; justify-content: space-between; align-items: center; padding-top: 0.35rem; border-top: 1px solid var(--tk-border);">
                                    <span>Teknisi: <strong style="color: var(--tk-ink);">{{ $ticket->technician?->name ?? 'Belum diambil' }}</strong></span>
                                    @if($ticket->parts->count() > 0)
                                        <span>{{ $ticket->parts->count() }} part</span>
                                    @endif
                                </div>

                                <div class="tk-btn-row" style="padding-top: 0.15rem;">
                                    @if($laneKey === 'intake')
                                        @if(!$ticket->technician_id || $ticket->status === 'booked')
                                            <button type="button" wire:click="claimTicket({{ $ticket->id }})" class="tk-btn tk-btn-sm tk-btn-primary" style="flex: 1;">
                                                Ambil &amp; Diagnosa
                                            </button>
                                        @else
                                            <button type="button" wire:click="advanceStatus({{ $ticket->id }}, 'in_progress')" class="tk-btn tk-btn-sm tk-btn-primary" style="flex: 1;">
                                                Mulai Kerjakan
                                            </button>
                                        @endif
                                    @elseif($laneKey === 'approval')
                                        <a href="{{ $ticket->customerWhatsappUrl() }}" target="_blank" rel="noopener noreferrer" class="tk-btn tk-btn-sm tk-btn-soft" style="flex: 1;">
                                            Kirim Estimasi WA
                                        </a>
                                        <button type="button" wire:click="advanceStatus({{ $ticket->id }}, 'in_progress', 'Pelanggan menyetujui biaya; teknisi memulai pengerjaan.')" class="tk-btn tk-btn-sm tk-btn-primary">
                                            Setuju &rarr; Kerjakan
                                        </button>
                                    @elseif($laneKey === 'working')
                                        @if($ticket->status === 'in_progress')
                                            <button type="button" wire:click="advanceStatus({{ $ticket->id }}, 'testing_qc')" class="tk-btn tk-btn-sm tk-btn-soft" style="flex: 1;">
                                                Lanjut Uji QC
                                            </button>
                                        @endif
                                        <button type="button" wire:click="advanceStatus({{ $ticket->id }}, 'ready')" class="tk-btn tk-btn-sm tk-btn-primary" style="flex: 1;">
                                            Siap Diambil
                                        </button>
                                    @elseif($laneKey === 'ready')
                                        <a href="{{ $ticket->customerWhatsappUrl() }}" target="_blank" rel="noopener noreferrer" class="tk-btn tk-btn-sm tk-btn-soft" style="flex: 1;">
                                            Kabari WA
                                        </a>
                                        <button type="button" wire:click="advanceStatus({{ $ticket->id }}, 'completed')" class="tk-btn tk-btn-sm tk-btn-ok">
                                            Serah Terima
                                        </button>
                                    @endif

                                    <button type="button" wire:click="openQuickWorkbench({{ $ticket->id }})" class="tk-btn tk-btn-sm">
                                        Diagnosa &amp; Suhu
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    @endif
                </section>
            @endforeach
        </div>

        @if($editingTicket)
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="tk-drawer-title"
                class="tk-modal-backdrop"
            >
                <div class="tk-drawer">
                    <div class="tk-drawer-head">
                        <div>
                            <span class="tk-mono" style="font-size: 0.78rem; font-weight: 700; color: var(--tk-accent);">
                                {{ $editingTicket->ticket_number }}
                            </span>
                            <h3 id="tk-drawer-title" style="font-size: 1.05rem; font-weight: 700; color: var(--tk-ink); margin: 0.1rem 0 0 0;">
                                {{ $editingTicket->device_brand_model }}
                            </h3>
                            <p style="font-size: 0.78rem; color: var(--tk-muted); margin: 0.1rem 0 0 0;">
                                {{ $editingTicket->customer_name }} ({{ $editingTicket->customer_phone }})
                            </p>
                        </div>
                        <button type="button" wire:click="closeQuickWorkbench" class="tk-btn tk-btn-sm">
                            Tutup (Esc)
                        </button>
                    </div>

                    <form wire:submit="saveQuickWorkbench" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                        <div class="tk-drawer-body">
                            <div class="tk-panel-subtle">
                                <div style="font-size: 0.74rem; font-weight: 600; color: var(--tk-muted);">Keluhan Pelanggan</div>
                                <div style="font-size: 0.85rem; color: var(--tk-ink); margin-top: 0.25rem;">
                                    {{ $editingTicket->complaint_summary }}
                                </div>
                            </div>

                            <div>
                                <label for="wb-status" class="tk-label">Tahap Pengerjaan</label>
                                <select id="wb-status" wire:model="editStatus" class="tk-input">
                                    @foreach(\App\Models\ServiceTicket::STATUSES as $stKey => $stLabel)
                                        <option value="{{ $stKey }}">{{ $stLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="tk-field-grid tk-field-grid-2">
                                <div>
                                    <label for="wb-temp-before" class="tk-label">Suhu Awal Sebelum Servis (°C)</label>
                                    <input id="wb-temp-before" type="number" wire:model="editTempBefore" placeholder="Misal: 90" class="tk-input">
                                </div>
                                <div>
                                    <label for="wb-temp-after" class="tk-label">Suhu Setelah QC (°C)</label>
                                    <input id="wb-temp-after" type="number" wire:model="editTempAfter" placeholder="Misal: 65" class="tk-input">
                                </div>
                            </div>

                            <div>
                                <label for="wb-fee" class="tk-label">Biaya Jasa Servis (Rp)</label>
                                <input id="wb-fee" type="number" wire:model="editServiceFee" class="tk-input">
                                @if($editingTicket->parts_total > 0)
                                    <p style="font-size: 0.76rem; color: var(--tk-muted); margin-top: 0.3rem;">
                                        Sparepart terpasang: Rp {{ number_format($editingTicket->parts_total, 0, ',', '.') }} ({{ $editingTicket->parts->count() }} item)
                                    </p>
                                @endif
                            </div>

                            <div>
                                <label for="wb-diag" class="tk-label">Hasil Diagnosa &amp; Tindakan Teknisi</label>
                                <textarea id="wb-diag" rows="3" wire:model="editDiagnosisNotes" placeholder="Catat penyebab kerusakan, bagian yang dibersihkan, atau temuan hardware..." class="tk-textarea"></textarea>
                            </div>

                            <div>
                                <label for="wb-log" class="tk-label">Tambahkan Catatan Progres Publik (Tampil di Cek Resi Pelanggan)</label>
                                <textarea id="wb-log" rows="2" wire:model="editPublicLogNote" placeholder="Opsional: tulis pesan singkat progres hari ini untuk dilihat pelanggan..." class="tk-textarea"></textarea>
                            </div>
                        </div>

                        <div class="tk-drawer-foot">
                            <a href="{{ url('/' . $panelPath . '/service-tickets/' . $editingTicket->id . '/edit') }}" class="tk-btn tk-btn-sm">
                                Tambah Sparepart / Form Penuh
                            </a>
                            <button type="submit" class="tk-btn tk-btn-primary">
                                Simpan Pembaruan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
