<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_items';

    protected $fillable = [
        'category_id',
        'brand_id',
        'inventory_id',
        'item_name',
        'asset_code',
        'serial_number',
        'asset_type',
        'purchase_price',
        'document_number',
        'owner_unit',
        'purchase_date',
        'condition',
        'specifications',
    ];

    protected $casts = [
        'specifications' => 'array',
        'purchase_date'  => 'date',
        'purchase_price' => 'decimal:2',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
    
    public function category()
    {
        return $this->belongsTo(InventoryCategory::class);
    }
    
    public function brand()
    {
        return $this->belongsTo(InventoryBrand::class);
    }

    public function getCpuSpecAttribute()
    {
        return $this->specifications['cpu'] ?? '-';
    }

    public function getRamSpecAttribute()
    {
        return $this->specifications['ram'] ?? '-';
    }
}