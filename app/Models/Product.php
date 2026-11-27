<?php

namespace App\Models;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'brand',
        'condition',
        'warranty_info',
        'condition_notes',
        'description',
        'price',
        'is_negotiable',
        'stock',
        'availability_status',
        'location_area',
        'cod_available',
        'show_in_marketplace',
        'show_in_pc_builder',
        'primary_image',
        'gallery_images',
        'compatibility_specs',
        'is_sample_data',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
            'is_negotiable' => 'boolean',
            'cod_available' => 'boolean',
            'show_in_marketplace' => 'boolean',
            'show_in_pc_builder' => 'boolean',
            'gallery_images' => 'array',
            'compatibility_specs' => 'array',
            'is_sample_data' => 'boolean',
        ];
    }

    public const CONDITIONS = [
        'new' => 'Baru Segel (BNIB)',
        'like_new' => 'Bekas Mulus (Like New)',
        'good_used' => 'Bekas Normal',
        'minus_parts' => 'Minus / Bahan Part',
    ];

    public const AVAILABILITY_STATUSES = [
        'available' => 'Tersedia',
        'booked' => 'Sedang Dipesan (Booked)',
        'sold' => 'Terjual',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            static::flushProductCaches();
        });

        static::updated(function (self $product) {
            $tracked = ['price', 'stock', 'availability_status', 'show_in_marketplace'];
            $changes = array_intersect_key($product->getChanges(), array_flip($tracked));
            if (count($changes) === 0) {
                return;
            }

            $before = [];
            $after = [];
            $parts = [];
            foreach ($changes as $field => $newVal) {
                $oldVal = $product->getOriginal($field);
                $before[$field] = $oldVal;
                $after[$field] = $newVal;
                if ($field === 'price') {
                    $parts[] = 'Harga Rp ' . number_format((int) $oldVal, 0, ',', '.') . ' -> Rp ' . number_format((int) $newVal, 0, ',', '.');
                } elseif ($field === 'stock') {
                    $parts[] = "Stok {$oldVal} -> {$newVal}";
                } elseif ($field === 'availability_status') {
                    $parts[] = "Status {$oldVal} -> {$newVal}";
                } elseif ($field === 'show_in_marketplace') {
                    $parts[] = 'Tayang etalase: ' . ($newVal ? 'Ya' : 'Tidak');
                }
            }

            ActivityLogger::record(
                action: 'product_updated',
                subjectType: 'Product',
                subjectId: $product->id,
                subjectLabel: "{$product->sku} - {$product->name}",
                description: implode(' · ', $parts),
                before: $before,
                after: $after,
            );
        });

        static::deleted(function (self $product) {
            static::flushProductCaches();
            ActivityLogger::record(
                action: 'product_deleted',
                subjectType: 'Product',
                subjectId: $product->id,
                subjectLabel: "{$product->sku} - {$product->name}",
                description: 'Produk dihapus dari katalog.',
                before: ['price' => $product->price, 'stock' => $product->stock],
            );
        });
    }

    public static function flushProductCaches(): void
    {
        Cache::forget('tokoku.nav.products_available');
        Cache::forget('tokoku.builder.store_products');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function conditionLabel(): string
    {
        return self::CONDITIONS[$this->condition] ?? $this->condition;
    }

    public function conditionShortLabel(): string
    {
        return match ($this->condition) {
            'new' => 'Baru Segel',
            'like_new' => 'Second Mulus',
            'good_used' => 'Second Normal',
            'minus_parts' => 'Unit Minus',
            default => ucfirst($this->condition),
        };
    }

    public function availabilityLabel(): string
    {
        return self::AVAILABILITY_STATUSES[$this->availability_status] ?? $this->availability_status;
    }

    public function formattedPrice(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function primaryImageUrl(): ?string
    {
        if (! $this->primary_image) {
            return null;
        }

        if (str_starts_with($this->primary_image, 'http://') || str_starts_with($this->primary_image, 'https://') || str_starts_with($this->primary_image, '/')) {
            return $this->primary_image;
        }

        return '/storage/' . ltrim($this->primary_image, '/');
    }

    public function specSummaryBadges(): array
    {
        $specs = $this->compatibility_specs ?? [];
        $badges = [];

        if (! empty($specs['socket'])) {
            $badges[] = 'Socket ' . $specs['socket'];
        }
        if (! empty($specs['ram_type'])) {
            $badges[] = $specs['ram_type'];
        }
        if (! empty($specs['capacity_gb'])) {
            $badges[] = $specs['capacity_gb'] . ' GB';
        }
        if (! empty($specs['form_factor'])) {
            $badges[] = $specs['form_factor'];
        }
        if (! empty($specs['supported_form_factors']) && is_array($specs['supported_form_factors'])) {
            $badges[] = implode('/', $specs['supported_form_factors']);
        }
        if (! empty($specs['gpu_length_mm'])) {
            $badges[] = $specs['gpu_length_mm'] . ' mm';
        }
        if (! empty($specs['max_gpu_mm'])) {
            $badges[] = 'Max VGA ' . $specs['max_gpu_mm'] . ' mm';
        }
        if (! empty($specs['tdp_watt'])) {
            $badges[] = $specs['tdp_watt'] . 'W TDP';
        }
        if (! empty($specs['psu_watt'])) {
            $badges[] = $specs['psu_watt'] . 'W PSU';
        }

        return array_slice($badges, 0, 4);
    }

    public function whatsappInquiryMessage(string $productUrl): string
    {
        $negoText = $this->is_negotiable ? 'Bisa Nego' : 'Harga Nett';

        return implode("\n", [
            "Halo Admin Tokoku, saya ingin menanyakan ketersediaan barang berikut:",
            "- Nama Barang: {$this->name}",
            "- Kode SKU: {$this->sku}",
            "- Kondisi: {$this->conditionLabel()}",
            "- Harga Tayang: {$this->formattedPrice()} ({$negoText})",
            "- Tautan: {$productUrl}",
            "",
            "Apakah unit ini masih siap diambil di toko / COD?",
        ]);
    }
}
