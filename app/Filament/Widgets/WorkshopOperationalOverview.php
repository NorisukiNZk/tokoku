<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\ServiceTicket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WorkshopOperationalOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $activeServiceCount = ServiceTicket::query()
            ->whereIn('status', ['booked', 'diagnosing', 'in_progress', 'testing_qc'])
            ->count();

        $waitingApprovalCount = ServiceTicket::query()
            ->where('status', 'waiting_approval')
            ->count();

        $readyPickupCount = ServiceTicket::query()
            ->where('status', 'ready')
            ->count();

        $availableItems = Product::query()
            ->where('availability_status', 'available')
            ->where('stock', '>', 0)
            ->count();

        return [
            Stat::make('Antrean Servis Dikerjakan', (string) $activeServiceCount)
                ->description('Unit berstatus jadwal, diagnosa, pengerjaan, atau QC')
                ->color('warning'),
            Stat::make('Menunggu Konfirmasi Biaya', (string) $waitingApprovalCount)
                ->description('Perlu persetujuan estimasi biaya pelanggan')
                ->color($waitingApprovalCount > 0 ? 'danger' : 'gray'),
            Stat::make('Servis Siap Diambil', (string) $readyPickupCount)
                ->description('Unit lulus QC menunggu pengambilan pelanggan')
                ->color('success'),
            Stat::make('Judul Barang Siap Jual', (string) $availableItems)
                ->description('Produk tersedia dengan stok fisik di atas nol')
                ->color('primary'),
        ];
    }
}
