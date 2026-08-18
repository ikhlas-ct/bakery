<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admin extends Model
{
    use HasFactory;

    protected $table = 'admins';

    protected $fillable = [
        'user_id',
        'nip',
        'nama',
        'no_telp',
        'alamat',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function permintaanBahanBakus(): HasMany
    {
        return $this->hasMany(PermintaanBahanBaku::class);
    }

    public function penerimaanBahanBakus(): HasMany
    {
        return $this->hasMany(PenerimaanBahanBaku::class);
    }

    public function pemakaianBahanBakus(): HasMany
    {
        return $this->hasMany(PemakaianBahanBaku::class);
    }
}
