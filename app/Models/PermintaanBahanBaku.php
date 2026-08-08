<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermintaanBahanBaku extends Model
{
    use HasFactory;

    protected $table = 'permintaan_bahan_bakus';

    protected $fillable = [
        'admin_id',
        'nomor_transaksi',
        'tanggal_permintaan',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_permintaan' => 'date',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function permintaanBahanBakuDetails(): HasMany
    {
        return $this->hasMany(PermintaanBahanBakuDetail::class);
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }
}
