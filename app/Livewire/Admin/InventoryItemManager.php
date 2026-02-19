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
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.admin')]
class InventoryItemManager extends Component
{
    use WithPagination, WithFileUploads;

    public $excelFile;
    // public $isImportModalOpen = false;

    // --- VARIABEL UTAMA (SEARCH & FILTER) ---
    public $search = '';
    public $filterCategory = '';
    public $filterBrand = '';
    public $filterLocation = '';  // Untuk filter lokasi (tabel inventory)
    public $filterCondition = ''; // Untuk filter kondisi fisik

    public $availableBrands = [];

    public $showQrModal = false;
    public $qrCodeTarget = ''; // Menyimpan Kode Aset (misal: INV/2024/001)
    public $qrCodeUrl = '';    // Link gambar QR
    public $qrItemName = '';   // Nama barang untuk judul modal

    public $isModalOpen = false;
    public $itemId;

    // --- VARIABEL KATEGORI & MERK BARU (On-the-fly) ---
    public $isNewCategory = false;
    public $new_category_name = '';
    
    public $isNewBrand = false;
    public $new_brand_name = '';

    // --- VARIABEL MODAL DELETE ---
    public $isDeleteModalOpen = false;
    public $deleteId = null; // Menyimpan ID barang yang akan dihapus
    public $deleteItemName = '';

    public $activeTab = 'manual'; // Default tab adalah 'manual'

    public $quantity = 1; // Default 1
    public $asset_codes = [''];

    // --- VARIABEL FORM (Mapping Kolom Database) ---
    public $category_id, $brand_id, $inventory_id;
    public $item_name;
    public $asset_code, $serial_number, $asset_type;
    public $purchase_price, $document_number, $owner_unit;
    public $purchase_date, $condition = 'good';

    // --- VARIABEL SPESIFIKASI (Pecahan JSON) ---
    // Kita buat properti terpisah agar mudah di-bind ke Input Form
    public $spec_cpu, $spec_ram, $spec_storage, $spec_gpu, $spec_other;

    public function updatedSearch() { $this->resetPage(); }

    public function updated($property)
    {
        // Jika yang berubah adalah properti filter, kembalikan ke halaman 1
        if (in_array($property, ['search', 'filterCategory', 'filterBrand', 'filterLocation', 'filterCondition'])) {
            $this->resetPage();
        }
    }

    public function updatedFilterCategory($value)
    {
        if (!empty($value)) {
            // Ambil merk yang hanya memiliki category_id tersebut
            $this->availableBrands = InventoryBrand::where('category_id', $value)
                ->orderBy('name')
                ->get();
        } else {
            // Jika kategori dikosongkan, kosongkan juga merk
            $this->availableBrands = [];
        }
        
        // Reset pilihan merk jika kategori berubah agar tidak terjadi konflik data
        $this->filterBrand = ''; 
        $this->resetPage();
    }

    public function updatedQuantity($value)
    {
        // Validasi minimal 1
        if ($value < 1) {
            $this->quantity = 1;
        }

        // Resize array asset_codes sesuai quantity
        // array_pad memastikan inputan yang sudah diketik tidak hilang saat quantity ditambah
        $this->asset_codes = array_pad(array_slice($this->asset_codes, 0, $this->quantity), $this->quantity, '');
    }

    // --- RULES VALIDASI ---
    protected function rules()
    {
        return [
            'quantity' => 'required|integer|min:1',
            // Validasi Dinamis Kategori
            'category_id'       => $this->isNewCategory ? 'nullable' : 'required',
            'new_category_name' => $this->isNewCategory ? 'required|string|max:255|unique:inventory_categories,name' : 'nullable',
            
            // Validasi Dinamis Merk
            'item_name'         => 'required|string|max:255',
            'brand_id'          => $this->isNewBrand ? 'nullable' : 'required',
            'new_brand_name'    => $this->isNewBrand ? 'required|string|max:255|unique:inventory_brands,name' : 'nullable',
            'inventory_id'      => 'nullable|exists:inventory,id', // Boleh null (Gudang)
            'asset_code.*'      => 'required|unique:inventory_items,asset_code,' . $this->itemId,
            'serial_number'     => 'nullable',
            'document_number'   => 'nullable|string|max:255', 
            'owner_unit'        => 'nullable|string|max:255',
            'purchase_price'    => 'nullable|numeric',
            'condition'         => 'required|in:good,maintenance,broken',
            // Specs opsional
            'spec_cpu'          => 'nullable|string',
            'spec_ram'          => 'nullable|string',
        ];
    }

    // --- LOGIC TAMPILKAN QR ASET ---
    public function showQr($id)
    {
        $item = InventoryItem::findOrFail($id);
        
        $this->qrItemName = $item->item_name;
        $this->qrCodeTarget = $item->asset_code; 
        
        // PERBAIKAN: Gunakan URL lengkap menuju halaman scan
        // Misal route name Anda adalah 'inventory.scan.show'
        $fullUrl = route('inventory.scan.show', $item->asset_code);
        
        // Generate QR dengan payload URL lengkap
        $this->qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($fullUrl);
        
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

    // Berjalan otomatis saat dropdown Kategori berubah
    public function updatedCategoryId($value)
    {
        if ($value === 'NEW') {
            $this->isNewCategory = true;
            $this->category_id = null; // Kosongkan ID agar tidak error
        }
    }

    // Berjalan otomatis saat dropdown Merk berubah
    public function updatedBrandId($value)
    {
        if ($value === 'NEW') {
            $this->isNewBrand = true;
            $this->brand_id = null; // Kosongkan ID
        }
    }

    // Aksi saat tombol silang (batal) diklik di frontend
    public function cancelNewCategory()
    {
        $this->isNewCategory = false;
        $this->new_category_name = '';
        $this->category_id = ''; // Kembalikan ke opsi "Pilih Kategori..."
    }

    public function cancelNewBrand()
    {
        $this->isNewBrand = false;
        $this->new_brand_name = '';
        $this->brand_id = ''; // Kembalikan ke opsi "Pilih Merk..."
    }

    #[Computed]
    public function inventories()
    {
        return Inventory::orderBy('name')->get();
    }

    #[Computed]
    public function categories()
    {
        return InventoryCategory::orderBy('name')->get();
    }

    #[Computed]
    public function availableBrands()
    {
        // Jika Kategori kosong, kembalikan array kosong (mengunci data)
        if (empty($this->filterCategory)) {
            return collect(); 
        }

        // LOGIKA PENCARIAN MERK INDEPENDEN
        // Karena Kategori dan Merk bertemu di tabel Inventaris, kita cari Merk
        // yang secara aktual memiliki barang di Kategori yang dipilih admin.
        return InventoryBrand::whereHas('inventories', function ($query) {
            $query->where('category_id', $this->filterCategory);
        })->get();
    }

    #[computed]
    public function activeConditions()
    {
        // Mengambil nilai unik: ['good', 'broken', dll] hanya yang ada di database
        return InventoryItem::select('condition')
            ->distinct()
            ->pluck('condition');
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

        if ($this->filterLocation) {
            if ($this->filterLocation === 'NULL') {
                // Jika user memilih opsi Gudang/Stok
                $query->whereNull('inventory_id');
            } else {
                // Jika user memilih ruangan spesifik
                $query->where('inventory_id', $this->filterLocation);
            }
        }

        if ($this->filterCondition) {
            $query->where('condition', $this->filterCondition);
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
            'activeConditions' => \App\Models\InventoryItem::select('condition')
            ->distinct()
            ->pluck('condition')
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

        // 1. EKSEKUSI PEMBUATAN KATEGORI BARU
        if ($this->isNewCategory && !empty($this->new_category_name)) {
            $category = InventoryCategory::create([
                'name' => $this->new_category_name
            ]);
            $this->category_id = $category->id;
        }

        // 2. EKSEKUSI PEMBUATAN MERK BARU
        if ($this->isNewBrand && !empty($this->new_brand_name)) {
            $brand = InventoryBrand::create([
                'name' => $this->new_brand_name,
                'category_id' => $this->category_id
            ]);
            $this->brand_id = $brand->id;
        }

        // 3. PACKING JSON
        $specsArray = [
            'cpu'     => $this->spec_cpu,
            'ram'     => $this->spec_ram,
            'storage' => $this->spec_storage,
            'gpu'     => $this->spec_gpu,
            'other'   => $this->spec_other,
        ];

        // 4. EKSEKUSI PENYIMPANAN DATABASE (TRANSACTION)
        DB::transaction(function () use ($specsArray) {
            
            // Loop melalui array asset_codes (hasil input quantity > 1)
            foreach ($this->asset_codes as $code) {
                
                // Skip jika kode kosong
                if (empty($code)) continue;

                InventoryItem::updateOrCreate(
                    // KUNCI PENCARIAN (WHERE):
                    ['asset_code' => $code], 

                    // DATA YANG DISIMPAN/DIUPDATE:
                    [
                        'name'            => $this->item_name, // Pastikan field nama barang tersimpan
                        'category_id'     => $this->category_id,
                        'brand_id'        => $this->brand_id,
                        'inventory_id'    => $this->inventory_id ?: null,
                        'asset_code'      => $code, // PERBAIKAN: Gunakan $code dari loop, bukan $this->asset_code
                        'serial_number'   => $this->serial_number, // Peringatan: Jika input massal, SN ini akan sama semua
                        'asset_type'      => $this->asset_type,
                        'purchase_price'  => $this->purchase_price,
                        'document_number' => $this->document_number,
                        'owner_unit'      => $this->owner_unit,
                        'purchase_date'   => $this->purchase_date,
                        'condition'       => $this->condition,
                        'specifications'  => $specsArray,
                    ]
                );
            }
        });

        // 5. FLASH MESSAGE & RESET
        session()->flash('message', $this->itemId ? 'Barang berhasil diupdate.' : 'Barang berhasil ditambahkan.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $item = InventoryItem::findOrFail($id);
        $this->itemId = $id;
        
        // Load Data Biasa
        $this->item_name = $item->item_name;
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

    public function delete()
    {
        // Validasi ganda untuk memastikan ID benar-benar ada sebelum dihapus
        if ($this->deleteId) {
            InventoryItem::findOrFail($this->deleteId)->delete();
            
            session()->flash('message', 'Barang berhasil dihapus.');
            $this->closeDeleteModal(); // Tutup modal setelah berhasil
        }
    }

    public function openModal() { $this->isModalOpen = true; }
    public function closeModal() { $this->isModalOpen = false; }
    
    private function resetInputFields()
    {
        $this->reset([
            'itemId', 'item_name', 'category_id', 'brand_id', 'inventory_id', 
            'asset_code', 'serial_number', 'asset_type', 'purchase_price',
            'document_number', 'owner_unit', 'purchase_date', 'condition',
            'spec_cpu', 'spec_ram', 'spec_storage', 'spec_gpu', 'spec_other',
            'isNewCategory', 'new_category_name', 'isNewBrand', 'new_brand_name',
            'quantity', 'asset_codes'
        ]);

        $this->asset_codes = ['']; 
        $this->quantity = 1;
    }

    public function confirmDelete($id)
    {
        $item = InventoryItem::findOrFail($id); // Cari datanya dulu
        
        $this->deleteId = $id;
        $this->deleteItemName = $item->item_name; // Simpan namanya untuk ditampilkan di Modal
        $this->isDeleteModalOpen = true;
    }

    public function closeDeleteModal()
    {
        $this->isDeleteModalOpen = false;
        $this->deleteId = null;
    }

    public function importExcel()
    {
        $this->validate([
            'excelFile' => 'required|mimes:xlsx,csv|max:2048',
        ]);

        try {
            Excel::import(new InventoryItemsImport, $this->excelFile);
            
            session()->flash('message', 'Data berhasil diimport dari Excel!');
            
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // 1. TANGKAP ERROR VALIDASI EXCEL (Termasuk Duplikat)
            $failures = $e->failures();
            
            // Ambil error dari baris pertama yang bermasalah agar pesan tidak terlalu panjang
            if (count($failures) > 0) {
                $first = $failures[0];
                $pesan = "Gagal Import! Baris Excel ke-{$first->row()}: {$first->errors()[0]}";
                session()->flash('error', $pesan);
            }

         
        } catch (\Exception $e) {
            // 3. TANGKAP ERROR SISTEM UMUM
            session()->flash('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }

        $this->closeModal();
    }
    
    public function customValidationMessages()
    {
        return [
            'asset_code.unique' => 'Kode Aset ini sudah terdaftar di sistem. Tidak boleh ada duplikat.',
            'asset_code.required' => 'Kolom Kode Aset tidak boleh kosong.',
        ];
    }

    public function resetFilters()
    {
        $this->reset(['filterCategory', 'filterBrand', 'filterLocation', 'filterCondition', 'search']);
        $this->resetPage(); // Selalu reset paginasi ke halaman 1 saat filter diubah
    }
}