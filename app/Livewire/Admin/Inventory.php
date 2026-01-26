<?php

namespace App\Livewire\Admin;

use App\Models\Cluster;
use App\Models\Inventory as InventoryModel;
use App\Livewire\Forms\InventoryForm;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Inventaris PC')]
class Inventory extends Component
{
    use WithPagination;

    public InventoryForm $form;

    public $search = '';
    public $filterCluster = '';

    // State Modal Utama
    public $isOpen = false;
    public $isEdit = false;
    public $showDeleteModal = false;
    public $deleteId;

    // --- [BARU] STATE UNTUK QR MODAL ---
    public $showQrModal = false;
    public $qrName = '';
    public $qrUrl = '';

    public function updatedSearch() { $this->resetPage(); }

    public function create()
    {
        $this->form->reset();
        $this->isEdit = false;
        $this->isOpen = true;
    }

    public function edit(InventoryModel $inventory)
    {
        $this->form->setInventory($inventory);
        $this->isEdit = true;
        $this->isOpen = true;
    }

    // --- [BARU] LOGIC TAMPILKAN QR ---
    public function showQr($id)
    {
        $item = InventoryModel::findOrFail($id);
        
        $this->qrName = $item->name;
        
        // Generate Link Scan
        // Pastikan route 'scan.qr' sudah ada di web.php
        $scanUrl = route('scan.qr', ['qrToken' => $item->qr_token]);
        
        // Generate Link Gambar QR (API)
        $this->qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($scanUrl);
        
        $this->showQrModal = true;
    }

    // --- [BARU] LOGIC TUTUP QR (INI YANG HILANG TADI) ---
    public function closeQrModal()
    {
        $this->showQrModal = false;
        $this->qrName = '';
        $this->qrUrl = '';
    }

    public function store()
    {
        $this->form->save();
        session()->flash('message', $this->isEdit ? 'Data diperbarui.' : 'Data ditambahkan.');
        $this->closeModal(); // Ini akan memanggil fungsi di bawah
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function destroy()
    {
        InventoryModel::find($this->deleteId)->delete();
        $this->showDeleteModal = false;
        session()->flash('message', 'Data dihapus.');
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->showDeleteModal = false;
        
        // --- [BARU] Pastikan QR juga tertutup saat reset total
        $this->showQrModal = false;
        
        $this->form->reset();
    }

    public function render()
    {
        $query = InventoryModel::query()->with('cluster');

        if ($this->search) $query->where('name', 'like', '%' . $this->search . '%');
        if ($this->filterCluster) $query->where('cluster_id', $this->filterCluster);

        return view('livewire.admin.inventory', [
            'inventories' => $query->paginate(10),
            'clusters' => Cluster::all(),
        ]);
    }
}