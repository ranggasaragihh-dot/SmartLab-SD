<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryScanController extends Controller
{
    public function show($asset_code)
    {
        $item = InventoryItem::where('asset_code', $asset_code)->firstOrFail();
        return view('inventory.detail-scan', compact('item'));
    }
}