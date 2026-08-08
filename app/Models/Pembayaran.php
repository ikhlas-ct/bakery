<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayarans';

    protected $fillable = [
        'permintaan_bahan_baku_id',
        'produsen_id',
        'pemilik_id',
        'jumlah_bayar',
        'tanggal_bayar',
        'status_acc',
        'status_bayar',
        'metode_bayar',
    ];

    protected $casts = [
        'jumlah_bayar' => 'decimal:2',
        'tanggal_bayar' => 'date',
    ];

    public function permintaanBahanBaku(): BelongsTo
    {
        return $this->belongsTo(PermintaanBahanBaku::class);
    }

    public function produsen(): BelongsTo
    {
        return $this->belongsTo(Produsen::class);
    }

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(Pemilik::class);
    }
}
