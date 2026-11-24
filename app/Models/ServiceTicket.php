<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceTicket extends Model
{
    protected $fillable = [
        'ticket_number',
        'customer_name',
        'customer_phone',
        'customer_address',
        'device_type',
        'device_brand_model',
        'service_package_id',
        'complaint_summary',
        'visit_type',
        'preferred_date',
        'technician_id',
        'status',
        'diagnosis_notes',
        'temp_before_c',
        'temp_after_c',
        'service_fee',
        'parts_total',
        'grand_total',
        'warranty_until',
        'is_sample_data',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'warranty_until' => 'date',
            'temp_before_c' => 'integer',
            'temp_after_c' => 'integer',
            'service_fee' => 'integer',
            'parts_total' => 'integer',
            'grand_total' => 'integer',
            'is_sample_data' => 'boolean',
        ];
    }

    public const STATUSES = [
        'booked' => 'Booking Terjadwal',
        'diagnosing' => 'Pengecekan & Diagnosa',
        'waiting_approval' => 'Menunggu Persetujuan Biaya',
        'in_progress' => 'Sedang Dikerjakan Teknisi',
        'testing_qc' => 'Uji Suhu & Stress Test (QC)',
        'ready' => 'Selesai & Siap Diambil',
        'completed' => 'Sudah Diambil (Garansi Aktif)',
        'cancelled' => 'Dibatalkan',
    ];

    public const PROGRESS_ORDER = [
        'booked' => 1,
        'diagnosing' => 2,
        'waiting_approval' => 3,
        'in_progress' => 4,
        'testing_qc' => 5,
        'ready' => 6,
        'completed' => 7,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = static::generateTicketNumber();
            }
            $ticket->grand_total = (int) $ticket->service_fee + (int) $ticket->parts_total;
        });

        static::saving(function (self $ticket) {
            $ticket->grand_total = (int) $ticket->service_fee + (int) $ticket->parts_total;
        });
    }

    public static function generateTicketNumber(): string
    {
        $prefix = 'SRV-' . now()->format('Ym') . '-';
        $latest = static::query()
            ->where('ticket_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('ticket_number');

        $nextSequence = 1;
        if ($latest && preg_match('/-(\d{4})$/', $latest, $matches)) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }

    public function servicePackage(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ServiceTicketLog::class)->orderByDesc('created_at');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(ServiceTicketPart::class);
    }

    public function recalculateTotals(): void
    {
        $partsSum = (int) $this->parts()->sum('subtotal');
        $this->parts_total = $partsSum;
        $this->grand_total = (int) $this->service_fee + $partsSum;
        $this->saveQuietly();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function phoneLastFour(): string
    {
        $digits = preg_replace('/\D+/', '', $this->customer_phone);

        return substr($digits, -4) ?: '';
    }

    public function matchesPhoneLastFour(string $input): bool
    {
        $cleanInput = preg_replace('/\D+/', '', $input);

        return strlen($cleanInput) === 4 && $this->phoneLastFour() === $cleanInput;
    }
}
