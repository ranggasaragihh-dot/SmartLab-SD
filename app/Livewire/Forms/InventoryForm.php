<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;
use App\Models\Inventory;
use Illuminate\Support\Str;

class InventoryForm extends Form
{
    // Tambahkan properti ID untuk memastikan kita tahu ini mode Edit
    public $id = null; 

    // Properti Model (Opsional, tapi bagus untuk helper)
    public ?Inventory $inventory = null;

    #[Validate('required|exists:clusters,id')]
    public $cluster_id = '';

    #[Validate('required|string|max:255')]
    public $name = '';

    // public $mac_address = '';

    #[Validate('required|in:available,maintenance,broken')]
    public $status = 'available';

    // #[Validate('required|in:none,force_open,force_close')]
    // public $admin_override = 'none';

    #[Validate('required|in:0,1')]
    public $is_bookable = 0;

    public $qr_token = '';

    // protected function rules()
    // {
    //     // Logika Unik: Abaikan ID sendiri jika sedang mode Edit ($this->id)
    //     $uniqueRule = 'unique:inventories,mac_address';
    //     if ($this->id) {
    //         $uniqueRule .= ',' . $this->id;
    //     }

    //     return [
    //         // Kita gabungkan rules dari Attributes + Rules manual ini
    //         'mac_address' => ['nullable', 'string', 'max:255', $uniqueRule],
    //     ];
    // }

    // FUNGSI INI DIPANGGIL SAAT TOMBOL EDIT DITEKAN
    public function setInventory(Inventory $inventory)
    {
        $this->inventory = $inventory;
        $this->id = $inventory->id; // Simpan ID
        
        // Isi form dengan data lama
        $this->cluster_id = $inventory->cluster_id;
        $this->name = $inventory->name;
        // $this->mac_address = $inventory->mac_address;
        $this->status = $inventory->status;
        // $this->admin_override = $inventory->admin_override;
        $this->is_bookable = intval($inventory->is_bookable);
        $this->qr_token = $inventory->qr_token;
    }

    // FUNGSI SIMPAN (CREATE ATAU UPDATE)
    public function save()
    {
        $this->validate();

        // 1. Tentukan QR Token (Generate baru jika kosong)
        $token = $this->qr_token ?: Str::random(32);

        // 2. Siapkan Data
        $data = [
            'cluster_id'          => $this->cluster_id,
            'name'                => $this->name,
            // 'mac_address'         => $this->mac_address,
            'status'              => $this->status,
            // 'admin_override'      => $this->admin_override,
            'is_bookable'         => $this->is_bookable,
            'qr_token'            => $token,
        ];

        // 3. Logic Penentuan Update vs Create
        if ($this->id) {
            // Jika ada ID, berarti UPDATE
            $item = Inventory::find($this->id);
            if ($item) {
                $item->update($data);
            }
        } else {
            // Jika tidak ada ID, berarti CREATE BARU
            Inventory::create($data);
        }

        // dd([
        //     'STATUS_ID' => $this->id,
        //     'DATA_INPUT' => $this->all(),
        //     'MODE' => $this->id ? 'Harusnya UPDATE' : 'Harusnya CREATE'
        // ]);
        
        // Reset form setelah simpan
        $this->reset();
    }
}