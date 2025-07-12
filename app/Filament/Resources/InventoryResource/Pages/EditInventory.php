<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use Filament\Actions;
use App\Models\StockHistory;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\InventoryResource;
use App\Models\Inventory;

class EditInventory extends EditRecord
{
    protected static string $resource = InventoryResource::class;
    protected static ?string $title = "Ubah Barang";
    protected $oldRecordData;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    StockHistory::create([
                        'kode_transaksi' => 'Stok Awal ' . $this->record->nama_barang,
                        'inventory_id' => $this->record->id,
                        'jenis' => 'keluar',
                        'jumlah' => 0,
                        'keterangan' => 'Hapus barang, stok dikosongkan',
                        'tanggal_transaksi' => now(),
                        'sisa_stok' => 0,

                    ]);
                }),
        ];
    }

    protected function beforeSave(): void
    {
        // Ambil data lama sebelum update
        $this->oldRecordData = Inventory::find($this->record->id);
    }

    public function afterSave()
    {
        $oldJumlah = $this->oldRecordData->stok_awal;
        $oldInventoryId = $this->oldRecordData->id;

        $newJumlah = $this->record->stok_awal;

        $selisih = $newJumlah - $oldJumlah; // positif atau negatif
        $previousStock = StockHistory::getSisaStok($oldInventoryId);
        if ($selisih != 0) {
            StockHistory::create([
                'kode_transaksi' => 'Stok Awal ' . $this->record->nama_barang,
                'tanggal_transaksi' => now(),
                'inventory_id' => $this->record->id,
                'jenis' => $selisih > 0 ? 'masuk' : 'keluar',
                'jumlah' => abs($selisih),
                'keterangan' => $selisih > 0 ? 'Ubah stok awal - penambahan stok' : 'Ubah stok awal - pengurangan stok',
                'sisa_stok' => $previousStock + $selisih,
            ]);
        }
    }
}
