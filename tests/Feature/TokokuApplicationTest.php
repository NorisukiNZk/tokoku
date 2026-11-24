<?php

namespace Tests\Feature;

use App\Livewire\MarketplaceCatalog;
use App\Livewire\PcBuilderWorkbench;
use App\Livewire\ServiceCenterPage;
use App\Models\PcBuild;
use App\Models\Product;
use App\Models\ServiceTicket;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TokokuApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_storefront_pages_render_cleanly(): void
    {
        $this->get('/')->assertOk()->assertSee('Pilihan Barang Hari Ini');

        $product = Product::query()->firstOrFail();
        $this->get('/barang/' . $product->slug)
            ->assertOk()
            ->assertSee($product->name);

        $this->get('/rakit-pc')->assertOk()->assertSee('Simulasi Rakit PC');
        $this->get('/servis')->assertOk()->assertSee('Layanan Servis');
    }

    public function test_marketplace_filters_and_empty_state_work(): void
    {
        Livewire::test(MarketplaceCatalog::class)
            ->set('conditionFilter', 'minus_parts')
            ->assertSee('Gigabyte GA-H81M-DS2')
            ->set('search', 'KATAKUNCIYANGTIDAKADA999')
            ->assertSee('Belum ada barang yang sesuai pencarian Anda')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('conditionFilter', 'all');
    }

    public function test_pc_builder_preset_and_saving_share_code_work(): void
    {
        Livewire::test(PcBuilderWorkbench::class)
            ->call('applySmartTierPreset', 'budget')
            ->call('openSlotPicker', 'cpu')
            ->set('pickerTier', 'budget')
            ->call('closeSlotPicker')
            ->set('buildName', '')
            ->call('saveBuild')
            ->assertHasErrors(['buildName'])
            ->set('buildName', 'Rakitan Uji Pelanggan')
            ->set('customerName', 'Penguji Sistem')
            ->call('saveBuild')
            ->assertHasNoErrors();

        $this->assertTrue(PcBuild::query()->where('build_name', 'Rakitan Uji Pelanggan')->exists());
    }

    public function test_service_tracking_and_booking_submission_work(): void
    {
        Livewire::test(ServiceCenterPage::class)
            ->set('ticketInput', 'SRV-202611-0001')
            ->set('pinInput', '9999')
            ->call('lookupTicket')
            ->assertSee('Data tiket tidak ditemukan atau 4 digit terakhir nomor WhatsApp tidak sesuai')
            ->call('fillDemoTicket')
            ->assertSee('92°C')
            ->assertSee('67°C');

        Livewire::test(ServiceCenterPage::class)
            ->set('customerName', 'Rizky Pratama')
            ->set('customerPhone', '081355667788')
            ->set('deviceType', 'PC Rakitan Desktop')
            ->set('deviceBrandModel', 'Ryzen 5 5600 + RX 6600')
            ->set('visitType', 'walk_in')
            ->set('preferredDate', now()->addDay()->toDateString())
            ->set('complaintSummary', 'Ingin pembersihan debu rutin dan ganti pasta termal baru.')
            ->call('submitBooking')
            ->assertHasNoErrors();

        $this->assertTrue(
            ServiceTicket::query()->where('customer_phone', '081355667788')->exists()
        );
    }

    public function test_filament_admin_and_teknisi_panel_access_control(): void
    {
        $this->get('/admin/login')->assertOk();
        $this->get('/teknisi/login')->assertOk();

        $admin = User::query()->where('email', 'admin@tokoku.local')->firstOrFail();
        $tech = User::query()->where('email', 'teknisi@tokoku.local')->firstOrFail();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/products')->assertOk();
        $this->actingAs($admin)->get('/admin/pc-catalog-parts')->assertOk();
        $this->actingAs($admin)->get('/admin/service-tickets')->assertOk();
        $this->actingAs($admin)->get('/admin/manage-store-settings')->assertOk();
        $this->actingAs($admin)->get('/teknisi')->assertOk();

        $this->flushSession();
        $this->actingAs($tech)->get('/teknisi')->assertOk();
        $this->actingAs($tech)->get('/teknisi/service-tickets')->assertOk();
        $this->actingAs($tech)->get('/admin')->assertForbidden();
    }
}
