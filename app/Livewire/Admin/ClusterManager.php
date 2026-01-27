<?php

namespace App\Livewire\Admin;

use App\Models\Cluster;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads; // Wajib import ini
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.admin')]
class ClusterManager extends Component
{
    use WithFileUploads; // Wajib gunakan trait ini

    // Variabel khusus untuk file upload sementara
    // Jangan dicampur dengan $form agar tidak error saat serialize data
    public $image_upload; 

    public $isEdit = false;
    public $editId = null;
    public $showFormModal = false;

    // Data Form (Sesuai kolom database terbaru)
    public $form = [
        'name' => '',
        'type' => 'public', 
        'description' => '',
        'is_active' => true,
        'delay_time' => 15, // Default sesuai request (pengganti booking_timeout)
        'denah_image' => null, // Menyimpan path string (bukan file fisik)
    ];

    // --- READ ---
    public function render()
    {
        return view('livewire.admin.cluster-manager', [
            'clusters' => Cluster::orderBy('name', 'asc')->get()
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

        // Isi form dengan data dari database
        $this->form = [
            'name' => $data->name,
            'type' => $data->type,
            'description' => $data->description,
            'is_active' => (bool) $data->is_active,
            'delay_time' => $data->delay_time ?? 15, // Ambil delay_time
            'denah_image' => $data->denah_image, // Path gambar lama
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
            'image_upload' => 'nullable|image|max:2048', // Max 2MB (Validasi file fisik)
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

    // --- DELETE ---
    public function delete($id)
    {
        // Cek apakah ada inventory (PC) di dalamnya
        // Sesuaikan 'inventory' dengan nama relasi di Model Cluster Anda (misal: 'pcs')
        $cluster = Cluster::withCount('inventory')->findOrFail($id);
        
        if ($cluster->pcs_count > 0) {
            session()->flash('error', 'Gagal hapus! Cluster ini masih memiliki PC aktif.');
            return;
        }

        // Hapus file gambar denah jika ada
        if ($cluster->denah_image && Storage::disk('public')->exists($cluster->denah_image)) {
            Storage::disk('public')->delete($cluster->denah_image);
        }

        $cluster->delete();
        session()->flash('success', 'Cluster berhasil dihapus.');
    }

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
            'denah_image' => null,
        ];
        $this->image_upload = null; // Reset input file
        $this->editId = null;
    }
}