<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class LoginModal extends Component
{
    public bool $open = false;

    public string $username = '';

    public string $password = '';

    public bool $remember = false;

    public ?string $errorMessage = null;

    public function mount(): void
    {
        if (session('open_login')) {
            $this->open = true;
        }
    }

    #[On('open-login-modal')]
    public function showModal(): void
    {
        $this->reset(['password', 'errorMessage']);
        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
        $this->resetValidation();
    }

    public function authenticate(): mixed
    {
        $this->errorMessage = null;
        $this->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $credentials = [
            'username' => trim($this->username),
            'password' => $this->password,
        ];

        if (! Auth::attempt($credentials, $this->remember)) {
            $this->errorMessage = 'Username atau password tidak sesuai.';

            return null;
        }

        session()->regenerate();
        $user = Auth::user();
        $this->open = false;

        $target = match ($user->role) {
            'driver' => route('kurir'),
            'owner', 'admin' => route('operasional'),
            default => route('public.tracking'),
        };

        session()->flash('success', "Selamat datang, {$user->full_name}");

        return $this->redirect($target, navigate: true);
    }

    public function render()
    {
        return view('livewire.login-modal');
    }
}
