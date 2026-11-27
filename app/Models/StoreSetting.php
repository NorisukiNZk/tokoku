<?php

namespace App\Models;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class StoreSetting extends Model
{
    public const CACHE_KEY = 'tokoku.store_setting';

    protected $fillable = [
        'store_name',
        'tagline',
        'logo_path',
        'whatsapp_number',
        'workshop_address',
        'cod_areas',
        'operating_hours',
        'default_assembly_fee',
        'usd_to_idr_rate',
        'last_parts_sync_at',
        'is_placeholder_data',
    ];

    protected function casts(): array
    {
        return [
            'default_assembly_fee' => 'integer',
            'usd_to_idr_rate' => 'integer',
            'last_parts_sync_at' => 'datetime',
            'is_placeholder_data' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            static::flushCache();
        });

        static::updated(function (self $setting) {
            $tracked = ['store_name', 'whatsapp_number', 'workshop_address', 'default_assembly_fee', 'usd_to_idr_rate'];
            $changes = array_intersect_key($setting->getChanges(), array_flip($tracked));
            if (count($changes) === 0) {
                return;
            }

            $before = [];
            $after = [];
            foreach ($changes as $field => $newVal) {
                $before[$field] = $setting->getOriginal($field);
                $after[$field] = $newVal;
            }

            ActivityLogger::record(
                action: 'settings_updated',
                subjectType: 'StoreSetting',
                subjectId: $setting->id,
                subjectLabel: $setting->store_name,
                description: 'Profil/tarif toko diperbarui (' . implode(', ', array_keys($changes)) . ').',
                before: $before,
                after: $after,
            );
        });

        static::deleted(function () {
            static::flushCache();
        });
    }

    public static function flushCache(): void
    {
        app()->forgetInstance(self::CACHE_KEY);
        Cache::forget(self::CACHE_KEY);
    }

    public static function current(): self
    {
        if (app()->bound(self::CACHE_KEY)) {
            return app(self::CACHE_KEY);
        }

        if (! Schema::hasTable('store_settings')) {
            return new self([
                'store_name' => 'Tokoku',
                'whatsapp_number' => '6280000000000',
                'workshop_address' => '[ALAMAT WORKSHOP: Ubah di Pengaturan Toko]',
                'cod_areas' => 'Ambil di workshop atau COD lokal',
                'operating_hours' => 'Senin - Sabtu, 09:00 - 18:00',
                'default_assembly_fee' => 150000,
                'is_placeholder_data' => true,
            ]);
        }

        $setting = Cache::remember(self::CACHE_KEY, 3600, function () {
            return static::query()->first() ?? static::query()->create([
                'store_name' => 'Tokoku',
                'tagline' => 'Jual Beli Part PC Baru & Bekas, Bengkel Servis, dan Rakit PC',
                'whatsapp_number' => '6280000000000',
                'workshop_address' => '[ALAMAT WORKSHOP: Ubah di Pengaturan Toko]',
                'cod_areas' => 'Ambil di workshop atau COD lokal sesuai kesepakatan',
                'operating_hours' => 'Senin - Sabtu, 09:00 - 18:00',
                'default_assembly_fee' => 150000,
                'is_placeholder_data' => true,
            ]);
        });

        app()->instance(self::CACHE_KEY, $setting);

        return $setting;
    }

    public function whatsappUrl(string $message): string
    {
        $digits = preg_replace('/\D+/', '', $this->whatsapp_number ?? '') ?: '6280000000000';
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($message);
    }
}
