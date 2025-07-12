<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\Inventory;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use App\Models\IncomingItem;
use App\Models\StockHistory;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Filament\Actions\CreateAction;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\IncomingItemResource\Pages;
use App\Filament\Resources\IncomingItemResource\RelationManagers;

class IncomingItemResource extends Resource
{
    protected static ?string $model = IncomingItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationLabel = 'Barang Masuk';
    protected static ?string $breadcrumb = 'Barang Masuk';

    // CreateAction::make()
    // ->mutateFormDataUsing(function (array $data): array {
    //     $data['user_id'] = auth()->id();

    //     return $data;
    // });

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
                            ->required()
                            ->default(fn() => IncomingItem::generateKodeTransaksi())
                            ->disabled()
                            ->dehydrated(false)
                            ->maxLength(255)
                            ->columnSpan(['sm' => 2, 'xl' => 3, '2xl' => 4]),

                        DatePicker::make('tanggal_pesan')
                            ->required()
                            ->default(now())
                            ->columnSpan(['sm' => 2, 'xl' => 3, '2xl' => 4]),
                        
                        DatePicker::make('tanggal_masuk')
                            ->required()
                            ->default(now())
                            ->columnSpan(['sm' => 2, 'xl' => 3, '2xl' => 4]),

                        Select::make('inventory_id')
                            ->relationship('inventory', 'nama_barang')
                            ->label('Barang')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $harga = Inventory::find($state)?->harga_beli ?? 0;
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
                            ->columnSpan(['sm' => 2, 'xl' => 1, '2xl' => 4]),

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
                Tables\Columns\TextColumn::make('tanggal_pesan')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_masuk')
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
                SelectFilter::make('inventory_id')
                    ->label('Barang')
                    ->relationship('inventory', 'nama_barang')
                    ->multiple()
                    ->preload(),
                Filter::make('tanggal_masuk')
                    ->form([
                        DatePicker::make('tgl_masuk_dari'),
                        DatePicker::make('tgl_masuk_hingga'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tgl_masuk_dari'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_masuk', '>=', $date),
                            )
                            ->when(
                                $data['tgl_masuk_hingga'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_masuk', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['tgl_masuk_dari'] ?? null) {
                            $indicators[] = Indicator::make('Tgl pengajuan dari ' . Carbon::parse($data['tgl_masuk_dari'])->toFormattedDateString())
                                ->removeField('tgl_masuk_dari');
                        }

                        if ($data['tgl_masuk_hingga'] ?? null) {
                            $indicators[] = Indicator::make('Tgl pengajuan hingga ' . Carbon::parse($data['tgl_masuk_hingga'])->toFormattedDateString())
                                ->removeField('tgl_masuk_hingga');
                        }

                        return $indicators;
                    })

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
                                    'keterangan' => 'Hapus barang masuk, stok dikurangi',
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
            ]);;
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
            'index' => Pages\ListIncomingItems::route('/'),
            'create' => Pages\CreateIncomingItem::route('/create'),
            'edit' => Pages\EditIncomingItem::route('/{record}/edit'),
        ];
    }
}
