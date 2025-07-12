<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\RelationManagers\ExitItemRelationManager;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Inventory;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use App\Models\StockHistory;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\InventoryResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\InventoryResource\RelationManagers;
use App\Filament\Resources\InventoryResource\RelationManagers\ExitItemRelationManager as RelationManagersExitItemRelationManager;
use App\Filament\Resources\InventoryResource\RelationManagers\IncomingItemRelationManager;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Barang';
    protected static ?string $breadcrumb = 'Barang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns([
                        'sm' => 2,
                        'xl' => 6,
                        '2xl' => 8,
                    ])
                    ->schema([
                        TextInput::make('nama_barang')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan([
                                'sm' => 2,
                                'xl' => 2,
                                '2xl' => 4,
                            ]),
                        TextInput::make('harga_beli')
                            ->required()
                            ->prefix('Rp')
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->minValue(0)
                            ->columnSpan([
                                'sm' => 2,
                                'xl' => 2,
                                '2xl' => 4,
                            ]),
                        TextInput::make('harga_jual')
                            ->required()
                            ->prefix('Rp')
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->minValue(0)
                            ->columnSpan([
                                'sm' => 2,
                                'xl' => 2,
                                '2xl' => 4,
                            ]),
                        TextInput::make('stok_awal')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->columnSpan([
                                'sm' => 2,
                                'xl' => 1,
                                '2xl' => 4,
                            ]),
                        Select::make('unit_weight_id')
                            ->relationship('unitWeight', 'keterangan')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->columnSpan([
                                'sm' => 2,
                                'xl' => 1,
                                '2xl' => 4,
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_barang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stok_awal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unitWeight.nama')
                    ->label('Satuan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_beli')
                    ->money('IDR')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_jual')
                    ->money('IDR')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                // ✅ HOOK SEBELUM DELETE
                                StockHistory::create([
                                    'kode_transaksi' => 'Stok Awal ' . $record->nama_barang,
                                    'inventory_id' => $record->id,
                                    'jenis' => 'keluar',
                                    'jumlah' => 0,
                                    'keterangan' => 'Hapus barang, stok dikosongkan',
                                    'tanggal_transaksi' => now(),
                                    'sisa_stok' => 0,

                                ]);

                                // DELETE MANUAL
                                $record->delete();
                            }

                            // ✅ HOOK SETELAH DELETE (misalnya notifikasi)
                            Notification::make()
                                ->title('Berhasil hapus data!')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            IncomingItemRelationManager::class,
            ExitItemRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
