<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Validators\ValidationException;

#[Layout('layouts.admin')]
class UserManagement extends Component
{
    use WithPagination;
    use WithFileUploads;
    
    protected $paginationTheme = 'tailwind'; 

    public $search = '';

    public $showModal = false;
    public $isEditMode = false;
    public $showDeleteModal = false;

    public $deleteId = null;

    public $editingUserId = null;
    public $name, $email, $nomor_induk, $password;
    public $photo, $existingPhoto;
    public $selectedRoles = [];

    public $showImportModal = false;
    public $importFile;

    public $activeTab = 'manual';
    public $ta=0;

    public function create()
    {
        $this->resetInputFields();
        $this->resetValidation();
        $this->isEditMode = false;
        $this->activeTab = 'manual'; 
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate([
            'name'          => 'required|min:3',
            'email'         => 'required|email|unique:users,email',
            'nomor_induk'   => 'required|string|max:20|unique:users,nomor_induk',
            'password'      => 'required|min:6',
            'selectedRoles' => 'required|array|size:1',
            'selectedRoles.*' => 'required|exists:roles,id',
            'ta'            => 'required|boolean'
        ], [
            'nomor_induk.required' => 'NPM wajib diisi.',
            'nomor_induk.unique' => 'NPM ini sudah terdaftar.',
            'selectedRoles.size' => 'Pilih tepat satu role untuk pengguna.',
        ]);

        $selectedRole = Role::findOrFail($this->selectedRoles[0]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'nomor_induk' => $this->nomor_induk,
            'password' => bcrypt($this->password),
            'ta' => $selectedRole->role === 'student' ? $this->ta : false,
        ]);

        $user->roles()->sync($this->selectedRoles);

        $this->closeModal();
        session()->flash('message', 'User baru berhasil ditambahkan.');
    }

    public function openImportModal()
    {
        $this->reset(['importFile']);
        $this->resetValidation();
        $this->showImportModal = true;
    }

    public function selectRole(int $roleId): void
    {
        $this->selectedRoles = [(string) $roleId];
        if (Role::findOrFail($roleId)->role !== 'student') {
            $this->ta = 0;
        }
    }

    public function import()
    {
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            set_time_limit(300);
            \Illuminate\Support\Facades\DB::transaction(fn () => Excel::import(new UsersImport, $this->importFile));
            
            $this->showImportModal = false;
            session()->flash('message', 'Import data massal berhasil!');
        } catch (ValidationException $e) {
             $failures = $e->failures();
             $errorMsg = 'Error pada baris: ';
             foreach ($failures as $failure) {
                 $errorMsg .= $failure->row() . ' (' . implode(', ', $failure->errors()) . ') | ';
             }
             session()->flash('error', $errorMsg);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal import: ' . $e->getMessage());
        }
        $this->closeModal();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->activeTab = 'delete';
        $this->showDeleteModal = true;
        $user = User::find($id);
        $this->name = $user->name;
        $this->deleteId = $user->id;
        
    }

    public function delete($userId)
    {
        if (!is_numeric($userId)) {
            session()->flash('error', 'ID User tidak valid.');
            return;
        }

        $user = User::find($userId);

        if (! $user) {
            session()->flash('error', 'User tidak ditemukan.');
            return;
        }

        if ((int) $user->id === (int) Auth::id()) {
            session()->flash('error', 'Dilarang menghapus akun sendiri.');
            return;
        }

        $user->delete();
        $this->closeDeleteModal();

        session()->flash('message', 'User berhasil dihapus.');
    }

    public function toggleActive(int $userId): void
    {
        $user = User::find($userId);
        if (!$user || (int) $user->id === (int) Auth::id()) {
            session()->flash('error', 'Akun tidak dapat diubah.');
            return;
        }

        $user->update(['is_banned' => !$user->is_banned]);

        if ($user->is_banned) {
            // Booking yang belum dipakai dilepas agar PC bisa dipesan orang lain.
            $user->bookings()
                ->where('status', 'booked')
                ->update(['status' => 'cancelled', 'cancel_reason' => 'Akun dinonaktifkan admin']);
            session()->flash('message', "Akun {$user->name} dinonaktifkan.");
        } else {
            session()->flash('message', "Akun {$user->name} diaktifkan kembali.");
        }
    }

    public function edit($id)
    {
        $this->resetValidation();
        $this->isEditMode = true;
        $this->editingUserId = $id;

        $user = User::with('roles')->findOrFail($id);

        $this->name = $user->name;
        $this->email = $user->email;
        $this->nomor_induk = $user->nomor_induk;
        $this->ta = $user->ta;
        $this->selectedRoles = $user->roles->pluck('id')->map(fn($id) => (string) $id)->toArray();

        $this->showModal = true;
    }

    public function update()
    {
        $user = User::findOrFail($this->editingUserId);

        $this->validate([
            'selectedRoles' => 'required|array|size:1',
            'selectedRoles.*' => 'required|exists:roles,id',
        ], [
            'selectedRoles.size' => 'Pilih tepat satu role untuk pengguna.',
        ]);

        $selectedRole = Role::findOrFail($this->selectedRoles[0]);

        if ((int) $user->id === (int) Auth::id()) {
            $adminRoleId = Role::where('role', 'admin')->value('id');
            if ($adminRoleId && !in_array((string) $adminRoleId, array_map('strval', $this->selectedRoles), true)) {
                session()->flash('error', 'TINDAKAN DITOLAK: Anda tidak boleh menghapus akses Admin dari akun sendiri!');
                $this->showModal = false;
                return;
            }
        }

        $this->validate([
            'name' => 'required|min:3',
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'nomor_induk' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'nomor_induk')->ignore($user->id),
            ],
            'password' => 'nullable|min:6',
        ], [
            'nomor_induk.required' => 'NPM wajib diisi.',
            'nomor_induk.unique' => 'NPM ini sudah terdaftar.',
            'email.unique' => 'Email ini sudah dipakai user lain.',
        ]);

        $userData = [
            'name' => $this->name,
            'email' => $this->email ?: null,
            'nomor_induk' => $this->nomor_induk,
            'ta' => $selectedRole->role === 'student' ? $this->ta : false,
        ];

        if (!empty($this->password)) {
            $userData['password'] = bcrypt($this->password);
        }

        $user->update($userData);
        $user->roles()->sync($this->selectedRoles);

        $this->showModal = false;
        $this->resetInputFields();
        session()->flash('message', 'Data user berhasil diperbarui.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetInputFields();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->nomor_induk = '';
        $this->password = '';
        $this->photo = null;
        $this->existingPhoto = null;
        $this->selectedRoles = [];
        $this->editingUserId = null;
        $this->isEditMode = false;
        $this->activeTab = 'manual';
        $this->importFile = null;
        $this->ta = 0;
    }

    public function render()
    {
        $allRoles = Role::all();
        $users = User::query()
            ->with('roles')
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('email', 'like', '%'.$this->search.'%')
                      ->orWhere('nomor_induk', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(10);

        $selectedRole = $allRoles->first(
            fn (Role $role) => (string) $role->id === (string) ($this->selectedRoles[0] ?? '')
        );

        return view('livewire.admin.manajemen-user', [
            'users' => $users,
            'allRoles' => $allRoles,
            'isStudentRoleSelected' => $selectedRole?->role === 'student',
        ]);
    }
}