<?php

namespace App\Filament\Resources\IncomingItemResource\Pages;

use Filament\Actions;
use App\Models\IncomingItem;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\IncomingItemResource;
use App\Models\Inventory;
use App\Models\StockHistory;
use Psy\Command\HistoryCommand;

class CreateIncomingItem extends CreateRecord
{
    protected static string $resource = IncomingItemResource::class;
    protected static ?string $title = "Buat Barang Masuk";

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['kode_transaksi'] = IncomingItem::generateKodeTransaksi();
        $data['total'] = $data['harga'] * $data['jumlah'];
        return $data;
    }

    public function afterCreate(): void
    {
        $previousStock = StockHistory::getSisaStok($this->record->inventory_id);

        StockHistory::create([
            'kode_transaksi' => $this->record->kode_transaksi,
            'tanggal_transaksi' => $this->record->tanggal_masuk,
            'inventory_id' => $this->record->inventory_id,
            'jenis' => 'masuk',
            'jumlah' => $this->record->jumlah,
            'sisa_stok' => $previousStock + $this->record->jumlah,
            'keterangan' => 'Tambah barang masuk',
        ]);
    }
}
