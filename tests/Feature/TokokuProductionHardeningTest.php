<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\TokokuLogin;
use App\Filament\Pages\Auth\TokokuRequestPasswordReset;
use App\Filament\Pages\Auth\TokokuResetPassword;
use App\Livewire\PcBuilderWorkbench;
use App\Livewire\ServiceCenterPage;
use App\Models\PcCatalogPart;
use App\Models\Product;
use App\Models\ServiceTicket;
use App\Models\ServiceTicketPart;
use App\Models\StoreSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class TokokuProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_booking_rejects_past_dates_and_honeypot_silently_drops_bots(): void
    {
        $initialCount = ServiceTicket::query()->count();

        Livewire::test(ServiceCenterPage::class)
            ->set('customerName', 'Pelanggan Tanggal Lama')
            ->set('customerPhone', '081234567890')
            ->set('deviceType', 'PC Rakitan Desktop')
            ->set('deviceBrandModel', 'Core i5 Office')
            ->set('visitType', 'walk_in')
            ->set('preferredDate', now()->subDay()->toDateString())
            ->set('complaintSummary', 'Ingin bersihkan debu dan cek suhu prosesor.')
            ->call('submitBooking')
            ->assertHasErrors(['preferredDate']);

        Livewire::test(ServiceCenterPage::class)
            ->set('websiteUrl', 'https://spam-bot.example.com')
            ->set('customerName', 'Bot Otomatis')
            ->set('customerPhone', '081299998888')
            ->set('deviceType', 'PC Rakitan Desktop')
            ->set('deviceBrandModel', 'Spam Device')
            ->set('visitType', 'walk_in')
            ->set('preferredDate', now()->addDay()->toDateString())
            ->set('complaintSummary', 'Pesan otomatis dari bot spam.')
            ->call('submitBooking')
            ->assertHasNoErrors();

        $this->assertSame($initialCount, ServiceTicket::query()->count());
    }

    public function test_rate_limiting_protects_ticket_pin_lookup_and_booking(): void
    {
        RateLimiter::clear('tokoku:lookup-ticket:127.0.0.1:SRV-202611-0001');
        RateLimiter::clear('tokoku:booking:127.0.0.1');

        $component = Livewire::test(ServiceCenterPage::class)
            ->set('ticketInput', 'SRV-202611-0001')
            ->set('pinInput', '0000');

        for ($i = 0; $i < 10; $i++) {
            $component->call('lookupTicket');
        }

        $component->call('lookupTicket')
            ->assertSee('Terlalu banyak percobaan verifikasi PIN');

        for ($b = 1; $b <= 3; $b++) {
            Livewire::test(ServiceCenterPage::class)
                ->set('customerName', "Pelanggan {$b}")
                ->set('customerPhone', "08137777000{$b}")
                ->set('deviceType', 'Laptop Gaming / Kerja')
                ->set('deviceBrandModel', 'ASUS VivoBook')
                ->set('visitType', 'walk_in')
                ->set('preferredDate', now()->addDays(2)->toDateString())
                ->set('complaintSummary', 'Kipas berbunyi keras saat dipakai kerja.')
                ->call('submitBooking')
                ->assertHasNoErrors();
        }

        Livewire::test(ServiceCenterPage::class)
            ->set('customerName', 'Pelanggan Keempat')
            ->set('customerPhone', '081377770004')
            ->set('deviceType', 'Laptop Gaming / Kerja')
            ->set('deviceBrandModel', 'ASUS VivoBook')
            ->set('visitType', 'walk_in')
            ->set('preferredDate', now()->addDays(2)->toDateString())
            ->set('complaintSummary', 'Kipas berbunyi keras saat dipakai kerja.')
            ->call('submitBooking')
            ->assertHasErrors(['customerName']);
    }

    public function test_sparepart_inventory_syncs_stock_across_add_edit_cancel_and_delete(): void
    {
        $product = Product::query()
            ->where('availability_status', 'available')
            ->where('stock', '>=', 3)
            ->firstOrFail();

        $product->update(['stock' => 3, 'availability_status' => 'available']);

        $ticket = ServiceTicket::query()->where('status', 'in_progress')->first()
            ?? ServiceTicket::query()->firstOrFail();
        $ticket->update(['status' => 'in_progress']);

        $part = ServiceTicketPart::query()->create([
            'service_ticket_id' => $ticket->id,
            'product_id' => $product->id,
            'part_name' => $product->name,
            'qty' => 2,
            'unit_price' => $product->price,
        ]);

        $this->assertSame(1, $product->fresh()->stock);
        $this->assertSame(2, $part->fresh()->stock_deducted_qty);

        $part->update(['qty' => 3]);
        $this->assertSame(0, $product->fresh()->stock);
        $this->assertSame('sold', $product->fresh()->availability_status);
        $this->assertSame(3, $part->fresh()->stock_deducted_qty);

        $part->update(['qty' => 1]);
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertSame('available', $product->fresh()->availability_status);
        $this->assertSame(1, $part->fresh()->stock_deducted_qty);

        $ticket->update(['status' => 'cancelled']);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame(0, $part->fresh()->stock_deducted_qty);

        $ticket->update(['status' => 'in_progress']);
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertSame(1, $part->fresh()->stock_deducted_qty);

        $part->delete();
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_store_setting_cache_invalidates_immediately_on_save(): void
    {
        $initial = StoreSetting::current();
        $this->assertSame('Tokoku', $initial->store_name);

        $initial->update(['store_name' => 'Tokoku Pusat']);
        $this->assertSame('Tokoku Pusat', StoreSetting::current()->store_name);
    }

    public function test_opengraph_meta_custom_404_print_sheets_and_store_stock_badge_work(): void
    {
        $product = Product::query()->where('sku', 'CPU-R5-5600')->firstOrFail();

        $this->get('/barang/' . $product->slug)
            ->assertOk()
            ->assertSee('property="og:title"', false)
            ->assertSee('property="og:description"', false)
            ->assertSee('property="og:image"', false)
            ->assertSee($product->formattedPrice());

        $this->get('/rakit-pc?kode=RKT-CONTOH')
            ->assertOk()
            ->assertSee('property="og:description"', false)
            ->assertSee('Lembar Estimasi Rakitan PC');

        $this->get('/servis?tiket=SRV-202611-0001&pin=1234')
            ->assertOk()
            ->assertSee('Cetak Bukti Servis / PDF')
            ->assertSee('Laporan Servis');

        $this->get('/barang/unit-fiktif-tidak-ada-999')
            ->assertNotFound()
            ->assertSee('KODE 404')
            ->assertSee('Kembali ke Etalase Barang');

        $catalogCpu = PcCatalogPart::query()->updateOrCreate(
            ['external_key' => 'test-cpu-ryzen-5-5600'],
            [
                'slot' => 'cpu',
                'name' => 'AMD Ryzen 5 5600',
                'brand' => 'AMD',
                'price_usd' => 115.00,
                'estimated_price_idr' => 1897500,
                'price_tier' => 'budget',
                'spec_summary' => '6 Cores · 3.5 GHz · 65W',
                'compatibility_specs' => ['socket' => 'AM4', 'tdp_watt' => 65, 'has_igpu' => false],
            ]
        );

        Livewire::test(PcBuilderWorkbench::class)
            ->call('chooseComponent', 'cpu', $catalogCpu->id)
            ->assertSee('Ready di Stok Toko');
    }

    public function test_split_screen_login_and_recaptcha_verification_work(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Pusat Kendali Katalog')
            ->assertSee('Lupa kata sandi?')
            ->assertSee('Verifikasi Keamanan (reCAPTCHA)')
            ->assertSee('Buka Login Teknisi');

        $this->get('/teknisi/login')
            ->assertOk()
            ->assertSee('Papan Antrean Servis')
            ->assertSee('Lupa kata sandi?')
            ->assertSee('Buka Login Admin');

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(TokokuLogin::class)
            ->set('email', 'admin@tokoku.local')
            ->set('password', 'admin123')
            ->set('securityAnswer', '999')
            ->call('authenticate')
            ->assertHasErrors(['securityAnswer']);

        $this->assertGuest();

        Livewire::test(TokokuLogin::class)
            ->call('fillLocalDemoAccount')
            ->call('authenticate')
            ->assertHasNoErrors();

        $this->assertAuthenticated();
        auth()->logout();
        $this->flushSession();

        config([
            'services.recaptcha.site_key' => 'mock-site-key',
            'services.recaptcha.secret_key' => 'mock-secret-key',
        ]);

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
        ]);

        Livewire::test(TokokuLogin::class)
            ->set('email', 'admin@tokoku.local')
            ->set('password', 'admin123')
            ->set('recaptchaToken', '')
            ->call('authenticate')
            ->assertHasErrors(['recaptchaToken']);

        Livewire::test(TokokuLogin::class)
            ->set('email', 'admin@tokoku.local')
            ->set('password', 'admin123')
            ->set('recaptchaToken', 'mock-valid-google-token')
            ->call('authenticate')
            ->assertHasNoErrors();

        $this->assertAuthenticated();
    }

    public function test_forgot_password_and_reset_password_flow_work_for_staff(): void
    {
        $this->get('/admin/password-reset/request')
            ->assertOk()
            ->assertSee('Atur Ulang Kata Sandi Instan')
            ->assertSee('PIN Pemulihan 6 Digit');

        $this->get('/teknisi/password-reset/request')
            ->assertOk()
            ->assertSee('Atur Ulang Kata Sandi Instan')
            ->assertSee('Minta bantuan Admin via WA');

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $admin = User::query()->where('email', 'admin@tokoku.local')->firstOrFail();

        $wrongAttempt = Livewire::test(TokokuRequestPasswordReset::class);
        $challengeAns = session('tokoku.security_challenge.reset_admin');

        $wrongAttempt
            ->set('email', 'admin@tokoku.local')
            ->set('recoveryPin', '000000')
            ->set('securityAnswer', (string) $challengeAns)
            ->call('verifyPinAndProceed')
            ->assertHasErrors(['recoveryPin'])
            ->assertSet('verifiedUserId', null);

        Livewire::test(TokokuRequestPasswordReset::class)
            ->call('fillLocalDemoRecovery')
            ->call('verifyPinAndProceed')
            ->assertHasNoErrors()
            ->assertSet('verifiedUserId', $admin->id)
            ->set('newPassword', 'sandibaru2026')
            ->set('newPasswordConfirmation', 'sandibaru2026')
            ->call('saveNewPassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('sandibaru2026', $admin->fresh()->password));

        config(['services.tokoku.recovery_code' => 'DARURAT-TOKO-99']);
        Filament::setCurrentPanel(Filament::getPanel('teknisi'));
        $tech = User::query()->where('email', 'teknisi@tokoku.local')->firstOrFail();

        $masterComp = Livewire::test(TokokuRequestPasswordReset::class);
        $techChallenge = session('tokoku.security_challenge.reset_teknisi');

        $masterComp
            ->set('email', 'teknisi@tokoku.local')
            ->set('recoveryPin', 'DARURAT-TOKO-99')
            ->set('securityAnswer', (string) $techChallenge)
            ->call('verifyPinAndProceed')
            ->assertHasNoErrors()
            ->assertSet('verifiedUserId', $tech->id)
            ->set('newPassword', 'teknisibaru2026')
            ->set('newPasswordConfirmation', 'teknisibaru2026')
            ->call('saveNewPassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('teknisibaru2026', $tech->fresh()->password));
    }
}
