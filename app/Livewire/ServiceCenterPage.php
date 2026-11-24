<?php

namespace App\Livewire;

use App\Models\ServicePackage;
use App\Models\ServiceTicket;
use App\Models\ServiceTicketLog;
use App\Models\StoreSetting;
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

    public string $customerName = '';
    public string $customerPhone = '';
    public string $customerAddress = '';
    public string $deviceType = 'PC Rakitan Desktop';
    public string $deviceBrandModel = '';
    public ?int $servicePackageId = null;
    public string $visitType = 'walk_in';
    public string $preferredDate = '';
    public string $complaintSummary = '';

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

        $ticket = ServiceTicket::query()
            ->with(['servicePackage', 'technician', 'parts', 'logs' => fn ($q) => $q->where('is_public', true)])
            ->where('ticket_number', $code)
            ->first();

        if (! $ticket || ! $ticket->matchesPhoneLastFour($pin)) {
            $this->trackingError = 'Data tiket tidak ditemukan atau 4 digit terakhir nomor WhatsApp tidak sesuai dengan catatan pendaftaran.';

            return;
        }

        $this->trackedTicket = $ticket;
    }

    public function choosePackage(int $packageId): void
    {
        $this->activeTab = 'booking';
        $this->servicePackageId = $packageId;
    }

    public function submitBooking(): void
    {
        $validated = $this->validate([
            'customerName' => ['required', 'string', 'min:2', 'max:120'],
            'customerPhone' => ['required', 'string', 'min:8', 'max:30'],
            'deviceType' => ['required', 'string', 'max:50'],
            'deviceBrandModel' => ['required', 'string', 'min:2', 'max:140'],
            'servicePackageId' => ['nullable', 'exists:service_packages,id'],
            'visitType' => ['required', 'in:walk_in,home_pickup'],
            'preferredDate' => ['required', 'date'],
            'customerAddress' => ['required_if:visitType,home_pickup', 'nullable', 'string', 'max:300'],
            'complaintSummary' => ['required', 'string', 'min:8', 'max:600'],
        ], [
            'customerName.required' => 'Nama pelanggan wajib diisi.',
            'customerPhone.required' => 'Nomor WhatsApp aktif wajib diisi minimal 8 digit.',
            'customerPhone.min' => 'Nomor WhatsApp terlalu pendek (minimal 8 digit agar 4 digit akhir dapat dipakai cek resi).',
            'deviceBrandModel.required' => 'Tuliskan tipe laptop atau spesifikasi singkat PC Anda.',
            'customerAddress.required_if' => 'Alamat lengkap wajib diisi bila memilih jemput unit ke lokasi.',
            'complaintSummary.required' => 'Jelaskan gejala kerusakan atau perawatan yang dibutuhkan.',
            'complaintSummary.min' => 'Jelaskan keluhan minimal 8 karakter.',
        ]);

        $pkg = $validated['servicePackageId']
            ? ServicePackage::query()->find($validated['servicePackageId'])
            : null;

        $baseFee = $pkg ? (int) $pkg->base_price : 0;

        $ticket = ServiceTicket::query()->create([
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
        ]);

        ServiceTicketLog::query()->create([
            'service_ticket_id' => $ticket->id,
            'status' => 'booked',
            'note' => 'Jadwal servis didaftarkan melalui formulir website oleh pelanggan.',
            'is_public' => true,
        ]);

        $this->createdTicket = $ticket;
        $this->ticketInput = $ticket->ticket_number;
        $this->pinInput = $ticket->phoneLastFour();
        $this->lookupTicket();

        $this->reset(['customerName', 'customerPhone', 'customerAddress', 'deviceBrandModel', 'complaintSummary']);
    }

    public function render()
    {
        $store = StoreSetting::current();
        $packages = ServicePackage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('livewire.service-center-page', [
            'store' => $store,
            'packages' => $packages,
        ])->title('Layanan Servis PC & Lacak Resi - ' . $store->store_name);
    }
}
