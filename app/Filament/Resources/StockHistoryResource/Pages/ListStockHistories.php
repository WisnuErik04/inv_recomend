<?php

namespace App\Filament\Resources\StockHistoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords\Tab;
use App\Filament\Resources\StockHistoryResource;

class ListStockHistories extends ListRecords
{
    protected static string $resource = StockHistoryResource::class;
    protected static ?string $title = "Histori Stok";

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
    public function getTabs(): array
    {
        return [
            'Semua' => Tab::make(),
            'Masuk' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jenis', 'masuk')),
            'Keluar' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jenis', 'keluar')),
        ];
    }
}
