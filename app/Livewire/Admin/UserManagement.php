<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel; // Tambahkan ini
use App\Imports\UsersImport; // Kita buat ini nanti

#[Layout('layouts.admin')]
class UserManagement extends Component // Ganti nama class agar representatif
{
    use WithPagination;

    protected $paginationTheme = 'tailwind'; 

    // Properti untuk pencarian (Opsional tapi sangat disarankan untuk backend yang baik)
    public $search = '';

    public $showModal = false; // Kontrol visibilitas Modal
    public $isEditMode = false; // Pembeda antara Create vs Edit

    // Data Form
    public $editingUserId = null;
    public $name, $email, $nomor_induk, $password;
    public $photo, $existingPhoto; // Handle upload baru & preview lama
    public $selectedRoles = []; // Array untuk menyimpan ID role yang dipilih

    public $showImportModal = false;
    public $importFile;

    public function create()
    {
        $this->resetInputFields(); // Pastikan bersih dari sisa data edit
        $this->resetValidation();
        $this->isEditMode = false; // Flag bahwa ini mode Create
        $this->showModal = true;
    }

    public function store()
    {
        // Validasi Create (Password Wajib)
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email', // Cek unique murni
            'nomor_induk' => 'nullable|string|max:20',
            'password' => 'required|min:6', // Password wajib saat create
            'selectedRoles' => 'required|array|min:1',
            'photo' => 'nullable|image|max:1024',
        ]);

        // Handle Foto
        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('photos', 'public');
        }

        // Create User
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'nomor_induk' => $this->nomor_induk,
            'password' => bcrypt($this->password),
            'photo' => $photoPath,
        ]);

        // Attach Roles
        $user->roles()->attach($this->selectedRoles);

        $this->closeModal();
        session()->flash('message', 'User baru berhasil ditambahkan.');
    }

    // --- BAGIAN 2: BULK IMPORT (FILE) ---

    // 1. Buka Modal Import
    public function openImportModal()
    {
        $this->reset(['importFile']);
        $this->resetValidation();
        $this->showImportModal = true;
    }

    // 2. Eksekusi Import
    public function import()
    {
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv|max:2048', // Max 2MB
        ]);

        try {
            // Kita panggil Class Import terpisah agar Controller rapi
            Excel::import(new UsersImport, $this->importFile);
            
            $this->showImportModal = false;
            session()->flash('message', 'Import data massal berhasil!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Tangkap error validasi baris Excel (misal email duplikat di baris 5)
             $failures = $e->failures();
             $errorMsg = 'Error pada baris: ';
             foreach ($failures as $failure) {
                 $errorMsg .= $failure->row() . ' (' . implode(', ', $failure->errors()) . ') | ';
             }
             session()->flash('error', $errorMsg);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    // Reset pagination saat search berubah agar tidak stuck di halaman kosong
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function delete($userId)
    {
        // 1. Validasi Input
        if (!is_numeric($userId)) {
            session()->flash('error', 'ID User tidak valid.');
            return;
        }

        // 2. Cari User (Optimasi: Fail early)
        $user = User::find($userId);

        if (! $user) {
            session()->flash('error', 'User tidak ditemukan.');
            return;
        }

        // 3. Authorization Check
        if ($user->id === Auth::id()) {
            session()->flash('error', 'Dilarang menghapus akun sendiri.');
            return;
        }

        // 4. Hapus File Fisik
        // PERHATIKAN: Di frontend anda pakai 'photo', di sini 'avatar'. 
        // Saya samakan menjadi 'photo' sesuai kode frontend sebelumnya. Cek database anda!
        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        // 5. Hapus Data
        $user->delete();

        // Flash message
        session()->flash('message', 'User berhasil dihapus.');
    }

    public function edit($id)
    {
        $this->resetValidation(); // Hapus pesan error sisa sebelumnya
        $this->isEditMode = true;
        $this->editingUserId = $id;

        $user = User::with('roles')->findOrFail($id);

        // Isi form dengan data database
        $this->name = $user->name;
        $this->email = $user->email;
        $this->nomor_induk = $user->nomor_induk;
        $this->existingPhoto = $user->photo; // Untuk preview di modal
        
        // Ambil ID roles yang dimiliki user saat ini
        $this->selectedRoles = $user->roles->pluck('id')->map(fn($id) => (string) $id)->toArray();

        $this->showModal = true; // Buka Modal via Frontend
    }

    // --- 2. Eksekusi Update (Menyimpan Data) ---
    public function update()
    {
        // --- 1. DEFINISI RULES LANGSUNG DI SINI ---
        // Kita taruh rules di variabel agar kode rapi dan eksplisit
        $validatedData = $this->validate([
            'name' => 'required|min:3',
            // Rule unique email: abaikan ID user yang sedang diedit
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->editingUserId)],
            'nomor_induk' => 'nullable|string|max:20',
            'selectedRoles' => 'required|array|min:1', 
            // Password opsional saat edit
            'password' => $this->isEditMode ? 'nullable|min:6' : 'required|min:6',
            'photo' => 'nullable|image|max:1024',
        ]);

        // --- 2. LOGIKA SELANJUTNYA TETAP SAMA ---
        $user = User::findOrFail($this->editingUserId);

        // Safety Logic: Pengaman Bunuh Diri (Tetap pertahankan ini!)
        if ($user->id === Auth::id()) {
            $adminRoleId = Role::where('name', 'admin')->value('id');
            if ($adminRoleId && !in_array($adminRoleId, $this->selectedRoles)) {
                session()->flash('error', 'TINDAKAN DITOLAK: Anda tidak boleh menghapus akses Admin dari akun sendiri!');
                $this->showModal = false;
                return;
            }
        }

        // Handle Photo
        $photoPath = $user->photo;
        if ($this->photo) {
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            $photoPath = $this->photo->store('photos', 'public');
        }

        // Update Data User
        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'nomor_induk' => $this->nomor_induk,
            'photo' => $photoPath,
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

    // --- Helper: Reset Form ---
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetInputFields();
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
    }

    public function render()
    {
        // QUERY OPTIMIZATION
        $users = User::query()
            ->with('roles') // <--- WAJIB: Eager Loading untuk mencegah N+1 Query di Frontend
            // ->where('id', '!=', Auth::id()) // Exclude diri sendiri
            ->when($this->search, function($query) {
                // Logic pencarian sederhana
                $query->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(10);

        // Pastikan view path sesuai dengan file blade anda
        return view('livewire.admin.manajemen-user', [
            'users' => $users,
            'allRoles' => Role::all()
        ]);
    }
}