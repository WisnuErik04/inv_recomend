<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\ExitItem;
use Filament\Forms\Form;
use App\Models\Inventory;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use App\Models\StockHistory;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\Indicator;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ExitItemResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ExitItemResource\RelationManagers;

class ExitItemResource extends Resource
{
    protected static ?string $model = ExitItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationLabel = 'Barang Keluar';
    protected static ?string $breadcrumb = 'Barang Keluar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns([
                        'sm' => 2,
                        'xl' => 9,
                        '2xl' => 8,
                    ])
                    ->schema([
                        TextInput::make('kode_transaksi')
                            ->label('kode transaksi barang keluar')
                            ->required()
                            ->default(fn() => ExitItem::generateKodeTransaksi())
                            ->disabled()
                            ->dehydrated(false)
                            ->maxLength(255)
                            ->columnSpan(['sm' => 2, 'xl' => 3, '2xl' => 4]),

                        DatePicker::make('tanggal_keluar')
                            ->required()
                            ->default(now())
                            ->columnSpan(['sm' => 2, 'xl' => 3, '2xl' => 4]),

                        Select::make('customer_id')
                            ->relationship('customer', 'nama')
                            ->label('Pelanggan')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->columnSpan(['sm' => 2, 'xl' => 3, '2xl' => 4]),


                        Select::make('inventory_id')
                            ->relationship('inventory', 'nama_barang')
                            ->label('Barang')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $harga = Inventory::find($state)?->harga_jual ?? 0;
                                $jumlah = $get('jumlah') ?? 1;

                                $harga = preg_replace('/[^\d.]/', '', $harga);
                                $jumlah = preg_replace('/[^\d.]/', '', $jumlah);

                                $set('jumlah', $get('jumlah') ?? 1);
                                $set('harga', $harga);
                                $set('total', floatval($jumlah) * floatval($harga));
                            })
                            ->columnSpan(['sm' => 2, 'xl' => 3, '2xl' => 4]),

                        TextInput::make('jumlah')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $harga = $get('harga') ?? 0;
                                $state = preg_replace('/[^\d.]/', '', $state);

                                $harga = preg_replace('/[^\d.]/', '', $harga);
                                $set('total', $state * floatval($harga));
                            })
                            ->columnSpan(['sm' => 2, 'xl' => 2, '2xl' => 4]),

                        TextInput::make('harga')
                            ->required()
                            ->label('Harga Satuan')
                            ->numeric()
                            ->prefix('Rp')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->minValue(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $jumlah = $get('jumlah') ?? 1;
                                $jumlah = preg_replace('/[^\d.]/', '', $jumlah);
                                $state = preg_replace('/[^\d.]/', '', $state);
                                $set('total', floatval($jumlah) * floatval($state));
                            })
                            ->mask(RawJs::make('$money($input)'))
                            ->columnSpan(['sm' => 2, 'xl' => 2, '2xl' => 4]),

                        TextInput::make('total')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(false)
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->columnSpan(['sm' => 2, 'xl' => 2, '2xl' => 4]),

                        Textarea::make('keterangan')
                            ->columnSpanFull()
                            ->columnSpan(['sm' => 2, 'xl' => 4, '2xl' => 4]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_transaksi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('inventory.nama_barang')
                    ->label('Barang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.nama')
                    ->label('Pelanggan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_keluar')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga')
                    ->money('IDR')
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total')
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
                SelectFilter::make('customer_id')
                    ->label('Pelanggan')
                    ->relationship('customer', 'nama')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('inventory_id')
                    ->label('Barang')
                    ->relationship('inventory', 'nama_barang')
                    ->multiple()
                    ->preload(),
                Filter::make('tanggal_keluar')
                    ->form([
                        DatePicker::make('tgl_keluar_dari'),
                        DatePicker::make('tgl_keluar_hingga'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tgl_keluar_dari'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_keluar', '>=', $date),
                            )
                            ->when(
                                $data['tgl_keluar_hingga'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_keluar', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['tgl_keluar_dari'] ?? null) {
                            $indicators[] = Indicator::make('Tgl pengajuan dari ' . Carbon::parse($data['tgl_keluar_dari'])->toFormattedDateString())
                                ->removeField('tgl_keluar_dari');
                        }

                        if ($data['tgl_keluar_hingga'] ?? null) {
                            $indicators[] = Indicator::make('Tgl pengajuan hingga ' . Carbon::parse($data['tgl_keluar_hingga'])->toFormattedDateString())
                                ->removeField('tgl_keluar_hingga');
                        }

                        return $indicators;
                    })

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                // ✅ HOOK SEBELUM DELETE
                                $previousStock = StockHistory::getSisaStok($record->inventory_id);
                                StockHistory::create([
                                    'kode_transaksi' => $record->kode_transaksi,
                                    'inventory_id' => $record->inventory_id,
                                    'jenis' => 'keluar',
                                    'jumlah' => $record->jumlah,
                                    'keterangan' => 'Hapus barang keluar, stok dikurangi',
                                    'tanggal_transaksi' => now(),
                                    'sisa_stok' => $previousStock - $record->jumlah,

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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExitItems::route('/'),
            'create' => Pages\CreateExitItem::route('/create'),
            'edit' => Pages\EditExitItem::route('/{record}/edit'),
        ];
    }
}
