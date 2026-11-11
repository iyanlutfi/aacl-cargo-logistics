<div class="space-y-6" x-data @keydown.escape.window="$wire.set('showUserModal', false)">
    {{-- Employee Management --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-extrabold text-[#0F2744]">Manajemen Akun &amp; Role Karyawan (Khusus Owner)</h1>
                <p class="text-xs text-slate-500">Perubahan role atau penghapusan akun langsung berlaku secara real-time di sesi berikutnya.</p>
            </div>
            <button type="button" wire:click="openCreateUser"
                    class="px-4 py-2 rounded-xl bg-[#0F2744] hover:bg-[#17375e] text-white text-xs font-bold cursor-pointer">
                + Tambah Karyawan
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-left">Username</th>
                        <th class="px-4 py-3 text-left">Nama Lengkap</th>
                        <th class="px-4 py-3 text-center">Role</th>
                        <th class="px-4 py-3 text-left">Telepon</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($users as $u)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-extrabold text-[#0F2744]">{{ $u->username }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $u->full_name }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $u->role === 'owner' ? 'bg-amber-100 text-amber-900' : ($u->role === 'admin' ? 'bg-sky-100 text-sky-900' : 'bg-emerald-100 text-emerald-900') }}">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $u->phone ?: '-' }}</td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <button type="button" wire:click="openEditUser({{ $u->id }})" class="px-2.5 py-1 rounded-lg bg-sky-100 text-sky-900 font-bold cursor-pointer">Edit</button>
                                @if(!$u->isPrimaryOwner() && $u->id !== auth()->id())
                                    <button type="button" wire:click="deleteUser({{ $u->id }})" wire:confirm="Hapus akun {{ $u->full_name }}?" class="ml-1 px-2.5 py-1 rounded-lg bg-rose-100 text-rose-800 font-bold cursor-pointer">Hapus</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Company Settings --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
        <div>
            <h2 class="text-base font-extrabold text-[#0F2744]">Profil Perusahaan &amp; Prefix Resi CN</h2>
            <p class="text-xs text-slate-500">Digunakan di kop Surat Jalan, Laporan Excel, dan penomoran otomatis CN.</p>
        </div>

        <form wire:submit="saveCompanySettings" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Perusahaan *</label>
                <input type="text" wire:model="company_name" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Prefix No. CN *</label>
                <input type="text" wire:model="cn_prefix" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm uppercase">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Telepon</label>
                <input type="text" wire:model="company_phone" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                <input type="email" wire:model="company_email" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Kantor / Gudang</label>
                <input type="text" wire:model="company_address" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
            <div class="sm:col-span-2 flex justify-end">
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#0F2744] hover:bg-[#17375e] text-white text-xs font-bold cursor-pointer">
                    Simpan Profil Perusahaan
                </button>
            </div>
        </form>
    </div>

    {{-- User Modal --}}
    @if($showUserModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden">
                <div class="bg-[#0F2744] px-5 py-4 text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold">{{ $editingUserId ? 'Edit Akun Karyawan' : 'Tambah Karyawan Baru' }}</h3>
                    <button type="button" wire:click="$set('showUserModal', false)" class="text-slate-300 hover:text-white text-xl cursor-pointer">&times;</button>
                </div>
                <form wire:submit="saveUser" class="p-5 space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Username *</label>
                        <input type="text" wire:model="u_username" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        @error('u_username') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                        <input type="text" wire:model="u_full_name" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        @error('u_full_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Role *</label>
                            <select wire:model="u_role" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                <option value="driver">Driver / Kurir</option>
                                <option value="admin">Admin Operasional</option>
                                <option value="owner">Owner</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. HP</label>
                            <input type="text" wire:model="u_phone" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Password {{ $editingUserId ? '(Kosongkan jika tidak diubah)' : '*' }}
                        </label>
                        <input type="password" wire:model="u_password" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        @error('u_password') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showUserModal', false)" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-[#0F2744] text-white text-xs font-bold cursor-pointer">Simpan Akun</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
