<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Inventory extends Model
{
    protected $table = 'inventory';
    protected $guarded = ['id'];
    use HasFactory;

    protected $fillable = [
        'cluster_id',
        'name',
        'brand',
        'qr_token',
        'status',
        'admin_override',
        'mac_address',
        'is_bookable',
    ];

    protected $casts = [
        'is_bookable' => 'boolean', // Pastikan baris ini ada!
    ];

    protected static function booted()
    {
        static::creating(function ($inventory) {
            // Jika qr_token tidak diisi manual, buatkan otomatis
            if (empty($inventory->qr_token)) {
                // Generate string random 32 karakter (unik)
                $inventory->qr_token = (string) Str::uuid(); 
            }
        });
    }

    // Barang milik satu Lab
    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    // Relasi ke Items (Barang yang nempel)
    public function item()
    {
        return $this->hasOne(InventoryItem::class, 'inventory_id');
    }

    // Relasi ke Booking
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function activeBooking()
    {
        // Mengambil satu data booking terbaru yang statusnya 'booked' atau 'active'
        return $this->hasOne(Booking::class)->latestOfMany()->where('status', 'booked');
    }
}
