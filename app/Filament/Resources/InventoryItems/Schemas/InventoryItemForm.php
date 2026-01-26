<?php

namespace App\Filament\Resources\InventoryItems\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class InventoryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('category_id')
                    ->required()
                    ->numeric(),
                TextInput::make('brand_id')
                    ->required()
                    ->numeric(),
                TextInput::make('inventory_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('serial_number')
                    ->required(),
                TextInput::make('model_name')
                    ->default(null),
                DatePicker::make('purchase_date'),
                Select::make('condition')
                    ->options(['good' => 'Good', 'maintenance' => 'Maintenance', 'broken' => 'Broken'])
                    ->default('good')
                    ->required(),
                Textarea::make('specifications')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
