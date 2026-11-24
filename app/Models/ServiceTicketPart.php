<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTicketPart extends Model
{
    protected $fillable = [
        'service_ticket_id',
        'product_id',
        'part_name',
        'qty',
        'unit_price',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'unit_price' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $part) {
            $part->subtotal = max(1, (int) $part->qty) * max(0, (int) $part->unit_price);
        });

        static::saved(function (self $part) {
            $part->ticket?->recalculateTotals();
        });

        static::deleted(function (self $part) {
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
