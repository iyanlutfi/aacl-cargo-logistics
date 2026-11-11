<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Fleet;
use App\Models\Shipment;
use App\Services\ShipmentDomainService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Master Customer & Armada — AACL Cargo & Logistics')]
class MasterData extends Component
{
    public string $activeTab = 'customers'; // customers | fleets

    public string $search = '';

    // Customer Form Modal
    public bool $showCustomerModal = false;

    public ?int $editingCustomerId = null;

    public string $cust_code = '';

    public string $cust_company_name = '';

    public string $cust_pic_name = '';

    public string $cust_phone = '';

    public string $cust_default_address = '';

    public string $cust_notes = '';

    // Fleet Form Modal
    public bool $showFleetModal = false;

    public ?int $editingFleetId = null;

    public string $fleet_code = '';

    public string $fleet_plate_number = '';

    public string $fleet_vehicle_type = '';

    public string $fleet_driver_name = '';

    public string $fleet_driver_phone = '';

    public string $fleet_status = 'available';

    public function closeAllModals(): void
    {
        $this->showCustomerModal = false;
        $this->showFleetModal = false;
    }

    public function openCreateCustomer(ShipmentDomainService $domain): void
    {
        $this->resetValidation();
        $this->editingCustomerId = null;
        $this->cust_code = $domain->generateNextCustomerCode();
        $this->cust_company_name = '';
        $this->cust_pic_name = '';
        $this->cust_phone = '';
        $this->cust_default_address = '';
        $this->cust_notes = '';
        $this->showCustomerModal = true;
    }

    public function openEditCustomer(int $id): void
    {
        $c = Customer::findOrFail($id);
        $this->resetValidation();
        $this->editingCustomerId = $c->id;
        $this->cust_code = $c->customer_code;
        $this->cust_company_name = $c->company_name;
        $this->cust_pic_name = (string) ($c->pic_name ?? '');
        $this->cust_phone = (string) ($c->phone ?? '');
        $this->cust_default_address = (string) ($c->default_address ?? '');
        $this->cust_notes = (string) ($c->notes ?? '');
        $this->showCustomerModal = true;
    }

    public function saveCustomer(): void
    {
        $uniqueCode = $this->editingCustomerId
            ? 'unique:customers,customer_code,'.$this->editingCustomerId
            : 'unique:customers,customer_code';

        $this->validate([
            'cust_code' => ['required', 'string', 'max:50', $uniqueCode],
            'cust_company_name' => ['required', 'string', 'max:255'],
        ], [
            'cust_code.required' => 'Kode customer wajib diisi.',
            'cust_code.unique' => 'Kode customer sudah digunakan.',
            'cust_company_name.required' => 'Nama perusahaan / customer wajib diisi.',
        ]);

        $payload = [
            'customer_code' => strtoupper(trim($this->cust_code)),
            'company_name' => trim($this->cust_company_name),
            'pic_name' => trim($this->cust_pic_name) ?: null,
            'phone' => trim($this->cust_phone) ?: null,
            'default_address' => trim($this->cust_default_address) ?: null,
            'notes' => trim($this->cust_notes) ?: null,
        ];

        if ($this->editingCustomerId) {
            $cust = Customer::findOrFail($this->editingCustomerId);
            $cust->update($payload);
            // Sync denormalized customer_name on linked shipments
            Shipment::where('customer_id', $cust->id)->update(['customer_name' => $cust->company_name]);
            $msg = "Customer {$payload['company_name']} diperbarui.";
        } else {
            Customer::create($payload);
            $msg = "Customer {$payload['company_name']} ditambahkan.";
        }

        $this->showCustomerModal = false;
        $this->dispatch('toast', message: $msg, type: 'success');
    }

    public function deleteCustomer(int $id): void
    {
        if (! Auth::user()->isOwner()) {
            $this->dispatch('toast', message: 'Hanya Owner yang diizinkan menghapus Master Customer.', type: 'error');

            return;
        }

        $c = Customer::findOrFail($id);
        $name = $c->company_name;
        $c->delete();
        $this->dispatch('toast', message: "Customer {$name} dihapus.", type: 'success');
    }

    public function openCreateFleet(ShipmentDomainService $domain): void
    {
        $this->resetValidation();
        $this->editingFleetId = null;
        $this->fleet_code = $domain->generateNextFleetCode();
        $this->fleet_plate_number = '';
        $this->fleet_vehicle_type = '';
        $this->fleet_driver_name = '';
        $this->fleet_driver_phone = '';
        $this->fleet_status = 'available';
        $this->showFleetModal = true;
    }

    public function openEditFleet(int $id): void
    {
        $f = Fleet::findOrFail($id);
        $this->resetValidation();
        $this->editingFleetId = $f->id;
        $this->fleet_code = $f->fleet_code;
        $this->fleet_plate_number = $f->plate_number;
        $this->fleet_vehicle_type = $f->vehicle_type;
        $this->fleet_driver_name = (string) ($f->driver_name ?? '');
        $this->fleet_driver_phone = (string) ($f->driver_phone ?? '');
        $this->fleet_status = $f->status ?: 'available';
        $this->showFleetModal = true;
    }

    public function saveFleet(): void
    {
        $uniqueCode = $this->editingFleetId
            ? 'unique:fleets,fleet_code,'.$this->editingFleetId
            : 'unique:fleets,fleet_code';
        $uniquePlate = $this->editingFleetId
            ? 'unique:fleets,plate_number,'.$this->editingFleetId
            : 'unique:fleets,plate_number';

        $this->validate([
            'fleet_code' => ['required', 'string', 'max:50', $uniqueCode],
            'fleet_plate_number' => ['required', 'string', 'max:50', $uniquePlate],
            'fleet_vehicle_type' => ['required', 'string', 'max:100'],
        ], [
            'fleet_plate_number.required' => 'Nomor polisi kendaraan wajib diisi.',
            'fleet_plate_number.unique' => 'Nomor polisi sudah terdaftar.',
            'fleet_vehicle_type.required' => 'Tipe armada wajib diisi.',
        ]);

        $payload = [
            'fleet_code' => strtoupper(trim($this->fleet_code)),
            'plate_number' => strtoupper(trim($this->fleet_plate_number)),
            'vehicle_type' => trim($this->fleet_vehicle_type),
            'driver_name' => trim($this->fleet_driver_name) ?: null,
            'driver_phone' => trim($this->fleet_driver_phone) ?: null,
            'status' => $this->fleet_status ?: 'available',
        ];

        if ($this->editingFleetId) {
            $fleet = Fleet::findOrFail($this->editingFleetId);
            $fleet->update($payload);
            Shipment::where('fleet_id', $fleet->id)->update([
                'fleet_name' => "{$fleet->vehicle_type} ({$fleet->plate_number})",
            ]);
            $msg = "Armada {$payload['plate_number']} diperbarui.";
        } else {
            Fleet::create($payload);
            $msg = "Armada {$payload['plate_number']} ditambahkan.";
        }

        $this->showFleetModal = false;
        $this->dispatch('toast', message: $msg, type: 'success');
    }

    public function deleteFleet(int $id): void
    {
        if (! Auth::user()->isOwner()) {
            $this->dispatch('toast', message: 'Hanya Owner yang diizinkan menghapus Armada.', type: 'error');

            return;
        }

        $f = Fleet::findOrFail($id);
        $plate = $f->plate_number;
        $f->delete();
        $this->dispatch('toast', message: "Armada {$plate} dihapus.", type: 'success');
    }

    public function render()
    {
        $custQuery = Customer::query()->orderBy('customer_code');
        $fleetQuery = Fleet::query()->orderBy('fleet_code');

        $q = trim($this->search);
        if ($q !== '') {
            $like = '%'.$q.'%';
            $custQuery->where(function ($sub) use ($like) {
                $sub->where('customer_code', 'like', $like)
                    ->orWhere('company_name', 'like', $like)
                    ->orWhere('pic_name', 'like', $like)
                    ->orWhere('default_address', 'like', $like);
            });
            $fleetQuery->where(function ($sub) use ($like) {
                $sub->where('fleet_code', 'like', $like)
                    ->orWhere('plate_number', 'like', $like)
                    ->orWhere('vehicle_type', 'like', $like)
                    ->orWhere('driver_name', 'like', $like);
            });
        }

        return view('livewire.master-data', [
            'customers' => $custQuery->get(),
            'fleets' => $fleetQuery->get(),
        ]);
    }
}
