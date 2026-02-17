<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryBrand extends Model
{
    protected $fillable = [
        'name', 'category_id', 'slug'
    ];

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }
}
