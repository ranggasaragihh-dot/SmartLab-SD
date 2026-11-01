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
        'early_checkin_minutes',
        'description',
        'type',
        'requires_ta',
        'is_active',
        'min_duration_minutes',
        'max_duration_minutes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_ta' => 'boolean',
        'delay_time' => 'integer',
        'early_checkin_minutes' => 'integer',
        'max_duration_minutes' => 'integer',
    ];

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function canBeBookedBy(User $user): bool
    {
        return !$this->requires_ta || ($user->hasRole('student') && (bool) $user->ta);
    }

    public function getDenahUrlAttribute()
    {
        return $this->denah_image 
            ? Storage::disk('public')->url($this->denah_image) 
            : asset('images/default-denah.jpg');
    }
}