<?php

namespace App\Livewire\Admin;

use App\Models\Cluster;
use App\Models\Inventory as InventoryModel;
use App\Models\InventoryItem;
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
    protected $paginationTheme = 'tailwind';

    public InventoryForm $form;

    public $search = '';
    public $filterCluster = '';

    public $itemSearch = ''; 
    public $isItemDropdownOpen = false;

    // State Modal Utama
    public $isOpen = false;
    public $isEdit = false;
    
    // State Modal Delete
    public $showDeleteModal = false;
    public $deleteId;
    public $deleteName;

    // State Modal QR
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
        if ($inventory->item) {
            $this->itemSearch = $inventory->item->item_name;
        } else {
            $this->itemSearch = '';
        }

        $this->isEdit = true;
        $this->isOpen = true;
    }

    public function selectItem($id, $name)
    {
        $this->form->selected_item_id = $id;
        $this->itemSearch = $name;
        $this->isItemDropdownOpen = false;
    }

    public function store()
    {
        $this->form->save();
        session()->flash('message', $this->isEdit ? 'Data Meja & Aset diperbarui.' : 'Data Meja baru ditambahkan.');
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->showDeleteModal = false;
        $this->showQrModal = false;
        $this->itemSearch = '';
        
        $this->form->reset();
    }

    public function showQr($id)
    {
        $item = InventoryModel::findOrFail($id);
        $this->qrName = $item->name;
        $scanUrl = route('scan.qr', ['qrToken' => $item->qr_token]);
        $this->qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($scanUrl);
        $this->showQrModal = true;
    }

    public function closeQrModal()
    {
        $this->showQrModal = false;
        $this->qrName = '';
        $this->qrUrl = '';
    }

    public function confirmDelete($id)
    {
        $item = InventoryModel::find($id);
        if ($item) {
            $this->deleteId = $id;
            $this->deleteName = $item->name;
            $this->showDeleteModal = true;
        }
    }

    public function destroy()
    {
        $item = InventoryModel::find($this->deleteId);
        if ($item) {
            InventoryItem::where('inventory_id', $item->id)->update(['inventory_id' => null]);
            $item->delete();
            session()->flash('message', 'Unit Meja berhasil dihapus.');
        }
        $this->showDeleteModal = false;
    }

    public function render()
    {
        $query = InventoryModel::query()->with(['cluster', 'item']); 
        if ($this->search) $query->where('name', 'like', '%' . $this->search . '%');
        if ($this->filterCluster) $query->where('cluster_id', $this->filterCluster);

        $currentInventoryId = $this->form->id ?? 0;
        $availableItems = InventoryItem::query()
            ->where(function($q) use ($currentInventoryId) {
                $q->whereNull('inventory_id')
                  ->orWhere('inventory_id', $currentInventoryId);
            })
            ->when($this->itemSearch, function($q) {
                $q->where(function($sub) {
                    $sub->where('item_name', 'like', '%' . $this->itemSearch . '%')
                        ->orWhere('asset_code', 'like', '%' . $this->itemSearch . '%');
                });
            })
            ->orderBy('item_name', 'asc')
            ->limit(50)
            ->get();

        return view('livewire.admin.inventory', [
            'inventories' => $query->paginate(10),
            'clusters' => Cluster::all(),
            'availableItems' => $availableItems,
        ]);
    }
}