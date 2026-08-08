<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produsen extends Model
{
    use HasFactory;

    protected $table = 'produsens';

    protected $fillable = [
        'user_id',
        'nama_produsen',
        'alamat',
        'no_telp',
        'status_mitra',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Bahan baku yang dipasok produsen ini, lengkap dengan harga per satuan (pivot).
     */
    public function bahanBakus(): BelongsToMany
    {
        return $this->belongsToMany(BahanBaku::class, 'bahan_baku_produsens')
            ->using(BahanBakuProdusen::class)
            ->withPivot('harga')
            ->withTimestamps();
    }

    public function bahanBakuProdusens(): HasMany
    {
        return $this->hasMany(BahanBakuProdusen::class);
    }

    public function permintaanBahanBakuDetails(): HasMany
    {
        return $this->hasMany(PermintaanBahanBakuDetail::class);
    }

    /**
     * Pengalihan di mana produsen ini berperan sebagai produsen pengganti.
     */
    public function pengalihanSebagaiPengganti(): HasMany
    {
        return $this->hasMany(PengalihanBahanBaku::class, 'produsen_pengganti_id');
    }

    public function penerimaanBahanBakus(): HasMany
    {
        return $this->hasMany(PenerimaanBahanBaku::class);
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }
}
