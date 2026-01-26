<?php

namespace App\Filament\Resources\InventoryBrands;

use App\Filament\Resources\InventoryBrands\Pages\CreateInventoryBrand;
use App\Filament\Resources\InventoryBrands\Pages\EditInventoryBrand;
use App\Filament\Resources\InventoryBrands\Pages\ListInventoryBrands;
use App\Filament\Resources\InventoryBrands\Schemas\InventoryBrandForm;
use App\Filament\Resources\InventoryBrands\Tables\InventoryBrandsTable;
use App\Models\InventoryBrand;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class InventoryBrandResource extends Resource
{
    protected static ?string $model = InventoryBrand::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Brand';

    public static function form(Schema $schema): Schema
    {
        // return InventoryBrandForm::configure($schema);
        return $schema->components([
            TextInput::make('name')->required()->unique(ignoreRecord: true),
        ]);
    }

    public static function table(Table $table): Table
    {
        // return InventoryBrandsTable::configure($table);
        return $table
            ->columns([
                TextColumn::make('name'),
                // TextColumn::make('category.name'),
                // TextColumn::make('resource.code'),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInventoryBrands::route('/'),
            'create' => CreateInventoryBrand::route('/create'),
            'edit' => EditInventoryBrand::route('/{record}/edit'),
        ];
    }
}
