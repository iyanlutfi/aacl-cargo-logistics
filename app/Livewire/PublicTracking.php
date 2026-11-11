<?php

namespace App\Livewire;

use App\Models\CompanySetting;
use App\Models\Shipment;
use App\Services\ShipmentDomainService;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Cek Resi Publik — AACL Cargo & Logistics')]
class PublicTracking extends Component
{
    #[Url(as: 'cn')]
    public string $cnQuery = '';

    public ?array $result = null;

    public bool $searched = false;

    public function mount(ShipmentDomainService $domain): void
    {
        if (trim($this->cnQuery) !== '') {
            $this->searchCn($domain);
        }
    }

    public function quickSelectCn(string $cn, ShipmentDomainService $domain): void
    {
        $this->cnQuery = $cn;
        $this->searchCn($domain);
    }

    public function searchCn(ShipmentDomainService $domain): void
    {
        $cn = strtoupper(trim($this->cnQuery));
        $this->cnQuery = $cn;
        $this->searched = true;

        if ($cn === '') {
            $this->result = null;

            return;
        }

        $shipment = Shipment::query()
            ->whereRaw('UPPER(cn_number) = ?', [$cn])
            ->first();

        $this->result = $shipment ? $domain->sanitizePublicTracking($shipment) : null;
    }

    public function render()
    {
        $company = CompanySetting::query()->first();

        return view('livewire.public-tracking', [
            'company' => $company,
            'sampleCns' => Shipment::query()
                ->select(['cn_number', 'current_stage'])
                ->orderBy('cn_number')
                ->limit(5)
                ->get(),
        ]);
    }
}
