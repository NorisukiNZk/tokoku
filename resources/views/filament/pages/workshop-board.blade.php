@php
    $laneAccentColors = [
        'intake' => '#38BDF8',
        'approval' => '#F59E0B',
        'working' => '#FB923C',
        'ready' => '#10B981',
    ];
@endphp
<x-filament-panels::page>
    <div
        x-data
        @keydown.escape.window="$wire.closeQuickWorkbench()"
        class="tk-stack"
    >
        {{-- UNIFIED COMPACT DARK COMMAND BAR (No double header, spacious controls) --}}
        <header class="tk-cmd-banner tk-anim-enter" style="display: flex; flex-direction: column; gap: 1rem;">
            <div class="tk-hero-bar">
                <div>
                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                        <span class="tk-auth-badge">Ruang Kerja Teknisi</span>
                        <span style="font-size: 0.76rem; font-weight: 600; color: #A7F3D0;">
                            Selesai Hari Ini: {{ $completedToday }} Unit
                        </span>
                    </div>
                    <h1 class="tk-cmd-title">Papan Antrean Bengkel</h1>
                    <p class="tk-cmd-sub">
                        Klaim unit masuk, catat suhu awal &amp; akhir QC pada panel geser, lalu pindahkan tahap pengerjaan hingga siap diambil pelanggan.
                    </p>
                </div>

                <div class="tk-btn-row">
                    <a href="{{ url('/' . $panelPath . '/service-tickets/create') }}" class="tk-cmd-btn-accent">
                        <svg class="tk-ico-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Terima Unit Baru</span>
                    </a>
                    <a href="{{ url('/' . $panelPath . '/service-tickets') }}" class="tk-cmd-btn-outline">
                        <span>Tabel &amp; Arsip</span>
                    </a>
                </div>
            </div>

            <div style="padding-top: 0.9rem; border-top: 1px solid #334155; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
                <div style="position: relative; flex: 1 1 16rem; max-width: 24rem;">
                    <label for="tk-board-search" class="sr-only">Cari tiket servis</label>
                    <svg style="position: absolute; inset-inline-start: 0.8rem; top: 0.82rem; width: 1rem; height: 1rem; color: #CBD5E1; pointer-events: none;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                    </svg>
                    <input
                        id="tk-board-search"
                        type="search"
                        wire:model.live.debounce.250ms="search"
                        placeholder="Cari SRV-..., pelanggan, atau tipe perangkat..."
                        class="tk-cmd-search"
                    >
                </div>

                <div class="tk-btn-row" role="group" aria-label="Saringan Tugas Bengkel">
                    @foreach([
                        'all' => 'Semua (' . $totalActive . ')',
                        'mine' => 'Tugas Saya',
                        'unassigned' => 'Belum Diambil',
                        'pickup' => 'Jemput Lokasi',
                    ] as $scopeKey => $scopeLabel)
                        <button
                            type="button"
                            wire:click="setScope('{{ $scopeKey }}')"
                            class="{{ $scope === $scopeKey ? 'tk-cmd-btn-accent' : 'tk-cmd-btn-outline' }}"
                            style="min-height: 2.4rem; padding: 0 0.8rem; font-size: 0.78rem;"
                        >
                            {{ $scopeLabel }}
                        </button>
                    @endforeach
                </div>
            </div>
        </header>

        <div wire:loading.delay class="tk-panel-subtle" role="status" aria-live="polite" style="font-size: 0.83rem; font-weight: 600;">
            Menyegarkan papan antrean...
        </div>

        {{-- 4 FULL-WIDTH COLOR-CODED KANBAN LANES --}}
        <div class="tk-board-grid">
            @foreach($lanes as $laneKey => $lane)
                @php
                    $laneColor = $laneAccentColors[$laneKey] ?? '#FB923C';
                    $delayClass = 'tk-delay-' . min(4, $loop->iteration);
                @endphp
                <section
                    aria-label="{{ $lane['title'] }}"
                    class="tk-lane tk-anim-enter {{ $delayClass }}"
                    style="border-top: 3px solid {{ $laneColor }};"
                >
                    <div class="tk-lane-head">
                        <div style="display: inline-flex; align-items: center; gap: 0.45rem;">
                            <span style="width: 0.5rem; height: 0.5rem; border-radius: 999px; background-color: {{ $laneColor }};" aria-hidden="true"></span>
                            <h2 class="tk-lane-title" style="margin: 0;">{{ $lane['title'] }}</h2>
                        </div>
                        <span class="tk-count-pill">{{ $lane['items']->count() }}</span>
                    </div>

                    @if($lane['items']->isEmpty())
                        <div class="tk-empty">
                            {{ $lane['empty'] }}
                        </div>
                    @else
                        @foreach($lane['items'] as $ticket)
                            <article class="tk-lane-card" wire:key="wb-card-{{ $ticket->id }}">
                                {{-- Card Top: Ticket Code + Visit Mode --}}
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                                    <span class="tk-mono" style="font-size: 0.78rem; font-weight: 700; color: var(--tk-accent);">
                                        {{ $ticket->ticket_number }}
                                    </span>
                                    <span class="tk-badge {{ $ticket->visit_type === 'home_pickup' ? 'tk-badge-warn' : 'tk-badge-muted' }}">
                                        {{ $ticket->visit_type === 'home_pickup' ? 'Jemput' : 'Workshop' }}
                                    </span>
                                </div>

                                {{-- Card Middle: Device + Customer + 2-Line Complaint --}}
                                <div>
                                    <h3 style="font-size: 0.94rem; font-weight: 700; color: var(--tk-ink); margin: 0; line-height: 1.3;">
                                        {{ $ticket->device_brand_model }}
                                    </h3>
                                    <p style="font-size: 0.78rem; font-weight: 600; color: var(--tk-ink); margin: 0.2rem 0 0 0;">
                                        {{ $ticket->customer_name }}
                                    </p>
                                    <p class="tk-clamp-2" style="font-size: 0.78rem; color: var(--tk-muted); margin: 0.15rem 0 0 0; line-height: 1.42;">
                                        {{ $ticket->complaint_summary }}
                                    </p>
                                </div>

                                {{-- Compact Telemetry Row (Thermal & Cost) --}}
                                <div style="display: flex; flex-wrap: wrap; gap: 0.35rem; align-items: center;">
                                    @if($ticket->temp_before_c || $ticket->temp_after_c)
                                        <span class="tk-badge tk-badge-ok tk-mono">
                                            {{ $ticket->temp_before_c ? $ticket->temp_before_c . '°C' : '-' }}
                                            &rarr;
                                            {{ $ticket->temp_after_c ? $ticket->temp_after_c . '°C' : '?' }}
                                        </span>
                                    @endif

                                    @if($ticket->grand_total > 0)
                                        <span class="tk-badge tk-badge-muted tk-mono">
                                            Rp {{ number_format($ticket->grand_total, 0, ',', '.') }}
                                        </span>
                                    @endif

                                    @if($ticket->parts->count() > 0)
                                        <span class="tk-badge tk-badge-blue">
                                            {{ $ticket->parts->count() }} part
                                        </span>
                                    @endif
                                </div>

                                <div style="font-size: 0.74rem; color: var(--tk-muted); display: flex; align-items: center; justify-content: space-between; gap: 0.4rem; padding-top: 0.4rem; border-top: 1px solid var(--tk-border);">
                                    <span>PIC: <strong style="color: var(--tk-ink);">{{ $ticket->technician?->name ?? 'Belum diambil' }}</strong></span>
                                    <a
                                        href="{{ $ticket->customerWhatsappUrl() }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        style="font-weight: 700; color: var(--tk-accent-soft-fg); text-decoration: none;"
                                    >
                                        Chat WA &nearr;
                                    </a>
                                </div>

                                {{-- Clean 2-Button Action Row (No wrapping mess!) --}}
                                <div style="display: flex; gap: 0.45rem; padding-top: 0.1rem;">
                                    @if($laneKey === 'intake')
                                        @if(!$ticket->technician_id || $ticket->status === 'booked')
                                            <button type="button" wire:click="claimTicket({{ $ticket->id }})" class="tk-btn tk-btn-sm tk-btn-primary" style="flex: 1;">
                                                Ambil Tugas
                                            </button>
                                        @else
                                            <button type="button" wire:click="advanceStatus({{ $ticket->id }}, 'in_progress')" class="tk-btn tk-btn-sm tk-btn-primary" style="flex: 1;">
                                                Mulai Kerjakan
                                            </button>
                                        @endif
                                    @elseif($laneKey === 'approval')
                                        <button type="button" wire:click="advanceStatus({{ $ticket->id }}, 'in_progress', 'Pelanggan menyetujui biaya; teknisi memulai pengerjaan.')" class="tk-btn tk-btn-sm tk-btn-primary" style="flex: 1;">
                                            Disetujui &rarr; Kerjakan
                                        </button>
                                    @elseif($laneKey === 'working')
                                        @if($ticket->status === 'in_progress')
                                            <button type="button" wire:click="advanceStatus({{ $ticket->id }}, 'testing_qc')" class="tk-btn tk-btn-sm tk-btn-soft" style="flex: 1;">
                                                Masuk Uji QC
                                            </button>
                                        @else
                                            <button type="button" wire:click="advanceStatus({{ $ticket->id }}, 'ready')" class="tk-btn tk-btn-sm tk-btn-primary" style="flex: 1;">
                                                Lulus QC &rarr; Siap
                                            </button>
                                        @endif
                                    @elseif($laneKey === 'ready')
                                        <button type="button" wire:click="advanceStatus({{ $ticket->id }}, 'completed')" class="tk-btn tk-btn-sm tk-btn-ok" style="flex: 1;">
                                            Serah Terima Selesai
                                        </button>
                                    @endif

                                    <button
                                        type="button"
                                        wire:click="openQuickWorkbench({{ $ticket->id }})"
                                        class="tk-btn tk-btn-sm"
                                        aria-label="Catat diagnosa dan suhu {{ $ticket->ticket_number }}"
                                    >
                                        Diagnosa
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    @endif
                </section>
            @endforeach
        </div>

        {{-- ANIMATED SLIDE-OVER DRAWER --}}
        @if($editingTicket)
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="tk-drawer-title"
                class="tk-modal-backdrop"
                wire:click.self="closeQuickWorkbench"
            >
                <div class="tk-drawer">
                    <div class="tk-drawer-head">
                        <div>
                            <span class="tk-mono" style="font-size: 0.78rem; font-weight: 700; color: var(--tk-accent);">
                                {{ $editingTicket->ticket_number }}
                            </span>
                            <h2 id="tk-drawer-title" style="font-size: 1.08rem; font-weight: 800; color: var(--tk-ink); margin: 0.1rem 0 0 0;">
                                {{ $editingTicket->device_brand_model }}
                            </h2>
                            <p style="font-size: 0.78rem; color: var(--tk-muted); margin: 0.1rem 0 0 0;">
                                {{ $editingTicket->customer_name }} &middot; {{ $editingTicket->customer_phone }}
                            </p>
                        </div>
                        <button type="button" wire:click="closeQuickWorkbench" class="tk-btn tk-btn-sm">
                            Tutup (Esc)
                        </button>
                    </div>

                    <form wire:submit="saveQuickWorkbench" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                        <div class="tk-drawer-body">
                            <div class="tk-panel-subtle">
                                <div style="font-size: 0.74rem; font-weight: 700; color: var(--tk-muted); text-transform: uppercase; letter-spacing: 0.03em;">Keluhan Awal</div>
                                <div style="font-size: 0.85rem; color: var(--tk-ink); margin-top: 0.25rem; line-height: 1.45;">
                                    {{ $editingTicket->complaint_summary }}
                                </div>
                            </div>

                            <div>
                                <label for="wb-status" class="tk-label">Tahap Pengerjaan Saat Ini</label>
                                <select id="wb-status" wire:model="editStatus" class="tk-input">
                                    @foreach(\App\Models\ServiceTicket::STATUSES as $stKey => $stLabel)
                                        <option value="{{ $stKey }}">{{ $stLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="tk-field-grid tk-field-grid-2">
                                <div>
                                    <label for="wb-temp-before" class="tk-label">Suhu Awal (°C)</label>
                                    <input id="wb-temp-before" type="number" wire:model="editTempBefore" placeholder="Contoh: 91" class="tk-input">
                                </div>
                                <div>
                                    <label for="wb-temp-after" class="tk-label">Suhu Setelah QC (°C)</label>
                                    <input id="wb-temp-after" type="number" wire:model="editTempAfter" placeholder="Contoh: 65" class="tk-input">
                                </div>
                            </div>

                            <div>
                                <label for="wb-fee" class="tk-label">Biaya Jasa Servis (Rp)</label>
                                <input id="wb-fee" type="number" wire:model="editServiceFee" class="tk-input">
                                @if($editingTicket->parts_total > 0)
                                    <p style="font-size: 0.76rem; color: var(--tk-muted); margin-top: 0.3rem;">
                                        Sparepart terpasang: <strong>Rp {{ number_format($editingTicket->parts_total, 0, ',', '.') }}</strong> ({{ $editingTicket->parts->count() }} item)
                                    </p>
                                @endif
                            </div>

                            <div>
                                <label for="wb-diag" class="tk-label">Catatan Diagnosa &amp; Tindakan Teknisi</label>
                                <textarea id="wb-diag" rows="3" wire:model="editDiagnosisNotes" placeholder="Catat penyebab masalah, pembersihan, atau penggantian part..." class="tk-textarea"></textarea>
                            </div>

                            <div>
                                <label for="wb-log" class="tk-label">Pesan Progres Publik (Dilihat Pelanggan di Cek Resi)</label>
                                <textarea id="wb-log" rows="2" wire:model="editPublicLogNote" placeholder="Opsional: tulis kabar progres singkat..." class="tk-textarea"></textarea>
                            </div>
                        </div>

                        <div class="tk-drawer-foot">
                            <a href="{{ url('/' . $panelPath . '/service-tickets/' . $editingTicket->id . '/edit') }}" class="tk-btn tk-btn-sm">
                                Kelola Sparepart &amp; Form Penuh
                            </a>
                            <button type="submit" wire:loading.attr="disabled" wire:target="saveQuickWorkbench" class="tk-btn tk-btn-primary">
                                <span wire:loading.remove wire:target="saveQuickWorkbench">Simpan Pembaruan</span>
                                <span wire:loading wire:target="saveQuickWorkbench">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
