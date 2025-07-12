<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'nama',
        'alamat',
        'telepon',
        'keterangan',
    ];

    public function exitItem(): HasMany
    {
        return $this->hasMany(ExitItem::class, 'customer_id');
    }
}
