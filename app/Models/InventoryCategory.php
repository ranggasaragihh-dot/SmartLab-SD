<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryCategory extends Model
{
    protected $fillable = [
        'name', 'description'
    ];
    
    public function brands()
    {
        return $this->hasMany(InventoryBrand::class, 'category_id');
    }
}
