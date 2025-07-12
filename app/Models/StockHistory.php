<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockHistory extends Model
{
    protected $fillable = [
        'kode_transaksi',
        'tanggal_transaksi',
        'inventory_id',
        'jenis',
        'jumlah',
        'sisa_stok',
        'keterangan',
    ];
    protected $with = ['inventory'];


    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    // Di model Inventory atau helper class
    public static function getSisaStok(int $inventoryId): int
    {
        $lastHistory = StockHistory::where('inventory_id', $inventoryId)->orderByDesc('created_at')->first();
        $previousStock = $lastHistory?->sisa_stok ?? 0;
        return $previousStock;
    }

}
