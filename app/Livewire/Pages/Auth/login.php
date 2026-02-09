<?php

namespace App\Livewire\Pages\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.guest')] 
class Login extends Component
{
    // 1. GANTI PROPERTI EMAIL JADI NOMOR INDUK
    // Hapus validasi 'email' karena nomor induk biasanya string/angka biasa
    #[Validate('required|string')] 
    public string $nomor_induk = '';

    #[Validate('required')]
    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        // 2. VALIDASI VARIABEL DI ATAS
        $this->validate();
        // dd("Input Masuk:", $this->nomor_induk, $this->password);

        // 3. COBA LOGIN (Gunakan key 'nomor_induk' sesuai kolom database)
        if (! Auth::attempt(['nomor_induk' => $this->nomor_induk, 'password' => $this->password], $this->remember)) {
            
            // Jika gagal, lempar error validasi ke field nomor_induk
            throw ValidationException::withMessages([
                'nomor_induk' => trans('auth.failed'),
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