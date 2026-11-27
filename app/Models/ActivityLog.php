<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    public const ACTIONS = [
        'product_updated' => 'Perubahan Produk / Stok',
        'product_deleted' => 'Hapus Produk',
        'ticket_updated' => 'Perubahan Tiket Servis',
        'ticket_customer_approved' => 'Persetujuan Biaya Pelanggan',
        'settings_updated' => 'Pengaturan Toko',
    ];

    protected $fillable = [
        'user_id',
        'actor_name',
        'action',
        'subject_type',
        'subject_id',
        'subject_label',
        'description',
        'before_values',
        'after_values',
        'ip_address',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'before_values' => 'array',
            'after_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
    }
}
