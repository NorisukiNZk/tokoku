<?php

namespace App\Filament\Resources\ServiceTicketResource\Pages;

use App\Filament\Resources\ServiceTicketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceTicket extends EditRecord
{
    protected static string $resource = ServiceTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview_tracking')
                ->label('Lihat Halaman Lacak Pelanggan')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => url('/servis?tiket=' . $this->record->ticket_number . '&pin=' . $this->record->phoneLastFour()), shouldOpenInNewTab: true),
            Actions\DeleteAction::make(),
        ];
    }
}
