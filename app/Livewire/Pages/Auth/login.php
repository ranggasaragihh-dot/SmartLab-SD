<?php

namespace App\Livewire\Pages\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.guest')] 
class Login extends Component
{
    #[Validate('required|string')] 
    public string $nomor_induk = '';

    #[Validate('required')]
    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $this->validate();

        $throttleKey = Str::lower(trim($this->nomor_induk)) . '|' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'nomor_induk' => 'Terlalu banyak percobaan login. Coba lagi dalam ' . RateLimiter::availableIn($throttleKey) . ' detik.',
            ]);
        }

        if (! Auth::attempt(['nomor_induk' => $this->nomor_induk, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'nomor_induk' => trans('auth.failed'),
            ]);
        }

        if (Auth::user()->is_banned) {
            Auth::logout();
            throw ValidationException::withMessages([
                'nomor_induk' => 'Akun Anda dinonaktifkan. Hubungi admin laboratorium.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        Session::regenerate();

        // Kembali ke halaman yang tadi diminta (mis. URL scan QR) agar tidak perlu scan dua kali.
        return $this->redirectIntended(default: route('dashboard'));
    }

    public function render()
    {
        return view('livewire.pages.auth.login');
    }
}