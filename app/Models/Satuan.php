<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Satuan extends Model
{
    use HasFactory;

    protected $table = 'satuans';

    protected $fillable = [
        'nama',
        'kode_satuan',
    ];

    public function bahanBakus(): HasMany
    {
        return $this->hasMany(BahanBaku::class);
    }
}
