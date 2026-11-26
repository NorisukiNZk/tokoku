<?php

namespace App\Models;

use App\Services\SparepartInventoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
                $ticket->ticket_number = static::generateTicketNumber(DB::transactionLevel() > 0);
            }
            $ticket->grand_total = (int) $ticket->service_fee + (int) $ticket->parts_total;
        });

        static::saving(function (self $ticket) {
            $ticket->grand_total = (int) $ticket->service_fee + (int) $ticket->parts_total;
        });

        static::saved(function (self $ticket) {
            static::flushNavigationCache();
            if ($ticket->wasChanged('status') && class_exists(SparepartInventoryService::class)) {
                app(SparepartInventoryService::class)->syncTicketPartsForStatus($ticket);
            }
        });

        static::deleted(function () {
            static::flushNavigationCache();
        });
    }

    public static function flushNavigationCache(): void
    {
        Cache::forget('tokoku.nav.tickets_active');
        Cache::forget('tokoku.nav.tickets_waiting');
    }

    public static function generateTicketNumber(bool $lock = false): string
    {
        $prefix = 'SRV-' . now()->format('Ym') . '-';
        $latest = static::query()
            ->where('ticket_number', 'like', $prefix . '%')
            ->when($lock, fn ($query) => $query->lockForUpdate())
            ->orderByDesc('id')
            ->value('ticket_number');

        $nextSequence = 1;
        if ($latest && preg_match('/-(\d{4})$/', $latest, $matches)) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a service ticket and its initial public log atomically with retry on unique ticket_number collision.
     *
     * @param array<string, mixed> $attributes
     */
    public static function createBookingWithInitialLog(array $attributes, string $initialNote): self
    {
        $attempts = 0;

        while (true) {
            $attempts++;
            try {
                return DB::transaction(function () use ($attributes, $initialNote) {
                    $payload = $attributes;
                    if (empty($payload['ticket_number'])) {
                        $payload['ticket_number'] = static::generateTicketNumber(true);
                    }

                    $ticket = static::query()->create($payload);

                    ServiceTicketLog::query()->create([
                        'service_ticket_id' => $ticket->id,
                        'status' => $ticket->status ?? 'booked',
                        'note' => $initialNote,
                        'is_public' => true,
                    ]);

                    return $ticket;
                });
            } catch (QueryException $e) {
                $isUniqueCollision = str_contains(strtolower($e->getMessage()), 'unique')
                    || str_contains(strtolower($e->getMessage()), 'duplicate');

                if ($isUniqueCollision && $attempts < 3 && empty($attributes['ticket_number'])) {
                    continue;
                }

                throw $e;
            }
        }
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

    public function customerWhatsappUrl(?string $customMessage = null): string
    {
        $digits = preg_replace('/\D+/', '', $this->customer_phone ?? '') ?: '';
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        $trackingUrl = url('/servis?tiket=' . $this->ticket_number . '&pin=' . $this->phoneLastFour());
        $totalFormatted = 'Rp ' . number_format((int) $this->grand_total, 0, ',', '.');

        $message = $customMessage ?? match ($this->status) {
            'waiting_approval' => "Halo {$this->customer_name}, hasil diagnosa unit {$this->device_brand_model} (Tiket {$this->ticket_number}): {$this->diagnosis_notes}\nEstimasi total biaya: {$totalFormatted}.\nMohon konfirmasi apakah pengerjaan dapat kami lanjutkan. Lacak detail: {$trackingUrl}",
            'ready' => "Halo {$this->customer_name}, perangkat {$this->device_brand_model} (Tiket {$this->ticket_number}) sudah selesai uji QC dan siap diambil.\nTotal biaya: {$totalFormatted}.\nRincian lengkap: {$trackingUrl}",
            default => "Halo {$this->customer_name}, pembaruan tiket servis {$this->ticket_number} ({$this->device_brand_model}): status saat ini {$this->statusLabel()}.\nPantau progres: {$trackingUrl}",
        };

        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($message);
    }
}
