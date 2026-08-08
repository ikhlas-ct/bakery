<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemakaianBahanBakuDetail extends Model
{
    use HasFactory;

    protected $table = 'pemakaian_bahan_baku_details';

    protected $fillable = [
        'pemakaian_bahan_baku_id',
        'bahan_baku_id',
        'penerimaan_bahan_baku_detail_id',
        'jumlah_dipakai',
    ];

    protected $casts = [
        'jumlah_dipakai' => 'decimal:2',
    ];

    public function pemakaianBahanBaku(): BelongsTo
    {
        return $this->belongsTo(PemakaianBahanBaku::class);
    }

    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class);
    }

    public function penerimaanBahanBakuDetail(): BelongsTo
    {
        return $this->belongsTo(PenerimaanBahanBakuDetail::class);
    }
}
