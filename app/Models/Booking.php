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
        'cluster_id',
        'inventory_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class)->withDefault(); 
    }
    
    public function getLabelUnitAttribute()
    {
        if ($this->inventory_id) {
            // Jika inventory_id ada, berarti dia booking PC spesifik
            return $this->inventory->name ?? 'Unknown Unit';
        }
        return 'Full Room: ' . ($this->cluster->name ?? 'Unknown Room');
    }

    public function getIsRoomBookingAttribute(): bool
    {
        return is_null($this->inventory_id);
    }
}