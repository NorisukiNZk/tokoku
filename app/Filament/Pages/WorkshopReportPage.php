<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\ServiceTicket;
use App\Models\StoreSetting;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkshopReportPage extends Page
{
    protected static ?string $navigationGroup = 'Bengkel & Servis PC';

    protected static ?int $navigationSort = 13;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Laporan Omzet & Kinerja';

    protected static ?string $title = 'Laporan Omzet & Kinerja';

    protected static ?string $slug = 'laporan-omzet';

    protected static string $view = 'filament.pages.workshop-report';

    public string $period = '30d';

    public const PERIOD_OPTIONS = [
        '7d' => '7 Hari',
        '30d' => '30 Hari',
        'month' => 'Bulan Ini',
        'all' => 'Semua Waktu',
    ];

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function getHeading(): string | Htmlable
    {
        return '';
    }

    public function setPeriod(string $period): void
    {
        if (array_key_exists($period, self::PERIOD_OPTIONS)) {
            $this->period = $period;
        }
    }

    public function exportServiceCsv(): StreamedResponse
    {
        $filename = 'tokoku-laporan-servis-' . $this->period . '-' . now()->format('Ymd') . '.csv';
        $tickets = $this->applyPeriodFilter(
            ServiceTicket::query()->with(['technician', 'servicePackage'])->orderByDesc('created_at')
        )->get();

        return response()->streamDownload(function () use ($tickets): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // UTF-8 BOM so Microsoft Excel opens Indonesian characters cleanly.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No Tiket',
                'Tanggal Masuk',
                'Pelanggan',
                'No HP',
                'Perangkat',
                'Paket Servis',
                'Status',
                'Teknisi',
                'Suhu Awal (C)',
                'Suhu Akhir (C)',
                'Biaya Jasa (Rp)',
                'Biaya Part (Rp)',
                'Total (Rp)',
                'Disetujui Pelanggan',
                'Garansi Sampai',
            ]);

            foreach ($tickets as $ticket) {
                fputcsv($handle, [
                    $this->safeCsvCell($ticket->ticket_number),
                    $ticket->created_at?->format('Y-m-d H:i') ?? '',
                    $this->safeCsvCell($ticket->customer_name),
                    $this->safeCsvCell($ticket->customer_phone),
                    $this->safeCsvCell($ticket->device_brand_model),
                    $this->safeCsvCell($ticket->servicePackage?->name ?? 'Reguler / Custom'),
                    $this->safeCsvCell($ticket->statusLabel()),
                    $this->safeCsvCell($ticket->technician?->name ?? 'Belum ditugaskan'),
                    $ticket->temp_before_c !== null ? (string) $ticket->temp_before_c : '',
                    $ticket->temp_after_c !== null ? (string) $ticket->temp_after_c : '',
                    (string) (int) $ticket->service_fee,
                    (string) (int) $ticket->parts_total,
                    (string) ((int) $ticket->service_fee + (int) $ticket->parts_total),
                    $ticket->customer_approved_at?->format('Y-m-d H:i') ?? '',
                    $ticket->warranty_until?->format('Y-m-d') ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportProductsCsv(): StreamedResponse
    {
        $filename = 'tokoku-inventaris-produk-' . now()->format('Ymd') . '.csv';
        $products = Product::query()
            ->with('category')
            ->orderBy('availability_status')
            ->orderBy('name')
            ->get();

        return response()->streamDownload(function () use ($products): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'SKU',
                'Nama Produk',
                'Kategori',
                'Brand',
                'Kondisi',
                'Status',
                'Stok',
                'Harga Satuan (Rp)',
                'Nilai Total Stok (Rp)',
                'Slot Rakit PC',
            ]);

            foreach ($products as $product) {
                fputcsv($handle, [
                    $this->safeCsvCell($product->sku),
                    $this->safeCsvCell($product->name),
                    $this->safeCsvCell($product->category?->name ?? '-'),
                    $this->safeCsvCell($product->brand ?? '-'),
                    $this->safeCsvCell($product->conditionShortLabel()),
                    $this->safeCsvCell(Product::AVAILABILITY_STATUSES[$product->availability_status] ?? $product->availability_status),
                    (string) (int) $product->stock,
                    (string) (int) $product->price,
                    (string) ((int) $product->price * (int) $product->stock),
                    $this->safeCsvCell($product->component_slot ?? '-'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function getViewData(): array
    {
        $store = StoreSetting::current();
        $periodStart = $this->periodStartDate();

        $allPeriodTickets = $this->applyPeriodFilter(
            ServiceTicket::query()->with('technician')
        )->get();

        $revenueTickets = $allPeriodTickets->filter(
            fn (ServiceTicket $ticket): bool => in_array($ticket->status, ['ready', 'completed'], true)
        );

        $serviceFeeTotal = (int) $revenueTickets->sum('service_fee');
        $partsRevenueTotal = (int) $revenueTickets->sum('parts_total');
        $revenueTotal = $serviceFeeTotal + $partsRevenueTotal;
        $completedCount = $revenueTickets->count();
        $customerApprovedCount = $allPeriodTickets->whereNotNull('customer_approved_at')->count();

        $tempDrops = $allPeriodTickets
            ->filter(fn (ServiceTicket $ticket): bool => $ticket->temp_before_c !== null && $ticket->temp_after_c !== null && $ticket->temp_before_c > $ticket->temp_after_c)
            ->map(fn (ServiceTicket $ticket): int => (int) $ticket->temp_before_c - (int) $ticket->temp_after_c);

        $avgTempDrop = $tempDrops->isNotEmpty()
            ? round((float) $tempDrops->avg(), 1)
            : null;

        $activeInventoryQuery = Product::query()
            ->where('availability_status', 'available')
            ->where('stock', '>', 0);

        $inventoryValuation = (int) (clone $activeInventoryQuery)
            ->selectRaw('COALESCE(SUM(price * stock), 0) as total_val')
            ->value('total_val');
        $inventorySkuCount = (int) (clone $activeInventoryQuery)->count();
        $inventoryUnitCount = (int) (clone $activeInventoryQuery)->sum('stock');

        $chartBuckets = $this->buildChartBuckets($revenueTickets);
        $chartMaxTotal = max(1, (int) collect($chartBuckets)->max('total'));

        $activeTicketsByTech = ServiceTicket::query()
            ->whereIn('status', ['booked', 'diagnosing', 'waiting_approval', 'in_progress', 'testing_qc'])
            ->selectRaw('technician_id, COUNT(*) as total')
            ->groupBy('technician_id')
            ->pluck('total', 'technician_id');

        $staffUsers = User::query()
            ->whereIn('role', ['admin', 'technician'])
            ->orderByRaw("CASE WHEN role = 'technician' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        $technicianMetrics = $staffUsers->map(function (User $user) use ($revenueTickets, $allPeriodTickets, $activeTicketsByTech): array {
            $techDone = $revenueTickets->where('technician_id', $user->id);
            $techPeriod = $allPeriodTickets->where('technician_id', $user->id);
            $techDrops = $techPeriod
                ->filter(fn (ServiceTicket $t): bool => $t->temp_before_c !== null && $t->temp_after_c !== null && $t->temp_before_c > $t->temp_after_c)
                ->map(fn (ServiceTicket $t): int => (int) $t->temp_before_c - (int) $t->temp_after_c);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'role_label' => $user->role === 'technician' ? 'Teknisi' : 'Admin',
                'active_count' => (int) ($activeTicketsByTech[$user->id] ?? 0),
                'done_count' => $techDone->count(),
                'service_fee' => (int) $techDone->sum('service_fee'),
                'parts_total' => (int) $techDone->sum('parts_total'),
                'revenue_total' => (int) $techDone->sum('service_fee') + (int) $techDone->sum('parts_total'),
                'avg_temp_drop' => $techDrops->isNotEmpty() ? round((float) $techDrops->avg(), 1) : null,
            ];
        })->sortByDesc('revenue_total')->values();

        $unassignedActiveCount = (int) ($activeTicketsByTech->get('') ?? $activeTicketsByTech->get(0) ?? 0);

        return [
            'store' => $store,
            'periodLabel' => self::PERIOD_OPTIONS[$this->period] ?? '30 Hari',
            'periodStartLabel' => $periodStart ? $periodStart->format('d M Y') . ' - ' . now()->format('d M Y') : 'Seluruh Riwayat Database',
            'revenueTotal' => $revenueTotal,
            'serviceFeeTotal' => $serviceFeeTotal,
            'partsRevenueTotal' => $partsRevenueTotal,
            'completedCount' => $completedCount,
            'totalPeriodTickets' => $allPeriodTickets->count(),
            'customerApprovedCount' => $customerApprovedCount,
            'avgTempDrop' => $avgTempDrop,
            'tempSampleCount' => $tempDrops->count(),
            'inventoryValuation' => $inventoryValuation,
            'inventorySkuCount' => $inventorySkuCount,
            'inventoryUnitCount' => $inventoryUnitCount,
            'chartBuckets' => $chartBuckets,
            'chartMaxTotal' => $chartMaxTotal,
            'technicianMetrics' => $technicianMetrics,
            'unassignedActiveCount' => $unassignedActiveCount,
        ];
    }

    private function periodStartDate(): ?Carbon
    {
        return match ($this->period) {
            '7d' => now()->subDays(6)->startOfDay(),
            '30d' => now()->subDays(29)->startOfDay(),
            'month' => now()->startOfMonth(),
            default => null,
        };
    }

    private function applyPeriodFilter(Builder $query): Builder
    {
        $start = $this->periodStartDate();
        if ($start !== null) {
            $query->where('updated_at', '>=', $start);
        }

        return $query;
    }

    /**
     * @param Collection<int, ServiceTicket> $revenueTickets
     * @return array<int, array{label: string, service_fee: int, parts_total: int, total: int, count: int}>
     */
    private function buildChartBuckets(Collection $revenueTickets): array
    {
        $buckets = [];
        $now = now();

        if ($this->period === '7d') {
            for ($i = 6; $i >= 0; $i--) {
                $dayStart = $now->copy()->subDays($i)->startOfDay();
                $dayEnd = $dayStart->copy()->endOfDay();
                $buckets[] = $this->summarizeBucket($revenueTickets, $dayStart, $dayEnd, $dayStart->format('d M'));
            }

            return $buckets;
        }

        if ($this->period === '30d') {
            for ($i = 5; $i >= 0; $i--) {
                $rangeStart = $now->copy()->subDays(($i * 5) + 4)->startOfDay();
                $rangeEnd = $now->copy()->subDays($i * 5)->endOfDay();
                $label = $rangeStart->format('d') . '-' . $rangeEnd->format('d M');
                $buckets[] = $this->summarizeBucket($revenueTickets, $rangeStart, $rangeEnd, $label);
            }

            return $buckets;
        }

        if ($this->period === 'month') {
            $monthStart = $now->copy()->startOfMonth();
            $daysInMonth = $now->daysInMonth;
            for ($week = 0; $week < 5; $week++) {
                $startDay = ($week * 7) + 1;
                if ($startDay > $daysInMonth) {
                    break;
                }
                $endDay = min($daysInMonth, $startDay + 6);
                $rangeStart = $monthStart->copy()->day($startDay)->startOfDay();
                $rangeEnd = $monthStart->copy()->day($endDay)->endOfDay();
                $buckets[] = $this->summarizeBucket($revenueTickets, $rangeStart, $rangeEnd, 'Mg ' . ($week + 1));
            }

            return $buckets;
        }

        // All time: show the latest 6 calendar months so bars stay crisp and readable.
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonthsNoOverflow($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $buckets[] = $this->summarizeBucket($revenueTickets, $monthStart, $monthEnd, $monthStart->format('M y'));
        }

        return $buckets;
    }

    /**
     * @param Collection<int, ServiceTicket> $revenueTickets
     * @return array{label: string, service_fee: int, parts_total: int, total: int, count: int}
     */
    private function summarizeBucket(Collection $revenueTickets, Carbon $start, Carbon $end, string $label): array
    {
        $matched = $revenueTickets->filter(function (ServiceTicket $ticket) use ($start, $end): bool {
            $ts = $ticket->updated_at ?? $ticket->created_at;

            return $ts !== null && $ts->between($start, $end);
        });

        $serviceFee = (int) $matched->sum('service_fee');
        $partsTotal = (int) $matched->sum('parts_total');

        return [
            'label' => $label,
            'service_fee' => $serviceFee,
            'parts_total' => $partsTotal,
            'total' => $serviceFee + $partsTotal,
            'count' => $matched->count(),
        ];
    }

    private function safeCsvCell(?string $value): string
    {
        $str = (string) ($value ?? '');
        if ($str !== '' && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $str;
        }

        return $str;
    }
}
