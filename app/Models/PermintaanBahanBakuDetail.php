<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PermintaanBahanBakuDetail extends Model
{
    use HasFactory;

    protected $table = 'permintaan_bahan_baku_details';

    protected $fillable = [
        'permintaan_bahan_baku_id',
        'bahan_baku_id',
        'produsen_id',
        'harga_satuan',
        'jumlah_diminta',
        'jumlah_disetujui',
        'status_produsen',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'jumlah_diminta' => 'decimal:2',
        'jumlah_disetujui' => 'decimal:2',
    ];

    public function permintaanBahanBaku(): BelongsTo
    {
        return $this->belongsTo(PermintaanBahanBaku::class);
    }

    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class);
    }

    public function produsen(): BelongsTo
    {
        return $this->belongsTo(Produsen::class);
    }

    /**
     * Baris pengalihan untuk item ini (jika kekurangan dialihkan).
     */
    public function pengalihan(): HasOne
    {
        return $this->hasOne(PengalihanBahanBaku::class);
    }

    public function penerimaanBahanBakuDetails(): HasMany
    {
        return $this->hasMany(PenerimaanBahanBakuDetail::class);
    }
}
