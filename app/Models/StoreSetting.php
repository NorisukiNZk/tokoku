<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class StoreSetting extends Model
{
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

    public static function current(): self
    {
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
