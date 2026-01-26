<?php

namespace App\Livewire\Admin;

use App\Models\Cluster;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class ClusterManager extends Component
{
    // Data Form
    public $form = [
        'name' => '',
        'type' => 'public', // public (Mahasiswa), private (Dosen/Khusus)
        'description' => '',
        'is_active' => true,
    ];

    public $isEdit = false;
    public $editId = null;
    public $showFormModal = false;

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

        $this->form = [
            'name' => $data->name,
            'type' => $data->type,
            'description' => $data->description,
            'is_active' => $data->is_active ? 1 : 0,
        ];

        $this->showFormModal = true;
    }

    // --- STORE / UPDATE ---
    public function store()
    {
        $this->validate([
            'form.name' => 'required|string|max:50',
            'form.type' => 'required|in:public,private',
            'form.description' => 'nullable|string|max:255',
            'form.is_active' => 'boolean',
        ]);

        if ($this->isEdit) {
            Cluster::find($this->editId)->update($this->form);
            session()->flash('success', 'Data Lab berhasil diperbarui.');
        } else {
            Cluster::create($this->form);
            session()->flash('success', 'Lab baru berhasil ditambahkan.');
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    // --- DELETE ---
    public function delete($id)
    {
        // Opsional: Cek apakah ada inventory di dalamnya sebelum hapus
        $cluster = Cluster::withCount('inventory')->findOrFail($id);
        
        if ($cluster->inventory_count > 0) {
            session()->flash('error', 'Gagal hapus! Lab ini masih memiliki unit PC/Meja.');
            return;
        }

        $cluster->delete();
        session()->flash('success', 'Lab berhasil dihapus.');
    }

    public function closeModal()
    {
        $this->showFormModal = false;
    }

    public function resetForm()
    {
        $this->form = [
            'name' => '',
            'type' => 'public',
            'description' => '',
            'is_active' => true,
        ];
    }
}