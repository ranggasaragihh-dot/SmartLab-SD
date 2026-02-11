<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_items';

    // 1. FILLABLE LENGKAP (Sesuai kolom Excel & Migration)
    protected $fillable = [
        'category_id',
        'brand_id',
        'inventory_id', // Link ke Meja/Slot (Bisa Null jika di gudang)
        'item_name',
        'asset_code',   // Kode Asset Excel
        'serial_number',
        'asset_type',   // Aktiva
        'purchase_price',
        'document_number',
        'owner_unit',
        'purchase_date',
        'condition',    // good, maintenance, broken
        'specifications', // Kolom JSON
    ];

    // 2. CASTING (PENTING)
    protected $casts = [
        'specifications' => 'array', // JSON otomatis jadi Array PHP
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
    ];

    // 3. RELASI
    public function inventory() // Relasi ke Meja/Slot
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

    // 4. HELPER ACCESSOR (Opsional: Agar mudah dipanggil di Blade)
    // Contoh: $item->cpu_spec
    public function getCpuSpecAttribute()
    {
        return $this->specifications['cpu'] ?? '-';
    }

    public function getRamSpecAttribute()
    {
        return $this->specifications['ram'] ?? '-';
    }
}