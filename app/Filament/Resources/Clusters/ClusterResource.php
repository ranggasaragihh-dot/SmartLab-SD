<?php

namespace App\Filament\Resources\Clusters;

use App\Filament\Resources\Clusters\Pages\CreateCluster;
use App\Filament\Resources\Clusters\Pages\EditCluster;
use App\Filament\Resources\Clusters\Pages\ListClusters;
use App\Filament\Resources\Clusters\Schemas\ClusterForm;
use App\Filament\Resources\Clusters\Tables\ClustersTable;
use App\Models\Cluster;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;

class ClusterResource extends Resource
{
    protected static ?string $model = Cluster::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Manajemen Lab (Cluster)';

    protected static ?string $recordTitleAttribute = 'Cluster';

    public static function form(Schema $schema): Schema
    {
        // return ClusterForm::configure($schema);
        return $schema
            ->components([
                Section::make('Informasi Lab')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lab')
                            ->placeholder('Contoh: Lab Struktur Data')
                            ->required(),
                        
                        Select::make('type')
                            ->options([
                                'public' => 'Public (Mahasiswa & Dosen)',
                                'private' => 'Private (Khusus Dosen)',
                            ])
                            ->required()
                            ->default('public'),
                        
                        // TextInput::make('location')
                        //     ->label('Lokasi Fisik')
                        //     ->placeholder('Contoh: Gedung 3, Lantai 2'),
                            
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                            
                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Jika dimatikan, lab ini tidak bisa dibooking.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        // return ClustersTable::configure($table);
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Lab')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('location')
                    ->label('Lokasi'),
                    
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
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
            'index' => ListClusters::route('/'),
            'create' => CreateCluster::route('/create'),
            'edit' => EditCluster::route('/{record}/edit'),
        ];
    }
}
