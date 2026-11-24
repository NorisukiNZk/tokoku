<?php

namespace App\Filament\Pages;

use App\Models\PcBuild;
use App\Models\PcCatalogPart;
use App\Models\Product;
use App\Models\ServiceTicket;
use App\Models\ServiceTicketLog;
use App\Models\StoreSetting;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class AdminDashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Beranda Operasional';
    protected static ?string $title = 'Pusat Kendali Toko';
    protected static string $view = 'filament.pages.admin-dashboard';

    public string $ticketFilter = 'active';
    public string $stockFilter = 'low';

    public function setTicketFilter(string $filter): void
    {
        if (in_array($filter, ['active', 'approval', 'ready'], true)) {
            $this->ticketFilter = $filter;
        }
    }

    public function setStockFilter(string $filter): void
    {
        if (in_array($filter, ['low', 'all'], true)) {
            $this->stockFilter = $filter;
        }
    }

    public function advanceTicketStep(int $ticketId): void
    {
        $ticket = ServiceTicket::query()->find($ticketId);
        if (! $ticket) {
            return;
        }

        $nextMap = [
            'booked' => 'diagnosing',
            'diagnosing' => 'in_progress',
            'waiting_approval' => 'in_progress',
            'in_progress' => 'testing_qc',
            'testing_qc' => 'ready',
            'ready' => 'completed',
        ];

        $nextStatus = $nextMap[$ticket->status] ?? null;
        if (! $nextStatus) {
            return;
        }

        $updates = ['status' => $nextStatus];
        if (! $ticket->technician_id && Auth::id()) {
            $updates['technician_id'] = Auth::id();
        }
        if ($nextStatus === 'completed' && ! $ticket->warranty_until) {
            $updates['warranty_until'] = now()->addDays(14)->toDateString();
        }

        $ticket->update($updates);

        ServiceTicketLog::query()->create([
            'service_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'status' => $nextStatus,
            'note' => 'Status dilanjutkan ke ' . ServiceTicket::STATUSES[$nextStatus] . ' dari Beranda Admin.',
            'is_public' => true,
        ]);

        Notification::make()
            ->title("Tiket {$ticket->ticket_number} -> " . ServiceTicket::STATUSES[$nextStatus])
            ->success()
            ->send();
    }

    public function adjustProductStock(int $productId, int $delta): void
    {
        $product = Product::query()->find($productId);
        if (! $product) {
            return;
        }

        $newStock = max(0, (int) $product->stock + $delta);
        $status = $newStock === 0 ? 'sold' : 'available';

        $product->update([
            'stock' => $newStock,
            'availability_status' => $status,
        ]);

        Notification::make()
            ->title("Stok {$product->sku} diubah menjadi {$newStock} unit.")
            ->success()
            ->send();
    }

    public function markProductSold(int $productId): void
    {
        $product = Product::query()->find($productId);
        if (! $product) {
            return;
        }

        $product->update([
            'stock' => 0,
            'availability_status' => 'sold',
        ]);

        Notification::make()
            ->title("{$product->name} ditandai Terjual.")
            ->success()
            ->send();
    }

    protected function getViewData(): array
    {
        $store = StoreSetting::current();

        $activeQueueCount = ServiceTicket::query()
            ->whereIn('status', ['booked', 'diagnosing', 'in_progress', 'testing_qc'])
            ->count();

        $waitingApprovalCount = ServiceTicket::query()
            ->where('status', 'waiting_approval')
            ->count();

        $readyPickupCount = ServiceTicket::query()
            ->where('status', 'ready')
            ->count();

        $availableProductsCount = Product::query()
            ->where('availability_status', 'available')
            ->where('stock', '>', 0)
            ->count();

        $inventoryValuation = (int) Product::query()
            ->where('availability_status', 'available')
            ->where('stock', '>', 0)
            ->selectRaw('COALESCE(SUM(price * stock), 0) as total_val')
            ->value('total_val');

        $catalogPartsCount = PcCatalogPart::query()->count();

        $ticketsQuery = ServiceTicket::query()
            ->with(['technician', 'parts'])
            ->orderByDesc('updated_at');

        if ($this->ticketFilter === 'approval') {
            $ticketsQuery->where('status', 'waiting_approval');
        } elseif ($this->ticketFilter === 'ready') {
            $ticketsQuery->where('status', 'ready');
        } else {
            $ticketsQuery->whereIn('status', ['booked', 'diagnosing', 'waiting_approval', 'in_progress', 'testing_qc', 'ready']);
        }

        $productsQuery = Product::query()
            ->with('category')
            ->where('availability_status', 'available')
            ->orderBy('stock')
            ->orderByDesc('updated_at');

        if ($this->stockFilter === 'low') {
            $productsQuery->where('stock', '<=', 2);
        }

        $recentBuilds = PcBuild::query()
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        return [
            'store' => $store,
            'activeQueueCount' => $activeQueueCount,
            'waitingApprovalCount' => $waitingApprovalCount,
            'readyPickupCount' => $readyPickupCount,
            'availableProductsCount' => $availableProductsCount,
            'inventoryValuation' => $inventoryValuation,
            'catalogPartsCount' => $catalogPartsCount,
            'tickets' => $ticketsQuery->limit(8)->get(),
            'products' => $productsQuery->limit(6)->get(),
            'recentBuilds' => $recentBuilds,
        ];
    }
}
