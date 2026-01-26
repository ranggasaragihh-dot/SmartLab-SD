<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;
    // Agar bisa diisi massal
    protected $guarded = []; 
    protected $table = 'inventory_items';

    // PENTING: Mengubah JSON DB menjadi Array PHP otomatis
    protected $fillable = [
        'category_id',
        'brand_id',
        'inventory_id',
        'serial_number',
        'purchase_date',
        'condition',
        'specifications', // Kolom JSON
    ];

    protected $casts = [
        'specifications' => 'array', // Wajib dicast ke array agar bisa jadi Key-Value
        'purchase_date' => 'date',
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
}