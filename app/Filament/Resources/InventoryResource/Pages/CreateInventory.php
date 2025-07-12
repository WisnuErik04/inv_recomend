<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use Filament\Actions;
use App\Models\StockHistory;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\InventoryResource;

class CreateInventory extends CreateRecord
{
    protected static string $resource = InventoryResource::class;
    protected static ?string $title = "Buat Barang";

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function afterCreate(): void
    {
        $previousStock = StockHistory::getSisaStok($this->record->id);
        if ($previousStock != 0) {
            StockHistory::create([
                'kode_transaksi' => 'Stok Awal '.$this->record->nama_barang,
                'tanggal_transaksi' => now(),
                'inventory_id' => $this->record->id,
                'jenis' => 'masuk',
                'jumlah' => $this->record->stok_awal,
                'sisa_stok' => $previousStock + $this->record->stok_awal,
                'keterangan' => 'Stok awal',
            ]);
        }
    }
}
