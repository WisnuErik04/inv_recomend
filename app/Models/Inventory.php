<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    protected $fillable = [
        'nama_barang',
        'stok_awal',
        'harga_beli',
        'harga_jual',
        'unit_weight_id',
    ];

    protected $with = ['unitWeight'];
    public function unitWeight(): BelongsTo
    {
        return $this->belongsTo(UnitWeight::class);
    }

    public function incomingItem(): HasMany
    {
        return $this->hasMany(IncomingItem::class, 'inventory_id');
    }
    public function exitItem(): HasMany
    {
        return $this->hasMany(ExitItem::class, 'inventory_id');
    }
    public function stockHistories(): HasMany
    {
        return $this->hasMany(StockHistory::class, 'inventory_id');
    }
}
