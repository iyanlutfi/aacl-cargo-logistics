<?php

namespace App\Livewire;

use App\Models\Shipment;
use App\Services\ShipmentDomainService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Panel Kurir Lapangan — AACL Cargo & Logistics')]
class DriverPanel extends Component
{
    use WithFileUploads;

    public string $search = '';

    public string $statusFilter = 'pending'; // all | pending | completed

    // Quick Stage Modal
    public bool $showStageModal = false;

    public ?int $stageShipmentId = null;

    public string $stageShipmentCn = '';

    public int $stageNumber = 1;

    public string $stageValue = '';

    // Quick POD Modal
    public bool $showPodModal = false;

    public ?int $podShipmentId = null;

    public string $podShipmentCn = '';

    public string $pod_receiver_name = '';

    public string $pod_received_at = '';

    public string $pod_notes = '';

    public ?string $pod_existing_photo_url = null;

    public $pod_photo_file = null;

    public string $pod_signature_data = '';

    public bool $pod_clear_signature = false;

    public function setStatusFilter(string $filter): void
    {
        $this->statusFilter = $filter;
    }

    public function closeAllModals(): void
    {
        $this->showStageModal = false;
        $this->showPodModal = false;
    }

    public function quickAdvanceStage(int $shipmentId, int $stage, ShipmentDomainService $domain): void
    {
        $s = Shipment::findOrFail($shipmentId);
        $defaultTexts = [
            1 => 'Barang sudah dijemput kurir',
            2 => 'Barang tiba di Hub Sortir AACL',
            3 => 'Dalam perjalanan menuju tujuan',
            4 => 'Kurir menuju alamat penerima',
        ];

        $text = $domain->shortWibTimestamp().' - '.($defaultTexts[$stage] ?? "Update Stage {$stage}");
        $domain->updateShipmentStage($s, $stage, $text, Auth::user()->full_name);

        $this->dispatch('toast', message: "{$s->cn_number} ditandai Status {$stage}.", type: 'success');
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
        $this->dispatch('toast', message: "Status {$this->stageNumber} untuk {$s->cn_number} disimpan.", type: 'success');
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
            'pod_photo_file.image' => 'Foto POD harus berupa file gambar.',
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
        $this->dispatch('toast', message: "POD {$s->cn_number} tersimpan & Status 5 Selesai.", type: 'success');
    }

    public function render()
    {
        // Deliberately exclude financial fields (service_cost, payment_status) from driver query
        $query = Shipment::query()
            ->select([
                'id', 'cn_number', 'shipment_date', 'customer_name',
                'shipper_address', 'receiver_address', 'colly', 'weight_kg',
                'volume_m3', 'area_m2', 'fleet_name', 'pickup_time',
                'status_1_at', 'status_2_at', 'status_3_at', 'status_4_at', 'status_5_at',
                'pod_receiver_name', 'pod_received_at', 'pod_photo_url', 'pod_signature_data', 'pod_notes',
                'current_stage', 'notes',
            ])
            ->orderByDesc('shipment_date')
            ->orderByDesc('id');

        $search = trim($this->search);
        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('cn_number', 'like', $like)
                    ->orWhere('customer_name', 'like', $like)
                    ->orWhere('receiver_address', 'like', $like)
                    ->orWhere('shipper_address', 'like', $like);
            });
        }

        if ($this->statusFilter === 'pending') {
            $query->where('current_stage', '<', 5);
        } elseif ($this->statusFilter === 'completed') {
            $query->where('current_stage', '>=', 5);
        }

        $cards = $query->get();

        return view('livewire.driver-panel', [
            'cards' => $cards,
            'counts' => [
                'all' => Shipment::count(),
                'pending' => Shipment::where('current_stage', '<', 5)->count(),
                'completed' => Shipment::where('current_stage', '>=', 5)->count(),
            ],
        ]);
    }
}
