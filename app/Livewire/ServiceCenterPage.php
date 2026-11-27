<?php

namespace App\Livewire;

use App\Models\ServicePackage;
use App\Models\ServiceTicket;
use App\Models\ServiceTicketLog;
use App\Models\StoreSetting;
use App\Services\ActivityLogger;
use App\Services\QrCodeSvgService;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Url;
use Livewire\Component;

class ServiceCenterPage extends Component
{
    #[Url(as: 'tab', except: 'booking')]
    public string $activeTab = 'booking';

    #[Url(as: 'tiket', except: '')]
    public string $ticketInput = '';

    #[Url(as: 'pin', except: '')]
    public string $pinInput = '';

    public ?ServiceTicket $trackedTicket = null;
    public ?string $trackingError = null;
    public string $approvalNote = '';
    public ?string $approvalSuccessMessage = null;

    public string $customerName = '';
    public string $customerPhone = '';
    public string $customerAddress = '';
    public string $deviceType = 'PC Rakitan Desktop';
    public string $deviceBrandModel = '';
    public ?int $servicePackageId = null;
    public string $visitType = 'walk_in';
    public string $preferredDate = '';
    public string $complaintSummary = '';
    public string $websiteUrl = '';

    public ?ServiceTicket $createdTicket = null;

    public function mount(): void
    {
        $this->preferredDate = now()->addDay()->toDateString();

        if ($this->ticketInput !== '' || $this->pinInput !== '') {
            $this->activeTab = 'tracking';
            if ($this->ticketInput !== '' && $this->pinInput !== '') {
                $this->lookupTicket();
            }
        }
    }

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['booking', 'tracking'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function fillDemoTicket(): void
    {
        $this->activeTab = 'tracking';
        $this->ticketInput = 'SRV-202611-0001';
        $this->pinInput = '1234';
        $this->lookupTicket();
    }

    public function lookupTicket(): void
    {
        $this->activeTab = 'tracking';
        $this->trackingError = null;
        $this->trackedTicket = null;

        $code = strtoupper(trim($this->ticketInput));
        $pin = preg_replace('/\D+/', '', $this->pinInput);

        if ($code === '' || strlen($pin) !== 4) {
            $this->trackingError = 'Masukkan nomor tiket lengkap (contoh: SRV-202611-0001) beserta tepat 4 digit terakhir nomor WhatsApp saat mendaftar.';

            return;
        }

        $rateKey = 'tokoku:lookup-ticket:' . request()->ip() . ':' . $code;
        if (RateLimiter::tooManyAttempts($rateKey, 10)) {
            $seconds = RateLimiter::availableIn($rateKey);
            $this->trackingError = "Terlalu banyak percobaan verifikasi PIN untuk tiket ini. Silakan tunggu {$seconds} detik lalu coba lagi.";

            return;
        }

        $ticket = ServiceTicket::query()
            ->with(['servicePackage', 'technician', 'parts', 'logs' => fn ($q) => $q->where('is_public', true)])
            ->where('ticket_number', $code)
            ->first();

        if (! $ticket || ! $ticket->matchesPhoneLastFour($pin)) {
            RateLimiter::hit($rateKey, 60);
            $this->trackingError = 'Data tiket tidak ditemukan atau 4 digit terakhir nomor WhatsApp tidak sesuai dengan catatan pendaftaran.';

            return;
        }

        RateLimiter::clear($rateKey);
        $this->trackedTicket = $ticket;
        $this->approvalSuccessMessage = null;
    }

    public function approveTrackedTicketCost(): void
    {
        if (! $this->trackedTicket) {
            return;
        }

        $rateKey = 'tokoku:approve-cost:' . request()->ip() . ':' . $this->trackedTicket->id;
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $seconds = RateLimiter::availableIn($rateKey);
            $this->trackingError = "Terlalu banyak percobaan konfirmasi. Tunggu {$seconds} detik.";

            return;
        }
        RateLimiter::hit($rateKey, 60);

        $this->validate([
            'approvalNote' => ['nullable', 'string', 'max:200'],
        ]);

        $ticketId = $this->trackedTicket->id;
        $noteText = trim($this->approvalNote);
        $approved = false;

        DB::transaction(function () use ($ticketId, $noteText, &$approved) {
            $fresh = ServiceTicket::query()->lockForUpdate()->find($ticketId);
            if (! $fresh || $fresh->status !== 'waiting_approval') {
                return;
            }

            $fresh->update([
                'status' => 'in_progress',
                'customer_approved_at' => now(),
                'customer_approval_note' => $noteText !== '' ? $noteText : null,
            ]);

            $totalText = 'Rp ' . number_format((int) $fresh->grand_total, 0, ',', '.');
            $logMsg = "Pelanggan menyetujui estimasi total biaya {$totalText} secara daring melalui halaman lacak resi.";
            if ($noteText !== '') {
                $logMsg .= " Catatan pelanggan: {$noteText}";
            }

            ServiceTicketLog::query()->create([
                'service_ticket_id' => $fresh->id,
                'status' => 'in_progress',
                'note' => $logMsg,
                'is_public' => true,
            ]);

            ActivityLogger::record(
                action: 'ticket_customer_approved',
                subjectType: 'ServiceTicket',
                subjectId: $fresh->id,
                subjectLabel: "{$fresh->ticket_number} ({$fresh->device_brand_model})",
                description: "Pelanggan {$fresh->customer_name} menyetujui biaya {$totalText} via halaman cek resi.",
                before: ['status' => 'waiting_approval'],
                after: ['status' => 'in_progress', 'grand_total' => $fresh->grand_total],
                customActorName: "Pelanggan ({$fresh->customer_name})",
            );

            $approved = true;
        });

        if (! $approved) {
            $this->trackingError = 'Status tiket sudah berubah dan tidak lagi menunggu persetujuan biaya.';

            return;
        }

        $this->approvalNote = '';
        $this->trackedTicket = ServiceTicket::query()
            ->with(['servicePackage', 'technician', 'parts', 'logs' => fn ($q) => $q->where('is_public', true)])
            ->find($ticketId);
        $this->approvalSuccessMessage = 'Persetujuan biaya tercatat! Tim teknisi kami akan segera melanjutkan pengerjaan perangkat Anda.';
    }

    public function choosePackage(int $packageId): void
    {
        $this->activeTab = 'booking';
        $this->servicePackageId = $packageId;
    }

    public function submitBooking(): void
    {
        if (trim($this->websiteUrl) !== '') {
            $this->reset(['customerName', 'customerPhone', 'customerAddress', 'deviceBrandModel', 'complaintSummary', 'websiteUrl']);

            return;
        }

        $bookingRateKey = 'tokoku:booking:' . request()->ip();
        if (RateLimiter::tooManyAttempts($bookingRateKey, 3)) {
            $seconds = RateLimiter::availableIn($bookingRateKey);
            $minutes = max(1, (int) ceil($seconds / 60));
            $this->addError('customerName', "Batas pendaftaran tercapai (maksimal 3 jadwal setiap 10 menit). Silakan tunggu sekitar {$minutes} menit atau hubungi WhatsApp toko.");

            return;
        }

        $maxDate = now()->addDays(60)->toDateString();

        $validated = $this->validate([
            'customerName' => ['required', 'string', 'min:2', 'max:120'],
            'customerPhone' => [
                'required',
                'string',
                'max:30',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $digits = preg_replace('/\D+/', '', (string) $value) ?: '';
                    if (strlen($digits) < 9 || strlen($digits) > 16) {
                        $fail('Nomor WhatsApp harus berisi 9 sampai 16 angka aktif.');
                    }
                },
            ],
            'deviceType' => ['required', 'string', 'max:50'],
            'deviceBrandModel' => ['required', 'string', 'min:2', 'max:140'],
            'servicePackageId' => ['nullable', 'exists:service_packages,id'],
            'visitType' => ['required', 'in:walk_in,home_pickup'],
            'preferredDate' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . $maxDate],
            'customerAddress' => ['required_if:visitType,home_pickup', 'nullable', 'string', 'max:300'],
            'complaintSummary' => ['required', 'string', 'min:8', 'max:600'],
        ], [
            'customerName.required' => 'Nama pelanggan wajib diisi.',
            'customerPhone.required' => 'Nomor WhatsApp aktif wajib diisi.',
            'deviceBrandModel.required' => 'Tuliskan tipe laptop atau spesifikasi singkat PC Anda.',
            'preferredDate.after_or_equal' => 'Rencana tanggal kedatangan tidak boleh di masa lalu.',
            'preferredDate.before_or_equal' => 'Rencana jadwal maksimal 60 hari ke depan.',
            'customerAddress.required_if' => 'Alamat lengkap wajib diisi bila memilih jemput unit ke lokasi.',
            'complaintSummary.required' => 'Jelaskan gejala kerusakan atau perawatan yang dibutuhkan.',
            'complaintSummary.min' => 'Jelaskan keluhan minimal 8 karakter.',
        ]);

        $pkg = $validated['servicePackageId']
            ? ServicePackage::query()->find($validated['servicePackageId'])
            : null;

        $baseFee = $pkg ? (int) $pkg->base_price : 0;

        $ticket = ServiceTicket::createBookingWithInitialLog([
            'customer_name' => trim($validated['customerName']),
            'customer_phone' => trim($validated['customerPhone']),
            'customer_address' => trim((string) ($validated['customerAddress'] ?? '')) ?: null,
            'device_type' => $validated['deviceType'],
            'device_brand_model' => trim($validated['deviceBrandModel']),
            'service_package_id' => $pkg?->id,
            'complaint_summary' => trim($validated['complaintSummary']),
            'visit_type' => $validated['visitType'],
            'preferred_date' => $validated['preferredDate'],
            'status' => 'booked',
            'service_fee' => $baseFee,
            'parts_total' => 0,
            'grand_total' => $baseFee,
        ], 'Jadwal servis didaftarkan melalui formulir website oleh pelanggan.');

        RateLimiter::hit($bookingRateKey, 600);

        $this->createdTicket = $ticket;
        $this->ticketInput = $ticket->ticket_number;
        $this->pinInput = $ticket->phoneLastFour();
        $this->lookupTicket();

        $this->reset(['customerName', 'customerPhone', 'customerAddress', 'deviceBrandModel', 'complaintSummary', 'websiteUrl']);
    }

    public function render(QrCodeSvgService $qr)
    {
        $store = StoreSetting::current();
        $packages = ServicePackage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $ticketQrSvg = null;
        if ($this->trackedTicket) {
            $trackUrl = route('service.index', [
                'tiket' => $this->trackedTicket->ticket_number,
                'pin' => $this->trackedTicket->phoneLastFour(),
            ]);
            $ticketQrSvg = $qr->generateInlineSvg($trackUrl, 86, 1);
        }

        return view('livewire.service-center-page', [
            'store' => $store,
            'packages' => $packages,
            'ticketQrSvg' => $ticketQrSvg,
        ])
            ->title('Layanan Servis PC & Lacak Resi - ' . $store->store_name)
            ->layoutData([
                'metaDescription' => "Daftar jadwal perawatan suhu atau perbaikan PC/laptop di {$store->store_name}, dan pantau progres pengerjaan teknisi 7 tahap cukup dengan nomor tiket.",
            ]);
    }
}
