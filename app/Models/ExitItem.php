<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExitItem extends Model
{
    protected $fillable = [
        'kode_transaksi',
        'inventory_id',
        'customer_id',
        'tanggal_keluar',
        'jumlah',
        'harga',
        'total',
        'keterangan',
    ];
    protected $with = ['inventory', 'customer'];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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
        return "OUT-{$today}-{$nextNumber}";
    }
}
