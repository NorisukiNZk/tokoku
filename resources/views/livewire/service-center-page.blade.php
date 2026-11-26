<div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-5 sm:space-y-6">
    <div class="surface-ink rounded-2xl p-4 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
        <div class="space-y-1.5">
            <span class="inline-block px-2.5 py-0.5 rounded-md bg-[#FB923C] text-[#0F172A] font-display font-bold text-xs">
                Workshop &amp; Lacak Resi Transparan
            </span>
            <h1 class="font-display text-xl sm:text-3xl font-extrabold tracking-tight">Layanan Servis &amp; Perawatan PC</h1>
            <p class="text-xs sm:text-sm ink-muted max-w-xl">
                Pilih paket perawatan atau perbaikan di bawah ini, atau lacak perkembangan suhu dan pengerjaan unit yang sedang dititipkan di bengkel kami.
            </p>
        </div>

        <div role="tablist" aria-label="Pilihan Layanan Servis" class="surface-ink-card p-1.5 rounded-xl w-full md:w-auto grid grid-cols-2 md:inline-flex gap-1.5 shrink-0">
            <button
                type="button"
                role="tab"
                aria-selected="{{ $activeTab === 'booking' ? 'true' : 'false' }}"
                wire:click="switchTab('booking')"
                class="min-h-11 px-3 sm:px-4 rounded-lg text-xs sm:text-sm text-center cursor-pointer transition-colors {{ $activeTab === 'booking' ? 'btn-ink-accent' : 'ink-muted hover:text-white font-medium' }}"
            >
                Booking Servis
            </button>
            <button
                type="button"
                role="tab"
                aria-selected="{{ $activeTab === 'tracking' ? 'true' : 'false' }}"
                wire:click="switchTab('tracking')"
                class="min-h-11 px-3 sm:px-4 rounded-lg text-xs sm:text-sm text-center cursor-pointer transition-colors {{ $activeTab === 'tracking' ? 'btn-ink-accent' : 'ink-muted hover:text-white font-medium' }}"
            >
                Lacak Status Resi
            </button>
        </div>
    </div>

    @if($createdTicket && $activeTab === 'tracking')
        <div class="badge-ok rounded-xl p-5 space-y-2 no-print" role="status">
            <p class="font-bold text-base">
                Pendaftaran berhasil! Nomor Tiket Anda:
                <span class="font-mono">{{ $createdTicket->ticket_number }}</span>
            </p>
            <p class="text-sm">
                Catat nomor tiket tersebut beserta 4 digit akhir WhatsApp Anda (<strong>{{ $createdTicket->phoneLastFour() }}</strong>) untuk memantau progres kapan saja.
            </p>
            <div class="pt-1 flex flex-wrap gap-2">
                <a
                    href="{{ $store->whatsappUrl("Halo Admin {$store->store_name}, saya sudah mendaftar jadwal servis:\nNo Tiket: {$createdTicket->ticket_number}\nPerangkat: {$createdTicket->device_brand_model}\nJadwal: " . optional($createdTicket->preferred_date)->format('d M Y')) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn-accent min-h-11 px-4 rounded-lg text-sm inline-flex items-center"
                >
                    Konfirmasi Jadwal via WhatsApp
                </a>
                <button
                    type="button"
                    @click="$store.tokoku.copyText(@js($createdTicket->ticket_number), 'Nomor tiket ' + @js($createdTicket->ticket_number) + ' disalin')"
                    class="btn-secondary min-h-11 px-4 rounded-lg text-sm cursor-pointer"
                >
                    Salin No. Tiket
                </button>
            </div>
        </div>
    @endif

    @if($activeTab === 'booking')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
            <section aria-labelledby="judul-paket-servis" class="lg:col-span-5 space-y-3.5">
                <div class="space-y-1">
                    <h2 id="judul-paket-servis" class="text-lg font-bold">Pilih Paket Layanan</h2>
                    <p class="text-xs sm:text-sm text-muted-ink">
                        Klik salah satu paket untuk memasukkannya ke formulir pendaftaran. Bila ada komponen rusak yang perlu diganti, biayanya selalu kami konfirmasikan lebih dulu.
                    </p>
                </div>

                @foreach($packages as $pkg)
                    <div class="surface-card rounded-xl p-4 sm:p-5 space-y-3 {{ $servicePackageId === $pkg->id ? 'ring-2 ring-[var(--accent)]' : '' }}">
                        <div class="flex items-center justify-between gap-2 text-xs text-muted-ink">
                            <span class="badge-accent-soft px-2.5 py-0.5 rounded-md font-semibold">{{ $pkg->category_label }}</span>
                            <span>Estimasi {{ $pkg->estimated_duration }}</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-base">{{ $pkg->name }}</h3>
                            <p class="text-xs sm:text-sm text-muted-ink mt-1">{{ $pkg->description }}</p>
                        </div>
                        <div class="flex items-center justify-between gap-3 pt-2 border-t border-[var(--border-default)]">
                            <span class="font-bold text-base sm:text-lg tabular-nums">{{ $pkg->formattedBasePrice() }}</span>
                            <button
                                type="button"
                                wire:click="choosePackage({{ $pkg->id }})"
                                @click="if (window.innerWidth < 1024) document.getElementById('form-booking-servis')?.scrollIntoView({ behavior: 'smooth' })"
                                class="{{ $servicePackageId === $pkg->id ? 'btn-accent' : 'btn-secondary' }} min-h-11 px-3.5 rounded-lg text-xs sm:text-sm cursor-pointer"
                            >
                                {{ $servicePackageId === $pkg->id ? 'Paket Dipilih' : 'Pilih Paket' }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </section>

            <section id="form-booking-servis" aria-labelledby="judul-booking" class="lg:col-span-7 surface-card rounded-xl p-5 sm:p-7 space-y-5">
                <div class="space-y-1">
                    <h2 id="judul-booking" class="text-lg sm:text-xl font-bold">Isi Data Kedatangan / Penjemputan</h2>
                    <p class="text-sm text-muted-ink">
                        Lengkapi formulir singkat ini agar tim teknisi kami dapat menyiapkan slot pengerjaan dan menerbitkan nomor resi servis Anda.
                    </p>
                </div>

                <form wire:submit="submitBooking" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sr-only" aria-hidden="true">
                        <label for="booking-website-hp">Jangan isi kolom ini</label>
                        <input id="booking-website-hp" type="text" wire:model="websiteUrl" tabindex="-1" autocomplete="off">
                    </div>
                    <div>
                        <label for="booking-nama" class="block text-xs font-semibold mb-1.5">Nama Lengkap</label>
                        <input id="booking-nama" type="text" wire:model="customerName" placeholder="Nama pemilik perangkat" class="input-field w-full min-h-11 px-3.5 rounded-lg text-sm">
                        @error('customerName') <p class="text-xs text-[var(--status-err-fg)] font-semibold mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="booking-wa" class="block text-xs font-semibold mb-1.5">Nomor WhatsApp Aktif</label>
                        <input id="booking-wa" type="tel" wire:model="customerPhone" placeholder="0812xxxxxxxx" class="input-field w-full min-h-11 px-3.5 rounded-lg text-sm">
                        @error('customerPhone') <p class="text-xs text-[var(--status-err-fg)] font-semibold mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="booking-jenis" class="block text-xs font-semibold mb-1.5">Jenis Perangkat</label>
                        <select id="booking-jenis" wire:model="deviceType" class="input-field w-full min-h-11 px-3 rounded-lg text-sm">
                            <option value="PC Rakitan Desktop">PC Rakitan Desktop</option>
                            <option value="Laptop Gaming / Kerja">Laptop Gaming / Kerja</option>
                            <option value="PC Kantor / All-in-One">PC Kantor / All-in-One</option>
                            <option value="Komponen Lepasan (VGA/Mobo)">Komponen Lepasan (VGA/Mobo)</option>
                        </select>
                    </div>

                    <div>
                        <label for="booking-tipe" class="block text-xs font-semibold mb-1.5">Merek / Tipe Perangkat</label>
                        <input id="booking-tipe" type="text" wire:model="deviceBrandModel" placeholder="Contoh: ASUS TUF A15 / PC Core i5" class="input-field w-full min-h-11 px-3.5 rounded-lg text-sm">
                        @error('deviceBrandModel') <p class="text-xs text-[var(--status-err-fg)] font-semibold mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="booking-paket" class="block text-xs font-semibold mb-1.5">Paket yang Diinginkan</label>
                        <select id="booking-paket" wire:model="servicePackageId" class="input-field w-full min-h-11 px-3 rounded-lg text-sm">
                            <option value="">Konsultasi / Cek Kerusakan Lebih Dulu</option>
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}">{{ $pkg->name }} ({{ $pkg->formattedBasePrice() }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="booking-metode" class="block text-xs font-semibold mb-1.5">Cara Penyerahan Unit</label>
                        <select id="booking-metode" wire:model.live="visitType" class="input-field w-full min-h-11 px-3 rounded-lg text-sm">
                            <option value="walk_in">Saya antar ke toko</option>
                            <option value="home_pickup">Minta jemput ke alamat saya</option>
                        </select>
                    </div>

                    <div>
                        <label for="booking-tanggal" class="block text-xs font-semibold mb-1.5">Rencana Tanggal</label>
                        <input id="booking-tanggal" type="date" wire:model="preferredDate" class="input-field w-full min-h-11 px-3 rounded-lg text-sm">
                        @error('preferredDate') <p class="text-xs text-[var(--status-err-fg)] font-semibold mt-1">{{ $message }}</p> @enderror
                    </div>

                    @if($visitType === 'home_pickup')
                        <div class="sm:col-span-2">
                            <label for="booking-alamat" class="block text-xs font-semibold mb-1.5">Alamat Lengkap Penjemputan</label>
                            <textarea id="booking-alamat" rows="2" wire:model="customerAddress" placeholder="Tuliskan nama jalan, nomor rumah, dan patokan lokasi..." class="input-field w-full p-3.5 rounded-lg text-sm"></textarea>
                            @error('customerAddress') <p class="text-xs text-[var(--status-err-fg)] font-semibold mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="sm:col-span-2">
                        <label for="booking-keluhan" class="block text-xs font-semibold mb-1.5">Ceritakan Keluhan atau Kebutuhan Anda</label>
                        <textarea id="booking-keluhan" rows="3" wire:model="complaintSummary" placeholder="Misal: Laptop cepat panas dan kipas bising saat dipakai kerja, atau PC tidak mau tampil ke layar..." class="input-field w-full p-3.5 rounded-lg text-sm"></textarea>
                        @error('complaintSummary') <p class="text-xs text-[var(--status-err-fg)] font-semibold mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2 pt-2">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="submitBooking"
                            class="btn-accent w-full sm:w-auto min-h-11 px-6 rounded-lg text-sm cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="submitBooking">Daftar &amp; Dapatkan Nomor Tiket</span>
                            <span wire:loading wire:target="submitBooking">Mendaftarkan Jadwal...</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start no-print">
            <div class="lg:col-span-4 surface-card rounded-xl p-5 sm:p-6 space-y-4">
                <div class="space-y-1">
                    <h2 class="text-lg font-bold">Cek Nomor Tiket Servis</h2>
                    <p class="text-sm text-muted-ink">
                        Masukkan nomor tiket beserta 4 angka terakhir nomor WhatsApp Anda saat mendaftar.
                    </p>
                </div>

                <form wire:submit="lookupTicket" class="space-y-3.5">
                    <div>
                        <label for="input-nomor-tiket" class="block text-xs font-semibold mb-1.5">Nomor Tiket</label>
                        <input
                            id="input-nomor-tiket"
                            type="text"
                            wire:model="ticketInput"
                            placeholder="SRV-202611-0001"
                            class="input-field w-full min-h-11 px-3.5 rounded-lg text-sm font-mono"
                        >
                    </div>

                    <div>
                        <label for="input-pin-wa" class="block text-xs font-semibold mb-1.5">4 Digit Akhir Nomor WhatsApp</label>
                        <input
                            id="input-pin-wa"
                            type="text"
                            maxlength="4"
                            wire:model="pinInput"
                            placeholder="1234"
                            class="input-field w-full min-h-11 px-3.5 rounded-lg text-sm font-mono"
                        >
                    </div>

                    <div class="flex flex-col gap-2 pt-1">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="lookupTicket"
                            class="btn-accent w-full min-h-11 px-4 rounded-lg text-sm cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="lookupTicket">Tampilkan Status Servis</span>
                            <span wire:loading wire:target="lookupTicket">Mencari Resi...</span>
                        </button>
                        <button
                            type="button"
                            wire:click="fillDemoTicket"
                            class="btn-secondary w-full min-h-11 px-4 rounded-lg text-xs sm:text-sm cursor-pointer"
                        >
                            Lihat Contoh Hasil Lacak Resi
                        </button>
                    </div>
                </form>

                @if($trackingError)
                    <div class="badge-err rounded-xl p-3.5 text-xs sm:text-sm font-medium" role="alert">
                        {{ $trackingError }}
                    </div>
                @endif
            </div>

            <div class="lg:col-span-8">
                <div wire:loading.delay wire:target="lookupTicket,fillDemoTicket" class="surface-subtle rounded-xl p-5 text-sm font-medium mb-4" role="status">
                    Mencari data pengerjaan unit...
                </div>

                @if($trackedTicket)
                    <div class="surface-card rounded-xl p-5 sm:p-7 space-y-6">
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-[var(--border-default)] pb-4">
                            <div class="space-y-1">
                                @if($trackedTicket->is_sample_data)
                                    <span class="badge-accent-soft inline-block px-2.5 py-0.5 rounded-md text-xs font-semibold">
                                        Contoh Data Simulasi
                                    </span>
                                @endif
                                <p class="font-mono text-lg font-bold">{{ $trackedTicket->ticket_number }}</p>
                                <p class="text-base font-semibold">{{ $trackedTicket->device_type }} &middot; {{ $trackedTicket->device_brand_model }}</p>
                                <p class="text-xs text-muted-ink">Pemilik: {{ $trackedTicket->customer_name }}</p>
                            </div>
                            <span class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ in_array($trackedTicket->status, ['ready', 'completed']) ? 'badge-ok' : ($trackedTicket->status === 'waiting_approval' ? 'badge-err' : 'badge-warn') }}">
                                {{ $trackedTicket->statusLabel() }}
                            </span>
                        </div>

                        @if($trackedTicket->status !== 'cancelled')
                            @php $currentRank = \App\Models\ServiceTicket::PROGRESS_ORDER[$trackedTicket->status] ?? 1; @endphp
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                                @foreach(\App\Models\ServiceTicket::PROGRESS_ORDER as $stKey => $rank)
                                    <div class="rounded-lg p-3 {{ $rank <= $currentRank ? 'badge-accent-soft font-semibold' : 'surface-subtle text-muted-ink' }}">
                                        <span class="block text-[11px] opacity-80">Tahap {{ $rank }}</span>
                                        <span>{{ \App\Models\ServiceTicket::STATUSES[$stKey] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($trackedTicket->temp_before_c || $trackedTicket->temp_after_c || $trackedTicket->diagnosis_notes)
                            <div class="surface-subtle rounded-xl p-4 space-y-3 text-sm">
                                <h3 class="font-semibold">Hasil Pemeriksaan &amp; Suhu Perangkat</h3>
                                @if($trackedTicket->temp_before_c || $trackedTicket->temp_after_c)
                                    <div class="flex flex-wrap gap-3">
                                        @if($trackedTicket->temp_before_c)
                                            <div class="surface-card rounded-lg px-3.5 py-2">
                                                <span class="block text-xs text-muted-ink">Suhu Awal</span>
                                                <strong class="text-base tabular-nums">{{ $trackedTicket->temp_before_c }}°C</strong>
                                            </div>
                                        @endif
                                        @if($trackedTicket->temp_after_c)
                                            <div class="badge-ok rounded-lg px-3.5 py-2">
                                                <span class="block text-xs opacity-90">Suhu Setelah Servis</span>
                                                <strong class="text-base tabular-nums">{{ $trackedTicket->temp_after_c }}°C</strong>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                @if($trackedTicket->diagnosis_notes)
                                    <p class="text-muted-ink leading-relaxed">{{ $trackedTicket->diagnosis_notes }}</p>
                                @endif
                            </div>
                        @endif

                        <div class="space-y-2">
                            <h3 class="font-semibold text-sm">Rincian Biaya</h3>
                            <div class="surface-subtle rounded-xl p-4 space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Jasa ({{ $trackedTicket->servicePackage?->name ?? 'Pemeriksaan & Servis' }})</span>
                                    <span class="font-semibold tabular-nums">Rp {{ number_format($trackedTicket->service_fee, 0, ',', '.') }}</span>
                                </div>

                                @if($trackedTicket->parts->isEmpty())
                                    <p class="text-xs text-muted-ink">Tidak ada pergantian komponen tambahan.</p>
                                @else
                                    @foreach($trackedTicket->parts as $part)
                                        <div class="flex justify-between">
                                            <span>{{ $part->part_name }} (&times;{{ $part->qty }})</span>
                                            <span class="font-semibold tabular-nums">Rp {{ number_format($part->subtotal, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                @endif

                                <div class="border-t border-[var(--border-default)] pt-2.5 flex justify-between font-bold text-base">
                                    <span>Total Biaya</span>
                                    <span class="tabular-nums">Rp {{ number_format($trackedTicket->grand_total, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h3 class="font-semibold text-sm">Catatan Pengerjaan Teknisi</h3>
                            @if($trackedTicket->logs->isEmpty())
                                <p class="text-xs text-muted-ink">Belum ada catatan pengerjaan terbaru.</p>
                            @else
                                <ol class="space-y-2.5 text-sm">
                                    @foreach($trackedTicket->logs as $log)
                                        <li class="surface-subtle rounded-xl p-3.5 flex flex-col sm:flex-row sm:items-baseline justify-between gap-1">
                                            <div>
                                                <span class="font-semibold">{{ $log->statusLabel() }}:</span>
                                                <span class="text-muted-ink">{{ $log->note }}</span>
                                            </div>
                                            <time class="text-xs text-muted-ink shrink-0">
                                                {{ $log->created_at->format('d M Y, H:i') }}
                                            </time>
                                        </li>
                                    @endforeach
                                </ol>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a
                                href="{{ $store->whatsappUrl('Halo Admin ' . $store->store_name . ', saya ingin menanyakan tiket servis nomor ' . $trackedTicket->ticket_number . ' (' . $trackedTicket->device_brand_model . ').') }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn-accent min-h-11 px-5 rounded-lg text-sm inline-flex items-center"
                            >
                                Hubungi Teknisi via WhatsApp
                            </a>
                            <button
                                type="button"
                                @click="$store.tokoku.copyText(@js(route('service.index', ['tiket' => $trackedTicket->ticket_number, 'pin' => $trackedTicket->phoneLastFour()])), 'Tautan lacak resi ' + @js($trackedTicket->ticket_number) + ' disalin')"
                                class="btn-secondary min-h-11 px-4 rounded-lg text-sm cursor-pointer"
                            >
                                Salin Link Lacak
                            </button>
                            <button
                                type="button"
                                onclick="window.print()"
                                class="btn-secondary min-h-11 px-4 rounded-lg text-sm cursor-pointer"
                            >
                                Cetak Bukti Servis / PDF
                            </button>
                        </div>
                    </div>
                @else
                    <div class="surface-card rounded-xl p-8 space-y-2">
                        <h3 class="font-semibold text-base">Lacak Progres Servis Tanpa Perlu Login</h3>
                        <p class="text-sm text-muted-ink max-w-lg">
                            Ketik nomor tiket Anda di sebelah kiri, atau klik tombol <strong>Lihat Contoh Hasil Lacak Resi</strong> untuk melihat tampilan laporan suhu dan riwayat pengerjaan teknisi kami.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        @if($trackedTicket)
            <section aria-label="Lembar Cetak Tanda Terima dan Laporan Servis" class="print-only space-y-5">
                <div class="border-b-2 border-slate-800 pb-3 flex justify-between items-start gap-4">
                    <div>
                        <h1 class="text-xl font-extrabold">{{ $store->store_name }} - Bukti &amp; Laporan Servis</h1>
                        <p class="text-xs text-slate-600">{{ $store->workshop_address }} | WA: {{ $store->whatsapp_number }}</p>
                    </div>
                    <div class="text-right text-xs">
                        <p class="font-mono font-bold text-sm">{{ $trackedTicket->ticket_number }}</p>
                        <p>Status: <strong>{{ $trackedTicket->statusLabel() }}</strong></p>
                        <p>Dicetak: {{ now()->format('d M Y, H:i') }}</p>
                    </div>
                </div>

                <table class="print-table">
                    <tbody>
                        <tr>
                            <th style="width: 28%;">Nama Pelanggan</th>
                            <td>{{ $trackedTicket->customer_name }}</td>
                        </tr>
                        <tr>
                            <th>Perangkat</th>
                            <td>{{ $trackedTicket->device_type }} - {{ $trackedTicket->device_brand_model }}</td>
                        </tr>
                        <tr>
                            <th>Keluhan Awal</th>
                            <td>{{ $trackedTicket->complaint_summary }}</td>
                        </tr>
                        @if($trackedTicket->temp_before_c || $trackedTicket->temp_after_c)
                            <tr>
                                <th>Pengukuran Suhu</th>
                                <td>
                                    Suhu Awal: {{ $trackedTicket->temp_before_c ? $trackedTicket->temp_before_c . '°C' : '-' }}
                                    | Suhu Setelah QC: {{ $trackedTicket->temp_after_c ? $trackedTicket->temp_after_c . '°C' : '-' }}
                                </td>
                            </tr>
                        @endif
                        @if($trackedTicket->diagnosis_notes)
                            <tr>
                                <th>Hasil Pemeriksaan Teknisi</th>
                                <td>{{ $trackedTicket->diagnosis_notes }}</td>
                            </tr>
                        @endif
                        @if($trackedTicket->warranty_until)
                            <tr>
                                <th>Garansi Servis Hingga</th>
                                <td>{{ $trackedTicket->warranty_until->format('d M Y') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <table class="print-table">
                    <thead>
                        <tr>
                            <th>Uraian Jasa / Sparepart</th>
                            <th style="width: 15%;">Qty</th>
                            <th style="width: 28%; text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Jasa ({{ $trackedTicket->servicePackage?->name ?? 'Pemeriksaan & Servis' }})</td>
                            <td>1</td>
                            <td style="text-align: right;" class="tabular-nums">Rp {{ number_format($trackedTicket->service_fee, 0, ',', '.') }}</td>
                        </tr>
                        @foreach($trackedTicket->parts as $part)
                            <tr>
                                <td>{{ $part->part_name }}</td>
                                <td>{{ $part->qty }}</td>
                                <td style="text-align: right;" class="tabular-nums">Rp {{ number_format($part->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="2" class="font-extrabold">TOTAL BIAYA SERVIS</td>
                            <td style="text-align: right;" class="tabular-nums font-extrabold">Rp {{ number_format($trackedTicket->grand_total, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        @endif
    @endif
</div>
