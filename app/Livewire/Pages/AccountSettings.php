<?php

namespace App\Livewire\Pages;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class AccountSettings extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        $validated = $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'different:current_password', 'confirmed'],
        ], [
            'password.different' => 'Password baru harus berbeda dari password saat ini.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal 6 karakter.',
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);
        session()->flash('password-updated', 'Password berhasil diperbarui.');
    }

    public function render()
    {
        $layout = Auth::user()->hasRole('admin') ? 'layouts.admin' : 'layouts.app';

        return view('livewire.pages.account-settings')
            ->layout($layout, ['header' => 'Pengaturan Akun']);
    }
}