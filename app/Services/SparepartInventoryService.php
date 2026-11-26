<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ServiceTicket;
use App\Models\ServiceTicketPart;
use Illuminate\Support\Facades\DB;

class SparepartInventoryService
{
    public function shouldHoldStock(?ServiceTicket $ticket): bool
    {
        if (! $ticket) {
            return false;
        }

        return $ticket->status !== 'cancelled';
    }

    public function reconcileSavedPart(ServiceTicketPart $part, ?int $oldProductId, int $oldDeductedQty): void
    {
        DB::transaction(function () use ($part, $oldProductId, $oldDeductedQty) {
            $ticket = $part->ticket()->first();
            $holdStock = $this->shouldHoldStock($ticket);

            $targetProductId = ($holdStock && $part->product_id) ? (int) $part->product_id : null;
            $targetQty = $targetProductId ? max(0, (int) $part->qty) : 0;

            $currentDeducted = max(0, $oldDeductedQty);

            if ($oldProductId && $oldProductId !== $targetProductId && $currentDeducted > 0) {
                $this->returnStockToProduct($oldProductId, $currentDeducted);
                $currentDeducted = 0;
            }

            $newDeducted = 0;
            if ($targetProductId) {
                $delta = $targetQty - $currentDeducted;
                if ($delta > 0) {
                    $actuallyTaken = $this->deductStockFromProduct($targetProductId, $delta);
                    $newDeducted = $currentDeducted + $actuallyTaken;
                } elseif ($delta < 0) {
                    $this->returnStockToProduct($targetProductId, abs($delta));
                    $newDeducted = $targetQty;
                } else {
                    $newDeducted = $currentDeducted;
                }
            }

            if ((int) $part->stock_deducted_qty !== $newDeducted) {
                $part->stock_deducted_qty = $newDeducted;
                $part->saveQuietly();
            }
        });
    }

    public function releaseDeletedPart(ServiceTicketPart $part): void
    {
        $productId = $part->product_id ? (int) $part->product_id : null;
        $deducted = max(0, (int) $part->stock_deducted_qty);

        if (! $productId || $deducted <= 0) {
            return;
        }

        DB::transaction(function () use ($productId, $deducted) {
            $this->returnStockToProduct($productId, $deducted);
        });
    }

    public function syncTicketPartsForStatus(ServiceTicket $ticket): void
    {
        DB::transaction(function () use ($ticket) {
            $holdStock = $this->shouldHoldStock($ticket);
            $parts = $ticket->parts()->get();

            foreach ($parts as $part) {
                $productId = $part->product_id ? (int) $part->product_id : null;
                $deducted = max(0, (int) $part->stock_deducted_qty);

                if (! $productId) {
                    continue;
                }

                if (! $holdStock && $deducted > 0) {
                    $this->returnStockToProduct($productId, $deducted);
                    $part->stock_deducted_qty = 0;
                    $part->saveQuietly();
                } elseif ($holdStock && $deducted < (int) $part->qty) {
                    $needed = max(0, (int) $part->qty - $deducted);
                    $taken = $this->deductStockFromProduct($productId, $needed);
                    if ($taken > 0) {
                        $part->stock_deducted_qty = $deducted + $taken;
                        $part->saveQuietly();
                    }
                }
            }
        });
    }

    private function deductStockFromProduct(int $productId, int $requestedQty): int
    {
        if ($requestedQty <= 0) {
            return 0;
        }

        $product = Product::query()->lockForUpdate()->find($productId);
        if (! $product || $product->stock <= 0) {
            return 0;
        }

        $taken = min((int) $product->stock, $requestedQty);
        $remaining = max(0, (int) $product->stock - $taken);

        $updates = ['stock' => $remaining];
        if ($remaining === 0 && $product->availability_status === 'available') {
            $updates['availability_status'] = 'sold';
        }

        $product->update($updates);

        return $taken;
    }

    private function returnStockToProduct(int $productId, int $returnQty): void
    {
        if ($returnQty <= 0) {
            return;
        }

        $product = Product::query()->lockForUpdate()->find($productId);
        if (! $product) {
            return;
        }

        $newStock = (int) $product->stock + $returnQty;
        $updates = ['stock' => $newStock];
        if ($newStock > 0 && $product->availability_status === 'sold') {
            $updates['availability_status'] = 'available';
        }

        $product->update($updates);
    }
}
