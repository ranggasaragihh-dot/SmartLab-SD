<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InventoryItemsImport;
use App\Models\InventoryItem;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\InventoryBrand;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;

#[Layout('layouts.admin')]
class InventoryItemManager extends Component
{
    use WithPagination, WithFileUploads;

    public $excelFile;
    // public $isImportModalOpen = false;

    // --- VARIABEL UTAMA ---
    public $search = '';
    public $filterCategory = '';
    public $filterBrand = '';

    public $showQrModal = false;
    public $qrCodeTarget = ''; // Menyimpan Kode Aset (misal: INV/2024/001)
    public $qrCodeUrl = '';    // Link gambar QR
    public $qrItemName = '';   // Nama barang untuk judul modal

    public $isModalOpen = false;
    public $itemId;

    public $activeTab = 'manual'; // Default tab adalah 'manual'

    // --- VARIABEL FORM (Mapping Kolom Database) ---
    public $category_id, $brand_id, $inventory_id;
    public $asset_code, $serial_number, $asset_type;
    public $purchase_price, $document_number, $owner_unit;
    public $purchase_date, $condition = 'good';

    // --- VARIABEL SPESIFIKASI (Pecahan JSON) ---
    // Kita buat properti terpisah agar mudah di-bind ke Input Form
    public $spec_cpu, $spec_ram, $spec_storage, $spec_gpu, $spec_other;

    public function updatedSearch() { $this->resetPage(); }

    // --- RULES VALIDASI ---
    protected function rules()
    {
        return [
            'category_id' => 'required',
            'brand_id'    => 'required',
            'inventory_id'=> 'nullable|exists:inventory,id', // Boleh null (Gudang)
            'asset_code'  => 'required|unique:inventory_items,asset_code,' . $this->itemId,
            'serial_number' => 'nullable',
            'purchase_price'=> 'nullable|numeric',
            'condition'   => 'required|in:good,maintenance,broken',
            // Specs opsional
            'spec_cpu' => 'nullable|string',
            'spec_ram' => 'nullable|string',
        ];
    }

    // --- LOGIC TAMPILKAN QR ASET ---
    public function showQr($id)
    {
        $item = InventoryItem::findOrFail($id);
        
        $this->qrItemName = $item->item_name;
        $this->qrCodeTarget = $item->asset_code; // Isi QR adalah KODE ASET
        
        // Generate QR menggunakan API (Bisa juga pakai library simple-qrcode)
        // Ukuran 300x300, isinya teks kode aset mentah
        $this->qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($item->asset_code);
        
        $this->showQrModal = true;
    }

    public function closeQrModal()
    {
        $this->showQrModal = false;
        $this->qrCodeTarget = '';
        $this->qrCodeUrl = '';
    }

    public function downloadQrImage()
    {
        // 1. Cek URL
        if (empty($this->qrCodeUrl)) return;

        // 2. Ambil gambar dari API via Server Laravel (Bukan Browser)
        $response = Http::get($this->qrCodeUrl);

        if ($response->successful()) {
            // 3. Bersihkan nama file (Ganti tanda miring jadi strip)
            $safeFileName = 'QR-' . str_replace(['/', '\\'], '-', $this->qrCodeTarget) . '.png';

            // 4. Stream download ke browser user
            return response()->streamDownload(function () use ($response) {
                echo $response->body();
            }, $safeFileName);
        }
    }

    public function render()
    {
        $query = InventoryItem::query()
            ->with(['brand', 'category', 'inventory']); // Eager load relasi

        // 1. LOGIC SEARCH (Cari di Nama, Kode, Merk, atau Kategori)
        if ($this->search) {
            $query->where(function (Builder $q) {
                $q->where('item_name', 'like', '%' . $this->search . '%')
                  ->orWhere('asset_code', 'like', '%' . $this->search . '%')
                  // Cari di dalam tabel Brand (Relasi)
                  ->orWhereHas('brand', function ($b) {
                      $b->where('name', 'like', '%' . $this->search . '%');
                  })
                  // Cari di dalam tabel Category (Relasi)
                  ->orWhereHas('category', function ($c) {
                      $c->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // 2. LOGIC FILTER DROPDOWN
        if ($this->filterCategory) {
            $query->where('category_id', $this->filterCategory);
        }
        
        if ($this->filterBrand) {
            $query->where('brand_id', $this->filterBrand);
        }

        // Ambil data dengan Search & Pagination
        // $items = InventoryItem::with(['inventory', 'category', 'brand'])
        //     ->where('asset_code', 'like', '%' . $this->search . '%')
        //     ->orWhere('serial_number', 'like', '%' . $this->search . '%')
        //     ->orderBy('created_at', 'desc')
        //     ->paginate(10);

        return view('livewire.admin.inventory-item-manager', [
            'items' => $query->latest()->paginate(10),
            'inventories' => Inventory::orderBy('name')->get(), // Untuk Dropdown Lokasi Meja
            'categories' => InventoryCategory::orderBy('name')->get(),
            'brands' => InventoryBrand::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->activeTab = 'manual';
        $this->openModal();
    }

    public function store()
    {
        $this->validate();

        // 1. PACKING JSON
        // Gabungkan inputan specs menjadi satu array
        $specsArray = [
            'cpu' => $this->spec_cpu,
            'ram' => $this->spec_ram,
            'storage' => $this->spec_storage,
            'gpu' => $this->spec_gpu,
            'other' => $this->spec_other,
        ];

        // 2. SIMPAN KE DB
        InventoryItem::updateOrCreate(['id' => $this->itemId], [
            'category_id' => $this->category_id,
            'brand_id'    => $this->brand_id,
            'inventory_id'=> $this->inventory_id ?: null, // Handle string kosong jadi null
            'asset_code'  => $this->asset_code,
            'serial_number'=> $this->serial_number,
            'asset_type'  => $this->asset_type,
            'purchase_price' => $this->purchase_price,
            'document_number'=> $this->document_number,
            'owner_unit'  => $this->owner_unit,
            'purchase_date'=> $this->purchase_date,
            'condition'   => $this->condition,
            'specifications' => $specsArray, // Simpan sebagai Array (Laravel otomatis convert ke JSON)
        ]);

        session()->flash('message', $this->itemId ? 'Barang berhasil diupdate.' : 'Barang berhasil ditambahkan.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $item = InventoryItem::findOrFail($id);
        $this->itemId = $id;
        
        // Load Data Biasa
        $this->category_id = $item->category_id;
        $this->brand_id = $item->brand_id;
        $this->inventory_id = $item->inventory_id;
        $this->asset_code = $item->asset_code;
        $this->serial_number = $item->serial_number;
        $this->asset_type = $item->asset_type;
        $this->purchase_price = $item->purchase_price;
        $this->document_number = $item->document_number;
        $this->owner_unit = $item->owner_unit;
        // Format tanggal agar pas di input type="date"
        $this->purchase_date = $item->purchase_date ? $item->purchase_date->format('Y-m-d') : null;
        $this->condition = $item->condition;

        // Load Data JSON (Unpacking)
        $specs = $item->specifications ?? [];
        $this->spec_cpu = $specs['cpu'] ?? '';
        $this->spec_ram = $specs['ram'] ?? '';
        $this->spec_storage = $specs['storage'] ?? '';
        $this->spec_gpu = $specs['gpu'] ?? '';
        $this->spec_other = $specs['other'] ?? '';

        $this->openModal();
    }

    public function delete($id)
    {
        InventoryItem::find($id)->delete();
        session()->flash('message', 'Barang dihapus.');
    }

    public function openModal() { $this->isModalOpen = true; }
    public function closeModal() { $this->isModalOpen = false; }
    
    private function resetInputFields()
    {
        $this->reset([
            'itemId', 'category_id', 'brand_id', 'inventory_id', 
            'asset_code', 'serial_number', 'asset_type', 'purchase_price',
            'document_number', 'owner_unit', 'purchase_date', 'condition',
            'spec_cpu', 'spec_ram', 'spec_storage', 'spec_gpu', 'spec_other'
        ]);
    }

    // public function openImportModal()
    // {
    //     $this->isImportModalOpen = true;
    // }

    // public function closeImportModal()
    // {
    //     $this->isImportModalOpen = false;
    //     $this->excelFile = null; // Reset file
    // }

    public function importExcel()
    {
        $this->validate([
            'excelFile' => 'required|mimes:xlsx,csv|max:2048', // Max 2MB
        ]);

        try {
            // Jalankan proses import
            Excel::import(new InventoryItemsImport, $this->excelFile);
            
            session()->flash('message', 'Data berhasil diimport dari Excel!');
            $this->closeImportModal();
        } catch (\Exception $e) {
            // Tangkap error jika format excel salah
            session()->flash('error', 'Gagal Import: ' . $e->getMessage());
        }
    }
}