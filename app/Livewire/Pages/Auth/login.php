<?php

namespace App\Livewire\Pages\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate; // Import Validate attribute
use Livewire\Component;

#[Layout('layouts.guest')] 
class Login extends Component
{
    // 1. DEFINISIKAN VARIABEL LANGSUNG DI SINI
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required')]
    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        // 2. VALIDASI VARIABEL DI ATAS
        $this->validate();

        // 3. COBA LOGIN (Auth Logic Langsung Di Sini)
        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            // Jika gagal, lempar error validasi
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // 4. JIKA SUKSES, BERSIHKAN RATE LIMITER & REGENERATE SESSION
        Session::regenerate();

        // 5. REDIRECT SESUAI ROLE
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            return $this->redirect(route('admin.dashboard'), navigate: true);
        } else {
            return $this->redirect(route('booking.index'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.pages.auth.login');
    }
}