<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PcBuild extends Model
{
    protected $fillable = [
        'share_code',
        'build_name',
        'customer_name',
        'purpose_label',
        'selected_components',
        'estimated_wattage',
        'recommended_psu_wattage',
        'compatibility_passed',
        'compatibility_notes',
        'include_assembly_service',
        'assembly_fee',
        'parts_subtotal',
        'grand_total',
    ];

    protected function casts(): array
    {
        return [
            'selected_components' => 'array',
            'estimated_wattage' => 'integer',
            'recommended_psu_wattage' => 'integer',
            'compatibility_passed' => 'boolean',
            'compatibility_notes' => 'array',
            'include_assembly_service' => 'boolean',
            'assembly_fee' => 'integer',
            'parts_subtotal' => 'integer',
            'grand_total' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $build) {
            if (empty($build->share_code)) {
                do {
                    $code = 'RKT-' . strtoupper(Str::random(6));
                } while (static::query()->where('share_code', $code)->exists());

                $build->share_code = $code;
            }
        });
    }

    public function formattedGrandTotal(): string
    {
        return 'Rp ' . number_format($this->grand_total, 0, ',', '.');
    }
}
