<?php

namespace App\Filament\Resources\InventoryBrands\Pages;

use App\Filament\Resources\InventoryBrands\InventoryBrandResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInventoryBrands extends ListRecords
{
    protected static string $resource = InventoryBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
