<?php

namespace App\Filament\Resources\ServiceTicketResource\Pages;

use App\Filament\Resources\ServiceTicketResource;
use App\Models\ServiceTicketLog;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateServiceTicket extends CreateRecord
{
    protected static string $resource = ServiceTicketResource::class;

    protected function afterCreate(): void
    {
        ServiceTicketLog::query()->create([
            'service_ticket_id' => $this->record->id,
            'user_id' => Auth::id(),
            'status' => $this->record->status,
            'note' => 'Tiket servis dibuat melalui meja operasional toko.',
            'is_public' => true,
        ]);
    }
}
