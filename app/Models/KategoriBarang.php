<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBarang extends Model
{
    use HasFactory;

    protected $table = 'kategori_barangs';

    protected $fillable = [
        'nama_kategori',
    ];

    public function bahanBakus(): HasMany
    {
        return $this->hasMany(BahanBaku::class);
    }
}
