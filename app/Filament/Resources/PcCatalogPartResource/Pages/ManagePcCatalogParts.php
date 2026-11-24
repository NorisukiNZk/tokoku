<?php

namespace App\Filament\Resources\PcCatalogPartResource\Pages;

use App\Filament\Resources\PcCatalogPartResource;
use App\Models\StoreSetting;
use App\Services\PcPartDatasetSyncService;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManagePcCatalogParts extends ManageRecords
{
    protected static string $resource = PcCatalogPartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync_api')
                ->label('Sinkronkan Ribuan Part dari API')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->form([
                    TextInput::make('custom_rate')
                        ->label('Kurs 1 USD ke Rupiah (Kosongkan untuk ambil kurs otomatis)')
                        ->numeric()
                        ->placeholder((string) (StoreSetting::current()->usd_to_idr_rate ?: 16500)),
                ])
                ->action(function (array $data, PcPartDatasetSyncService $syncService): void {
                    $rate = ! empty($data['custom_rate']) ? (int) $data['custom_rate'] : null;
                    $res = $syncService->syncAll($rate);

                    Notification::make()
                        ->title("Berhasil memperbarui {$res['total_synced']} komponen PC (Kurs Rp " . number_format($res['usd_rate'], 0, ',', '.') . ').')
                        ->success()
                        ->send();
                }),
        ];
    }
}
