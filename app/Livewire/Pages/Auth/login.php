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
    #[Validate('required|string')] 
    public string $nomor_induk = '';

    #[Validate('required')]
    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $this->validate();

        if (! Auth::attempt(['nomor_induk' => $this->nomor_induk, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'nomor_induk' => trans('auth.failed'),
            ]);
        }

        Session::regenerate();

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