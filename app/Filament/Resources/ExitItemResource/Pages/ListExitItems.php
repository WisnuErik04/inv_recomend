<?php

namespace App\Filament\Resources\ExitItemResource\Pages;

use App\Filament\Resources\ExitItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExitItems extends ListRecords
{
    protected static string $resource = ExitItemResource::class;
    protected static ?string $title = "Barang Keluar";

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
