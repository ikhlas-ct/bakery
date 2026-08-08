<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenerimaanBahanBaku extends Model
{
    use HasFactory;

    protected $table = 'penerimaan_bahan_bakus';

    protected $fillable = [
        'admin_id',
        'produsen_id',
        'nomor_transaksi',
        'tanggal_terima',
        'catatan',
    ];

    protected $casts = [
        'tanggal_terima' => 'date',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function produsen(): BelongsTo
    {
        return $this->belongsTo(Produsen::class);
    }

    public function penerimaanBahanBakuDetails(): HasMany
    {
        return $this->hasMany(PenerimaanBahanBakuDetail::class);
    }
}
