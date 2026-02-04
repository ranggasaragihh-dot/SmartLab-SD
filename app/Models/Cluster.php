<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Cluster extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'denah_image',
        'delay_time',
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

    public function bookings()
    {
        return $this->hasManyThrough(
            Booking::class,    // Model Tujuan (Booking)
            Inventory::class,  // Model Perantara (Inventory/Unit)
            'cluster_id',      // Foreign Key di tabel Inventory
            'inventory_id',    // Foreign Key di tabel Booking
            'id',              // Local Key di tabel Cluster
            'id'               // Local Key di tabel Inventory
        );
    }

    public function getDenahUrlAttribute()
    {
        return $this->denah_image 
            ? Storage::url($this->denah_image) 
            : asset('images/default-denah.jpg'); // Gambar default jika kosong
    }

    // public function inventory()
    // {
    //     // Parameter 2: nama kolom foreign key di tabel inventories
    //     // Parameter 3: nama kolom primary key di tabel clusters
    //     return $this->hasMany(Inventory::class, 'cluster_id', 'id');
    // }
}
