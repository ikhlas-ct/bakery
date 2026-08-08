<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengalihanBahanBaku extends Model
{
    use HasFactory;

    protected $table = 'pengalihan_bahan_bakus';

    protected $fillable = [
        'permintaan_bahan_baku_detail_id',
        'bahan_baku_asal_id',
        'bahan_baku_pengganti_id',
        'produsen_pengganti_id',
        'jumlah_dialihkan',
        'status_produsen_pengganti',
        'jumlah_disetujui_pengganti',
        'alasan_pengalihan',
        'tanggal',
    ];

    protected $casts = [
        'jumlah_dialihkan' => 'decimal:2',
        'jumlah_disetujui_pengganti' => 'decimal:2',
        'tanggal' => 'date',
    ];

    public function permintaanBahanBakuDetail(): BelongsTo
    {
        return $this->belongsTo(PermintaanBahanBakuDetail::class);
    }

    public function bahanBakuAsal(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_asal_id');
    }

    public function bahanBakuPengganti(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_pengganti_id');
    }

    public function produsenPengganti(): BelongsTo
    {
        return $this->belongsTo(Produsen::class, 'produsen_pengganti_id');
    }
}
