<?php

namespace App\Console\Commands;

use App\Services\PcPartDatasetSyncService;
use Illuminate\Console\Command;

class SyncPcPartsCommand extends Command
{
    protected $signature = 'tokoku:sync-parts {--rate= : Kurs USD ke IDR manual (opsional)}';
    protected $description = 'Tarik ribuan komponen PC beserta harga dari API docyx/pc-part-dataset dan konversi ke Rupiah';

    public function handle(PcPartDatasetSyncService $syncService): int
    {
        $overrideRate = $this->option('rate') ? (int) $this->option('rate') : null;

        $this->info('Menghubungi API kurs mata uang & repositori JSON docyx/pc-part-dataset...');

        $result = $syncService->syncAll($overrideRate);

        $this->info("Berhasil menyinkronkan {$result['total_synced']} komponen PC (Kurs 1 USD = Rp " . number_format($result['usd_rate'], 0, ',', '.') . ').');
        foreach ($result['counts_by_slot'] as $slot => $count) {
            $this->line(" - {$slot}: {$count} part");
        }

        return self::SUCCESS;
    }
}
