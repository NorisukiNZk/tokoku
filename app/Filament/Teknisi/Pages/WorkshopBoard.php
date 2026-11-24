<?php

namespace App\Filament\Teknisi\Pages;

use App\Models\ServiceTicket;
use App\Models\ServiceTicketLog;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class WorkshopBoard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationLabel = 'Papan Antrean Bengkel';
    protected static ?string $title = 'Papan Antrean Bengkel';
    protected static ?int $navigationSort = -1;
    protected static string $view = 'filament.pages.workshop-board';

    public string $search = '';
    public string $scope = 'all';

    public ?int $editingTicketId = null;
    public string $editStatus = 'diagnosing';
    public ?int $editTempBefore = null;
    public ?int $editTempAfter = null;
    public int $editServiceFee = 0;
    public string $editDiagnosisNotes = '';
    public string $editPublicLogNote = '';

    public static function getNavigationGroup(): ?string
    {
        return Filament::getCurrentPanel()?->getId() === 'admin'
            ? 'Bengkel & Servis PC'
            : null;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ServiceTicket::query()
            ->whereIn('status', ['booked', 'diagnosing', 'waiting_approval', 'in_progress', 'testing_qc', 'ready'])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $waiting = ServiceTicket::query()->where('status', 'waiting_approval')->count();

        return $waiting > 0 ? 'warning' : 'primary';
    }

    public static function getSlug(): string
    {
        return 'papan-bengkel';
    }

    public static function routes(Panel $panel): void
    {
        $path = $panel->getId() === 'teknisi' ? '/' : '/papan-bengkel';

        Route::get($path, static::class)
            ->middleware(static::getRouteMiddleware($panel))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
            ->name(static::getSlug());
    }

    public function setScope(string $scope): void
    {
        if (in_array($scope, ['all', 'mine', 'unassigned', 'pickup'], true)) {
            $this->scope = $scope;
        }
    }

    public function claimTicket(int $ticketId): void
    {
        $ticket = ServiceTicket::query()->find($ticketId);
        if (! $ticket) {
            return;
        }

        $nextStatus = $ticket->status === 'booked' ? 'diagnosing' : $ticket->status;
        $ticket->update([
            'technician_id' => Auth::id(),
            'status' => $nextStatus,
        ]);

        ServiceTicketLog::query()->create([
            'service_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'status' => $nextStatus,
            'note' => 'Unit mulai ditangani dan dicek oleh teknisi ' . (Auth::user()?->name ?? 'Bengkel') . '.',
            'is_public' => true,
        ]);

        Notification::make()
            ->title("Tiket {$ticket->ticket_number} diambil dan masuk tahap diagnosa.")
            ->success()
            ->send();
    }

    public function advanceStatus(int $ticketId, string $nextStatus, ?string $defaultNote = null): void
    {
        $ticket = ServiceTicket::query()->find($ticketId);
        if (! $ticket || ! array_key_exists($nextStatus, ServiceTicket::STATUSES)) {
            return;
        }

        $updates = ['status' => $nextStatus];
        if (! $ticket->technician_id && Auth::id()) {
            $updates['technician_id'] = Auth::id();
        }
        if ($nextStatus === 'completed' && ! $ticket->warranty_until) {
            $updates['warranty_until'] = now()->addDays(14)->toDateString();
        }

        $ticket->update($updates);

        $note = $defaultNote ?? match ($nextStatus) {
            'in_progress' => 'Pengerjaan perbaikan / perawatan dimulai oleh teknisi.',
            'testing_qc' => 'Pengerjaan selesai, memasuki tahap pengujian beban dan suhu (QC).',
            'ready' => 'Unit lulus uji QC dan siap diambil atau dikirim ke pelanggan.',
            'completed' => 'Unit telah diserahkan ke pelanggan. Garansi servis aktif.',
            default => 'Status pengerjaan diperbarui menjadi ' . ServiceTicket::STATUSES[$nextStatus] . '.',
        };

        ServiceTicketLog::query()->create([
            'service_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'status' => $nextStatus,
            'note' => $note,
            'is_public' => true,
        ]);

        Notification::make()
            ->title("{$ticket->ticket_number}: " . ServiceTicket::STATUSES[$nextStatus])
            ->success()
            ->send();
    }

    public function openQuickWorkbench(int $ticketId): void
    {
        $ticket = ServiceTicket::query()->find($ticketId);
        if (! $ticket) {
            return;
        }

        $this->editingTicketId = $ticket->id;
        $this->editStatus = $ticket->status;
        $this->editTempBefore = $ticket->temp_before_c;
        $this->editTempAfter = $ticket->temp_after_c;
        $this->editServiceFee = (int) $ticket->service_fee;
        $this->editDiagnosisNotes = (string) ($ticket->diagnosis_notes ?? '');
        $this->editPublicLogNote = '';
    }

    public function closeQuickWorkbench(): void
    {
        $this->editingTicketId = null;
        $this->editPublicLogNote = '';
    }

    public function saveQuickWorkbench(): void
    {
        if (! $this->editingTicketId) {
            return;
        }

        $ticket = ServiceTicket::query()->find($this->editingTicketId);
        if (! $ticket) {
            $this->closeQuickWorkbench();

            return;
        }

        $previousStatus = $ticket->status;
        $updates = [
            'status' => $this->editStatus,
            'temp_before_c' => $this->editTempBefore ?: null,
            'temp_after_c' => $this->editTempAfter ?: null,
            'service_fee' => max(0, (int) $this->editServiceFee),
            'diagnosis_notes' => trim($this->editDiagnosisNotes) ?: null,
        ];

        if (! $ticket->technician_id && Auth::id()) {
            $updates['technician_id'] = Auth::id();
        }
        if ($this->editStatus === 'completed' && ! $ticket->warranty_until) {
            $updates['warranty_until'] = now()->addDays(14)->toDateString();
        }

        $ticket->update($updates);

        $logText = trim($this->editPublicLogNote);
        if ($logText !== '' || $previousStatus !== $this->editStatus) {
            ServiceTicketLog::query()->create([
                'service_ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'status' => $this->editStatus,
                'note' => $logText !== '' ? $logText : ('Status diperbarui ke ' . ServiceTicket::STATUSES[$this->editStatus] . '.'),
                'is_public' => true,
            ]);
        }

        Notification::make()
            ->title("Pembaruan tiket {$ticket->ticket_number} tersimpan.")
            ->success()
            ->send();

        $this->closeQuickWorkbench();
    }

    protected function getViewData(): array
    {
        $panelPath = Filament::getCurrentPanel()?->getPath() ?? 'teknisi';

        $query = ServiceTicket::query()
            ->with(['technician', 'servicePackage', 'parts'])
            ->whereIn('status', ['booked', 'diagnosing', 'waiting_approval', 'in_progress', 'testing_qc', 'ready']);

        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($sub) use ($term) {
                $sub->where('ticket_number', 'like', $term)
                    ->orWhere('customer_name', 'like', $term)
                    ->orWhere('customer_phone', 'like', $term)
                    ->orWhere('device_brand_model', 'like', $term)
                    ->orWhere('complaint_summary', 'like', $term);
            });
        }

        if ($this->scope === 'mine') {
            $query->where('technician_id', Auth::id());
        } elseif ($this->scope === 'unassigned') {
            $query->whereNull('technician_id');
        } elseif ($this->scope === 'pickup') {
            $query->where('visit_type', 'home_pickup');
        }

        $tickets = $query->orderByDesc('updated_at')->get();

        $lanes = [
            'intake' => [
                'title' => 'Masuk & Diagnosa',
                'empty' => 'Tidak ada unit baru yang menunggu pengecekan awal.',
                'items' => $tickets->whereIn('status', ['booked', 'diagnosing'])->values(),
            ],
            'approval' => [
                'title' => 'Tunggu Konfirmasi Biaya',
                'empty' => 'Tidak ada estimasi biaya yang sedang menunggu persetujuan pelanggan.',
                'items' => $tickets->where('status', 'waiting_approval')->values(),
            ],
            'working' => [
                'title' => 'Dikerjakan & Uji QC',
                'empty' => 'Meja kerja sedang kosong dari unit yang dikerjakan atau diuji suhu.',
                'items' => $tickets->whereIn('status', ['in_progress', 'testing_qc'])->values(),
            ],
            'ready' => [
                'title' => 'Siap Diambil',
                'empty' => 'Belum ada unit lulus QC yang menunggu pengambilan pelanggan.',
                'items' => $tickets->where('status', 'ready')->values(),
            ],
        ];

        $editingTicket = $this->editingTicketId
            ? ServiceTicket::query()->with('parts')->find($this->editingTicketId)
            : null;

        return [
            'panelPath' => $panelPath,
            'lanes' => $lanes,
            'totalActive' => $tickets->count(),
            'completedToday' => ServiceTicket::query()
                ->where('status', 'completed')
                ->whereDate('updated_at', today())
                ->count(),
            'editingTicket' => $editingTicket,
        ];
    }
}
