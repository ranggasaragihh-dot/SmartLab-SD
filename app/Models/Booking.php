<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cluster_id',   // <--- WAJIB DITAMBAHKAN (Agar tahu ini booking di Lab mana)
        'inventory_id', // <--- Boleh Null (Jika null = Booking Ruangan)
        'start_time',
        'end_time',
        'check_in_time',
        'status',
        'purpose',
        'cancel_reason'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'check_in_time' => 'datetime'
    ];

    // Relasi ke User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Cluster (Induk Ruangan) -- WAJIB ADA
    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    // Relasi ke Inventory (Unit PC)
    // withDefault() berguna mencegah error "Attempt to read property of null" di View
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class)->withDefault(); 
    }

    // ------------------------------------------------------------------
    // ACCESSOR (Kecerdasan Buatan untuk View)
    // ------------------------------------------------------------------
    
    /**
     * Helper untuk menampilkan apa yang dibooking.
     * Panggil di blade: {{ $booking->label_unit }}
     */
    public function getLabelUnitAttribute()
    {
        if ($this->inventory_id) {
            // Jika inventory_id ada, berarti dia booking PC spesifik
            return $this->inventory->name ?? 'Unknown Unit';
        }

        // Jika inventory_id NULL, berarti dia booking ruangan
        // Kita ambil nama clusternya
        return 'Full Room: ' . ($this->cluster->name ?? 'Unknown Room');
    }

    /**
     * Helper boolean untuk cek tipe booking.
     * Panggil di blade: @if($booking->is_room_booking) ... @endif
     */
    public function getIsRoomBookingAttribute(): bool
    {
        return is_null($this->inventory_id);
    }
}