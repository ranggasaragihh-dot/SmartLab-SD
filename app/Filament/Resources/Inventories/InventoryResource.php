<?php

namespace App\Filament\Resources\Inventories;

use App\Filament\Resources\Inventories\Pages\CreateInventory;
use App\Filament\Resources\Inventories\Pages\EditInventory;
use App\Filament\Resources\Inventories\Pages\ListInventories;
use App\Filament\Resources\Inventories\Schemas\InventoryForm;
use App\Filament\Resources\Inventories\Tables\InventoriesTable;
use App\Models\Inventory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $navigationLabel = 'Station / Meja (PC)';
    // protected static string|UnitEnum|null $navigationGroup = 'Booking System';

    protected static ?string $recordTitleAttribute = 'Inventaris';

    public static function form(Schema $schema): Schema
    {
        // return InventoryForm::configure($schema);
        return $schema
            ->components([
                Section::make('Identitas Station')
                    ->schema([
                        // 1. Relasi ke Cluster (PENTING: Gunakan 'cluster_id' di Select)
                        Select::make('cluster_id')
                            ->relationship('cluster', 'name') // Menampilkan nama cluster
                            ->label('Lokasi Lab')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('name')
                            ->label('Kode Meja/PC')
                            ->placeholder('Contoh: A-01')
                            ->required(),

                        TextInput::make('mac_address')
                            ->label('MAC Address (Opsional)')
                            ->placeholder('00:1B:44:11:3A:B7'),

                        TextInput::make('qr_token')
                            ->label('QR Token')
                            ->disabled() // Biasanya digenerate otomatis, jadi disable dulu
                            ->dehydrated(false) // Jangan kirim ke DB kalau kosong
                            ->placeholder('Auto-generated'),
                    ])->columns(2),

                Section::make('Status & Kontrol')
                    ->schema([
                        // Pilihan Status (Enum)
                        Select::make('status')
                            ->options([
                                'available' => '🟢 Available',
                                'maintenance' => '🟠 Maintenance',
                                'broken' => '🔴 Broken',
                            ])
                            ->default('available')
                            ->required(),

                        // Pilihan Override Mode (Enum)
                        Select::make('admin_override_mode')
                            ->label('Admin Override')
                            ->options([
                                'none' => '🛡️ Normal (By System)',
                                'force_open' => '🔓 Force Open (Selalu Buka)',
                                'force_close' => '🔒 Force Close (Selalu Tutup)',
                            ])
                            ->default('none')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        // return InventoriesTable::configure($table);
        return $table
            ->columns([
                // INI YANG SERING ERROR: Relasi Cluster
                // Pastikan pakai TITIK (.) -> 'cluster.name'
                TextColumn::make('cluster.name')
                    ->label('Lokasi Lab')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('name')
                    ->label('Kode Meja')
                    ->searchable()
                    ->sortable(),

                // Menampilkan Status dengan Warna
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'maintenance' => 'warning',
                        'broken' => 'danger',
                        default => 'gray',
                    }),
                
                // Menampilkan Override Mode
                TextColumn::make('admin_override_mode')
                    ->label('Mode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'none' => 'gray',
                        'force_open' => 'success',
                        'force_close' => 'danger',
                    }),
            ])
            ->filters([
                // Filter berdasarkan Lab
                SelectFilter::make('cluster_id')
                    ->relationship('cluster', 'name')
                    ->label('Filter Lab'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),

                Action::make('qr_code')
                ->label('QR')
                ->icon('heroicon-o-qr-code')
                ->color('success')
                
                // Closure ini berjalan saat tombol ditekan:
                ->modalContent(function ($record) {
                    
                    // 1. BUAT VARIABLE URL DISINI
                    // Pastikan route 'scan.qr' sudah ada di web.php
                    $url = route('scan.qr', ['qrToken' => $record->qr_token]); 
                    
                    // 2. KIRIM KE VIEW
                    return view('filament.components.qr-modal', [
                        'token' => $url, // QR Code akan berisi Link lengkap
                        'name' => $record->name
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
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
            'index' => ListInventories::route('/'),
            'create' => CreateInventory::route('/create'),
            'edit' => EditInventory::route('/{record}/edit'),
        ];
    }
}
