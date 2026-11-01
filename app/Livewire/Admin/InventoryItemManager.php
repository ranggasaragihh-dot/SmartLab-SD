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
use Maatwebsite\Excel\Validators\ValidationException;

#[Layout('layouts.admin')]
class InventoryItemManager extends Component
{
    use WithPagination, WithFileUploads;

    public $excelFile;
    public $search = '';
    public $filterCategory = '';
    public $filterBrand = '';
    public $filterLocation = '';
    public $filterCondition = ''; 

    public $availableBrands = [];

    public $showQrModal = false;
    public $qrCodeTarget = '';
    public $qrCodeUrl = '';
    public $qrItemName = '';

    public $isModalOpen = false;
    public $itemId;

    public $isNewCategory = false;
    public $new_category_name = '';
    
    public $isNewBrand = false;
    public $new_brand_name = '';

    public $isDeleteModalOpen = false;
    public $deleteId = null;
    public $deleteItemName = '';

    public $activeTab = 'manual';

    public $quantity = 1;
    public $asset_codes = [''];

    public $category_id, $brand_id, $inventory_id;
    public $item_name;
    public $asset_code, $serial_number, $asset_type;
    public $purchase_price, $document_number, $owner_unit;
    public $purchase_date, $condition = 'good';

    public $isNewAssetType = false;
    public $new_asset_type_name = '';

    public $spec_cpu, $spec_ram, $spec_storage, $spec_gpu, $spec_other;

    public function updatedSearch() { $this->resetPage(); }

    public function updated($property)
    {
        if (in_array($property, ['search', 'filterCategory', 'filterBrand', 'filterLocation', 'filterCondition'])) {
            $this->resetPage();
        }
    }

    public function updatedFilterCategory($value)
    {
        if (!empty($value)) {
            $this->availableBrands = InventoryBrand::where('category_id', $value)
                ->orderBy('name')
                ->get();
        } else {
            $this->availableBrands = [];
        }

        $this->filterBrand = ''; 
        $this->resetPage();
    }

    public function updatedQuantity($value)
    {
        if ($value < 1) {
            $this->quantity = 1;
        }
        $this->asset_codes = array_pad(array_slice($this->asset_codes, 0, $this->quantity), $this->quantity, '');
    }

    protected function rules()
    {
        return [
            'quantity' => 'required|integer|min:1',
            'category_id'       => $this->isNewCategory ? 'nullable' : 'required',
            'new_category_name' => $this->isNewCategory ? 'required|string|max:255|unique:inventory_categories,name' : 'nullable',
            'item_name'         => 'required|string|max:255',
            'brand_id'          => $this->isNewBrand ? 'nullable' : 'required',
            'new_brand_name'    => $this->isNewBrand ? 'required|string|max:255|unique:inventory_brands,name' : 'nullable',
            'inventory_id'      => 'nullable|exists:inventory,id',
            'asset_codes.*'     => 'required|unique:inventory_items,asset_code,' . $this->itemId,
            'serial_number'     => 'nullable',
            'document_number'   => 'nullable|string|max:255', 
            'asset_type'          => $this->isNewAssetType ? 'nullable' : 'required|string',
            'new_asset_type_name' => $this->isNewAssetType ? 'required|string|max:255' : 'nullable',
            'owner_unit'        => 'nullable|string|max:255',
            'purchase_price'    => 'nullable|numeric',
            'condition'         => 'required|in:good,maintenance,broken',
            'spec_cpu'          => 'nullable|string',
            'spec_ram'          => 'nullable|string',
        ];
    }

    public function showQr($id)
    {
        $item = InventoryItem::findOrFail($id);
        $this->qrItemName = $item->item_name;
        $this->qrCodeTarget = $item->asset_code; 
        
        $fullUrl = route('inventory.scan.show', $item->asset_code);
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
        if (empty($this->qrCodeUrl)) return;

        $response = Http::get($this->qrCodeUrl);

        if ($response->successful()) {
            $safeFileName = 'QR-' . str_replace(['/', '\\'], '-', $this->qrCodeTarget) . '.png';
            return response()->streamDownload(function () use ($response) {
                echo $response->body();
            }, $safeFileName);
        }
    }

    public function updatedCategoryId($value)
    {
        if ($value === 'NEW') {
            $this->isNewCategory = true;
            $this->category_id = null;
        }
    }

    public function updatedBrandId($value)
    {
        if ($value === 'NEW') {
            $this->isNewBrand = true;
            $this->brand_id = null;
        }
    }

    public function cancelNewCategory()
    {
        $this->isNewCategory = false;
        $this->new_category_name = '';
        $this->category_id = '';
    }

    public function cancelNewBrand()
    {
        $this->isNewBrand = false;
        $this->new_brand_name = '';
        $this->brand_id = '';
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
        if (empty($this->filterCategory)) {
            return collect(); 
        }

        return InventoryBrand::whereHas('inventories', function ($query) {
            $query->where('category_id', $this->filterCategory);
        })->get();
    }

    #[computed]
    public function activeConditions()
    {
        return InventoryItem::select('condition')
            ->distinct()
            ->pluck('condition');
    }

    #[Computed]
    public function availableAssetTypes()
    {
        return InventoryItem::whereNotNull('asset_type')
            ->where('asset_type', '!=', '')
            ->distinct()
            ->orderBy('asset_type')
            ->pluck('asset_type');
    }

    public function updatedAssetType($value)
    {
        if ($value === 'NEW') {
            $this->isNewAssetType = true;
            $this->asset_type = null; 
        }
    }

    public function cancelNewAssetType()
    {
        $this->isNewAssetType = false;
        $this->new_asset_type_name = '';
        $this->asset_type = ''; 
    }

    public function render()
    {
        $query = InventoryItem::query()
            ->with(['brand', 'category', 'inventory']);

        if ($this->search) {
            $query->where(function (Builder $q) {
                $q->where('item_name', 'like', '%' . $this->search . '%')
                  ->orWhere('asset_code', 'like', '%' . $this->search . '%')
                  ->orWhereHas('brand', function ($b) {
                      $b->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('category', function ($c) {
                      $c->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->filterCategory) {
            $query->where('category_id', $this->filterCategory);
        }
        
        if ($this->filterBrand) {
            $query->where('brand_id', $this->filterBrand);
        }

        if ($this->filterLocation) {
            if ($this->filterLocation === 'NULL') {
                $query->whereNull('inventory_id');
            } else {
                $query->where('inventory_id', $this->filterLocation);
            }
        }

        if ($this->filterCondition) {
            $query->where('condition', $this->filterCondition);
        }

        return view('livewire.admin.inventory-item-manager', [
            'items'            => $query->latest()->paginate(10),
            'inventories'      => Inventory::orderBy('name')->get(),
            'categories'       => InventoryCategory::orderBy('name')->get(),
            'brands'           => InventoryBrand::orderBy('name')->get(),
            'activeConditions' => InventoryItem::select('condition')
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

        if ($this->isNewCategory && !empty($this->new_category_name)) {
            $category = InventoryCategory::create([
                'name' => $this->new_category_name
            ]);
            $this->category_id = $category->id;
        }

        if ($this->isNewBrand && !empty($this->new_brand_name)) {
            $brand = InventoryBrand::create([
                'name' => $this->new_brand_name,
                'category_id' => $this->category_id
            ]);
            $this->brand_id = $brand->id;
        }

        $specsArray = [
            'cpu'     => $this->spec_cpu,
            'ram'     => $this->spec_ram,
            'storage' => $this->spec_storage,
            'gpu'     => $this->spec_gpu,
            'other'   => $this->spec_other,
        ];

        $finalAssetType = $this->isNewAssetType 
        ? ucwords(strtolower(trim($this->new_asset_type_name))) 
        : $this->asset_type;

        DB::transaction(function () use ($specsArray, $finalAssetType) {

            if ($this->itemId) {
                $item = InventoryItem::findOrFail($this->itemId);
                
                $item->update([
                    'asset_code'      => $this->asset_codes[0], 
                    'item_name'       => $this->item_name,
                    'category_id'     => $this->category_id,
                    'brand_id'        => $this->brand_id,
                    'inventory_id'    => $this->inventory_id ?: null,
                    'serial_number'   => $this->serial_number,
                    'asset_type'      => $finalAssetType,
                    'purchase_price'  => $this->purchase_price,
                    'document_number' => $this->document_number,
                    'owner_unit'      => $this->owner_unit,
                    'purchase_date'   => $this->purchase_date,
                    'condition'       => $this->condition,
                    'specifications'  => $specsArray,
                ]);

            }
            else{
                foreach ($this->asset_codes as $code) {
                    if (empty($code)) continue;

                    InventoryItem::updateOrCreate(
                        ['asset_code' => $code],
                        [
                            'item_name'       => $this->item_name,
                            'category_id'     => $this->category_id,
                            'brand_id'        => $this->brand_id,
                            'inventory_id'    => $this->inventory_id ?: null,
                            'asset_code'      => $code,
                            'serial_number'   => $this->serial_number,
                            'asset_type'      => $this->asset_type,
                            'purchase_price'  => $this->purchase_price,
                            'document_number' => $this->document_number,
                            'asset_type'      => $finalAssetType,
                            'owner_unit'      => $this->owner_unit,
                            'purchase_date'   => $this->purchase_date,
                            'condition'       => $this->condition,
                            'specifications'  => $specsArray,
                        ]
                    );
                }
            }
        });

        session()->flash('message', $this->itemId ? 'Barang berhasil diupdate.' : 'Barang berhasil ditambahkan.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $item = InventoryItem::findOrFail($id);
        $this->itemId = $id;
        $this->item_name = $item->item_name;
        $this->category_id = $item->category_id;
        $this->brand_id = $item->brand_id;
        $this->inventory_id = $item->inventory_id;
        $this->quantity = 1;
        $this->asset_codes = [$item->asset_code];
        $this->serial_number = $item->serial_number;
        $this->asset_type = $item->asset_type;
        $this->isNewAssetType = false; 
        $this->new_asset_type_name = '';
        $this->purchase_price = $item->purchase_price;
        $this->document_number = $item->document_number;
        $this->owner_unit = $item->owner_unit;
        $this->purchase_date = $item->purchase_date ? $item->purchase_date->format('Y-m-d') : null;
        $this->condition = $item->condition;

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
        if ($this->deleteId) {
            InventoryItem::findOrFail($this->deleteId)->delete();
            session()->flash('message', 'Barang berhasil dihapus.');
            $this->closeDeleteModal();
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
        $item = InventoryItem::findOrFail($id);
        
        $this->deleteId = $id;
        $this->deleteItemName = $item->item_name;
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
            // Semua baris tersimpan atau tidak sama sekali (DPPL: bulk insert jika tidak ada anomali).
            DB::transaction(fn () => Excel::import(new InventoryItemsImport, $this->excelFile));

            session()->flash('message', 'Data berhasil diimport dari Excel!');
            
        } catch (ValidationException $e) {
            $failures = $e->failures();
            
            if (count($failures) > 0) {
                $first = $failures[0];
                $pesan = "Gagal Import! Baris Excel ke-{$first->row()}: {$first->errors()[0]}";
                session()->flash('error', $pesan);
            }

         
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }

        $this->closeModal();
    }
    
    public function customValidationMessages()
    {
        return [
            'asset_code.unique'   => 'Kode Aset ini sudah terdaftar di sistem. Tidak boleh ada duplikat.',
            'asset_code.required' => 'Kolom Kode Aset tidak boleh kosong.',
        ];
    }

    public function resetFilters()
    {
        $this->reset(['filterCategory', 'filterBrand', 'filterLocation', 'filterCondition', 'search']);
        $this->resetPage();
    }
}