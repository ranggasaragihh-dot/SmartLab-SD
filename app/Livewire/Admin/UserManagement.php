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
            'nomor_induk'   => 'nullable|string|max:20',
            'password'      => 'required|min:6',
            'selectedRoles' => 'required|array|min:1',
            'ta'            => 'required|boolean'
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'nomor_induk' => $this->nomor_induk,
            'password' => bcrypt($this->password),
            'ta' => $this->ta,
        ]);

        $user->roles()->attach($this->selectedRoles);

        $this->closeModal();
        session()->flash('message', 'User baru berhasil ditambahkan.');
    }

    public function openImportModal()
    {
        $this->reset(['importFile']);
        $this->resetValidation();
        $this->showImportModal = true;
    }

    public function import()
    {
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            set_time_limit(300);
            Excel::import(new UsersImport, $this->importFile);
            
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

        if ($user->id === Auth::id()) {
            session()->flash('error', 'Dilarang menghapus akun sendiri.');
            return;
        }

        $user->delete();
        $this->closeDeleteModal();

        session()->flash('message', 'User berhasil dihapus.');
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

        if ($user->id === Auth::id()) {
            $adminRoleId = Role::where('name', 'admin')->value('id');
            if ($adminRoleId && !in_array($adminRoleId, $this->selectedRoles)) {
                session()->flash('error', 'TINDAKAN DITOLAK: Anda tidak boleh menghapus akses Admin dari akun sendiri!');
                $this->showModal = false;
                return;
            }
        }

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'nomor_induk' => $this->nomor_induk,
            'ta' => $this->ta,
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
        $users = User::query()
            ->with('roles')
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.manajemen-user', [
            'users' => $users,
            'allRoles' => Role::all()
        ]);
    }
}