<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PemakaianBahanBaku extends Model
{
    use HasFactory;

    protected $table = 'pemakaian_bahan_bakus';

    protected $fillable = [
        'admin_id',
        'nomor_transaksi',
        'tanggal_pakai',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_pakai' => 'date',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function pemakaianBahanBakuDetails(): HasMany
    {
        return $this->hasMany(PemakaianBahanBakuDetail::class);
    }
}
