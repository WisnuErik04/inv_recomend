<?php

namespace App\Filament\Resources;

use App\Filament\Exports\StockHistoryExporter;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\StockHistory;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Filters\Indicator;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StockHistoryResource\Pages;
use App\Filament\Resources\StockHistoryResource\RelationManagers;
use Filament\Actions\Exports\Exporter;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;

class StockHistoryResource extends Resource
{
    protected static ?string $model = StockHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Histori Stok';
    protected static ?string $breadcrumb = 'Histori Stok';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_transaksi')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('tanggal_transaksi')
                    ->required(),
                Forms\Components\Select::make('inventory_id')
                    ->relationship('inventory', 'id')
                    ->required(),
                Forms\Components\TextInput::make('jenis')
                    ->required(),
                Forms\Components\TextInput::make('jumlah')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('sisa_stok')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('keterangan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('inventory.nama_barang')
            ->groups([
                Group::make('inventory.nama_barang')
                    ->label('Barang')
                    ->collapsible(),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('kode_transaksi')
                    ->label('Transaksi')
                    ->searchable()
                    ->description(
                        fn(StockHistory $record): string =>
                        Carbon::parse($record->tanggal_transaksi)
                            ->translatedFormat('F d, Y')  
                    ),
                // Tables\Columns\TextColumn::make('tanggal_transaksi')
                //     ->date(),
                Tables\Columns\TextColumn::make('jenis')
                    ->badge() // Mengaktifkan tampilan badge
                    ->color(fn($state) => match ($state ?? '') {
                        'masuk' => 'success',
                        'keluar'   => 'danger',
                        default     => 'secondary',
                    })
                    ->icon(fn($state) => match ($state) {
                        'masuk' => 'heroicon-o-check-circle',
                        'keluar'   => 'heroicon-o-x-circle',
                        default     => 'heroicon-o-question-mark-circle',
                    }),
                Tables\Columns\TextColumn::make('jumlah')
                    ->numeric(),

                Tables\Columns\TextColumn::make('sisa_stok')
                    ->numeric(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label("Tanggal Input")
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->searchable()
                    ->lineClamp(2)
                    ->wrap(),
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
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('tgl_input_dari'),
                        DatePicker::make('tgl_input_hingga'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tgl_input_dari'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['tgl_input_hingga'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['tgl_input_dari'] ?? null) {
                            $indicators[] = Indicator::make('Tgl pengajuan dari ' . Carbon::parse($data['tgl_input_dari'])->toFormattedDateString())
                                ->removeField('tgl_input_dari');
                        }

                        if ($data['tgl_input_hingga'] ?? null) {
                            $indicators[] = Indicator::make('Tgl pengajuan hingga ' . Carbon::parse($data['tgl_input_hingga'])->toFormattedDateString())
                                ->removeField('tgl_input_hingga');
                        }

                        return $indicators;
                    })

            ])
            ->headerActions([
                ExportAction::make()->Exporter(StockHistoryExporter::class),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->Exporter(StockHistoryExporter::class),
                //     Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListStockHistories::route('/'),
            // 'create' => Pages\CreateStockHistory::route('/create'),
            // 'edit' => Pages\EditStockHistory::route('/{record}/edit'),
        ];
    }
}
