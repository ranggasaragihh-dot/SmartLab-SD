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
        'qr_token',
        'status',
        'is_bookable',
        'last_seen_at',
    ];

    protected $casts = [
        'is_bookable' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($inventory) {
            if (empty($inventory->qr_token)) {
                $inventory->qr_token = (string) Str::uuid(); 
            }
        });
    }

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    public function item()
    {
        return $this->hasOne(InventoryItem::class, 'inventory_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function activeBooking()
    {
        return $this->hasOne(Booking::class)->latestOfMany()->where('status', 'booked');
    }
}
