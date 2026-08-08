<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BahanBaku extends Model
{
    use HasFactory;

    protected $table = 'bahan_bakus';

    protected $fillable = [
        'kode_bahan_baku',
        'nama_bahan_baku',
        'kategori_barang_id',
        'satuan_id',
        'stok_saat_ini',
        'stok_minimum',
        'is_aktif',
    ];

    protected $casts = [
        'stok_saat_ini' => 'decimal:2',
        'stok_minimum' => 'decimal:2',
        'is_aktif' => 'boolean',
    ];

    public function kategoriBarang(): BelongsTo
    {
        return $this->belongsTo(KategoriBarang::class);
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class);
    }

    /**
     * Produsen yang memasok bahan baku ini, lengkap dengan harga per satuan (pivot).
     */
    public function produsens(): BelongsToMany
    {
        return $this->belongsToMany(Produsen::class, 'bahan_baku_produsens')
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
     * Pengalihan di mana bahan baku ini adalah bahan baku asal yang diminta.
     */
    public function pengalihanSebagaiAsal(): HasMany
    {
        return $this->hasMany(PengalihanBahanBaku::class, 'bahan_baku_asal_id');
    }

    /**
     * Pengalihan di mana bahan baku ini adalah bahan baku pengganti.
     */
    public function pengalihanSebagaiPengganti(): HasMany
    {
        return $this->hasMany(PengalihanBahanBaku::class, 'bahan_baku_pengganti_id');
    }

    public function penerimaanBahanBakuDetails(): HasMany
    {
        return $this->hasMany(PenerimaanBahanBakuDetail::class);
    }

    public function pemakaianBahanBakuDetails(): HasMany
    {
        return $this->hasMany(PemakaianBahanBakuDetail::class);
    }
}
