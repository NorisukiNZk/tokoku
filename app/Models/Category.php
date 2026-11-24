<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'component_slot',
        'sort_order',
    ];

    public const COMPONENT_SLOTS = [
        'cpu' => 'Prosesor (CPU)',
        'motherboard' => 'Motherboard',
        'ram' => 'Memori (RAM)',
        'gpu' => 'Kartu Grafis (VGA / GPU)',
        'storage' => 'Penyimpanan (SSD / HDD)',
        'psu' => 'Power Supply (PSU)',
        'case' => 'Casing PC',
        'cooler' => 'Pendingin CPU (Cooler)',
        'monitor' => 'Monitor',
        'laptop' => 'Laptop',
        'peripheral' => 'Aksesori & Peripheral',
        'other' => 'Lainnya',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
