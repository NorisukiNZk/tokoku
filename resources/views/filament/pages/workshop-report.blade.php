<x-filament-panels::page>
    @php
        $formatCompactRp = static function (int $amount): string {
            if ($amount >= 1000000) {
                return 'Rp ' . number_format($amount / 1000000, 1, ',', '.') . 'jt';
            }
            if ($amount >= 1000) {
                return 'Rp ' . number_format($amount / 1000, 0, ',', '.') . 'rb';
            }

            return 'Rp ' . number_format($amount, 0, ',', '.');
        };
    @endphp

    <div class="tk-stack">
        {{-- 1. UNIFIED DARK COMMAND BANNER --}}
        <header class="tk-cmd-banner tk-anim-enter">
            <div class="tk-hero-bar">
                <div style="max-width: 39rem;">
                    <div style="display: inline-flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; margin-bottom: 0.45rem;">
                        <span class="tk-auth-badge">Rekap Bengkel &amp; Stok</span>
                        <span style="font-size: 0.76rem; color: #CBD5E1;">Rentang: {{ $periodStartLabel }}</span>
                    </div>
                    <h1 class="tk-cmd-title">Laporan Omzet &amp; Kinerja Teknisi</h1>
                    <p class="tk-cmd-sub">
                        Pantau realisasi omzet jasa servis, penjualan sparepart, efektivitas pendinginan unit, serta unduh laporan CSV siap buka di Excel.
                    </p>
                </div>

                <div class="tk-cmd-actions-grid">
                    <button type="button" wire:click="exportServiceCsv" class="tk-cmd-btn-accent tk-cmd-span-mobile">
                        <svg class="tk-ico-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span>Ekspor CSV Servis</span>
                    </button>
                    <button type="button" wire:click="exportProductsCsv" class="tk-cmd-btn-outline">
                        <span>CSV Stok Barang</span>
                    </button>
                    <a href="{{ url('/admin/activity-logs') }}" class="tk-cmd-btn-outline">
                        <span>Log Audit</span>
                    </a>
                </div>
            </div>

            {{-- Period Filter Strip --}}
            <div style="margin-top: 1rem; padding-top: 0.85rem; border-top: 1px solid #334155; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.65rem;">
                <span style="font-size: 0.78rem; font-weight: 600; color: #CBD5E1;">Periode Analisis:</span>
                <div class="tk-scroll-pills" role="group" aria-label="Pilih Periode Laporan">
                    @foreach(\App\Filament\Pages\WorkshopReportPage::PERIOD_OPTIONS as $key => $label)
                        <button
                            type="button"
                            wire:click="setPeriod('{{ $key }}')"
                            style="min-height: 2.35rem; padding: 0 0.9rem; border-radius: 0.55rem; font-size: 0.8rem; font-weight: 700; cursor: pointer; flex-shrink: 0; border: 1px solid {{ $period === $key ? '#FB923C' : '#8FA0B8' }}; background-color: {{ $period === $key ? '#FB923C' : '#0B1220' }}; color: {{ $period === $key ? '#0F172A' : '#E2E8F0' }};"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </header>

        {{-- 2. KPI SUMMARY GRID (2x2 on phones, 4-col on desktop) --}}
        <section aria-label="Ringkasan Omzet dan Kualitas Bengkel" class="tk-metrics-grid">
            <div class="tk-kpi-card tk-anim-enter tk-delay-1">
                <span class="tk-kpi-bar" style="background-color: #FB923C;" aria-hidden="true"></span>
                <span class="tk-metric-label">Omzet Servis Selesai / Siap Ambil</span>
                <span class="tk-kpi-value">Rp {{ number_format($revenueTotal, 0, ',', '.') }}</span>
                <span class="tk-metric-note">
                    Jasa Rp {{ number_format($serviceFeeTotal, 0, ',', '.') }} &middot; Part Rp {{ number_format($partsRevenueTotal, 0, ',', '.') }}
                </span>
            </div>

            <div class="tk-kpi-card tk-anim-enter tk-delay-2">
                <span class="tk-kpi-bar" style="background-color: #10B981;" aria-hidden="true"></span>
                <span class="tk-metric-label">Tiket Selesai / Siap Diambil</span>
                <span class="tk-kpi-value">{{ $completedCount }} Unit</span>
                <span class="tk-metric-note">
                    Dari {{ $totalPeriodTickets }} tiket periode {{ strtolower($periodLabel) }} ({{ $customerApprovedCount }} disetujui 1-klik)
                </span>
            </div>

            <div class="tk-kpi-card tk-anim-enter tk-delay-3">
                <span class="tk-kpi-bar" style="background-color: #38BDF8;" aria-hidden="true"></span>
                <span class="tk-metric-label">Rata-rata Penurunan Suhu</span>
                <span class="tk-kpi-value">
                    @if($avgTempDrop !== null)
                        -{{ number_format($avgTempDrop, 1, ',', '.') }}°C
                    @else
                        -
                    @endif
                </span>
                <span class="tk-metric-note">
                    @if($tempSampleCount > 0)
                        Dihitung dari {{ $tempSampleCount }} tiket dengan log suhu QC
                    @else
                        Belum ada tiket dengan catatan suhu awal &amp; akhir
                    @endif
                </span>
            </div>

            <div class="tk-kpi-card tk-anim-enter tk-delay-4">
                <span class="tk-kpi-bar" style="background-color: #F59E0B;" aria-hidden="true"></span>
                <span class="tk-metric-label">Valuasi Stok Etalase Aktif</span>
                <span class="tk-kpi-value">Rp {{ number_format($inventoryValuation, 0, ',', '.') }}</span>
                <span class="tk-metric-note">{{ $inventorySkuCount }} SKU aktif &middot; total {{ $inventoryUnitCount }} unit siap jual</span>
            </div>
        </section>

        {{-- 3. PURE-SVG REVENUE TREND CHART + TECHNICIAN PERFORMANCE --}}
        <div class="tk-admin-grid">
            {{-- Left: Pure SVG Revenue Bar Chart (0 KB JS) --}}
            <section class="tk-panel tk-anim-enter tk-delay-2" aria-label="Grafik Tren Omzet Bengkel">
                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 1rem;">
                    <div>
                        <h2 class="tk-section-title">Tren Omzet Bengkel ({{ $periodLabel }})</h2>
                        <p class="tk-subtitle">Akumulasi tiket berstatus Siap Diambil dan Selesai per rentang waktu.</p>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.85rem; font-size: 0.75rem; font-weight: 600; color: var(--tk-muted);">
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            <span style="width: 0.7rem; height: 0.7rem; border-radius: 0.2rem; background-color: var(--tk-accent); display: inline-block;"></span>
                            Biaya Jasa
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            <span style="width: 0.7rem; height: 0.7rem; border-radius: 0.2rem; background-color: #0284C7; display: inline-block;"></span>
                            Sparepart
                        </span>
                    </div>
                </div>

                @php
                    $barCount = max(1, count($chartBuckets));
                    $svgW = 640;
                    $svgH = 250;
                    $padLeft = 68;
                    $padRight = 18;
                    $padTop = 24;
                    $padBottom = 42;
                    $plotW = $svgW - $padLeft - $padRight;
                    $plotH = $svgH - $padTop - $padBottom;
                    $slotW = $plotW / $barCount;
                    $barW = min(46, max(20, $slotW * 0.56));
                @endphp

                <div style="width: 100%; overflow-x: auto;">
                    <svg viewBox="0 0 {{ $svgW }} {{ $svgH }}" style="width: 100%; min-width: 20rem; height: auto; display: block;" role="img" aria-label="Grafik batang omzet bengkel untuk periode {{ $periodLabel }}">
                        {{-- Horizontal Gridlines --}}
                        @foreach([0, 0.5, 1] as $ratio)
                            @php
                                $y = $padTop + ($plotH * (1 - $ratio));
                                $val = (int) round($chartMaxTotal * $ratio);
                            @endphp
                            <line x1="{{ $padLeft }}" y1="{{ $y }}" x2="{{ $svgW - $padRight }}" y2="{{ $y }}" stroke="var(--tk-border)" stroke-width="1" stroke-dasharray="{{ $ratio === 0 ? '0' : '4 4' }}" />
                            <text x="{{ $padLeft - 8 }}" y="{{ $y + 4 }}" text-anchor="end" fill="var(--tk-muted)" font-size="11" font-family="Inter, sans-serif">{{ $formatCompactRp($val) }}</text>
                        @endforeach

                        {{-- Stacked Bars --}}
                        @foreach($chartBuckets as $index => $bucket)
                            @php
                                $cx = $padLeft + ($index * $slotW) + ($slotW / 2);
                                $x = $cx - ($barW / 2);
                                $totalH = $bucket['total'] > 0 ? max(4, ($bucket['total'] / $chartMaxTotal) * $plotH) : 0;
                                $serviceH = $bucket['total'] > 0 ? ($bucket['service_fee'] / $bucket['total']) * $totalH : 0;
                                $partsH = $totalH - $serviceH;
                                $baseY = $padTop + $plotH;
                                $serviceY = $baseY - $serviceH;
                                $partsY = $serviceY - $partsH;
                            @endphp
                            <g>
                                <title>{{ $bucket['label'] }}: Total Rp {{ number_format($bucket['total'], 0, ',', '.') }} (Jasa Rp {{ number_format($bucket['service_fee'], 0, ',', '.') }}, Part Rp {{ number_format($bucket['parts_total'], 0, ',', '.') }}, {{ $bucket['count'] }} unit)</title>
                                @if($bucket['total'] > 0)
                                    @if($serviceH > 0)
                                        <rect x="{{ $x }}" y="{{ $serviceY }}" width="{{ $barW }}" height="{{ $serviceH }}" rx="4" fill="var(--tk-accent)" />
                                    @endif
                                    @if($partsH > 0)
                                        <rect x="{{ $x }}" y="{{ $partsY }}" width="{{ $barW }}" height="{{ $partsH }}" rx="4" fill="#0284C7" />
                                    @endif
                                    <text x="{{ $cx }}" y="{{ max(14, $partsY - 6) }}" text-anchor="middle" fill="var(--tk-ink)" font-size="10.5" font-weight="700" font-family="Inter, sans-serif">
                                        {{ $formatCompactRp($bucket['total']) }}
                                    </text>
                                @else
                                    <rect x="{{ $x }}" y="{{ $baseY - 3 }}" width="{{ $barW }}" height="3" rx="1.5" fill="var(--tk-border)" />
                                @endif
                                <text x="{{ $cx }}" y="{{ $baseY + 20 }}" text-anchor="middle" fill="var(--tk-muted)" font-size="11" font-weight="600" font-family="Inter, sans-serif">
                                    {{ $bucket['label'] }}
                                </text>
                            </g>
                        @endforeach
                    </svg>
                </div>

                @if($revenueTotal === 0)
                    <div class="tk-panel-subtle" style="margin-top: 0.85rem; font-size: 0.8rem; color: var(--tk-muted);">
                        Belum ada tiket berstatus <strong>Siap Diambil</strong> atau <strong>Selesai</strong> pada rentang <strong>{{ $periodLabel }}</strong>. Coba ubah periode ke "Semua Waktu" atau selesaikan tiket di Papan Bengkel.
                    </div>
                @endif
            </section>

            {{-- Right: Technician Productivity Table --}}
            <section class="tk-panel tk-anim-enter tk-delay-3" aria-label="Rekap Kinerja Teknisi">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 1rem;">
                    <div>
                        <h2 class="tk-section-title">Kinerja Teknisi &amp; Staf</h2>
                        <p class="tk-subtitle">Beban antrean aktif dan realisasi omzet selesai.</p>
                    </div>
                    @if($unassignedActiveCount > 0)
                        <span class="tk-badge tk-badge-warn">{{ $unassignedActiveCount }} Belum Diambil</span>
                    @endif
                </div>

                @if($technicianMetrics->isEmpty())
                    <div class="tk-empty">Belum ada akun teknisi atau admin terdaftar.</div>
                @else
                    <div class="tk-clean-table">
                        @foreach($technicianMetrics as $tech)
                            <div class="tk-clean-row" wire:key="report-tech-{{ $tech['id'] }}">
                                <div style="flex: 1 1 11rem; min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 0.45rem;">
                                        <span style="font-size: 0.9rem; font-weight: 700; color: var(--tk-ink);">{{ $tech['name'] }}</span>
                                        <span class="tk-badge tk-badge-muted">{{ $tech['role_label'] }}</span>
                                    </div>
                                    <div style="font-size: 0.77rem; color: var(--tk-muted); margin-top: 0.2rem;">
                                        Aktif: <strong style="color: var(--tk-ink);">{{ $tech['active_count'] }}</strong>
                                        &middot; Selesai: <strong style="color: var(--tk-ink);">{{ $tech['done_count'] }}</strong>
                                        @if($tech['avg_temp_drop'] !== null)
                                            &middot; Suhu <strong style="color: var(--tk-ok-fg);">-{{ number_format($tech['avg_temp_drop'], 1, ',', '.') }}°C</strong>
                                        @endif
                                    </div>
                                </div>
                                <div class="tk-clean-row-price">
                                    <div class="tk-mono" style="font-size: 0.9rem; font-weight: 700; color: var(--tk-ink);">
                                        Rp {{ number_format($tech['revenue_total'], 0, ',', '.') }}
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--tk-muted);">
                                        Jasa {{ $formatCompactRp($tech['service_fee']) }} &middot; Part {{ $formatCompactRp($tech['parts_total']) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-filament-panels::page>
