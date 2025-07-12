<?php

namespace App\Filament\Resources\ExitItemResource\Pages;

use Filament\Actions;
use App\Models\ExitItem;
use App\Models\StockHistory;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ExitItemResource;

class CreateExitItem extends CreateRecord
{
    protected static string $resource = ExitItemResource::class;
    protected static ?string $title = "Buat Barang Keluar";

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['kode_transaksi'] = ExitItem::generateKodeTransaksi();
        $data['total'] = $data['harga'] * $data['jumlah'];
        return $data;
    }

    public function afterCreate(): void
    {
        $previousStock = StockHistory::getSisaStok($this->record->inventory_id);

        StockHistory::create([
            'kode_transaksi' => $this->record->kode_transaksi,
            'tanggal_transaksi' => $this->record->tanggal_keluar,
            'inventory_id' => $this->record->inventory_id,
            'jenis' => 'keluar',
            'jumlah' => $this->record->jumlah,
            'sisa_stok' => $previousStock - $this->record->jumlah,
            'keterangan' => 'Tambah barang keluar',
        ]);
    }
}
