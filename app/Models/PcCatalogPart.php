<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcCatalogPart extends Model
{
    protected $fillable = [
        'external_key',
        'slot',
        'name',
        'brand',
        'price_usd',
        'estimated_price_idr',
        'price_tier',
        'spec_summary',
        'compatibility_specs',
    ];

    protected function casts(): array
    {
        return [
            'price_usd' => 'float',
            'estimated_price_idr' => 'integer',
            'compatibility_specs' => 'array',
        ];
    }

    public const PRICE_TIERS = [
        'budget' => 'Kelas Hemat (Entry)',
        'mid' => 'Kelas Menengah (Mid)',
        'high' => 'Kelas Tinggi (High-End)',
    ];

    public function formattedPrice(): string
    {
        return 'Rp ' . number_format($this->estimated_price_idr, 0, ',', '.');
    }

    public function tierLabel(): string
    {
        return match ($this->price_tier) {
            'budget' => 'Kelas Hemat',
            'mid' => 'Menengah',
            'high' => 'High-End',
            default => 'Menengah',
        };
    }

    public function specBadges(): array
    {
        if (! empty($this->spec_summary)) {
            return array_slice(array_map('trim', explode('|', $this->spec_summary)), 0, 3);
        }

        return [];
    }
}
