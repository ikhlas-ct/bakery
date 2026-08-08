<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BahanBakuProdusen extends Pivot
{
    /**
     * Tabel ini punya kolom id sendiri (bukan composite key dari 2 FK),
     * jadi incrementing harus diaktifkan seperti model Eloquent biasa.
     */
    public $incrementing = true;

    protected $table = 'bahan_baku_produsens';

    protected $fillable = [
        'bahan_baku_id',
        'produsen_id',
        'harga',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
    ];

    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class);
    }

    public function produsen(): BelongsTo
    {
        return $this->belongsTo(Produsen::class);
    }
}
