<?php

namespace App\Filament\Resources\InventoryItems;

use App\Filament\Resources\InventoryItems\Pages\CreateInventoryItem;
use App\Filament\Resources\InventoryItems\Pages\EditInventoryItem;
use App\Filament\Resources\InventoryItems\Pages\ListInventoryItems;
use App\Filament\Resources\InventoryItems\Schemas\InventoryItemForm;
use App\Filament\Resources\InventoryItems\Tables\InventoryItemsTable;
use App\Models\InventoryItem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Tables\Columns\TextColumn;

class InventoryItemResource extends Resource
{
    protected static ?string $model = InventoryItem::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Item';

    public static function form(Schema $schema): Schema
    {
        // return InventoryItemForm::configure($schema);
        return $schema
            ->components([
                // BAGIAN 1: INFO DASAR
                ComponentsSection::make('Informasi Dasar')
                    ->schema([
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->label('Kategori')
                            ->required(),
                            
                        Select::make('brand_id')
                            ->relationship('brand', 'name')
                            ->label('Brand/Merk')
                            ->required(),
                            
                        Select::make('resource_id')
                            ->relationship('resource', 'code')
                            ->label('Lokasi Pemasangan')
                            ->placeholder('Di Gudang (Belum Terpasang)')
                            ->searchable()
                            ->nullable(),
                    ])->columns(3),

                // BAGIAN 2: DETAIL FISIK
                ComponentsSection::make('Detail Fisik')
                    ->schema([
                        TextInput::make('serial_number')
                            ->label('Nomor Seri (SN)')
                            ->required()
                            ->unique(ignoreRecord: true),
                            
                        Select::make('condition')
                            ->label('Kondisi')
                            ->options([
                                'good' => 'Baik',
                                'maintenance' => 'Perbaikan',
                                'broken' => 'Rusak',
                            ])->required(),
                        
                        // JSON SPEK
                        KeyValue::make('specifications')
                            ->label('Spesifikasi Teknis')
                            ->columnSpan('full'), 
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        // return InventoryItemsTable::configure($table);
        return $table
            ->columns([
                TextColumn::make('serial_number'),
                TextColumn::make('category.name'),
                TextColumn::make('inventory.name'),
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
            'index' => ListInventoryItems::route('/'),
            'create' => CreateInventoryItem::route('/create'),
            'edit' => EditInventoryItem::route('/{record}/edit'),
        ];
    }
}
