<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cluster extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        // 'location',
        'description',
        'type',
        'is_active',
    ];

    // Relasi: Satu Lab punya banyak PC/Meja
    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    // public function inventory()
    // {
    //     // Parameter 2: nama kolom foreign key di tabel inventories
    //     // Parameter 3: nama kolom primary key di tabel clusters
    //     return $this->hasMany(Inventory::class, 'cluster_id', 'id');
    // }
}
