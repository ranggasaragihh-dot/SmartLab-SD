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
        'description',
        'type',                 // 'public' / 'private'
        'is_active',
        'max_duration_minutes', // <--- WAJIB DITAMBAHKAN
    ];

    /**
     * Casting tipe data agar output JSON/Array konsisten.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'delay_time' => 'integer',
        'max_duration_minutes' => 'integer',
    ];

    /**
     * Relasi ke Unit Inventory (PC/Meja)
     * Saya ubah nama method jadi jamak 'inventories' (Best Practice Laravel)
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Relasi ke Booking
     * PERBAIKAN: Ganti hasManyThrough menjadi hasMany biasa.
     * * Alasan: Tabel bookings sekarang sudah punya kolom 'cluster_id'.
     * Keuntungan: 
     * 1. Query lebih cepat (tidak perlu join tabel inventory).
     * 2. Bisa membaca booking tipe "Whole Room" (yang inventory_id-nya null).
     * (Jika pakai hasManyThrough, booking ruangan tidak akan terdeteksi).
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // Accessor URL Gambar
    public function getDenahUrlAttribute()
    {
        return $this->denah_image 
            ? Storage::url($this->denah_image) 
            : asset('images/default-denah.jpg');
    }
}