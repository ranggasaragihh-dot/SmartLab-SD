<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class Register extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $nomor_induk; // Bisa NIM atau NIP
    public $password;
    public $role = 'mahasiswa'; // Default select
    public $photo;

    public function register()
    {
        // 1. Validasi Keras
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email',
            'nomor_induk' => 'required|numeric|unique:users,nomor_induk',
            'ta' => 'required_if:role,mahasiswa|nullable|string',
            'password' => 'required|min:6',
            // PENTING: Hanya izinkan 'mahasiswa' atau 'dosen'. Admin DITOLAK disini.
            'role' => 'required|in:mahasiswa,dosen', 
            // 'photo' => 'nullable|image|max:2048', 
        ]);

        // 2. Upload photo (Opsional)
        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('photos', 'public');
        }

        // 3. Create User
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'nomor_induk' => $this->nomor_induk,
            'ta' => $this->ta,
            'password' => Hash::make($this->password),
            'role' => $this->role, // Nilai aman dari input user
            'foto' => $photoPath,
        ]);

        // 4. Login & Redirect
        Auth::login($user);
        
        session()->flash('message', 'Registrasi berhasil.');
        return $this->redirect(route('home'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}