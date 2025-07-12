<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingItem extends Model
{
    protected $fillable = [
        'kode_transaksi',
        'inventory_id',
        'tanggal_masuk',
        'jumlah',
        'harga',
        'total',
        'keterangan',
        'tanggal_pesan',
    ];
    protected $with = ['inventory'];


    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public static function generateKodeTransaksi(): string
    {
        $today = now()->format('Ymd');
        $lastItem = self::whereDate('created_at', now()->toDateString())
            ->orderByDesc('id')
            ->first();

        $lastNumber = 0;
        if ($lastItem && preg_match('/(\d+)$/', $lastItem->kode_transaksi, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        return "TRX-{$today}-{$nextNumber}";
    }
}
