<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;
use App\Models\Inventory;
use App\Models\InventoryItem;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InventoryForm extends Form
{
    public $id = null; 
    public ?Inventory $inventory = null;

    #[Validate('required|exists:clusters,id')]
    public $cluster_id = '';

    public $name = '';

    #[Validate('required|in:available,maintenance,broken')]
    public $status = 'available';

    #[Validate('required|in:0,1')]
    public $is_bookable = 1;

    public $qr_token = '';

    #[Validate('nullable|exists:inventory_items,id')]
    public $selected_item_id = ''; 

    public function setInventory(Inventory $inventory)
    {
        $this->inventory = $inventory;
        $this->id = $inventory->id;
        $this->cluster_id = $inventory->cluster_id;
        $this->name = $inventory->name;
        $this->status = $inventory->status;
        $this->is_bookable = intval($inventory->is_bookable);
        $this->qr_token = $inventory->qr_token;

        $currentItem = InventoryItem::where('inventory_id', $inventory->id)->first();
        $this->selected_item_id = $currentItem ? $currentItem->id : '';
    }

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('inventory', 'name')->ignore($this->id)],
        ];
    }

    protected function messages()
    {
        return ['name.unique' => 'Nama unit ini sudah dipakai.'];
    }

    public function save()
    {
        $this->validate();
        $token = $this->qr_token ?: Str::random(32);

        $data = [
            'cluster_id'  => $this->cluster_id,
            'name'        => $this->name,
            'status'      => $this->status,
            'is_bookable' => $this->is_bookable,
            'qr_token'    => $token,
        ];

        if ($this->id) {
            $inventoryRecord = Inventory::find($this->id);
            $inventoryRecord->update($data);
        } else {
            $inventoryRecord = Inventory::create($data);
            $this->id = $inventoryRecord->id;
        }

        InventoryItem::where('inventory_id', $this->id)->update(['inventory_id' => null]);

        if ($this->selected_item_id) {
            $newItem = InventoryItem::find($this->selected_item_id);
            
            if ($newItem) {
                $newItem->update(['inventory_id' => $this->id]);
            }
        } 
        $this->reset();
    }
}