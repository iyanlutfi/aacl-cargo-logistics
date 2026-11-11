<div class="space-y-5" x-data @keydown.escape.window="$wire.closeAllModals()">
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-extrabold text-[#0F2744]">Master Data Customer &amp; Armada</h1>
                <p class="text-xs text-slate-500">Kelola database pelanggan tetap dan armada pengiriman AACL Cargo.</p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" wire:click="$set('activeTab', 'customers')"
                        class="px-4 py-2 rounded-xl text-xs font-bold cursor-pointer {{ $activeTab === 'customers' ? 'bg-[#0F2744] text-white' : 'bg-slate-100 text-slate-700' }}">
                    Customers ({{ $customers->count() }})
                </button>
                <button type="button" wire:click="$set('activeTab', 'fleets')"
                        class="px-4 py-2 rounded-xl text-xs font-bold cursor-pointer {{ $activeTab === 'fleets' ? 'bg-[#0F2744] text-white' : 'bg-slate-100 text-slate-700' }}">
                    Armada ({{ $fleets->count() }})
                </button>
            </div>
        </div>

        <div>
            <input type="text" wire:model.live.debounce.250ms="search"
                   placeholder="Cari nama customer, kode, PIC, nopol armada, atau nama driver..."
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 outline-none">
        </div>
    </div>

    @if($activeTab === 'customers')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-sm font-extrabold text-slate-900">Daftar Customer</h2>
                <button type="button" wire:click="openCreateCustomer"
                        class="px-3.5 py-2 rounded-xl bg-[#0F2744] hover:bg-[#17375e] text-white text-xs font-bold cursor-pointer">
                    + Tambah Customer
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-bold border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left">Kode</th>
                            <th class="px-4 py-3 text-left">Nama Perusahaan</th>
                            <th class="px-4 py-3 text-left">PIC</th>
                            <th class="px-4 py-3 text-left">Telepon</th>
                            <th class="px-4 py-3 text-left">Alamat Default</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($customers as $c)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-extrabold text-[#0F2744]">{{ $c->customer_code }}</td>
                                <td class="px-4 py-3 font-bold text-slate-900">{{ $c->company_name }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $c->pic_name ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $c->phone ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $c->default_address ?: '-' }}</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <button type="button" wire:click="openEditCustomer({{ $c->id }})" class="px-2.5 py-1 rounded-lg bg-sky-100 text-sky-900 font-bold cursor-pointer">Edit</button>
                                    @if(auth()->user()?->isOwner())
                                        <button type="button" wire:click="deleteCustomer({{ $c->id }})" wire:confirm="Hapus customer {{ $c->company_name }}?" class="ml-1 px-2.5 py-1 rounded-lg bg-rose-100 text-rose-800 font-bold cursor-pointer">Hapus</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">Tidak ada customer ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-sm font-extrabold text-slate-900">Daftar Armada</h2>
                <button type="button" wire:click="openCreateFleet"
                        class="px-3.5 py-2 rounded-xl bg-[#0F2744] hover:bg-[#17375e] text-white text-xs font-bold cursor-pointer">
                    + Tambah Armada
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-bold border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left">Kode</th>
                            <th class="px-4 py-3 text-left">No. Polisi</th>
                            <th class="px-4 py-3 text-left">Tipe Kendaraan</th>
                            <th class="px-4 py-3 text-left">Driver Tetap</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($fleets as $f)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-extrabold text-[#0F2744]">{{ $f->fleet_code }}</td>
                                <td class="px-4 py-3 font-bold text-slate-900">{{ $f->plate_number }}</td>
                                <td class="px-4 py-3 text-slate-800">{{ $f->vehicle_type }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $f->driver_name ?: '-' }} ({{ $f->driver_phone ?: '-' }})</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $f->status === 'available' ? 'bg-emerald-100 text-emerald-800' : ($f->status === 'on_trip' ? 'bg-sky-100 text-sky-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ strtoupper($f->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <button type="button" wire:click="openEditFleet({{ $f->id }})" class="px-2.5 py-1 rounded-lg bg-sky-100 text-sky-900 font-bold cursor-pointer">Edit</button>
                                    @if(auth()->user()?->isOwner())
                                        <button type="button" wire:click="deleteFleet({{ $f->id }})" wire:confirm="Hapus armada {{ $f->plate_number }}?" class="ml-1 px-2.5 py-1 rounded-lg bg-rose-100 text-rose-800 font-bold cursor-pointer">Hapus</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">Tidak ada armada ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Customer Modal --}}
    @if($showCustomerModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg max-h-[92vh] flex flex-col overflow-hidden">
                <div class="bg-[#0F2744] px-5 py-4 text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold">{{ $editingCustomerId ? 'Edit Customer' : 'Tambah Customer Baru' }}</h3>
                    <button type="button" wire:click="$set('showCustomerModal', false)" class="text-slate-300 hover:text-white text-xl cursor-pointer">&times;</button>
                </div>
                <form wire:submit="saveCustomer" class="p-5 space-y-3 overflow-y-auto">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kode *</label>
                            <input type="text" wire:model="cust_code" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm uppercase">
                            @error('cust_code') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Telepon</label>
                            <input type="text" wire:model="cust_phone" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Perusahaan *</label>
                        <input type="text" wire:model="cust_company_name" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        @error('cust_company_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama PIC</label>
                        <input type="text" wire:model="cust_pic_name" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Default</label>
                        <textarea wire:model="cust_default_address" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan</label>
                        <textarea wire:model="cust_notes" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showCustomerModal', false)" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-[#0F2744] text-white text-xs font-bold cursor-pointer">Simpan Customer</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Fleet Modal --}}
    @if($showFleetModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg max-h-[92vh] flex flex-col overflow-hidden">
                <div class="bg-[#0F2744] px-5 py-4 text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold">{{ $editingFleetId ? 'Edit Armada' : 'Tambah Armada Baru' }}</h3>
                    <button type="button" wire:click="$set('showFleetModal', false)" class="text-slate-300 hover:text-white text-xl cursor-pointer">&times;</button>
                </div>
                <form wire:submit="saveFleet" class="p-5 space-y-3 overflow-y-auto">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kode *</label>
                            <input type="text" wire:model="fleet_code" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm uppercase">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Polisi *</label>
                            <input type="text" wire:model="fleet_plate_number" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm uppercase">
                            @error('fleet_plate_number') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Kendaraan *</label>
                        <input type="text" wire:model="fleet_vehicle_type" placeholder="Contoh: CDD Box Long" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Driver Tetap</label>
                            <input type="text" wire:model="fleet_driver_name" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">HP Driver</label>
                            <input type="text" wire:model="fleet_driver_phone" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Armada</label>
                        <select wire:model="fleet_status" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                            <option value="available">Available (Siap Jalan)</option>
                            <option value="on_trip">On Trip (Sedang Jalan)</option>
                            <option value="maintenance">Maintenance (Servis)</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showFleetModal', false)" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-[#0F2744] text-white text-xs font-bold cursor-pointer">Simpan Armada</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
