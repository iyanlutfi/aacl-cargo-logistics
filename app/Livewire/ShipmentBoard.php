<?php

namespace App\Livewire;

use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Fleet;
use App\Models\Shipment;
use App\Models\TrackingLog;
use App\Services\CsvService;
use App\Services\ShipmentDomainService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Operasional Spreadsheet — AACL Cargo & Logistics')]
class ShipmentBoard extends Component
{
    use WithFileUploads;

    // Filters
    public string $search = '';

    public string $stage = 'all';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $paymentStatus = 'all';

    // Modal flags
    public bool $showFormModal = false;

    public bool $showStageModal = false;

    public bool $showPodModal = false;

    public bool $showPricingModal = false;

    public bool $showImportModal = false;

    public bool $showPrintModal = false;

    // Form CN (Create / Edit)
    public ?int $editingId = null;

    public string $form_cn_number = '';

    public string $form_shipment_date = '';

    public string $form_customer_id = '';

    public string $form_customer_name = '';

    public bool $form_save_customer = true;

    public string $form_shipper_address = '';

    public string $form_receiver_address = '';

    public int|string $form_colly = 0;

    public float|string $form_weight_kg = 0;

    public float|string $form_volume_m3 = 0;

    public float|string $form_area_m2 = 0;

    public string $form_fleet_id = '';

    public string $form_fleet_name = '';

    public string $form_pickup_time = '';

    public float|string $form_service_cost = 0;

    public string $form_payment_status = 'Belum Lunas';

    public string $form_status_1 = '';

    public string $form_status_2 = '';

    public string $form_status_3 = '';

    public string $form_status_4 = '';

    public string $form_status_5 = '';

    public string $form_pod_receiver_name = '';

    public string $form_pod_received_at = '';

    public string $form_pod_notes = '';

    public string $form_notes = '';

    // Quick Stage Modal
    public ?int $stageShipmentId = null;

    public string $stageShipmentCn = '';

    public int $stageNumber = 1;

    public string $stageValue = '';

    // Quick POD Modal
    public ?int $podShipmentId = null;

    public string $podShipmentCn = '';

    public string $pod_receiver_name = '';

    public string $pod_received_at = '';

    public string $pod_notes = '';

    public ?string $pod_existing_photo_url = null;

    public $pod_photo_file = null;

    public string $pod_signature_data = '';

    public bool $pod_clear_signature = false;

    // Quick Pricing Modal
    public ?int $pricingShipmentId = null;

    public string $pricingShipmentCn = '';

    public float|string $pricing_service_cost = 0;

    public string $pricing_payment_status = 'Belum Lunas';

    // Import CSV Modal
    public string $csvText = '';

    public $csvFile = null;

    // Print Surat Jalan Modal
    public ?array $printData = null;

    public bool $includePriceInPrint = true;

    public function resetFilters(): void
    {
        $this->reset(['search', 'stage', 'dateFrom', 'dateTo', 'paymentStatus']);
        $this->stage = 'all';
        $this->paymentStatus = 'all';
    }

    public function setStageFilter(string $stage): void
    {
        $this->stage = $stage;
    }

    public function closeAllModals(): void
    {
        $this->showFormModal = false;
        $this->showStageModal = false;
        $this->showPodModal = false;
        $this->showPricingModal = false;
        $this->showImportModal = false;
        $this->showPrintModal = false;
    }

    public function updatedFormCustomerId($val): void
    {
        if ($val !== '' && is_numeric($val)) {
            $cust = Customer::find((int) $val);
            if ($cust) {
                $this->form_customer_name = $cust->company_name;
                if (trim($this->form_shipper_address) === '' && $cust->default_address) {
                    $this->form_shipper_address = $cust->default_address;
                }
            }
        }
    }

    public function updatedFormFleetId($val): void
    {
        if ($val !== '' && is_numeric($val)) {
            $fleet = Fleet::find((int) $val);
            if ($fleet) {
                $this->form_fleet_name = "{$fleet->vehicle_type} ({$fleet->plate_number})";
            }
        }
    }

    public function updatedCsvFile(): void
    {
        if ($this->csvFile) {
            $content = @file_get_contents($this->csvFile->getRealPath());
            if ($content !== false) {
                $this->csvText = $content;
            }
        }
    }

    public function openCreateModal(ShipmentDomainService $domain): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->form_cn_number = $domain->generateNextCnNumber();
        $this->form_shipment_date = $domain->localIsoDate();
        $this->form_customer_id = '';
        $this->form_customer_name = '';
        $this->form_save_customer = true;
        $this->form_shipper_address = '';
        $this->form_receiver_address = '';
        $this->form_colly = 0;
        $this->form_weight_kg = 0;
        $this->form_volume_m3 = 0;
        $this->form_area_m2 = 0;
        $this->form_fleet_id = '';
        $this->form_fleet_name = '';
        $this->form_pickup_time = now('Asia/Jakarta')->format('H:i').' WIB';
        $this->form_service_cost = 0;
        $this->form_payment_status = 'Belum Lunas';
        $this->form_status_1 = $domain->shortWibTimestamp().' - Barang dijadwalkan / dijemput';
        $this->form_status_2 = '';
        $this->form_status_3 = '';
        $this->form_status_4 = '';
        $this->form_status_5 = '';
        $this->form_pod_receiver_name = '';
        $this->form_pod_received_at = '';
        $this->form_pod_notes = '';
        $this->form_notes = '';
        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $s = Shipment::findOrFail($id);
        $this->resetValidation();
        $this->editingId = $s->id;
        $this->form_cn_number = $s->cn_number;
        $this->form_shipment_date = $s->shipment_date;
        $this->form_customer_id = (string) ($s->customer_id ?? '');
        $this->form_customer_name = $s->customer_name;
        $this->form_save_customer = false;
        $this->form_shipper_address = (string) ($s->shipper_address ?? '');
        $this->form_receiver_address = (string) ($s->receiver_address ?? '');
        $this->form_colly = $s->colly;
        $this->form_weight_kg = $s->weight_kg;
        $this->form_volume_m3 = $s->volume_m3;
        $this->form_area_m2 = $s->area_m2;
        $this->form_fleet_id = (string) ($s->fleet_id ?? '');
        $this->form_fleet_name = (string) ($s->fleet_name ?? '');
        $this->form_pickup_time = (string) ($s->pickup_time ?? '');
        $this->form_service_cost = $s->service_cost;
        $this->form_payment_status = $s->payment_status ?: 'Belum Lunas';
        $this->form_status_1 = (string) ($s->status_1_at ?? '');
        $this->form_status_2 = (string) ($s->status_2_at ?? '');
        $this->form_status_3 = (string) ($s->status_3_at ?? '');
        $this->form_status_4 = (string) ($s->status_4_at ?? '');
        $this->form_status_5 = (string) ($s->status_5_at ?? '');
        $this->form_pod_receiver_name = (string) ($s->pod_receiver_name ?? '');
        $this->form_pod_received_at = (string) ($s->pod_received_at ?? '');
        $this->form_pod_notes = (string) ($s->pod_notes ?? '');
        $this->form_notes = (string) ($s->notes ?? '');
        $this->showFormModal = true;
    }

    public function saveShipment(ShipmentDomainService $domain): void
    {
        $uniqueRule = $this->editingId
            ? 'unique:shipments,cn_number,'.$this->editingId
            : 'unique:shipments,cn_number';

        $this->validate([
            'form_cn_number' => ['required', 'string', 'max:60', $uniqueRule],
            'form_shipment_date' => ['required', 'string', 'max:20'],
            'form_customer_name' => ['required', 'string', 'max:255'],
            'form_colly' => ['nullable', 'numeric', 'min:0'],
            'form_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'form_volume_m3' => ['nullable', 'numeric', 'min:0'],
            'form_area_m2' => ['nullable', 'numeric', 'min:0'],
            'form_service_cost' => ['nullable', 'numeric', 'min:0'],
        ], [
            'form_cn_number.required' => 'Nomor CN wajib diisi.',
            'form_cn_number.unique' => 'Nomor CN sudah terdaftar.',
            'form_customer_name.required' => 'Nama customer wajib diisi.',
        ]);

        $actor = Auth::user()->full_name;

        DB::transaction(function () use ($domain, $actor) {
            $custId = $this->form_customer_id !== '' ? (int) $this->form_customer_id : null;
            $customer = $domain->resolveOrCreateCustomer(
                $custId,
                $this->form_customer_name,
                $this->form_shipper_address,
                $this->form_save_customer
            );

            $fleetId = $this->form_fleet_id !== '' ? (int) $this->form_fleet_id : null;
            $fleetName = trim($this->form_fleet_name);
            if ($fleetId && $fleetName === '') {
                $fleet = Fleet::find($fleetId);
                if ($fleet) {
                    $fleetName = "{$fleet->vehicle_type} ({$fleet->plate_number})";
                }
            }

            $podReceiver = trim($this->form_pod_receiver_name) ?: null;
            $podReceivedAt = trim($this->form_pod_received_at) ?: ($podReceiver ? $domain->localDateTimeStr() : null);
            $podNotes = trim($this->form_pod_notes) ?: null;
            $status5 = trim($this->form_status_5) ?: null;
            if (! $status5 && $podReceiver) {
                $status5 = $domain->shortWibTimestamp()." - Diterima oleh {$podReceiver}";
            }

            $data = [
                'cn_number' => strtoupper(trim($this->form_cn_number)),
                'shipment_date' => trim($this->form_shipment_date) ?: $domain->localIsoDate(),
                'customer_id' => $customer?->id,
                'customer_name' => $customer?->company_name ?? trim($this->form_customer_name),
                'shipper_address' => trim($this->form_shipper_address) ?: null,
                'receiver_address' => trim($this->form_receiver_address) ?: null,
                'colly' => (int) ($this->form_colly ?: 0),
                'weight_kg' => (float) ($this->form_weight_kg ?: 0),
                'volume_m3' => (float) ($this->form_volume_m3 ?: 0),
                'area_m2' => (float) ($this->form_area_m2 ?: 0),
                'fleet_id' => $fleetId,
                'fleet_name' => $fleetName ?: null,
                'pickup_time' => trim($this->form_pickup_time) ?: null,
                'service_cost' => (float) ($this->form_service_cost ?: 0),
                'payment_status' => $this->form_payment_status ?: 'Belum Lunas',
                'status_1_at' => trim($this->form_status_1) ?: null,
                'status_2_at' => trim($this->form_status_2) ?: null,
                'status_3_at' => trim($this->form_status_3) ?: null,
                'status_4_at' => trim($this->form_status_4) ?: null,
                'status_5_at' => $status5,
                'pod_receiver_name' => $podReceiver,
                'pod_received_at' => $podReceivedAt,
                'pod_notes' => $podNotes,
                'notes' => trim($this->form_notes) ?: null,
            ];
            $data['current_stage'] = $domain->computeCurrentStage($data);

            if ($this->editingId) {
                $shipment = Shipment::findOrFail($this->editingId);
                $shipment->update($data);
                TrackingLog::create([
                    'shipment_id' => $shipment->id,
                    'stage_number' => $shipment->current_stage ?: null,
                    'stage_label' => 'Edit Data Pengiriman',
                    'description' => "Data CN {$shipment->cn_number} diperbarui oleh {$actor}",
                    'updated_by' => $actor,
                    'created_at' => now('Asia/Jakarta'),
                ]);
                $msg = "Pengiriman {$shipment->cn_number} berhasil diperbarui.";
            } else {
                $shipment = Shipment::create($data);
                TrackingLog::create([
                    'shipment_id' => $shipment->id,
                    'stage_number' => $shipment->current_stage ?: null,
                    'stage_label' => 'Input CN Baru',
                    'description' => "CN {$shipment->cn_number} dibuat untuk {$shipment->customer_name}",
                    'updated_by' => $actor,
                    'created_at' => now('Asia/Jakarta'),
                ]);
                $msg = "Pengiriman {$shipment->cn_number} berhasil ditambahkan.";
            }

            $this->showFormModal = false;
            $this->dispatch('toast', message: $msg, type: 'success');
        });
    }

    public function deleteShipment(int $id, ShipmentDomainService $domain): void
    {
        if (! Auth::user()->isOwner()) {
            $this->dispatch('toast', message: 'Hanya Owner yang dapat menghapus pengiriman.', type: 'error');

            return;
        }

        $shipment = Shipment::findOrFail($id);
        $cn = $shipment->cn_number;
        $domain->deleteShipment($shipment);
        $this->dispatch('toast', message: "Pengiriman {$cn} beserta foto POD berhasil dihapus.", type: 'success');
    }

    public function openStageModal(int $id, int $stageNum): void
    {
        $s = Shipment::findOrFail($id);
        $this->stageShipmentId = $s->id;
        $this->stageShipmentCn = $s->cn_number;
        $this->stageNumber = max(1, min(5, $stageNum));
        $this->stageValue = (string) ($s->{"status_{$this->stageNumber}_at"} ?? '');
        $this->showStageModal = true;
    }

    public function fillStageTemplate(string $template, ShipmentDomainService $domain): void
    {
        $this->stageValue = $domain->shortWibTimestamp().' - '.$template;
    }

    public function saveStage(ShipmentDomainService $domain): void
    {
        $s = Shipment::findOrFail($this->stageShipmentId);
        $domain->updateShipmentStage($s, $this->stageNumber, $this->stageValue, Auth::user()->full_name);
        $this->showStageModal = false;
        $this->dispatch('toast', message: "Status {$this->stageNumber} untuk {$s->cn_number} diperbarui.", type: 'success');
    }

    public function clearStage(ShipmentDomainService $domain): void
    {
        $s = Shipment::findOrFail($this->stageShipmentId);
        $domain->updateShipmentStage($s, $this->stageNumber, null, Auth::user()->full_name);
        $this->showStageModal = false;
        $this->dispatch('toast', message: "Status {$this->stageNumber} untuk {$s->cn_number} dikosongkan.", type: 'info');
    }

    public function openPodModal(int $id, ShipmentDomainService $domain): void
    {
        $s = Shipment::findOrFail($id);
        $this->resetValidation();
        $this->podShipmentId = $s->id;
        $this->podShipmentCn = $s->cn_number;
        $this->pod_receiver_name = (string) ($s->pod_receiver_name ?? '');
        $this->pod_received_at = (string) ($s->pod_received_at ?: $domain->localDateTimeStr());
        $this->pod_notes = (string) ($s->pod_notes ?? '');
        $this->pod_existing_photo_url = $s->pod_photo_url;
        $this->pod_photo_file = null;
        $this->pod_signature_data = (string) ($s->pod_signature_data ?? '');
        $this->pod_clear_signature = false;
        $this->showPodModal = true;
    }

    public function savePod(ShipmentDomainService $domain): void
    {
        $this->validate([
            'pod_receiver_name' => ['required', 'string', 'max:150'],
            'pod_photo_file' => ['nullable', 'image', 'max:5120'],
        ], [
            'pod_receiver_name.required' => 'Nama penerima barang wajib diisi.',
            'pod_photo_file.image' => 'File POD harus berupa gambar (JPG/PNG/WEBP).',
            'pod_photo_file.max' => 'Ukuran foto POD maksimal 5MB.',
        ]);

        $s = Shipment::findOrFail($this->podShipmentId);
        $domain->updateShipmentPod(
            $s,
            [
                'pod_receiver_name' => $this->pod_receiver_name,
                'pod_received_at' => $this->pod_received_at,
                'pod_notes' => $this->pod_notes,
                'pod_signature_data' => $this->pod_signature_data,
                'clear_signature' => $this->pod_clear_signature,
            ],
            $this->pod_photo_file,
            Auth::user()->full_name
        );

        $this->pod_photo_file = null;
        $this->showPodModal = false;
        $this->dispatch('toast', message: "POD & Status 5 untuk {$s->cn_number} tersimpan.", type: 'success');
    }

    public function openPricingModal(int $id): void
    {
        $s = Shipment::findOrFail($id);
        $this->pricingShipmentId = $s->id;
        $this->pricingShipmentCn = $s->cn_number;
        $this->pricing_service_cost = $s->service_cost;
        $this->pricing_payment_status = $s->payment_status ?: 'Belum Lunas';
        $this->showPricingModal = true;
    }

    public function savePricing(): void
    {
        $this->validate([
            'pricing_service_cost' => ['required', 'numeric', 'min:0'],
            'pricing_payment_status' => ['required', 'string', 'max:50'],
        ]);

        $s = Shipment::findOrFail($this->pricingShipmentId);
        $s->service_cost = (float) $this->pricing_service_cost;
        $s->payment_status = $this->pricing_payment_status;
        $s->save();

        TrackingLog::create([
            'shipment_id' => $s->id,
            'stage_number' => $s->current_stage ?: null,
            'stage_label' => 'Update Harga & Pembayaran',
            'description' => 'Harga: Rp '.number_format($s->service_cost, 0, ',', '.')." | Status: {$s->payment_status}",
            'updated_by' => Auth::user()->full_name,
            'created_at' => now('Asia/Jakarta'),
        ]);

        $this->showPricingModal = false;
        $this->dispatch('toast', message: "Harga & status bayar {$s->cn_number} diperbarui.", type: 'success');
    }

    public function openImportModal(): void
    {
        $this->csvText = '';
        $this->csvFile = null;
        $this->showImportModal = true;
    }

    public function runCsvImport(CsvService $csv, ShipmentDomainService $domain): void
    {
        if (trim($this->csvText) === '') {
            $this->dispatch('toast', message: 'Tempel data CSV atau pilih file .csv terlebih dahulu.', type: 'error');

            return;
        }

        $res = $csv->importCsv($this->csvText, $domain, Auth::user()->full_name);
        $this->showImportModal = false;
        $this->csvText = '';
        $this->csvFile = null;

        $this->dispatch(
            'toast',
            message: "Import CSV selesai: {$res['inserted']} baru, {$res['updated']} diperbarui, {$res['skipped']} dilewati.",
            type: 'success'
        );
    }

    public function openPrintModal(int $id): void
    {
        $s = Shipment::findOrFail($id);
        $this->printData = $s->toArray();
        $this->includePriceInPrint = true;
        $this->showPrintModal = true;
    }

    public function render(ShipmentDomainService $domain)
    {
        $filters = [
            'search' => $this->search,
            'stage' => $this->stage,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'payment_status' => $this->paymentStatus,
        ];

        $shipments = $domain->filterShipmentsQuery($filters)->get();

        $baseForCounts = $domain->filterShipmentsQuery([
            'search' => $this->search,
            'stage' => 'all',
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'payment_status' => $this->paymentStatus,
        ])->get();

        $pipelineStats = $domain->computeStats($baseForCounts);
        $filteredStats = $domain->computeStats($shipments);

        return view('livewire.shipment-board', [
            'shipments' => $shipments,
            'stats' => $filteredStats,
            'pipelineStats' => $pipelineStats,
            'customers' => Customer::query()->orderBy('company_name')->get(),
            'fleets' => Fleet::query()->orderBy('fleet_code')->get(),
            'company' => CompanySetting::query()->first(),
            'paymentOptions' => ShipmentDomainService::PAYMENT_STATUS_OPTIONS,
            'stageLabels' => ShipmentDomainService::STAGE_LABELS,
            'exportParams' => array_filter([
                'search' => $this->search,
                'stage' => $this->stage !== 'all' ? $this->stage : null,
                'date_from' => $this->dateFrom,
                'date_to' => $this->dateTo,
                'payment_status' => $this->paymentStatus !== 'all' ? $this->paymentStatus : null,
            ]),
        ]);
    }
}
