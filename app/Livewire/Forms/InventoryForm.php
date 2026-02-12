<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;
use App\Models\Inventory;
use App\Models\InventoryItem; // <--- JANGAN LUPA IMPORT INI
use Illuminate\Support\Str;

class InventoryForm extends Form
{
    // ID Meja (Untuk Mode Edit)
    public $id = null; 
    public ?Inventory $inventory = null;

    // --- FIELD TABEL INVENTORY ---
    #[Validate('required|exists:clusters,id')]
    public $cluster_id = '';

    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('required|in:available,maintenance,broken')]
    public $status = 'available';

    #[Validate('required|in:0,1')]
    public $is_bookable = 0;

    public $qr_token = '';

    // --- FIELD BARU: PILIHAN BARANG FISIK ---
    // Validasi: Boleh kosong (nullable), tapi kalau diisi harus ada di tabel inventory_items
    #[Validate('nullable|exists:inventory_items,id')]
    public $selected_item_id = ''; 


    // --- 1. SET DATA SAAT EDIT ---
    public function setInventory(Inventory $inventory)
    {
        $this->inventory = $inventory;
        $this->id = $inventory->id;
        
        // Isi field dasar
        $this->cluster_id = $inventory->cluster_id;
        $this->name = $inventory->name;
        $this->status = $inventory->status;
        $this->is_bookable = intval($inventory->is_bookable);
        $this->qr_token = $inventory->qr_token;

        // LOAD BARANG FISIK SAAT INI
        // Cari barang yang kolom inventory_id-nya mengarah ke meja ini
        $currentItem = InventoryItem::where('inventory_id', $inventory->id)->first();
        
        // Jika ada, isi properti selected_item_id agar dropdown terpilih otomatis
        $this->selected_item_id = $currentItem ? $currentItem->id : '';
    }

    // --- 2. SIMPAN DATA (CREATE / UPDATE) ---
    public function save()
    {
        $this->validate();

        // A. Generate Token jika kosong
        $token = $this->qr_token ?: Str::random(32);

        // B. Siapkan Data Dasar Meja
        $data = [
            'cluster_id'  => $this->cluster_id,
            'name'        => $this->name,
            'status'      => $this->status,
            'is_bookable' => $this->is_bookable,
            'qr_token'    => $token,
            // Kita update specifications nanti di bawah
        ];

        // C. Simpan ke Database (Inventory)
        if ($this->id) {
            // -- UPDATE --
            $inventoryRecord = Inventory::find($this->id);
            $inventoryRecord->update($data);
        } else {
            // -- CREATE --
            $inventoryRecord = Inventory::create($data);
            $this->id = $inventoryRecord->id; // Simpan ID baru untuk dipakai di bawah
        }

        // D. LOGIKA SPESIFIKASI & BARANG FISIK (PENTING!)
        
        // 1. Reset Barang Lama (Lepaskan barang apapun yang dulu nempel di meja ini)
        // Set inventory_id jadi NULL untuk semua barang yang punya ID meja ini
        InventoryItem::where('inventory_id', $this->id)->update(['inventory_id' => null]);

        // 2. Pasang Barang Baru (Jika user memilih sesuatu dari dropdown)
        if ($this->selected_item_id) {
            $newItem = InventoryItem::find($this->selected_item_id);
            
            if ($newItem) {
                // HANYA LINK-KAN ID SAJA.
                // Tidak perlu copy spesifikasi karena kolomnya tidak ada.
                $newItem->update(['inventory_id' => $this->id]);
            }
        } 
        
        // Selesai. Tidak ada update ke tabel Inventory untuk kolom specs.

        $this->reset();
    }
}