<?php

namespace App\Filament\Resources\InventoryCategories;

use App\Filament\Resources\InventoryCategories\Pages\CreateInventoryCategory;
use App\Filament\Resources\InventoryCategories\Pages\EditInventoryCategory;
use App\Filament\Resources\InventoryCategories\Pages\ListInventoryCategories;
use App\Filament\Resources\InventoryCategories\Schemas\InventoryCategoryForm;
use App\Filament\Resources\InventoryCategories\Tables\InventoryCategoriesTable;
use App\Models\InventoryCategory;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class InventoryCategoryResource extends Resource
{
    protected static ?string $model = InventoryCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Category';

    public static function form(Schema $schema): Schema
    {
        // return InventoryCategoryForm::configure($schema);
        return $schema->components([
            TextInput::make('name')->required()->unique(ignoreRecord: true)
        ]);
    }

    public static function table(Table $table): Table
    {
        // return InventoryCategoriesTable::configure($table);
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
            'index' => ListInventoryCategories::route('/'),
            'create' => CreateInventoryCategory::route('/create'),
            'edit' => EditInventoryCategory::route('/{record}/edit'),
        ];
    }
}
