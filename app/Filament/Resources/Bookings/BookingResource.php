<?php

namespace App\Filament\Resources\Bookings;

use App\Filament\Resources\Bookings\Pages\CreateBooking;
use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Bookings\Schemas\BookingForm;
use App\Filament\Resources\Bookings\Tables\BookingsTable;
use App\Models\Booking;
use App\Models\Inventory;
use App\Models\Cluster;
use BackedEnum;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
// use Filament\Support\Icons\Heroicon;
// use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
// use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
// use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\SelectColumn;
use Filament\Actions\BulkAction;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    // protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $navigationLabel = 'Booking';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $recordTitleAttribute = 'Booking';

    public static function form(Schema $schema): Schema
    {
        // return BookingForm::configure($schema);
        return $schema
            ->components([
                Section::make('Form Peminjaman')
                    ->schema([
                        // 1. Pilih User
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Mahasiswa/Peminjam')
                            ->searchable()
                            ->preload()
                            ->required(),

                        // 2. Pilih Lab (Hanya untuk filter, tidak disimpan ke DB Booking)
                        Select::make('cluster_id')
                            ->label('Pilih Lokasi Lab')
                            ->options(Cluster::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->reactive() // Wajib reactive biar dropdown bawahnya berubah
                            ->afterStateUpdated(fn ($set) => $set('inventory_id', null)) // Reset PC jika ganti Lab
                            ->dehydrated(false), // PENTING: Jangan simpan ini ke tabel bookings

                        // 3. Pilih Meja/PC (Dropdown Standar)
                        Select::make('inventory_id')
                            ->label('Pilih Station / Meja')
                            ->options(function (Get $get) {
                                $clusterId = $get('cluster_id');
                                
                                // Jika Lab belum dipilih, kosongkan opsi
                                if (!$clusterId) {
                                    return [];
                                }

                                // Ambil daftar Meja di Lab tersebut yang statusnya Available
                                return Inventory::where('cluster_id', $clusterId)
                                    ->where('status', 'available') // Filter hanya yang bisa dipinjam
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->rules([
                                // VALIDASI ANTI BENTROK
                                fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $start = $get('start_time');
                                    $end = $get('end_time');
                                    $inventoryId = $value;

                                    if (!$start || !$end || !$inventoryId) return;

                                    // Cek apakah ada booking lain di jam & meja yang sama
                                    $exists = Booking::query()
                                        ->where('inventory_id', $inventoryId)
                                        ->whereNotIn('status', ['cancelled', 'rejected'])
                                        ->where(function ($query) use ($start, $end) {
                                            $query->whereBetween('start_time', [$start, $end])
                                                ->orWhereBetween('end_time', [$start, $end])
                                                ->orWhere(function ($sub) use ($start, $end) {
                                                    $sub->where('start_time', '<=', $start)
                                                        ->where('end_time', '>=', $end);
                                                });
                                        })
                                        ->when($get('id'), fn ($q) => $q->where('id', '!=', $get('id'))) 
                                        ->exists();

                                    if ($exists) {
                                        $fail('❌ Meja ini sudah dibooking orang lain di jam tersebut!');
                                    }
                                },
                            ]),

                        // 4. Waktu Mulai
                        DateTimePicker::make('start_time')
                            ->label('Waktu Mulai')
                            ->required()
                            ->seconds(false) // Biasanya booking per menit, detik tidak perlu
                            ->reactive(),

                        // 5. Waktu Selesai
                        DateTimePicker::make('end_time')
                            ->label('Waktu Selesai')
                            ->required()
                            ->seconds(false)
                            ->after('start_time') // Validasi harus setelah waktu mulai
                            ->reactive(),
                    ])->columns(2),

                Section::make('Persetujuan')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'pending' => '⏳ Menunggu',
                                'booked' => '✅ Disetujui',
                                'rejected' => '❌ Ditolak',
                                'finished' => '🏁 Selesai',
                                'cancelled' => '🚫 Dibatalkan',
                            ])
                            ->default('pending')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        // return BookingsTable::configure($table);
        return $table
            ->columns([
                // 1. Info Peminjam
                TextColumn::make('user.name')
                    ->label('Peminjam')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Booking $record) => $record->user->npm_nip ?? '-'), // Tampilkan NPM di bawah nama

                // 2. Info Station / Meja
                TextColumn::make('inventory.name')
                    ->label('Station')
                    ->searchable()
                    ->sortable()
                    ->badge() // Biar terlihat seperti "Tag"
                    ->color('gray')
                    ->description(fn (Booking $record) => $record->inventory->cluster->name ?? '-'), // Tampilkan Lokasi Lab

                // 3. Waktu (Digabung biar hemat tempat)
                TextColumn::make('start_time')
                    ->label('Jadwal')
                    ->dateTime('d M, H:i')
                    ->description(fn (Booking $record) => 's/d ' . $record->end_time->format('H:i'))
                    ->sortable(),

                // 4. STATUS (FITUR UTAMA: DROPDOWN LANGSUNG)
                SelectColumn::make('status')
                    ->options([
                        'pending' => '⏳ Menunggu',
                        'booked' => '✅ Disetujui',
                        'rejected' => '❌ Ditolak',
                        'finished' => '🏁 Selesai',
                        'cancelled' => '🚫 Dibatalkan',
                    ])
                    ->selectablePlaceholder(false) // Admin wajib pilih salah satu
                    ->searchable()
                    ->sortable()
                    // Matikan fitur edit jika status sudah Selesai/Dibatalkan (Opsional)
                    ->disabled(fn ($record) => in_array($record->status, ['finished', 'cancelled'])),
            ])
            ->defaultSort('created_at', 'desc') // Booking terbaru paling atas
            ->filters([
                // Filter Status biar Admin gampang cari yang 'Pending'
                SelectFilter::make('status')
                    ->options([
                        'pending' => '⏳ Menunggu',
                        'approved' => '✅ Disetujui',
                        'rejected' => '❌ Ditolak',
                        'finished' => '🏁 Selesai',
                    ]),
                
                // Filter Lab
                SelectFilter::make('cluster')
                    ->relationship('inventory.cluster', 'name')
                    ->label('Lokasi Lab'),
            ])
            ->actions([
                // Tombol Edit tetap ada untuk ubah jam/tanggal
                EditAction::make(), 
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    // Fitur Bulk Approve (Sekaligus banyak)
                    BulkAction::make('approve_selected')
                        ->label('Setujui Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->update(['status' => 'approved'])),
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
            'index' => ListBookings::route('/'),
            'create' => CreateBooking::route('/create'),
            'edit' => EditBooking::route('/{record}/edit'),
        ];
    }
}
