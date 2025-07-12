<?php

namespace App\Filament\Resources\IncomingItemResource\Pages;

use App\Filament\Resources\IncomingItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIncomingItems extends ListRecords
{
    protected static string $resource = IncomingItemResource::class;
    protected static ?string $title = "Barang Masuk";
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
