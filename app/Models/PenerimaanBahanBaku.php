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
        'pemilik_id',
        'produsen_id',
        'nomor_transaksi',
        'tanggal_terima',
        'catatan',
        'foto_receipt',
    ];

    protected $casts = [
        'tanggal_terima' => 'date',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /** Diisi bila penerimaan dicatat oleh pemilik (admin_id kosong). */
    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(Pemilik::class);
    }

    /**
     * Nama pencatat penerimaan, baik admin maupun pemilik.
     * Pakai ini (bukan $penerimaan->admin->nama) agar aman untuk penerimaan yang dicatat pemilik.
     */
    public function getNamaPencatatAttribute(): string
    {
        if ($this->admin) {
            return $this->admin->nama ?: $this->admin->user?->username ?: 'Admin';
        }

        if ($this->pemilik) {
            return $this->pemilik->user?->username ?: 'Pemilik';
        }

        return '-';
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
