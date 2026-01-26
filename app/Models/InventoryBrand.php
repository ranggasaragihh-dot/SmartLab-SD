<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryBrand extends Model
{
    protected $fillable = [
        'name', 'slug'
    ];
}
