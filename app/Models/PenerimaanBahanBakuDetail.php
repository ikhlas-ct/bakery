<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenerimaanBahanBakuDetail extends Model
{
    use HasFactory;

    protected $table = 'penerimaan_bahan_baku_details';

    protected $fillable = [
        'penerimaan_bahan_baku_id',
        'permintaan_bahan_baku_detail_id',
        'bahan_baku_id',
        'jumlah_diterima',
        'jumlah_tersisa',
        'tanggal_kadaluarsa',
    ];

    protected $casts = [
        'jumlah_diterima' => 'decimal:2',
        'jumlah_tersisa' => 'decimal:2',
        'tanggal_kadaluarsa' => 'date',
    ];

    public function penerimaanBahanBaku(): BelongsTo
    {
        return $this->belongsTo(PenerimaanBahanBaku::class);
    }

    public function permintaanBahanBakuDetail(): BelongsTo
    {
        return $this->belongsTo(PermintaanBahanBakuDetail::class);
    }

    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class);
    }

    /**
     * Baris pemakaian yang mengonsumsi batch ini (penelusuran FEFO).
     */
    public function pemakaianBahanBakuDetails(): HasMany
    {
        return $this->hasMany(PemakaianBahanBakuDetail::class);
    }
}
