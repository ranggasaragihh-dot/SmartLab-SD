<?php

namespace App\Livewire\Admin;

use App\Models\Cluster;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads; // Wajib import ini
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.admin')]
class ClusterManager extends Component
{
    use WithFileUploads; // Wajib gunakan trait ini
    use WithPagination;

    // Variabel khusus untuk file upload sementara
    // Jangan dicampur dengan $form agar tidak error saat serialize data
    public $image_upload; 

    public $isEdit = false;
    public $editId = null;
    public $showFormModal = false;

    // Property untuk Delete Modal
    public $showDeleteModal = false;
    public $deleteId;
    public $deleteName; // <--- Variabel untuk menyimpan nama lab sementara

    // Data Form (Sesuai kolom database terbaru)
    public $form = [
        'name' => '',
        'type' => 'public', 
        'description' => '',
        'is_active' => true,
        'delay_time' => 15, // Default sesuai request (pengganti booking_timeout)
        'denah_image' => null, // Menyimpan path string (bukan file fisik)
        'min_duration_minutes' => 30, // Default 30 menit
        'max_duration_minutes' => 120, // Default 2 jam
    ];

    // --- READ ---
    public function render()
    {
        return view('livewire.admin.cluster-manager', [
            // UBAH get() MENJADI paginate(jumlah_per_halaman)
            'clusters' => Cluster::orderBy('name', 'asc')->paginate(10) 
        ]);
    }

    // --- CREATE ---
    public function create()
    {
        $this->resetForm();
        $this->isEdit = false;
        $this->showFormModal = true;
    }

    // --- EDIT ---
    public function edit($id)
    {
        $data = Cluster::findOrFail($id);
        $this->editId = $id;
        $this->isEdit = true;

        $this->form = [
            'name' => $data->name,
            'type' => $data->type,
            'description' => $data->description,
            
            // PERBAIKAN DI SINI:
            // Gunakan ternary operator. Jika true/1 jadi 1, jika false/0 jadi 0.
            'is_active' => $data->is_active ? 1 : 0, 

            'delay_time' => $data->delay_time ?? 15,
            'denah_image' => $data->denah_image,
            
            // Jangan lupa field baru ini jika sudah ditambahkan di migration
            'min_duration_minutes' => $data->min_duration_minutes ?? 30, 
            'max_duration_minutes' => $data->max_duration_minutes ?? 120, 
        ];

        $this->showFormModal = true;
    }

    // --- STORE / UPDATE ---
    public function store()
    {
        // 1. Validasi
        $this->validate([
            'form.name' => 'required|string|max:50',
            'form.type' => 'required|in:public,private',
            'form.description' => 'nullable|string|max:255',
            'form.is_active' => 'boolean',
            'form.delay_time' => 'required|integer|min:1', // Validasi delay_time
            'form.min_duration_minutes' => 'required|integer|min:1',
            'form.max_duration_minutes' => 'required|integer|min:1|gt:form.min_duration_minutes',
            'image_upload' => 'nullable|image|max:2048', // Max 2MB (Validasi file fisik)
        ],[
            'form.max_duration_minutes.gt' => 'Durasi maksimal harus lebih besar dari durasi minimal.'
        ]);

        $imagePath = $this->isEdit ? $this->form['denah_image'] : null;

        // 2. Logika Upload Gambar
        if ($this->image_upload) {
            // Jika Mode Edit & Sudah ada gambar lama -> Hapus gambar lama
            if ($this->isEdit && !empty($this->form['denah_image'])) {
                if (Storage::disk('public')->exists($this->form['denah_image'])) {
                    Storage::disk('public')->delete($this->form['denah_image']);
                }
            }

            // Simpan gambar baru & update path di form
            // $path = $this->image_upload->store('denahs', 'public');
            $imagePath = $this->image_upload->store('denahs', 'public');
            // $this->form['denah_image'] = $path;
        }

        $this->form['denah_image'] = $imagePath;

        // 3. Simpan ke Database
        if ($this->isEdit) {
            $cluster = Cluster::find($this->editId);
            $cluster->update($this->form);
            session()->flash('success', 'Data Cluster & Konfigurasi berhasil diperbarui.');
        } else {
            Cluster::create($this->form);
            session()->flash('success', 'Cluster baru berhasil ditambahkan.');
        }

        // 4. Reset & Tutup
        $this->showFormModal = false;
        $this->resetForm();
    }

    // Dipanggil saat klik ikon sampah di tabel
    public function confirmDelete($id)
    {
        $cluster = Cluster::find($id);
        
        if ($cluster) {
            $this->deleteId = $id;
            $this->deleteName = $cluster->name; // Ambil nama untuk ditampilkan di modal
            $this->showDeleteModal = true;      // Buka modal merah
        }
    }

    // Dipanggil saat klik "Batal"
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteName = null;
    }

    // Dipanggil saat klik "Ya, Hapus"
    public function destroy()
    {
        if ($this->deleteId) {
            $cluster = Cluster::find($this->deleteId);
            if ($cluster) {
                // Hapus gambar fisik jika ada
                if ($cluster->denah_image) {
                    Storage::disk('public')->delete($cluster->denah_image);
                }
                $cluster->delete();
                
                // Kirim notifikasi sukses
                session()->flash('message', 'Cluster berhasil dihapus.');
            }
        }

        $this->closeDeleteModal(); // Tutup modal
    }

    // --- DELETE ---
    // public function delete($id)
    // {
    //     // Cek apakah ada inventory (PC) di dalamnya
    //     // Sesuaikan 'inventory' dengan nama relasi di Model Cluster Anda (misal: 'pcs')
    //     $cluster = Cluster::withCount('inventory')->findOrFail($id);
        
    //     if ($cluster->pcs_count > 0) {
    //         session()->flash('error', 'Gagal hapus! Cluster ini masih memiliki PC aktif.');
    //         return;
    //     }

    //     // Hapus file gambar denah jika ada
    //     if ($cluster->denah_image && Storage::disk('public')->exists($cluster->denah_image)) {
    //         Storage::disk('public')->delete($cluster->denah_image);
    //     }

    //     $cluster->delete();
    //     session()->flash('success', 'Cluster berhasil dihapus.');
    // }

    public function closeModal()
    {
        $this->showFormModal = false;
        // Reset image upload agar tidak nyangkut saat buka modal lagi
        $this->image_upload = null; 
    }

    public function resetForm()
    {
        $this->form = [
            'name' => '',
            'type' => 'public',
            'description' => '',
            'is_active' => true,
            'delay_time' => 15, // Default 15 menit
            'min_duration_minutes' => 30,
            'max_duration_minutes' => 120,
            'denah_image' => null,
        ];
        $this->image_upload = null; // Reset input file
        $this->editId = null;
    }
}