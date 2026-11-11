<?php

namespace Tests\Feature;

use App\Livewire\DriverPanel;
use App\Livewire\LoginModal;
use App\Livewire\MasterData;
use App\Livewire\OwnerUsers;
use App\Livewire\PublicTracking;
use App\Livewire\ShipmentBoard;
use App\Models\Customer;
use App\Models\Fleet;
use App\Models\Shipment;
use App\Models\User;
use App\Services\CsvService;
use App\Services\ShipmentDomainService;
use Database\Seeders\AaclCargoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class AaclCargoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AaclCargoSeeder::class);
    }

    public function test_public_tracking_works_and_redacts_financial_fields(): void
    {
        $response = $this->get('/?cn=AACL-2611-0001');
        $response->assertOk();
        $response->assertSee('AACL-2611-0001');
        $response->assertSee('PT. Sinar Baja Elektronika');
        $response->assertDontSee('4.250.000');
        $response->assertDontSee('Tagihan TOP');

        Livewire::test(PublicTracking::class)
            ->set('cnQuery', 'aacl-2611-0001')
            ->call('searchCn')
            ->assertSet('searched', true)
            ->assertSet('result.cn_number', 'AACL-2611-0001')
            ->assertDontSee('4250000')
            ->assertDontSee('Lunas');
    }

    public function test_login_modal_authenticates_without_demo_buttons_and_redirects_by_role(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('Klik Cepat Akun Internal');

        Livewire::test(LoginModal::class)
            ->set('username', 'kurir')
            ->set('password', 'kurir123')
            ->call('authenticate')
            ->assertRedirect('/kurir');

        $this->assertAuthenticated();
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        Livewire::test(LoginModal::class)
            ->set('username', 'admin')
            ->set('password', 'wrongpass')
            ->call('authenticate')
            ->assertSet('errorMessage', 'Username atau password tidak sesuai.');
    }

    public function test_rbac_protects_routes_and_hides_prices_from_driver(): void
    {
        $driver = User::where('username', 'kurir')->firstOrFail();
        $admin = User::where('username', 'admin')->firstOrFail();
        $owner = User::where('username', 'owner')->firstOrFail();

        // Guest redirected
        $this->get('/operasional')->assertRedirect('/');
        $this->get('/kurir')->assertRedirect('/');
        $this->get('/owner')->assertRedirect('/');

        // Driver: allowed /kurir (no prices), forbidden /operasional, /master, /owner, /export/xlsx
        $this->actingAs($driver)->get('/kurir')
            ->assertOk()
            ->assertSee('AACL-2611-0005')
            ->assertDontSee('4.250.000')
            ->assertDontSee('Belum Lunas');
        $this->actingAs($driver)->get('/operasional')->assertForbidden();
        $this->actingAs($driver)->get('/master')->assertForbidden();
        $this->actingAs($driver)->get('/owner')->assertForbidden();
        $this->actingAs($driver)->get('/export/xlsx')->assertForbidden();

        // Admin: allowed /operasional, /master, /export/xlsx; forbidden /owner and /kurir
        $this->actingAs($admin)->get('/operasional')->assertOk()->assertSee('4.250.000');
        $this->actingAs($admin)->get('/master')->assertOk();
        $this->actingAs($admin)->get('/owner')->assertForbidden();
        $this->actingAs($admin)->get('/kurir')->assertForbidden();

        // Admin cannot delete shipment or customer
        $ship = Shipment::firstOrFail();
        Livewire::actingAs($admin)
            ->test(ShipmentBoard::class)
            ->call('deleteShipment', $ship->id);
        $this->assertDatabaseHas('shipments', ['id' => $ship->id]);

        $cust = Customer::firstOrFail();
        Livewire::actingAs($admin)
            ->test(MasterData::class)
            ->call('deleteCustomer', $cust->id);
        $this->assertDatabaseHas('customers', ['id' => $cust->id]);

        // Owner: allowed everywhere
        $this->actingAs($owner)->get('/operasional')->assertOk();
        $this->actingAs($owner)->get('/kurir')->assertOk();
        $this->actingAs($owner)->get('/master')->assertOk();
        $this->actingAs($owner)->get('/owner')->assertOk();
    }

    public function test_deleted_user_session_is_invalidated_immediately(): void
    {
        $tempDriver = User::create([
            'username' => 'tempdriver',
            'full_name' => 'Temp Driver',
            'name' => 'Temp Driver',
            'email' => 'temp@aacl-cargo.local',
            'password' => 'secret123',
            'role' => 'driver',
        ]);

        $this->actingAs($tempDriver)->get('/kurir')->assertOk();

        // Owner deletes tempDriver
        $tempDriver->delete();

        // Next request as deleted user must fail & redirect
        $this->actingAs($tempDriver)->get('/kurir')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_primary_owner_cannot_be_deleted_or_demoted(): void
    {
        $owner = User::where('username', 'owner')->firstOrFail();

        Livewire::actingAs($owner)
            ->test(OwnerUsers::class)
            ->call('deleteUser', $owner->id)
            ->call('openEditUser', $owner->id)
            ->set('u_role', 'driver')
            ->set('u_full_name', 'Owner Updated')
            ->call('saveUser');

        $owner->refresh();
        $this->assertSame('owner', $owner->role);
        $this->assertSame('Owner Updated', $owner->full_name);
    }

    public function test_shipment_creation_pod_cleanup_and_delete(): void
    {
        $owner = User::where('username', 'owner')->firstOrFail();
        $domain = app(ShipmentDomainService::class);

        Livewire::actingAs($owner)
            ->test(ShipmentBoard::class)
            ->call('openCreateModal')
            ->set('form_cn_number', 'AACL-2611-9999')
            ->set('form_customer_name', 'PT. Uji TALL Baru')
            ->set('form_shipper_address', 'Jakarta Utara')
            ->set('form_receiver_address', 'Surabaya')
            ->set('form_colly', 10)
            ->set('form_weight_kg', 250)
            ->set('form_volume_m3', 1.5)
            ->set('form_area_m2', 1.2)
            ->set('form_service_cost', 1750000)
            ->set('form_payment_status', 'Tagihan TOP 7 Hari')
            ->call('saveShipment');

        $shipment = Shipment::where('cn_number', 'AACL-2611-9999')->firstOrFail();
        $this->assertSame(1750000.0, (float) $shipment->service_cost);

        // Save POD photo #1 then replace with POD photo #2 -> #1 must be cleaned up
        $tinyPng = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        $domain->updateShipmentPod($shipment, [
            'pod_receiver_name' => 'Penerima Pertama',
            'pod_photo_base64' => $tinyPng,
        ], null, 'Owner');
        $shipment->refresh();
        $photo1Url = $shipment->pod_photo_url;
        $photo1Path = public_path(ltrim($photo1Url, '/'));
        $this->assertFileExists($photo1Path);
        $this->assertSame(5, $shipment->current_stage);

        $domain->updateShipmentPod($shipment, [
            'pod_receiver_name' => 'Penerima Kedua',
            'pod_photo_base64' => $tinyPng,
        ], null, 'Owner');
        $shipment->refresh();
        $photo2Url = $shipment->pod_photo_url;
        $photo2Path = public_path(ltrim($photo2Url, '/'));
        $this->assertNotSame($photo1Url, $photo2Url);
        $this->assertFileDoesNotExist($photo1Path);
        $this->assertFileExists($photo2Path);

        // Deleting shipment removes photo2 too
        $domain->deleteShipment($shipment);
        $this->assertFileDoesNotExist($photo2Path);
        $this->assertDatabaseMissing('shipments', ['cn_number' => 'AACL-2611-9999']);
    }

    public function test_excel_xlsx_and_csv_export_import_work_cleanly(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        $xlsxRes = $this->actingAs($admin)->get('/export/xlsx');
        $xlsxRes->assertOk();
        $binary = $xlsxRes->getContent();
        $this->assertStringStartsWith('PK', $binary);

        $tmp = tempnam(sys_get_temp_dir(), 'test_xlsx_');
        file_put_contents($tmp, $binary);
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tmp) === true);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($tmp);

        $this->assertIsString($sheetXml);
        $this->assertStringContainsString('AACL-2611-0001', $sheetXml);
        $this->assertStringContainsString('SUM(F6:', $sheetXml);
        $this->assertStringContainsString('fitToWidth="1"', $sheetXml);

        // CSV Export + Multiline CSV Import roundtrip
        $csvRes = $this->actingAs($admin)->get('/export/csv');
        $csvRes->assertOk();
        $this->assertStringContainsString('No. CN,Tanggal,Nama Cust', $csvRes->getContent());

        $multilineCsv = "No. CN,Tanggal,Nama Cust,Alamat Pengirim,Alamat Penerima,Jumlah,,,,Armada,Pickup,Status,,,,,Bukti Diterima (POD),Harga Kirim (Rp),Status Bayar,Catatan\r\n"
            .",,,,,Colly,Kg,Vol (m³),m²,,,1,2,3,4,5,,,,\r\n"
            ."AACL-2611-8888,2026-11-11,\"PT. Multiline\nTest\",\"Jl. Asal\nBlok A\",\"Jl. Tujuan\nBlok B\",5,100,1.2,0.8,CDD Box,09:00,Pickup ok,,,,,POD ok,950000,Lunas,Catatan\r\n";

        $importResult = app(CsvService::class)->importCsv(
            $multilineCsv,
            app(ShipmentDomainService::class),
            'Admin Tester'
        );

        $this->assertSame(1, $importResult['inserted']);
        $imported = Shipment::where('cn_number', 'AACL-2611-8888')->firstOrFail();
        $this->assertStringContainsString("PT. Multiline\nTest", $imported->customer_name);
        $this->assertSame(950000.0, (float) $imported->service_cost);

        // Verify CSV export -> re-import preserves POD receiver name & time
        $reimportResult = app(CsvService::class)->importCsv(
            $csvRes->getContent(),
            app(ShipmentDomainService::class),
            'Admin Tester'
        );
        $this->assertGreaterThanOrEqual(5, $reimportResult['updated']);
        $cn1 = Shipment::where('cn_number', 'AACL-2611-0001')->firstOrFail();
        $this->assertSame('Suharto (Kepala Gudang)', $cn1->pod_receiver_name);
    }

    public function test_master_data_rename_syncs_to_shipments_and_pod_autosyncs_in_form(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $cust = Customer::where('customer_code', 'CUST-001')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(MasterData::class)
            ->call('openEditCustomer', $cust->id)
            ->set('cust_company_name', 'PT. Sinar Baja Elektronika Tbk')
            ->call('saveCustomer');

        $this->assertDatabaseHas('shipments', [
            'cn_number' => 'AACL-2611-0001',
            'customer_name' => 'PT. Sinar Baja Elektronika Tbk',
        ]);

        // Input CN with POD receiver name directly in form auto-fills Status 5 & stage 5
        Livewire::actingAs($admin)
            ->test(ShipmentBoard::class)
            ->call('openCreateModal')
            ->set('form_cn_number', 'AACL-2611-7777')
            ->set('form_customer_name', 'PT. Langsung POD')
            ->set('form_pod_receiver_name', 'Bpk. Wahyu')
            ->call('saveShipment');

        $directPod = Shipment::where('cn_number', 'AACL-2611-7777')->firstOrFail();
        $this->assertSame(5, $directPod->current_stage);
        $this->assertNotEmpty($directPod->status_5_at);
    }

    public function test_driver_panel_filters_work(): void
    {
        $driver = User::where('username', 'kurir')->firstOrFail();

        Livewire::actingAs($driver)
            ->test(DriverPanel::class)
            ->set('statusFilter', 'completed')
            ->assertSee('AACL-2611-0001')
            ->assertDontSee('AACL-2611-0005')
            ->set('statusFilter', 'pending')
            ->set('search', 'Tekstil')
            ->assertSee('AACL-2611-0005')
            ->assertDontSee('AACL-2611-0001');
    }
}
