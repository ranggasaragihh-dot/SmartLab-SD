<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class EditProfile extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $newPhoto; // Untuk upload baru
    public $oldPhoto; // Untuk preview foto lama

    public function mount()
    {
        // Isi form dengan data user yang sedang login
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->oldPhoto = $user->avatar;
    }

    public function updateProfile()
    {
        // $user = Auth::user();
        $user = User::find(Auth::id());

        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $user->id, // Ignore email sendiri
            'newPhoto' => 'nullable|image|max:2048',
        ]);

        // Logic Ganti Foto
        if ($this->newPhoto) {
            // 1. Hapus foto lama jika ada (biar server gak penuh sampah)
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // 2. Upload foto baru
            $path = $this->newPhoto->store('photos', 'public');
            $user->photo = $path;
        }

        $user->name = $this->name;
        $user->email = $this->email;
        $user->save();

        // Refresh preview
        $this->oldPhoto = $user->avatar;
        $this->newPhoto = null; // Reset input file

        session()->flash('message', 'Profil berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.profile.edit-profile');
    }
}