<?php

namespace App\Filament\Resources\InventoryBrands\Pages;

use App\Filament\Resources\InventoryBrands\InventoryBrandResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInventoryBrand extends EditRecord
{
    protected static string $resource = InventoryBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
