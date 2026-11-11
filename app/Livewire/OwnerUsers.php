<?php

namespace App\Livewire;

use App\Models\CompanySetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Panel Owner & Karyawan — AACL Cargo & Logistics')]
class OwnerUsers extends Component
{
    // Company Profile Settings
    public string $company_name = '';

    public string $company_tagline = '';

    public string $company_address = '';

    public string $company_phone = '';

    public string $company_email = '';

    public string $cn_prefix = 'AACL';

    // Employee Modal
    public bool $showUserModal = false;

    public ?int $editingUserId = null;

    public string $u_username = '';

    public string $u_full_name = '';

    public string $u_role = 'driver';

    public string $u_phone = '';

    public string $u_password = '';

    public function mount(): void
    {
        $c = CompanySetting::query()->first();
        if ($c) {
            $this->company_name = (string) $c->company_name;
            $this->company_tagline = (string) ($c->company_tagline ?? '');
            $this->company_address = (string) ($c->company_address ?? '');
            $this->company_phone = (string) ($c->company_phone ?? '');
            $this->company_email = (string) ($c->company_email ?? '');
            $this->cn_prefix = (string) ($c->cn_prefix ?: 'AACL');
        }
    }

    public function saveCompanySettings(): void
    {
        if (! Auth::user()->isOwner()) {
            abort(403);
        }

        $this->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'cn_prefix' => ['required', 'string', 'max:15'],
        ]);

        $c = CompanySetting::query()->first() ?: new CompanySetting;
        $c->fill([
            'company_name' => trim($this->company_name),
            'company_tagline' => trim($this->company_tagline) ?: null,
            'company_address' => trim($this->company_address) ?: null,
            'company_phone' => trim($this->company_phone) ?: null,
            'company_email' => trim($this->company_email) ?: null,
            'cn_prefix' => strtoupper(trim($this->cn_prefix)) ?: 'AACL',
        ]);
        $c->save();

        $this->dispatch('toast', message: 'Profil perusahaan berhasil disimpan.', type: 'success');
    }

    public function openCreateUser(): void
    {
        $this->resetValidation();
        $this->editingUserId = null;
        $this->u_username = '';
        $this->u_full_name = '';
        $this->u_role = 'driver';
        $this->u_phone = '';
        $this->u_password = '';
        $this->showUserModal = true;
    }

    public function openEditUser(int $id): void
    {
        $u = User::findOrFail($id);
        $this->resetValidation();
        $this->editingUserId = $u->id;
        $this->u_username = $u->username;
        $this->u_full_name = $u->full_name;
        $this->u_role = $u->role;
        $this->u_phone = (string) ($u->phone ?? '');
        $this->u_password = '';
        $this->showUserModal = true;
    }

    public function saveUser(): void
    {
        if (! Auth::user()->isOwner()) {
            abort(403);
        }

        $uniqueUsername = $this->editingUserId
            ? 'unique:users,username,'.$this->editingUserId
            : 'unique:users,username';

        $rules = [
            'u_username' => ['required', 'string', 'max:80', 'alpha_dash', $uniqueUsername],
            'u_full_name' => ['required', 'string', 'max:150'],
            'u_role' => ['required', 'in:owner,admin,driver'],
            'u_phone' => ['nullable', 'string', 'max:50'],
            'u_password' => $this->editingUserId ? ['nullable', 'string', 'min:4'] : ['required', 'string', 'min:4'],
        ];

        $this->validate($rules, [
            'u_username.required' => 'Username wajib diisi.',
            'u_username.unique' => 'Username sudah dipakai.',
            'u_full_name.required' => 'Nama lengkap wajib diisi.',
            'u_password.required' => 'Password wajib diisi untuk akun baru.',
            'u_password.min' => 'Password minimal 4 karakter.',
        ]);

        if ($this->editingUserId) {
            $target = User::findOrFail($this->editingUserId);
            // Primary owner account cannot be demoted or renamed from 'owner'
            if ($target->isPrimaryOwner()) {
                $this->u_role = 'owner';
                $this->u_username = 'owner';
            }

            $target->username = strtolower(trim($this->u_username));
            $target->full_name = trim($this->u_full_name);
            $target->name = trim($this->u_full_name);
            $target->role = $this->u_role;
            $target->phone = trim($this->u_phone) ?: null;
            if (trim($this->u_password) !== '') {
                $target->password = Hash::make($this->u_password);
            }
            $target->save();
            $msg = "Akun {$target->username} diperbarui.";
        } else {
            $u = User::create([
                'username' => strtolower(trim($this->u_username)),
                'full_name' => trim($this->u_full_name),
                'name' => trim($this->u_full_name),
                'email' => strtolower(trim($this->u_username)).'@aacl-cargo.local',
                'role' => $this->u_role,
                'phone' => trim($this->u_phone) ?: null,
                'password' => Hash::make($this->u_password),
            ]);
            $msg = "Akun {$u->username} ({$u->role}) berhasil dibuat.";
        }

        $this->showUserModal = false;
        $this->dispatch('toast', message: $msg, type: 'success');
    }

    public function deleteUser(int $id): void
    {
        if (! Auth::user()->isOwner()) {
            abort(403);
        }

        $target = User::findOrFail($id);
        if ($target->isPrimaryOwner()) {
            $this->dispatch('toast', message: 'Akun Owner utama tidak boleh dihapus.', type: 'error');

            return;
        }
        if ($target->id === Auth::id()) {
            $this->dispatch('toast', message: 'Anda tidak dapat menghapus akun yang sedang aktif dipakai.', type: 'error');

            return;
        }

        $name = $target->full_name;
        $target->delete();
        $this->dispatch('toast', message: "Akun {$name} dihapus. Sesi lamanya otomatis gugur.", type: 'success');
    }

    public function render()
    {
        return view('livewire.owner-users', [
            'users' => User::query()->orderByRaw("CASE role WHEN 'owner' THEN 1 WHEN 'admin' THEN 2 ELSE 3 END")->orderBy('id')->get(),
        ]);
    }
}
