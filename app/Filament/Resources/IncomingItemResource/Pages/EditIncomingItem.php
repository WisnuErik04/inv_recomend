<?php

namespace App\Filament\Resources\IncomingItemResource\Pages;

use Filament\Actions;
use App\Models\IncomingItem;
use App\Models\StockHistory;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\IncomingItemResource;

class EditIncomingItem extends EditRecord
{
    protected static string $resource = IncomingItemResource::class;
    protected static ?string $title = "Ubah Barang Masuk";
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
                    $previousStock = StockHistory::getSisaStok($this->record->inventory_id);
                    StockHistory::create([
                        'kode_transaksi' => $this->record->kode_transaksi,
                        'inventory_id' => $this->record->inventory_id,
                        'jenis' => 'keluar',
                        'jumlah' => $this->record->jumlah,
                        'keterangan' => 'Hapus barang masuk, stok dikurangi',
                        'tanggal_transaksi' => now(),
                        'sisa_stok' => $previousStock - $this->record->jumlah,

                    ]);
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['total'] = $data['harga'] * $data['jumlah'];
        return $data;
    }

    protected function beforeSave(): void
    {
        // Ambil data lama sebelum update
        $this->oldRecordData = IncomingItem::find($this->record->id);
    }

    public function afterSave()
    {
        $oldJumlah = $this->oldRecordData->jumlah;
        $oldInventoryId = $this->oldRecordData->inventory_id;

        $newJumlah = $this->record->jumlah;
        $newInventoryId = $this->record->inventory_id;

        $selisih = $newJumlah - $oldJumlah; // positif atau negatif
        $previousStock = StockHistory::getSisaStok($oldInventoryId);
        if ($oldInventoryId == $newInventoryId) {
            if ($selisih != 0) {
                StockHistory::create([
                    'kode_transaksi' => $this->record->kode_transaksi,
                    'tanggal_transaksi' => now(),
                    'inventory_id' => $this->record->inventory_id,
                    'jenis' => $selisih > 0 ? 'masuk' : 'keluar',
                    'jumlah' => abs($selisih),
                    'keterangan' => $selisih > 0 ? 'Ubah barang masuk - penambahan stok' : 'Ubah barang masuk - pengurangan stok',
                    'sisa_stok' => $previousStock + $selisih,
                ]);
            }
        } else {
            // Barang lama dikurangi stok
            StockHistory::create([
                'kode_transaksi' => $this->record->kode_transaksi,
                'inventory_id' => $oldInventoryId,
                'jenis' => 'keluar',
                'jumlah' => $oldJumlah,
                'keterangan' => 'Ubah barang masuk - pindah barang, stok dikurangi',
                'tanggal_transaksi' => now(),
                'sisa_stok' => $previousStock - $oldJumlah,
            ]);
            // Barang baru ditambah stok
            $previousStockNew = StockHistory::getSisaStok($newInventoryId);

            StockHistory::create([
                'kode_transaksi' => $this->record->kode_transaksi,
                'inventory_id' => $newInventoryId,
                'jenis' => 'masuk',
                'jumlah' => $newJumlah,
                'keterangan' => 'Ubah barang masuk - pindah barang, stok ditambah',
                'tanggal_transaksi' => now(),
                'sisa_stok' => $previousStockNew + $newJumlah,
            ]);
        }
    }

}
