<?php

namespace App\Models;

use App\Services\SparepartInventoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTicketPart extends Model
{
    /** @var array{product_id: int|null, deducted_qty: int}|null */
    protected ?array $previousInventorySnapshot = null;

    protected $fillable = [
        'service_ticket_id',
        'product_id',
        'part_name',
        'qty',
        'stock_deducted_qty',
        'unit_price',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'stock_deducted_qty' => 'integer',
            'unit_price' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $part) {
            $rawOldProduct = $part->exists ? $part->getOriginal('product_id') : null;
            $rawOldDeducted = $part->exists ? $part->getOriginal('stock_deducted_qty') : 0;

            $part->previousInventorySnapshot = [
                'product_id' => $rawOldProduct ? (int) $rawOldProduct : null,
                'deducted_qty' => max(0, (int) $rawOldDeducted),
            ];

            $part->subtotal = max(1, (int) $part->qty) * max(0, (int) $part->unit_price);
        });

        static::saved(function (self $part) {
            $snapshot = $part->previousInventorySnapshot ?? ['product_id' => null, 'deducted_qty' => 0];
            $part->previousInventorySnapshot = null;

            app(SparepartInventoryService::class)->reconcileSavedPart(
                $part,
                $snapshot['product_id'],
                $snapshot['deducted_qty']
            );

            $part->ticket?->recalculateTotals();
        });

        static::deleted(function (self $part) {
            app(SparepartInventoryService::class)->releaseDeletedPart($part);
            $part->ticket?->recalculateTotals();
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(ServiceTicket::class, 'service_ticket_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
