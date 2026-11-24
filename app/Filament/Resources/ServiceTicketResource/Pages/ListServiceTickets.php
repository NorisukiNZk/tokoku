<?php

namespace App\Filament\Resources\ServiceTicketResource\Pages;

use App\Filament\Resources\ServiceTicketResource;
use App\Models\ServiceTicket;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListServiceTickets extends ListRecords
{
    protected static string $resource = ServiceTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Buat Tiket Servis Baru'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'aktif' => Tab::make('Aktif di Bengkel')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['booked', 'diagnosing', 'waiting_approval', 'in_progress', 'testing_qc', 'ready']))
                ->badge(ServiceTicket::query()->whereIn('status', ['booked', 'diagnosing', 'waiting_approval', 'in_progress', 'testing_qc', 'ready'])->count()),
            'tunggu_biaya' => Tab::make('Tunggu Persetujuan')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'waiting_approval'))
                ->badge(ServiceTicket::query()->where('status', 'waiting_approval')->count())
                ->badgeColor('danger'),
            'siap_ambil' => Tab::make('Siap Diambil')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'ready'))
                ->badge(ServiceTicket::query()->where('status', 'ready')->count())
                ->badgeColor('success'),
            'selesai' => Tab::make('Arsip Selesai')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['completed', 'cancelled'])),
            'semua' => Tab::make('Semua Tiket'),
        ];
    }
}
