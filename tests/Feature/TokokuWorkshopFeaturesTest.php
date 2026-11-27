<?php

namespace Tests\Feature;

use App\Filament\Pages\WorkshopReportPage;
use App\Filament\Resources\ActivityLogResource;
use App\Livewire\ServiceCenterPage;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ServiceTicket;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\QrCodeSvgService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class TokokuWorkshopFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        RateLimiter::clear('tokoku:lookup-ticket:127.0.0.1:SRV-202611-0003');
        RateLimiter::clear('tokoku:lookup-ticket:127.0.0.1:SRV-202611-0001');
    }

    public function test_customer_can_approve_waiting_approval_ticket_with_one_click_and_cannot_reapprove(): void
    {
        $ticket = ServiceTicket::query()->where('ticket_number', 'SRV-202611-0003')->firstOrFail();
        RateLimiter::clear('tokoku:approve-cost:127.0.0.1:' . $ticket->id);

        $this->assertSame('waiting_approval', $ticket->status);
        $this->assertNull($ticket->customer_approved_at);

        Livewire::test(ServiceCenterPage::class)
            ->set('ticketInput', 'SRV-202611-0003')
            ->set('pinInput', '3333')
            ->call('lookupTicket')
            ->assertSet('trackedTicket.ticket_number', 'SRV-202611-0003')
            ->set('approvalNote', 'Lanjut kerjakan hari ini ya Mas')
            ->call('approveTrackedTicketCost')
            ->assertSet('trackedTicket.status', 'in_progress')
            ->assertSee('Persetujuan biaya tercatat!');

        $ticket->refresh();
        $this->assertSame('in_progress', $ticket->status);
        $this->assertNotNull($ticket->customer_approved_at);
        $this->assertSame('Lanjut kerjakan hari ini ya Mas', $ticket->customer_approval_note);

        $this->assertDatabaseHas('service_ticket_logs', [
            'service_ticket_id' => $ticket->id,
            'status' => 'in_progress',
            'is_public' => 1,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'ticket_customer_approved',
            'subject_type' => 'ServiceTicket',
            'subject_id' => $ticket->id,
        ]);

        // Second approval attempt must be rejected because status is no longer waiting_approval.
        Livewire::test(ServiceCenterPage::class)
            ->set('ticketInput', 'SRV-202611-0003')
            ->set('pinInput', '3333')
            ->call('lookupTicket')
            ->call('approveTrackedTicketCost')
            ->assertSee('Status tiket sudah berubah dan tidak lagi menunggu persetujuan biaya.');
    }

    public function test_before_after_photos_and_local_qr_svg_render_on_service_and_pc_builder_pages(): void
    {
        $qrSvg = app(QrCodeSvgService::class)->generateInlineSvg('https://tokoku.local/servis?tiket=SRV-202611-0001&pin=1234', 96);
        $this->assertStringContainsString('<svg', $qrSvg);
        $this->assertStringNotContainsString('<?xml', $qrSvg);

        $this->get('/servis?tiket=SRV-202611-0001&pin=1234')
            ->assertOk()
            ->assertSee('Dokumentasi Foto Sebelum & Sesudah Servis')
            ->assertSee('sample-before-dust.svg')
            ->assertSee('sample-after-clean.svg')
            ->assertSee('<svg', false);

        $this->get('/rakit-pc?kode=RKT-CONTOH')
            ->assertOk()
            ->assertSee('RKT-CONTOH')
            ->assertSee('<svg', false);
    }

    public function test_thermal_sticker_route_requires_staff_role_and_supports_58mm_and_80mm(): void
    {
        $ticket = ServiceTicket::query()->where('ticket_number', 'SRV-202611-0001')->firstOrFail();

        $this->get(route('staff.service-sticker', $ticket))
            ->assertRedirect('/admin/login');

        $admin = User::query()->where('email', 'admin@tokoku.local')->firstOrFail();
        $tech = User::query()->where('email', 'teknisi@tokoku.local')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('staff.service-sticker', $ticket))
            ->assertOk()
            ->assertSee($ticket->ticket_number)
            ->assertSee('58mm')
            ->assertSee('<svg', false);

        $this->flushSession();

        $this->actingAs($tech)
            ->get(route('staff.service-sticker', ['ticket' => $ticket->id, 'lebar' => '80']))
            ->assertOk()
            ->assertSee($ticket->ticket_number)
            ->assertSee('80mm')
            ->assertSee('<svg', false);

        $this->flushSession();

        $customerUser = User::query()->create([
            'name' => 'Pelanggan Biasa',
            'email' => 'customer@tokoku.local',
            'role' => 'customer',
            'password' => 'password123',
        ]);

        $this->actingAs($customerUser)
            ->get(route('staff.service-sticker', $ticket))
            ->assertForbidden();
    }

    public function test_activity_logger_records_model_changes_and_admin_only_pages_enforce_rbac(): void
    {
        $admin = User::query()->where('email', 'admin@tokoku.local')->firstOrFail();
        $tech = User::query()->where('email', 'teknisi@tokoku.local')->firstOrFail();

        $this->actingAs($admin);

        $product = Product::query()->firstOrFail();
        $oldStock = $product->stock;
        $product->update(['stock' => $oldStock + 3]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'product_updated',
            'subject_type' => 'Product',
            'subject_id' => $product->id,
            'user_id' => $admin->id,
        ]);

        $ticket = ServiceTicket::query()->where('ticket_number', 'SRV-202611-0001')->firstOrFail();
        $ticket->update(['status' => 'ready']);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'ticket_updated',
            'subject_type' => 'ServiceTicket',
            'subject_id' => $ticket->id,
            'user_id' => $admin->id,
        ]);

        $setting = StoreSetting::current();
        $setting->update(['default_assembly_fee' => 175000]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'settings_updated',
            'subject_type' => 'StoreSetting',
            'subject_id' => $setting->id,
        ]);

        // Read-only audit guard checks
        $sampleLog = ActivityLog::query()->latest('id')->firstOrFail();
        $this->assertFalse(ActivityLogResource::canCreate());
        $this->assertFalse(ActivityLogResource::canEdit($sampleLog));
        $this->assertFalse(ActivityLogResource::canDelete($sampleLog));
        $this->assertFalse(ActivityLogResource::canDeleteAny());

        // Admin can access both Audit Log and Revenue Report
        $this->get('/admin/activity-logs')
            ->assertOk()
            ->assertSee('Log Audit Aktivitas');

        $this->get('/admin/laporan-omzet')
            ->assertOk()
            ->assertSee('Laporan Omzet & Kinerja Teknisi');

        // Technician must be forbidden from admin-only Audit Log and Revenue Report
        $this->flushSession();
        $this->actingAs($tech);

        $this->get('/admin/activity-logs')->assertForbidden();
        $this->get('/admin/laporan-omzet')->assertForbidden();
    }

    public function test_workshop_report_page_period_filters_and_csv_exports_work(): void
    {
        $admin = User::query()->where('email', 'admin@tokoku.local')->firstOrFail();
        $this->actingAs($admin);

        // Ensure at least one completed ticket exists for revenue & CSV validation, including a formula-like customer name.
        $ticket = ServiceTicket::query()->where('ticket_number', 'SRV-202611-0001')->firstOrFail();
        $ticket->update([
            'status' => 'completed',
            'customer_name' => '=SUM(A1:A2) Uji Formula',
        ]);

        $component = Livewire::test(WorkshopReportPage::class)
            ->assertSee('Laporan Omzet & Kinerja Teknisi')
            ->call('setPeriod', '7d')
            ->assertSet('period', '7d')
            ->call('setPeriod', 'all')
            ->assertSet('period', 'all');

        $page = new WorkshopReportPage();
        $page->period = 'all';

        $serviceResponse = $page->exportServiceCsv();
        ob_start();
        $serviceResponse->sendContent();
        $serviceCsv = (string) ob_get_clean();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $serviceCsv);
        $this->assertStringContainsString('No Tiket', $serviceCsv);
        $this->assertStringContainsString('SRV-202611-0001', $serviceCsv);
        $this->assertStringContainsString("'=SUM(A1:A2) Uji Formula", $serviceCsv);

        $productResponse = $page->exportProductsCsv();
        ob_start();
        $productResponse->sendContent();
        $productCsv = (string) ob_get_clean();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $productCsv);
        $this->assertStringContainsString('SKU', $productCsv);
        $this->assertStringContainsString('Nilai Total Stok (Rp)', $productCsv);
        $this->assertGreaterThan(100, strlen($productCsv));

        unset($component);
    }
}
