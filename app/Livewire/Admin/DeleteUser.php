<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth; // <--- PENTING: Import Facade Auth
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class DeleteUser extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind'; 

    public function delete($userId)
    {
        // 0. Validasi Ringan: Pastikan ID adalah angka
        if (!is_numeric($userId)) {
            session()->flash('error', 'ID User tidak valid.');
            return;
        }

        // 1. Cari User
        $user = User::find($userId);

        // 2. Cek Keberadaan Data (Wajib sebelum akses property)
        if (! $user) {
            session()->flash('error', 'User tidak ditemukan atau sudah dihapus.');
            return;
        }

        // 3. Proteksi: Cek ID Admin via Facade Auth
        // Menggunakan Auth::id() lebih aman dan dikenali editor daripada auth()->id()
        if ($user->id === Auth::id()) {
            session()->flash('error', 'Anda tidak bisa menghapus akun sendiri saat sedang login.');
            return;
        }

        // 4. Hapus Foto Fisik
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // 5. Hapus Data DB
        $user->delete();

        session()->flash('message', 'User berhasil dihapus permanen.');
    }

    public function render()
    {
        // Menggunakan Auth::id() untuk exclude diri sendiri
        $users = User::where('id', '!=', Auth::id())
                    ->latest()
                    ->paginate(10);

        return view('livewire.admin.manajemen-user', [
            'users' => $users
        ]);
    }
}